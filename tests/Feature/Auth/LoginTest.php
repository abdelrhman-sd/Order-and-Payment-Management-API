<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('issues_access_token_and_refresh_token_cookie_on_successful_login', function (): void {

    $response = postJson('/api/login', [
        'email'     => User::factory()->create()->email,
        'password'  => 'password'
    ]);

    $response
        ->assertOk()
        ->assertJsonStructure([
            'success',
            'data' => [
                'access_token',
                'token_type',
                'expires_in'
            ]
        ]);
});


it('rejects_login_with_wrong_password', function (): void {

    $response = postJson('/api/login', [
        'email'     => User::factory()->create()->email,
        'password'  => 'wrong-password'
    ]);

    $response
        ->assertUnauthorized()
        ->assertJson([
            'success'   => false,
            'status'    => Response::HTTP_UNAUTHORIZED,
            'message'   => __('auth.failed')
        ]);
});


it('rejects_login_with_nonexistent_email', function (): void {

    $response = postJson('/api/login', [
        'email'     => 'email@nonexistent.email',
        'password'  => 'password'
    ]);

    $response
        ->assertNotFound()
        ->assertJson([
            'success'   => false,
            'status'    => Response::HTTP_NOT_FOUND,
            'message'   => __('resource.not_found', ['resource' => 'User'])
        ]);
});


it('it_rejects_login_with_invalid_login_fields', function () {

    $response = postjson('/api/login', []);
    $response
        ->assertUnprocessable()
        ->assertjson([
            'success'   => false,
            'status'    => response::HTTP_UNPROCESSABLE_ENTITY,
            'message'   => __('validation.failed')
        ]);
});
