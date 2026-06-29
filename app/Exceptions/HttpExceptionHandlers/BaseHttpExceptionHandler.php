<?php

namespace App\Exceptions\HttpExceptionHandlers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

abstract class BaseHttpExceptionHandler
{

    protected static function status(Throwable $e): int
    {
        return $e instanceof HttpException
            ? $e->getStatusCode()
            : Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    protected static function message(Throwable $e): string
    {
        return $e->getMessage();
    }

    protected static function buildHttpJsonResponse(Throwable $e, bool $isHandled = false): array
    {
        /** @var UnauthorizedException $e */
        $response = [
            'success'   => false,
            'status'    => static::status($e),
            'message'   => static::message($e)
        ];

        if (app()->hasDebugModeEnabled() && !$isHandled) {
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

    public static function getHttpJsonResponse(Throwable $e, bool $isHandled = false): JsonResponse
    {
        return response()->json(static::buildHttpJsonResponse($e, $isHandled), static::status($e));
    }
}
