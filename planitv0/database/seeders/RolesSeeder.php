<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Superadmin: puede cancelar vuelos globalmente (doc sección 1.8)
        DB::table('roles')->updateOrInsert(
            ['nombre' => 'superadmin'],
            ['updated_at' => now(), 'created_at' => now()]
        );

        // Admin: gestión operacional y cancelaciones de vuelo (doc sección 1.8)
        DB::table('roles')->updateOrInsert(
            ['nombre' => 'admin'],
            ['updated_at' => now(), 'created_at' => now()]
        );

        // Usuario: cliente registrado (doc sección 1 - Registro)
        DB::table('roles')->updateOrInsert(
            ['nombre' => 'usuario'],
            ['updated_at' => now(), 'created_at' => now()]
        );
    }
}
