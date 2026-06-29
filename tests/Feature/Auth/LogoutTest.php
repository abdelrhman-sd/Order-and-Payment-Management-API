<?php

use App\Models\RefreshToken;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\TestResponse;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withToken;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertTrue;

uses(RefreshDatabase::class);

it('revoke_tokens_on_logout', function (): void {

    $user = User::factory()->create();

    $response = postJson('/api/login', [
        'email'     => $user->email,
        'password'  => 'password'
    ]);

    $response
        ->assertJsonStructure(['data' => ['access_token']]);

    $token = $response->json('data.access_token');

    $refreshTokenRaw = $response
        ->getCookie(config('auth.refresh_token_cookie_name'), false)
        ->getValue();

    /** @var TestResponse  */
    $response = test()->call(
        'POST',
        '/api/logout',
        [],
        [config('auth.refresh_token_cookie_name') => $refreshTokenRaw], // cookies array
        [],
        ['HTTP_AUTHORIZATION' => 'Bearer ' . $token],
    );

    $payload = JwtService::decodeToken($token);

    assertTrue(Cache::has('blacklist:' . $payload->jti));

    $response->assertOk();

    // asserting revokation of refresh token
    assertDatabaseHas('refresh_tokens', ['user_id' => $user->id]);

    $revoked_at = RefreshToken::where('token', hash('sha256', $refreshTokenRaw))
        ->value('revoked_at');

    assertNotNull($revoked_at);
});

it('rejects_requests_with_blacklisted_tokens', function (): void {

    $user = User::factory()->create();
    $token = JwtService::generateAccessToken($user->id, ['email' => $user->email]);

    withToken($token)
        ->postJson('api/logout')
        ->assertOk();

    withToken($token)
        ->get('api/me')
        ->assertUnauthorized()
        ->assertJson([
            'success'   => false,
            'status'    => Response::HTTP_UNAUTHORIZED,
            'message'   => __('auth.jwt_rvk')
        ]);
});
