<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    protected $table = 'ofertas';

    protected $fillable = [
        'vuelo_id',
        'nombre',
        'descripcion',
        'descuento',
        'precio_promocional',
        'fecha_inicio',
        'fecha_fin',
        'cupo',
        'activo',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'activo' => 'boolean',
    ];

    public function vuelo()
    {
        return $this->belongsTo(Vuelo::class);
    }

    public function estaActiva(): bool
    {
        return $this->activo
            && $this->fecha_inicio <= now()
            && $this->fecha_fin >= now()
            && $this->cupo > 0;
    }
}
