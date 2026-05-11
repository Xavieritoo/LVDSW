<?php

namespace App\Http\Controllers;

use App\Models\Vuelo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VueloController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('destinos.index');
    }

    public function resultados(Request $request)
    {
        $busqueda = $this->construirBusqueda($request);

        $validator = Validator::make($busqueda, [
            'tipo_viaje'   => 'required|in:solo_ida,ida_vuelta',
            'origen'       => 'nullable|string|max:100',
            'destino'      => 'nullable|string|max:100|different:origen',
            'fecha_ida'    => 'nullable|date',
            'fecha_vuelta' => 'nullable|date|after_or_equal:fecha_ida',
            'adultos'      => 'required|integer|min:0|max:9',
            'menores'      => 'required|integer|min:0|max:8',
            'infantes'     => 'required|integer|min:0|max:8',
            'zona'         => 'required|in:all,schengen,international',
        ]);

        $validator->after(function ($validator) use ($busqueda) {
            $adultos  = (int) $busqueda['adultos'];
            $menores  = (int) $busqueda['menores'];
            $infantes = (int) $busqueda['infantes'];

            if (($adultos + $menores + $infantes) < 1) {
                $validator->errors()->add('adultos', 'Debes seleccionar al menos un pasajero.');
            }

            if ($infantes > $adultos) {
                $validator->errors()->add('infantes', 'Cada bebé debe viajar con un adulto.');
            }

            if ($busqueda['tipo_viaje'] === 'ida_vuelta' && empty($busqueda['fecha_vuelta'])) {
                $validator->errors()->add('fecha_vuelta', 'La fecha de vuelta es obligatoria para ida y vuelta.');
            }
        });

        if ($validator->fails()) {
            return redirect()->route('destinos.index')
                ->withErrors($validator)
                ->withInput();
        }

        [$vuelosIda, $avisoIda] = $this->buscarVuelosConFallback(
            $busqueda['origen'],
            $busqueda['destino'],
            $busqueda['fecha_ida'],
            $busqueda['zona'],
            'ida'
        );

        $vuelosVuelta = collect();
        $avisoVuelta  = null;

        if ($busqueda['tipo_viaje'] === 'ida_vuelta') {
            [$vuelosVuelta, $avisoVuelta] = $this->buscarVuelosConFallback(
                $busqueda['destino'],
                $busqueda['origen'],
                $busqueda['fecha_vuelta'],
                $busqueda['zona'],
                'vuelta'
            );
        }

        return view('resultados', [
            'busqueda'       => $busqueda,
            'vuelosIda'      => $vuelosIda,
            'vuelosVuelta'   => $vuelosVuelta,
            'avisoIda'       => $avisoIda,
            'avisoVuelta'    => $avisoVuelta,
            'totalPasajeros' => (int) $busqueda['adultos'] + (int) $busqueda['menores'] + (int) $busqueda['infantes'],
        ]);
    }

    private function buscarVuelosConFallback(?string $origen, ?string $destino, ?string $fecha, string $zona, string $direccion): array
    {
        $vuelos = $this->consultarVuelos($origen, $destino, $fecha, $zona)->get();
        $aviso  = null;

        if ($vuelos->isEmpty() && !empty($fecha)) {
            $vuelos = $this->consultarVuelosCercanos($origen, $destino, $fecha, $zona);
            if ($vuelos->isNotEmpty()) {
                $fechaReal   = $vuelos->first()->fecha_salida->format('d/m/Y');
                $fechaPedida = \Carbon\Carbon::parse($fecha)->format('d/m/Y');
                $aviso = "No hay vuelos de {$direccion} el {$fechaPedida}. Mostrando los más cercanos del {$fechaReal}.";
            }
        }

        return [$vuelos, $aviso];
    }

    private function construirBusqueda(Request $request): array
    {
        return [
            'tipo_viaje'   => $request->input('tipo_viaje', 'ida_vuelta'),
            'origen'       => $request->input('origen'),
            'destino'      => $request->input('destino'),
            'fecha_ida'    => $request->input('fecha_ida'),
            'fecha_vuelta' => $request->input('fecha_vuelta'),
            'adultos'      => $request->input('adultos', 1),
            'menores'      => $request->input('menores', 0),
            'infantes'     => $request->input('infantes', 0),
            'zona'         => $request->input('zona', 'all'),
        ];
    }

    private function consultarVuelos(?string $origen, ?string $destino, ?string $fecha, string $zona): Builder
    {
        $query = Vuelo::query()
            ->with(['ciudadOrigen', 'ciudadDestino'])
            ->orderBy('fecha_salida');

        if (!empty($origen)) {
            $query->where(function ($q) use ($origen) {
                $q->where('origen', 'like', '%' . $origen . '%')
                  ->orWhereHas('ciudadOrigen', fn ($cq) => $cq->where('nombre', 'like', '%' . $origen . '%'));
            });
        }

        if (!empty($destino)) {
            $query->where(function ($q) use ($destino) {
                $q->where('destino', 'like', '%' . $destino . '%')
                  ->orWhereHas('ciudadDestino', fn ($cq) => $cq->where('nombre', 'like', '%' . $destino . '%'));
            });
        }

        if (!empty($fecha)) {
            $query->whereDate('fecha_salida', $fecha);
        }

        if ($zona === 'schengen') {
            $query->where('es_schengen', true);
        }

        if ($zona === 'international') {
            $query->where('es_schengen', false);
        }

        return $query;
    }

    private function consultarVuelosCercanos(?string $origen, ?string $destino, string $fecha, string $zona): \Illuminate\Support\Collection
    {
        // Buscar el vuelo más cercano a la fecha pedida (solo futuro o mismo día)
        $cercano = $this->consultarVuelos($origen, $destino, null, $zona)
            ->whereDate('fecha_salida', '>=', $fecha)
            ->orderBy('fecha_salida')
            ->first();

        if (!$cercano) {
            return collect();
        }

        return $this->consultarVuelos($origen, $destino, $cercano->fecha_salida->toDateString(), $zona)->get();
    }
}
