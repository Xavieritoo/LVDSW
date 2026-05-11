<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vuelo extends Model
{
    use HasFactory;

    protected $table = 'vuelos';

    protected $fillable = [
        'codigo',
        'origen',
        'destino',
        'fecha_salida',
        'fecha_llegada',
        'es_schengen',
        'precio_base',
        'origen_ciudad_id',
        'destino_ciudad_id',
        'precio',
        'asientos_disponibles',
        'activo',
        'terminal',
        'tipo_tarifa',
        'numero_vuelo',
        'aerolinea_id',
        'ruta_id',
        'estado_id',
        'hora_salida_programada',
        'hora_salida_real',
        'hora_llegada_programada',
        'hora_llegada_real',
        'avion_id',
        'puerta_salida',
        'puerta_llegada',
        'terminal_salida',
        'terminal_llegada',
        'pasajeros_confirmados',
        'tripulacion_cantidad',
    ];

    protected $casts = [
        'fecha_salida' => 'datetime',
        'fecha_llegada' => 'datetime',
        'es_schengen' => 'boolean',
        'precio_base' => 'decimal:2',
        'precio' => 'decimal:2',
        'activo' => 'boolean',
        'hora_salida_programada' => 'datetime',
        'hora_salida_real' => 'datetime',
        'hora_llegada_programada' => 'datetime',
        'hora_llegada_real' => 'datetime',
    ];

    public function reservasIda()
    {
        return $this->hasMany(Reserva::class, 'vuelo_id');
    }

    public function reservasVuelta()
    {
        return $this->hasMany(Reserva::class, 'vuelo_vuelta_id');
    }

    public function asientos()
    {
        return $this->hasMany(Asiento::class, 'vuelo_id');
    }

    public function equipajes()
    {
        return $this->hasMany(Equipaje::class, 'vuelo_id');
    }

    public function ciudadOrigen()
    {
        return $this->belongsTo(Ciudad::class, 'origen_ciudad_id');
    }

    public function ciudadDestino()
    {
        return $this->belongsTo(Ciudad::class, 'destino_ciudad_id');
    }

    public function ofertas()
    {
        return $this->hasMany(Oferta::class, 'vuelo_id');
    }
}
