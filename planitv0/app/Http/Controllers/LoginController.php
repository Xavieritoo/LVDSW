<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Servicios\UsuarioServicio;

class LoginController extends Controller
{
    private $usuarioServicio;

    public function __construct()
    {
        $this->usuarioServicio = new UsuarioServicio();
    }

    /**
     * Mostrar formulario de login
     */
    public function mostrarLogin()
    {
        return view('login');
    }

    /**
     * Procesar login
     */
    public function login(Request $request)
    {
        // Validación de datos
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        // Usamos el servicio para obtener usuario y verificar contraseña
        $usuario = $this->usuarioServicio->login($email, $password);

        if ($usuario) {
            // Login correcto, regenerar sesión
            $request->session()->regenerate();
            Session::put('usuario_id', $usuario->id);
            return redirect()->route('principal');
        }

        // Login incorrecto
        return back()->withErrors([
            'email' => 'Credenciales incorrectas',
        ]);
    }

    /**
     * Logout del usuario
     */
    public function logout(Request $request)
    {
        // Limpiar sesión
        Session::forget('usuario_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('principal');
    }
}
