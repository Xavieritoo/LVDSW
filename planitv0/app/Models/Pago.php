<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    public $timestamps = false;

    protected $fillable = [
        'reserva_id',
        'metodo',
        'cantidad',
        'estado',
        'fecha_pago',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'fecha_pago' => 'datetime',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }
}
