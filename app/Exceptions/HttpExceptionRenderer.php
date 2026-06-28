<?php

namespace App\Exceptions;

use App\Exceptions\HttpExceptionHandlers\AuthenticationExceptionHandler;
use App\Exceptions\HttpExceptionHandlers\BaseHttpExceptionHandler;
use App\Exceptions\HttpExceptionHandlers\ModelNotFoundExceptionHandler;
use App\Exceptions\HttpExceptionHandlers\NotFoundExceptionHandler;
use App\Exceptions\HttpExceptionHandlers\ValidationExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HttpExceptionRenderer
{
    public static function register(Exceptions $exceptions): void
    {
        $handlers = [
            ValidationException::class      => ValidationExceptionHandler::class,
            NotFoundHttpException::class    => NotFoundExceptionHandler::class,
            AuthenticationException::class  => AuthenticationExceptionHandler::class,
            \Throwable::class               => BaseHttpExceptionHandler::class,
        ];

        foreach ($handlers as $exception => $handler) {
            $exceptions->render(function (\Throwable $e, Request $request) use ($exception, $handler) {
                if (!$request->is('api/*')) return null;

                if ($e instanceof NotFoundHttpException && $e->getPrevious() instanceof ModelNotFoundException) {
                    return ModelNotFoundExceptionHandler::getHttpJsonResponse($e->getPrevious());
                }

                if ($e instanceof $exception) {
                    return $handler::getHttpJsonResponse($e);
                }
            });
        }
    }
}
