<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuariosPerfil extends Model
{
    use HasFactory;

    protected $table = 'usuarios_perfil';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'fecha_nacimiento',
        'telefono_prefijo',
        'telefono_numero',
        'pais',
        'ciudad',
        'direccion',
        'codigo_postal',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    // Relacion: un perfil pertenece a un usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
}
