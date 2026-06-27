.PHONY: help up down build shell artisan migrate fresh seed logs ps

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

up: ## Start all containers
	docker compose up -d

down: ## Stop all containers
	docker compose down

build: ## Build/rebuild images
	docker compose build --no-cache

shell: ## Shell into the app container
	docker compose exec app bash

artisan: ## Run artisan command — usage: make artisan CMD="migrate"
	docker compose exec app php artisan $(CMD)

migrate: ## Run migrations
	docker compose exec app php artisan migrate

fresh: ## Fresh migrate with seeders
	docker compose exec app php artisan migrate:fresh --seed

seed: ## Run seeders
	docker compose exec app php artisan db:seed

logs: ## Tail logs
	docker compose logs -f

ps: ## Show running containers
	docker compose ps

key: ## Generate app key
	docker compose exec app php artisan key:generate

setup: ## First-time setup: copy .env, install deps, key, migrate
	#cp -n .env.example .env || true
	docker compose up -d --build
	docker compose exec app composer install
	docker compose exec app php artisan key:generate
	docker compose exec app php artisan migrate
	@echo "\n✅  App running at http://localhost:$${APP_PORT:-8000}"
