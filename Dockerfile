FROM php:8.5-rc-cli-alpine AS base

# install-php-extensions handles pre-built binaries, much faster than docker-php-ext-install
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    xml \
    opcache \
    redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# ─── Development ──────────────────────────────────────────────────────────────
FROM base AS development

COPY php/php.ini /usr/local/etc/php/conf.d/app.ini

RUN install-php-extensions xdebug

# deps layer cached separately from app code
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

# ─── Production ───────────────────────────────────────────────────────────────
FROM base AS production

COPY php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader

COPY . .

RUN composer dump-autoload --optimize \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && chown -R www-data:www-data /var/www

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
