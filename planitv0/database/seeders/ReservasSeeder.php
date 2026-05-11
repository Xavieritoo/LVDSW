<?php

namespace Database\Seeders;

use App\Models\Asiento;
use App\Models\Reserva;
use App\Models\ReservaPasajero;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReservasSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = DB::table('usuarios')
            ->whereIn('email', ['ana@planit.local', 'mooldovan.21@gmail.com', 'moooldo.03@gmail.com'])
            ->get(['id', 'email']);

        if ($usuarios->isEmpty()) {
            if ($this->command) {
                $this->command->warn('No se encontraron usuarios. Ejecuta primero UsuariosSeeder.');
            }
            return;
        }

        $this->limpiarDatosPrevios($usuarios->pluck('id')->all());

        foreach ($usuarios as $usuario) {
            DB::transaction(function () use ($usuario) {
                $this->seedReservasPorUsuario($usuario);
            });
        }

        $this->seedReservasInvitadoMisViajes('mooldovan.21@gmail.com', 'AND');
        $this->seedReservasInvitadoMisViajes('moooldo.03@gmail.com', 'MOO');
        $this->seedReservasEnlace($usuarios);
    }

    private function limpiarDatosPrevios(array $userIds): void
    {
        $reservaIds = DB::table('reservas')
            ->where(function ($q) use ($userIds) {
                $q->whereIn('user_id', $userIds)
                    ->orWhere('localizador', 'LIKE', 'ANA%')
                    ->orWhere('localizador', 'LIKE', 'AND%')
                    ->orWhere('localizador', 'LIKE', 'MOO%')
                    ->orWhere('localizador', 'LIKE', 'ENL%')
                    ->orWhere('localizador', 'LIKE', 'MVI%');
            })
            ->pluck('id')
            ->all();

        if (!empty($reservaIds)) {
            DB::table('checkin_eventos')->whereIn('reserva_id', $reservaIds)->delete();
            DB::table('verificaciones_enlace_reserva')->whereIn('reserva_id', $reservaIds)->delete();
            DB::table('reserva_pasajeros')->whereIn('reserva_id', $reservaIds)->delete();
            DB::table('cancelaciones')->whereIn('reserva_id', $reservaIds)->delete();
            DB::table('reembolsos')->whereIn('reserva_id', $reservaIds)->delete();
            DB::table('reserva_estado_historial')->whereIn('reserva_id', $reservaIds)->delete();
            DB::table('reservas')->whereIn('id', $reservaIds)->delete();
        }
    }

    private function seedReservasPorUsuario(object $usuario): void
    {
        $email = strtolower(trim((string) $usuario->email));
        $prefijo = $this->prefijoPorEmail($email);
        $cantidad = $this->cantidadReservasPorUsuario($email);

        for ($indice = 1; $indice <= $cantidad; $indice++) {
            $tipo = $this->tipoEscenarioPorIndice($indice);
            $localizador = $prefijo . str_pad((string) $indice, 3, '0', STR_PAD_LEFT);
            $this->crearReservaEscenario($usuario, $localizador, $tipo, $indice);
        }
    }

    private function cantidadReservasPorUsuario(string $email): int
    {
        if ($email === 'mooldovan.21@gmail.com') {
            return 18;
        }

        if ($email === 'moooldo.03@gmail.com') {
            return 16;
        }

        return 8;
    }

    private function tipoEscenarioPorIndice(int $indice): string
    {
        $tipos = [
            'proxima_checkin_realizado',
            'proxima_checkin_disponible',
            'proxima_checkin_pendiente',
            'datos_pendientes',
            'volada',
            'cancelada_usuario',
            'cancelada_aerolinea',
        ];

        return $tipos[($indice - 1) % count($tipos)];
    }

    private function crearReservaEscenario(object $usuario, string $localizador, string $tipo, int $indice): void
    {
        $ahora = now();
        $dias = max(1, $indice);
        $fechaCompra = $ahora->copy()->subDays(25 + $indice);

        $estado = 'confirmada';
        if ($indice % 2 === 0) {
            $planTarifa = 'planit_comfort';
        } else {
            $planTarifa = 'planit_easy';
        }
        $fechaSalida = $ahora->copy()->addDays($dias)->setTime(9 + ($indice % 8), 0);
        $fechaLlegada = $fechaSalida->copy()->addHours(2)->addMinutes(15);
        $checkinDesde = $this->checkinDisponibleDesde($planTarifa, $fechaSalida, $fechaCompra);
        $checkinRealizado = null;
        $checkinEstado = 'pendiente';
        $tarjetasEmitidas = 0;
        $correoIntentado = null;
        $correoEstado = 'pendiente';
        $correoError = null;

        if ($tipo === 'volada') {
            $estado = 'completada';
            $fechaSalida = $ahora->copy()->subDays(2 + $indice)->setTime(8, 15);
            $fechaLlegada = $fechaSalida->copy()->addHours(2)->addMinutes(35);
            $checkinDesde = $this->checkinDisponibleDesde($planTarifa, $fechaSalida, $fechaCompra);
            $checkinRealizado = $fechaSalida->copy()->subHours(10);
            $checkinEstado = 'confirmada';
            $tarjetasEmitidas = 1;
            $correoIntentado = $checkinRealizado->copy()->addMinutes(2);
            if ($indice % 3 === 0) {
                $correoEstado = 'fallido';
            } else {
                $correoEstado = 'enviado';
            }
            if ($correoEstado === 'fallido') {
                $correoError = 'SMTP timeout en intento unico de envio';
            } else {
                $correoError = null;
            }
        }

        if ($tipo === 'cancelada_usuario' || $tipo === 'cancelada_aerolinea') {
            if ($tipo === 'cancelada_usuario') {
                $estado = 'cancelada_usuario';
            } else {
                $estado = 'cancelada_aerolinea';
            }
            $fechaSalida = $ahora->copy()->addDays(2 + $indice)->setTime(10, 20);
            $fechaLlegada = $fechaSalida->copy()->addHours(3)->addMinutes(10);
            $checkinDesde = $this->checkinDisponibleDesde($planTarifa, $fechaSalida, $fechaCompra);
            $correoEstado = 'pendiente';
        }

        if ($tipo === 'proxima_checkin_realizado') {
            $estado = 'confirmada';
            $fechaSalida = $ahora->copy()->addDays(3 + $indice)->setTime(13, 0);
            $fechaLlegada = $fechaSalida->copy()->addHours(2)->addMinutes(40);
            $checkinDesde = $this->checkinDisponibleDesde($planTarifa, $fechaSalida, $fechaCompra);
            $checkinRealizado = $ahora->copy()->subHours(4);
            $checkinEstado = 'confirmada';
            $tarjetasEmitidas = 1;
            $correoIntentado = $ahora->copy()->subHours(3);
            if ($indice % 4 === 0) {
                $correoEstado = 'fallido';
            } else {
                $correoEstado = 'enviado';
            }
            if ($correoEstado === 'fallido') {
                $correoError = 'Error SMTP al adjuntar PDF de tarjetas';
            } else {
                $correoError = null;
            }
        }

        if ($tipo === 'proxima_checkin_disponible') {
            $estado = 'confirmada';
            if ($planTarifa === 'planit_easy') {
                $fechaSalida = $ahora->copy()->addHours(10 + ($indice % 8));
            } else {
                $fechaSalida = $ahora->copy()->addDays(6 + $indice)->setTime(11, 10);
            }
            $fechaLlegada = $fechaSalida->copy()->addHours(2)->addMinutes(30);
            $checkinDesde = $this->checkinDisponibleDesde($planTarifa, $fechaSalida, $fechaCompra);
            $correoEstado = 'pendiente';
        }

        if ($tipo === 'proxima_checkin_pendiente') {
            $estado = 'confirmada';
            $planTarifa = 'planit_easy';
            $fechaSalida = $ahora->copy()->addDays(4 + $indice)->setTime(7, 45);
            $fechaLlegada = $fechaSalida->copy()->addHours(1)->addMinutes(55);
            $checkinDesde = $this->checkinDisponibleDesde($planTarifa, $fechaSalida, $fechaCompra);
            $correoEstado = 'pendiente';
        }

        if ($tipo === 'datos_pendientes') {
            $estado = 'datos_pendientes';
            $fechaSalida = $ahora->copy()->addDays(6 + $indice)->setTime(15, 30);
            $fechaLlegada = $fechaSalida->copy()->addHours(2);
            $checkinDesde = $this->checkinDisponibleDesde($planTarifa, $fechaSalida, $fechaCompra);
            $correoEstado = 'pendiente';
        }

        if ($planTarifa === 'planit_comfort') {
            $equipajeResumen = 'Incluye maleta facturada de 23kg y equipaje de mano.';
        } else {
            $equipajeResumen = 'Incluye equipaje de mano. Maleta facturada opcional.';
        }
        if ($planTarifa === 'planit_comfort') {
            $asientosResumen = 'Seleccion y cambio de asiento sin coste desde compra hasta despegue.';
        } else {
            $asientosResumen = 'Asiento estandar sin coste al completar check-in; seleccion especifica con suplemento.';
        }

        $vueloId = DB::table('vuelos')
            ->where('activo', true)
            ->inRandomOrder()
            ->value('id');

        $precioBase = round(50 + ($indice * 13.5) + (($indice % 3) * 25), 2);
        $cantidadPasajeros = 2 + ($indice % 2);
        $precioTotal = round($precioBase * $cantidadPasajeros, 2);

        $reserva = Reserva::query()->create([
            'user_id' => $usuario->id,
            'enlazada_en' => now(),
            'localizador' => $localizador,
            'vuelo_id' => $vueloId,
            'origen' => $this->origenPorIndice($indice),
            'destino' => $this->destinoPorIndice($indice),
            'fecha_salida' => $fechaSalida,
            'fecha_llegada' => $fechaLlegada,
            'estado' => $estado,
            'plan_tarifa' => $planTarifa,
            'precio_total' => $precioTotal,
            'email_contacto' => $usuario->email,
            'checkin_disponible_desde' => $checkinDesde,
            'checkin_realizado_en' => $checkinRealizado,
            'checkin_estado' => $checkinEstado,
            'tarjetas_emitidas' => $tarjetasEmitidas,
            'checkin_correo_intentado_en' => $correoIntentado,
            'checkin_correo_estado' => $correoEstado,
            'checkin_correo_error' => $correoError,
            'equipaje_resumen' => $equipajeResumen,
            'asientos_resumen' => $asientosResumen,
            'meteorologia_resumen' => 'Prevision de vuelo estable con nubosidad variable.',
        ]);

        $conCheckinPasajeros = $checkinEstado === 'confirmada';
        $this->crearPasajerosReserva($reserva->id, strtolower(trim((string) $usuario->email)), $conCheckinPasajeros, $indice, $vueloId);

        if ($tipo === 'cancelada_usuario' || $tipo === 'cancelada_aerolinea') {
            $this->registrarCancelacionYReembolso($reserva->id, $tipo, $planTarifa);
        }

        $this->registrarHistorialEstado($reserva->id, $estado, $tipo);
        $this->registrarEventosCheckin($reserva->id, $usuario->id, $usuario->email, $tipo, $checkinEstado, $correoEstado);
    }

    private function crearPasajerosReserva(int $reservaId, string $email, bool $conCheckIn, int $indice, ?int $vueloId = null): void
    {
        $cantidadPasajeros = 2 + ($indice % 2);

        // Si hay check-in realizado, generar asientos del vuelo primero
        if ($conCheckIn && $vueloId) {
            $existeAsientos = Asiento::where('vuelo_id', $vueloId)->exists();
            if (!$existeAsientos) {
                $this->generarAsientosVuelo($vueloId);
            }
        }

        for ($i = 1; $i <= $cantidadPasajeros; $i++) {
            $tipoDocumento = null;
            $numeroDocumento = null;
            $asiento = null;
            $asientoAsignadoEn = null;
            $checkinConfirmadoEn = null;

            if ($conCheckIn) {
                if ($i % 2 === 0) {
                    $tipoDocumento = 'DNI';
                } else {
                    $tipoDocumento = 'PASAPORTE';
                }
                $numeroDocumento = $this->documentoUnico($email, $indice, $i, $tipoDocumento);
                $asiento = (10 + $i) . chr(64 + $i);
                $asientoAsignadoEn = now()->subHours(2);
                $checkinConfirmadoEn = now()->subHours(2);

                // Marcar asiento como ocupado en asientos_vuelo
                if ($vueloId) {
                    Asiento::where('vuelo_id', $vueloId)
                        ->where('codigo', $asiento)
                        ->update(['ocupado' => true]);
                }
            }

            $docNorm = null;
            if ($numeroDocumento) {
                $docNorm = strtoupper($numeroDocumento);
            }

            ReservaPasajero::query()->create([
                'reserva_id' => $reservaId,
                'nombre' => $this->nombrePasajero($email, $i),
                'apellidos' => $this->apellidosPasajero($email, $i),
                'tipo_documento' => $tipoDocumento,
                'numero_documento' => $numeroDocumento,
                'numero_documento_norm' => $docNorm,
                'fecha_nacimiento' => Carbon::now()->subYears(18 + $i + ($indice % 10))->format('Y-m-d'),
                'checkin_confirmado_en' => $checkinConfirmadoEn,
                'asiento_codigo' => $asiento,
                'asiento_asignado_en' => $asientoAsignadoEn,
            ]);
        }
    }

    private function registrarCancelacionYReembolso(int $reservaId, string $tipoEscenario, string $planTarifa): void
    {
        $esUsuario = $tipoEscenario === 'cancelada_usuario';
        if ($esUsuario) {
            $motivo = 'Cambio de planes de viaje';
        } else {
            $motivo = 'Cancelacion operativa del vuelo por la aerolinea';
        }

        if ($esUsuario) {
            if ($planTarifa === 'planit_comfort') {
                $estadoReembolso = 'completado';
            } else {
                $estadoReembolso = 'pendiente';
            }
        } else {
            $estadoReembolso = 'completado';
        }

        if ($esUsuario) {
            if ($planTarifa === 'planit_comfort') {
                $cantidad = 145.00;
            } else {
                $cantidad = 59.90;
            }
        } else {
            $cantidad = 214.50;
        }

        $tipoCancelacion = 'aerolinea';
        if ($esUsuario) {
            $tipoCancelacion = 'usuario';
        }

        DB::table('cancelaciones')->insert([
            'reserva_id' => $reservaId,
            'tipo' => $tipoCancelacion,
            'motivo' => $motivo,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('reembolsos')->insert([
            'reserva_id' => $reservaId,
            'estado' => $estadoReembolso,
            'cantidad' => $cantidad,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function registrarHistorialEstado(int $reservaId, string $estadoFinal, string $tipoEscenario): void
    {
        DB::table('reserva_estado_historial')->insert([
            'reserva_id' => $reservaId,
            'estado_anterior' => 'confirmada',
            'estado_nuevo' => $estadoFinal,
            'motivo' => $this->motivoHistorial($tipoEscenario),
            'changed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function registrarEventosCheckin(
        int $reservaId,
        int $usuarioId,
        string $emailActor,
        string $tipoEscenario,
        string $checkinEstado,
        string $correoEstado
    ): void {
        DB::table('checkin_eventos')->insert([
            'reserva_id' => $reservaId,
            'reserva_pasajero_id' => null,
            'tipo' => 'checkin_iniciado',
            'actor_tipo' => 'usuario',
            'actor_user_id' => $usuarioId,
            'actor_email' => $emailActor,
            'descripcion' => 'Reserva creada con flujo de check-in preparado para pruebas.',
            'meta' => json_encode(['escenario' => $tipoEscenario], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);

        if ($checkinEstado === 'confirmada') {
            DB::table('checkin_eventos')->insert([
                'reserva_id' => $reservaId,
                'reserva_pasajero_id' => null,
                'tipo' => 'checkin_confirmado',
                'actor_tipo' => 'usuario',
                'actor_user_id' => $usuarioId,
                'actor_email' => $emailActor,
                'descripcion' => 'Check-in completado y tarjetas emitidas.',
                'meta' => json_encode(['tarjetas_emitidas' => true], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
            ]);

            $tipoCorreoEvento = 'correo_checkin_fallo';
            if ($correoEstado === 'enviado') {
                $tipoCorreoEvento = 'correo_checkin_exito';
            }
            $descripcionCorreo = 'No se pudo enviar el correo de check-in.';
            if ($correoEstado === 'enviado') {
                $descripcionCorreo = 'Correo de check-in enviado al pasajero principal.';
            }

            DB::table('checkin_eventos')->insert([
                'reserva_id' => $reservaId,
                'reserva_pasajero_id' => null,
                'tipo' => $tipoCorreoEvento,
                'actor_tipo' => 'sistema',
                'actor_user_id' => null,
                'actor_email' => null,
                'descripcion' => $descripcionCorreo,
                'meta' => json_encode(['correo_estado' => $correoEstado], JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
            ]);
        }
    }

    private function seedReservasEnlace(iterable $usuarios): void
    {
        $plantillas = [
            ['sufijo' => 'A', 'origen' => 'Gran Canaria', 'destino' => 'Madrid'],
            ['sufijo' => 'B', 'origen' => 'Tenerife', 'destino' => 'Barcelona'],
        ];

        foreach ($usuarios as $usuario) {
            $email = strtolower(trim((string) $usuario->email));
            $prefix = $this->prefijoPorEmail($email);

            foreach ($plantillas as $p) {
                $loc = 'ENL' . $prefix . $p['sufijo'];

                $reserva = Reserva::factory()->planitEasy()->proximas()->create([
                    'user_id' => null,
                    'enlazada_en' => null,
                    'localizador' => $loc,
                    'origen' => $p['origen'],
                    'destino' => $p['destino'],
                    'email_contacto' => $usuario->email,
                    'estado' => 'confirmada',
                    'checkin_estado' => 'pendiente',
                    'tarjetas_emitidas' => 0,
                ]);

                ReservaPasajero::query()->create([
                    'reserva_id' => $reserva->id,
                    'nombre' => 'Pasajero',
                    'apellidos' => 'Invitado ' . $usuario->id,
                    'tipo_documento' => 'DNI',
                    'numero_documento' => 'INV' . $usuario->id . $p['sufijo'] . '0001',
                    'numero_documento_norm' => 'INV' . $usuario->id . $p['sufijo'] . '0001',
                    'fecha_nacimiento' => '1995-01-15',
                ]);

                DB::table('verificaciones_enlace_reserva')->insert([
                    'reserva_id' => $reserva->id,
                    'user_id' => $usuario->id,
                    'email_contacto' => $usuario->email,
                    'hash_token' => hash('sha256', 'ENLACE-' . $loc),
                    'expira_en' => now()->addMinutes(15),
                    'usado' => 0,
                    'intentos' => 0,
                    'bloqueado_hasta' => null,
                    'ultimo_envio_en' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedReservasInvitadoMisViajes(string $email, string $prefijo): void
    {
        $escenarios = [
            [
                'localizador' => 'MVI' . $prefijo . '01',
                'states' => ['planitEasy', 'proximas'],
                'pax_count' => 3,
            ],
            [
                'localizador' => 'MVI' . $prefijo . '02',
                'states' => ['planitComfort', 'checkinCompletado', 'voladas'],
                'pax_count' => 2,
            ],
            [
                'localizador' => 'MVI' . $prefijo . '03',
                'states' => ['planitEasy', 'canceladasUsuario'],
                'pax_count' => 1,
                'cancelacion' => [
                    'tipo' => 'usuario',
                    'motivo' => 'Cambio de planes de viaje',
                    'reembolso' => 45.00,
                    'reembolso_estado' => 'pendiente',
                ],
            ],
        ];

        foreach ($escenarios as $esc) {
            $factory = Reserva::factory();
            foreach ($esc['states'] as $state) {
                $factory = $factory->$state();
            }

            $reserva = $factory->create([
                'user_id' => null,
                'enlazada_en' => null,
                'localizador' => $esc['localizador'],
                'email_contacto' => strtolower(trim($email)),
            ]);

            ReservaPasajero::factory()
                ->count($esc['pax_count'])
                ->conPasaporte()
                ->adulto()
                ->sinCheckIn()
                ->create(['reserva_id' => $reserva->id]);

            if (isset($esc['cancelacion'])) {
                $c = $esc['cancelacion'];
                DB::table('cancelaciones')->insert([
                    'reserva_id' => $reserva->id,
                    'tipo' => $c['tipo'],
                    'motivo' => $c['motivo'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('reembolsos')->insert([
                    'reserva_id' => $reserva->id,
                    'estado' => $c['reembolso_estado'],
                    'cantidad' => $c['reembolso'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function prefijoPorEmail(string $email): string
    {
        if ($email === 'mooldovan.21@gmail.com') {
            return 'AND';
        }

        if ($email === 'moooldo.03@gmail.com') {
            return 'MOO';
        }

        return 'ANA';
    }

    private function origenPorIndice(int $indice): string
    {
        $origenes = ['Madrid', 'Barcelona', 'Bilbao', 'Valencia', 'Sevilla', 'Tenerife', 'Gran Canaria'];
        return $origenes[$indice % count($origenes)];
    }

    private function destinoPorIndice(int $indice): string
    {
        $destinos = ['Roma', 'Paris', 'Berlin', 'Lisboa', 'Londres', 'Amsterdam', 'Bruselas'];
        return $destinos[$indice % count($destinos)];
    }

    private function motivoHistorial(string $tipoEscenario): string
    {
        return match ($tipoEscenario) {
            'cancelada_usuario' => 'Cancelacion solicitada por el usuario desde Mis Reservas.',
            'cancelada_aerolinea' => 'Cancelacion realizada por la aerolinea por causa operativa.',
            'volada' => 'Reserva marcada como completada al superar fecha de llegada.',
            'datos_pendientes' => 'Reserva creada pendiente de completar datos de pasajero.',
            default => 'Reserva confirmada y clasificada automaticamente por fecha y estado.',
        };
    }

    private function documentoUnico(string $email, int $indice, int $pasajero, string $tipo): string
    {
        $base = strtoupper(substr(md5($email . '-' . $indice . '-' . $pasajero . '-' . $tipo), 0, 8));
        if ($tipo === 'DNI') {
            $numero = (string) (10000000 + (($indice * 131 + $pasajero * 17) % 89999999));
            $letra = substr('TRWAGMYFPDXBNJZSQVHLCKE', ((int) $numero) % 23, 1);
            return $numero . $letra;
        }

        return 'P' . $base;
    }

    private function nombrePasajero(string $email, int $indicePasajero): string
    {
        if ($email === 'mooldovan.21@gmail.com') {
            if ($indicePasajero === 1) {
                return 'Andrei';
            }
            if ($indicePasajero === 2) {
                return 'Ioan';
            }
            return 'Elena';
        }

        if ($email === 'moooldo.03@gmail.com') {
            if ($indicePasajero === 1) {
                return 'Moldo';
            }
            if ($indicePasajero === 2) {
                return 'Carla';
            }
            return 'Hector';
        }

        if ($indicePasajero === 1) {
            return 'Ana';
        }
        if ($indicePasajero === 2) {
            return 'Lucia';
        }
        return 'Marco';
    }

    private function checkinDisponibleDesde(string $planTarifa, Carbon $fechaSalida, Carbon $fechaCompra): Carbon
    {
        if ($planTarifa === 'planit_comfort') {
            return $fechaCompra->copy();
        }

        return $fechaSalida->copy()->subHours(24);
    }

    private function apellidosPasajero(string $email, int $indicePasajero): string
    {
        if ($email === 'mooldovan.21@gmail.com') {
            if ($indicePasajero === 1) {
                return 'Moldovan';
            }
            if ($indicePasajero === 2) {
                return 'Moldovan Stoica';
            }
            return 'Moldovan Popescu';
        }

        if ($email === 'moooldo.03@gmail.com') {
            if ($indicePasajero === 1) {
                return 'Demo Prueba';
            }
            if ($indicePasajero === 2) {
                return 'Navarro Gil';
            }
            return 'Suarez Pardo';
        }

        if ($indicePasajero === 1) {
            return 'Martin Diaz';
        }
        if ($indicePasajero === 2) {
            return 'Santos Perez';
        }
        return 'Rossi Bianchi';
    }

    private function generarAsientosVuelo(int $vueloId): void
    {
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
}
