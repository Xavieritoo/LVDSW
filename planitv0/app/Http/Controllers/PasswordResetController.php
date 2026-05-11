<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    private const MINUTOS_EXPIRACION_TOKEN_EMAIL = 15;
    private const SEGUNDOS_ENFRIAMIENTO_REENVIO = 60;
    private const MAX_INTENTOS_TOKEN = 5;
    private const MINUTOS_BLOQUEO_TOKEN = 5;

    public function enviarCorreo(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:usuarios,email',
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !$usuario->esta_activo) {
            return back()->withErrors([
                'email' => 'No existe una cuenta activa con ese correo.',
            ]);
        }

        $tokenRecuperacion = strtoupper(Str::random(6));

        DB::table('reseteos_contrasena')
            ->where('expira_en', '<', now())
            ->delete();

        DB::table('reseteos_contrasena')->updateOrInsert(
            ['user_id' => $usuario->id],
            [
                'hash_token' => hash('sha256', $tokenRecuperacion),
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
            <p>Has solicitado restablecer tu contrasena. Tu codigo de recuperacion es:</p>
            <h2 style='color: #5170ff;'>{$tokenRecuperacion}</h2>
            <p>Este codigo expirara en " . self::MINUTOS_EXPIRACION_TOKEN_EMAIL . " minutos.</p>
            ";

            $mail->enviarEmail($usuario->email, 'Codigo de recuperacion de contrasena', $html);
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors([
                'email' => 'No se pudo enviar el correo de recuperacion. Revisa la configuracion SMTP e intentalo de nuevo.',
            ]);
        }

        return redirect()->route('password.reset.form', ['email' => $usuario->email])
            ->with([
                'status' => 'Se ha enviado un codigo de recuperacion a tu correo.',
                'email_recuperacion' => $usuario->email,
            ]);
    }

    public function reenviarCodigo()
    {
        $email = session('email_recuperacion');

        if (!$email) {
            return redirect()->route('password.request')
                ->with('status', 'Primero solicita un codigo ingresando tu correo.');
        }

        $usuario = Usuario::where('email', $email)->first();

        if (!$usuario || !$usuario->esta_activo) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'No existe una cuenta activa con ese correo.']);
        }

        $registro = DB::table('reseteos_contrasena')
            ->where('user_id', $usuario->id)
            ->first();

        if ($registro && $registro->bloqueado_hasta && now()->lt($registro->bloqueado_hasta)) {
            return back()->withErrors([
                'token' => 'Demasiados intentos. Espera 5 minutos antes de solicitar otro codigo.',
            ]);
        }

        if ($registro && $registro->bloqueado_hasta && now()->gte($registro->bloqueado_hasta)) {
            DB::table('reseteos_contrasena')
                ->where('user_id', $usuario->id)
                ->update([
                    'intentos' => 0,
                    'bloqueado_hasta' => null,
                ]);

            $registro = DB::table('reseteos_contrasena')
                ->where('user_id', $usuario->id)
                ->first();
        }

        if ($registro && $registro->ultimo_envio_en) {
            $segundosDesdeUltimoEnvio = now()->diffInSeconds($registro->ultimo_envio_en);
            if ($segundosDesdeUltimoEnvio < self::SEGUNDOS_ENFRIAMIENTO_REENVIO) {
                $segundosRestantes = self::SEGUNDOS_ENFRIAMIENTO_REENVIO - $segundosDesdeUltimoEnvio;
                return back()->withErrors([
                    'token' => 'Espera ' . $segundosRestantes . ' segundos antes de reenviar el codigo.',
                ]);
            }
        }

        $tokenRecuperacion = strtoupper(Str::random(6));

        DB::table('reseteos_contrasena')
            ->where('expira_en', '<', now())
            ->delete();

        DB::table('reseteos_contrasena')->updateOrInsert(
            ['user_id' => $usuario->id],
            [
                'hash_token' => hash('sha256', $tokenRecuperacion),
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
            <p>Has solicitado restablecer tu contrasena. Tu codigo de recuperacion es:</p>
            <h2 style='color: #5170ff;'>{$tokenRecuperacion}</h2>
            <p>Este codigo expirara en " . self::MINUTOS_EXPIRACION_TOKEN_EMAIL . " minutos.</p>
            ";

            $mail->enviarEmail($email, 'Codigo de recuperacion de contrasena', $html);
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors([
                'email' => 'No se pudo reenviar el correo de recuperacion. Revisa la configuracion SMTP e intentalo de nuevo.',
            ]);
        }

        return redirect()->route('password.reset.form', ['email' => $email])
            ->with([
                'status' => 'Se ha reenviado un codigo de recuperacion a tu correo.',
                'email_recuperacion' => $email,
            ]);
    }

    public function restablecer(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:usuarios,email',
            'password' => [
                'required',
                'confirmed',
                'min:5',
                function ($attribute, $value, $fail) {
                    $texto = (string) $value;
                    if (!$this->contieneMayuscula($texto) || !$this->contieneNumero($texto)) {
                        $fail('La contraseña debe incluir al menos una mayúscula y un número.');
                    }
                },
            ],
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (!$usuario || !$usuario->esta_activo) {
            return back()->withErrors([
                'email' => 'No existe una cuenta activa con ese correo.',
            ]);
        }

        $registro = DB::table('reseteos_contrasena')
            ->where('user_id', $usuario->id)
            ->where('usado', false)
            ->first();

        if (!$registro) {
            return back()->withErrors([
                'email' => 'No hay solicitud de recuperacion para este correo.',
            ]);
        }

        if ($registro->bloqueado_hasta && now()->lt($registro->bloqueado_hasta)) {
            return back()->withErrors([
                'token' => 'Has superado los intentos permitidos. Intentalo de nuevo en 5 minutos.',
            ]);
        }

        if ($registro->bloqueado_hasta && now()->gte($registro->bloqueado_hasta)) {
            DB::table('reseteos_contrasena')
                ->where('user_id', $usuario->id)
                ->update([
                    'intentos' => 0,
                    'bloqueado_hasta' => null,
                ]);

            $registro = DB::table('reseteos_contrasena')
                ->where('user_id', $usuario->id)
                ->where('usado', false)
                ->first();
        }

        if (now()->gt($registro->expira_en)) {
            return back()->withErrors([
                'token' => 'El codigo ha expirado. Solicita un reenvio para recibir uno nuevo.',
            ]);
        }

        if (!hash_equals($registro->hash_token, hash('sha256', $request->token))) {
            $intentos = ((int) $registro->intentos) + 1;
            $actualizacion = ['intentos' => $intentos];

            if ($intentos >= self::MAX_INTENTOS_TOKEN) {
                $actualizacion['bloqueado_hasta'] = now()->addMinutes(self::MINUTOS_BLOQUEO_TOKEN);
            }

            DB::table('reseteos_contrasena')
                ->where('user_id', $usuario->id)
                ->update($actualizacion);

            if ($intentos >= self::MAX_INTENTOS_TOKEN) {
                return back()->withErrors([
                    'token' => 'Has superado los intentos permitidos. Intentalo de nuevo en 5 minutos.',
                ]);
            }

            return back()->withErrors([
                'token' => 'Codigo de recuperacion invalido. Te quedan ' . (self::MAX_INTENTOS_TOKEN - $intentos) . ' intentos.',
            ]);
        }

        DB::table('historial_contrasenas')->insert([
            'user_id' => $usuario->id,
            'hash_contrasena' => $usuario->password,
            'created_at' => now(),
        ]);

        $usuario->password = Hash::make($request->password);
        $usuario->save();

        DB::table('reseteos_contrasena')
            ->where('user_id', $usuario->id)
            ->update([
                'usado' => true,
                'intentos' => 0,
                'bloqueado_hasta' => null,
            ]);

        return redirect()->route('login')
            ->with('status', 'Contrasena cambiada correctamente. Ahora puedes iniciar sesion.');
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
