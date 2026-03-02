<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Session;
use App\Servicios\UsuarioServicio;

class ConfiguracionController extends Controller
{
    private $usuarioServicio;

    public function __construct()
    {
        $this->usuarioServicio = new UsuarioServicio();
    }

    /**
     * Array centralizado de países
     */
    private function getPaises()
    {
        return [
            'ES' => ['nombre' => 'España', 'prefijo' => '+34'],
            'FR' => ['nombre' => 'Francia', 'prefijo' => '+33'],
            'DE' => ['nombre' => 'Alemania', 'prefijo' => '+49'],
            'IT' => ['nombre' => 'Italia', 'prefijo' => '+39'],
            'PT' => ['nombre' => 'Portugal', 'prefijo' => '+351'],
            'US' => ['nombre' => 'Estados Unidos', 'prefijo' => '+1'],
            'MX' => ['nombre' => 'México', 'prefijo' => '+52'],
            'AR' => ['nombre' => 'Argentina', 'prefijo' => '+54'],
            'BR' => ['nombre' => 'Brasil', 'prefijo' => '+55'],
            'CL' => ['nombre' => 'Chile', 'prefijo' => '+56'],
            'CO' => ['nombre' => 'Colombia', 'prefijo' => '+57'],
            'PE' => ['nombre' => 'Perú', 'prefijo' => '+51'],
            'VE' => ['nombre' => 'Venezuela', 'prefijo' => '+58'],
            'UY' => ['nombre' => 'Uruguay', 'prefijo' => '+598'],
            'PY' => ['nombre' => 'Paraguay', 'prefijo' => '+595'],
            'BO' => ['nombre' => 'Bolivia', 'prefijo' => '+591'],
            'EC' => ['nombre' => 'Ecuador', 'prefijo' => '+593'],
            'GB' => ['nombre' => 'Reino Unido', 'prefijo' => '+44'],
            'IE' => ['nombre' => 'Irlanda', 'prefijo' => '+353'],
            'NL' => ['nombre' => 'Países Bajos', 'prefijo' => '+31'],
            'BE' => ['nombre' => 'Bélgica', 'prefijo' => '+32'],
            'CH' => ['nombre' => 'Suiza', 'prefijo' => '+41'],
            'AT' => ['nombre' => 'Austria', 'prefijo' => '+43'],
            'SE' => ['nombre' => 'Suecia', 'prefijo' => '+46'],
            'NO' => ['nombre' => 'Noruega', 'prefijo' => '+47'],
            'DK' => ['nombre' => 'Dinamarca', 'prefijo' => '+45'],
            'FI' => ['nombre' => 'Finlandia', 'prefijo' => '+358'],
        ];
    }

    /**
     * Mostrar configuración
     */
    public function index()
    {
        $usuarioId = Session::get('usuario_id');
        if (!$usuarioId) {
            return redirect()->route('login');
        }

        $usuario = $this->usuarioServicio->obtenerPorId($usuarioId);
        $paises = $this->getPaises();

        $telefonoNumero = '';
        $prefijoSeleccionado = null;
        $paisSeleccionado = null;

        if ($usuario->telefono) {
            foreach ($paises as $codigo => $pais) {
                if (str_starts_with($usuario->telefono, $pais['prefijo'])) {
                    $prefijoSeleccionado = $pais['prefijo'];
                    $telefonoNumero = trim(str_replace($pais['prefijo'], '', $usuario->telefono));
                    break;
                }
            }
            $paisSeleccionado = $usuario->pais;
        }

        return view('configuracion', compact(
            'usuario',
            'paises',
            'telefonoNumero',
            'prefijoSeleccionado',
            'paisSeleccionado'
        ));
    }

    /**
     * Actualizar datos
     */
    public function update(Request $request)
    {
        $usuarioId = Session::get('usuario_id');
        if (!$usuarioId) {
            return redirect()->route('login');
        }

        $usuario = $this->usuarioServicio->obtenerPorId($usuarioId);
        $paises = $this->getPaises();

        $validated = $request->validate([
            'telefono' => 'nullable|string|max:50',
            'prefijo' => ['nullable', 'string', Rule::in(array_column($paises, 'prefijo'))],
            'pais' => ['nullable', 'string', Rule::in(array_keys($paises))],
            'codigo_postal' => 'nullable|string|max:20',
            'poblacion' => 'nullable|string|max:100',
            'direccion' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date|after_or_equal:1900-01-01',
            'documento_identidad' => 'nullable|string|max:20',
        ]);

        // Construir teléfono con prefijo
        if (!empty($validated['telefono']) && !empty($validated['prefijo'])) {
            $validated['telefono'] = $validated['prefijo'] . ' ' . $validated['telefono'];
        } elseif (!empty($validated['telefono'])) {
            $validated['telefono'] = $validated['telefono'];
        } else {
            $validated['telefono'] = null;
        }

        // prefijo no es columna de la tabla; lo eliminamos antes de la consulta
        unset($validated['prefijo']);

        // Actualizar usando el servicio
        $this->usuarioServicio->actualizarDatos($usuarioId, $validated);

        return redirect()->route('configuracion')
            ->with('success', '¡Tus datos se han actualizado correctamente!');
    }
}
