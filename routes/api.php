<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;;

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

Route::post('orders', [OrderController::class, 'store'])->middleware(JwtMiddleware::class);
