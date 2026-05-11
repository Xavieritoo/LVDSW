<?php

namespace App\Http\Controllers;

use App\Models\PasajeroFrecuente;
use App\Models\Vuelo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PasajeroController extends Controller
{
    public function pasajeros(Request $request)
    {
        $pasajerosFrecuentesPorTipo = $this->obtenerPasajerosFrecuentesPorTipoUsuario();
        $reserva = session('reserva');
        if ($reserva && !$request->hasAny(['vuelo_ida_id', 'adultos'])) {
            $seleccionParam = [];
            if (isset($reserva['seleccion'])) {
                $seleccionParam = $reserva['seleccion'];
            }
            $tipoViajeParam = 'solo_ida';
            if (isset($reserva['tipo_viaje'])) {
                $tipoViajeParam = $reserva['tipo_viaje'];
            }

            return view('pasajeros', [
                'tipoViaje'      => $reserva['tipo_viaje'],
                'adultos'        => $reserva['cantidades']['adultos'],
                'menores'        => $reserva['cantidades']['menores'],
                'infantes'       => $reserva['cantidades']['infantes'],
                'totalPasajeros' => array_sum($reserva['cantidades']),
                'esSchengen'     => $reserva['es_schengen'],
                'pasajerosFrecuentesPorTipo' => $pasajerosFrecuentesPorTipo,
                'detalleVuelosSeleccionados' => $this->obtenerDetalleVuelosSeleccionados(
                    $seleccionParam,
                    $tipoViajeParam
                ),
            ]);
        }

        $validator = Validator::make($request->all(), $this->reglaSeleccion());
        $this->verificarSeleccion($validator, $request);

        if ($validator->fails()) {
            return redirect()->route('principal')
                ->withErrors($validator)
                ->withInput();
        }

        $cantidades = $this->obtenerCantidades($request);
        $contexto   = $this->contextoVuelo($request);

        session()->put('reserva', [
            'tipo_viaje'      => $request->input('tipo_viaje', 'ida_vuelta'),
            'es_schengen'     => $contexto['esSchengen'],
            'busqueda'        => [
                'tipo_viaje'   => $request->input('tipo_viaje', 'ida_vuelta'),
                'origen'       => $request->input('origen'),
                'destino'      => $request->input('destino'),
                'fecha_ida'    => $request->input('fecha_ida'),
                'fecha_vuelta' => $request->input('fecha_vuelta'),
                'adultos'      => $cantidades['adultos'],
                'menores'      => $cantidades['menores'],
                'infantes'     => $cantidades['infantes'],
                'zona'         => $request->input('zona', 'all'),
            ],
            'seleccion'       => $this->datosSeleccion($request),
            'cantidades'      => $cantidades,
            'correo_contacto' => '',
            'pasajeros'       => ['adultos' => [], 'menores' => [], 'infantes' => []],
            'equipajes'       => [],
        ]);

        return view('pasajeros', [
            'tipoViaje'      => $request->input('tipo_viaje', 'ida_vuelta'),
            'adultos'        => $cantidades['adultos'],
            'menores'        => $cantidades['menores'],
            'infantes'       => $cantidades['infantes'],
            'totalPasajeros' => array_sum($cantidades),
            'esSchengen'     => $contexto['esSchengen'],
            'pasajerosFrecuentesPorTipo' => $pasajerosFrecuentesPorTipo,
            'detalleVuelosSeleccionados' => $this->obtenerDetalleVuelosSeleccionados($this->datosSeleccion($request), $request->input('tipo_viaje', 'ida_vuelta')),
        ]);
    }

    public function guardarPasajeros(Request $request)
    {
        $reserva = session('reserva');
        if (!$reserva) {
            return redirect()->route('principal')
                ->with('error', 'Sesión expirada. Inicia la búsqueda de nuevo.');
        }

        $validator = Validator::make(
            $request->all(),
            $this->reglasPasajeros()
        );
        $this->verificarPasajeros($validator, $request, $reserva['cantidades']);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        session()->put('reserva.correo_contacto', $request->input('correo_contacto'));
        session()->put('reserva.pasajeros', $this->datosPasajeros($request));

        return redirect()->route('flight.baggage');
    }

    public function mostrarEquipajes()
    {
        $reserva = session('reserva');
        if (!$reserva || empty($reserva['pasajeros']['adultos']) && empty($reserva['pasajeros']['menores'])) {
            return redirect()->route('flight.passengers');
        }

        $pasajeros = $reserva['pasajeros'];
        $seleccion = $reserva['seleccion'];
        $tipoViaje = $reserva['tipo_viaje'];

        $tarifasEquipaje = [20 => 120, 25 => 130, 30 => 140];

        $listaPasajeros = [];
        foreach ($pasajeros['adultos'] as $i => $p) {
            $nombreAdulto = '';
            if (isset($p['nombre'])) {
                $nombreAdulto = $p['nombre'];
            }
            $apellidosAdulto = '';
            if (isset($p['apellidos'])) {
                $apellidosAdulto = $p['apellidos'];
            }
            $listaPasajeros[] = ['clave' => 'adulto_' . $i, 'tipo' => 'Adulto', 'nombre' => trim($nombreAdulto . ' ' . $apellidosAdulto)];
        }
        foreach ($pasajeros['menores'] as $i => $p) {
            $nombreMenor = '';
            if (isset($p['nombre'])) {
                $nombreMenor = $p['nombre'];
            }
            $apellidosMenor = '';
            if (isset($p['apellidos'])) {
                $apellidosMenor = $p['apellidos'];
            }
            $listaPasajeros[] = ['clave' => 'menor_' . $i, 'tipo' => 'Niño', 'nombre' => trim($nombreMenor . ' ' . $apellidosMenor)];
        }
        foreach ($pasajeros['infantes'] as $i => $p) {
            $nombreInfante = '';
            if (isset($p['nombre'])) {
                $nombreInfante = $p['nombre'];
            }
            $apellidosInfante = '';
            if (isset($p['apellidos'])) {
                $apellidosInfante = $p['apellidos'];
            }
            $listaPasajeros[] = ['clave' => 'infante_' . $i, 'tipo' => 'Bebé', 'nombre' => trim($nombreInfante . ' ' . $apellidosInfante)];
        }

        $precioBaseIda    = (float) $seleccion['precio_ida'];
        $precioBaseVuelta = 0;
        if ($tipoViaje === 'ida_vuelta' && isset($seleccion['precio_vuelta'])) {
            $precioBaseVuelta = (float) $seleccion['precio_vuelta'];
        }
        $pasajerosFacturables = count($pasajeros['adultos']) + count($pasajeros['menores']);
        $precioBaseTotal  = ($precioBaseIda + $precioBaseVuelta) * $pasajerosFacturables;

        return view('equipajes', [
            'tipoViaje'       => $tipoViaje,
            'esSchengen'      => $reserva['es_schengen'],
            'correoContacto'  => $reserva['correo_contacto'],
            'detalleVuelosSeleccionados' => $this->obtenerDetalleVuelosSeleccionados($seleccion, $tipoViaje),
            'tarifasEquipaje' => $tarifasEquipaje,
            'listaPasajeros'  => $listaPasajeros,
            'precioBaseTotal' => $precioBaseTotal,
        ]);
    }

    public function guardarEquipajes(Request $request)
    {
        if (!session('reserva')) {
            return redirect()->route('principal');
        }

        session()->put('reserva.equipajes', (array) $request->input('equipajes', []));

        return redirect()->route('flight.summary');
    }

    private function reglaSeleccion(): array
    {
        return [
            'adultos'         => 'required|integer|min:0|max:9',
            'menores'         => 'required|integer|min:0|max:8',
            'infantes'        => 'required|integer|min:0|max:8',
            'tipo_viaje'      => 'nullable|in:solo_ida,ida_vuelta',
            'vuelo_ida_id'    => 'required|integer|exists:vuelos,id',
            'plan_ida'        => 'required|in:PLANIT EASY,PLANIT COMFORT',
            'precio_ida'      => 'required|numeric|min:0',
            'vuelo_vuelta_id' => 'nullable|integer|exists:vuelos,id',
            'plan_vuelta'     => 'nullable|in:PLANIT EASY,PLANIT COMFORT',
            'precio_vuelta'   => 'nullable|numeric|min:0',
        ];
    }

    private function reglasPasajeros(): array
    {
        return [
            'correo_contacto' => 'required|email',
            'adultos' => 'nullable|array',
            'adultos.*.nombre' => 'required|string|max:100',
            'adultos.*.apellidos' => 'required|string|max:150',
            'adultos.*.fecha_nacimiento' => 'required|date|before_or_equal:today',
            'menores' => 'nullable|array',
            'menores.*.nombre' => 'required|string|max:100',
            'menores.*.apellidos' => 'required|string|max:150',
            'menores.*.fecha_nacimiento' => 'required|date|before_or_equal:today',
            'infantes' => 'nullable|array',
            'infantes.*.nombre' => 'required|string|max:100',
            'infantes.*.apellidos' => 'required|string|max:150',
            'infantes.*.fecha_nacimiento' => 'required|date|before_or_equal:today',
        ];
    }

    private function obtenerPasajerosFrecuentesPorTipoUsuario(): array
    {
        if (!auth()->check()) {
            return [
                'adultos' => [],
                'menores' => [],
                'infantes' => [],
            ];
        }

        $pasajeros = PasajeroFrecuente::query()
            ->where('user_id', auth()->id())
            ->orderByDesc('favorito')
            ->orderBy('nombre')
            ->orderBy('apellidos')
            ->get(['id', 'nombre', 'apellidos', 'fecha_nacimiento'])
            ->map(function (PasajeroFrecuente $pasajero) {
                $fechaNacimiento = optional($pasajero->fecha_nacimiento)->format('Y-m-d');

                return [
                    'id' => $pasajero->id,
                    'nombre' => $pasajero->nombre,
                    'apellidos' => $pasajero->apellidos,
                    'fecha_nacimiento' => $fechaNacimiento,
                    'tipo_formulario' => $this->resolverTipoFormularioPasajero($fechaNacimiento),
                ];
            })
            ->all();

        $agrupados = [
            'adultos' => [],
            'menores' => [],
            'infantes' => [],
        ];

        foreach ($pasajeros as $pasajero) {
            $tipoFormulario = $pasajero['tipo_formulario'];
            unset($pasajero['tipo_formulario']);

            if (isset($agrupados[$tipoFormulario])) {
                $agrupados[$tipoFormulario][] = $pasajero;
            }
        }

        return $agrupados;
    }

    private function resolverTipoFormularioPasajero(?string $fechaNacimiento): string
    {
        if (empty($fechaNacimiento)) {
            return 'adultos';
        }

        $edad = Carbon::parse($fechaNacimiento)->age;

        if ($edad < 2) {
            return 'infantes';
        }

        if ($edad <= 15) {
            return 'menores';
        }

        return 'adultos';
    }

    private function verificarSeleccion($validator, Request $request): void
    {
        $validator->after(function ($validator) use ($request) {
            $cantidades = $this->obtenerCantidades($request);
            $this->validarMinimoPasajeros($validator, $cantidades, 'Debes seleccionar al menos un pasajero.');
            $this->validarInfantesConAdultos($validator, $cantidades);
            $this->validarCamposVueltaVuelta($validator, $request);
        });
    }

    private function verificarPasajeros($validator, Request $request, array $cantidades): void
    {
        $validator->after(function ($validator) use ($request, $cantidades) {
            $this->validarMinimoPasajeros($validator, $cantidades, 'Debes incluir al menos un pasajero.');
            $this->validarInfantesConAdultos($validator, $cantidades);
            $this->validarCoincidenCantidades($validator, $request, $cantidades);
            $this->validarRangoEdad($validator, $request, 'adultos', 16, 150, 'debe ser mayor de 16 años para ser adulto.');
            $this->validarRangoEdad($validator, $request, 'menores', 2, 15, 'debe estar entre 2 y 15 años.');
            $this->validarRangoEdad($validator, $request, 'infantes', 0, 2, 'debe estar entre 0 y 2 años.');
        });
    }

    private function validarMinimoPasajeros($validator, array $cantidades, string $mensaje): void
    {
        if (array_sum($cantidades) < 1) {
            $validator->errors()->add('adultos', $mensaje);
        }
    }

    private function validarInfantesConAdultos($validator, array $cantidades): void
    {
        if ($cantidades['infantes'] > $cantidades['adultos']) {
            $validator->errors()->add('infantes', 'Cada bebé debe viajar con un adulto.');
        }
    }

    private function validarCoincidenCantidades($validator, Request $request, array $cantidades): void
    {
        foreach (['adultos', 'menores', 'infantes'] as $campo) {
            if (count((array) $request->input($campo, [])) !== $cantidades[$campo]) {
                $validator->errors()->add($campo, "El número de {$campo} no coincide con la selección inicial.");
            }
        }
    }

    private function validarCamposVueltaVuelta($validator, Request $request): void
    {
        if ($request->input('tipo_viaje') !== 'ida_vuelta') {
            return;
        }
        foreach (['vuelo_vuelta_id', 'plan_vuelta', 'precio_vuelta'] as $campo) {
            if (!$request->filled($campo)) {
                $validator->errors()->add($campo, 'Faltan datos del vuelo de vuelta.');
            }
        }
    }

    private function validarRangoEdad($validator, Request $request, string $campo, int $min, int $max, string $mensaje): void
    {
        foreach ((array) $request->input($campo, []) as $index => $persona) {
            if (empty($persona['fecha_nacimiento'])) {
                continue;
            }
            $edad = $this->calcularEdad($persona['fecha_nacimiento']);
            if ($edad < $min || $edad > $max) {
                $validator->errors()->add(
                    "{$campo}.{$index}.fecha_nacimiento",
                    "Edad inválida en posición " . ($index + 1) . ": {$mensaje}"
                );
            }
        }
    }

    private function calcularEdad($fechaNacimiento): int
    {
        return Carbon::parse($fechaNacimiento)->age;
    }

    private function obtenerCantidades(Request $request): array
    {
        return [
            'adultos'  => (int) $request->input('adultos', 0),
            'menores'  => (int) $request->input('menores', 0),
            'infantes' => (int) $request->input('infantes', 0),
        ];
    }

    private function contextoVuelo(Request $request): array
    {
        $vueloIda = Vuelo::findOrFail((int) $request->input('vuelo_ida_id'));
        $vueloVuelta = null;
        if ($request->filled('vuelo_vuelta_id')) {
            $vueloVuelta = Vuelo::findOrFail((int) $request->input('vuelo_vuelta_id'));
        }

        return [
            'vueloIda'    => $vueloIda,
            'vueloVuelta' => $vueloVuelta,
            'esSchengen'  => (bool) $vueloIda->es_schengen && (!$vueloVuelta || (bool) $vueloVuelta->es_schengen),
        ];
    }

    private function datosSeleccion(Request $request): array
    {
        $vueloVueltaId = null;
        if ($request->filled('vuelo_vuelta_id')) {
            $vueloVueltaId = (int) $request->input('vuelo_vuelta_id');
        }

        $precioVuelta = null;
        if ($request->filled('precio_vuelta')) {
            $precioVuelta = (float) $request->input('precio_vuelta');
        }

        return [
            'vuelo_ida_id'    => (int) $request->input('vuelo_ida_id'),
            'plan_ida'        => $request->input('plan_ida'),
            'precio_ida'      => (float) $request->input('precio_ida'),
            'vuelo_vuelta_id' => $vueloVueltaId,
            'plan_vuelta'     => $request->input('plan_vuelta'),
            'precio_vuelta'   => $precioVuelta,
        ];
    }

    private function datosPasajeros(Request $request): array
    {
        return [
            'adultos'  => (array) $request->input('adultos', []),
            'menores'  => (array) $request->input('menores', []),
            'infantes' => (array) $request->input('infantes', []),
        ];
    }

    private function obtenerDetalleVuelosSeleccionados(array $seleccion, string $tipoViaje): array
    {
        $detalle = [];

        $vueloIdaId = null;
        if (isset($seleccion['vuelo_ida_id'])) {
            $vueloIdaId = $seleccion['vuelo_ida_id'];
        }
        $vueloIda = Vuelo::with(['ciudadOrigen', 'ciudadDestino'])->find($vueloIdaId);
        if ($vueloIda) {
            $planIda = null;
            if (isset($seleccion['plan_ida'])) {
                $planIda = $seleccion['plan_ida'];
            }
            $precioIda = null;
            if (isset($seleccion['precio_ida'])) {
                $precioIda = $seleccion['precio_ida'];
            }
            $detalle['ida'] = $this->mapearDetalleVuelo($vueloIda, $planIda, $precioIda);
        }

        if ($tipoViaje === 'ida_vuelta' && !empty($seleccion['vuelo_vuelta_id'])) {
            $vueloVuelta = Vuelo::with(['ciudadOrigen', 'ciudadDestino'])->find($seleccion['vuelo_vuelta_id']);
            if ($vueloVuelta) {
                $planVuelta = null;
                if (isset($seleccion['plan_vuelta'])) {
                    $planVuelta = $seleccion['plan_vuelta'];
                }
                $precioVuelta = null;
                if (isset($seleccion['precio_vuelta'])) {
                    $precioVuelta = $seleccion['precio_vuelta'];
                }
                $detalle['vuelta'] = $this->mapearDetalleVuelo($vueloVuelta, $planVuelta, $precioVuelta);
            }
        }

        return $detalle;
    }

    private function mapearDetalleVuelo(Vuelo $vuelo, ?string $plan, $precio): array
    {
        $origen = $vuelo->origen;
        if (!$origen) {
            $ciudadOrigen = $vuelo->ciudadOrigen;
            $origen = 'Origen';
            if ($ciudadOrigen) {
                $origen = $ciudadOrigen->nombre;
            }
        }

        $destino = $vuelo->destino;
        if (!$destino) {
            $ciudadDestino = $vuelo->ciudadDestino;
            $destino = 'Destino';
            if ($ciudadDestino) {
                $destino = $ciudadDestino->nombre;
            }
        }

        $fechaSalidaObj = $vuelo->hora_salida_programada;
        if (!$fechaSalidaObj) {
            $fechaSalidaObj = $vuelo->fecha_salida;
        }
        $salida = '--';
        if ($fechaSalidaObj) {
            $salida = $fechaSalidaObj->format('d/m/Y H:i');
        }

        $fechaLlegadaObj = $vuelo->fecha_llegada;
        if (!$fechaLlegadaObj) {
            $fechaLlegadaObj = $vuelo->hora_llegada_programada;
        }
        $llegada = '--';
        if ($fechaLlegadaObj) {
            $llegada = $fechaLlegadaObj->format('d/m/Y H:i');
        }

        $planTexto = $plan;
        if (!$planTexto) {
            $planTexto = 'Plan no definido';
        }

        $precioTexto = '--';
        if (is_numeric($precio)) {
            $precioTexto = number_format((float) $precio, 2, ',', '.') . ' EUR';
        }

        $zona = 'Fuera Schengen';
        if ($vuelo->es_schengen) {
            $zona = 'Schengen';
        }

        return [
            'ruta' => $origen . ' -> ' . $destino,
            'salida' => $salida,
            'llegada' => $llegada,
            'plan' => $planTexto,
            'precio' => $precioTexto,
            'zona' => $zona,
        ];
    }
}
