<?php

namespace Database\Factories;

use App\Models\PasajeroFrecuente;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PasajeroFrecuenteFactory extends Factory
{
    protected $model = PasajeroFrecuente::class;

    private array $paises = [
        'España', 'Italia', 'Francia', 'Alemania', 'Portugal', 'Bélgica',
        'Países Bajos', 'Suiza', 'Austria', 'Suecia', 'Noruega', 'Dinamarca',
        'Canadá', 'Estados Unidos', 'México', 'Brasil', 'Argentina', 'Chile',
        'Colombia', 'Perú', 'Japón', 'India',
    ];

    public function definition(): array
    {
        $usuarioRolId = (int) DB::table('roles')->where('nombre', 'usuario')->value('id');
        $usuarioId = Usuario::query()
            ->when($usuarioRolId > 0, fn ($q) => $q->where('rol_id', $usuarioRolId))
            ->inRandomOrder()
            ->value('id');

        if (fake()->boolean(65)) {
            $tipoDocumento = null;
            $numeroDocumento = null;
            $numeroNorm = null;
        } else {
            if (fake()->boolean()) {
                $tipoDocumento = 'DNI';
                $numeroDocumento = $this->documentoDni();
            } else {
                $tipoDocumento = 'PASAPORTE';
                $numeroDocumento = $this->documentoPasaporte();
            }

            $numeroNorm = $this->normalizarDocumento($numeroDocumento);
        }

        return [
            'user_id' => $usuarioId,
            'nombre' => fake()->firstName(),
            'apellidos' => fake()->lastName() . ' ' . fake()->lastName(),
            'tipo_documento' => $tipoDocumento,
            'numero_documento' => $numeroDocumento,
            'numero_documento_norm' => $numeroNorm,
            'fecha_nacimiento' => $this->fechaAdulto(),
            'pais' => fake()->optional(0.85)->randomElement($this->paises),
            'favorito' => false,
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

    public function conDni(): self
    {
        return $this->state(function () {
            $numero = $this->documentoDni();

            return [
                'tipo_documento' => 'DNI',
                'numero_documento' => $numero,
                'numero_documento_norm' => $this->normalizarDocumento($numero),
            ];
        });
    }

    public function conPasaporte(): self
    {
        return $this->state(function () {
            $numero = $this->documentoPasaporte();

            return [
                'tipo_documento' => 'PASAPORTE',
                'numero_documento' => $numero,
                'numero_documento_norm' => $this->normalizarDocumento($numero),
            ];
        });
    }

    public function sinDocumento(): self
    {
        return $this->state(fn () => [
            'tipo_documento' => null,
            'numero_documento' => null,
            'numero_documento_norm' => null,
        ]);
    }

    public function favorito(): self
    {
        return $this->state(fn () => ['favorito' => true]);
    }

    public function noFavorito(): self
    {
        return $this->state(fn () => ['favorito' => false]);
    }

    private function documentoDni(): string
    {
        $letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
        $numero = fake()->numberBetween(10000000, 99999999);

        return $numero . $letras[$numero % 23];
    }

    private function documentoPasaporte(): string
    {
        $longitud = fake()->numberBetween(6, 15);
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $resultado = strtoupper(fake()->randomLetter()) . strtoupper(fake()->randomLetter());
        $resultado .= str_pad((string) fake()->numberBetween(1000, 9999), 4, '0', STR_PAD_LEFT);

        while (strlen($resultado) < $longitud) {
            $resultado .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $resultado;
    }

    private function normalizarDocumento(string $documento): string
    {
        return strtoupper(str_replace(' ', '', $documento));
    }

    private function fechaAdulto(): string
    {
        return Carbon::now()
            ->subYears(fake()->numberBetween(16, 80))
            ->subDays(fake()->numberBetween(0, 364))
            ->format('Y-m-d');
    }

    private function fechaNino(): string
    {
        return Carbon::now()
            ->subYears(fake()->numberBetween(3, 15))
            ->subDays(fake()->numberBetween(0, 364))
            ->format('Y-m-d');
    }

    private function fechaBebe(): string
    {
        return Carbon::now()
            ->subMonths(fake()->numberBetween(0, 23))
            ->subDays(fake()->numberBetween(0, 30))
            ->format('Y-m-d');
    }
}
