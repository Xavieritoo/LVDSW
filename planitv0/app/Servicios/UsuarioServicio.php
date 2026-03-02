<?php

namespace App\Servicios;

use App\DAO\UsuarioDAO;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class UsuarioServicio
{
    private $usuarioDAO;

    public function __construct()
    {
        $this->usuarioDAO = new UsuarioDAO();
    }

    /**
     * Registrar un nuevo usuario
     */
    public function registrar(array $datos)
    {
        // Hashear contraseña
        $datos['password'] = \Illuminate\Support\Facades\Hash::make($datos['password']);

        $usuario = new \App\Models\Usuario($datos);

        // 🔹 Retornar el usuario insertado (con ID asignado)
        return $this->usuarioDAO->insertar($usuario);
    }

    /**
     * Login de usuario
     */
    public function login(string $email, string $password)
    {
        $usuario = $this->usuarioDAO->obtenerPorEmail($email);

        if (!$usuario) {
            return null; // usuario no encontrado
        }

        // Verificar contraseña
        if (!Hash::check($password, $usuario->password)) {
            // Aumentar intentos fallidos
            $intentos = $usuario->intentos_fallidos + 1;
            $this->usuarioDAO->actualizarIntentosFallidos($usuario->id, $intentos);
            return null;
        }

        // Resetear intentos fallidos si estaba bloqueado
        if ($usuario->intentos_fallidos > 0) {
            $this->usuarioDAO->actualizarIntentosFallidos($usuario->id, 0);
        }

        // Guardar usuario en sesión
        Session::put('usuario_id', $usuario->id);

        return $usuario;
    }

    /**
     * Bloquear un usuario hasta cierta fecha
     */
    public function bloquearUsuario($usuarioId, $fechaBloqueo)
    {
        $this->usuarioDAO->bloquearUsuario($usuarioId, $fechaBloqueo);
    }

    /**
     * Obtener todos los usuarios
     */
    public function obtenerTodos()
    {
        return $this->usuarioDAO->obtenerTodos();
    }

    /**
     * Obtener usuario por ID
     */
    public function obtenerPorId($usuarioId)
    {
        return $this->usuarioDAO->obtenerPorId($usuarioId);
    }

    /**
     * Obtener usuario por email
     */
    public function obtenerPorEmail($email)
    {
        return $this->usuarioDAO->obtenerPorEmail($email);
    }

    /**
     * Eliminar usuario
     */
    public function eliminar($usuarioId)
    {
        $this->usuarioDAO->eliminar($usuarioId);
    }
    public function actualizarDatos($usuarioId, array $datos)
    {
        $campos = [];
        $valores = [];

        foreach ($datos as $key => $valor) {
            $campos[] = "$key = ?";
            $valores[] = $valor;
        }

        $valores[] = $usuarioId;

        $sql = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = ?";
        return \Illuminate\Support\Facades\DB::update($sql, $valores);
    }
    public function cambiarPassword($email, $password)
    {
        $hash = \Illuminate\Support\Facades\Hash::make($password);
        $sql = "UPDATE usuarios SET password = ? WHERE email = ?";
        return \Illuminate\Support\Facades\DB::update($sql, [$hash, $email]);
    }
}
