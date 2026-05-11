<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckinEvento extends Model
{
    use HasFactory;

    protected $table = 'checkin_eventos';

    public $timestamps = false;

    protected $fillable = [
        'reserva_id',
        'reserva_pasajero_id',
        'tipo',
        'actor_tipo',
        'actor_user_id',
        'actor_email',
        'descripcion',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    // Relacion: un evento pertenece a una reserva
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    // Relacion: un evento puede pertenecer a un pasajero
    public function pasajero()
    {
        return $this->belongsTo(ReservaPasajero::class, 'reserva_pasajero_id');
    }

    // Relacion: un evento puede tener como actor a un usuario
    public function actorUsuario()
    {
        return $this->belongsTo(Usuario::class, 'actor_user_id');
    }
}
