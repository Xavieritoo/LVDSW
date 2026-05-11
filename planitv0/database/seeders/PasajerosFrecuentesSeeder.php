<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PasajerosFrecuentesSeeder extends Seeder
{
    public function run(): void
    {
        $emailsEj = [
            'ana@planit.local',
            'mooldovan.21@gmail.com',
            'moooldo.03@gmail.com',
            'pendiente@planit.local',
        ];

        $usuarios = DB::table('usuarios')
            ->whereIn('email', $emailsEj)
            ->get(['id', 'email']);

        if ($usuarios->isEmpty()) {
            if ($this->command) {
                $this->command->warn('No se encontraron usuarios para PasajerosFrecuentesSeeder. Ejecuta primero UsuariosSeeder.');
            }
            return;
        }

        $userIds = $usuarios->pluck('id')->all();
        $this->limpiarDatosPrevios($userIds);

        foreach ($usuarios as $usuario) {
            DB::transaction(function () use ($usuario) {
                $this->seedPorUsuario((int) $usuario->id, strtolower(trim((string) $usuario->email)));
            });
        }
    }

    private function limpiarDatosPrevios(array $userIds): void
    {
        DB::table('pasajeros_frecuentes')->whereIn('user_id', $userIds)->delete();

        DB::table('logs_cambios')
            ->whereIn('user_id', $userIds)
            ->where('tabla_afectada', 'pasajeros_frecuentes')
            ->where('descripcion', 'LIKE', 'Seeder PF:%')
            ->delete();
    }

    private function seedPorUsuario(int $userId, string $email): void
    {
        $ahora = now();
        $base = $this->pasajerosPorEmail($email);
        $insertados = [];

        foreach ($base as $dato) {
            $favoritoVal = 0;
            if ($dato['favorito']) {
                $favoritoVal = 1;
            }

            $fila = [
                'user_id' => $userId,
                'nombre' => $dato['nombre'],
                'apellidos' => $dato['apellidos'],
                'tipo_documento' => $dato['tipo_documento'],
                'numero_documento' => $dato['numero_documento'],
                'numero_documento_norm' => $dato['numero_documento_norm'],
                'fecha_nacimiento' => $dato['fecha_nacimiento'],
                'pais' => $dato['pais'],
                'favorito' => $favoritoVal,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ];

            $pasajeroId = DB::table('pasajeros_frecuentes')->insertGetId($fila);
            $insertados[] = [
                'id' => $pasajeroId,
                'nombre' => $dato['nombre'],
                'apellidos' => $dato['apellidos'],
                'fecha_nacimiento' => $dato['fecha_nacimiento'],
                'favorito' => (bool) $fila['favorito'],
            ];

            $tipo = $this->calcularTipoPasajero($dato['fecha_nacimiento']);
            $this->registrarAuditoria(
                $userId,
                'INSERT',
                'Seeder PF: INSERT pasajero ' . $dato['nombre'] . ' ' . $dato['apellidos'] . ' tipo=' . $tipo
            );
        }

        if (!empty($insertados)) {
            $primero = $insertados[0];
            DB::table('pasajeros_frecuentes')
                ->where('id', $primero['id'])
                ->update([
                    'favorito' => 1,
                    'updated_at' => now(),
                ]);

            $this->registrarAuditoria(
                $userId,
                'UPDATE',
                'Seeder PF: UPDATE favorito pasajero ID ' . $primero['id']
            );
        }

        $temporal = DB::table('pasajeros_frecuentes')->insertGetId([
            'user_id' => $userId,
            'nombre' => 'Temporal',
            'apellidos' => 'Seeder Eliminado',
            'tipo_documento' => null,
            'numero_documento' => null,
            'numero_documento_norm' => null,
            'fecha_nacimiento' => Carbon::now()->subYears(30)->format('Y-m-d'),
            'pais' => null,
            'favorito' => 0,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);

        DB::table('pasajeros_frecuentes')->where('id', $temporal)->delete();
        $this->registrarAuditoria($userId, 'DELETE', 'Seeder PF: DELETE pasajero temporal ID ' . $temporal);
    }

    private function pasajerosPorEmail(string $email): array
    {
        if ($email === 'mooldovan.21@gmail.com') {
            return [
                $this->pasajero('Adrian', 'Martin Pardo', $this->fechaAdulto(22), 'España', true, 'DNI', $this->dniDesdeNumero(43123456)),
                $this->pasajero('Elena', 'Navarro Ruiz', $this->fechaAdulto(52), 'España', true, 'PASAPORTE', 'ESA1234567'),
                $this->pasajero('Ivan', 'Martin Soto', $this->fechaNino(8), 'España', false, null, null),
                $this->pasajero('Mara', 'Martin Pardo', $this->fechaBebe(18), 'España', true, null, null),
                $this->pasajero('Daniel', 'Lopez Marin', $this->fechaAdulto(34), null, false, 'PASAPORTE', 'ESX8899123'),
                $this->pasajero('Sofia', 'Gil Ortega', $this->fechaNino(14), 'Italia', false, 'PASAPORTE', 'ITA6X7M9'),
                $this->pasajero('Nora', 'Perez Leon', $this->fechaBebe(5), null, false, null, null),
            ];
        }

        if ($email === 'moooldo.03@gmail.com') {
            return [
                $this->pasajero('Moldo', 'Demo Prueba', $this->fechaAdulto(25), 'España', true, 'DNI', $this->dniDesdeNumero(52345678)),
                $this->pasajero('Carla', 'Navarro Gil', $this->fechaAdulto(23), 'España', true, 'DNI', $this->dniDesdeNumero(61234567)),
                $this->pasajero('Hector', 'Suarez Pardo', $this->fechaAdulto(27), 'Portugal', false, 'PASAPORTE', 'PT9911AA2'),
                $this->pasajero('Lucas', 'Mena Ortiz', $this->fechaNino(11), 'España', false, null, null),
                $this->pasajero('Aitana', 'Mena Ortiz', $this->fechaBebe(20), 'España', true, null, null),
                $this->pasajero('Noa', 'Mena Ortiz', $this->fechaBebe(9), null, false, null, null),
                $this->pasajero('Julia', 'Santos Perez', $this->fechaNino(5), 'Francia', false, 'PASAPORTE', 'FR45AB671'),
            ];
        }

        if ($email === 'pendiente@planit.local') {
            return [
                $this->pasajero('Lucia', 'Pendiente Correo', $this->fechaAdulto(27), 'España', true, 'DNI', $this->dniDesdeNumero(73456789)),
                $this->pasajero('Mario', 'Pendiente Correo', $this->fechaNino(6), 'España', false, null, null),
                $this->pasajero('Iris', 'Pendiente Correo', $this->fechaBebe(14), null, false, null, null),
            ];
        }

        return [
            $this->pasajero('Ana', 'Martin Diaz', $this->fechaAdulto(33), 'España', true, 'DNI', $this->dniDesdeNumero(34567890)),
            $this->pasajero('Marco', 'Rossi Bianchi', $this->fechaAdulto(36), 'Italia', false, 'PASAPORTE', 'ITAA784321'),
            $this->pasajero('Lucia', 'Santos Perez', $this->fechaNino(10), 'España', true, null, null),
            $this->pasajero('Belen', 'Santos Perez', $this->fechaBebe(7), null, false, null, null),
            $this->pasajero('Gabriel', 'Ramos Vega', $this->fechaAdulto(45), 'Portugal', false, 'PASAPORTE', 'PTA9123412'),
        ];
    }

    private function pasajero(
        string $nombre,
        string $apellidos,
        string $fechaNacimiento,
        ?string $pais,
        bool $favorito,
        ?string $tipoDocumento,
        ?string $numeroDocumento
    ): array {
        $numeroNorm = null;
        if ($numeroDocumento !== null) {
            $numeroNorm = strtoupper(str_replace(' ', '', $numeroDocumento));
        }

        return [
            'nombre' => $nombre,
            'apellidos' => $apellidos,
            'fecha_nacimiento' => $fechaNacimiento,
            'pais' => $pais,
            'favorito' => $favorito,
            'tipo_documento' => $tipoDocumento,
            'numero_documento' => $numeroDocumento,
            'numero_documento_norm' => $numeroNorm,
        ];
    }

    private function calcularTipoPasajero(string $fechaNacimiento): string
    {
        $fecha = Carbon::parse($fechaNacimiento);
        $edad = $fecha->diffInYears(Carbon::now());

        if ($edad <= 2) {
            return 'bebe';
        }

        if ($edad < 16) {
            return 'nino';
        }

        return 'adulto';
    }

    private function fechaAdulto(int $edadEnAnos): string
    {
        return Carbon::now()->subYears($edadEnAnos)->subDays(20)->format('Y-m-d');
    }

    private function fechaNino(int $edadEnAnos): string
    {
        return Carbon::now()->subYears($edadEnAnos)->subMonths(2)->format('Y-m-d');
    }

    private function fechaBebe(int $edadEnMeses): string
    {
        return Carbon::now()->subMonths($edadEnMeses)->subDays(10)->format('Y-m-d');
    }

    private function dniDesdeNumero(int $numero): string
    {
        $letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
        $letra = substr($letras, $numero % 23, 1);

        return (string) $numero . $letra;
    }

    private function registrarAuditoria(int $userId, string $accion, string $descripcion): void
    {
        DB::table('logs_cambios')->insert([
            'user_id' => $userId,
            'tabla_afectada' => 'pasajeros_frecuentes',
            'accion' => $accion,
            'descripcion' => $descripcion,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
