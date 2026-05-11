<?php

use App\Models\Reserva;
use App\Models\ReservaPasajero;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

if (!class_exists(\App\Services\MailService::class, false)) {
    class MailServiceFalso
    {
        public static bool $debeFallar = false;

        public function enviarEmail($to, $subject, $html)
        {
            if (self::$debeFallar) {
                throw new \Exception('Fallo SMTP simulado en test');
            }

            return true;
        }
    }

    class_alias(MailServiceFalso::class, \App\Services\MailService::class);
}

beforeEach(function () {
    Schema::dropIfExists('checkin_eventos');
    Schema::dropIfExists('reserva_pasajeros');
    Schema::dropIfExists('reservas');
    Schema::dropIfExists('historial_contrasenas');
    Schema::dropIfExists('reseteos_contrasena');
    Schema::dropIfExists('verificaciones_email');
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

    Schema::create('verificaciones_email', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->unique();
        $table->string('hash_codigo', 64);
        $table->timestamp('expira_en');
        $table->boolean('usado')->default(false);
        $table->unsignedInteger('intentos')->default(0);
        $table->timestamp('bloqueado_hasta')->nullable();
        $table->timestamp('ultimo_envio_en')->nullable();
        $table->timestamp('created_at')->nullable();
    });

    Schema::create('reseteos_contrasena', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->unique();
        $table->string('hash_token', 64);
        $table->timestamp('expira_en');
        $table->boolean('usado')->default(false);
        $table->unsignedInteger('intentos')->default(0);
        $table->timestamp('bloqueado_hasta')->nullable();
        $table->timestamp('ultimo_envio_en')->nullable();
        $table->timestamp('created_at')->nullable();
    });

    Schema::create('historial_contrasenas', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('hash_contrasena');
        $table->timestamp('created_at')->nullable();
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

    Schema::create('checkin_eventos', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('reserva_id');
        $table->unsignedBigInteger('reserva_pasajero_id')->nullable();
        $table->string('tipo', 60);
        $table->string('actor_tipo', 40)->nullable();
        $table->unsignedBigInteger('actor_user_id')->nullable();
        $table->string('actor_email', 150)->nullable();
        $table->string('descripcion', 255)->nullable();
        $table->text('meta')->nullable();
        $table->timestamp('created_at')->nullable();
    });
});

function crearUsuarioBase(array $override = []): Usuario
{
    $rol = Rol::firstOrCreate(['nombre' => 'usuario']);

    return Usuario::create(array_merge([
        'nombre' => 'Alumno',
        'apellidos' => 'Prueba',
        'email' => 'alumno@example.com',
        'password' => Hash::make('Clave1'),
        'rol_id' => $rol->id,
        'esta_verificado' => true,
        'esta_activo' => true,
    ], $override));
}

test('registro ok y email duplicado falla', function () {
    $respuestaRegistro = $this->post('/register', [
        'nombre' => 'Mario',
        'apellidos' => 'Suarez',
        'email' => 'mario@example.com',
        'password' => 'Clave1',
        'password_confirmation' => 'Clave1',
    ]);

    $respuestaRegistro->assertRedirect(route('register.verify'));
    $this->assertDatabaseHas('usuarios', ['email' => 'mario@example.com']);

    $respuestaDuplicado = $this->post('/register', [
        'nombre' => 'Mario',
        'apellidos' => 'Suarez',
        'email' => 'mario@example.com',
        'password' => 'Clave1',
        'password_confirmation' => 'Clave1',
    ]);

    $respuestaDuplicado->assertSessionHasErrors('email');
});

test('login ok y bloqueo tras intentos', function () {
    $usuario = crearUsuarioBase();

    $this->post('/login', ['email' => $usuario->email, 'password' => 'mal1'])
        ->assertSessionHasErrors('email');
    $this->post('/login', ['email' => $usuario->email, 'password' => 'mal1'])
        ->assertSessionHasErrors('email');
    $this->post('/login', ['email' => $usuario->email, 'password' => 'mal1'])
        ->assertSessionHasErrors('email');

    $usuario->refresh();
    expect($usuario->bloqueado_hasta)->not->toBeNull();

    $this->post('/login', ['email' => $usuario->email, 'password' => 'Clave1'])
        ->assertSessionHasErrors('email');

    $usuario->bloqueado_hasta = now()->subMinute();
    $usuario->save();

    $this->post('/login', ['email' => $usuario->email, 'password' => 'Clave1'])
        ->assertRedirect(route('principal'));
});

test('reset contrasena completo', function () {
    $usuario = crearUsuarioBase([
        'email' => 'reset@example.com',
        'password' => Hash::make('Vieja1'),
    ]);

    $tokenPlano = 'ABC123';

    DB::table('reseteos_contrasena')->insert([
        'user_id' => $usuario->id,
        'hash_token' => hash('sha256', $tokenPlano),
        'expira_en' => now()->addMinutes(10),
        'usado' => false,
        'intentos' => 0,
        'bloqueado_hasta' => null,
        'ultimo_envio_en' => now(),
        'created_at' => now(),
    ]);

    $this->post('/password/reset', [
        'email' => 'reset@example.com',
        'token' => $tokenPlano,
        'password' => 'Nueva1',
        'password_confirmation' => 'Nueva1',
    ])->assertRedirect(route('login'));

    $usuario->refresh();
    expect(Hash::check('Nueva1', $usuario->password))->toBeTrue();
    $this->assertDatabaseHas('reseteos_contrasena', [
        'user_id' => $usuario->id,
        'usado' => 1,
    ]);
    $this->assertDatabaseCount('historial_contrasenas', 1);
});

test('checkin con datos validos confirma', function () {
    $usuario = crearUsuarioBase(['email' => 'checkinok@example.com']);

    $reserva = Reserva::create([
        'user_id' => $usuario->id,
        'localizador' => 'LOC123',
        'origen' => 'Madrid',
        'destino' => 'Paris',
        'fecha_salida' => now()->addHours(8),
        'fecha_llegada' => now()->addHours(10),
        'estado' => 'confirmada',
        'plan_tarifa' => 'planit_easy',
        'email_contacto' => $usuario->email,
        'checkin_disponible_desde' => now()->subHour(),
        'checkin_estado' => null,
        'tarjetas_emitidas' => false,
    ]);

    $pasajero = ReservaPasajero::create([
        'reserva_id' => $reserva->id,
        'nombre' => 'Ana',
        'apellidos' => 'Lopez',
    ]);

    $this->actingAs($usuario)
        ->post('/area-personal/mis-reservas/' . $reserva->id . '/checkin/confirmar', [
            'pasajero_' . $pasajero->id => [
                'nombre' => 'Ana',
                'apellidos' => 'Lopez',
                'fecha_nacimiento' => '1990-01-01',
                'tipo_documento' => 'DNI',
                'numero_documento' => '12345678Z',
                'asiento_codigo' => '12A',
                'equipaje_extra' => 1,
            ],
        ])
        ->assertRedirect(route('mis-reservas.index', ['tab' => 'proximas']));

    $reserva->refresh();
    expect($reserva->checkin_estado)->toBe('confirmada');
    expect($reserva->tarjetas_emitidas)->toBeTrue();
});

test('checkin con asiento duplicado falla', function () {
    $usuario = crearUsuarioBase(['email' => 'checkinfallo@example.com']);

    $reserva = Reserva::create([
        'user_id' => $usuario->id,
        'localizador' => 'LOC456',
        'origen' => 'Madrid',
        'destino' => 'Paris',
        'fecha_salida' => now()->addHours(8),
        'fecha_llegada' => now()->addHours(10),
        'estado' => 'confirmada',
        'plan_tarifa' => 'planit_easy',
        'email_contacto' => $usuario->email,
        'checkin_disponible_desde' => now()->subHour(),
        'checkin_estado' => null,
        'tarjetas_emitidas' => false,
    ]);

    $pasajero1 = ReservaPasajero::create([
        'reserva_id' => $reserva->id,
        'nombre' => 'Ana',
        'apellidos' => 'Lopez',
    ]);
    $pasajero2 = ReservaPasajero::create([
        'reserva_id' => $reserva->id,
        'nombre' => 'Luis',
        'apellidos' => 'Perez',
    ]);

    $this->actingAs($usuario)
        ->from('/area-personal/mis-reservas/' . $reserva->id . '/checkin')
        ->post('/area-personal/mis-reservas/' . $reserva->id . '/checkin/confirmar', [
            'pasajero_' . $pasajero1->id => [
                'nombre' => 'Ana',
                'apellidos' => 'Lopez',
                'fecha_nacimiento' => '1990-01-01',
                'tipo_documento' => 'DNI',
                'numero_documento' => '12345678Z',
                'asiento_codigo' => '12A',
                'equipaje_extra' => 0,
            ],
            'pasajero_' . $pasajero2->id => [
                'nombre' => 'Luis',
                'apellidos' => 'Perez',
                'fecha_nacimiento' => '1991-01-01',
                'tipo_documento' => 'DNI',
                'numero_documento' => '87654321X',
                'asiento_codigo' => '12A',
                'equipaje_extra' => 0,
            ],
        ])
        ->assertSessionHasErrors('checkin');

    $reserva->refresh();
    expect($reserva->checkin_estado)->toBeNull();
});

test('ruta privada sin auth redirige', function () {
    $this->get('/area-personal')->assertRedirect(route('login'));
});

test('registro valida contrasena debil', function () {
    $respuesta = $this->post('/register', [
        'nombre' => 'Mario',
        'apellidos' => 'Suarez',
        'email' => 'debil@example.com',
        'password' => 'aaaaa',
        'password_confirmation' => 'aaaaa',
    ]);

    $respuesta->assertSessionHasErrors('password');
});

test('registro valida confirmacion de contrasena distinta', function () {
    $respuesta = $this->post('/register', [
        'nombre' => 'Mario',
        'apellidos' => 'Suarez',
        'email' => 'confirmacion@example.com',
        'password' => 'Clave1',
        'password_confirmation' => 'Otra1',
    ]);

    $respuesta->assertSessionHasErrors('password');
});

test('registro asigna siempre rol usuario', function () {
    $rolAdmin = Rol::create(['nombre' => 'admin']);
    $rolUsuario = Rol::create(['nombre' => 'usuario']);

    $this->post('/register', [
        'nombre' => 'Mario',
        'apellidos' => 'Suarez',
        'email' => 'rol@example.com',
        'password' => 'Clave1',
        'password_confirmation' => 'Clave1',
    ])->assertRedirect(route('register.verify'));

    $usuario = Usuario::where('email', 'rol@example.com')->firstOrFail();

    expect($usuario->rol_id)->toBe($rolUsuario->id);
    expect($usuario->rol_id)->not->toBe($rolAdmin->id);
});

test('registro reactiva usuario inactivo o eliminado', function () {
    $rolUsuario = Rol::create(['nombre' => 'usuario']);

    $usuarioViejo = Usuario::create([
        'nombre' => 'Viejo',
        'apellidos' => 'Usuario',
        'email' => 'reactivar@example.com',
        'password' => Hash::make('Antigua1'),
        'rol_id' => $rolUsuario->id,
        'esta_verificado' => false,
        'esta_activo' => false,
        'deleted_at' => now()->subDay(),
        'anonymized_at' => now()->subDay(),
    ]);

    $this->post('/register', [
        'nombre' => 'Nuevo',
        'apellidos' => 'Nombre',
        'email' => 'reactivar@example.com',
        'password' => 'Clave1',
        'password_confirmation' => 'Clave1',
    ])->assertRedirect(route('register.verify'));

    $usuarioViejo->refresh();

    expect($usuarioViejo->nombre)->toBe('Nuevo');
    expect($usuarioViejo->esta_activo)->toBeTrue();
    expect($usuarioViejo->deleted_at)->toBeNull();
    expect($usuarioViejo->anonymized_at)->toBeNull();
    expect(Hash::check('Clave1', $usuarioViejo->password))->toBeTrue();
    expect(Usuario::where('email', 'reactivar@example.com')->count())->toBe(1);
});

test('login usuario no verificado no puede entrar', function () {
    $usuario = crearUsuarioBase([
        'email' => 'noverificado@example.com',
        'esta_verificado' => false,
    ]);

    $this->post('/login', ['email' => $usuario->email, 'password' => 'Clave1'])
        ->assertSessionHasErrors('email');
});

test('login usuario desactivado no puede entrar', function () {
    $usuario = crearUsuarioBase([
        'email' => 'desactivado@example.com',
        'esta_activo' => false,
    ]);

    $this->post('/login', ['email' => $usuario->email, 'password' => 'Clave1'])
        ->assertSessionHasErrors('email');
});

test('login correcto reinicia intentos y bloqueo', function () {
    $usuario = crearUsuarioBase([
        'email' => 'resetlogin@example.com',
        'intentos_fallidos' => 2,
        'bloqueado_hasta' => now()->subMinute(),
    ]);

    $this->post('/login', ['email' => $usuario->email, 'password' => 'Clave1'])
        ->assertRedirect(route('principal'));

    $usuario->refresh();
    expect($usuario->intentos_fallidos)->toBe(0);
    expect($usuario->bloqueado_hasta)->toBeNull();
});

test('reset con token incorrecto suma intentos', function () {
    $usuario = crearUsuarioBase([
        'email' => 'token-invalido@example.com',
        'password' => Hash::make('Vieja1'),
    ]);

    DB::table('reseteos_contrasena')->insert([
        'user_id' => $usuario->id,
        'hash_token' => hash('sha256', 'ABC123'),
        'expira_en' => now()->addMinutes(10),
        'usado' => false,
        'intentos' => 0,
        'bloqueado_hasta' => null,
        'ultimo_envio_en' => now(),
        'created_at' => now(),
    ]);

    $this->post('/password/reset', [
        'email' => $usuario->email,
        'token' => 'XXX999',
        'password' => 'Nueva1',
        'password_confirmation' => 'Nueva1',
    ])->assertSessionHasErrors('token');

    $registro = DB::table('reseteos_contrasena')->where('user_id', $usuario->id)->first();
    expect((int) $registro->intentos)->toBe(1);
});

test('reset con token expirado falla', function () {
    $usuario = crearUsuarioBase(['email' => 'expirado@example.com']);

    DB::table('reseteos_contrasena')->insert([
        'user_id' => $usuario->id,
        'hash_token' => hash('sha256', 'ABC123'),
        'expira_en' => now()->subMinute(),
        'usado' => false,
        'intentos' => 0,
        'bloqueado_hasta' => null,
        'ultimo_envio_en' => now(),
        'created_at' => now(),
    ]);

    $this->post('/password/reset', [
        'email' => $usuario->email,
        'token' => 'ABC123',
        'password' => 'Nueva1',
        'password_confirmation' => 'Nueva1',
    ])->assertSessionHasErrors('token');
});

test('reset bloquea tras demasiados intentos', function () {
    $usuario = crearUsuarioBase(['email' => 'bloqueo-reset@example.com']);

    DB::table('reseteos_contrasena')->insert([
        'user_id' => $usuario->id,
        'hash_token' => hash('sha256', 'ABC123'),
        'expira_en' => now()->addMinutes(10),
        'usado' => false,
        'intentos' => 4,
        'bloqueado_hasta' => null,
        'ultimo_envio_en' => now(),
        'created_at' => now(),
    ]);

    $this->post('/password/reset', [
        'email' => $usuario->email,
        'token' => 'ZZZ999',
        'password' => 'Nueva1',
        'password_confirmation' => 'Nueva1',
    ])->assertSessionHasErrors('token');

    $registro = DB::table('reseteos_contrasena')->where('user_id', $usuario->id)->first();
    expect((int) $registro->intentos)->toBe(5);
    expect($registro->bloqueado_hasta)->not->toBeNull();
});

test('reset no permite cuando no hay solicitud activa', function () {
    $usuario = crearUsuarioBase(['email' => 'sin-solicitud@example.com']);

    $this->post('/password/reset', [
        'email' => $usuario->email,
        'token' => 'ABC123',
        'password' => 'Nueva1',
        'password_confirmation' => 'Nueva1',
    ])->assertSessionHasErrors('email');
});

test('checkin fuera de ventana falla', function () {
    $usuario = crearUsuarioBase(['email' => 'fuera-ventana@example.com']);

    $reserva = Reserva::create([
        'user_id' => $usuario->id,
        'localizador' => 'LOC789',
        'origen' => 'Madrid',
        'destino' => 'Paris',
        'fecha_salida' => now()->addHours(8),
        'fecha_llegada' => now()->addHours(10),
        'estado' => 'confirmada',
        'plan_tarifa' => 'planit_easy',
        'email_contacto' => $usuario->email,
        'checkin_disponible_desde' => now()->addHour(),
        'checkin_estado' => null,
        'tarjetas_emitidas' => false,
    ]);

    ReservaPasajero::create([
        'reserva_id' => $reserva->id,
        'nombre' => 'Ana',
        'apellidos' => 'Lopez',
    ]);

    $this->actingAs($usuario)
        ->get('/area-personal/mis-reservas/' . $reserva->id . '/checkin')
        ->assertStatus(422);
});

test('checkin de reserva cancelada o completada falla', function () {
    $usuario = crearUsuarioBase(['email' => 'estado-checkin@example.com']);

    foreach (['cancelada_usuario', 'completada'] as $estado) {
        $reserva = Reserva::create([
            'user_id' => $usuario->id,
            'localizador' => 'LOC' . strtoupper(substr($estado, 0, 3)) . random_int(100, 999),
            'origen' => 'Madrid',
            'destino' => 'Paris',
            'fecha_salida' => now()->addHours(8),
            'fecha_llegada' => now()->addHours(10),
            'estado' => $estado,
            'plan_tarifa' => 'planit_easy',
            'email_contacto' => $usuario->email,
            'checkin_disponible_desde' => now()->subHour(),
            'checkin_estado' => null,
            'tarjetas_emitidas' => false,
        ]);

        $this->actingAs($usuario)
            ->get('/area-personal/mis-reservas/' . $reserva->id . '/checkin')
            ->assertStatus(422);
    }
});

test('checkin con documento invalido falla', function () {
    $usuario = crearUsuarioBase(['email' => 'doc-invalido@example.com']);

    $reserva = Reserva::create([
        'user_id' => $usuario->id,
        'localizador' => 'LOCDOC',
        'origen' => 'Madrid',
        'destino' => 'Paris',
        'fecha_salida' => now()->addHours(8),
        'fecha_llegada' => now()->addHours(10),
        'estado' => 'confirmada',
        'plan_tarifa' => 'planit_easy',
        'email_contacto' => $usuario->email,
        'checkin_disponible_desde' => now()->subHour(),
        'checkin_estado' => null,
        'tarjetas_emitidas' => false,
    ]);

    $pasajero = ReservaPasajero::create([
        'reserva_id' => $reserva->id,
        'nombre' => 'Ana',
        'apellidos' => 'Lopez',
    ]);

    $this->actingAs($usuario)
        ->post('/area-personal/mis-reservas/' . $reserva->id . '/checkin/confirmar', [
            'pasajero_' . $pasajero->id => [
                'nombre' => 'Ana',
                'apellidos' => 'Lopez',
                'fecha_nacimiento' => '1990-01-01',
                'tipo_documento' => 'DNI',
                'numero_documento' => '12A',
                'asiento_codigo' => '10A',
                'equipaje_extra' => 0,
            ],
        ])
        ->assertSessionHasErrors('checkin');
});

test('checkin falla cuando el asiento ya esta ocupado en otra reserva del mismo vuelo', function () {
    $usuario = crearUsuarioBase(['email' => 'asiento-ocupado@example.com']);

    $horaSalida = now()->addHours(8)->startOfMinute();

    $reservaObjetivo = Reserva::create([
        'user_id' => $usuario->id,
        'localizador' => 'LOCAS1',
        'origen' => 'Madrid',
        'destino' => 'Paris',
        'fecha_salida' => $horaSalida,
        'fecha_llegada' => now()->addHours(10),
        'estado' => 'confirmada',
        'plan_tarifa' => 'planit_easy',
        'email_contacto' => $usuario->email,
        'checkin_disponible_desde' => now()->subHour(),
        'checkin_estado' => null,
        'tarjetas_emitidas' => false,
    ]);

    $reservaConflicto = Reserva::create([
        'user_id' => null,
        'localizador' => 'LOCAS2',
        'origen' => 'Madrid',
        'destino' => 'Paris',
        'fecha_salida' => $horaSalida,
        'fecha_llegada' => now()->addHours(10),
        'estado' => 'confirmada',
        'plan_tarifa' => 'planit_easy',
        'email_contacto' => 'otro@example.com',
        'checkin_disponible_desde' => now()->subHour(),
        'checkin_estado' => null,
        'tarjetas_emitidas' => false,
    ]);

    $pasajeroObjetivo = ReservaPasajero::create([
        'reserva_id' => $reservaObjetivo->id,
        'nombre' => 'Ana',
        'apellidos' => 'Lopez',
    ]);

    ReservaPasajero::create([
        'reserva_id' => $reservaConflicto->id,
        'nombre' => 'Luis',
        'apellidos' => 'Perez',
        'asiento_codigo' => '15C',
    ]);

    $this->actingAs($usuario)
        ->post('/area-personal/mis-reservas/' . $reservaObjetivo->id . '/checkin/confirmar', [
            'pasajero_' . $pasajeroObjetivo->id => [
                'nombre' => 'Ana',
                'apellidos' => 'Lopez',
                'fecha_nacimiento' => '1990-01-01',
                'tipo_documento' => 'DNI',
                'numero_documento' => '12345678Z',
                'asiento_codigo' => '15C',
                'equipaje_extra' => 0,
            ],
        ])
        ->assertSessionHasErrors('checkin');
});

test('checkin de reserva ajena con usuario autenticado devuelve 403', function () {
    $propietario = crearUsuarioBase(['email' => 'propietario@example.com']);
    $intruso = crearUsuarioBase(['email' => 'intruso@example.com']);

    $reserva = Reserva::create([
        'user_id' => $propietario->id,
        'localizador' => 'LOCOWN',
        'origen' => 'Madrid',
        'destino' => 'Paris',
        'fecha_salida' => now()->addHours(8),
        'fecha_llegada' => now()->addHours(10),
        'estado' => 'confirmada',
        'plan_tarifa' => 'planit_easy',
        'email_contacto' => $propietario->email,
        'checkin_disponible_desde' => now()->subHour(),
        'checkin_estado' => null,
        'tarjetas_emitidas' => false,
    ]);

    $this->actingAs($intruso)
        ->get('/area-personal/mis-reservas/' . $reserva->id . '/checkin')
        ->assertStatus(403);
});

test('flujo invitado con localizador o email incorrectos devuelve 403', function () {
    $reserva = Reserva::create([
        'user_id' => null,
        'localizador' => 'LOCGUEST',
        'origen' => 'Madrid',
        'destino' => 'Paris',
        'fecha_salida' => now()->addHours(8),
        'fecha_llegada' => now()->addHours(10),
        'estado' => 'confirmada',
        'plan_tarifa' => 'planit_easy',
        'email_contacto' => 'guest@example.com',
        'checkin_disponible_desde' => now()->subHour(),
        'checkin_estado' => null,
        'tarjetas_emitidas' => false,
    ]);

    ReservaPasajero::create([
        'reserva_id' => $reserva->id,
        'nombre' => 'Ana',
        'apellidos' => 'Lopez',
    ]);

    $this->get('/mis-viajes/' . $reserva->id . '/checkin?localizador=MAL&email_contacto=guest@example.com')
        ->assertStatus(403);

    $this->get('/mis-viajes/' . $reserva->id . '/checkin?localizador=LOCGUEST&email_contacto=mal@example.com')
        ->assertStatus(403);
});

test('todas las rutas protegidas clave redirigen sin sesion', function () {
    $usuario = crearUsuarioBase(['email' => 'rutas-protegidas@example.com']);

    $reserva = Reserva::create([
        'user_id' => $usuario->id,
        'localizador' => 'LOCPROT',
        'origen' => 'Madrid',
        'destino' => 'Paris',
        'fecha_salida' => now()->addHours(8),
        'fecha_llegada' => now()->addHours(10),
        'estado' => 'confirmada',
        'plan_tarifa' => 'planit_easy',
        'email_contacto' => $usuario->email,
        'checkin_disponible_desde' => now()->subHour(),
        'checkin_estado' => null,
        'tarjetas_emitidas' => false,
    ]);

    $rutas = [
        ['get', '/area-personal'],
        ['get', '/area-personal/baja-cuenta'],
        ['post', '/area-personal/baja-cuenta'],
        ['get', '/area-personal/cambiar-password'],
        ['put', '/area-personal/cambiar-password'],
        ['put', '/area-personal'],
        ['get', '/area-personal/mis-reservas'],
        ['get', '/area-personal/mis-reservas/' . $reserva->id . '/checkin'],
        ['post', '/area-personal/mis-reservas/' . $reserva->id . '/checkin/confirmar'],
        ['post', '/area-personal/mis-reservas/' . $reserva->id . '/checkin/asiento'],
        ['get', '/area-personal/mis-reservas/' . $reserva->id . '/tarjetas-embarque'],
    ];

    foreach ($rutas as [$metodo, $url]) {
        $this->{$metodo}($url)->assertRedirect(route('login'));
    }
});

test('acciones sobre recursos ajenos quedan bloqueadas con 403', function () {
    $propietario = crearUsuarioBase(['email' => 'propietario2@example.com']);
    $intruso = crearUsuarioBase(['email' => 'intruso2@example.com']);

    $reserva = Reserva::create([
        'user_id' => $propietario->id,
        'localizador' => 'LOCAJENA',
        'origen' => 'Madrid',
        'destino' => 'Paris',
        'fecha_salida' => now()->addHours(8),
        'fecha_llegada' => now()->addHours(10),
        'estado' => 'confirmada',
        'plan_tarifa' => 'planit_easy',
        'email_contacto' => $propietario->email,
        'checkin_disponible_desde' => now()->subHour(),
        'checkin_estado' => null,
        'tarjetas_emitidas' => true,
    ]);

    $pasajero = ReservaPasajero::create([
        'reserva_id' => $reserva->id,
        'nombre' => 'Ana',
        'apellidos' => 'Lopez',
    ]);

    $this->actingAs($intruso)
        ->post('/area-personal/mis-reservas/' . $reserva->id . '/checkin/asiento', [
            'reserva_pasajero_id' => $pasajero->id,
            'asiento_codigo' => '10B',
        ])->assertStatus(403);

    $this->actingAs($intruso)
        ->get('/area-personal/mis-reservas/' . $reserva->id . '/tarjetas-embarque')
        ->assertStatus(403);
});

test('si falla envio de correo devuelve mensaje controlado y no rompe', function () {
    MailServiceFalso::$debeFallar = true;

    $respuesta = $this->post('/register', [
        'nombre' => 'Mario',
        'apellidos' => 'Suarez',
        'email' => 'mail-falla@example.com',
        'password' => 'Clave1',
        'password_confirmation' => 'Clave1',
    ]);

    MailServiceFalso::$debeFallar = false;

    $respuesta->assertRedirect(route('register'));
    $respuesta->assertSessionHasErrors('email');
});
