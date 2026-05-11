<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasajeroFrecuente extends Model
{
    use HasFactory;

    protected $table = 'pasajeros_frecuentes';

    protected $fillable = [
        'user_id',
        'nombre',
        'apellidos',
        'tipo_documento',
        'numero_documento',
        'numero_documento_norm',
        'fecha_nacimiento',
        'pais',
        'favorito',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'favorito' => 'boolean',
    ];

    // Relacion: un pasajero frecuente pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
}
