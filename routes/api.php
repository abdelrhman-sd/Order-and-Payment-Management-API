<?php

use App\Http\Controllers\AuthController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Support\Facades\Route;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('logout', 'logout');

    Route::post('refresh/access/token', 'refresh');

    Route::get('/me', 'me')->middleware(JwtMiddleware::class);
});
