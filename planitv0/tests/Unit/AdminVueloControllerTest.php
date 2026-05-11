<?php

use App\Http\Controllers\AdminVueloController;
use App\Models\Reserva;
use App\Models\Vuelo;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');

    Schema::dropIfExists('reservas');
    Schema::dropIfExists('vuelos');

    Schema::create('vuelos', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('origen_ciudad_id')->nullable();
        $table->unsignedBigInteger('destino_ciudad_id')->nullable();
        $table->string('origen')->nullable();
        $table->string('destino')->nullable();
        $table->dateTime('fecha_salida')->nullable();
        $table->dateTime('fecha_llegada')->nullable();
        $table->decimal('precio', 10, 2)->nullable();
        $table->integer('asientos_disponibles')->nullable();
        $table->boolean('es_schengen')->default(false);
        $table->boolean('activo')->default(true);
        $table->timestamps();
    });

    Schema::create('reservas', function (Blueprint $table) {
        $table->id();
        $table->string('plan_tarifa')->nullable();
        $table->unsignedBigInteger('vuelo_id')->nullable();
        $table->unsignedBigInteger('vuelo_vuelta_id')->nullable();
        $table->string('estado')->nullable();
        $table->timestamps();
    });
});

test('detecta reservas confirmadas asociadas al vuelo', function () {
    $controlador = new AdminVueloController();
    $metodo = new ReflectionMethod(AdminVueloController::class, 'vueloTieneReservasConfirmadas');
    $metodo->setAccessible(true);

    $vuelo = Vuelo::query()->create([
        'origen' => 'Madrid',
        'destino' => 'Paris',
    ]);

    Reserva::query()->create([
        'vuelo_id' => $vuelo->id,
        'estado' => 'confirmada',
    ]);

    $resultado = $metodo->invoke($controlador, $vuelo);

    expect($resultado)->toBeTrue();
});

test('retorna falso cuando no hay reservas confirmadas para el vuelo', function () {
    $controlador = new AdminVueloController();
    $metodo = new ReflectionMethod(AdminVueloController::class, 'vueloTieneReservasConfirmadas');
    $metodo->setAccessible(true);

    $vuelo = Vuelo::query()->create([
        'origen' => 'Madrid',
        'destino' => 'Roma',
    ]);

    Reserva::query()->create([
        'vuelo_id' => $vuelo->id,
        'estado' => 'cancelada_usuario',
    ]);

    $resultado = $metodo->invoke($controlador, $vuelo);

    expect($resultado)->toBeFalse();
});
