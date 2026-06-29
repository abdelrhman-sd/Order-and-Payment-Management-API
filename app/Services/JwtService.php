<?php

namespace App\Services;

use App\Models\RefreshToken;
use Firebase\JWT\{JWT, Key};
use Illuminate\Support\Facades\Cache;

class JwtService
{
    /**
     * @param int $id
     * @param string[] $payload
     * @return string
     */
    public static function generateAccessToken(mixed $id, array $payload): string
    {
        $payload = [
            'sub' => $id,
            ...$payload,
            'iat' => time(),
            'exp' => time() + config('auth.jwt_ttl'),
            'jti' => bin2hex(random_bytes(16))
        ];

        return JWT::encode($payload, config('auth.jwt_secret'), 'HS256');
    }

    /**
     * @param int $id
     * @return string
     */
    public static function generateRefreshToken(int $id): string
    {
        RefreshToken::create([
            'user_id'       => $id,
            'token'         => hash('sha256', $raw = bin2hex(random_bytes(32))),
            'expires_at'    => now()->addDays(14)
        ]);

        return $raw;
    }

    /**
     * @param string $token
     * @return object
     */
    public static function decodeToken(string $token): object
    {
        return JWT::decode($token, new Key(config('auth.jwt_secret'), 'HS256'));
    }

    /**
     * @param string $jti
     * @return bool
     */
    public static function isBlackListed(string $jti): bool
    {
        return Cache::has('blacklist:' . $jti);
    }

    /**
     * @param string $token
     * @param string $refreshToken
     * @return void
     */
    public static function revokeTokens(?string $token = null, ?string $refreshToken = null): void
    {
        if (! is_null($token) && ($ttl = ($payload = self::decodeToken($token))->exp - time()) > 0) {
            Cache::put('blacklist:' . $payload->jti, true, $ttl);
        }

        if (! is_null($refreshToken)) {
            RefreshToken::where('token', hash('sha256', $refreshToken))
                ->update(['revoked_at' => now()]);
        }
    }
}
