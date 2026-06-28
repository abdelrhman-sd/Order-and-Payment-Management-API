<?php

namespace App\Exceptions\HttpExceptionHandlers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

abstract class BaseHttpExceptionHandler
{

    protected static function status(): int
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    protected static function message(Throwable $e): string
    {
        return 'Internal server error';
    }

    protected static function buildHttpJsonResponse(Throwable $e): array
    {
        $response = [
            'success'   => false,
            'status'    => static::status(),
            'message'   => static::message($e),
        ];

        if (app()->hasDebugModeEnabled() && !($e instanceof HttpException)) {
            $response = array_merge($response, [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => collect($e->getTrace())->map(fn($t) => [
                    'file'     => $t['file'] ?? null,
                    'line'     => $t['line'] ?? null,
                    'function' => $t['function'],
                    'class'    => $t['class'] ?? null,
                ])
            ]);
        }

        return $response;
    }

    public static function getHttpJsonResponse(Throwable $e): JsonResponse
    {
        return response()->json(static::buildHttpJsonResponse($e), static::status());
    }
}
