<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservaEstadoHistorial extends Model
{
    use HasFactory;

    protected $table = 'reserva_estado_historial';

    public $timestamps = false;

    protected $fillable = [
        'reserva_id',
        'estado_anterior',
        'estado_nuevo',
        'motivo',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    // Relacion: un cambio de estado pertenece a una reserva
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }
}
