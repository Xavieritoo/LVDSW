<?php

namespace App\DAO;

use Illuminate\Support\Facades\DB;
use App\Models\Usuario;

class UsuarioDAO
{
    /**
     * Obtener un usuario por email
     */
    public function insertar(Usuario $usuario)
    {
        try {
            DB::insert(
                "INSERT INTO usuarios 
                (nombre, apellidos, email, password, rol_id, telefono, pais, codigo_postal, poblacion, direccion, fecha_nacimiento, documento_identidad, intentos_fallidos, bloqueado_hasta, remember_token) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $usuario->nombre,
                    $usuario->apellidos,
                    $usuario->email,
                    $usuario->password,
                    $usuario->rol_id,
                    $usuario->telefono ?? null,
                    $usuario->pais ?? null,
                    $usuario->codigo_postal ?? null,
                    $usuario->poblacion ?? null,
                    $usuario->direccion ?? null,
                    $usuario->fecha_nacimiento ?? null,
                    $usuario->documento_identidad ?? null,
                    $usuario->intentos_fallidos ?? 0,
                    $usuario->bloqueado_hasta ?? null,
                    $usuario->remember_token ?? null,
                ]
            );

            // Asignar ID generado por la BD
            $usuario->id = DB::getPdo()->lastInsertId();

            return $usuario;
        } catch (\Exception $e) {
            // Mostrar error para depuración
            dd("Error al insertar usuario: " . $e->getMessage());
        }
    }

    public function obtenerPorEmail($email)
    {
        $resultado = DB::select(
            "SELECT * FROM usuarios WHERE email = ? LIMIT 1",
            [$email]
        );

        return $resultado ? new Usuario($resultado[0]) : null;
    }

    /**
     * Obtener un usuario por su ID
     */
    public function obtenerPorId($usuarioId)
    {
        $resultado = DB::select(
            "SELECT * FROM usuarios WHERE id = ? LIMIT 1",
            [$usuarioId]
        );

        return $resultado ? new Usuario($resultado[0]) : null;
    }


    /**
     * Actualizar intentos fallidos de un usuario
     */
    public function actualizarIntentosFallidos($usuarioId, $intentos)
    {
        return DB::update(
            "UPDATE usuarios SET intentos_fallidos = ? WHERE id = ?",
            [$intentos, $usuarioId]
        );
    }

    /**
     * Bloquear un usuario hasta una fecha/hora determinada
     */
    public function bloquearUsuario($usuarioId, $fechaBloqueo)
    {
        return DB::update(
            "UPDATE usuarios SET bloqueado_hasta = ? WHERE id = ?",
            [$fechaBloqueo, $usuarioId]
        );
    }

    /**
     * Eliminar un usuario por ID
     */
    public function eliminar($usuarioId)
    {
        return DB::delete(
            "DELETE FROM usuarios WHERE id = ?",
            [$usuarioId]
        );
    }

    /**
     * Obtener todos los usuarios
     */
    public function obtenerTodos()
    {
        $resultados = DB::select("SELECT * FROM usuarios");
        return array_map(fn($u) => new Usuario($u), $resultados);
    }
}
