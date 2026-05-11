<?php

namespace App\Http\Controllers;

use App\Models\UsuariosPerfil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AreaPersonalController extends Controller
{
    // Baja de cuenta

    public function mostrarBajaCuenta()
    {
        $usuario = Auth::user();
        return view('baja-cuenta', compact('usuario'));
    }

    public function procesarBajaCuenta(Request $request)
    {
        $usuario = Auth::user();

        // Control de bloqueo por intentos fallidos
        if ($usuario->bloqueado_hasta && now()->lt($usuario->bloqueado_hasta)) {
            $minutosRestantes = now()->diffInMinutes($usuario->bloqueado_hasta) + 1;
            return back()->withErrors(['baja' => "Has superado el número de intentos. Intenta de nuevo en $minutosRestantes minuto(s)."]);
        } elseif ($usuario->bloqueado_hasta && now()->gte($usuario->bloqueado_hasta)) {
            // Si ya pasó el bloqueo, reiniciar
            $usuario->intentos_fallidos = 0;
            $usuario->bloqueado_hasta = null;
            $usuario->save();
        }

        $request->validate([
            'motivo' => 'nullable|in:problemas_web,atencion_cliente,no_necesito,otro',
            'comentario' => 'nullable|string|max:500',
            'password' => 'required|string',
        ], [
            'password.required' => 'Debes introducir tu contraseña para confirmar.',
        ]);

        // Verificar contraseña
        if (!Hash::check($request->password, $usuario->password)) {
            $usuario->intentos_fallidos += 1;
            if ($usuario->intentos_fallidos >= 4) {
                $usuario->bloqueado_hasta = now()->addMinutes(5);
            }
            $usuario->save();
            if ($usuario->intentos_fallidos >= 4) {
                $mensajeError = 'Has superado el número de intentos. Intenta de nuevo en 5 minutos.';
            } else {
                $mensajeError = 'La contraseña introducida no es correcta.';
            }
            return back()->withErrors(['baja' => $mensajeError]);
        }

        // Reiniciar intentos si la contrasena es correcta.
        $usuario->intentos_fallidos = 0;
        $usuario->bloqueado_hasta = null;

        // Motivo opcional: si no se selecciona, guardar 'otro' (valor válido del ENUM)
        $motivo = $request->motivo;
        if (empty($motivo)) {
            $motivo = 'otro';
        }

        if ($motivo === 'otro') {
            $comentario = $request->comentario;
            if (is_null($comentario) || $comentario === '') {
                $comentario = 'No especificado';
            }
        } else {
            $comentario = null;
        }

        // Comprobación explícita: las reservas activas no se cancelan automáticamente.
        $tieneReservasActivas = DB::table('reservas')
            ->where('user_id', $usuario->id)
            ->whereNotIn('estado', ['cancelada_usuario', 'cancelada_aerolinea', 'completada'])
            ->exists();

        // Registrar motivo de baja
        DB::table('bajas_cuenta')->insert([
            'user_id' => $usuario->id,
            'motivo' => $motivo,
            'comentario' => $comentario,
            'created_at' => now(),
        ]);

        // Desactivación lógica
        $usuario->esta_activo = 0;
        $usuario->deleted_at = now();
        $usuario->save();

        // Cerrar sesión
        Auth::logout();

        $mensaje = 'Cuenta eliminada correctamente. Puedes gestionar tus reservas como invitado desde Mis viajes.';

        if ($tieneReservasActivas) {
            $mensaje = 'Cuenta eliminada correctamente. Tus reservas activas no se cancelan y puedes gestionarlas como invitado desde Mis viajes.';
        }

        return redirect('/')->with('exito', $mensaje);
    }

    // Datos personales

    public function mostrar()
    {
        $usuario = Auth::user();
        $perfil  = UsuariosPerfil::where('user_id', $usuario->id)->first();

        $telefonoPrefijo = null;
        $telefonoNumero  = null;

        if ($perfil) {
            if (isset($perfil->telefono_prefijo)) {
                $telefonoPrefijo = $perfil->telefono_prefijo;
            }

            if (isset($perfil->telefono_numero)) {
                $telefonoNumero = $perfil->telefono_numero;
            }
        }

        return view('area-personal', compact('usuario', 'perfil', 'telefonoPrefijo', 'telefonoNumero'));
    }

    public function actualizar(Request $request)
    {
        $usuario = Auth::user();

        $request->validate([
            'nombre'            => 'required|string|max:100',
            'apellidos'         => 'required|string|max:150',
            'email'             => 'required|email|max:150|unique:usuarios,email,' . $usuario->id,
            'fecha_nacimiento'  => ['required', 'date'],
            'telefono_prefijo'  => 'nullable|string|max:10',
            'telefono_numero'   => ['nullable', 'digits_between:9,10'],
            'pais'              => 'nullable|string|max:100',
            'ciudad'            => [
                'nullable',
                'string',
                'min:2',
                'max:100',
                function ($attribute, $value, $fail) {
                    if (!$this->esCiudadValida((string) $value)) {
                        $fail('La ciudad solo puede contener letras, espacios y guiones.');
                    }
                },
            ],
            'direccion'         => ['nullable', 'string', 'min:5', 'max:150'],
            'codigo_postal'     => [
                'nullable',
                'string',
                'min:4',
                'max:10',
                function ($attribute, $value, $fail) {
                    $normalizado = strtoupper(trim((string) $value));
                    if ($normalizado !== '' && !ctype_alnum($normalizado)) {
                        $fail('El código postal solo puede contener letras y números.');
                    }
                },
            ],
        ]);

        // Convertir fecha del calendario y validar rango de edad permitido.
        $fechaNacimiento = null;
        $fechaEntrada    = $request->input('fecha_nacimiento');

        if (!empty($fechaEntrada)) {
            try {
            $fecha = Carbon::parse($fechaEntrada);
                $edad  = $fecha->age;

                if ($edad < 0 || $edad > 120) {
                    return back()
                        ->withErrors(['fecha_nacimiento' => 'La fecha de nacimiento no es válida (edad entre 0 y 120 años).'])
                        ->withInput();
                }

                $fechaNacimiento = $fecha->format('Y-m-d');
            } catch (\Exception $e) {
                return back()
                    ->withErrors(['fecha_nacimiento' => 'Formato de fecha no válido. Selecciona una fecha válida en el calendario.'])
                    ->withInput();
            }
        }

        // Normalizar campos de texto antes de persistir.
        $ciudad = null;
        if (!empty($request->ciudad)) {
            $ciudad = trim($this->primeraLetraMayuscula(mb_strtolower(trim($request->ciudad), 'UTF-8')));
        }

        $direccion = null;
        if (!empty($request->direccion)) {
            $direccion = trim($request->direccion);
        }

        $codigoPostal = null;
        if (!empty($request->codigo_postal)) {
            $codigoPostal = strtoupper(trim($request->codigo_postal));
        }

        $pais = null;
        if (!empty($request->pais)) {
            $pais = trim($request->pais);
        }

        $usuario->nombre    = trim($request->nombre);
        $usuario->apellidos = trim($request->apellidos);
        $usuario->email     = trim((string) $request->email);
        $usuario->save();

        $telefonoPrefijo = null;
        if ($request->telefono_prefijo) {
            $telefonoPrefijo = trim((string) $request->telefono_prefijo);
        }

        $telefonoNumero = null;
        if ($request->telefono_numero) {
            $telefonoNumero = trim((string) $request->telefono_numero);
        }

        $datosPerfil = [
            'fecha_nacimiento'      => $fechaNacimiento,
            'telefono_prefijo'      => $telefonoPrefijo,
            'telefono_numero'       => $telefonoNumero,
            'pais'                  => $pais,
            'ciudad'                => $ciudad,
            'direccion'             => $direccion,
            'codigo_postal'         => $codigoPostal,
        ];

        UsuariosPerfil::updateOrCreate(['user_id' => $usuario->id], $datosPerfil);

        return back()->with('exito', 'Datos personales actualizados correctamente.');
    }

    // Cambio de contrasena

    public function mostrarCambioPassword()
    {
        return view('cambiar-password');
    }

    public function cambiarPassword(Request $request)
    {
        $request->validateWithBag('password', [
            'password_actual' => 'required',
            'password_nuevo'  => [
                'required',
                'min:5',
                function ($attribute, $value, $fail) {
                    $texto = (string) $value;
                    if (!$this->contieneMayuscula($texto) || !$this->contieneNumero($texto)) {
                        $fail('La contraseña debe incluir al menos una mayúscula y un número.');
                    }
                },
                'confirmed',
            ],
        ], [
            'password_nuevo.min'    => 'La contraseña debe tener al menos 5 caracteres.',
            'password_nuevo.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $usuario = Auth::user();

        // Validar contrasena actual.
        if (!Hash::check($request->password_actual, $usuario->password)) {
            return back()->withErrors(['password_actual' => 'La contraseña actual no es correcta.'], 'password');
        }

        // Evitar reutilizar la misma contrasena actual.
        if (Hash::check($request->password_nuevo, $usuario->password)) {
            return back()->withErrors(['password_nuevo' => 'La nueva contraseña no puede ser igual a la actual.'], 'password');
        }

        // Comprobar reutilizacion contra la ultima contrasena guardada.
        $historial = DB::table('historial_contrasenas')
            ->where('user_id', $usuario->id)
            ->orderByDesc('created_at')
            ->take(1)
            ->pluck('hash_contrasena');

        foreach ($historial as $hashAntiguo) {
            if (Hash::check($request->password_nuevo, $hashAntiguo)) {
                return back()->withErrors(['password_nuevo' => 'No puedes reutilizar tu contraseña anterior.'], 'password');
            }
        }

        // Guardar hash anterior antes de actualizar.
        DB::table('historial_contrasenas')->insert([
            'user_id'       => $usuario->id,
            'hash_contrasena' => $usuario->password,
            'created_at'    => now(),
        ]);

        $usuario->password = Hash::make($request->password_nuevo);
        $usuario->save();

        return back()->with('exito', 'Contraseña actualizada correctamente.');
    }

    private function contieneMayuscula(string $texto): bool
    {
        return $texto !== strtolower($texto);
    }

    private function contieneNumero(string $texto): bool
    {
        $longitud = strlen($texto);
        for ($i = 0; $i < $longitud; $i++) {
            if (ctype_digit($texto[$i])) {
                return true;
            }
        }

        return false;
    }

    private function primeraLetraMayuscula(string $texto): string
    {
        if ($texto === '') {
            return '';
        }

        $primera = mb_substr($texto, 0, 1, 'UTF-8');
        $resto = mb_substr($texto, 1, null, 'UTF-8');

        return mb_strtoupper($primera, 'UTF-8') . $resto;
    }

    private function esCiudadValida(string $ciudad): bool
    {
        $texto = trim($ciudad);
        if ($texto === '') {
            return true;
        }

        $longitud = mb_strlen($texto, 'UTF-8');
        for ($i = 0; $i < $longitud; $i++) {
            $caracter = mb_substr($texto, $i, 1, 'UTF-8');
            if ($caracter === ' ' || $caracter === '-') {
                continue;
            }

            $caracterAscii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $caracter);
            if ($caracterAscii === false || $caracterAscii === '') {
                return false;
            }

            $caracterAscii = strtoupper(substr($caracterAscii, 0, 1));
            if (!ctype_alpha($caracterAscii)) {
                return false;
            }
        }

        return true;
    }
}
