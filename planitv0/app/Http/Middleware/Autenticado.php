<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class Autenticado
{
    /**
     * Maneja la solicitud entrante.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('usuario_id')) {
            return redirect()->route('login')
                ->withErrors(['mensaje' => 'Debes iniciar sesión.']);
        }

        return $next($request);
    }
}
