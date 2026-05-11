<?php

namespace Database\Factories;

use App\Models\Cancelacion;
use App\Models\Reserva;
use Illuminate\Database\Eloquent\Factories\Factory;

class CancelacionFactory extends Factory
{
    protected $model = Cancelacion::class;

    private array $motivosAerolinea = [
        'Condiciones meteorológicas adversas',
        'Retrasos operativos significativos',
        'Problemas técnicos o mecánicos en la aeronave',
        'Cancelación operativa del vuelo',
        'Restricciones operativas o de seguridad',
    ];

    private array $motivosUsuario = [
        'Cambio de planes de viaje',
        'Problemas personales o familiares',
        'Motivos laborales o académicos',
        'Problemas de salud',
        'Error en la reserva',
        'Otros motivos personales',
    ];

    public function definition(): array
    {
        $tipo = fake()->randomElement(['usuario', 'aerolinea']);

        if ($tipo === 'aerolinea') {
            $motivo = fake()->randomElement($this->motivosAerolinea);
        } else {
            $motivo = fake()->randomElement($this->motivosUsuario);
        }

        return [
            'reserva_id' => $this->reservaCanceladaSinCancelacion($tipo),
            'tipo' => $tipo,
            'motivo' => $motivo,
            'created_at' => now(),
        ];
    }

    public function porAerolinea(): self
    {
        return $this->state(fn () => [
            'reserva_id' => $this->reservaCanceladaSinCancelacion('aerolinea'),
            'tipo' => 'aerolinea',
            'motivo' => fake()->randomElement($this->motivosAerolinea),
        ]);
    }

    public function porUsuario(): self
    {
        return $this->state(fn () => [
            'reserva_id' => $this->reservaCanceladaSinCancelacion('usuario'),
            'tipo' => 'usuario',
            'motivo' => fake()->randomElement($this->motivosUsuario),
        ]);
    }

    private function reservaCanceladaSinCancelacion(string $tipo): int
    {
        if ($tipo === 'aerolinea') {
            $estado = 'cancelada_aerolinea';
        } else {
            $estado = 'cancelada_usuario';
        }

        $id = Reserva::query()
            ->where('estado', $estado)
            ->whereDoesntHave('cancelacion')
            ->inRandomOrder()
            ->value('id');

        if ($id) {
            return (int) $id;
        }

        if ($tipo === 'aerolinea') {
            return (int) Reserva::factory()->canceladasAerolinea()->create()->id;
        }

        return (int) Reserva::factory()->canceladasUsuario()->create()->id;
    }
}
