<?php

namespace App\Exceptions\HttpExceptionHandlers;

use Illuminate\Http\Response;
use Override;
use Throwable;

class NotFoundExceptionHandler extends BaseHttpExceptionHandler
{
    #[Override]
    protected static function status(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    #[Override]
    protected static function message(Throwable $e): string
    {
        return __('route.not_found');
    }
}
