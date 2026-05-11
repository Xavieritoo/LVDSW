<?php

use App\Http\Controllers\SuperAdminUsuarioController;
use App\Http\Middleware\AdminRoleMiddleware;
use App\Http\Middleware\SuperAdminRoleMiddleware;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

function crearRolesBaseParaSuperAdminTests(): array
{
    $usuario = Rol::query()->create(['nombre' => 'usuario']);
    $admin = Rol::query()->create(['nombre' => 'admin']);
    $superadmin = Rol::query()->create(['nombre' => 'superadmin']);

    return [
        'usuario' => $usuario,
        'admin' => $admin,
        'superadmin' => $superadmin,
    ];
}

test('superadmin crea usuario correctamente', function () {
    $roles = crearRolesBaseParaSuperAdminTests();
    $controlador = app(SuperAdminUsuarioController::class);

    $request = Request::create('/superadmin/usuarios', 'POST', [
        'nombre' => 'Marta',
        'apellidos' => 'Sanchez',
        'email' => 'marta@planit.test',
        'password' => 'Clave1',
        'password_confirmation' => 'Clave1',
        'rol_id' => $roles['admin']->id,
        'esta_activo' => 1,
        'esta_verificado' => 1,
    ]);

    $response = $controlador->store($request);

    $usuario = Usuario::query()->where('email', 'marta@planit.test')->first();

    expect($response->isRedirect())->toBeTrue()
        ->and($usuario)->not->toBeNull()
        ->and((int) $usuario->rol_id)->toBe((int) $roles['admin']->id)
        ->and(Hash::check('Clave1', (string) $usuario->password))->toBeTrue();
});

test('superadmin actualiza datos de usuario', function () {
    $roles = crearRolesBaseParaSuperAdminTests();
    $controlador = app(SuperAdminUsuarioController::class);

    $usuario = Usuario::query()->create([
        'nombre' => 'Luis',
        'apellidos' => 'Diaz',
        'email' => 'luis@planit.test',
        'password' => Hash::make('Clave1'),
        'rol_id' => $roles['usuario']->id,
        'esta_verificado' => true,
        'esta_activo' => true,
    ]);

    $request = Request::create('/superadmin/usuarios/' . $usuario->id, 'PUT', [
        'nombre' => 'Luis Editado',
        'apellidos' => 'Diaz Editado',
        'email' => 'luis.editado@planit.test',
        'password' => '',
        'password_confirmation' => '',
        'rol_id' => $roles['admin']->id,
        'esta_activo' => 1,
        'esta_verificado' => 1,
    ]);

    $response = $controlador->update($request, $usuario);
    $usuario->refresh();

    expect($response->isRedirect())->toBeTrue()
        ->and($usuario->nombre)->toBe('Luis Editado')
        ->and($usuario->apellidos)->toBe('Diaz Editado')
        ->and($usuario->email)->toBe('luis.editado@planit.test')
        ->and((int) $usuario->rol_id)->toBe((int) $roles['admin']->id);
});

test('superadmin elimina usuario correctamente', function () {
    $roles = crearRolesBaseParaSuperAdminTests();
    $controlador = app(SuperAdminUsuarioController::class);

    $superadmin = Usuario::query()->create([
        'nombre' => 'Root',
        'apellidos' => 'Admin',
        'email' => 'root@planit.test',
        'password' => Hash::make('Clave1'),
        'rol_id' => $roles['superadmin']->id,
        'esta_verificado' => true,
        'esta_activo' => true,
    ]);

    $usuario = Usuario::query()->create([
        'nombre' => 'Carlos',
        'apellidos' => 'User',
        'email' => 'carlos@planit.test',
        'password' => Hash::make('Clave1'),
        'rol_id' => $roles['usuario']->id,
        'esta_verificado' => true,
        'esta_activo' => true,
    ]);

    Auth::login($superadmin);

    $response = $controlador->destroy($usuario);

    expect($response->isRedirect())->toBeTrue()
        ->and(Usuario::query()->find($usuario->id))->toBeNull();
});

test('superadmin middleware permite solo rol superadmin', function () {
    $roles = crearRolesBaseParaSuperAdminTests();

    $superadmin = Usuario::query()->create([
        'nombre' => 'Root',
        'apellidos' => 'Admin',
        'email' => 'root@planit.test',
        'password' => Hash::make('Clave1'),
        'rol_id' => $roles['superadmin']->id,
        'esta_verificado' => true,
        'esta_activo' => true,
    ]);

    $admin = Usuario::query()->create([
        'nombre' => 'Ana',
        'apellidos' => 'Admin',
        'email' => 'admin@planit.test',
        'password' => Hash::make('Clave1'),
        'rol_id' => $roles['admin']->id,
        'esta_verificado' => true,
        'esta_activo' => true,
    ]);

    $middleware = new SuperAdminRoleMiddleware();
    $request = Request::create('/fake', 'GET');

    Auth::login($superadmin);
    $responseOk = $middleware->handle($request, fn () => new Response('ok', 200));
    expect($responseOk->getStatusCode())->toBe(200);

    Auth::login($admin);
    expect(fn () => $middleware->handle($request, fn () => new Response('ok', 200)))
        ->toThrow(HttpException::class);
});

test('admin middleware permite admin y superadmin pero bloquea usuario', function () {
    $roles = crearRolesBaseParaSuperAdminTests();

    $superadmin = Usuario::query()->create([
        'nombre' => 'Root',
        'apellidos' => 'Admin',
        'email' => 'root2@planit.test',
        'password' => Hash::make('Clave1'),
        'rol_id' => $roles['superadmin']->id,
        'esta_verificado' => true,
        'esta_activo' => true,
    ]);

    $admin = Usuario::query()->create([
        'nombre' => 'Ana',
        'apellidos' => 'Admin',
        'email' => 'admin2@planit.test',
        'password' => Hash::make('Clave1'),
        'rol_id' => $roles['admin']->id,
        'esta_verificado' => true,
        'esta_activo' => true,
    ]);

    $usuario = Usuario::query()->create([
        'nombre' => 'Pepe',
        'apellidos' => 'Usuario',
        'email' => 'usuario@planit.test',
        'password' => Hash::make('Clave1'),
        'rol_id' => $roles['usuario']->id,
        'esta_verificado' => true,
        'esta_activo' => true,
    ]);

    $middleware = new AdminRoleMiddleware();
    $request = Request::create('/fake', 'GET');

    Auth::login($admin);
    expect($middleware->handle($request, fn () => new Response('ok', 200))->getStatusCode())->toBe(200);

    Auth::login($superadmin);
    expect($middleware->handle($request, fn () => new Response('ok', 200))->getStatusCode())->toBe(200);

    Auth::login($usuario);
    expect(fn () => $middleware->handle($request, fn () => new Response('ok', 200)))
        ->toThrow(HttpException::class);
});
