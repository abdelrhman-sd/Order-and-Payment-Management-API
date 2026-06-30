<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterUserRequest;
use App\Http\Responses\ApiResponse;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        return ApiResponse::build(data: AuthService::login($request->email, $request->password));
    }

    public function logout(Request $request): JsonResponse
    {
        AuthService::logout($request);

        return ApiResponse::build(additional: [
            'message' => __('auth.logout')
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        return ApiResponse::build(data: AuthService::refresh($request));
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::build($request->user);
    }

    public function register(RegisterUserRequest $request): JsonResponse
    {
        User::create($request->validated());

        return ApiResponse::build(
            message: __('resource.created', ['resource' => 'User']),
            status: Response::HTTP_CREATED,
        );
    }
}
