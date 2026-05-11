<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Throwable;

class JwtAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        $token = $this->getTokenFromRequest($request);

        if (!$token) {
            return response()->json([
                'error' => 'Token requerido.',
            ], 401);
        }

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret(), 'HS256'));

            $usuario = Usuario::find($decoded->sub);

            if (!$usuario) {
                return response()->json([
                    'error' => 'Token invalido: usuario no encontrado.',
                ], 401);
            }

            if (!$usuario->esta_activo) {
                return response()->json([
                    'error' => 'Cuenta desactivada.',
                ], 403);
            }

            $request->setUserResolver(function () use ($usuario) {
                return $usuario;
            });
        } catch (ExpiredException $e) {
            return response()->json([
                'error' => 'Token expirado.',
            ], 401);
        } catch (SignatureInvalidException $e) {
            return response()->json([
                'error' => 'Token invalido.',
            ], 401);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Token invalido.',
            ], 401);
        }

        return $next($request);
    }

    private function getTokenFromRequest(Request $request): ?string
    {
        $header = $request->header('Authorization');

        if ($header && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return null;
    }

    private function jwtSecret(): string
    {
        $clave = (string) config('app.key');
        if (str_starts_with($clave, 'base64:')) {
            $decoded = base64_decode(substr($clave, 7), true);
            if ($decoded !== false && $decoded !== '') {
                return $decoded;
            }
        }

        return $clave;
    }
}
