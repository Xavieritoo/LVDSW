<?php

use App\Http\Controllers\PasajeroController;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class);

test('valida que en ida y vuelta existan datos de vuelo de regreso', function () {
    $controlador = new PasajeroController();
    $metodo = new ReflectionMethod(PasajeroController::class, 'validarCamposVueltaVuelta');
    $metodo->setAccessible(true);

    $validator = validator([], []);
    $request = Request::create('/pasajeros', 'GET', [
        'tipo_viaje' => 'ida_vuelta',
    ]);

    $metodo->invoke($controlador, $validator, $request);

    expect($validator->errors()->has('vuelo_vuelta_id'))->toBeTrue()
        ->and($validator->errors()->has('plan_vuelta'))->toBeTrue()
        ->and($validator->errors()->has('precio_vuelta'))->toBeTrue();
});

test('valida que haya al menos un pasajero', function () {
    $controlador = new PasajeroController();
    $metodo = new ReflectionMethod(PasajeroController::class, 'validarMinimoPasajeros');
    $metodo->setAccessible(true);

    $validator = validator([], []);
    $metodo->invoke($controlador, $validator, [
        'adultos' => 0,
        'menores' => 0,
        'infantes' => 0,
    ], 'Debes seleccionar al menos un pasajero.');

    expect($validator->errors()->has('adultos'))->toBeTrue();
});

test('valida la regla de negocio de infantes con adultos', function () {
    $controlador = new PasajeroController();
    $metodo = new ReflectionMethod(PasajeroController::class, 'validarInfantesConAdultos');
    $metodo->setAccessible(true);

    $validator = validator([], []);
    $metodo->invoke($controlador, $validator, [
        'adultos' => 1,
        'menores' => 0,
        'infantes' => 2,
    ]);

    expect($validator->errors()->has('infantes'))->toBeTrue();
});
