<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Servicios\UsuarioServicio;
use App\Servicios\RolServicio;

class RegisterController extends Controller
{
    private $usuarioServicio;
    private $rolServicio;

    public function __construct()
    {
        $this->usuarioServicio = new UsuarioServicio();
        $this->rolServicio = new RolServicio();
    }

    /**
     * Mostrar formulario de registro
     */
    public function mostrarRegistro()
    {
        return view('register');
    }

    /**
     * Procesar registro
     */
    public function registrar(Request $request)
    {
        // validar datos básicos
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:150',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|min:4|confirmed',
        ]);

        // obtenemos el rol "usuario" sin preocuparnos por mayúsculas/minúsculas
        // (la seeder inserta "Usuario" con U mayúscula).
        $roles = $this->rolServicio->obtenerTodos();
        $rolUsuario = null;
        foreach ($roles as $rol) {
            if (strtolower($rol->nombre) === 'usuario') {
                $rolUsuario = $rol;
                break;
            }
        }

        if (!$rolUsuario) {
            return back()->withErrors(['email' => 'El rol usuario no existe en la base de datos.']);
        }

        $datosUsuario = [
            'nombre' => $request->nombre,
            'apellidos' => $request->apellidos,
            'email' => $request->email,
            'password' => $request->password,
            'rol_id' => $rolUsuario->id,
        ];

        $usuario = $this->usuarioServicio->registrar($datosUsuario);

        // iniciar sesión regenerando la sesión para evitar fijación
        $request->session()->regenerate();
        Session::put('usuario_id', $usuario->id);

        return redirect()->route('principal')
            ->with('success', 'Cuenta creada correctamente.');
    }
}
