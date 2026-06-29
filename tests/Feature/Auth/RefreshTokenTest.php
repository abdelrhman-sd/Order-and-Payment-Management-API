<?php

use App\Models\RefreshToken;
use App\Models\User;
use App\Services\AuthService;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withCookie;
use function PHPUnit\Framework\assertNotNull;

//uses(RefreshDatabase::class);

//test('it_issues_new_tokens_with_valid_refresh_token', function () {
//
//    $response = postJson('/api/login', [
//        'email'     => User::factory()->create()->email,
//        'password'  => 'password',
//    ]);
//
//    $refreshTokenRaw = $response
//        ->getCookie(AuthService::getRefreshTokenCookie(), false)
//        ->getValue();
//
//    /** @var TestResponse  */
//    $response = test()->call(
//        'POST',
//        '/api/refresh/access/token',
//        [],
//        [AuthService::getRefreshTokenCookie() => $refreshTokenRaw],
//    );
//
//    $response
//        ->assertOk()
//        ->assertJsonPath('success', true)
//        ->assertJsonStructure([
//            'data' => ['access_token', 'token_type', 'expires_in']
//        ]);
//
//    // assert rotating the refresh token
//    $refreshToken = RefreshToken::where('token', hash('sha256', $refreshTokenRaw))->first();
//    dd($refreshTokenRaw, hash('sha256', $refreshTokenRaw), $refreshToken->token);
//    assertNotNull($refreshToken);
//});
