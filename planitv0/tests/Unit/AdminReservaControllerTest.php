<?php

use App\Http\Controllers\AdminReservaController;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', ':memory:');

    Schema::dropIfExists('usuarios');
    Schema::dropIfExists('roles');

    Schema::create('roles', function (Blueprint $table) {
        $table->id();
        $table->string('nombre')->unique();
        $table->timestamps();
    });

    Schema::create('usuarios', function (Blueprint $table) {
        $table->id();
        $table->string('nombre');
        $table->string('apellidos');
        $table->string('email')->unique();
        $table->string('password');
        $table->unsignedBigInteger('rol_id')->nullable();
        $table->boolean('esta_verificado')->default(true);
        $table->boolean('esta_activo')->default(true);
        $table->timestamp('deleted_at')->nullable();
        $table->timestamp('anonymized_at')->nullable();
        $table->integer('intentos_fallidos')->default(0);
        $table->timestamp('bloqueado_hasta')->nullable();
        $table->string('remember_token')->nullable();
        $table->timestamps();
    });

});

test('enmascara el documento de forma protegida', function () {
    $controlador = new AdminReservaController();
    $metodo = new ReflectionMethod(AdminReservaController::class, 'mascarDocumento');
    $metodo->setAccessible(true);

    $resultado = $metodo->invoke($controlador, '12345678Z');

    expect($resultado)->toBe('12*****8Z');
});

test('detecta correctamente si el usuario autenticado es superadmin', function () {
    $rolSuperadmin = Rol::query()->create(['nombre' => 'superadmin']);
    $rolAdmin = Rol::query()->create(['nombre' => 'admin']);

    $superadmin = Usuario::query()->create([
        'nombre' => 'Root',
        'apellidos' => 'Admin',
        'email' => 'root-admin@planit.test',
        'password' => Hash::make('Clave1'),
        'rol_id' => $rolSuperadmin->id,
        'esta_verificado' => true,
        'esta_activo' => true,
    ]);

    $admin = Usuario::query()->create([
        'nombre' => 'Ana',
        'apellidos' => 'Admin',
        'email' => 'ana-admin@planit.test',
        'password' => Hash::make('Clave1'),
        'rol_id' => $rolAdmin->id,
        'esta_verificado' => true,
        'esta_activo' => true,
    ]);

    $controlador = new AdminReservaController();
    $metodo = new ReflectionMethod(AdminReservaController::class, 'usuarioActualEsSuperadmin');
    $metodo->setAccessible(true);

    Auth::login($superadmin);
    expect($metodo->invoke($controlador))->toBeTrue();

    Auth::login($admin);
    expect($metodo->invoke($controlador))->toBeFalse();
});

test('enmascara documento corto con asteriscos', function () {
    $controlador = new AdminReservaController();
    $metodo = new ReflectionMethod(AdminReservaController::class, 'mascarDocumento');
    $metodo->setAccessible(true);

    $resultado = $metodo->invoke($controlador, 'AB12');

    expect($resultado)->toBe('****');
});
