<?php

use App\Http\Controllers\CompraController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');

    Schema::dropIfExists('vuelos');
    Schema::create('vuelos', function (Blueprint $table) {
        $table->id();
        $table->decimal('precio_base', 10, 2)->default(0);
        $table->decimal('precio', 10, 2)->default(0);
        $table->timestamps();
    });
});

test('calcula el precio total en resumen con ida y vuelta y equipajes', function () {
    $idaId    = DB::table('vuelos')->insertGetId(['precio_base' => 100, 'precio' => 100, 'created_at' => now(), 'updated_at' => now()]);
    $vueltaId = DB::table('vuelos')->insertGetId(['precio_base' => 80,  'precio' => 80,  'created_at' => now(), 'updated_at' => now()]);

    session()->put('reserva', [
        'tipo_viaje' => 'ida_vuelta',
        'correo_contacto' => 'demo@planit.test',
        'pasajeros' => [
            'adultos' => [
                ['nombre' => 'Ana', 'apellidos' => 'Lopez'],
            ],
            'menores' => [
                ['nombre' => 'Leo', 'apellidos' => 'Lopez'],
            ],
            'infantes' => [],
        ],
        'equipajes' => [
            'adulto_0' => [
                'ida' => ['20' => 1],
                'vuelta' => ['25' => 1],
            ],
            'menor_0' => [
                'ida' => ['30' => 1],
            ],
        ],
        'seleccion' => [
            'vuelo_ida_id'    => $idaId,
            'vuelo_vuelta_id' => $vueltaId,
            'precio_ida'      => 100,
            'precio_vuelta'   => 80,
            'plan_ida'        => 'PLANIT EASY',
            'plan_vuelta'     => 'PLANIT EASY',
        ],
    ]);

    $respuesta = app(CompraController::class)->resumen();
    $datos = $respuesta->getData();

    expect((float) $datos['precioBase'])->toBe(360.0)
        ->and((float) $datos['precioEquipajes'])->toBe(390.0)
        ->and((float) $datos['totalFinal'])->toBe(750.0);
});

test('ignora pesos de equipaje no permitidos en el calculo', function () {
    $idaId = DB::table('vuelos')->insertGetId(['precio_base' => 90, 'precio' => 90, 'created_at' => now(), 'updated_at' => now()]);

    session()->put('reserva', [
        'tipo_viaje' => 'solo_ida',
        'correo_contacto' => 'demo@planit.test',
        'pasajeros' => [
            'adultos' => [
                ['nombre' => 'Ana', 'apellidos' => 'Lopez'],
            ],
            'menores' => [],
            'infantes' => [],
        ],
        'equipajes' => [
            'adulto_0' => [
                'ida' => ['99' => 1, '20' => 1],
            ],
        ],
        'seleccion' => [
            'vuelo_ida_id' => $idaId,
            'precio_ida'   => 90,
            'plan_ida'     => 'PLANIT EASY',
        ],
    ]);

    $respuesta = app(CompraController::class)->resumen();
    $datos = $respuesta->getData();

    expect((float) $datos['precioEquipajes'])->toBe(120.0)
        ->and((float) $datos['totalFinal'])->toBe(210.0);
});
