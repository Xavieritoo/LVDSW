<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FlightController extends Controller
{
    public function index(Request $request)
    {
        $origen = $request->query('origen', '');
        $destino = $request->query('destino', '');
        $numeroVuelo = strtoupper($request->query('numeroVuelo', ''));
        $fecha = $request->query('fecha', '');
        $mes = $request->query('mes', '');

        $hoy = Carbon::today();
        $mesMinimo = $hoy->copy()->startOfMonth()->format('Y-m');
        $mesMaximo = '2027-12';

        $vuelos = collect();
        $ciudadOrigen = null;
        $ciudadDestino = null;
        $calendarioDias = [];
        if ($mes) {
            $mesSeleccionado = $mes;
        } else {
            $mesSeleccionado = $hoy->format('Y-m');
        }
        $hayCalendario = false;

        // Búsqueda por número de vuelo (mantiene input date)
        if ($fecha && $numeroVuelo) {
            $vuelos = $this->buscarVuelos(null, null, $numeroVuelo, $fecha);
        }

        // Búsqueda por ruta + mes → calendario
        if ($origen && $destino && $mes) {
            $ciudadOrigen = Ciudad::where('codigo_iata', $origen)->first();
            $ciudadDestino = Ciudad::where('codigo_iata', $destino)->first();

            if ($ciudadOrigen && $ciudadDestino) {
                $hayCalendario = true;
                $calendarioDias = $this->construirCalendarioMes($ciudadOrigen, $ciudadDestino, $mesSeleccionado, $hoy);
            }
        }

        // Si se hace clic en un día del calendario
        if ($origen && $destino && $fecha && !$numeroVuelo) {
            if (!$ciudadOrigen) {
                $ciudadOrigen = Ciudad::where('codigo_iata', $origen)->first();
            }
            if (!$ciudadDestino) {
                $ciudadDestino = Ciudad::where('codigo_iata', $destino)->first();
            }
            $vuelos = $this->buscarVuelos($origen, $destino, null, $fecha);
        }

        return view('flights.estado-vuelos', [
            'origen' => $origen,
            'destino' => $destino,
            'numeroVuelo' => $numeroVuelo,
            'fecha' => $fecha,
            'mes' => $mesSeleccionado,
            'mesMinimo' => $mesMinimo,
            'mesMaximo' => $mesMaximo,
            'vuelos' => $vuelos,
            'ciudadOrigen' => $ciudadOrigen,
            'ciudadDestino' => $ciudadDestino,
            'hayCalendario' => $hayCalendario,
            'calendarioDias' => $calendarioDias,
        ]);
    }

    private function buscarVuelos($origen, $destino, $numeroVuelo, $fecha)
    {
        $query = DB::table('vuelos')
            ->join('ciudades as origen_c', 'vuelos.origen_ciudad_id', '=', 'origen_c.id')
            ->join('ciudades as destino_c', 'vuelos.destino_ciudad_id', '=', 'destino_c.id')
            ->leftJoin('aerolineas', 'vuelos.aerolinea_id', '=', 'aerolineas.id')
            ->leftJoin('estados_vuelo', 'vuelos.estado_id', '=', 'estados_vuelo.id')
            ->select(
                'vuelos.*',
                'aerolineas.nombre as aerolinea_nombre',
                'origen_c.codigo_iata as origen_codigo',
                'origen_c.nombre as origen_ciudad',
                'origen_c.pais as origen_pais',
                'destino_c.codigo_iata as destino_codigo',
                'destino_c.nombre as destino_ciudad',
                'destino_c.pais as destino_pais',
                'estados_vuelo.nombre as estado_nombre'
            )
            ->whereDate('vuelos.fecha_salida', $fecha);

        if ($numeroVuelo) {
            $query->where('vuelos.numero_vuelo', $numeroVuelo);
        } else {
            $query->where('origen_c.codigo_iata', $origen)
                ->where('destino_c.codigo_iata', $destino);
        }

        return $query->orderBy('vuelos.hora_salida_programada')->get()
            ->map(function ($item) {
                $item->aerolinea = (object) ['nombre' => $item->aerolinea_nombre ?? 'Planit'];
                $item->estado = (object) ['nombre' => $this->resolverEstadoDinamico($item)];
                $item->ruta = (object) [
                    'aeropuertoOrigen' => (object) [
                        'codigo_iata' => $item->origen_codigo,
                        'ciudad' => $item->origen_ciudad,
                        'pais' => $item->origen_pais,
                    ],
                    'aeropuertoDestino' => (object) [
                        'codigo_iata' => $item->destino_codigo,
                        'ciudad' => $item->destino_ciudad,
                        'pais' => $item->destino_pais,
                    ],
                ];
                return $item;
            });
    }

    private function construirCalendarioMes($ciudadOrigen, $ciudadDestino, $mes, $hoy)
    {
        $inicioMes = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
        $finMes = $inicioMes->copy()->endOfMonth();

        // Buscar qué días del mes tienen vuelos en esta ruta
        $vuelosDelMes = DB::table('vuelos')
            ->join('ciudades as origen_c', 'vuelos.origen_ciudad_id', '=', 'origen_c.id')
            ->join('ciudades as destino_c', 'vuelos.destino_ciudad_id', '=', 'destino_c.id')
            ->where('origen_c.codigo_iata', $ciudadOrigen->codigo_iata)
            ->where('destino_c.codigo_iata', $ciudadDestino->codigo_iata)
            ->whereBetween('vuelos.fecha_salida', [$inicioMes->format('Y-m-d'), $finMes->format('Y-m-d 23:59:59')])
            ->select(DB::raw('DATE(vuelos.fecha_salida) as dia'), DB::raw('COUNT(*) as total_vuelos'))
            ->groupBy('dia')
            ->pluck('total_vuelos', 'dia')
            ->toArray();

        $dias = [];
        $primerDiaSemana = $inicioMes->copy()->dayOfWeekIso; // 1=lunes, 7=domingo

        // Celdas vacías al inicio
        for ($i = 1; $i < $primerDiaSemana; $i++) {
            $dias[] = ['vacio' => true];
        }

        // Días del mes
        $cursor = $inicioMes->copy();
        while ($cursor->lte($finMes)) {
            $fechaStr = $cursor->format('Y-m-d');
            $esPasado = $cursor->lt($hoy);
            $totalVuelos = $vuelosDelMes[$fechaStr] ?? 0;

            $dias[] = [
                'vacio' => false,
                'dia' => $cursor->day,
                'fecha' => $fechaStr,
                'pasado' => $esPasado,
                'tiene_vuelos' => $totalVuelos > 0,
                'total_vuelos' => $totalVuelos,
            ];

            $cursor->addDay();
        }

        return $dias;
    }

    public function show(int $id)
    {
        $vuelo = DB::table('vuelos')
            ->join('ciudades as origen_c', 'vuelos.origen_ciudad_id', '=', 'origen_c.id')
            ->join('ciudades as destino_c', 'vuelos.destino_ciudad_id', '=', 'destino_c.id')
            ->leftJoin('aerolineas', 'vuelos.aerolinea_id', '=', 'aerolineas.id')
            ->leftJoin('estados_vuelo', 'vuelos.estado_id', '=', 'estados_vuelo.id')
            ->leftJoin('aviones', 'vuelos.avion_id', '=', 'aviones.id')
            ->select(
                'vuelos.*',
                'aerolineas.nombre as aerolinea_nombre',
                'aviones.matricula as avion_matricula',
                'origen_c.codigo_iata as origen_codigo',
                'origen_c.nombre as origen_ciudad',
                'origen_c.pais as origen_pais',
                'destino_c.codigo_iata as destino_codigo',
                'destino_c.nombre as destino_ciudad',
                'destino_c.pais as destino_pais',
                'estados_vuelo.nombre as estado_nombre'
            )
            ->where('vuelos.id', $id)
            ->first();

        if (! $vuelo) {
            abort(404);
        }

        $vuelo->aerolinea = (object) ['nombre' => $vuelo->aerolinea_nombre ?? 'Planit'];
        $vuelo->estado = (object) ['nombre' => $this->resolverEstadoDinamico($vuelo)];
        $vuelo->ruta = (object) [
            'aeropuertoOrigen' => (object) [
                'codigo_iata' => $vuelo->origen_codigo,
                'ciudad' => $vuelo->origen_ciudad,
                'pais' => $vuelo->origen_pais,
            ],
            'aeropuertoDestino' => (object) [
                'codigo_iata' => $vuelo->destino_codigo,
                'ciudad' => $vuelo->destino_ciudad,
                'pais' => $vuelo->destino_pais,
            ],
        ];

        return view('flights.detail', ['vuelo' => $vuelo]);
    }

    private function resolverEstadoDinamico(object $vuelo): string
    {
        $estadoActual = strtolower((string) ($vuelo->estado_nombre ?? 'programado'));

        // Si el vuelo ya figura cancelado en BD, no se recalcula.
        if ($estadoActual === 'cancelado') {
            return 'cancelado';
        }

        $ahora = Carbon::now();
        $salida = $this->aCarbon($vuelo->hora_salida_programada ?? $vuelo->fecha_salida ?? null);
        $llegada = $this->aCarbon($vuelo->hora_llegada_programada ?? $vuelo->fecha_llegada ?? null);

        if (! $salida || ! $llegada) {
            if ($estadoActual !== '') {
                return $estadoActual;
            }

            return 'programado';
        }

        if ($ahora->gte($llegada)) {
            return 'aterrizado';
        }

        if ($ahora->lt($salida)) {
            $minutosParaSalida = $ahora->diffInMinutes($salida);
            if ($minutosParaSalida <= 45) {
                return 'embarcando';
            }

            return 'programado';
        }

        // Entre salida y llegada: si acumula retraso antes de despegar, mostrar retrasado.
        $salidaReal = $this->aCarbon($vuelo->hora_salida_real ?? null);
        $retrasoUmbral = $salida->copy()->addMinutes(15);
        if (! $salidaReal && $ahora->gt($retrasoUmbral)) {
            return 'retrasado';
        }

        return 'en_vuelo';
    }

    private function aCarbon($fecha): ?Carbon
    {
        if (! $fecha) {
            return null;
        }

        if ($fecha instanceof Carbon) {
            return $fecha;
        }

        return Carbon::parse($fecha);
    }
}
