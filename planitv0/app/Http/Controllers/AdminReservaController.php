<?php

namespace App\Http\Controllers;

use App\Models\Asiento;
use App\Models\Reserva;
use App\Models\ReservaPasajero;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminReservaController extends Controller
{
    public function index(Request $request): View
    {
        $filtros = [
            'localizador' => trim((string) $request->query('localizador', '')),
            'estado' => trim((string) $request->query('estado', '')),
            'checkin' => trim((string) $request->query('checkin', '')),
        ];

        $query = Reserva::query()
            ->with(['vueloIda:id,numero_vuelo,fecha_salida,fecha_llegada', 'vueloVuelta:id,numero_vuelo,fecha_salida,fecha_llegada'])
            ->orderByDesc('created_at');

        if ($filtros['localizador'] !== '') {
            $query->where('localizador', 'like', '%' . strtoupper($filtros['localizador']) . '%');
        }

        if ($filtros['estado'] !== '') {
            $query->where('estado', $filtros['estado']);
        }

        if ($filtros['checkin'] === 'completado') {
            $query->where(function ($sub) {
                $sub->where('checkin_estado', 'confirmada')
                    ->orWhereNotNull('checkin_realizado_en');
            });
        }

        if ($filtros['checkin'] === 'pendiente') {
            $query->where(function ($sub) {
                $sub->whereNull('checkin_realizado_en')
                    ->where(function ($nested) {
                        $nested->whereNull('checkin_estado')
                            ->orWhere('checkin_estado', '!=', 'confirmada');
                    });
            });
        }

        $reservas = $query
            ->paginate(15)
            ->withQueryString();

        return view('admin.reservas.index', [
            'reservas' => $reservas,
            'filtros' => $filtros,
            'esSuperadmin' => $this->usuarioActualEsSuperadmin(),
        ]);
    }

    public function show(Reserva $reserva): View
    {
        $reserva->load([
            'vueloIda:id,numero_vuelo,origen,destino,fecha_salida,fecha_llegada',
            'vueloVuelta:id,numero_vuelo,origen,destino,fecha_salida,fecha_llegada',
            'pasajeros:id,reserva_id,nombre,apellidos,tipo_documento,numero_documento,fecha_nacimiento,checkin_confirmado_en,asiento_codigo',
            'pasajerosCompra:id,reserva_id,nombre,apellidos,fecha_nacimiento,tipo,documento_identidad,nacionalidad',
        ]);

        $pasajerosCheckin = $reserva->pasajeros->map(function ($pasajero) {
            if (trim((string) $pasajero->nombre) !== '') {
                $nombre = (string) $pasajero->nombre;
            } else {
                $nombre = '-';
            }

            if (trim((string) $pasajero->apellidos) !== '') {
                $apellidos = (string) $pasajero->apellidos;
            } else {
                $apellidos = '-';
            }

            return [
                'fuente' => 'checkin',
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'fecha_nacimiento' => optional($pasajero->fecha_nacimiento)->format('d/m/Y'),
                'documento' => $this->mascarDocumento((string) $pasajero->numero_documento),
                'tipo_documento' => (string) $pasajero->tipo_documento,
                'nacionalidad' => null,
                'checkin_completado' => !is_null($pasajero->checkin_confirmado_en),
                'asiento' => $pasajero->asiento_codigo,
            ];
        });

        $pasajerosCompra = $reserva->pasajerosCompra->map(function ($pasajero) use ($reserva) {
            if (trim((string) $pasajero->nombre) !== '') {
                $nombre = (string) $pasajero->nombre;
            } else {
                $nombre = '-';
            }

            if (trim((string) $pasajero->apellidos) !== '') {
                $apellidos = (string) $pasajero->apellidos;
            } else {
                $apellidos = '-';
            }

            return [
                'fuente' => 'compra',
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'fecha_nacimiento' => optional($pasajero->fecha_nacimiento)->format('d/m/Y'),
                'documento' => $this->mascarDocumento((string) $pasajero->documento_identidad),
                'tipo_documento' => null,
                'nacionalidad' => $pasajero->nacionalidad,
                'checkin_completado' => $reserva->checkinRealizado(),
                'asiento' => null,
            ];
        });

        $pasajeros = $pasajerosCheckin->concat($pasajerosCompra)->values();

        return view('admin.reservas.show', [
            'reserva' => $reserva,
            'pasajeros' => $pasajeros,
            'esSuperadmin' => $this->usuarioActualEsSuperadmin(),
        ]);
    }

    public function edit(Reserva $reserva): View
    {
        $this->autorizarSuperadmin();

        $reserva->load([
            'vueloIda:id,numero_vuelo,origen,destino,fecha_salida,fecha_llegada',
            'vueloVuelta:id,numero_vuelo,origen,destino,fecha_salida,fecha_llegada',
            'pasajeros:id,reserva_id,nombre,apellidos,tipo_documento,numero_documento,fecha_nacimiento,checkin_confirmado_en,asiento_codigo',
        ]);

        return view('admin.reservas.edit', [
            'reserva' => $reserva,
            'pasajeros' => $reserva->pasajeros()->orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, Reserva $reserva): RedirectResponse
    {
        $this->autorizarSuperadmin();

        $datos = $request->validate([
            'estado' => 'required|in:confirmada,completada,cancelada_usuario,cancelada_aerolinea',
            'checkin_estado' => 'required|in:pendiente,confirmada',
            'pasajeros' => 'required|array|min:1',
            'pasajeros.*.id' => 'required|integer|exists:reserva_pasajeros,id',
            'pasajeros.*.nombre' => 'required|string|max:100',
            'pasajeros.*.apellidos' => 'required|string|max:150',
            'pasajeros.*.tipo_documento' => 'required|in:DNI,PASAPORTE',
            'pasajeros.*.numero_documento' => 'required|string|max:20',
            'pasajeros.*.fecha_nacimiento' => 'required|date|before:today',
            'pasajeros.*.asiento_codigo' => ['nullable', 'string', 'min:2', 'max:3'],
        ]);

        // Validación manual del formato de asiento (fila 1-30, columna A-F)
        foreach ($datos['pasajeros'] as $indice => $filaPasajero) {
            if (isset($filaPasajero['asiento_codigo']) && $filaPasajero['asiento_codigo'] !== null && $filaPasajero['asiento_codigo'] !== '') {
                $codigo = strtoupper(trim((string) $filaPasajero['asiento_codigo']));
                $longitudCodigo = mb_strlen($codigo, 'UTF-8');
                $esValido = false;

                if ($longitudCodigo >= 2 && $longitudCodigo <= 3) {
                    $letra = mb_substr($codigo, -1, 1, 'UTF-8');
                    $numero = mb_substr($codigo, 0, $longitudCodigo - 1, 'UTF-8');

                    if (in_array($letra, ['A', 'B', 'C', 'D', 'E', 'F'], true) && ctype_digit($numero)) {
                        $filaNum = (int) $numero;
                        if ($filaNum >= 1 && $filaNum <= 30) {
                            $esValido = true;
                        }
                    }
                }

                if (!$esValido) {
                    return redirect()->back()->withInput()->withErrors([
                        'pasajeros.' . $indice . '.asiento_codigo' => 'El asiento debe tener formato 1-30 seguido de A-F (ejemplo: 12C).',
                    ]);
                }
            }
        }

        $pasajerosReserva = $reserva->pasajeros()->get()->keyBy('id');
        $pasajerosInput = collect($datos['pasajeros']);

        foreach ($pasajerosInput as $fila) {
            $pasajeroId = (int) $fila['id'];
            if (!$pasajerosReserva->has($pasajeroId)) {
                return redirect()->back()->withInput()->withErrors([
                    'pasajeros' => 'Se detectaron pasajeros que no pertenecen a la reserva.',
                ]);
            }
        }

        $asientosSolicitados = [];
        foreach ($pasajerosInput as $fila) {
            if (isset($fila['asiento_codigo'])) {
                $asientoRaw = $fila['asiento_codigo'];
            } else {
                $asientoRaw = '';
            }
            $asiento = strtoupper(trim((string) $asientoRaw));
            if ($asiento === '') {
                continue;
            }

            if (array_key_exists($asiento, $asientosSolicitados)) {
                return redirect()->back()->withInput()->withErrors([
                    'pasajeros' => 'No se puede repetir el mismo asiento para dos pasajeros de la reserva.',
                ]);
            }

            $asientosSolicitados[$asiento] = true;
        }

        if (count($asientosSolicitados) > 0 && !is_null($reserva->vuelo_id)) {
            $asientosExistentes = Asiento::query()
                ->where('vuelo_id', (int) $reserva->vuelo_id)
                ->whereIn('codigo', array_keys($asientosSolicitados))
                ->pluck('codigo')
                ->map(fn ($codigo) => strtoupper((string) $codigo))
                ->all();

            foreach (array_keys($asientosSolicitados) as $codigo) {
                if (!in_array($codigo, $asientosExistentes, true)) {
                    return redirect()->back()->withInput()->withErrors([
                        'pasajeros' => 'El asiento ' . $codigo . ' no existe para este vuelo.',
                    ]);
                }
            }
        }

        foreach ($pasajerosInput as $fila) {
            $pasajero = ReservaPasajero::query()->find((int) $fila['id']);
            if (!$pasajero || (int) $pasajero->reserva_id !== (int) $reserva->id) {
                continue;
            }

            if (isset($fila['asiento_codigo'])) {
                $asientoCodigoRaw = $fila['asiento_codigo'];
            } else {
                $asientoCodigoRaw = '';
            }
            $asientoCodigo = strtoupper(trim((string) $asientoCodigoRaw));
            if ($asientoCodigo === '') {
                $asientoCodigo = null;
            }

            $pasajero->nombre = trim((string) $fila['nombre']);
            $pasajero->apellidos = trim((string) $fila['apellidos']);
            $pasajero->tipo_documento = (string) $fila['tipo_documento'];
            $pasajero->numero_documento = strtoupper(trim((string) $fila['numero_documento']));
            $pasajero->numero_documento_norm = strtoupper(str_replace(' ', '', (string) $fila['numero_documento']));
            $pasajero->fecha_nacimiento = $fila['fecha_nacimiento'];
            $pasajero->asiento_codigo = $asientoCodigo;
            $pasajero->save();
        }

        $reserva->estado = $datos['estado'];
        $reserva->checkin_estado = $datos['checkin_estado'];

        if ($datos['checkin_estado'] === 'confirmada') {
            if (is_null($reserva->checkin_realizado_en)) {
                $reserva->checkin_realizado_en = now();
            }
            $reserva->tarjetas_emitidas = true;
        } else {
            $reserva->checkin_realizado_en = null;
            $reserva->tarjetas_emitidas = false;
        }

        $reserva->save();

        return redirect()
            ->route('admin.reservas.show', $reserva)
            ->with('exito', 'Reserva actualizada correctamente.');
    }

    private function mascarNombre(string $texto): string
    {
        $texto = trim($texto);
        if ($texto === '') {
            return '-';
        }

        $longitud = mb_strlen($texto, 'UTF-8');
        if ($longitud <= 1) {
            return '*';
        }

        return mb_substr($texto, 0, 1, 'UTF-8') . str_repeat('*', max(1, $longitud - 1));
    }

    private function mascarDocumento(string $documento): string
    {
        $documento = preg_replace('/\s+/', '', trim($documento));
        if (!$documento) {
            return '-';
        }

        $longitud = mb_strlen($documento, 'UTF-8');
        if ($longitud <= 4) {
            return str_repeat('*', $longitud);
        }

        $inicio = mb_substr($documento, 0, 2, 'UTF-8');
        $fin = mb_substr($documento, -2, 2, 'UTF-8');

        return $inicio . str_repeat('*', $longitud - 4) . $fin;
    }

    private function autorizarSuperadmin(): void
    {
        if (!$this->usuarioActualEsSuperadmin()) {
            abort(403, 'Acceso no autorizado.');
        }
    }

    private function usuarioActualEsSuperadmin(): bool
    {
        $usuario = Usuario::query()
            ->with('rol')
            ->find(Auth::id());

        if (!$usuario || !$usuario->rol) {
            return false;
        }

        $rolNombre = mb_strtolower(trim((string) $usuario->rol->nombre), 'UTF-8');

        return $rolNombre === 'superadmin';
    }
}
