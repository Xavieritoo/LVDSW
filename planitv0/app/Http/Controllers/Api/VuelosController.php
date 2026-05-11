<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vuelo;
use Illuminate\Http\JsonResponse;

class VuelosController extends Controller
{
    public function index(): JsonResponse
    {
        $vuelos = Vuelo::query()
            ->select(['origen', 'destino', 'tipo_tarifa'])
            ->where('activo', true)
            ->where('fecha_salida', '>=', now())
            ->distinct()
            ->orderBy('origen')
            ->orderBy('destino')
            ->limit(30)
            ->get();

        return response()->json([
            'data' => $vuelos,
            'meta' => [
                'count' => $vuelos->count(),
            ],
        ]);
    }
}
