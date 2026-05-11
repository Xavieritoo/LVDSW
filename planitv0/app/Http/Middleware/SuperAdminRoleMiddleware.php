<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminRoleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = Auth::id();

        $rolNombre = Cache::remember('rol_usuario_' . $userId, 300, function () use ($userId) {
            $usuario = Usuario::query()->with('rol')->find($userId);
            if (!$usuario || !$usuario->rol) {
                return null;
            }
            return mb_strtolower(trim((string) $usuario->rol->nombre), 'UTF-8');
        });

        if ($rolNombre !== 'superadmin') {
            abort(403, 'Acceso no autorizado.');
        }

        return $next($request);
    }
}
