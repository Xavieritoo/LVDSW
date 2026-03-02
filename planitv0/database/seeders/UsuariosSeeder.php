<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('usuarios')->insert([
            [
                'nombre' => 'Admin',
                'apellidos' => 'Principal',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('1234'),
                'rol_id' => 1, // Admin
                'telefono' => null,
                'pais' => null,
                'codigo_postal' => null,
                'poblacion' => null,
                'direccion' => null,
                'fecha_nacimiento' => null,
                'documento_identidad' => null,
                'intentos_fallidos' => 0,
                'bloqueado_hasta' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Usuario',
                'apellidos' => 'Normal',
                'email' => 'usuario@gmail.com',
                'password' => Hash::make('1234'),
                'rol_id' => 2, // Usuario
                'telefono' => null,
                'pais' => null,
                'codigo_postal' => null,
                'poblacion' => null,
                'direccion' => null,
                'fecha_nacimiento' => null,
                'documento_identidad' => null,
                'intentos_fallidos' => 0,
                'bloqueado_hasta' => null,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
