<?php

namespace App\Exceptions\HttpExceptionHandlers;

use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Override;
use Throwable;

class ValidationExceptionHandler extends BaseHttpExceptionHandler
{
    #[Override]
    protected static function status(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    #[Override]
    protected static function message(Throwable $e): string
    {
        return __('validation.failed');
    }

    #[Override]
    protected static function buildHttpJsonResponse(Throwable $e): array
    {
        assert($e instanceof ValidationException);

        return array_merge(parent::buildHttpJsonResponse($e), [
            'errors' => $e->errors()
        ]);
    }
}
