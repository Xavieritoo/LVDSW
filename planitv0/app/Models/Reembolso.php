<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reembolso extends Model
{
    use HasFactory;

    protected $table = 'reembolsos';

    public $timestamps = false;

    protected $fillable = [
        'reserva_id',
        'estado',
        'cantidad',
        'created_at',
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }
}
