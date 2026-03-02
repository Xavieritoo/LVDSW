<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Session;
use App\Servicios\UsuarioServicio;
use App\DAO\PasswordResetDAO;

class PasswordResetController extends Controller
{
    private $usuarioServicio;
    private $passwordResetDAO;

    public function __construct()
    {
        $this->usuarioServicio = new UsuarioServicio();
        $this->passwordResetDAO = new PasswordResetDAO();
    }

    /**
     * Procesar la solicitud de recuperación
     */
    public function enviarCorreo(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        // Verificar que exista usuario
        $usuario = $this->usuarioServicio->obtenerPorEmail($email);
        if (!$usuario) {
            return back()->withErrors(['email' => 'No existe un usuario con este correo.']);
        }

        // Generar token seguro
        $token = Str::random(64);

        // Guardar token hasheado
        $this->passwordResetDAO->updateOrInsert($email, Hash::make($token));

        // Crear enlace
        $link = url('/password/reset/' . $token . '?email=' . urlencode($email));

        // Enviar correo
        Mail::send('password', ['link' => $link], function ($message) use ($email) {
            $message->to($email);
            $message->subject('Recuperación de contraseña');
        });

        return back()->with('status', 'Te hemos enviado un enlace para restablecer tu contraseña.');
    }

    /**
     * Mostrar el formulario de restablecimiento
     */
    public function mostrarFormulario($token, Request $request)
    {
        return view('reset', [
            'token' => $token,
            'email' => $request->email
        ]);
    }

    /**
     * Procesar restablecimiento de contraseña
     */
    public function restablecer(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:4',
        ]);

        // Obtener registro de password_resets
        $record = $this->passwordResetDAO->obtenerPorEmail($request->email);
        if (!$record) {
            return back()->withErrors(['email' => 'No hay solicitud de recuperación para este correo.']);
        }

        // Validar expiración (30 minutos)
        if ((strtotime(now()) - strtotime($record->created_at)) > 1800) {
            return back()->withErrors(['token' => 'El enlace ha expirado.']);
        }

        // Validar token
        if (!Hash::check($request->token, $record->token)) {
            return back()->withErrors(['token' => 'Token inválido.']);
        }

        // Cambiar contraseña usando servicio
        $this->usuarioServicio->cambiarPassword($request->email, $request->password);

        // Eliminar token usado
        $this->passwordResetDAO->eliminarPorEmail($request->email);

        // Opcional: enviar correo de confirmación
        Mail::raw('Tu contraseña ha sido cambiada exitosamente.', function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Contraseña cambiada');
        });

        return redirect()->route('login')
            ->with('status', 'Contraseña cambiada correctamente. Ahora puedes iniciar sesión.');
    }
}
