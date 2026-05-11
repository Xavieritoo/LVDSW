<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asiento extends Model
{
    use HasFactory;

    protected $table = 'asientos_vuelo';

    protected $fillable = [
        'vuelo_id',
        'codigo',
        'tipo',
        'ocupado',
    ];

    protected $casts = [
        'ocupado' => 'boolean',
    ];

    public function vuelo()
    {
        return $this->belongsTo(Vuelo::class, 'vuelo_id');
    }
}
