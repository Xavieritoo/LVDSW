<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ciudad extends Model
{
    protected $table = 'ciudades';

    protected $fillable = [
        'nombre',
        'pais',
        'codigo_iata',
        'imagen',
        'latitud',
        'longitud',
    ];

    public function vuelosOrigen()
    {
        return $this->hasMany(Vuelo::class, 'origen_ciudad_id');
    }

    public function vuelosDestino()
    {
        return $this->hasMany(Vuelo::class, 'destino_ciudad_id');
    }
}
