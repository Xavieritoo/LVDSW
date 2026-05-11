<?php

return [

    // Nombre de la aplicación

    'name' => env('APP_NAME', 'Laravel'),

    // Entorno de la aplicación

    'env' => env('APP_ENV', 'production'),

    // Modo depuración de la aplicación

    'debug' => (bool) env('APP_DEBUG', false),

    // URL de la aplicación

    'url' => env('APP_URL', 'http://localhost'),

    // Zona horaria de la aplicación

    'timezone' => 'UTC',

    // Configuración de idioma de la aplicación

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    // Clave de encriptación

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    // Driver de modo mantenimiento

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
