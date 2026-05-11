<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class SuperAdminUsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $busqueda = trim((string) $request->query('q', ''));

        $rolesPermitidos = $this->rolesPermitidos();

        $query = Usuario::query()
            ->with('rol')
            ->whereIn('rol_id', $rolesPermitidos->pluck('id')->all())
            ->orderBy('id');

        if ($busqueda !== '') {
            $query->where(function ($sub) use ($busqueda) {
                $sub->where('nombre', 'like', '%' . $busqueda . '%')
                    ->orWhere('apellidos', 'like', '%' . $busqueda . '%')
                    ->orWhere('email', 'like', '%' . $busqueda . '%');
            });
        }

        $usuarios = $query
            ->paginate(15)
            ->withQueryString();

        return view('superadmin.usuarios.index', [
            'usuarios' => $usuarios,
            'busqueda' => $busqueda,
        ]);
    }

    public function create(): View
    {
        return view('superadmin.usuarios.create', [
            'roles' => $this->rolesPermitidos(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:usuarios,email',
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
            'rol_id' => 'required|integer',
            'esta_activo' => 'nullable|boolean',
            'esta_verificado' => 'nullable|boolean',
        ]);

        $rolesPermitidos = $this->rolesPermitidos();
        if (!$rolesPermitidos->pluck('id')->contains((int) $datos['rol_id'])) {
            return redirect()->back()->withInput()->withErrors([
                'rol_id' => 'Rol no valido. Solo se permite Usuario o Admin.',
            ]);
        }

        $estaActivo = true;
        if (array_key_exists('esta_activo', $datos)) {
            $estaActivo = (bool) $datos['esta_activo'];
        }
        $estaVerificado = true;
        if (array_key_exists('esta_verificado', $datos)) {
            $estaVerificado = (bool) $datos['esta_verificado'];
        }

        Usuario::query()->create([
            'nombre' => $datos['nombre'],
            'apellidos' => $datos['apellidos'],
            'email' => $datos['email'],
            'password' => Hash::make($datos['password']),
            'rol_id' => (int) $datos['rol_id'],
            'esta_activo' => $estaActivo,
            'esta_verificado' => $estaVerificado,
        ]);

        return redirect()->route('superadmin.usuarios.index')->with('exito', 'Usuario creado correctamente.');
    }

    public function edit(Usuario $usuario): View
    {
        $this->asegurarRolEditable($usuario);

        return view('superadmin.usuarios.edit', [
            'usuario' => $usuario,
            'roles' => $this->rolesPermitidos(),
        ]);
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        $this->asegurarRolEditable($usuario);

        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:usuarios,email,' . $usuario->id,
            'password' => [
                'nullable',
                'min:5',
                function ($attribute, $value, $fail) {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $texto = (string) $value;
                    if (!$this->contieneMayuscula($texto) || !$this->contieneNumero($texto)) {
                        $fail('La contraseña debe incluir al menos una mayúscula y un número.');
                    }
                },
                'confirmed',
            ],
            'rol_id' => 'required|integer',
            'esta_activo' => 'nullable|boolean',
            'esta_verificado' => 'nullable|boolean',
        ]);

        $rolesPermitidos = $this->rolesPermitidos();
        if (!$rolesPermitidos->pluck('id')->contains((int) $datos['rol_id'])) {
            return redirect()->back()->withInput()->withErrors([
                'rol_id' => 'Rol no valido. Solo se permite Usuario o Admin.',
            ]);
        }

        $estaActivo = false;
        if (array_key_exists('esta_activo', $datos)) {
            $estaActivo = (bool) $datos['esta_activo'];
        }
        $estaVerificado = false;
        if (array_key_exists('esta_verificado', $datos)) {
            $estaVerificado = (bool) $datos['esta_verificado'];
        }

        $payload = [
            'nombre' => $datos['nombre'],
            'apellidos' => $datos['apellidos'],
            'email' => $datos['email'],
            'rol_id' => (int) $datos['rol_id'],
            'esta_activo' => $estaActivo,
            'esta_verificado' => $estaVerificado,
        ];

        if (!empty($datos['password'])) {
            $payload['password'] = Hash::make($datos['password']);
        }

        $usuario->update($payload);

        return redirect()->route('superadmin.usuarios.index')->with('exito', 'Usuario actualizado correctamente.');
    }

    public function destroy(Usuario $usuario): RedirectResponse
    {
        $this->asegurarRolEditable($usuario);

        if ((int) Auth::id() === (int) $usuario->id) {
            return redirect()->route('superadmin.usuarios.index')->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();

        return redirect()->route('superadmin.usuarios.index')->with('exito', 'Usuario eliminado correctamente.');
    }

    private function rolesPermitidos()
    {
        return Rol::query()
            ->whereIn('nombre', ['usuario', 'admin'])
            ->orderBy('nombre')
            ->get();
    }

    private function asegurarRolEditable(Usuario $usuario): void
    {
        $usuario->loadMissing('rol');
        $rolNombre = mb_strtolower(trim((string) optional($usuario->rol)->nombre), 'UTF-8');
        if (!in_array($rolNombre, ['usuario', 'admin'], true)) {
            abort(403, 'Solo se pueden gestionar usuarios con rol Usuario/Admin.');
        }
    }

    private function contieneMayuscula(string $texto): bool
    {
        $longitud = mb_strlen($texto, 'UTF-8');
        for ($i = 0; $i < $longitud; $i++) {
            $char = mb_substr($texto, $i, 1, 'UTF-8');
            if ($char === '') {
                continue;
            }

            if ($char === mb_strtoupper($char, 'UTF-8') && $char !== mb_strtolower($char, 'UTF-8')) {
                return true;
            }
        }

        return false;
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
