<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReservaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $datos = $request->validate([
                'estado' => [
                    'nullable',
                    Rule::in(['confirmada', 'datos_pendientes', 'completada', 'cancelada_usuario', 'cancelada_aerolinea']),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Parametros de consulta no validos.',
                'errors' => $e->errors(),
            ], 422);
        }

        $usuario = $request->user();

        $query = Reserva::query()
            ->with(['pasajeros:id,reserva_id,nombre,apellidos'])
            ->where('user_id', $usuario->id)
            ->orderByDesc('fecha_salida');

        if (!empty($datos['estado'])) {
            $query->where('estado', $datos['estado']);
        }

        $reservas = $query->get()->map(function (Reserva $reserva): array {
            return $this->formatearReserva($reserva);
        });

        return response()->json([
            'data' => $reservas,
            'meta' => [
                'count' => $reservas->count(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $usuario = $request->user();

        $reserva = Reserva::query()
            ->with(['pasajeros:id,reserva_id,nombre,apellidos'])
            ->where('id', $id)
            ->first();

        if (!$reserva) {
            return response()->json([
                'message' => 'Reserva no encontrada.',
            ], 404);
        }

        if ((int) $reserva->user_id !== (int) $usuario->id) {
            return response()->json([
                'message' => 'No tienes permisos para acceder a esta reserva.',
            ], 403);
        }

        return response()->json([
            'data' => $this->formatearReserva($reserva),
        ]);
    }

    private function formatearReserva(Reserva $reserva): array
    {
        $fechaSalidaStr = null;
        if ($reserva->fecha_salida) {
            $fechaSalidaStr = $reserva->fecha_salida->toIso8601String();
        }
        $fechaLlegadaStr = null;
        if ($reserva->fecha_llegada) {
            $fechaLlegadaStr = $reserva->fecha_llegada->toIso8601String();
        }

        return [
            'id' => $reserva->id,
            'localizador' => $reserva->localizador,
            'origen' => $reserva->origen,
            'destino' => $reserva->destino,
            'fecha_salida' => $fechaSalidaStr,
            'fecha_llegada' => $fechaLlegadaStr,
            'estado' => $reserva->estado,
            'plan_tarifa' => $reserva->plan_tarifa,
            'checkin_estado' => $reserva->checkin_estado,
            'tarjetas_emitidas' => (bool) $reserva->tarjetas_emitidas,
            'checkin_disponible_ahora' => $reserva->checkinDisponibleAhora(),
            'pasajeros' => $reserva->pasajeros->map(fn ($p) => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'apellidos' => $p->apellidos,
            ])->values()->all(),
        ];
    }
}
