<?php

namespace App\Exceptions\HttpExceptionHandlers;

use Illuminate\Http\Response;
use Throwable;
use Override;

class AuthenticationExceptionHandler extends BaseHttpExceptionHandler
{

    #[Override]
    protected static function status(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }

    #[Override]
    protected static function message(Throwable $e): string
    {
        return __('auth.failed');
    }
}
