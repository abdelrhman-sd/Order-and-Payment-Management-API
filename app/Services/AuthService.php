<?php

namespace App\Services;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\UnauthorizedException;

class AuthService
{
    /**
     * @param string $email
     * @param string $password
     * @throws AuthenticationException
     * @return array<string, mixed>
     */
    public static function login(string $email, string $password): array
    {
        return self::issueToken((int) (self::attempt($email, $password))->id, ['email' => 'email']);
    }

    /**
     * @param string $email
     * @param string $password
     * @throws AuthenticationException
     * @return User
     */
    private static function attempt(string $email, string $password): User
    {

        /** @var User */
        $user = User::where('email', $email)->firstOrFail();

        if (! Hash::check($password, $user->password)) {
            throw new AuthenticationException();
        }

        return $user;
    }

    /**
     * @param int $userId
     * @param array $payload - jwt payload
     * @throws AuthenticationException
     * @return array<string, mixed>
     */
    public static function issueToken(int $userId, array $payload): array
    {
        Cookie::queue(
            config('auth.refresh_token_cookie_name'),
            JwtService::generateRefreshToken($userId),
            60 * 24 * 14,
            null,
            false,
            true,
            false,
            'Strict'
        );

        return [
            'access_token'  => JwtService::generateAccessToken($userId, $payload),
            'token_type'    => 'Bearer',
            'expires_in'    => config('auth.jwt_ttl')
        ];
    }

    /**
     * @param Request $request
     * @return void
     */
    public static function logout(Request $request): void
    {
        JwtService::revokeTokens(
            token: $request->bearerToken(),
            refreshToken: $request->cookie(config('auth.refresh_token_cookie_name'))
        );
    }

    public static function refresh(Request $request): array
    {
        if (! ($refreshToken = $request->cookie(config('auth.refresh_token_cookie_name')))) {
            throw new UnauthorizedException(__('auth.jwt_rft_missing'));
        }

        /** @var RefreshToken */
        $refreshToken = RefreshToken::where('token', hash('sha256', $refreshToken))->first();

        if (! $refreshToken || ! $refreshToken->isValid()) {
            throw new UnauthorizedException(__('auth.jwt_rf_invalid'));
        }

        JwtService::revokeTokens($request->bearerToken(), $refreshToken);

        return self::issueToken(
            $refreshToken->user->id,
            ['email' => $refreshToken->user->password]
        );
    }
}
