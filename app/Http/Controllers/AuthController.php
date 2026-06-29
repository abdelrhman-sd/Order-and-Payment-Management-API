<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        return ApiResponse::build(AuthService::login($request->email, $request->password));
    }

    public function logout(Request $request): void
    {
        AuthService::logout($request);
    }

    public function refresh(Request $request): JsonResponse
    {
        return ApiResponse::build(AuthService::refresh($request));
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::build($request->user);
    }
}
