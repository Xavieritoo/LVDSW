<?php

namespace App\Models;

class Rol
{
    public $id;
    public $nombre;

    /**
     * Constructor que recibe datos de la base de datos
     * $datos puede ser un objeto (stdClass de DB::select) o un array asociativo
     */
    public function __construct($datos = null)
    {
        if ($datos) {
            $this->id = $datos->id ?? null;
            $this->nombre = $datos->nombre ?? null;
        }
    }
}
