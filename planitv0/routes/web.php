<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Middleware\Autenticado; // <-- tu middleware personalizado

// Login
Route::get('/login', [LoginController::class, 'mostrarLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Register
Route::get('/register', [RegisterController::class, 'mostrarRegistro'])->name('register');
Route::post('/register', [RegisterController::class, 'registrar'])->name('register.submit');

// Página principal
Route::get('/', function () {
    return view('principal');
})->name('principal');

// Configuración → middleware directo
Route::get('/configuracion', [ConfiguracionController::class, 'index'])
    ->middleware(Autenticado::class)
    ->name('configuracion');

Route::post('/configuracion', [ConfiguracionController::class, 'update'])
    ->middleware(Autenticado::class)
    ->name('configuracion.submit');

// Mostrar formulario de recuperación (sin carpetas)
Route::get('/password/reset', function () {
    return view('email'); // <- coincide con tu archivo actual
})->name('password.request');

// Procesar solicitud de recuperación
Route::post('/password/email', [PasswordResetController::class, 'enviarCorreo'])
    ->name('password.email');

// Mostrar formulario de restablecimiento de contraseña
Route::get('/password/reset/{token}', [PasswordResetController::class, 'mostrarFormulario'])
    ->name('password.reset');

// Procesar el cambio de contraseña
Route::post('/password/reset', [PasswordResetController::class, 'restablecer'])
    ->name('password.update');
