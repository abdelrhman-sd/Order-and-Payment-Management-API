<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! ($token = $request->bearerToken())) {
            throw new UnauthorizedException(__('auth.unauthenticated'));
        }

        try {
            $payload = JwtService::decodeToken($token);

            if (JwtService::isBlackListed($payload->jti)) {
                throw new UnauthorizedException(__('auth.jwt_rvk'));
            }
            $request->merge(['user' => User::findOrFail($payload->sub)]);
        } catch (Exception $e) {
            throw $e instanceof UnauthorizedException
                ? $e
                : new UnauthorizedException(__('auth.jwt_invalid'));
        }

        return $next($request);
    }
}
