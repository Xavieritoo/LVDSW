<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    // Mostrar formulario de inicio de sesion
    public function mostrarLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $usuario = Usuario::where('email', $credenciales['email'])->first();

        if (!$usuario) {
            return back()->withErrors([
                'email' => 'La combinacion de correo electronico y contrasena introducida no coincide con ninguna cuenta. Vuelve a intentarlo.',
            ]);
        }

        if ($usuario->bloqueado_hasta && now()->lt($usuario->bloqueado_hasta)) {
            return back()->withErrors([
                'email' => 'Tu cuenta esta temporalmente bloqueada. Intentalo de nuevo mas tarde.',
            ]);
        }

        if (!$usuario->esta_activo) {
            return back()->withErrors([
                'email' => 'Tu cuenta esta desactivada. Contacta con soporte si necesitas ayuda.',
            ]);
        }

        if (!$usuario->esta_verificado) {
            return back()->withErrors([
                'email' => 'Debes verificar tu correo electronico antes de iniciar sesion.',
            ]);
        }

        if (!Hash::check($credenciales['password'], $usuario->password)) {
            $intentosFallidos = $usuario->intentos_fallidos;
            if (!$intentosFallidos) {
                $intentosFallidos = 0;
            }
            $usuario->intentos_fallidos = $intentosFallidos + 1;

            if ($usuario->intentos_fallidos >= 3) {
                $usuario->bloqueado_hasta = now()->addMinutes(10);
                $usuario->intentos_fallidos = 0;
            }

            $usuario->save();

            return back()->withErrors([
                'email' => 'La combinacion de correo electronico y contrasena introducida no coincide con ninguna cuenta. Vuelve a intentarlo.',
            ]);
        }

        Auth::login($usuario);

        $usuario->intentos_fallidos = 0;
        $usuario->bloqueado_hasta = null;
        $usuario->save();

        $request->session()->regenerate();

        return redirect()->route('principal');
    }

    // Cerrar sesion
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('principal');
    }
}
