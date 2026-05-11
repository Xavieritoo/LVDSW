<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DateTimeImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class PasajerosFrecuentesController extends Controller
{
    public function index(Request $request)
    {
        $idUsuario = Auth::id();

        $filtro_nombre = $request->get('nombre', '');
        $filtro_pais = $request->get('pais', '');
        $filtro_favorito = $request->get('favorito', '');
        $filtro_tipo = $request->get('tipo_pasajero', '');
        $orden = $request->get('orden', 'nombre_asc');

        $sql = "SELECT id, nombre, apellidos, fecha_nacimiento, pais, favorito, created_at
                  FROM pasajeros_frecuentes
                  WHERE user_id = ?";
        $parametros = [$idUsuario];

        if ($filtro_nombre) {
            $sql .= " AND (LOWER(nombre) LIKE ? OR LOWER(apellidos) LIKE ?)";
            $busqueda = '%' . strtolower($filtro_nombre) . '%';
            $parametros[] = $busqueda;
            $parametros[] = $busqueda;
        }

        if ($filtro_pais) {
            $sql .= " AND pais = ?";
            $parametros[] = $filtro_pais;
        }

        if ($filtro_favorito === '1') {
            $sql .= " AND favorito = 1";
        } elseif ($filtro_favorito === '0') {
            $sql .= " AND favorito = 0";
        }

        match ($orden) {
            'nombre_desc' => $sql .= " ORDER BY apellidos DESC, nombre DESC",
            'fecha_asc' => $sql .= " ORDER BY fecha_nacimiento ASC",
            'fecha_desc' => $sql .= " ORDER BY fecha_nacimiento DESC",
            'favorito' => $sql .= " ORDER BY favorito DESC, nombre ASC",
            'no_favorito' => $sql .= " ORDER BY favorito ASC, nombre ASC",
            default => $sql .= " ORDER BY nombre ASC, apellidos ASC",
        };

        $pasajeros = DB::select($sql, $parametros);

        $pasajeros = array_map(function ($p) {
            $p->tipo_pasajero = $this->calcularTipoPasajero($p->fecha_nacimiento);
            $p->edad = $this->calcularEdad($p->fecha_nacimiento);

            return $p;
        }, $pasajeros);

        if ($filtro_tipo) {
            $pasajeros = array_filter($pasajeros, fn($p) => $p->tipo_pasajero === $filtro_tipo);
        }

        $pasajeros = array_values($pasajeros);

        $paises = $this->obtenerPaises();

        return view('pasajeros-frecuentes.index', compact('pasajeros', 'paises', 'filtro_nombre', 'filtro_pais', 'filtro_favorito', 'filtro_tipo', 'orden'));
    }

    public function create()
    {
        $paises = $this->obtenerPaises();

        return view('pasajeros-frecuentes.create', compact('paises'));
    }

    public function store(Request $request)
    {
        $idUsuario = Auth::id();

        $validado = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:150',
            'fecha_nacimiento' => 'required|date|before:today',
            'pais' => 'nullable|string|max:100',
        ]);

        $existe = DB::selectOne(
            'SELECT COUNT(*) as cnt FROM pasajeros_frecuentes
             WHERE user_id = ? AND LOWER(nombre) = ? AND LOWER(apellidos) = ?',
            [$idUsuario, strtolower($validado['nombre']), strtolower($validado['apellidos'])]
        );

        if ($existe->cnt > 0) {
            return redirect()->back()->withErrors(['duplicado' => 'Ya existe un pasajero con este nombre y apellidos en tu cuenta.'])->withInput();
        }

        $pais = $validado['pais'];
        if ($pais === '') {
            $pais = null;
        }

        DB::insert(
            'INSERT INTO pasajeros_frecuentes (user_id, nombre, apellidos, fecha_nacimiento, pais, favorito, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $idUsuario,
                $validado['nombre'],
                $validado['apellidos'],
                $validado['fecha_nacimiento'],
                $pais,
                0,
            ]
        );

        $this->registrarAuditoria($idUsuario, 'pasajeros_frecuentes', 'INSERT',
            "Nuevo pasajero: {$validado['nombre']} {$validado['apellidos']}");

        return redirect()->route('pasajeros-frecuentes.index')->with('exito', 'Pasajero frecuente creado correctamente.');
    }

    public function show($id)
    {
        $idUsuario = Auth::id();

        $pasajero = DB::selectOne(
            'SELECT * FROM pasajeros_frecuentes WHERE id = ? AND user_id = ?',
            [$id, $idUsuario]
        );

        if (!$pasajero) {
            abort(404);
        }

        $pasajero->tipo_pasajero = $this->calcularTipoPasajero($pasajero->fecha_nacimiento);
        $pasajero->edad = $this->calcularEdad($pasajero->fecha_nacimiento);

        // JOIN con logs_cambios y usuarios para obtener historial de operaciones sobre este pasajero
        $historial = DB::select(
            'SELECT lc.accion, lc.descripcion, lc.created_at AS fecha_operacion,
                    u.nombre AS usuario_responsable, u.email AS email_responsable
             FROM logs_cambios lc
             INNER JOIN usuarios u ON lc.user_id = u.id
             WHERE lc.tabla_afectada = ?
               AND lc.user_id = ?
               AND lc.descripcion LIKE ?
             ORDER BY lc.created_at DESC',
            ['pasajeros_frecuentes', $idUsuario, '%' . $pasajero->nombre . ' ' . $pasajero->apellidos . '%']
        );

        return view('pasajeros-frecuentes.show', compact('pasajero', 'historial'));
    }

    public function edit($id)
    {
        $idUsuario = Auth::id();

        $pasajero = DB::selectOne(
            'SELECT * FROM pasajeros_frecuentes WHERE id = ? AND user_id = ?',
            [$id, $idUsuario]
        );

        if (!$pasajero) {
            abort(404);
        }

        $pasajero = (array) $pasajero;
        $paises = $this->obtenerPaises();

        return view('pasajeros-frecuentes.edit', compact('pasajero', 'paises'));
    }

    public function update(Request $request, $id)
    {
        $idUsuario = Auth::id();

        $pasajero = DB::selectOne(
            'SELECT * FROM pasajeros_frecuentes WHERE id = ? AND user_id = ?',
            [$id, $idUsuario]
        );

        if (!$pasajero) {
            abort(404);
        }

        $validador = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:150',
            'fecha_nacimiento' => 'required|date|before:today',
            'pais' => 'nullable|string|max:100',
        ]);

        if ($validador->fails()) {
            return redirect()->route('pasajeros-frecuentes.index')
                ->withErrors($validador, 'pasajero_edit')
                ->withInput()
                ->with('edit_modal_id', $id);
        }

        $validado = $validador->validated();

        $existe = DB::selectOne(
            'SELECT COUNT(*) as cnt FROM pasajeros_frecuentes
             WHERE user_id = ? AND id != ? AND LOWER(nombre) = ? AND LOWER(apellidos) = ?',
            [$idUsuario, $id, strtolower($validado['nombre']), strtolower($validado['apellidos'])]
        );

        if ($existe->cnt > 0) {
            return redirect()->route('pasajeros-frecuentes.index')
                ->withErrors([
                    'duplicado' => 'Ya existe otro pasajero con este nombre y apellidos en tu cuenta.',
                ], 'pasajero_edit')
                ->withInput()
                ->with('edit_modal_id', $id);
        }

        $pais = $validado['pais'];
        if ($pais === '') {
            $pais = null;
        }

        DB::update(
            'UPDATE pasajeros_frecuentes SET nombre = ?, apellidos = ?, fecha_nacimiento = ?,
             pais = ?, updated_at = NOW()
             WHERE id = ? AND user_id = ?',
            [
                $validado['nombre'],
                $validado['apellidos'],
                $validado['fecha_nacimiento'],
                $pais,
                $id,
                $idUsuario,
            ]
        );

        $this->registrarAuditoria($idUsuario, 'pasajeros_frecuentes', 'UPDATE',
            "Actualizado pasajero ID $id: {$validado['nombre']} {$validado['apellidos']}");

        return redirect()->route('pasajeros-frecuentes.index')->with('exito', 'Pasajero actualizado correctamente.');
    }

    public function destroy($id)
    {
        $idUsuario = Auth::id();

        $pasajero = DB::selectOne(
            'SELECT * FROM pasajeros_frecuentes WHERE id = ? AND user_id = ?',
            [$id, $idUsuario]
        );

        if (!$pasajero) {
            abort(404);
        }

        DB::delete('DELETE FROM pasajeros_frecuentes WHERE id = ? AND user_id = ?', [$id, $idUsuario]);

        $this->registrarAuditoria($idUsuario, 'pasajeros_frecuentes', 'DELETE',
            "Eliminado pasajero: {$pasajero->nombre} {$pasajero->apellidos}");

        return redirect()->route('pasajeros-frecuentes.index')->with('exito', 'Pasajero eliminado correctamente.');
    }

    public function toggleFavorito($id)
    {
        $idUsuario = Auth::id();

        $pasajero = DB::selectOne(
            'SELECT favorito FROM pasajeros_frecuentes WHERE id = ? AND user_id = ?',
            [$id, $idUsuario]
        );

        if (!$pasajero) {
            abort(404);
        }

        if ($pasajero->favorito) {
            $nuevoValor = 0;
        } else {
            $nuevoValor = 1;
        }

        DB::update(
            'UPDATE pasajeros_frecuentes SET favorito = ?, updated_at = NOW() WHERE id = ? AND user_id = ?',
            [$nuevoValor, $id, $idUsuario]
        );

        if ($nuevoValor) {
            $mensajeExito = 'Pasajero marcado como favorito.';
        } else {
            $mensajeExito = 'Pasajero desmarcado como favorito.';
        }

        return redirect()->back()->with('exito', $mensajeExito);
    }

    private function calcularTipoPasajero($fechaNacimiento): string
    {
        try {
            $fecha = Carbon::parse($fechaNacimiento);
        } catch (Throwable $e) {
            return 'adulto';
        }

        // Si hay datos inconsistentes en BD (fecha futura), evitamos clasificaciones inválidas.
        if ($fecha->isFuture()) {
            return 'adulto';
        }

        $edad = $fecha->diffInYears(Carbon::now());

        if ($edad >= 0 && $edad <= 2) {
            return 'bebe';
        } elseif ($edad > 2 && $edad < 16) {
            return 'nino';
        } else {
            return 'adulto';
        }
    }

    private function calcularEdad($fechaNacimiento): array
    {
        try {
            $fecha = Carbon::parse($fechaNacimiento);
        } catch (Throwable $e) {
            return [
                'anos' => 0,
                'meses' => 0,
                'dias' => 0,
            ];
        }

        if ($fecha->isFuture()) {
            return [
                'anos' => 0,
                'meses' => 0,
                'dias' => 0,
            ];
        }

        // Cálculo con DateTime para obtener enteros exactos (sin decimales/signos).
        $nacimiento = new DateTimeImmutable($fecha->format('Y-m-d'));
        $hoy = new DateTimeImmutable(Carbon::now()->format('Y-m-d'));
        $interval = $nacimiento->diff($hoy);

        return [
            'anos' => (int) $interval->y,
            'meses' => (int) $interval->m,
            'dias' => (int) $interval->d,
        ];
    }

    private function obtenerPaises(): array
    {
        return [
            'España', 'Italia', 'Francia', 'Alemania', 'Portugal', 'Bélgica',
            'Países Bajos', 'Suiza', 'Austria', 'Suecia', 'Noruega', 'Dinamarca',
            'Finlandia', 'Polonia', 'República Checa', 'Eslovaquia', 'Hungría',
            'Rumania', 'Bulgaria', 'Grecia', 'Reino Unido', 'Irlanda', 'Canadá',
            'Estados Unidos', 'México', 'Brasil', 'Argentina', 'Chile', 'Colombia',
            'Perú', 'Japón', 'China', 'India', 'Tailandia',
        ];
    }

    private function registrarAuditoria($userId, $tabla, $accion, $descripcion): void
    {
        DB::insert(
            'INSERT INTO logs_cambios (user_id, tabla_afectada, accion, descripcion, created_at, updated_at)
             VALUES (?, ?, ?, ?, NOW(), NOW())',
            [$userId, $tabla, $accion, $descripcion]
        );
    }
}
