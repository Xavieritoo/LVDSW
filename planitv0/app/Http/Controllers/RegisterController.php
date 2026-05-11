<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    private const MINUTOS_EXPIRACION_TOKEN_EMAIL = 15;
    private const SEGUNDOS_ENFRIAMIENTO_REENVIO = 60;
    private const MAX_INTENTOS_CODIGO = 5;
    private const MINUTOS_BLOQUEO_CODIGO = 5;

    public function mostrarRegistro()
    {
        return view('register');
    }

    public function registrar(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:150',
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('usuarios', 'email')->where(function ($query) {
                    return $query->where('esta_activo', 1)->whereNull('deleted_at');
                }),
            ],
            'password' => [
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
        ]);

        // Toda cuenta registrada desde web se asigna al rol usuario.
        $rolUsuario = Rol::whereRaw('LOWER(TRIM(nombre)) = ?', ['usuario'])->first();
        if (!$rolUsuario) {
            $rolUsuario = Rol::create(['nombre' => 'usuario']);
        }

        try {
            DB::beginTransaction();

            // Buscar usuario eliminado/inactivo con ese email
            $usuario = Usuario::where('email', $request->email)
                ->where(function ($q) {
                    $q->where('esta_activo', 0)->orWhereNotNull('deleted_at');
                })->first();

            if ($usuario) {
                // Reactivar y actualizar datos
                $usuario->nombre = $request->nombre;
                $usuario->apellidos = $request->apellidos;
                $usuario->password = Hash::make($request->password);
                $usuario->rol_id = $rolUsuario->id;
                $usuario->esta_verificado = false;
                $usuario->esta_activo = true;
                $usuario->deleted_at = null;
                $usuario->anonymized_at = null;
                $usuario->save();
            } else {
                // Crear usuario nuevo
                $usuario = Usuario::create([
                    'nombre' => $request->nombre,
                    'apellidos' => $request->apellidos,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'rol_id' => $rolUsuario->id,
                    'esta_verificado' => false,
                    'esta_activo' => true,
                ]);
            }

            $codigo = (string) random_int(100000, 999999);

            DB::table('verificaciones_email')
                ->where('expira_en', '<', now())
                ->delete();

            DB::table('verificaciones_email')->updateOrInsert(
                ['user_id' => $usuario->id],
                [
                    'hash_codigo' => hash('sha256', $codigo),
                    'expira_en' => now()->addMinutes(self::MINUTOS_EXPIRACION_TOKEN_EMAIL),
                    'usado' => false,
                    'intentos' => 0,
                    'bloqueado_hasta' => null,
                    'ultimo_envio_en' => now(),
                    'created_at' => now(),
                ]
            );

            $mail = new MailService();
            $html = "
            <p>Hola,</p>
            <p>Gracias por crear una cuenta. Tu codigo de verificacion es:</p>
            <h2 style='color: #5170ff;'>{$codigo}</h2>
            <p>Este codigo expirara en " . self::MINUTOS_EXPIRACION_TOKEN_EMAIL . " minutos.</p>
            <p>Este codigo sirve para confirmar que este correo te pertenece.</p>
            ";
            $mail->enviarEmail($request->email, 'Codigo de verificacion de cuenta', $html);

            DB::commit();

            session(['registro_user_id' => $usuario->id]);

            return redirect()->route('register.verify')
                ->with('status', 'Se ha enviado un codigo de verificacion a tu correo.');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return redirect()->route('register')
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'email' => 'No se pudo enviar el correo de verificacion. Revisa la configuracion SMTP e intentalo de nuevo.',
                ]);
        }
    }

    public function mostrarVerificacion()
    {
        return view('verify');
    }

    public function reenviarCodigo()
    {
        $usuarioId = session('registro_user_id');
        if (!$usuarioId) {
            return redirect()->route('register')
                ->with('status', 'No hay un codigo activo. Completa el registro para recibir uno.');
        }

        $usuario = Usuario::find($usuarioId);
        if (!$usuario) {
            return redirect()->route('register')
                ->with('status', 'No se encontro el usuario pendiente de verificacion.');
        }

        if ($usuario->esta_verificado) {
            return redirect()->route('login')
                ->with('status', 'Tu cuenta ya esta verificada.');
        }

        $registroVerificacion = DB::table('verificaciones_email')
            ->where('user_id', $usuario->id)
            ->first();

        if ($registroVerificacion && $registroVerificacion->bloqueado_hasta && now()->lt($registroVerificacion->bloqueado_hasta)) {
            return back()->withErrors([
                'codigo' => 'Demasiados intentos. Espera 5 minutos para solicitar un nuevo codigo.',
            ]);
        }

        if ($registroVerificacion && $registroVerificacion->bloqueado_hasta && now()->gte($registroVerificacion->bloqueado_hasta)) {
            DB::table('verificaciones_email')
                ->where('user_id', $usuario->id)
                ->update([
                    'intentos' => 0,
                    'bloqueado_hasta' => null,
                ]);

            $registroVerificacion = DB::table('verificaciones_email')
                ->where('user_id', $usuario->id)
                ->first();
        }

        if ($registroVerificacion && $registroVerificacion->ultimo_envio_en) {
            $segundosDesdeUltimoEnvio = now()->diffInSeconds($registroVerificacion->ultimo_envio_en);
            if ($segundosDesdeUltimoEnvio < self::SEGUNDOS_ENFRIAMIENTO_REENVIO) {
                $segundosRestantes = self::SEGUNDOS_ENFRIAMIENTO_REENVIO - $segundosDesdeUltimoEnvio;
                return back()->withErrors([
                    'codigo' => 'Espera ' . $segundosRestantes . ' segundos antes de reenviar el codigo.',
                ]);
            }
        }

        $codigo = (string) random_int(100000, 999999);

        DB::table('verificaciones_email')
            ->where('expira_en', '<', now())
            ->delete();

        DB::table('verificaciones_email')->updateOrInsert(
            ['user_id' => $usuario->id],
            [
                'hash_codigo' => hash('sha256', $codigo),
                'expira_en' => now()->addMinutes(self::MINUTOS_EXPIRACION_TOKEN_EMAIL),
                'usado' => false,
                'intentos' => 0,
                'bloqueado_hasta' => null,
                'ultimo_envio_en' => now(),
                'created_at' => now(),
            ]
        );

        try {
            $mail = new MailService();
            $html = "
            <p>Hola,</p>
            <p>Has solicitado reenvio del codigo de verificacion:</p>
            <h2 style='color: #5170ff;'>{$codigo}</h2>
            <p>Este codigo expirara en " . self::MINUTOS_EXPIRACION_TOKEN_EMAIL . " minutos.</p>
            <p>Usa este codigo para verificar tu cuenta.</p>
            ";
            $mail->enviarEmail($usuario->email, 'Reenvio - Codigo de verificacion de cuenta', $html);
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors([
                'codigo' => 'No se pudo reenviar el correo ahora mismo. Revisa la configuracion SMTP e intentalo de nuevo.',
            ]);
        }

        return redirect()->route('register.verify')
            ->with('status', 'Se ha reenviado un nuevo codigo de verificacion a tu correo.');
    }

    public function verificarCodigo(Request $request)
    {
        $request->validate(['codigo' => 'required']);

        $usuarioId = session('registro_user_id');
        if (!$usuarioId) {
            return redirect()->route('register')
                ->with('status', 'Necesitas registrarte primero para verificar tu correo.');
        }

        $usuario = Usuario::find($usuarioId);
        if (!$usuario) {
            return redirect()->route('register')
                ->with('status', 'No se encontro el usuario pendiente de verificacion.');
        }

        if ($usuario->esta_verificado) {
            return redirect()->route('login')
                ->with('status', 'Tu cuenta ya esta verificada.');
        }

        $registro = DB::table('verificaciones_email')
            ->where('user_id', $usuario->id)
            ->where('usado', false)
            ->first();

        if (!$registro) {
            return back()->withErrors([
                'codigo' => 'No se encontro un codigo de verificacion para esta cuenta. Registrate de nuevo.',
            ]);
        }

        if ($registro->bloqueado_hasta && now()->lt($registro->bloqueado_hasta)) {
            return back()->withErrors([
                'codigo' => 'Has superado los intentos permitidos. Intentalo de nuevo en 5 minutos.',
            ]);
        }

        if ($registro->bloqueado_hasta && now()->gte($registro->bloqueado_hasta)) {
            DB::table('verificaciones_email')
                ->where('user_id', $usuario->id)
                ->update([
                    'intentos' => 0,
                    'bloqueado_hasta' => null,
                ]);

            $registro = DB::table('verificaciones_email')
                ->where('user_id', $usuario->id)
                ->where('usado', false)
                ->first();
        }

        if (now()->gt($registro->expira_en)) {
            return back()->withErrors([
                'codigo' => 'El codigo ha expirado. Solicita un reenvio para recibir uno nuevo.',
            ]);
        }

        if (!hash_equals($registro->hash_codigo, hash('sha256', $request->codigo))) {
            $intentos = ((int) $registro->intentos) + 1;
            $actualizacion = ['intentos' => $intentos];

            if ($intentos >= self::MAX_INTENTOS_CODIGO) {
                $actualizacion['bloqueado_hasta'] = now()->addMinutes(self::MINUTOS_BLOQUEO_CODIGO);
            }

            DB::table('verificaciones_email')
                ->where('user_id', $usuario->id)
                ->update($actualizacion);

            if ($intentos >= self::MAX_INTENTOS_CODIGO) {
                return back()->withErrors([
                    'codigo' => 'Has superado los intentos permitidos. Intentalo de nuevo en 5 minutos.',
                ]);
            }

            return back()->withErrors([
                'codigo' => 'Codigo incorrecto. Te quedan ' . (self::MAX_INTENTOS_CODIGO - $intentos) . ' intentos.',
            ]);
        }

        DB::table('verificaciones_email')
            ->where('user_id', $usuario->id)
            ->update([
                'usado' => true,
                'intentos' => 0,
                'bloqueado_hasta' => null,
            ]);

        $usuario->esta_verificado = true;
        $usuario->save();

        Auth::login($usuario);
        session()->forget(['registro_user_id']);

        return redirect()->route('principal')
            ->with('success', 'Cuenta verificada y creada.');
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
}
