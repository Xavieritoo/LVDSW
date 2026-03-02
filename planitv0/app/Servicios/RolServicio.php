<?php

namespace App\Servicios;

use App\DAO\RolDAO;
use App\Models\Rol;

class RolServicio
{
    private $rolDAO;

    public function __construct()
    {
        $this->rolDAO = new RolDAO();
    }

    /**
     * Obtener un rol por ID
     */
    public function obtenerPorId($rolId)
    {
        return $this->rolDAO->obtenerPorId($rolId);
    }

    /**
     * Obtener todos los roles
     */
    public function obtenerTodos()
    {
        return $this->rolDAO->obtenerTodos();
    }

    /**
     * Insertar un nuevo rol
     */
    public function insertar(array $datos)
    {
        $rol = new Rol($datos);
        $this->rolDAO->insertar($rol);
        return $rol;
    }

    /**
     * Eliminar rol
     */
    public function eliminar($rolId)
    {
        $this->rolDAO->eliminar($rolId);
    }

    /**
     * Obtener todos los usuarios de un rol
     */
    public function obtenerUsuarios($rolId)
    {
        return $this->rolDAO->obtenerUsuarios($rolId);
    }
}
