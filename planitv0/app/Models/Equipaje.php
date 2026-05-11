<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipaje extends Model
{
    use HasFactory;

    protected $table = 'equipajes';

    protected $fillable = [
        'pasajero_id',
        'vuelo_id',
        'tipo',
        'peso',
        'cantidad',
        'precio',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio' => 'decimal:2',
    ];

    public function pasajero()
    {
        return $this->belongsTo(Pasajero::class, 'pasajero_id');
    }

    public function vuelo()
    {
        return $this->belongsTo(Vuelo::class, 'vuelo_id');
    }
}
