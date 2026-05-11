<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReservaPasajero extends Model
{
    use HasFactory;

    protected $table = 'reserva_pasajeros';

    public $timestamps = true;

    protected $fillable = [
        'reserva_id',
        'pasajero_id',
        'nombre',
        'apellidos',
        'tipo_documento',
        'numero_documento',
        'numero_documento_norm',
        'fecha_nacimiento',
        'checkin_confirmado_en',
        'asiento_codigo',
        'asiento_asignado_en',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'checkin_confirmado_en' => 'datetime',
        'asiento_asignado_en' => 'datetime',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }
}
