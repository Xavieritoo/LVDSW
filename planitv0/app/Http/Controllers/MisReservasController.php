<?php

namespace App\Http\Controllers;

use App\Models\Asiento;
use App\Models\Cancelacion;
use App\Models\Reembolso;
use App\Models\Reserva;
use App\Models\ReservaEstadoHistorial;
use App\Models\ReservaPasajero;
use App\Services\MailService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MisReservasController extends Controller
{
    private const MINUTOS_EXPIRACION_TOKEN_ENLACE = 15;
    private const SEGUNDOS_ENFRIAMIENTO_REENVIO = 60;
    private const MAX_INTENTOS_ENLACE = 5;
    private const MINUTOS_BLOQUEO_ENLACE = 5;

    // Listado de reservas del usuario autenticado.
    public function index(Request $request)
    {
        $idUsuario = Auth::id();
        $ahora = Carbon::now();

        $this->sincronizarReservasCompletadas($idUsuario, $ahora);

        $filtros = [
            'localizador' => trim((string) $request->query('localizador', '')),
            'origen' => trim((string) $request->query('origen', '')),
            'destino' => trim((string) $request->query('destino', '')),
            'fecha' => trim((string) $request->query('fecha', '')),
            'reembolso_estado' => trim((string) $request->query('reembolso_estado', '')),
        ];

        $base = Reserva::query()
            ->where('user_id', $idUsuario)
            ->with([
                'pasajeros:id,reserva_id,nombre,apellidos,fecha_nacimiento',
                'cancelacion:id,reserva_id,tipo,motivo,created_at',
                'reembolso:id,reserva_id,estado,cantidad,created_at',
                'historialEstados:id,reserva_id,estado_anterior,estado_nuevo,motivo,changed_at',
            ]);

        $this->aplicarFiltrosComunes($base, $filtros);

        $proximas = (clone $base)
            ->whereNotIn('estado', ['cancelada_usuario', 'cancelada_aerolinea', 'completada'])
            ->where('fecha_llegada', '>=', $ahora)
            ->orderBy('fecha_salida')
            ->get();

        $voladas = (clone $base)
            ->whereNotIn('estado', ['cancelada_usuario', 'cancelada_aerolinea'])
            ->where(function ($q) use ($ahora) {
                $q->where('estado', 'completada')
                    ->orWhere('fecha_llegada', '<', $ahora);
            })
            ->orderByDesc('fecha_salida')
            ->get();

        $canceladas = (clone $base)
            ->whereIn('estado', ['cancelada_usuario', 'cancelada_aerolinea'])
            ->when($filtros['reembolso_estado'] !== '', function ($q) use ($filtros) {
                if ($filtros['reembolso_estado'] === 'no_aplicable') {
                    $q->where(function ($subQuery) {
                        $subQuery->whereDoesntHave('reembolso')
                            ->orWhereHas('reembolso', function ($sub) {
                                $sub->where('estado', 'no_aplicable');
                            });
                    });
                    return;
                }

                $q->whereHas('reembolso', function ($sub) use ($filtros) {
                    $sub->where('estado', $filtros['reembolso_estado']);
                });
            })
            ->orderByDesc('updated_at')
            ->get();

        return view('mis-reservas', compact('proximas', 'voladas', 'canceladas', 'filtros'));
    }

    // Buscador de reservas para invitados.
    public function misViajes(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('mis-reservas.index');
        }

        $filtros = [
            'localizador' => strtoupper(trim((string) $request->query('localizador', ''))),
            'email_contacto' => strtolower(trim((string) $request->query('email_contacto', ''))),
        ];

        $reserva = null;
        $tipo = null;

        // Control de intentos y bloqueo por sesion.
        $claveIntento = $filtros['localizador'] . '|' . $filtros['email_contacto'];
        $intentos = $request->session()->get('mis_viajes_intentos', []);
        if (array_key_exists($claveIntento, $intentos)) {
            $registro = $intentos[$claveIntento];
        } else {
            $registro = ['intentos' => 0, 'bloqueado_hasta' => null];
        }
        $ahora = now();

        // Si esta bloqueado, comprobar si ya paso el tiempo.
        if ($registro['bloqueado_hasta'] && $ahora->lt($registro['bloqueado_hasta'])) {
            $minRest = $ahora->diffInMinutes($registro['bloqueado_hasta']) + 1;
            return back()->withErrors([
                'mis_viajes' => "Has superado el número de intentos. Intenta de nuevo en $minRest minuto(s)."
            ])->withInput();
        } elseif ($registro['bloqueado_hasta'] && $ahora->gte($registro['bloqueado_hasta'])) {
            // Si ya paso el bloqueo, reiniciar.
            $registro = ['intentos' => 0, 'bloqueado_hasta' => null];
        }

        if ($filtros['localizador'] !== '' || $filtros['email_contacto'] !== '') {
            $validador = Validator::make($filtros, [
                'localizador' => 'required|string|max:20',
                'email_contacto' => 'required|email|max:150',
            ], [
                'localizador.required' => 'El localizador es obligatorio.',
                'email_contacto.required' => 'El email de contacto es obligatorio.',
                'email_contacto.email' => 'El email de contacto no es válido.',
            ]);

            if ($validador->fails()) {
                return back()->withErrors($validador)->withInput();
            }

            $reserva = Reserva::query()
                ->with([
                    'pasajeros:id,reserva_id,nombre,apellidos,fecha_nacimiento',
                    'cancelacion:id,reserva_id,tipo,motivo,created_at',
                    'reembolso:id,reserva_id,estado,cantidad,created_at',
                    'historialEstados:id,reserva_id,estado_anterior,estado_nuevo,motivo,changed_at',
                ])
                ->whereRaw('UPPER(localizador) = ?', [$filtros['localizador']])
                ->whereRaw('LOWER(email_contacto) = ?', [$filtros['email_contacto']])
                ->first();

            if (!$reserva) {
                // Sumar intento
                $registro['intentos']++;
                if ($registro['intentos'] >= 3) {
                    $registro['bloqueado_hasta'] = $ahora->copy()->addMinutes(5);
                }
                $intentos[$claveIntento] = $registro;
                $request->session()->put('mis_viajes_intentos', $intentos);
                if ($registro['bloqueado_hasta']) {
                    $msg = 'Has superado el número de intentos. Intenta de nuevo en 5 minutos.';
                } else {
                    $msg = 'No encontramos ninguna reserva con ese localizador y email de contacto.';
                }
                return back()->withErrors([
                    'mis_viajes' => $msg,
                ])->withInput();
            } else {
                // Si acierta, reiniciar intentos.
                if ($registro['intentos'] > 0 || $registro['bloqueado_hasta']) {
                    $registro = ['intentos' => 0, 'bloqueado_hasta' => null];
                    $intentos[$claveIntento] = $registro;
                    $request->session()->put('mis_viajes_intentos', $intentos);
                }
            }

            // Ya no bloqueamos si esta vinculada a una cuenta.

            if (in_array($reserva->estado, ['cancelada_usuario', 'cancelada_aerolinea'], true)) {
                $tipo = 'canceladas';
            } elseif ($reserva->estado === 'completada' || Carbon::parse($reserva->fecha_llegada)->lt($ahora)) {
                $tipo = 'voladas';
            } else {
                $tipo = 'proximas';
            }
        }

        return view('mis-viajes', compact('filtros', 'reserva', 'tipo'));
    }

    // Enviar token por email para enlazar una reserva.
    public function solicitarEnlace(Request $request)
    {
        $request->validate([
            'localizador_enlace' => 'required|string|max:20',
        ]);

        $localizador = strtoupper(trim((string) $request->input('localizador_enlace')));
        $reserva = $this->buscarReservaPorLocalizador($localizador);

        if (!$reserva) {
            return back()->withErrors([
                'enlace' => 'No existe ninguna reserva con ese localizador.',
            ])->withInput()->with('enlace_modal', 'solicitar');
        }

        $estadoError = $this->validarReservaEnlazable($reserva);
        if ($estadoError) {
            return back()->withErrors(['enlace' => $estadoError])->withInput()->with('enlace_modal', 'solicitar');
        }

        $emailContacto = '';
        if ($reserva->email_contacto) {
            $emailContacto = trim((string) $reserva->email_contacto);
        }
        if ($emailContacto === '' || !filter_var($emailContacto, FILTER_VALIDATE_EMAIL)) {
            return back()->withErrors([
                'enlace' => 'La reserva no tiene un email de contacto valido para verificar la vinculacion.',
            ])->withInput()->with('enlace_modal', 'solicitar');
        }

        $record = DB::table('verificaciones_enlace_reserva')
            ->where('reserva_id', $reserva->id)
            ->first();

        if ($record && $record->bloqueado_hasta && now()->lt($record->bloqueado_hasta)) {
            return back()->withErrors([
                'enlace' => 'Has superado los intentos permitidos. El enlace de reservas esta bloqueado 5 minutos.',
            ])->withInput()->with('enlace_modal', 'solicitar');
        }

        if ($record && $record->ultimo_envio_en) {
            $secondsLeft = $this->cooldownSecondsLeft($record->ultimo_envio_en, self::SEGUNDOS_ENFRIAMIENTO_REENVIO);
            if ($secondsLeft > 0) {
                return back()->withErrors([
                    'enlace' => 'Espera ' . $secondsLeft . ' segundos antes de solicitar un nuevo token.',
                ])->withInput()->with('enlace_modal', 'solicitar');
            }
        }

        $token = (string) random_int(100000, 999999);

        $createdAtRegistro = now();
        if ($record) {
            $createdAtRegistro = $record->created_at;
        }

        DB::table('verificaciones_enlace_reserva')->updateOrInsert(
            ['reserva_id' => $reserva->id],
            [
                'user_id' => Auth::id(),
                'email_contacto' => $emailContacto,
                'hash_token' => hash('sha256', $token),
                'expira_en' => now()->addMinutes(self::MINUTOS_EXPIRACION_TOKEN_ENLACE),
                'usado' => false,
                'intentos' => 0,
                'bloqueado_hasta' => null,
                'ultimo_envio_en' => now(),
                'updated_at' => now(),
                'created_at' => $createdAtRegistro,
            ]
        );

        try {
            $mail = new MailService();
            $html = "
            <p>Hola,</p>
            <p>Has solicitado enlazar una reserva a tu cuenta en Planit.</p>
            <p><strong>Localizador:</strong> {$localizador}</p>
            <p>Tu token de verificacion es:</p>
            <h2 style='color: #5170ff;'>{$token}</h2>
            <p>Este token caduca en " . self::MINUTOS_EXPIRACION_TOKEN_ENLACE . " minutos y es de un solo uso.</p>
            ";

            $mail->enviarEmail($emailContacto, 'Token para enlazar reserva', $html);
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors([
                'enlace' => 'No se pudo enviar el token por correo. Revisa la configuracion SMTP e intentalo de nuevo.',
            ])->withInput()->with('enlace_modal', 'solicitar');
        }

        return back()
            ->with('enlace_exito', 'Te hemos enviado un token al email de contacto de la reserva. Introducelo para completar el enlace.')
            ->with('enlace_modal', 'token')
            ->with('enlace_localizador', $localizador);
    }

    // Reenviar token de enlace respetando cooldown.
    public function reenviarEnlace(Request $request)
    {
        $request->validate([
            'localizador_enlace' => 'required|string|max:20',
        ]);

        $localizador = strtoupper(trim((string) $request->input('localizador_enlace')));
        $reserva = $this->buscarReservaPorLocalizador($localizador);
        if (!$reserva) {
            return back()->withErrors([
                'enlace' => 'No existe ninguna reserva con ese localizador.',
            ])->withInput()->with('enlace_modal', 'token');
        }

        $estadoError = $this->validarReservaEnlazable($reserva);
        if ($estadoError) {
            return back()->withErrors(['enlace' => $estadoError])->withInput()->with('enlace_modal', 'token');
        }

        $record = DB::table('verificaciones_enlace_reserva')
            ->where('reserva_id', $reserva->id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$record) {
            return back()->withErrors([
                'enlace' => 'Primero debes solicitar el token de enlace para esta reserva.',
            ])->withInput()->with('enlace_modal', 'token');
        }

        if ($record->bloqueado_hasta && now()->lt($record->bloqueado_hasta)) {
            return back()->withErrors([
                'enlace' => 'Has superado los intentos permitidos. El enlace de reservas esta bloqueado 5 minutos.',
            ])->withInput()->with('enlace_modal', 'token');
        }

        if ($record->ultimo_envio_en) {
            $secondsLeft = $this->cooldownSecondsLeft($record->ultimo_envio_en, self::SEGUNDOS_ENFRIAMIENTO_REENVIO);
            if ($secondsLeft > 0) {
                return back()->withErrors([
                    'enlace' => 'Espera ' . $secondsLeft . ' segundos antes de reenviar el token.',
                ])->withInput()->with('enlace_modal', 'token');
            }
        }

        $emailContacto = '';
        if ($record->email_contacto) {
            $emailContacto = trim((string) $record->email_contacto);
        } elseif ($reserva->email_contacto) {
            $emailContacto = trim((string) $reserva->email_contacto);
        }
        if ($emailContacto === '' || !filter_var($emailContacto, FILTER_VALIDATE_EMAIL)) {
            return back()->withErrors([
                'enlace' => 'La reserva no tiene un email de contacto valido para verificar la vinculacion.',
            ])->withInput()->with('enlace_modal', 'token');
        }

        $token = (string) random_int(100000, 999999);

        DB::table('verificaciones_enlace_reserva')
            ->where('reserva_id', $reserva->id)
            ->update([
                'hash_token' => hash('sha256', $token),
                'expira_en' => now()->addMinutes(self::MINUTOS_EXPIRACION_TOKEN_ENLACE),
                'usado' => false,
                'intentos' => 0,
                'bloqueado_hasta' => null,
                'ultimo_envio_en' => now(),
                'email_contacto' => $emailContacto,
                'updated_at' => now(),
            ]);

        try {
            $mail = new \App\Services\MailService();
            $html = "
            <p>Hola,</p>
            <p>Has solicitado reenviar el token para enlazar una reserva en Planit.</p>
            <p><strong>Localizador:</strong> {$localizador}</p>
            <p>Tu nuevo token es:</p>
            <h2 style='color: #5170ff;'>{$token}</h2>
            <p>Este token caduca en " . self::MINUTOS_EXPIRACION_TOKEN_ENLACE . " minutos y es de un solo uso.</p>
            ";

            $mail->enviarEmail($emailContacto, 'Reenvio de token para enlazar reserva', $html);
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors([
                'enlace' => 'No se pudo reenviar el token por correo. Revisa la configuracion SMTP e intentalo de nuevo.',
            ])->withInput()->with('enlace_modal', 'token');
        }

        return back()
            ->with('enlace_exito', 'Te hemos reenviado un nuevo token al email de contacto de la reserva.')
            ->with('enlace_modal', 'token')
            ->with('enlace_localizador', $localizador);
    }

    // Verificar token y completar enlace de la reserva.
    public function verificarEnlace(Request $request)
    {
        $request->validate([
            'localizador_enlace' => 'required|string|max:20',
            'token_enlace' => 'required|string|max:20',
        ]);

        $localizador = strtoupper(trim((string) $request->input('localizador_enlace')));
        $token = trim((string) $request->input('token_enlace'));

        $reserva = $this->buscarReservaPorLocalizador($localizador);
        if (!$reserva) {
            return back()->withErrors([
                'enlace' => 'No existe ninguna reserva con ese localizador.',
            ])->withInput()->with('enlace_modal', 'token');
        }

        $estadoError = $this->validarReservaEnlazable($reserva);
        if ($estadoError) {
            return back()->withErrors(['enlace' => $estadoError])->withInput()->with('enlace_modal', 'token');
        }

        $record = DB::table('verificaciones_enlace_reserva')
            ->where('reserva_id', $reserva->id)
            ->where('user_id', Auth::id())
            ->where('usado', false)
            ->first();

        if (!$record) {
            return back()->withErrors([
                'enlace' => 'No hay un token activo para esta reserva. Solicita uno nuevo.',
            ])->withInput()->with('enlace_modal', 'token');
        }

        if ($record->bloqueado_hasta && now()->lt($record->bloqueado_hasta)) {
            return back()->withErrors([
                'enlace' => 'Has superado los intentos permitidos. El enlace de reservas esta bloqueado 5 minutos.',
            ])->withInput()->with('enlace_modal', 'token');
        }

        if (now()->gt($record->expira_en)) {
            return back()->withErrors([
                'enlace' => 'El token ha caducado. Solicita un nuevo token para enlazar la reserva.',
            ])->withInput()->with('enlace_modal', 'token');
        }

        if (!hash_equals($record->hash_token, hash('sha256', $token))) {
            $attempts = ((int) $record->intentos) + 1;

            if ($attempts >= self::MAX_INTENTOS_ENLACE) {
                DB::table('verificaciones_enlace_reserva')
                    ->where('id', $record->id)
                    ->update([
                        'intentos' => $attempts,
                        'bloqueado_hasta' => now()->addMinutes(self::MINUTOS_BLOQUEO_ENLACE),
                        'usado' => true,
                        'updated_at' => now(),
                    ]);

                return back()->withErrors([
                    'enlace' => 'Has superado los 5 intentos. El enlace queda bloqueado 5 minutos y debes solicitar un nuevo token.',
                ])->withInput()->with('enlace_modal', 'token');
            }

            DB::table('verificaciones_enlace_reserva')
                ->where('id', $record->id)
                ->update([
                    'intentos' => $attempts,
                    'updated_at' => now(),
                ]);

            return back()->withErrors([
                'enlace' => 'Token incorrecto. Te quedan ' . (self::MAX_INTENTOS_ENLACE - $attempts) . ' intentos.',
            ])->withInput()->with('enlace_modal', 'token');
        }

        DB::transaction(function () use ($reserva, $record) {
            Reserva::query()
                ->where('id', $reserva->id)
                ->update([
                    'user_id' => Auth::id(),
                    'enlazada_en' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('verificaciones_enlace_reserva')
                ->where('id', $record->id)
                ->update([
                    'usado' => true,
                    'intentos' => 0,
                    'bloqueado_hasta' => null,
                    'updated_at' => now(),
                ]);
        });

        return redirect()->route('mis-reservas.index')
            ->with('exito', 'Reserva enlazada correctamente a tu cuenta. Ya puedes gestionarla en Mis Reservas.');
    }

    // Cancelar reserva para usuario autenticado.
    public function cancelar(Request $request, Reserva $reserva)
    {
        $this->asegurarReservaPropia($reserva);

        $request->validate([
            'motivo' => 'required|string|min:3|max:255',
        ]);

        $yaCancelada = in_array($reserva->estado, ['cancelada_usuario', 'cancelada_aerolinea'], true);
        if ($yaCancelada) {
            return back()->withErrors(['cancelacion' => 'La reserva ya se encuentra cancelada.']);
        }

        $voladaOCompletada = $reserva->estado === 'completada' || Carbon::parse($reserva->fecha_salida)->isPast();
        if ($voladaOCompletada) {
            return back()->withErrors(['cancelacion' => 'No se puede cancelar una reserva ya volada o completada.']);
        }

        $politica = $this->resolverPoliticaReembolso($reserva);

        DB::transaction(function () use ($request, $reserva) {
            $estadoAnterior = $reserva->estado;

            $reserva->estado = 'cancelada_usuario';
            $reserva->save();

            Cancelacion::updateOrCreate(
                ['reserva_id' => $reserva->id],
                [
                    'tipo' => 'usuario',
                    'motivo' => trim($request->motivo),
                    'created_at' => now(),
                ]
            );

            $politica = $this->resolverPoliticaReembolso($reserva);

            Reembolso::updateOrCreate(
                ['reserva_id' => $reserva->id],
                [
                    'estado' => $politica['estado_reembolso'],
                    'cantidad' => $politica['cantidad'],
                    'created_at' => now(),
                ]
            );

            $this->registrarCambioEstado(
                $reserva->id,
                $estadoAnterior,
                'cancelada_usuario',
                'Cancelada por usuario: ' . trim($request->motivo)
            );

            $this->recalcularPasajerosConfirmadosVuelo($reserva);
        });

        return back()->with('exito', 'Reserva cancelada correctamente. ' . $politica['mensaje']);
    }

    // Redirigir al flujo de check-in de usuario.
    public function realizarCheckin(Reserva $reserva)
    {
        $this->asegurarReservaPropia($reserva);

        if (!$reserva->checkinDisponibleAhora()) {
            return back()->withErrors([
                'checkin' => 'El check-in aún no está disponible para esta reserva.',
            ]);
        }

        return redirect()->route('checkin.show', $reserva);
    }

    // Redirigir al flujo de check-in de invitado.
    public function realizarCheckinInvitado(Request $request, Reserva $reserva)
    {
        if (Auth::check()) {
            return redirect()->route('mis-reservas.index');
        }

        $credenciales = $this->extraerCredencialesInvitado($request);
        if (!$this->reservaCoincideConCredenciales($reserva, $credenciales)) {
            return back()->withErrors([
                'mis_viajes' => 'No tienes permiso para gestionar esta reserva con esos datos de contacto.',
            ])->withInput();
        }

        if (!$reserva->checkinDisponibleAhora()) {
            return back()->withErrors([
                'checkin' => 'El check-in aún no está disponible para esta reserva.',
            ])->withInput();
        }

        return redirect()->route('checkin.show.invitado', [
            'reserva' => $reserva,
            'localizador' => $credenciales['localizador'],
            'email_contacto' => $credenciales['email_contacto'],
        ]);
    }

    // Cancelar reserva para invitado.
    public function cancelarInvitado(Request $request, Reserva $reserva)
    {
        if (Auth::check()) {
            return redirect()->route('mis-reservas.index');
        }

        $request->validate([
            'localizador' => 'required|string|max:20',
            'email_contacto' => 'required|email|max:150',
            'motivo' => 'required|string|min:3|max:255',
        ]);

        $credenciales = $this->extraerCredencialesInvitado($request);
        if (!$this->reservaCoincideConCredenciales($reserva, $credenciales)) {
            return back()->withErrors([
                'mis_viajes' => 'No tienes permiso para gestionar esta reserva con esos datos de contacto.',
            ])->withInput();
        }

        $yaCancelada = in_array($reserva->estado, ['cancelada_usuario', 'cancelada_aerolinea'], true);
        if ($yaCancelada) {
            return back()->withErrors(['cancelacion' => 'La reserva ya se encuentra cancelada.'])->withInput();
        }

        $voladaOCompletada = $reserva->estado === 'completada' || Carbon::parse($reserva->fecha_salida)->isPast();
        if ($voladaOCompletada) {
            return back()->withErrors(['cancelacion' => 'No se puede cancelar una reserva ya volada o completada.'])->withInput();
        }

        $politica = $this->resolverPoliticaReembolso($reserva);

        DB::transaction(function () use ($request, $reserva) {
            $estadoAnterior = $reserva->estado;

            $reserva->estado = 'cancelada_usuario';
            $reserva->save();

            Cancelacion::updateOrCreate(
                ['reserva_id' => $reserva->id],
                [
                    'tipo' => 'usuario',
                    'motivo' => trim((string) $request->motivo),
                    'created_at' => now(),
                ]
            );

            $politica = $this->resolverPoliticaReembolso($reserva);

            Reembolso::updateOrCreate(
                ['reserva_id' => $reserva->id],
                [
                    'estado' => $politica['estado_reembolso'],
                    'cantidad' => $politica['cantidad'],
                    'created_at' => now(),
                ]
            );

            $this->registrarCambioEstado(
                $reserva->id,
                $estadoAnterior,
                'cancelada_usuario',
                'Cancelada por invitado: ' . trim((string) $request->motivo)
            );

            $this->recalcularPasajerosConfirmadosVuelo($reserva);
        });

        return redirect()->route('mis-viajes.index', [
            'localizador' => $credenciales['localizador'],
            'email_contacto' => $credenciales['email_contacto'],
        ])->with('exito', 'Reserva cancelada correctamente. ' . $politica['mensaje']);
    }

    // Aplicar filtros compartidos de la vista Mis Reservas.
    private function aplicarFiltrosComunes($query, array $filtros): void
    {
        if ($filtros['localizador'] !== '') {
            $query->where('localizador', 'like', '%' . $filtros['localizador'] . '%');
        }

        if ($filtros['origen'] !== '') {
            $query->where('origen', 'like', '%' . $filtros['origen'] . '%');
        }

        if ($filtros['destino'] !== '') {
            $query->where('destino', 'like', '%' . $filtros['destino'] . '%');
        }

        if ($filtros['fecha'] !== '') {
            try {
                $fecha = Carbon::createFromFormat('Y-m-d', $filtros['fecha'])->toDateString();
                $query->whereDate('fecha_salida', $fecha);
            } catch (Exception $e) {
                // Ignorar fechas invalidas para no romper el listado.
            }
        }
    }

    private function buscarReservaPorLocalizador(string $localizador): ?Reserva
    {
        return Reserva::query()
            ->whereRaw('UPPER(localizador) = ?', [$localizador])
            ->first();
    }

    private function validarReservaEnlazable(Reserva $reserva): ?string
    {
        if (!is_null($reserva->user_id)) {
            if ((int) $reserva->user_id === (int) Auth::id()) {
                return 'La reserva ya esta enlazada a tu cuenta.';
            }

            return 'La reserva ya esta enlazada a otra cuenta y no se puede volver a asociar.';
        }

        $estado = strtolower(trim((string) $reserva->estado));
        $estadosNoValidos = ['cancelada_usuario', 'cancelada_aerolinea', 'cancelada', 'anulada', 'eliminada'];
        if (in_array($estado, $estadosNoValidos, true)) {
            return 'Solo se pueden enlazar reservas validas (no canceladas, no anuladas y no eliminadas).';
        }

        $atributos = $reserva->getAttributes();

        if (array_key_exists('deleted_at', $atributos) && !is_null($atributos['deleted_at'])) {
            return 'Solo se pueden enlazar reservas validas (no canceladas, no anuladas y no eliminadas).';
        }

        if (array_key_exists('anulada_at', $atributos) && !is_null($atributos['anulada_at'])) {
            return 'Solo se pueden enlazar reservas validas (no canceladas, no anuladas y no eliminadas).';
        }

        if (array_key_exists('eliminada_at', $atributos) && !is_null($atributos['eliminada_at'])) {
            return 'Solo se pueden enlazar reservas validas (no canceladas, no anuladas y no eliminadas).';
        }

        return null;
    }

    private function asegurarReservaPropia(Reserva $reserva): void
    {
        if ((int) $reserva->user_id !== (int) Auth::id()) {
            abort(403);
        }
    }

    private function resolverPoliticaReembolso(Reserva $reserva): array
    {
        $horasParaSalida = Carbon::now()->diffInHours(Carbon::parse($reserva->fecha_salida), false);

        if ($horasParaSalida >= 168) {
            return [
                'estado_reembolso' => 'pendiente',
                'cantidad' => null,
                'mensaje' => 'Aplica politica de reembolso completo (en gestion).',
            ];
        }

        if ($horasParaSalida >= 72) {
            return [
                'estado_reembolso' => 'pendiente',
                'cantidad' => null,
                'mensaje' => 'Aplica politica de reembolso parcial (en gestion).',
            ];
        }

        return [
            'estado_reembolso' => 'no_aplicable',
            'cantidad' => null,
            'mensaje' => 'Tarifa no reembolsable segun condiciones aplicables.',
        ];
    }

    private function recalcularPasajerosConfirmadosVuelo(Reserva $reserva): void
    {
        if (is_null($reserva->vuelo_id)) {
            return;
        }


        // Liberar los asientos ocupados por los pasajeros de esta reserva
        $codigosAsientos = array_map(
            fn($item) => $item['asiento_codigo'],
            ReservaPasajero::where('reserva_id', $reserva->id)
                ->whereNotNull('asiento_codigo')
                ->select('asiento_codigo')
                ->get()
                ->toArray()
        );

        if (!empty($codigosAsientos)) {
            Asiento::where('vuelo_id', (int) $reserva->vuelo_id)
                ->whereIn('codigo', $codigosAsientos)
                ->update(['ocupado' => false]);
        }

        // Pasajeros confirmados = todos los check-in confirmados (incluye bebés sin asiento)
        $pasajerosConfirmados = ReservaPasajero::query()
            ->join('reservas', 'reservas.id', '=', 'reserva_pasajeros.reserva_id')
            ->where('reservas.vuelo_id', (int) $reserva->vuelo_id)
            ->whereNotIn('reservas.estado', ['cancelada_usuario', 'cancelada_aerolinea'])
            ->whereNotNull('reserva_pasajeros.checkin_confirmado_en')
            ->count('reserva_pasajeros.id');

        // Asientos disponibles = total físico - asientos realmente ocupados (excluye bebés)
        $asientosConfirmados = ReservaPasajero::query()
            ->join('reservas', 'reservas.id', '=', 'reserva_pasajeros.reserva_id')
            ->where('reservas.vuelo_id', (int) $reserva->vuelo_id)
            ->whereNotIn('reservas.estado', ['cancelada_usuario', 'cancelada_aerolinea'])
            ->whereNotNull('reserva_pasajeros.checkin_confirmado_en')
            ->whereNotNull('reserva_pasajeros.asiento_codigo')
            ->count('reserva_pasajeros.id');

        $totalAsientos = Asiento::where('vuelo_id', (int) $reserva->vuelo_id)->count();
        $asientosDisponibles = max(0, $totalAsientos - $asientosConfirmados);
        DB::table('vuelos')
            ->where('id', (int) $reserva->vuelo_id)
            ->update([
                'pasajeros_confirmados' => $pasajerosConfirmados,
                'asientos_disponibles' => $asientosDisponibles
            ]);
    }

    private function cooldownSecondsLeft(?string $ultimoEnvioEn, int $segundosEspera): int
    {
        if (!$ultimoEnvioEn) {
            return 0;
        }

        try {
            $ultimoEnvio = Carbon::parse($ultimoEnvioEn);
        } catch (\Throwable $e) {
            return 0;
        }

        $proximoEnvioEn = $ultimoEnvio->copy()->addSeconds($segundosEspera);
        $segundosRestantes = $proximoEnvioEn->getTimestamp() - now()->getTimestamp();

        if ($segundosRestantes <= 0) {
            return 0;
        }

        // Evitar valores inconsistentes si reloj de BD y app no esta sincronizado.
        return min($segundosEspera, (int) $segundosRestantes);
    }

    private function registrarCambioEstado(int $reservaId, ?string $estadoAnterior, string $estadoNuevo, ?string $motivo = null): void
    {
        ReservaEstadoHistorial::create([
            'reserva_id' => $reservaId,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'motivo' => $motivo,
            'changed_at' => now(),
        ]);
    }

    private function extraerCredencialesInvitado(Request $request): array
    {
        return [
            'localizador' => strtoupper(trim((string) $request->input('localizador', $request->query('localizador', '')))),
            'email_contacto' => strtolower(trim((string) $request->input('email_contacto', $request->query('email_contacto', '')))),
        ];
    }

    private function reservaCoincideConCredenciales(Reserva $reserva, array $credenciales): bool
    {
        return strtoupper((string) $reserva->localizador) === $credenciales['localizador']
            && strtolower((string) $reserva->email_contacto) === $credenciales['email_contacto'];
    }

    private function sincronizarReservasCompletadas(int $idUsuario, Carbon $ahora): void
    {
        $candidatas = Reserva::query()
            ->where('user_id', $idUsuario)
            ->whereNotIn('estado', ['cancelada_usuario', 'cancelada_aerolinea', 'completada'])
            ->where('fecha_llegada', '<', $ahora)
            ->get();

        foreach ($candidatas as $reserva) {
            $estadoAnterior = $reserva->estado;
            $reserva->estado = 'completada';
            $reserva->save();

            $this->registrarCambioEstado(
                $reserva->id,
                $estadoAnterior,
                'completada',
                'Completada automaticamente al superar la fecha de llegada.'
            );
        }
    }

}
