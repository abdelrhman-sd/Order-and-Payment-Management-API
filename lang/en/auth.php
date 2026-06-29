<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed'    => 'These credentials do not match our records.',
    'forbidden' => "You don't have the permission to make this action",
    'password'  => 'The provided password is incorrect.',
    'throttle'  => 'Too many login attempts. Please try again in :seconds seconds.',

    // JWT
    'jwt_rvk' => 'Access token has been revoked',
    'jwt_missing' => 'Access token missing',
    'jwt_invalid' => 'Access token invalid or expired',
    'jwt_rft_missing' => 'Refresh token missing',
    'jwt_rff_invalid' => 'Refresh token invalid or expired'
];
