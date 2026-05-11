<?php

use Laravel\Sanctum\Sanctum;

return [

    // Dominios con estado (stateful)

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        Sanctum::currentApplicationUrlWithPort(),
        // Sanctum::currentRequestHost(),
    ))),

    // Guards de Sanctum

    'guard' => ['web'],

    // Minutos de expiración del token

    'expiration' => null,

    // Prefijo del token

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    // Middleware de Sanctum

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];
