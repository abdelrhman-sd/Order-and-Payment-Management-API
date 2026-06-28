<?php

namespace App\Exceptions\HttpExceptionHandlers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Override;
use Throwable;
use Illuminate\Support\Str;

class ModelNotFoundExceptionHandler extends BaseHttpExceptionHandler
{
    #[Override]
    protected static function status(Throwable $e): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    #[Override]
    protected static function message(Throwable $e): string
    {
        assert($e instanceof ModelNotFoundException);

        return __('resource.not_found', [
            'resource' => Str::headline(class_basename($e->getModel()))
        ]);
    }
}
