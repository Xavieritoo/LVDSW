<?php

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('reserva_pasajeros');
    Schema::dropIfExists('reservas');
    Schema::dropIfExists('usuarios');
    Schema::dropIfExists('roles');

    Schema::create('roles', function (Blueprint $table) {
        $table->id();
        $table->string('nombre', 50);
        $table->timestamps();
    });

    Schema::create('usuarios', function (Blueprint $table) {
        $table->id();
        $table->string('nombre', 100);
        $table->string('apellidos', 150);
        $table->string('email', 150)->unique();
        $table->string('password');
        $table->unsignedBigInteger('rol_id')->nullable();
        $table->boolean('esta_verificado')->default(false);
        $table->boolean('esta_activo')->default(true);
        $table->timestamp('deleted_at')->nullable();
        $table->timestamp('anonymized_at')->nullable();
        $table->unsignedInteger('intentos_fallidos')->default(0);
        $table->timestamp('bloqueado_hasta')->nullable();
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('reservas', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->timestamp('enlazada_en')->nullable();
        $table->string('localizador', 20);
        $table->string('origen', 100);
        $table->string('destino', 100);
        $table->timestamp('fecha_salida');
        $table->timestamp('fecha_llegada')->nullable();
        $table->string('estado', 40)->default('confirmada');
        $table->string('plan_tarifa', 40)->default('planit_easy');
        $table->string('email_contacto', 150);
        $table->timestamp('checkin_disponible_desde')->nullable();
        $table->timestamp('checkin_realizado_en')->nullable();
        $table->string('checkin_estado', 40)->nullable();
        $table->boolean('tarjetas_emitidas')->default(false);
        $table->timestamp('checkin_correo_intentado_en')->nullable();
        $table->string('checkin_correo_estado', 40)->nullable();
        $table->string('checkin_correo_error', 255)->nullable();
        $table->string('equipaje_resumen', 255)->nullable();
        $table->string('asientos_resumen', 255)->nullable();
        $table->string('meteorologia_resumen', 255)->nullable();
        $table->timestamps();
    });

    Schema::create('reserva_pasajeros', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('reserva_id');
        $table->string('nombre', 100);
        $table->string('apellidos', 150);
        $table->string('tipo_documento', 30)->nullable();
        $table->string('numero_documento', 20)->nullable();
        $table->string('numero_documento_norm', 20)->nullable();
        $table->date('fecha_nacimiento')->nullable();
        $table->timestamp('checkin_confirmado_en')->nullable();
        $table->string('asiento_codigo', 10)->nullable();
        $table->timestamp('asiento_asignado_en')->nullable();
        $table->timestamps();
    });

    Schema::dropIfExists('vuelos');
    Schema::create('vuelos', function (Blueprint $table) {
        $table->id();
        $table->string('origen', 100)->nullable();
        $table->string('destino', 100)->nullable();
        $table->string('tipo_tarifa', 50)->nullable();
        $table->boolean('activo')->default(true);
        $table->timestamp('fecha_salida')->nullable();
        $table->timestamps();
    });
});

function crearUsuarioApi(array $override = []): Usuario
{
    $rol = Rol::firstOrCreate(['nombre' => 'usuario']);

    return Usuario::create(array_merge([
        'nombre' => 'Api',
        'apellidos' => 'Tester',
        'email' => 'api.tester@example.com',
        'password' => Hash::make('Clave1'),
        'rol_id' => $rol->id,
        'esta_verificado' => true,
        'esta_activo' => true,
        'intentos_fallidos' => 0,
        'bloqueado_hasta' => null,
    ], $override));
}

function crearReservaApi(Usuario $usuario, array $override = []): int
{
    $reserva = array_merge([
        'user_id' => $usuario->id,
        'enlazada_en' => null,
        'localizador' => 'API' . strtoupper(substr((string) md5((string) microtime(true)), 0, 6)),
        'origen' => 'Madrid',
        'destino' => 'Paris',
        'fecha_salida' => now()->addDays(2),
        'fecha_llegada' => now()->addDays(2)->addHours(2),
        'estado' => 'confirmada',
        'plan_tarifa' => 'planit_easy',
        'email_contacto' => $usuario->email,
        'checkin_disponible_desde' => now()->subHour(),
        'checkin_realizado_en' => null,
        'checkin_estado' => 'pendiente',
        'tarjetas_emitidas' => false,
        'checkin_correo_intentado_en' => null,
        'checkin_correo_estado' => null,
        'checkin_correo_error' => null,
        'equipaje_resumen' => null,
        'asientos_resumen' => null,
        'meteorologia_resumen' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ], $override);

    return (int) DB::table('reservas')->insertGetId($reserva);
}

test('api login devuelve 200 y token valido', function () {
    $usuario = crearUsuarioApi();

    $respuesta = $this->postJson('/api/login', [
        'email' => $usuario->email,
        'username' => $usuario->email,
        'password' => 'Clave1',
    ]);

    $respuesta
        ->assertOk()
        ->assertJsonStructure([
            'message',
            'token',
            'token_type',
            'expires_in',
            'user' => ['id', 'email'],
        ]);
});

test('api login devuelve 401 con credenciales invalidas', function () {
    $usuario = crearUsuarioApi();

    $this->postJson('/api/login', [
        'email' => $usuario->email,
        'username' => $usuario->email,
        'password' => 'incorrecta',
    ])
        ->assertStatus(401)
        ->assertJson([
            'message' => 'Credenciales invalidas.',
        ]);
});

test('api login devuelve 423 para usuario temporalmente bloqueado', function () {
    $usuario = crearUsuarioApi([
        'bloqueado_hasta' => now()->addMinutes(5),
    ]);

    $this->postJson('/api/login', [
        'email' => $usuario->email,
        'username' => $usuario->email,
        'password' => 'Clave1',
    ])
        ->assertStatus(423)
        ->assertJson([
            'message' => 'Cuenta temporalmente bloqueada.',
        ]);
});

test('api login prioriza bloqueo aunque la contrasena sea incorrecta', function () {
    $usuario = crearUsuarioApi([
        'email' => 'bloqueado.prioridad@example.com',
        'bloqueado_hasta' => now()->addMinutes(5),
    ]);

    $this->postJson('/api/login', [
        'email' => $usuario->email,
        'username' => $usuario->email,
        'password' => 'incorrecta',
    ])
        ->assertStatus(423)
        ->assertJson([
            'message' => 'Cuenta temporalmente bloqueada.',
        ]);
});

test('api login prioriza cuenta desactivada aunque la contrasena sea incorrecta', function () {
    $usuario = crearUsuarioApi([
        'email' => 'desactivado.prioridad@example.com',
        'esta_activo' => false,
    ]);

    $this->postJson('/api/login', [
        'email' => $usuario->email,
        'username' => $usuario->email,
        'password' => 'incorrecta',
    ])
        ->assertStatus(403)
        ->assertJson([
            'message' => 'Cuenta desactivada.',
        ]);
});

test('api ruta protegida responde 401 sin token y 200 con token', function () {
    $usuario = crearUsuarioApi([
        'email' => 'auth.ok@example.com',
    ]);

    $this->getJson('/api/perfil')
        ->assertStatus(401)
        ->assertJsonStructure(['error']);

    $login = $this->postJson('/api/login', [
        'email' => $usuario->email,
        'username' => $usuario->email,
        'password' => 'Clave1',
    ])->assertOk();

    $token = (string) $login->json('token');

    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/perfil')
        ->assertOk()
        ->assertJsonPath('user.email', $usuario->email);
});

test('api vuelos devuelve 200 con listado y meta count', function () {
    DB::table('vuelos')->insert([
        'origen'      => 'Barcelona',
        'destino'     => 'Roma',
        'tipo_tarifa' => 'planit_comfort',
        'activo'      => true,
        'fecha_salida' => now()->addDays(1),
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    $this->getJson('/api/vuelos')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                ['origen', 'destino', 'tipo_tarifa'],
            ],
            'meta' => ['count'],
        ])
        ->assertJsonPath('meta.count', 1);
});

test('api reservas devuelve solo reservas del usuario autenticado', function () {
    $usuarioAuth = crearUsuarioApi([
        'email' => 'reservas.auth@example.com',
    ]);
    $otroUsuario = crearUsuarioApi([
        'email' => 'reservas.otra@example.com',
    ]);

    crearReservaApi($usuarioAuth, [
        'localizador' => 'RESAUT1',
        'origen' => 'Madrid',
        'destino' => 'Berlin',
    ]);
    crearReservaApi($otroUsuario, [
        'localizador' => 'RESOTR1',
        'origen' => 'Valencia',
        'destino' => 'Lisboa',
    ]);

    $login = $this->postJson('/api/login', [
        'email' => $usuarioAuth->email,
        'username' => $usuarioAuth->email,
        'password' => 'Clave1',
    ])->assertOk();

    $token = (string) $login->json('token');

    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/reservas')
        ->assertOk()
        ->assertJsonPath('meta.count', 1)
        ->assertJsonPath('data.0.localizador', 'RESAUT1');
});

test('api reservas detalle de reserva ajena devuelve 403', function () {
    $usuarioAuth = crearUsuarioApi([
        'email' => 'reserva.detalle.auth@example.com',
    ]);
    $otroUsuario = crearUsuarioApi([
        'email' => 'reserva.detalle.otra@example.com',
    ]);

    $idReservaAjena = crearReservaApi($otroUsuario, [
        'localizador' => 'RESAJE1',
    ]);

    $login = $this->postJson('/api/login', [
        'email' => $usuarioAuth->email,
        'username' => $usuarioAuth->email,
        'password' => 'Clave1',
    ])->assertOk();

    $token = (string) $login->json('token');

    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/reservas/' . $idReservaAjena)
        ->assertStatus(403)
        ->assertJson([
            'message' => 'No tienes permisos para acceder a esta reserva.',
        ]);
});
