<?php

namespace Database\Seeders;

use App\Models\Asiento;
use App\Models\Vuelo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class VuelosConAsientosSeeder extends Seeder
{
    private const FILAS = 30;

    private const COLUMNAS = ['A', 'B', 'C', 'D', 'E', 'F'];

    public function run(): void
    {
        $cantidadPares = 10;

        for ($i = 0; $i < $cantidadPares; $i++) {
            $indiceIda = ($i * 2) + 1;
            $indiceVuelta = $indiceIda + 1;

            $vueloIdaData = $this->generarDatosVuelo($indiceIda);
            $vueloVueltaData = $this->generarDatosVuelta($vueloIdaData, $indiceVuelta);

            $vueloIda = Vuelo::create($vueloIdaData);
            $vueloVuelta = Vuelo::create($vueloVueltaData);

            $this->crearAsientosParaVuelo((int) $vueloIda->id);
            $this->crearAsientosParaVuelo((int) $vueloVuelta->id);
        }
    }

    private function generarDatosVuelo(int $indice): array
    {
        $aeropuertos = [
            ['codigo' => 'BCN', 'nombre' => 'Barcelona', 'schengen' => true],
            ['codigo' => 'MAD', 'nombre' => 'Madrid', 'schengen' => true],
            ['codigo' => 'CDG', 'nombre' => 'Paris', 'schengen' => true],
            ['codigo' => 'FCO', 'nombre' => 'Roma', 'schengen' => true],
            ['codigo' => 'AMS', 'nombre' => 'Amsterdam', 'schengen' => true],
            ['codigo' => 'LHR', 'nombre' => 'Londres', 'schengen' => false],
            ['codigo' => 'DUB', 'nombre' => 'Dublin', 'schengen' => false],
            ['codigo' => 'ZRH', 'nombre' => 'Zurich', 'schengen' => false],
        ];

        $origen = $aeropuertos[array_rand($aeropuertos)];

        do {
            $destino = $aeropuertos[array_rand($aeropuertos)];
        } while ($destino['codigo'] === $origen['codigo']);

        $fechaSalida = Carbon::parse('2027-01-01')
            ->addDays(random_int(1, 365))
            ->setTime(random_int(5, 22), [0, 15, 30, 45][array_rand([0, 15, 30, 45])], 0);

        $duracionMinutos = random_int(70, 230);
        $fechaLlegada = (clone $fechaSalida)->addMinutes($duracionMinutos);

        $esSchengen = $origen['schengen'] && $destino['schengen'];

        return [
            'codigo' => sprintf('PLN%03d%s', $indice, random_int(10, 99)),
            'origen' => $origen['nombre'],
            'destino' => $destino['nombre'],
            'fecha_salida' => $fechaSalida,
            'fecha_llegada' => $fechaLlegada,
            'es_schengen' => $esSchengen,
            'precio_base' => random_int(29, 199),
        ];
    }

    private function generarDatosVuelta(array $vueloIdaData, int $indice): array
    {
        $horaIda = Carbon::parse($vueloIdaData['fecha_salida'])->format('H:i');

        $fechaSalidaVuelta = Carbon::parse($vueloIdaData['fecha_salida'])
            ->addDays(random_int(1, 14));

        do {
            $fechaSalidaVuelta->setTime(random_int(5, 22), [0, 15, 30, 45][array_rand([0, 15, 30, 45])], 0);
        } while ($fechaSalidaVuelta->format('H:i') === $horaIda);

        $duracionIda = Carbon::parse($vueloIdaData['fecha_llegada'])
            ->diffInMinutes(Carbon::parse($vueloIdaData['fecha_salida']));

        $fechaLlegadaVuelta = (clone $fechaSalidaVuelta)->addMinutes(max(60, $duracionIda));

        return [
            'codigo' => sprintf('PLN%03d%s', $indice, random_int(10, 99)),
            'origen' => $vueloIdaData['destino'],
            'destino' => $vueloIdaData['origen'],
            'fecha_salida' => $fechaSalidaVuelta,
            'fecha_llegada' => $fechaLlegadaVuelta,
            'es_schengen' => $vueloIdaData['es_schengen'],
            'precio_base' => $vueloIdaData['precio_base'],
        ];
    }

    private function crearAsientosParaVuelo(int $vueloId): void
    {
        $asientos = [];
        $ahora = Carbon::now();

        for ($fila = 1; $fila <= self::FILAS; $fila++) {
            foreach (self::COLUMNAS as $columna) {
                $asientos[] = [
                    'vuelo_id' => $vueloId,
                    'codigo' => $fila . $columna,
                    'tipo' => $this->resolverTipoAsiento($fila),
                    'ocupado' => false,
                    'created_at' => $ahora,
                ];
            }
        }

        Asiento::insert($asientos);
    }

    private function resolverTipoAsiento(int $fila): string
    {
        if ($fila <= 3) {
            return 'planit_plus';
        }

        if ($fila <= 8) {
            return 'planit_one';
        }

        if ($fila >= 29) {
            return 'planit_space';
        }

        return 'estandar';
    }
}
