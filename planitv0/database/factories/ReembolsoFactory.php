<?php

namespace Database\Factories;

use App\Models\Reembolso;
use App\Models\Reserva;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReembolsoFactory extends Factory
{
    protected $model = Reembolso::class;

    public function definition(): array
    {
        $estado = fake()->randomElement(['pendiente', 'completado', 'no_aplicable']);

        if ($estado === 'no_aplicable') {
            $cantidad = null;
        } else {
            $cantidad = fake()->randomFloat(2, 49, 399);
        }

        return [
            'reserva_id' => $this->reservaCanceladaSinReembolso(),
            'estado' => $estado,
            'cantidad' => $cantidad,
            'created_at' => now(),
        ];
    }

    public function pendiente(): self
    {
        return $this->state(fn () => [
            'reserva_id' => $this->reservaCanceladaSinReembolso(),
            'estado' => 'pendiente',
            'cantidad' => fake()->randomFloat(2, 49, 399),
        ]);
    }

    public function completado(): self
    {
        return $this->state(fn () => [
            'reserva_id' => $this->reservaCanceladaSinReembolso(),
            'estado' => 'completado',
            'cantidad' => fake()->randomFloat(2, 49, 399),
        ]);
    }

    public function parcial(): self
    {
        return $this->state(fn () => [
            'reserva_id' => $this->reservaCanceladaSinReembolso('cancelada_usuario'),
            'estado' => 'pendiente',
            'cantidad' => fake()->randomFloat(2, 10, 100),
        ]);
    }

    public function noAplicable(): self
    {
        return $this->state(fn () => [
            'reserva_id' => $this->reservaCanceladaSinReembolso(),
            'estado' => 'no_aplicable',
            'cantidad' => null,
        ]);
    }

    private function reservaCanceladaSinReembolso(?string $estadoReserva = null): int
    {
        $query = Reserva::query()
            ->whereIn('estado', ['cancelada_usuario', 'cancelada_aerolinea'])
            ->whereDoesntHave('reembolso');

        if ($estadoReserva !== null) {
            $query->where('estado', $estadoReserva);
        }

        $id = $query->inRandomOrder()->value('id');

        if ($id) {
            return (int) $id;
        }

        if ($estadoReserva === 'cancelada_usuario') {
            return (int) Reserva::factory()->canceladasUsuario()->create()->id;
        }

        if (fake()->boolean()) {
            return (int) Reserva::factory()->canceladasUsuario()->create()->id;
        }

        return (int) Reserva::factory()->canceladasAerolinea()->create()->id;
    }
}
