<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pasajero extends Model
{
    use HasFactory;

    protected $table = 'pasajeros';

    protected $fillable = [
        'reserva_id',
        'nombre',
        'apellidos',
        'fecha_nacimiento',
        'tipo',
        'documento_identidad',
        'nacionalidad',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'reserva_id' => 'integer',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function equipajes()
    {
        return $this->hasMany(Equipaje::class, 'pasajero_id');
    }
}
