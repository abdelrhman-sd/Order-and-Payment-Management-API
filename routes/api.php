<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentGatewayController;;

use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {

    Route::controller(AuthController::class)->group(function () {
        Route::post('login', 'login');
        Route::post('logout', 'logout');

        Route::post('refresh', 'refresh');
        Route::post('register', 'register');

        Route::get('/me', 'me')->middleware(JwtMiddleware::class);
    });
});

Route::apiResource('orders', OrderController::class)
    ->only('index', 'show')
    ->middleware(JwtMiddleware::class);

Route::apiResource('payments', PaymentController::class)
    ->except('update')
    ->middleware(JwtMiddleware::class);

Route::controller(PaymentGatewayController::class)
    ->prefix('payments/{gateway}')
    ->group(function (): void {
        Route::post('initiate', 'initiate');
        Route::post('{payment}/refund', 'refund');
        Route::get('callback', 'returnFromGateway');
        Route::get('{gatewayPaymentId}/status', 'status');
    });
