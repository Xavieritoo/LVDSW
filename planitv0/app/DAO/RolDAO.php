<?php

namespace App\DAO;

use Illuminate\Support\Facades\DB;
use App\Models\Rol;
use App\Models\Usuario;

class RolDAO
{
    /**
     * Obtener un rol por ID
     */
    public function obtenerPorId($rolId)
    {
        $resultado = DB::select(
            "SELECT * FROM roles WHERE id = ? LIMIT 1",
            [$rolId]
        );

        return $resultado ? new Rol($resultado[0]) : null;
    }

    /**
     * Obtener todos los roles
     */
    public function obtenerTodos()
    {
        $resultados = DB::select("SELECT * FROM roles");
        return array_map(fn($r) => new Rol($r), $resultados);
    }

    /**
     * Insertar un nuevo rol
     */
    public function insertar(Rol $rol)
    {
        return DB::insert(
            "INSERT INTO roles (nombre) VALUES (?)",
            [$rol->nombre]
        );
    }

    /**
     * Eliminar un rol por ID
     */
    public function eliminar($rolId)
    {
        return DB::delete(
            "DELETE FROM roles WHERE id = ?",
            [$rolId]
        );
    }

    /**
     * Obtener todos los usuarios de un rol
     */
    public function obtenerUsuarios($rolId)
    {
        $usuarios = DB::select(
            "SELECT * FROM usuarios WHERE rol_id = ?",
            [$rolId]
        );

        return array_map(fn($u) => new Usuario($u), $usuarios);
    }
}
