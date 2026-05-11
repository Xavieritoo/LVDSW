<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use App\Models\Reserva;
use App\Models\Vuelo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminVueloController extends Controller
{
    public function index(Request $request): View
    {
        $origen = trim((string) $request->query('origen', ''));
        $destino = trim((string) $request->query('destino', ''));

        $query = Vuelo::query()
            ->with(['ciudadOrigen', 'ciudadDestino'])
            ->where('activo', true)
            ->orderByDesc('fecha_salida')
            ->orderByDesc('id');

        if ($origen !== '') {
            $query->where(function ($sub) use ($origen) {
                $sub->where('origen', 'like', '%' . $origen . '%')
                    ->orWhereHas('ciudadOrigen', function ($cityQuery) use ($origen) {
                        $cityQuery->where('nombre', 'like', '%' . $origen . '%');
                    });
            });
        }

        if ($destino !== '') {
            $query->where(function ($sub) use ($destino) {
                $sub->where('destino', 'like', '%' . $destino . '%')
                    ->orWhereHas('ciudadDestino', function ($cityQuery) use ($destino) {
                        $cityQuery->where('nombre', 'like', '%' . $destino . '%');
                    });
            });
        }

        $vuelos = $query
            ->paginate(12)
            ->withQueryString();

        return view('admin.vuelos.index', [
            'vuelos' => $vuelos,
            'filtroOrigen' => $origen,
            'filtroDestino' => $destino,
        ]);
    }

    public function create(): View
    {
        return view('admin.vuelos.create', [
            'ciudades' => $this->obtenerCiudades(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'origen_ciudad_id' => 'required|integer|exists:ciudades,id',
            'destino_ciudad_id' => 'required|integer|different:origen_ciudad_id|exists:ciudades,id',
            'fecha_salida' => 'required|date|after_or_equal:now',
            'fecha_llegada' => 'required|date|after:fecha_salida',
            'precio' => 'required|numeric|min:0',
            'asientos_disponibles' => 'required|integer|min:0',
            'terminal' => 'nullable|string|max:20',
            'tipo_tarifa' => 'nullable|string|max:50',
            'es_schengen' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
        ]);

        $origen = Ciudad::query()->findOrFail((int) $datos['origen_ciudad_id']);
        $destino = Ciudad::query()->findOrFail((int) $datos['destino_ciudad_id']);

        $vuelo = new Vuelo();
        $vuelo->origen_ciudad_id = (int) $datos['origen_ciudad_id'];
        $vuelo->destino_ciudad_id = (int) $datos['destino_ciudad_id'];
        $vuelo->origen = $origen->nombre;
        $vuelo->destino = $destino->nombre;
        $vuelo->fecha_salida = $datos['fecha_salida'];
        $vuelo->fecha_llegada = $datos['fecha_llegada'];
        $vuelo->precio = $datos['precio'];
        $vuelo->precio_base = $datos['precio'];
        $vuelo->asientos_disponibles = (int) $datos['asientos_disponibles'];
        if (isset($datos['terminal'])) {
            $vuelo->terminal = $datos['terminal'];
        } else {
            $vuelo->terminal = null;
        }

        if (isset($datos['tipo_tarifa'])) {
            $vuelo->tipo_tarifa = $datos['tipo_tarifa'];
        } else {
            $vuelo->tipo_tarifa = null;
        }

        if (array_key_exists('es_schengen', $datos)) {
            $vuelo->es_schengen = (bool) $datos['es_schengen'];
        } else {
            $vuelo->es_schengen = false;
        }

        if (array_key_exists('activo', $datos)) {
            $vuelo->activo = (bool) $datos['activo'];
        } else {
            $vuelo->activo = true;
        }

        $vuelo->save();

        return redirect()
            ->route('admin.vuelos.index')
            ->with('exito', 'Vuelo creado correctamente.');
    }

    public function edit(Vuelo $vuelo): View
    {
        return view('admin.vuelos.edit', [
            'vuelo' => $vuelo,
            'ciudades' => $this->obtenerCiudades(),
            'bloquearRuta' => $this->vueloTieneReservasConfirmadas($vuelo),
        ]);
    }

    public function update(Request $request, Vuelo $vuelo): RedirectResponse
    {
        $datos = $request->validate([
            'origen_ciudad_id' => 'required|integer|exists:ciudades,id',
            'destino_ciudad_id' => 'required|integer|different:origen_ciudad_id|exists:ciudades,id',
            'fecha_salida' => 'required|date|after_or_equal:now',
            'fecha_llegada' => 'required|date|after:fecha_salida',
            'precio' => 'required|numeric|min:0',
            'asientos_disponibles' => 'required|integer|min:0',
            'terminal' => 'nullable|string|max:20',
            'tipo_tarifa' => 'nullable|string|max:50',
            'es_schengen' => 'nullable|boolean',
            'activo' => 'nullable|boolean',
        ]);

        $bloquearRuta = $this->vueloTieneReservasConfirmadas($vuelo);
        $origenActual = (int) $vuelo->origen_ciudad_id;
        $destinoActual = (int) $vuelo->destino_ciudad_id;
        $nuevoOrigen = (int) $datos['origen_ciudad_id'];
        $nuevoDestino = (int) $datos['destino_ciudad_id'];

        if ($bloquearRuta && ($nuevoOrigen !== $origenActual || $nuevoDestino !== $destinoActual)) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'origen_ciudad_id' => 'No se puede modificar origen o destino porque el vuelo tiene reservas confirmadas.',
                ]);
        }

        $origen = Ciudad::query()->findOrFail((int) $datos['origen_ciudad_id']);
        $destino = Ciudad::query()->findOrFail((int) $datos['destino_ciudad_id']);

        $vuelo->origen_ciudad_id = (int) $datos['origen_ciudad_id'];
        $vuelo->destino_ciudad_id = (int) $datos['destino_ciudad_id'];
        $vuelo->origen = $origen->nombre;
        $vuelo->destino = $destino->nombre;
        $vuelo->fecha_salida = $datos['fecha_salida'];
        $vuelo->fecha_llegada = $datos['fecha_llegada'];
        $vuelo->precio = $datos['precio'];
        $vuelo->precio_base = $datos['precio'];
        $vuelo->asientos_disponibles = (int) $datos['asientos_disponibles'];
        if (isset($datos['terminal'])) {
            $vuelo->terminal = $datos['terminal'];
        } else {
            $vuelo->terminal = null;
        }

        if (isset($datos['tipo_tarifa'])) {
            $vuelo->tipo_tarifa = $datos['tipo_tarifa'];
        } else {
            $vuelo->tipo_tarifa = null;
        }

        if (array_key_exists('es_schengen', $datos)) {
            $vuelo->es_schengen = (bool) $datos['es_schengen'];
        } else {
            $vuelo->es_schengen = false;
        }

        if (array_key_exists('activo', $datos)) {
            $vuelo->activo = (bool) $datos['activo'];
        } else {
            $vuelo->activo = false;
        }

        $vuelo->save();

        return redirect()
            ->route('admin.vuelos.index')
            ->with('exito', 'Vuelo actualizado correctamente.');
    }

    public function definirHorarios(Request $request, Vuelo $vuelo): RedirectResponse
    {
        $datos = $request->validate([
            'hora_salida_programada' => 'required|date|after_or_equal:now',
            'hora_llegada_programada' => 'required|date|after:hora_salida_programada',
        ]);

        if ($this->columnaExiste('hora_salida_programada')) {
            $vuelo->hora_salida_programada = $datos['hora_salida_programada'];
        }

        if ($this->columnaExiste('hora_llegada_programada')) {
            $vuelo->hora_llegada_programada = $datos['hora_llegada_programada'];
        }

        if ($this->columnaExiste('fecha_salida')) {
            $vuelo->fecha_salida = $datos['hora_salida_programada'];
        }

        if ($this->columnaExiste('fecha_llegada')) {
            $vuelo->fecha_llegada = $datos['hora_llegada_programada'];
        }

        $vuelo->save();

        return redirect()
            ->route('admin.vuelos.index')
            ->with('exito', 'Horarios actualizados correctamente.');
    }

    public function destroy(Vuelo $vuelo): RedirectResponse
    {
        $reservasConfirmadas = $this->vueloTieneReservasConfirmadas($vuelo);

        if ($reservasConfirmadas) {
            return redirect()
                ->route('admin.vuelos.index')
                ->with('error', 'No se puede eliminar el vuelo porque tiene reservas confirmadas.');
        }

        $vuelo->delete();

        return redirect()
            ->route('admin.vuelos.index')
            ->with('exito', 'Vuelo eliminado correctamente.');
    }

    private function obtenerCiudades()
    {
        return Ciudad::query()
            ->orderBy('nombre')
            ->get();
    }

    private function columnaExiste(string $columna): bool
    {
        return Schema::hasColumn('vuelos', $columna);
    }

    private function vueloTieneReservasConfirmadas(Vuelo $vuelo): bool
    {
        return Reserva::query()
            ->where('estado', 'confirmada')
            ->where(function ($query) use ($vuelo) {
                $query->where('vuelo_id', $vuelo->id)
                    ->orWhere('vuelo_vuelta_id', $vuelo->id);
            })
            ->exists();
    }
}
