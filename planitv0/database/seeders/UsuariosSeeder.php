<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        $superadminRol = (int) DB::table('roles')->where('nombre', 'superadmin')->value('id');
        $adminRol      = (int) DB::table('roles')->where('nombre', 'admin')->value('id');
        $usuarioRol    = (int) DB::table('roles')->where('nombre', 'usuario')->value('id');

        if ($superadminRol <= 0 || $adminRol <= 0 || $usuarioRol <= 0) {
            if ($this->command) {
                $this->command->warn('Faltan roles base. Ejecuta primero RolesSeeder.');
            }
            return;
        }

        $ahora = now();

        $usuarios = [
            [
                'email' => 'superadmin@planit.local',
                'nombre' => 'Carlos',
                'apellidos' => 'Lopez Navarro',
                'password' => 'Super123',
                'rol_id' => $superadminRol,
                'esta_verificado' => true,
                'esta_activo' => true,
                'intentos_fallidos' => 0,
                'bloqueado_hasta' => null,
            ],
            [
                'email' => 'admin@planit.local',
                'nombre' => 'Marta',
                'apellidos' => 'Garcia Ruiz',
                'password' => 'Admin123',
                'rol_id' => $adminRol,
                'esta_verificado' => true,
                'esta_activo' => true,
                'intentos_fallidos' => 0,
                'bloqueado_hasta' => null,
            ],
            [
                'email' => 'ana@planit.local',
                'nombre' => 'Ana',
                'apellidos' => 'Martin Diaz',
                'password' => 'Usuario123',
                'rol_id' => $usuarioRol,
                'esta_verificado' => true,
                'esta_activo' => true,
                'intentos_fallidos' => 0,
                'bloqueado_hasta' => null,
            ],
            [
                'email' => 'mooldovan.21@gmail.com',
                'nombre' => 'Andrei',
                'apellidos' => 'Moldovan',
                'password' => 'Andrei123',
                'rol_id' => $usuarioRol,
                'esta_verificado' => true,
                'esta_activo' => true,
                'intentos_fallidos' => 0,
                'bloqueado_hasta' => null,
            ],
            [
                'email' => 'moooldo.03@gmail.com',
                'nombre' => 'Moldo',
                'apellidos' => 'Demo Prueba',
                'password' => 'Andrei123',
                'rol_id' => $usuarioRol,
                'esta_verificado' => true,
                'esta_activo' => true,
                'intentos_fallidos' => 0,
                'bloqueado_hasta' => null,
            ],
            [
                'email' => 'pendiente@planit.local',
                'nombre' => 'Lucia',
                'apellidos' => 'Pendiente Correo',
                'password' => 'Pendiente123',
                'rol_id' => $usuarioRol,
                'esta_verificado' => false,
                'esta_activo' => true,
                'intentos_fallidos' => 0,
                'bloqueado_hasta' => null,
            ],
            [
                'email' => 'bloqueado@planit.local',
                'nombre' => 'Diego',
                'apellidos' => 'Intentos Fallidos',
                'password' => 'Bloqueado123',
                'rol_id' => $usuarioRol,
                'esta_verificado' => true,
                'esta_activo' => true,
                'intentos_fallidos' => 3,
                'bloqueado_hasta' => $ahora->copy()->addMinutes(10),
            ],
            [
                'email' => 'baja@planit.local',
                'nombre' => 'Claudia',
                'apellidos' => 'Cuenta Desactivada',
                'password' => 'Baja123',
                'rol_id' => $usuarioRol,
                'esta_verificado' => true,
                'esta_activo' => false,
                'intentos_fallidos' => 0,
                'bloqueado_hasta' => null,
                'deleted_at' => $ahora->copy()->subDays(2),
            ],
        ];

        foreach ($usuarios as $datosUsuario) {
            $deletedAt = null;
            if (isset($datosUsuario['deleted_at'])) {
                $deletedAt = $datosUsuario['deleted_at'];
            }
            $anonymizedAt = null;
            if (isset($datosUsuario['anonymized_at'])) {
                $anonymizedAt = $datosUsuario['anonymized_at'];
            }

            DB::table('usuarios')->updateOrInsert(
                ['email' => $datosUsuario['email']],
                [
                    'nombre' => $datosUsuario['nombre'],
                    'apellidos' => $datosUsuario['apellidos'],
                    'password' => Hash::make($datosUsuario['password']),
                    'rol_id' => $datosUsuario['rol_id'],
                    'esta_verificado' => $datosUsuario['esta_verificado'],
                    'esta_activo' => $datosUsuario['esta_activo'],
                    'intentos_fallidos' => $datosUsuario['intentos_fallidos'],
                    'bloqueado_hasta' => $datosUsuario['bloqueado_hasta'],
                    'remember_token' => null,
                    'deleted_at' => $deletedAt,
                    'anonymized_at' => $anonymizedAt,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]
            );
        }

        $perfiles = [
            [
                'email' => 'ana@planit.local',
                'fecha_nacimiento' => '1993-04-11',
                'telefono_prefijo' => '+34',
                'telefono_numero' => '612345678',
                'pais' => 'España',
                'ciudad' => 'Madrid',
                'direccion' => 'Calle Mayor 15, 3B',
                'codigo_postal' => '28013',
            ],
            [
                'email' => 'mooldovan.21@gmail.com',
                'fecha_nacimiento' => '2003-07-22',
                'telefono_prefijo' => '+40',
                'telefono_numero' => '745123456',
                'pais' => 'Rumania',
                'ciudad' => 'Cluj-Napoca',
                'direccion' => 'Strada Libertatii 8',
                'codigo_postal' => '400001',
            ],
            [
                'email' => 'moooldo.03@gmail.com',
                'fecha_nacimiento' => '2001-03-14',
                'telefono_prefijo' => '+34',
                'telefono_numero' => '611223344',
                'pais' => 'España',
                'ciudad' => 'Bilbao',
                'direccion' => 'Avenida del Puerto 12',
                'codigo_postal' => '48001',
            ],
            [
                'email' => 'pendiente@planit.local',
                'fecha_nacimiento' => '1998-09-30',
                'telefono_prefijo' => '+34',
                'telefono_numero' => '600123456',
                'pais' => 'España',
                'ciudad' => 'Sevilla',
                'direccion' => 'Avenida de la Constitucion 22',
                'codigo_postal' => '41001',
            ],
        ];

        foreach ($perfiles as $perfil) {
            $userId = DB::table('usuarios')->where('email', $perfil['email'])->value('id');
            if (!$userId) {
                continue;
            }

            DB::table('usuarios_perfil')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'fecha_nacimiento' => $perfil['fecha_nacimiento'],
                    'telefono_prefijo' => $perfil['telefono_prefijo'],
                    'telefono_numero' => $perfil['telefono_numero'],
                    'pais' => $perfil['pais'],
                    'ciudad' => $perfil['ciudad'],
                    'direccion' => $perfil['direccion'],
                    'codigo_postal' => $perfil['codigo_postal'],
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]
            );
        }

        $this->seedVerificacionesEmail($ahora);
        $this->seedReseteosContrasena($ahora);
        $this->seedHistorialContrasenas($ahora);
        $this->seedBajasCuenta($ahora);
    }

    private function seedVerificacionesEmail($ahora): void
    {
        $emailPendiente = 'pendiente@planit.local';
        $userPendienteId = DB::table('usuarios')->where('email', $emailPendiente)->value('id');
        if (!$userPendienteId) {
            return;
        }

        DB::table('verificaciones_email')->updateOrInsert(
            ['user_id' => $userPendienteId],
            [
                'hash_codigo' => hash('sha256', '123456'),
                'expira_en' => $ahora->copy()->addMinutes(15),
                'usado' => 0,
                'intentos' => 0,
                'bloqueado_hasta' => null,
                'ultimo_envio_en' => $ahora,
                'created_at' => $ahora,
            ]
        );
    }

    private function seedReseteosContrasena($ahora): void
    {
        $emailUsuario = 'ana@planit.local';
        $userId = DB::table('usuarios')->where('email', $emailUsuario)->value('id');
        if (!$userId) {
            return;
        }

        DB::table('reseteos_contrasena')->updateOrInsert(
            ['user_id' => $userId],
            [
                'hash_token' => hash('sha256', '654321'),
                'expira_en' => $ahora->copy()->addMinutes(15),
                'usado' => 0,
                'intentos' => 0,
                'bloqueado_hasta' => null,
                'ultimo_envio_en' => $ahora,
                'created_at' => $ahora,
            ]
        );
    }

    private function seedHistorialContrasenas($ahora): void
    {
        $emailsConHistorial = [
            'ana@planit.local',
            'mooldovan.21@gmail.com',
            'moooldo.03@gmail.com',
        ];

        foreach ($emailsConHistorial as $email) {
            $userId = DB::table('usuarios')->where('email', $email)->value('id');
            if (!$userId) {
                continue;
            }

            DB::table('historial_contrasenas')->where('user_id', $userId)->delete();

            DB::table('historial_contrasenas')->insert([
                'user_id' => $userId,
                'hash_contrasena' => DB::table('usuarios')->where('id', $userId)->value('password'),
                'created_at' => $ahora,
            ]);
        }
    }

    private function seedBajasCuenta($ahora): void
    {
        $emailBaja = 'baja@planit.local';
        $userId = DB::table('usuarios')->where('email', $emailBaja)->value('id');
        if (!$userId) {
            return;
        }

        DB::table('bajas_cuenta')->where('user_id', $userId)->delete();

        DB::table('bajas_cuenta')->insert([
            'user_id' => $userId,
            'motivo' => 'no_necesito',
            'comentario' => 'Cuenta desactivada voluntariamente desde area personal.',
            'created_at' => $ahora->copy()->subDays(2),
        ]);
    }
}
