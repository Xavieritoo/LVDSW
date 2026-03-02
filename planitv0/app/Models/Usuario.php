<?php

namespace App\Models;

class Usuario
{
    public $id;
    public $nombre;
    public $apellidos;
    public $email;
    public $password;
    public $rol_id;
    public $telefono;
    public $pais;
    public $codigo_postal;
    public $poblacion;
    public $direccion;
    public $fecha_nacimiento;
    public $documento_identidad;
    public $intentos_fallidos;
    public $bloqueado_hasta;
    public $remember_token;

    public function __construct($datos = null)
    {
        if ($datos) {

            // 🔹 Si viene como objeto (DB::select)
            if (is_object($datos)) {
                $datos = (array) $datos;
            }

            $this->id = $datos['id'] ?? null;
            $this->nombre = $datos['nombre'] ?? null;
            $this->apellidos = $datos['apellidos'] ?? null;
            $this->email = $datos['email'] ?? null;
            $this->password = $datos['password'] ?? null;
            $this->rol_id = $datos['rol_id'] ?? null;
            $this->telefono = $datos['telefono'] ?? null;
            $this->pais = $datos['pais'] ?? null;
            $this->codigo_postal = $datos['codigo_postal'] ?? null;
            $this->poblacion = $datos['poblacion'] ?? null;
            $this->direccion = $datos['direccion'] ?? null;
            $this->fecha_nacimiento = $datos['fecha_nacimiento'] ?? null;
            $this->documento_identidad = $datos['documento_identidad'] ?? null;
            $this->intentos_fallidos = $datos['intentos_fallidos'] ?? 0;
            $this->bloqueado_hasta = $datos['bloqueado_hasta'] ?? null;
            $this->remember_token = $datos['remember_token'] ?? null;
        }
    }
}
