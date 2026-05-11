<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cancelacion extends Model
{
    use HasFactory;

    protected $table = 'cancelaciones';

    public $timestamps = false;

    protected $fillable = [
        'reserva_id',
        'tipo',
        'motivo',
        'created_at',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }
}
