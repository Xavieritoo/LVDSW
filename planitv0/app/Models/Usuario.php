<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [

        'nombre',
        'apellidos',
        'email',
        'password',
        'rol_id',
        'esta_verificado',
        'esta_activo',
        'deleted_at',
        'anonymized_at',
        'intentos_fallidos',
        'bloqueado_hasta',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'bloqueado_hasta' => 'datetime',
        'deleted_at' => 'datetime',
        'anonymized_at' => 'datetime',
        'esta_verificado' => 'boolean',
        'esta_activo' => 'boolean',
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'user_id');
    }

    public function perfil()
    {
        return $this->hasOne(UsuariosPerfil::class, 'user_id');
    }

    public function pasajerosFrecuentes()
    {
        return $this->hasMany(PasajeroFrecuente::class, 'user_id');
    }
}
