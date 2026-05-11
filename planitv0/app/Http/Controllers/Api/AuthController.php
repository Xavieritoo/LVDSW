<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        try {
            $datos = $request->validate([
                'email' => ['nullable', 'email', 'required_without:username'],
                'username' => ['nullable', 'string', 'max:150', 'required_without:email'],
                'password' => ['required', 'string'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Datos de acceso no validos.',
                'errors' => $e->errors(),
            ], 422);
        }

        $email = $datos['username'];
        if (isset($datos['email'])) {
            $email = $datos['email'];
        }
        $usuario = Usuario::where('email', (string) $email)->first();

        if (!$usuario) {
            return response()->json([
                'message' => 'Credenciales invalidas.',
            ], 401);
        }

        if ($usuario->bloqueado_hasta && now()->lt($usuario->bloqueado_hasta)) {
            return response()->json([
                'message' => 'Cuenta temporalmente bloqueada.',
            ], 423);
        }

        if (!$usuario->esta_activo) {
            return response()->json([
                'message' => 'Cuenta desactivada.',
            ], 403);
        }

        if (!$usuario->esta_verificado) {
            return response()->json([
                'message' => 'Debes verificar tu correo antes de iniciar sesion.',
            ], 403);
        }

        if (!Hash::check($datos['password'], $usuario->password)) {
            $intentosFallidos = 0;
            if (isset($usuario->intentos_fallidos)) {
                $intentosFallidos = (int) $usuario->intentos_fallidos;
            }
            $usuario->intentos_fallidos = $intentosFallidos + 1;

            if ($usuario->intentos_fallidos >= 3) {
                $usuario->bloqueado_hasta = now()->addMinutes(10);
                $usuario->intentos_fallidos = 0;
            }

            $usuario->save();

            return response()->json([
                'message' => 'Credenciales invalidas.',
            ], 401);
        }

        $cargaToken = [
            'iss' => config('app.url'),
            'sub' => $usuario->id,
            'iat' => now()->timestamp,
            'exp' => now()->addHour()->timestamp,
            'typ' => 'access',
        ];

        $tokenJwt = JWT::encode($cargaToken, $this->jwtSecret(), 'HS256');

        $usuario->intentos_fallidos = 0;
        $usuario->bloqueado_hasta = null;
        $usuario->save();

        return response()->json([
            'message' => 'Login correcto.',
            'token' => $tokenJwt,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'user' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'apellidos' => $usuario->apellidos,
                'email' => $usuario->email,
                'rol_id' => $usuario->rol_id,
                'esta_verificado' => (bool) $usuario->esta_verificado,
                'esta_activo' => (bool) $usuario->esta_activo,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $usuario = $request->user();

        return response()->json([
            'user' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombre,
                'apellidos' => $usuario->apellidos,
                'email' => $usuario->email,
                'rol_id' => $usuario->rol_id,
                'esta_verificado' => (bool) $usuario->esta_verificado,
                'esta_activo' => (bool) $usuario->esta_activo,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Sesion cerrada en cliente. El token debe eliminarse del almacenamiento local.',
        ]);
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
