<?php

namespace Database\Factories;

use App\Models\Reserva;
use App\Models\ReservaPasajero;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ReservaPasajeroFactory extends Factory
{
    protected $model = ReservaPasajero::class;

    public function definition(): array
    {
        $documento = $this->documentoDni();

        return [
            'reserva_id' => Reserva::query()->inRandomOrder()->value('id'),
            'nombre' => fake()->firstName(),
            'apellidos' => fake()->lastName() . ' ' . fake()->lastName(),
            'tipo_documento' => 'DNI',
            'numero_documento' => $documento,
            'numero_documento_norm' => $this->normalizarDocumento($documento),
            'fecha_nacimiento' => $this->fechaAdulto(),
            'asiento_codigo' => null,
            'asiento_asignado_en' => null,
            'checkin_confirmado_en' => null,
        ];
    }

    public function adulto(): self
    {
        return $this->state(fn () => [
            'fecha_nacimiento' => $this->fechaAdulto(),
        ]);
    }

    public function nino(): self
    {
        return $this->state(fn () => [
            'fecha_nacimiento' => $this->fechaNino(),
        ]);
    }

    public function bebe(): self
    {
        return $this->state(fn () => [
            'fecha_nacimiento' => $this->fechaBebe(),
        ]);
    }

    public function conDNI(): self
    {
        return $this->state(function () {
            $num = $this->documentoDni();

            return [
                'tipo_documento' => 'DNI',
                'numero_documento' => $num,
                'numero_documento_norm' => $this->normalizarDocumento($num),
            ];
        });
    }

    public function conPasaporte(): self
    {
        return $this->state(function () {
            $num = $this->documentoPasaporte();

            return [
                'tipo_documento' => 'PASAPORTE',
                'numero_documento' => $num,
                'numero_documento_norm' => $this->normalizarDocumento($num),
            ];
        });
    }

    public function sinCheckIn(): self
    {
        return $this->state(fn () => [
            'asiento_codigo' => null,
            'asiento_asignado_en' => null,
            'checkin_confirmado_en' => null,
        ]);
    }

    public function conCheckIn(): self
    {
        $fila = fake()->numberBetween(1, 30);
        $columna = fake()->randomElement(['A', 'B', 'C', 'D', 'E', 'F']);
        $asiento = $fila . $columna;
        $realizadoAt = Carbon::now()->subHours(fake()->numberBetween(1, 48));

        return $this->state(fn () => [
            'asiento_codigo' => $asiento,
            'asiento_asignado_en' => $realizadoAt,
            'checkin_confirmado_en' => $realizadoAt,
        ]);
    }

    public function sinDocumento(): self
    {
        return $this->state(fn () => [
            'tipo_documento' => null,
            'numero_documento' => null,
            'numero_documento_norm' => null,
        ]);
    }

    private function documentoDni(): string
    {
        $letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
        $numero = fake()->numberBetween(10000000, 99999999);

        $letra = $letras[$numero % 23];

        return $numero . $letra;
    }

    private function documentoPasaporte(): string
    {
        $longitud = fake()->numberBetween(6, 15);
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $resultado = '';

        $resultado .= fake()->randomLetter() . fake()->randomLetter();
        $resultado .= str_pad((string) fake()->numberBetween(1000, 9999), 4, '0', STR_PAD_LEFT);

        while (strlen($resultado) < $longitud) {
            $resultado .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return strtoupper($resultado);
    }

    private function normalizarDocumento(string $documento): string
    {
        return strtoupper(str_replace(' ', '', $documento));
    }

    private function fechaAdulto(): string
    {
        return Carbon::now()->subYears(fake()->numberBetween(16, 80))
            ->subDays(fake()->numberBetween(0, 364))
            ->format('Y-m-d');
    }

    private function fechaNino(): string
    {
        return Carbon::now()->subYears(fake()->numberBetween(3, 15))
            ->subDays(fake()->numberBetween(0, 364))
            ->format('Y-m-d');
    }

    private function fechaBebe(): string
    {
        return Carbon::now()->subMonths(fake()->numberBetween(0, 23))
            ->subDays(fake()->numberBetween(0, 30))
            ->format('Y-m-d');
    }
}
