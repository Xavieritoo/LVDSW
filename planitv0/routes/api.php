<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VuelosController;
use App\Http\Controllers\Api\ReservaController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/vuelos', [VuelosController::class, 'index']);

Route::middleware('jwt.auth')->group(function (): void {
    Route::get('/perfil', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/reservas', [ReservaController::class, 'index']);
    Route::get('/reservas/{id}', [ReservaController::class, 'show'])
        ->whereNumber('id');
});
