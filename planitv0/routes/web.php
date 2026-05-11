<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AreaPersonalController;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\MisReservasController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PasajerosFrecuentesController;
use App\Http\Controllers\VueloController;
use App\Http\Controllers\PasajeroController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\DestinoController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\AdminVueloController;
use App\Http\Controllers\AdminReservaController;
use App\Http\Controllers\SuperAdminUsuarioController;

// Login

Route::get('/login', [LoginController::class, 'mostrarLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Registro

Route::get('/register', [RegisterController::class, 'mostrarRegistro'])->name('register');
Route::post('/register', [RegisterController::class, 'registrar'])->name('register.submit');

// Página principal y flujo de compra de vuelos

Route::get('/', [VueloController::class, 'index'])->name('principal');
Route::get('/resultados', [VueloController::class, 'resultados'])->name('flight.results');
Route::get('/pasajeros', [PasajeroController::class, 'pasajeros'])->name('flight.passengers');
Route::post('/pasajeros', [PasajeroController::class, 'guardarPasajeros'])->name('flight.passengers.store');
Route::get('/equipajes', [PasajeroController::class, 'mostrarEquipajes'])->name('flight.baggage');
Route::post('/equipajes', [PasajeroController::class, 'guardarEquipajes'])->name('flight.baggage.store');
Route::get('/resumen', [CompraController::class, 'resumen'])->name('flight.summary');
Route::post('/pago', [CompraController::class, 'pagar'])->name('flight.pay');
Route::get('/compra-completada', [CompraController::class, 'completada'])->name('flight.completed');

// Destinos y ofertas

Route::get('/destinos', [DestinoController::class, 'index'])->name('destinos.index');
Route::get('/destinos/{destino}', [DestinoController::class, 'show'])->name('destinos.show');
Route::get('/api/ciudades-origen', [DestinoController::class, 'buscarCiudadesOrigen'])->name('api.ciudades-origen');
Route::get('/api/ciudades-destino', [DestinoController::class, 'buscarCiudadesDestino'])->name('api.ciudades-destino');

// Estado de vuelos

Route::get('/estado-vuelos', [FlightController::class, 'index'])->name('flights.index');
Route::get('/estado-vuelos/{id}', [FlightController::class, 'show'])->name('flights.detail');

// Mis Viajes (invitado)

Route::get('/mis-viajes', [MisReservasController::class, 'misViajes'])
    ->name('mis-viajes.index');

Route::put('/mis-viajes/{reserva}/cancelar', [MisReservasController::class, 'cancelarInvitado'])
    ->name('mis-viajes.cancelar');

Route::post('/mis-viajes/{reserva}/checkin', [MisReservasController::class, 'realizarCheckinInvitado'])
    ->name('mis-viajes.checkin');

Route::get('/mis-viajes/{reserva}/checkin', [CheckinController::class, 'showInvitado'])
    ->name('checkin.show.invitado');

Route::post('/mis-viajes/{reserva}/checkin/confirmar', [CheckinController::class, 'store'])
    ->name('checkin.store.invitado');

Route::post('/mis-viajes/{reserva}/checkin/asiento', [CheckinController::class, 'bloquearAsiento'])
    ->name('checkin.bloquear-asiento.invitado');

Route::get('/mis-viajes/{reserva}/tarjetas-embarque', [CheckinController::class, 'descargarTarjetasInvitado'])
    ->name('checkin.tarjetas.invitado');

// Recuperación de contraseña

Route::get('/password/reset', function () {
    return view('email');
})->name('password.request');

Route::post('/password/email', [PasswordResetController::class, 'enviarCorreo'])
    ->name('password.email');

Route::get('/password/nueva', function (Illuminate\Http\Request $request) {
    $email = $request->email;
    if (!$email) {
        $email = session('email_recuperacion');
    }

    return view('reset', ['email' => $email]);
})->name('password.reset.form');

Route::get('/password/nueva/reenvio', [PasswordResetController::class, 'reenviarCodigo'])
    ->name('password.reset.resend');

Route::post('/password/reset', [PasswordResetController::class, 'restablecer'])
    ->name('password.update');

Route::get('/register/verify', [RegisterController::class, 'mostrarVerificacion'])
    ->name('register.verify');

Route::get('/register/verify/resend', [RegisterController::class, 'reenviarCodigo'])
    ->name('register.verify.resend');

Route::post('/register/verify', [RegisterController::class, 'verificarCodigo'])
    ->name('register.verify.submit');

// Área Personal (requiere autenticación)

Route::middleware('auth')->group(function () {
        // Baja de cuenta
        Route::get('/area-personal/baja-cuenta', [AreaPersonalController::class, 'mostrarBajaCuenta'])
            ->name('area-personal.baja-cuenta');
        Route::post('/area-personal/baja-cuenta', [AreaPersonalController::class, 'procesarBajaCuenta'])
            ->name('area-personal.baja-cuenta.procesar');
    Route::get('/area-personal', [AreaPersonalController::class, 'mostrar'])
        ->name('area-personal');

    Route::put('/area-personal', [AreaPersonalController::class, 'actualizar'])
        ->name('area-personal.actualizar');

    Route::get('/area-personal/cambiar-password', [AreaPersonalController::class, 'mostrarCambioPassword'])
        ->name('area-personal.cambiar-password');

    Route::put('/area-personal/cambiar-password', [AreaPersonalController::class, 'cambiarPassword'])
        ->name('area-personal.cambiar-password.submit');

    Route::get('/area-personal/mis-reservas', [MisReservasController::class, 'index'])
        ->name('mis-reservas.index');

    Route::post('/area-personal/mis-reservas/enlace/solicitar', [MisReservasController::class, 'solicitarEnlace'])
        ->name('mis-reservas.enlace.solicitar');

    Route::post('/area-personal/mis-reservas/enlace/reenviar', [MisReservasController::class, 'reenviarEnlace'])
        ->name('mis-reservas.enlace.reenviar');

    Route::post('/area-personal/mis-reservas/enlace/verificar', [MisReservasController::class, 'verificarEnlace'])
        ->name('mis-reservas.enlace.verificar');

    Route::put('/area-personal/mis-reservas/{reserva}/cancelar', [MisReservasController::class, 'cancelar'])
        ->name('mis-reservas.cancelar');

    Route::post('/area-personal/mis-reservas/{reserva}/checkin', [MisReservasController::class, 'realizarCheckin'])
        ->name('mis-reservas.checkin');

    Route::get('/area-personal/mis-reservas/{reserva}/checkin', [CheckinController::class, 'show'])
        ->name('checkin.show');

    Route::post('/area-personal/mis-reservas/{reserva}/checkin/confirmar', [CheckinController::class, 'store'])
        ->name('checkin.store');

    Route::post('/area-personal/mis-reservas/{reserva}/checkin/asiento', [CheckinController::class, 'bloquearAsiento'])
        ->name('checkin.bloquear-asiento');

    Route::get('/area-personal/mis-reservas/{reserva}/tarjetas-embarque', [CheckinController::class, 'descargarTarjetas'])
        ->name('checkin.tarjetas');

    Route::prefix('area-personal/pasajeros-frecuentes')->group(function () {
        Route::get('/', [PasajerosFrecuentesController::class, 'index'])
            ->name('pasajeros-frecuentes.index');
        Route::get('/crear', [PasajerosFrecuentesController::class, 'create'])
            ->name('pasajeros-frecuentes.create');
        Route::post('/', [PasajerosFrecuentesController::class, 'store'])
            ->name('pasajeros-frecuentes.store');
        Route::get('/{id}', [PasajerosFrecuentesController::class, 'show'])
            ->name('pasajeros-frecuentes.show');
        Route::get('/{id}/editar', [PasajerosFrecuentesController::class, 'edit'])
            ->name('pasajeros-frecuentes.edit');
        Route::put('/{id}', [PasajerosFrecuentesController::class, 'update'])
            ->name('pasajeros-frecuentes.update');
        Route::delete('/{id}', [PasajerosFrecuentesController::class, 'destroy'])
            ->name('pasajeros-frecuentes.destroy');
        Route::post('/{id}/favorito', [PasajerosFrecuentesController::class, 'toggleFavorito'])
            ->name('pasajeros-frecuentes.toggle-favorito');
    });

    Route::middleware('admin.role')->prefix('admin/vuelos')->group(function () {
        Route::get('/', [AdminVueloController::class, 'index'])->name('admin.vuelos.index');
        Route::get('/crear', [AdminVueloController::class, 'create'])->name('admin.vuelos.create');
        Route::post('/', [AdminVueloController::class, 'store'])->name('admin.vuelos.store');
        Route::get('/{vuelo}/editar', [AdminVueloController::class, 'edit'])->name('admin.vuelos.edit');
        Route::put('/{vuelo}', [AdminVueloController::class, 'update'])->name('admin.vuelos.update');
        Route::patch('/{vuelo}/horarios', [AdminVueloController::class, 'definirHorarios'])->name('admin.vuelos.horarios');
        Route::delete('/{vuelo}', [AdminVueloController::class, 'destroy'])->name('admin.vuelos.destroy');
    });

    Route::middleware('admin.role')->prefix('admin/reservas')->group(function () {
        Route::get('/', [AdminReservaController::class, 'index'])->name('admin.reservas.index');
        Route::get('/{reserva}', [AdminReservaController::class, 'show'])->name('admin.reservas.show');
    });

    Route::middleware('superadmin.role')->prefix('admin/reservas')->group(function () {
        Route::get('/{reserva}/editar', [AdminReservaController::class, 'edit'])->name('admin.reservas.edit');
        Route::put('/{reserva}', [AdminReservaController::class, 'update'])->name('admin.reservas.update');
    });

    Route::middleware('superadmin.role')->prefix('superadmin/usuarios')->group(function () {
        Route::get('/', [SuperAdminUsuarioController::class, 'index'])->name('superadmin.usuarios.index');
        Route::get('/crear', [SuperAdminUsuarioController::class, 'create'])->name('superadmin.usuarios.create');
        Route::post('/', [SuperAdminUsuarioController::class, 'store'])->name('superadmin.usuarios.store');
        Route::get('/{usuario}/editar', [SuperAdminUsuarioController::class, 'edit'])->name('superadmin.usuarios.edit');
        Route::put('/{usuario}', [SuperAdminUsuarioController::class, 'update'])->name('superadmin.usuarios.update');
        Route::delete('/{usuario}', [SuperAdminUsuarioController::class, 'destroy'])->name('superadmin.usuarios.destroy');
    });
});

// Documentación API Swagger

Route::get('/swagger', function () {
    return view('swagger');
})->name('swagger');

Route::get('/swagger.json', function () {
    return response()->file(public_path('swagger.json'));
});
