<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    // Registrar los servicios de la aplicacion
    public function register(): void
    {
        //
    }

    // Inicializar los servicios de la aplicacion
    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email', 'guest');
            $key = strtolower($email) . '|' . $request->ip();

            return [
                Limit::perMinute(6)->by($key),
            ];
        });
    }
}
