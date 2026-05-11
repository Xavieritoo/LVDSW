<?php

namespace App\Http\Controllers;

use App\Models\Asiento;
use App\Models\CheckinEvento;
use App\Models\Reserva;
use App\Models\ReservaPasajero;
use App\Services\MailService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;
use Throwable;

class CheckinController extends Controller
{
    private const PRECIO_ASIENTO_SELECCIONADO = 10.00;
    private const PRECIO_MALETA_EXTRA = 25.00;

    public function show(Reserva $reserva)
    {
        $this->autorizarReservaAuth($reserva);
        $this->verificarCheckinDisponible($reserva);
        return $this->renderCheckin($reserva);
    }

    public function showInvitado(Request $request, Reserva $reserva)
    {
        if (Auth::check()) {
            return redirect()->route('checkin.show', $reserva);
        }

        $this->autorizarReservaInvitado($request, $reserva);
        $this->verificarCheckinDisponible($reserva);
        return $this->renderCheckin($reserva);
    }

    public function bloquearAsiento(Request $request, Reserva $reserva)
    {
        if (Auth::check()) {
            $this->autorizarReservaAuth($reserva);
        } else {
            $this->autorizarReservaInvitado($request, $reserva);
        }

        $request->validate([
            'reserva_pasajero_id' => 'required|integer',
            'asiento_codigo' => 'required|string|max:10',
        ]);

        $pasajeroId = (int) $request->reserva_pasajero_id;
        $asientoCodigo = strtoupper(trim((string) $request->asiento_codigo));

        // Verificar que el pasajero pertenece a esta reserva
        $pasajero = ReservaPasajero::where('id', $pasajeroId)
            ->where('reserva_id', $reserva->id)
            ->firstOrFail();

        if ($this->esBebeEnReserva($pasajero, $reserva)) {
            return response()->json(['error' => 'Los bebés no pueden seleccionar asiento. Viajan en el regazo de uno de sus padres.'], 422);
        }

        $reservaIdsMismoVuelo = $this->reservaIdsMismoVuelo($reserva);

        // Verificar que el asiento no está ya confirmado en el mismo vuelo
        $ocupado = ReservaPasajero::whereIn('reserva_id', $reservaIdsMismoVuelo)
            ->where('asiento_codigo', $asientoCodigo)
            ->where('id', '!=', $pasajeroId)
            ->whereNotNull('asiento_codigo')
            ->exists();

        if ($ocupado) {
            return response()->json(['error' => 'El asiento ya está ocupado por otro pasajero de este vuelo.'], 409);
        }

        // Sin bloqueo temporal: la selección es local y la disponibilidad final se valida al confirmar.

        return response()->json([
            'ok' => true,
        ]);
    }

    public function store(Request $request, Reserva $reserva)
    {
        if (Auth::check()) {
            $this->autorizarReservaAuth($reserva);
        } else {
            $this->autorizarReservaInvitado($request, $reserva);
        }

        if ($reserva->checkin_estado === 'confirmada') {
            if (Auth::check()) {
                return redirect()->route('checkin.tarjetas', $reserva)
                    ->with('info', 'El check-in de esta reserva ya ha sido completado. Aquí puedes descargar tus tarjetas de embarque.');
            } else {
                return redirect()->route('checkin.tarjetas.invitado', $reserva)
                    ->with('info', 'El check-in de esta reserva ya ha sido completado. Aquí puedes descargar tus tarjetas de embarque.');
            }
        }

        $this->verificarCheckinDisponible($reserva);

        $pasajeros = $reserva->pasajeros()->orderBy('id')->get();
        $tiposDocumentoPermitidos = $this->tiposDocumentoPermitidosPorRuta($reserva);

        if ($pasajeros->isEmpty()) {
            return back()->withErrors(['checkin' => 'La reserva no tiene pasajeros registrados.']);
        }

        // Validar datos de cada pasajero
        $reglas = [];
        $mensajes = [];

        foreach ($pasajeros as $p) {
            $k = "pasajero_{$p->id}";
            $reglas["{$k}.nombre"] = 'required|string|max:100';
            $reglas["{$k}.apellidos"] = 'required|string|max:150';
            $reglas["{$k}.fecha_nacimiento"] = 'required|date|before:today';
            $reglas["{$k}.tipo_documento"] = 'required|in:' . implode(',', $tiposDocumentoPermitidos);
            $reglas["{$k}.numero_documento"] = ['required', 'string', 'max:20'];
            $reglas["{$k}.asiento_codigo"] = 'nullable|string|max:10';
            $reglas["{$k}.equipaje_extra"] = 'required|integer|min:0|max:3';

            $mensajes["{$k}.nombre.required"] = "Nombre requerido para pasajero {$p->nombre} {$p->apellidos}.";
            $mensajes["{$k}.tipo_documento.required"] = "Tipo de documento requerido para {$p->nombre} {$p->apellidos}.";
            $mensajes["{$k}.numero_documento.required"] = "Número de documento requerido para {$p->nombre} {$p->apellidos}.";
            $mensajes["{$k}.tipo_documento.in"] = "El documento elegido para {$p->nombre} {$p->apellidos} no es válido para este destino.";
        }

        $validator = Validator::make($request->all(), $reglas, $mensajes);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validar duplicados de documento dentro de la misma reserva
        $documentosVistos = [];
        foreach ($pasajeros as $p) {
            $k = "pasajero_{$p->id}";
            $numDocRaw = $request->input("{$k}.numero_documento");
            $numDocNorm = strtoupper(str_replace(' ', '', (string) $numDocRaw));

            if (in_array($numDocNorm, $documentosVistos, true)) {
                return back()
                    ->withErrors(['checkin' => "El documento {$numDocNorm} está duplicado en esta reserva."])
                    ->withInput();
            }
            $documentosVistos[] = $numDocNorm;
        }

        // Validar asientos duplicados dentro de la misma reserva
        $asientosVistos = [];
        foreach ($pasajeros as $p) {
            $k = "pasajero_{$p->id}";
            $asiento = strtoupper(trim((string) $request->input("{$k}.asiento_codigo")));

            if ($this->esBebeEnReserva($p, $reserva)) {
                if ($asiento !== '') {
                    $nombreBebe = trim($p->nombre . ' ' . $p->apellidos);
                    return back()
                        ->withErrors(['checkin' => "{$nombreBebe}: los bebés no pueden seleccionar asiento. Viajan en el regazo de uno de sus padres."])
                        ->with('checkin_step', 2)
                        ->withInput();
                }
                continue;
            }

            if ($asiento === '') {
                continue;
            }
            if (isset($asientosVistos[$asiento])) {
                $nombreActual = trim($p->nombre . ' ' . $p->apellidos);
                $nombrePrevio = $asientosVistos[$asiento];
                return back()
                    ->withErrors(['checkin' => "Conflicto de asientos: {$nombreActual} y {$nombrePrevio} tienen el mismo asiento {$asiento}. Selecciona uno nuevo para uno de ellos."])
                    ->with('checkin_step', 2)
                    ->withInput();
            }
            $asientosVistos[$asiento] = trim($p->nombre . ' ' . $p->apellidos);
        }

        // Validar formato de documento
        foreach ($pasajeros as $p) {
            $k = "pasajero_{$p->id}";
            $tipo = $request->input("{$k}.tipo_documento");
            $numRaw = $request->input("{$k}.numero_documento");
            $numNorm = strtoupper(str_replace(' ', '', (string) $numRaw));

            $errorDoc = $this->validarFormatoDocumento($tipo, $numNorm);
            if ($errorDoc) {
                return back()
                    ->withErrors(['checkin' => "{$p->nombre} {$p->apellidos}: {$errorDoc}"])
                    ->withInput();
            }
        }

        $reservaIdsMismoVuelo = $this->reservaIdsMismoVuelo($reserva);

        foreach ($pasajeros as $p) {
            $k = "pasajero_{$p->id}";
            $asiento = strtoupper(trim((string) $request->input("{$k}.asiento_codigo")));

            if ($this->esBebeEnReserva($p, $reserva)) {
                if ($asiento !== '') {
                    $nombreBebe = trim($p->nombre . ' ' . $p->apellidos);
                    return back()
                        ->withErrors(['checkin' => "{$nombreBebe}: los bebés no pueden seleccionar asiento. Viajan en el regazo de uno de sus padres."])
                        ->with('checkin_step', 2)
                        ->withInput();
                }
                continue;
            }

            if ($asiento === '') {
                continue;
            }

            $ocupadoEnVuelo = ReservaPasajero::whereIn('reserva_id', $reservaIdsMismoVuelo)
                ->where('reserva_id', '!=', $reserva->id)
                ->where('asiento_codigo', $asiento)
                ->whereNotNull('asiento_codigo')
                ->exists();

            if ($ocupadoEnVuelo) {
                $nombrePasajero = trim($p->nombre . ' ' . $p->apellidos);
                return back()
                    ->withErrors(['checkin' => "{$nombrePasajero}: el asiento {$asiento} ya ha sido escogido por otro pasajero en este vuelo. Selecciona otro asiento."])
                    ->with('checkin_step', 2)
                    ->withInput();
            }
        }

        // Todo correcto → persistir en transacción
        DB::transaction(function () use ($request, $reserva, $pasajeros) {
            $ahora = now();
            $reservaIdsMismoVuelo = $this->reservaIdsMismoVuelo($reserva);

            $asientosUsados = ReservaPasajero::whereIn('reserva_id', $reservaIdsMismoVuelo)
                ->where('reserva_id', '!=', $reserva->id)
                ->whereNotNull('asiento_codigo')
                ->pluck('asiento_codigo')
                ->map(fn ($a) => strtoupper((string) $a))
                ->toArray();

            $lineasAsientos = [];
            $lineasEquipaje = [];
            $asientosPagados = 0;
            $maletasExtra = 0;
            $asientosConfirmados = [];

            foreach ($pasajeros as $p) {
                $k = "pasajero_{$p->id}";
                $numRaw = $request->input("{$k}.numero_documento");
                $numNorm = strtoupper(str_replace(' ', '', (string) $numRaw));
                $asientoSeleccionado = strtoupper(trim((string) $request->input("{$k}.asiento_codigo")));
                $esBebe = $this->esBebeEnReserva($p, $reserva);

                if ($esBebe) {
                    $asientoSeleccionado = '';
                    $asientoFinal = null;
                } elseif ($asientoSeleccionado !== '') {
                    $asientoFinal = $asientoSeleccionado;
                } else {
                    $asientoFinal = $this->asignarAsientoAutomatico($asientosUsados);
                }

                $asientoEsDePago = !$esBebe && $asientoSeleccionado !== '' && !$reserva->asientosIncluidosEnPlan();

                if ($asientoEsDePago) {
                    $asientosPagados++;
                }

                if (!is_null($asientoFinal) && $asientoFinal !== '') {
                    $asientosUsados[] = $asientoFinal;
                    $asientosConfirmados[] = $asientoFinal;
                }

                $maletasPasajero = (int) $request->input("{$k}.equipaje_extra", 0);
                $maletasExtra += $maletasPasajero;

                $p->nombre = trim((string) $request->input("{$k}.nombre"));
                $p->apellidos = trim((string) $request->input("{$k}.apellidos"));
                $p->fecha_nacimiento = $request->input("{$k}.fecha_nacimiento");
                $p->tipo_documento = $request->input("{$k}.tipo_documento");
                $p->numero_documento = strtoupper((string) $numRaw);
                $p->numero_documento_norm = $numNorm;
                $p->asiento_codigo = $asientoFinal;
                if (is_null($asientoFinal)) {
                    $p->asiento_asignado_en = null;
                } else {
                    $p->asiento_asignado_en = $ahora;
                }
                $p->checkin_confirmado_en = $ahora;
                $p->save();

                $nombrePasajero = trim($p->nombre . ' ' . $p->apellidos);
                if ($esBebe) {
                    $lineasAsientos[] = $nombrePasajero . ': regazo de uno de sus padres';
                } elseif ($asientoEsDePago) {
                    $detalleAsiento = ' (seleccionado)';
                } else {
                    $detalleAsiento = ' (auto)';
                }
                if (!$esBebe) {
                    $lineasAsientos[] = $nombrePasajero . ': ' . $asientoFinal . $detalleAsiento;
                }

                if ($maletasPasajero > 0) {
                    $lineasEquipaje[] = $nombrePasajero . ': +' . $maletasPasajero . ' maleta(s)';
                }

                if ($esBebe) {
                    $origenAsiento = 'sin_asiento_bebe';
                } elseif ($asientoEsDePago) {
                    $origenAsiento = 'seleccionado';
                } else {
                    $origenAsiento = 'asignacion_automatica';
                }

                $this->registrarEvento($reserva->id, $p->id, 'checkin_confirmado', [
                    'asiento' => $asientoFinal,
                    'asiento_origen' => $origenAsiento,
                    'maletas_extra' => $maletasPasajero,
                    'documento_norm' => $numNorm,
                ]);
            }

            // Sincroniza ocupacion fisica de asientos del vuelo para que la tabla asientos_vuelo refleje el check-in confirmado.
            if (!is_null($reserva->vuelo_id)) {
                if (!empty($asientosConfirmados)) {
                    Asiento::query()
                        ->where('vuelo_id', (int) $reserva->vuelo_id)
                        ->whereIn('codigo', array_values(array_unique($asientosConfirmados)))
                        ->update(['ocupado' => true]);
                }

                // Pasajeros confirmados = todos los check-in confirmados (incluye bebés sin asiento)
                $pasajerosConfirmadosCount = ReservaPasajero::query()
                    ->join('reservas', 'reservas.id', '=', 'reserva_pasajeros.reserva_id')
                    ->where('reservas.vuelo_id', (int) $reserva->vuelo_id)
                    ->whereNotIn('reservas.estado', ['cancelada_usuario', 'cancelada_aerolinea'])
                    ->whereNotNull('reserva_pasajeros.checkin_confirmado_en')
                    ->count('reserva_pasajeros.id');

                // Asientos disponibles = total físico - asientos realmente ocupados (excluye bebés)
                $asientosConfirmadosCount = ReservaPasajero::query()
                    ->join('reservas', 'reservas.id', '=', 'reserva_pasajeros.reserva_id')
                    ->where('reservas.vuelo_id', (int) $reserva->vuelo_id)
                    ->whereNotIn('reservas.estado', ['cancelada_usuario', 'cancelada_aerolinea'])
                    ->whereNotNull('reserva_pasajeros.checkin_confirmado_en')
                    ->whereNotNull('reserva_pasajeros.asiento_codigo')
                    ->count('reserva_pasajeros.id');

                $totalAsientos = Asiento::where('vuelo_id', (int) $reserva->vuelo_id)->count();
                $asientosDisponibles = max(0, $totalAsientos - $asientosConfirmadosCount);
                DB::table('vuelos')
                    ->where('id', (int) $reserva->vuelo_id)
                    ->update([
                        'pasajeros_confirmados' => $pasajerosConfirmadosCount,
                        'asientos_disponibles' => $asientosDisponibles
                    ]);
            }

            $importeAsientos = $asientosPagados * self::PRECIO_ASIENTO_SELECCIONADO;
            $importeMaletas = $maletasExtra * self::PRECIO_MALETA_EXTRA;

            if ($reserva->asientosIncluidosEnPlan()) {
                $resumenAsientos = 'Asientos incluidos en tu plan: ' . count($asientosConfirmados) . ' asignado(s), sin coste adicional';
            } else {
                $resumenAsientos = 'Asientos pago: ' . $asientosPagados . ' x ' . number_format(self::PRECIO_ASIENTO_SELECCIONADO, 2) . ' EUR = ' . number_format($importeAsientos, 2) . ' EUR';
            }
            if (!empty($lineasAsientos)) {
                $resumenAsientos .= ' | ' . implode(' | ', $lineasAsientos);
            }

            $resumenEquipaje = 'Maletas extra: ' . $maletasExtra . ' x ' . number_format(self::PRECIO_MALETA_EXTRA, 2) . ' EUR = ' . number_format($importeMaletas, 2) . ' EUR';
            if (!empty($lineasEquipaje)) {
                $resumenEquipaje .= ' | ' . implode(' | ', $lineasEquipaje);
            }

            $reserva->checkin_estado = 'confirmada';
            $reserva->tarjetas_emitidas = true;
            $reserva->checkin_realizado_en = $ahora;
            $reserva->asientos_resumen = substr($resumenAsientos, 0, 255);
            $reserva->equipaje_resumen = substr($resumenEquipaje, 0, 255);
            $reserva->save();

            $this->registrarEvento($reserva->id, null, 'tarjetas_emitidas', [
                'pasajeros' => $pasajeros->count(),
                'asientos_pago' => $asientosPagados,
                'maletas_extra' => $maletasExtra,
                'importe_total_extras' => round($importeAsientos + $importeMaletas, 2),
            ]);
        });

        $reserva->refresh();
        $pasajerosFrescos = $reserva->pasajeros()->orderBy('id')->get();

        // Generar PDF y enviar correo (1 intento)
        $this->enviarCorreoTarjetas($reserva, $pasajerosFrescos);

        if (Auth::check()) {
            return redirect()
                ->route('mis-reservas.index', ['tab' => 'proximas'])
                ->with('exito', 'Check-in completado. Tus tarjetas de embarque están disponibles en Mis Reservas.');
        }

        return redirect()
            ->route('mis-viajes.index', [
                'localizador' => $reserva->localizador,
                'email_contacto' => $reserva->email_contacto,
            ])
            ->with('exito', 'Check-in completado. Tus tarjetas de embarque están disponibles.');
    }

    public function descargarTarjetas(Reserva $reserva)
    {
        if (Auth::check()) {
            $this->autorizarReservaAuth($reserva);
        }

        if (!$reserva->tarjetas_emitidas) {
            return back()->withErrors(['boarding' => 'Las tarjetas de embarque aún no están disponibles para esta reserva.']);
        }

        $pasajeros = $reserva->pasajeros()->orderBy('id')->get();

        $pdf = Pdf::loadView('boarding-passes', compact('reserva', 'pasajeros'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('tarjetas-embarque-' . $reserva->localizador . '.pdf');
    }

    public function descargarTarjetasInvitado(Request $request, Reserva $reserva)
    {
        if (Auth::check()) {
            return redirect()->route('checkin.tarjetas', $reserva);
        }

        $this->autorizarReservaInvitado($request, $reserva);

        if (!$reserva->tarjetas_emitidas) {
            return back()->withErrors(['boarding' => 'Las tarjetas de embarque aún no están disponibles para esta reserva.']);
        }

        $pasajeros = $reserva->pasajeros()->orderBy('id')->get();

        $pdf = Pdf::loadView('boarding-passes', compact('reserva', 'pasajeros'))
            ->setPaper('A4', 'portrait');

        return $pdf->download('tarjetas-embarque-' . $reserva->localizador . '.pdf');
    }

    private function renderCheckin(Reserva $reserva): \Illuminate\View\View
    {
        $pasajeros = $reserva->pasajeros()->orderBy('id')->get();
        $asientosMap = $this->mapaAsientosDisponibles($reserva);
        $tiposDocumentoPermitidos = $this->tiposDocumentoPermitidosPorRuta($reserva);
        $documentacionMensaje = $this->mensajeDocumentacionRuta($reserva, $tiposDocumentoPermitidos);
        $planNombre = $reserva->nombrePlanTarifa();

        return view('checkin.index', compact(
            'reserva',
            'pasajeros',
            'asientosMap',
            'tiposDocumentoPermitidos',
            'documentacionMensaje',
            'planNombre'
        ));
    }

    private function autorizarReservaAuth(Reserva $reserva): void
    {
        if ($reserva->user_id !== Auth::id()) {
            abort(403);
        }
    }

    private function autorizarReservaInvitado(Request $request, Reserva $reserva): void
    {
        $localizador = strtoupper(trim((string) $request->input('localizador', '')));
        $email = strtolower(trim((string) $request->input('email_contacto', '')));

        if (
            strtoupper($reserva->localizador) !== $localizador ||
            strtolower($reserva->email_contacto) !== $email
        ) {
            abort(403);
        }
    }

    private function verificarCheckinDisponible(Reserva $reserva): void
    {
        if ($reserva->checkin_estado === 'confirmada') {
            abort(422, 'El check-in de esta reserva ya ha sido completado.');
        }

        if (!$reserva->checkinDisponibleAhora()) {
            abort(422, 'El check-in para esta reserva aún no está disponible.');
        }

        if (in_array($reserva->estado, ['cancelada_usuario', 'cancelada_aerolinea', 'completada'], true)) {
            abort(422, 'No es posible realizar el check-in en esta reserva.');
        }
    }

    private function mapaAsientosDisponibles(Reserva $reserva): array
    {
        $vueloId = $reserva->vuelo_id;
        if ($vueloId) {
            $this->generarAsientosSiNoExisten((int) $vueloId);
        }

        $reservaIdsMismoVuelo = $this->reservaIdsMismoVuelo($reserva);

        $asientosOcupados = ReservaPasajero::whereIn('reserva_id', $reservaIdsMismoVuelo)
            ->whereNotNull('asiento_codigo')
            ->pluck('asiento_codigo')
            ->map(fn ($a) => strtoupper($a))
            ->toArray();

        // Leer asientos desde asientos_vuelo (con tipo)
        if ($vueloId) {
            $asientosDb = Asiento::where('vuelo_id', (int) $vueloId)
                ->get(['codigo', 'tipo', 'ocupado']);

            $mapa = [];
            foreach ($asientosDb as $a) {
                $codigo = strtoupper($a->codigo);
                if ($a->ocupado || in_array($codigo, $asientosOcupados, true)) {
                    $estado = 'ocupado';
                } else {
                    $estado = 'libre';
                }
                $mapa[$codigo] = ['estado' => $estado, 'tipo' => $a->tipo];
            }

            return $mapa;
        }

        // Fallback si no hay vuelo_id: grid hardcodeado sin tipos
        $mapa = [];
        foreach (range(1, 30) as $fila) {
            foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $col) {
                $codigo = $fila . $col;
                if (in_array($codigo, $asientosOcupados, true)) {
                    $estado = 'ocupado';
                } else {
                    $estado = 'libre';
                }
                $mapa[$codigo] = ['estado' => $estado, 'tipo' => 'estandar'];
            }
        }

        return $mapa;
    }

    private function generarAsientosSiNoExisten(int $vueloId): void
    {
        $existe = Asiento::where('vuelo_id', $vueloId)->exists();
        if ($existe) {
            return;
        }

        $filas = range(1, 30);
        $columnas = ['A', 'B', 'C', 'D', 'E', 'F'];
        $ahora = now();
        $registros = [];

        foreach ($filas as $fila) {
            if ($fila <= 3) {
                $tipo = 'planit_plus';
            } elseif ($fila <= 8) {
                $tipo = 'planit_one';
            } elseif ($fila >= 29) {
                $tipo = 'planit_space';
            } else {
                $tipo = 'estandar';
            }

            foreach ($columnas as $col) {
                $registros[] = [
                    'vuelo_id' => $vueloId,
                    'codigo' => $fila . $col,
                    'tipo' => $tipo,
                    'ocupado' => false,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            }
        }

        Asiento::insert($registros);
    }

    private function reservaIdsMismoVuelo(Reserva $reserva): array
    {
        $salida = Carbon::parse($reserva->fecha_salida);
        $inicioMinuto = $salida->copy()->startOfMinute();
        $finMinuto = $salida->copy()->endOfMinute();

        $ids = Reserva::query()
            ->where('origen', $reserva->origen)
            ->where('destino', $reserva->destino)
            ->whereBetween('fecha_salida', [$inicioMinuto, $finMinuto])
            ->whereNotIn('estado', ['cancelada_usuario', 'cancelada_aerolinea'])
            ->pluck('id')
            ->all();

        if (!empty($ids)) {
            return $ids;
        }

        return [$reserva->id];
    }

    private function validarFormatoDocumento(string $tipo, string $numNorm): ?string
    {
        if ($tipo === 'DNI') {
            if (!$this->esDniValido($numNorm)) {
                return 'El DNI debe tener 8 dígitos seguidos de 1 letra mayúscula (ejemplo: 12345678Z).';
            }
        } elseif ($tipo === 'PASAPORTE') {
            if (!$this->esPasaporteValido($numNorm)) {
                return 'El pasaporte debe tener entre 6 y 15 caracteres alfanuméricos en mayúsculas.';
            }
        }
        return null;
    }

    private function esDniValido(string $dni): bool
    {
        if (strlen($dni) !== 9) {
            return false;
        }

        $numero = substr($dni, 0, 8);
        $letra = substr($dni, 8, 1);

        if (!ctype_digit($numero)) {
            return false;
        }

        return ctype_alpha($letra) && strtoupper($letra) === $letra;
    }

    private function esPasaporteValido(string $pasaporte): bool
    {
        $longitud = strlen($pasaporte);
        if ($longitud < 6 || $longitud > 15) {
            return false;
        }

        return ctype_alnum($pasaporte) && strtoupper($pasaporte) === $pasaporte;
    }

    private function tiposDocumentoPermitidosPorRuta(Reserva $reserva): array
    {
        if ($this->esRutaSchengen($reserva)) {
            return ['DNI', 'PASAPORTE'];
        }

        return ['PASAPORTE'];
    }

    private function mensajeDocumentacionRuta(Reserva $reserva, array $tipos): string
    {
        if ($this->esRutaSchengen($reserva)) {
            return 'Ruta Schengen: se admite DNI o pasaporte en vigor para todos los pasajeros.';
        }

        return 'Ruta fuera de Schengen: para este trayecto se requiere pasaporte en vigor para todos los pasajeros.';
    }

    private function esRutaSchengen(Reserva $reserva): bool
    {
        return $this->esCiudadSchengen((string) $reserva->origen)
            && $this->esCiudadSchengen((string) $reserva->destino);
    }

    private function esCiudadSchengen(string $lugar): bool
    {
        $ciudades = [
            // España
            'MADRID', 'BARCELONA', 'VALENCIA', 'SEVILLA', 'MALAGA', 'BILBAO', 'ALICANTE',
            'PALMA DE MALLORCA', 'PALMA', 'LAS PALMAS', 'GRAN CANARIA', 'TENERIFE',
            'TENERIFE NORTE', 'TENERIFE SUR', 'SANTIAGO DE COMPOSTELA', 'VIGO',
            'A CORUNA', 'LA CORUNA', 'OVIEDO', 'GIJON', 'SANTANDER', 'PAMPLONA',
            'ZARAGOZA', 'IBIZA', 'MENORCA', 'FUERTEVENTURA', 'LANZAROTE', 'LA PALMA',
            'GRANADA', 'MURCIA', 'VALLADOLID', 'SALAMANCA', 'CORDOBA', 'JEREZ',
            'REUS', 'ALMERIA', 'SAN SEBASTIAN',
            // Francia
            'PARIS', 'PARIS - ORLY', 'LYON', 'MARSELLA', 'NIZA', 'TOULOUSE',
            'BURDEOS', 'NANTES', 'ESTRASBURGO', 'BASTIA - CORSEGA',
            // Italia
            'ROMA', 'ROMA - FIUMICINO', 'MILAN', 'MILAN - LINATE', 'NAPOLES',
            'VENECIA', 'FLORENCIA', 'BOLOGNA', 'PISA', 'CATANIA', 'PALERMO', 'GENOVA',
            // Portugal
            'LISBOA', 'OPORTO', 'FARO', 'MADEIRA',
            // Alemania
            'BERLIN', 'BERLIN-BRANDENBURGO', 'MUNICH', 'FRANKFURT', 'HAMBURGO',
            'DUSSELDORF', 'STUTTGART', 'NUREMBERG', 'HANOVER',
            // Otros Schengen
            'AMSTERDAM', 'BRUSELAS', 'VIENA', 'PRAGA', 'VARSOVIA', 'BUDAPEST',
            'ATENAS', 'CRETA', 'SANTORINI', 'BUCAREST', 'BUCAREST - HENRI COANDA',
            'CLUJ-NAPOCA', 'TIMISOARA', 'IASI', 'SOFIA', 'ZURICH', 'BASILEA', 'GINEBRA',
            'COPENHAGUE', 'BILLUND', 'ESTOCOLMO', 'GOTEMBURGO',
            'OSLO', 'BERGEN', 'STAVANGER', 'TROMSO',
            'DUBROVNIK', 'SPLIT', 'MALTA', 'REIKIAVIK',
        ];

        return in_array($this->normalizarTexto($lugar), $ciudades, true);
    }

    private function normalizarTexto(string $texto): string
    {
        $upper = mb_strtoupper(trim($texto), 'UTF-8');

        return strtr($upper, [
            'Á' => 'A',
            'É' => 'E',
            'Í' => 'I',
            'Ó' => 'O',
            'Ú' => 'U',
            'Ü' => 'U',
            'Ñ' => 'N',
        ]);
    }

    private function esBebeEnReserva(ReservaPasajero $pasajero, Reserva $reserva): bool
    {
        if (is_null($pasajero->fecha_nacimiento) || is_null($reserva->fecha_salida)) {
            return false;
        }

        $fechaNacimiento = Carbon::parse($pasajero->fecha_nacimiento);
        $fechaSalida = Carbon::parse($reserva->fecha_salida);

        return $fechaNacimiento->diffInYears($fechaSalida) < 2;
    }

    private function asignarAsientoAutomatico(array $asientosUsados): string
    {
        $ocupados = array_values(array_unique(array_map(fn ($a) => strtoupper((string) $a), $asientosUsados)));

        // Primero asientos estándar (filas 9-28), luego space (29-30), luego premium (1-8)
        $orden = array_merge(range(9, 28), [29, 30], range(1, 8));

        foreach ($orden as $fila) {
            foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $col) {
                $codigo = $fila . $col;
                if (!in_array($codigo, $ocupados, true)) {
                    return $codigo;
                }
            }
        }

        throw new RuntimeException('No quedan asientos disponibles para asignación automática.');
    }

    private function registrarEvento(
        int $reservaId,
        ?int $pasajeroId,
        string $tipo,
        array $meta = []
    ): void {
        if (Auth::check()) {
            $actorTipo = 'usuario';
        } else {
            $actorTipo = 'invitado';
        }

        if (empty($meta)) {
            $metaEvento = null;
        } else {
            $metaEvento = $meta;
        }

        CheckinEvento::create([
            'reserva_id' => $reservaId,
            'reserva_pasajero_id' => $pasajeroId,
            'tipo' => $tipo,
            'actor_tipo' => $actorTipo,
            'actor_user_id' => Auth::id(),
            'descripcion' => $tipo,
            'meta' => $metaEvento,
        ]);
    }

    private function enviarCorreoTarjetas(Reserva $reserva, $pasajeros): void
    {
        try {
            $pdf = Pdf::loadView('boarding-passes', compact('reserva', 'pasajeros'))
                ->setPaper('A4', 'portrait');

            $pdfContent = $pdf->output();

            $html = view('emails.checkin-completado', compact('reserva', 'pasajeros'))->render();

            $mailService = new MailService();

            // PHPMailer directo para adjuntar PDF
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = (string) config('mail.mailers.smtp.host', 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = (string) config('mail.mailers.smtp.username', '');
            $mail->Password = (string) config('mail.mailers.smtp.password', '');
            $mail->SMTPSecure = (string) config('mail.mailers.smtp.scheme', 'tls');
            $mail->Port = (int) config('mail.mailers.smtp.port', 587);
            $mail->CharSet = 'UTF-8';
            $mail->setFrom((string) config('mail.from.address', 'planit@planit.com'), 'Planit');
            $mail->addAddress($reserva->email_contacto);
            $mail->Subject = 'Check-in completado - Tarjetas de embarque (' . $reserva->localizador . ')';
            $mail->isHTML(true);
            $mail->Body = $html;
            $mail->addStringAttachment($pdfContent, 'tarjetas-embarque-' . $reserva->localizador . '.pdf', 'base64', 'application/pdf');
            $mail->send();

            $reserva->checkin_correo_estado = 'enviado';
            $reserva->checkin_correo_intentado_en = now();
            $reserva->checkin_correo_error = null;
            $reserva->save();

            $this->registrarEvento($reserva->id, null, 'correo_checkin_exito', [
                'email' => $reserva->email_contacto,
            ]);
        } catch (Throwable $e) {
            $reserva->checkin_correo_estado = 'fallido';
            $reserva->checkin_correo_intentado_en = now();
            $reserva->checkin_correo_error = substr($e->getMessage(), 0, 255);
            $reserva->save();

            $this->registrarEvento($reserva->id, null, 'correo_checkin_fallo', [
                'error' => substr($e->getMessage(), 0, 200),
            ]);
        }
    }

}
