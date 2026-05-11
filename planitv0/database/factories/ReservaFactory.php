<?php

namespace Database\Factories;

use App\Models\Reserva;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReservaFactory extends Factory
{
    protected $model = Reserva::class;

    private array $origenes = [
        'Madrid', 'Barcelona', 'Valencia', 'Sevilla', 'Bilbao', 'Malaga', 'Gran Canaria', 'Tenerife', 'Alicante',
    ];

    private array $destinos = [
        'Roma', 'Paris', 'Berlin', 'Lisboa', 'Londres', 'Amsterdam', 'Munich', 'Zurich', 'Bruselas',
    ];

    public function definition(): array
    {
        $salida = Carbon::instance($this->faker->dateTimeBetween('+1 day', '+45 days'));
        $llegada = (clone $salida)->addHours($this->faker->numberBetween(1, 4));
        $plan = 'planit_easy';
        $ahora = now();
        $checkinDesde = $this->checkinDesdeSegunPlan($plan, $salida, $ahora);
        $contacto = $this->contactoUsuario();

        $vueloId = DB::table('vuelos')
            ->where('activo', true)
            ->where('fecha_salida', '>', now())
            ->inRandomOrder()
            ->value('id');

        return [
            'user_id' => $contacto['id'],
            'localizador' => strtoupper(fake()->unique()->bothify('??###??')),
            'vuelo_id' => $vueloId,
            'origen' => fake()->randomElement($this->origenes),
            'destino' => fake()->randomElement($this->destinos),
            'fecha_salida' => $salida,
            'fecha_llegada' => $llegada,
            'estado' => 'confirmada',
            'plan_tarifa' => $plan,
            'precio_total' => fake()->randomFloat(2, 50, 500),
            'email_contacto' => $contacto['email'],
            'checkin_disponible_desde' => $checkinDesde,
            'checkin_realizado_en' => null,
            'checkin_estado' => 'pendiente',
            'tarjetas_emitidas' => 0,
            'checkin_correo_intentado_en' => null,
            'checkin_correo_estado' => 'pendiente',
            'checkin_correo_error' => null,
            'equipaje_resumen' => 'Incluye equipaje de mano. Maleta facturada opcional.',
            'asientos_resumen' => 'Asiento estandar sin coste al completar check-in; seleccion especifica con suplemento.',
            'meteorologia_resumen' => 'Prevision de vuelo estable con nubosidad variable.',
        ];
    }

    public function planitEasy(): self
    {
        return $this->state(function (array $attr) {
            $salida = Carbon::parse($attr['fecha_salida']);

            return [
                'plan_tarifa' => 'planit_easy',
                'checkin_disponible_desde' => (clone $salida)->subHours(24),
                'equipaje_resumen' => 'Incluye equipaje de mano. Maleta facturada opcional.',
                'asientos_resumen' => 'Asiento estandar sin coste al completar check-in; seleccion especifica con suplemento.',
            ];
        });
    }

    public function planitComfort(): self
    {
        return $this->state(function (array $attr) {
            if (isset($attr['created_at'])) {
                $fechaCreacion = Carbon::parse($attr['created_at']);
            } else {
                $fechaCreacion = now();
            }

            return [
                'plan_tarifa' => 'planit_comfort',
                'checkin_disponible_desde' => $fechaCreacion,
                'equipaje_resumen' => 'Incluye maleta facturada de 23kg y equipaje de mano.',
                'asientos_resumen' => 'Seleccion y cambio de asiento sin coste desde compra hasta despegue.',
            ];
        });
    }

    public function checkinCompletado(): self
    {
        return $this->state(function () {
            $realizadoAt = Carbon::now()->subHours(fake()->numberBetween(1, 48));

            return [
                'checkin_realizado_en' => $realizadoAt,
                'checkin_estado' => 'confirmada',
                'tarjetas_emitidas' => 1,
                'checkin_correo_intentado_en' => $realizadoAt->copy()->addMinutes(2),
                'checkin_correo_estado' => 'enviado',
                'checkin_correo_error' => null,
            ];
        });
    }

    public function checkinDisponible(): self
    {
        return $this->state(function (array $attr) {
            if (isset($attr['plan_tarifa'])) {
                $plan = (string) $attr['plan_tarifa'];
            } else {
                $plan = 'planit_easy';
            }
            $salida = Carbon::now()->addHours(fake()->numberBetween(5, 20));
            $llegada = $salida->copy()->addHours(fake()->numberBetween(1, 4));

            return [
                'fecha_salida' => $salida,
                'fecha_llegada' => $llegada,
                'checkin_disponible_desde' => $this->checkinDesdeSegunPlan($plan, $salida, now()->subDays(10)),
                'checkin_realizado_en' => null,
                'checkin_estado' => 'pendiente',
                'tarjetas_emitidas' => 0,
                'checkin_correo_intentado_en' => null,
                'checkin_correo_estado' => 'pendiente',
                'checkin_correo_error' => null,
            ];
        });
    }

    public function checkinNoDisponible(): self
    {
        return $this->state(function () {
            $salida = Carbon::now()->addDays(fake()->numberBetween(3, 14));
            $llegada = $salida->copy()->addHours(fake()->numberBetween(1, 4));

            return [
                'plan_tarifa' => 'planit_easy',
                'fecha_salida' => $salida,
                'fecha_llegada' => $llegada,
                'checkin_disponible_desde' => (clone $salida)->subHours(24),
                'checkin_realizado_en' => null,
                'checkin_estado' => 'pendiente',
                'tarjetas_emitidas' => 0,
                'checkin_correo_intentado_en' => null,
                'checkin_correo_estado' => 'pendiente',
                'checkin_correo_error' => null,
            ];
        });
    }

    public function proximas(): self
    {
        return $this->state(function () {
            $salida = Carbon::now()->addDays(fake()->numberBetween(2, 40))->setTime(fake()->numberBetween(6, 20), 0);

            return [
                'fecha_salida' => $salida,
                'fecha_llegada' => (clone $salida)->addHours(fake()->numberBetween(1, 4)),
                'estado' => fake()->randomElement(['confirmada', 'datos_pendientes']),
                'checkin_realizado_en' => null,
                'checkin_estado' => 'pendiente',
                'tarjetas_emitidas' => 0,
            ];
        });
    }

    public function voladas(): self
    {
        return $this->state(function () {
            $salida = Carbon::now()->subDays(fake()->numberBetween(2, 60))->setTime(fake()->numberBetween(6, 20), 0);
            $checkinAt = $salida->copy()->subHours(fake()->numberBetween(2, 16));

            return [
                'fecha_salida' => $salida,
                'fecha_llegada' => (clone $salida)->addHours(fake()->numberBetween(1, 4)),
                'estado' => 'completada',
                'checkin_disponible_desde' => $salida->copy()->subHours(24),
                'checkin_realizado_en' => $checkinAt,
                'checkin_estado' => 'confirmada',
                'tarjetas_emitidas' => 1,
                'checkin_correo_intentado_en' => $checkinAt->copy()->addMinutes(2),
                'checkin_correo_estado' => 'enviado',
                'checkin_correo_error' => null,
            ];
        });
    }

    public function canceladasUsuario(): self
    {
        return $this->state(function () {
            $salida = Carbon::now()->addDays(fake()->numberBetween(3, 20))->setTime(fake()->numberBetween(6, 20), 0);

            return [
                'fecha_salida' => $salida,
                'fecha_llegada' => (clone $salida)->addHours(fake()->numberBetween(1, 4)),
                'estado' => 'cancelada_usuario',
                'checkin_realizado_en' => null,
                'checkin_estado' => 'pendiente',
                'tarjetas_emitidas' => 0,
            ];
        });
    }

    public function canceladasAerolinea(): self
    {
        return $this->state(function () {
            $salida = Carbon::now()->addDays(fake()->numberBetween(1, 15))->setTime(fake()->numberBetween(6, 20), 0);

            return [
                'fecha_salida' => $salida,
                'fecha_llegada' => (clone $salida)->addHours(fake()->numberBetween(1, 4)),
                'estado' => 'cancelada_aerolinea',
                'checkin_realizado_en' => null,
                'checkin_estado' => 'pendiente',
                'tarjetas_emitidas' => 0,
            ];
        });
    }

    private function contactoUsuario(): array
    {
        $usuarioRolId = (int) DB::table('roles')->where('nombre', 'usuario')->value('id');

        $usuario = Usuario::query()
            ->when($usuarioRolId > 0, fn ($q) => $q->where('rol_id', $usuarioRolId))
            ->inRandomOrder()
            ->first(['id', 'email']);

        if ($usuario) {
            return [
                'id' => $usuario->id,
                'email' => (string) $usuario->email,
            ];
        }

        return [
            'id' => null,
            'email' => fake()->safeEmail(),
        ];
    }

    private function checkinDesdeSegunPlan(string $planTarifa, Carbon $fechaSalida, Carbon $fechaCompra): Carbon
    {
        if (trim(mb_strtolower($planTarifa, 'UTF-8')) === 'planit_comfort') {
            return $fechaCompra;
        }

        return $fechaSalida->copy()->subHours(24);
    }
}
