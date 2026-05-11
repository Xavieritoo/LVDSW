<?php

use App\Http\Controllers\CheckinController;
use App\Models\Reserva;

test('normaliza texto con tildes y espacios', function () {
    $controlador = new CheckinController();
    $metodo = new ReflectionMethod(CheckinController::class, 'normalizarTexto');
    $metodo->setAccessible(true);

    $resultado = $metodo->invoke($controlador, ' Málaga ');

    expect($resultado)->toBe('MALAGA');
});

test('valida dni correcto e incorrecto', function () {
    $controlador = new CheckinController();
    $metodo = new ReflectionMethod(CheckinController::class, 'esDniValido');
    $metodo->setAccessible(true);

    $dniCorrecto = $metodo->invoke($controlador, '12345678Z');
    $dniIncorrecto = $metodo->invoke($controlador, '1234A');

    expect($dniCorrecto)->toBeTrue();
    expect($dniIncorrecto)->toBeFalse();
});

test('valida pasaporte alfanumerico en mayusculas', function () {
    $controlador = new CheckinController();
    $metodo = new ReflectionMethod(CheckinController::class, 'esPasaporteValido');
    $metodo->setAccessible(true);

    $pasaporteCorrecto = $metodo->invoke($controlador, 'AB123456');
    $pasaporteIncorrecto = $metodo->invoke($controlador, 'ab1234');

    expect($pasaporteCorrecto)->toBeTrue();
    expect($pasaporteIncorrecto)->toBeFalse();
});

test('validar formato documento devuelve null cuando es correcto', function () {
    $controlador = new CheckinController();
    $metodo = new ReflectionMethod(CheckinController::class, 'validarFormatoDocumento');
    $metodo->setAccessible(true);

    $errorDni = $metodo->invoke($controlador, 'DNI', '12345678Z');
    $errorPasaporte = $metodo->invoke($controlador, 'PASAPORTE', 'AB123456');

    expect($errorDni)->toBeNull();
    expect($errorPasaporte)->toBeNull();
});

test('validar formato documento devuelve mensaje cuando es incorrecto', function () {
    $controlador = new CheckinController();
    $metodo = new ReflectionMethod(CheckinController::class, 'validarFormatoDocumento');
    $metodo->setAccessible(true);

    $errorDni = $metodo->invoke($controlador, 'DNI', '1234A');
    $errorPasaporte = $metodo->invoke($controlador, 'PASAPORTE', 'ab12');

    expect($errorDni)->toContain('DNI');
    expect($errorPasaporte)->toContain('pasaporte');
});

test('tipos de documento permitidos cambian segun ruta', function () {
    $controlador = new CheckinController();
    $metodo = new ReflectionMethod(CheckinController::class, 'tiposDocumentoPermitidosPorRuta');
    $metodo->setAccessible(true);

    $reservaSchengen = new Reserva([
        'origen' => 'Madrid',
        'destino' => 'Paris',
    ]);

    $reservaNoSchengen = new Reserva([
        'origen' => 'Madrid',
        'destino' => 'Londres',
    ]);

    $tiposSchengen = $metodo->invoke($controlador, $reservaSchengen);
    $tiposNoSchengen = $metodo->invoke($controlador, $reservaNoSchengen);

    expect($tiposSchengen)->toBe(['DNI', 'PASAPORTE']);
    expect($tiposNoSchengen)->toBe(['PASAPORTE']);
});

test('asigna automaticamente el primer asiento libre empezando por estandar', function () {
    $controlador = new CheckinController();
    $metodo = new ReflectionMethod(CheckinController::class, 'asignarAsientoAutomatico');
    $metodo->setAccessible(true);

    // Sin ocupados: el primero debe ser 9A (primera fila estándar)
    $asiento = $metodo->invoke($controlador, []);
    expect($asiento)->toBe('9A');

    // Con algunos estándar ocupados: salta al siguiente libre
    $asiento2 = $metodo->invoke($controlador, ['9A', '9B', '9C']);
    expect($asiento2)->toBe('9D');
});

test('lanza excepcion cuando no quedan asientos libres', function () {
    $controlador = new CheckinController();
    $metodo = new ReflectionMethod(CheckinController::class, 'asignarAsientoAutomatico');
    $metodo->setAccessible(true);

    $asientosOcupados = [];
    foreach (range(1, 30) as $fila) {
        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $columna) {
            $asientosOcupados[] = $fila . $columna;
        }
    }

    expect(fn () => $metodo->invoke($controlador, $asientosOcupados))
        ->toThrow(\RuntimeException::class);
});
