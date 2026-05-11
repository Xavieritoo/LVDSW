<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use App\Models\Oferta;
use App\Models\Vuelo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DestinoController extends Controller
{
    protected function construirInfoAeropuerto(Ciudad $destino): array
    {
        $iata = strtoupper((string) $destino->codigo_iata);

        $base = [
            'titulo' => 'Aeropuerto de ' . $destino->nombre . ' (' . $iata . ')',
            'resumen' => 'Información orientativa para planificar tu salida y llegada en el aeropuerto de ' . $destino->nombre . '.',
            'terminal' => 'Consulta la terminal asignada en tu tarjeta de embarque y en los paneles del aeropuerto, ya que puede variar según operativa diaria.',
            'facturacion' => [
                'Cierre estimado de facturación (vuelos nacionales/Schengen): 40 minutos antes de la salida.',
                'Cierre estimado de facturación (vuelos no Schengen): 60 minutos antes de la salida.',
                'Cierre estimado de puerta de embarque: 20 minutos antes de la salida.',
            ],
            'transporte' => [
                'Taxi y VTC en paradas oficiales de llegadas.',
                'Bus aeropuerto con frecuencias reforzadas en hora punta.',
                'Tren/metro según conectividad de la ciudad.',
                'Alquiler de coche en terminal o edificio anexo.',
            ],
            'servicios' => [
                'Control de seguridad con carriles estándar y prioridad según tarifa/disponibilidad.',
                'Restauración, tiendas duty free y zonas de descanso en terminal.',
                'Asistencia PMR bajo solicitud previa con la aerolínea.',
                'Mostradores de información y objetos perdidos gestionados por el aeropuerto.',
            ],
            'nota' => 'Los horarios y terminales son orientativos y pueden cambiar por motivos operativos.',
        ];

        $porIata = [
            'BCN' => [
                'titulo' => 'Aeropuerto de Barcelona-El Prat (BCN)',
                'resumen' => 'El aeropuerto Josep Tarradellas Barcelona-El Prat es el principal punto de conexión aérea de Cataluña y opera vuelos nacionales, europeos e internacionales.',
                'terminal' => 'La operativa se concentra en T1 y T2. En rutas regulares de corto y medio radio, muchas salidas se canalizan por la T1, pero conviene confirmar siempre en tu reserva.',
                'transporte' => [
                    'Aerobús entre el aeropuerto y Plaça Catalunya con salidas frecuentes.',
                    'Línea de metro L9 Sud con conexión a la red urbana.',
                    'Tren Rodalies R2 Nord (desde T2) hacia Barcelona y área metropolitana.',
                    'Taxi y VTC autorizados en salidas de terminal.',
                ],
            ],
            'MAD' => [
                'titulo' => 'Aeropuerto Adolfo Suárez Madrid-Barajas (MAD)',
                'resumen' => 'Madrid-Barajas es uno de los principales hubs del sur de Europa, con alta conectividad nacional e internacional.',
                'terminal' => 'El aeropuerto opera con T1, T2, T3 y T4/T4S. Revisa siempre la terminal en el localizador para evitar desplazamientos internos de última hora.',
                'transporte' => [
                    'Metro Línea 8 hasta Nuevos Ministerios.',
                    'Cercanías C1/C10 con acceso a la T4.',
                    'Bus Exprés Aeropuerto 24 horas al centro de Madrid.',
                    'Taxi/VTC y alquiler de coche en todas las terminales principales.',
                ],
            ],
            'CDG' => [
                'titulo' => 'Aeropuerto Paris-Charles de Gaulle (CDG)',
                'resumen' => 'Paris-Charles de Gaulle es el mayor aeropuerto de Francia y uno de los más transitados de Europa.',
                'terminal' => 'CDG dispone de varias terminales y satélites. Los tiempos de traslado interno pueden ser elevados, por lo que se recomienda llegar con antelación.',
                'transporte' => [
                    'RER B hacia el centro de París.',
                    'Buses directos a puntos clave de la ciudad.',
                    'Taxi oficial con tarifa regulada por zonas.',
                    'VTC y alquiler de coche en terminales habilitadas.',
                ],
            ],
            'ORY' => [
                'titulo' => 'Aeropuerto de Paris-Orly (ORY)',
                'resumen' => 'Paris-Orly concentra gran parte del tráfico doméstico francés y rutas europeas de corto y medio radio.',
                'terminal' => 'El aeropuerto se divide en zonas Orly 1-2-3-4. Consulta tu puerta y zona con antelación para optimizar el acceso al control.',
            ],
            'LHR' => [
                'titulo' => 'Aeropuerto de Londres-Heathrow (LHR)',
                'resumen' => 'Heathrow es uno de los mayores aeropuertos internacionales del mundo, con alto volumen de conexiones intercontinentales.',
                'terminal' => 'Opera con múltiples terminales (T2, T3, T4 y T5). El tránsito entre terminales puede requerir bus o tren interno.',
            ],
            'LIS' => [
                'titulo' => 'Aeropuerto Humberto Delgado de Lisboa (LIS)',
                'resumen' => 'Lisboa ofrece conexiones frecuentes con la península ibérica y capitales europeas.',
                'terminal' => 'El aeropuerto opera principalmente con Terminal 1 y Terminal 2; revisa en tu reserva la asignación de compañía y puerta.',
            ],
            'FCO' => [
                'titulo' => 'Aeropuerto de Roma-Fiumicino (FCO)',
                'resumen' => 'Roma-Fiumicino es la principal puerta internacional de Italia, con gran actividad durante todo el año.',
                'terminal' => 'La distribución por terminales varía según destino y aerolínea. Se recomienda revisar paneles y app del aeropuerto al llegar.',
            ],
            'AMS' => [
                'titulo' => 'Aeropuerto de Amsterdam-Schiphol (AMS)',
                'resumen' => 'Schiphol destaca por su terminal integrada y una red de rutas europeas e intercontinentales muy extensa.',
                'terminal' => 'Aunque la terminal es unificada, las puertas se distribuyen en distintos muelles; calcula tiempo adicional para caminar hasta embarque.',
            ],
        ];

        $extras = [];
        if (isset($porIata[$iata])) {
            $extras = $porIata[$iata];
        }

        return array_merge($base, $extras);
    }

    protected function construirInfoTuristica(Ciudad $destino): array
    {
        $nombre = $this->limpiarNombreCiudad($destino->nombre);
        $pais = $destino->pais;
        $iata = strtoupper((string) $destino->codigo_iata);

        $base = [
            'intro' => $nombre . ' combina puntos culturales, zonas de ocio y propuestas gastronómicas que permiten planificar escapadas de fin de semana o viajes más largos.',
            'imprescindibles' => [
                'Centro histórico y principales plazas de la ciudad.',
                'Museos o espacios culturales representativos.',
                'Miradores, paseos urbanos o zonas naturales cercanas.',
            ],
            'barrios' => [
                'Área céntrica para visitas culturales y compras.',
                'Zona moderna con restaurantes, ocio y vida nocturna.',
                'Barrios residenciales con ambiente local y menor afluencia turística.',
            ],
            'gastronomia' => [
                'Prueba la cocina local en mercados y restaurantes tradicionales.',
                'Reserva en zonas de alta demanda durante fines de semana y festivos.',
            ],
            'movilidad' => [
                'Prioriza transporte público para desplazarte en horas punta.',
                'Valora bonos de 24/48/72h para reducir costes de movilidad.',
            ],
            'mejor_epoca' => 'Primavera y otoño suelen ofrecer un equilibrio favorable entre clima y afluencia de visitantes.',
            'nota' => 'Revisa siempre las fuentes oficiales de turismo local para horarios, eventos y posibles restricciones.',
        ];

        $porIata = [
            'BCN' => [
                'intro' => 'Barcelona ofrece una mezcla única de arquitectura modernista, playa urbana y barrios con identidad propia.',
                'imprescindibles' => [
                    'Sagrada Familia y entorno modernista de Gaudí.',
                    'Barrio Gótico, Catedral y plazas históricas.',
                    'Passeig de Gràcia, Casa Batlló y La Pedrera.',
                    'Montjuïc y su red de museos y miradores.',
                ],
                'barrios' => [
                    'Eixample para arquitectura y compras.',
                    'El Born para cultura, tapas y vida nocturna.',
                    'Barceloneta y frente marítimo para playa y ocio.',
                ],
                'gastronomia' => [
                    'Cocina catalana, arroces y tapeo en mercados como la Boqueria.',
                    'Evita horas punta en zonas turísticas para una mejor experiencia.',
                ],
            ],
            'MAD' => [
                'intro' => 'Madrid destaca por su oferta cultural, parques urbanos y una escena gastronómica muy variada durante todo el año.',
                'imprescindibles' => [
                    'Triángulo del Arte: Prado, Reina Sofía y Thyssen.',
                    'Parque del Retiro y entorno de Alcalá.',
                    'Palacio Real, Plaza Mayor y Madrid de los Austrias.',
                ],
                'barrios' => [
                    'Sol y Gran Vía para primera visita.',
                    'Malasaña y Chueca para ambiente urbano y restauración.',
                    'La Latina para tapeo y domingo en El Rastro.',
                ],
            ],
            'CDG' => [
                'intro' => 'París combina patrimonio histórico, museos de referencia mundial y barrios con una fuerte identidad cultural.',
                'imprescindibles' => [
                    'Torre Eiffel y orillas del Sena.',
                    'Museo del Louvre y zona de Tuileries.',
                    'Montmartre y Basílica del Sacré-Coeur.',
                ],
            ],
            'ORY' => [
                'intro' => 'París permite diseñar rutas culturales, gastronómicas y de compras en pocos días gracias a su excelente red de transporte.',
            ],
            'LHR' => [
                'intro' => 'Londres reúne museos gratuitos, mercados históricos y barrios multiculturales ideales para viajes cortos y largos.',
            ],
            'FCO' => [
                'intro' => 'Roma ofrece historia monumental, arte y gastronomía italiana en un trazado urbano perfecto para recorrer a pie.',
            ],
        ];

        $extrasTuristica = [];
        if (isset($porIata[$iata])) {
            $extrasTuristica = $porIata[$iata];
        }

        $info = array_merge($base, $extrasTuristica);
        $info['resumen_local'] = $nombre . ' (' . $iata . '), en ' . $pais . ', es una opción muy completa para escapadas urbanas y viajes culturales.';

        return $info;
    }

    protected function normalizeSearchValue(string $value): string
    {
        $value = mb_strtolower($value, 'UTF-8');

        return strtr($value, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
            'ñ' => 'n', 'Ñ' => 'n',
        ]);
    }

    protected function accentInsensitiveField(string $field): string
    {
        return "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE({$field}, 'Á', 'a'), 'É', 'e'), 'Í', 'i'), 'Ó', 'o'), 'Ú', 'u'), 'á', 'a'), 'é', 'e'), 'í', 'i'), 'ó', 'o'), 'ú', 'u'))";
    }

    protected function limpiarNombreCiudad(string $nombre): string
    {
        $prefijo1 = 'Aeropuerto de ';
        $prefijo2 = 'Aeropuerto ';

        if (stripos($nombre, $prefijo1) === 0) {
            return substr($nombre, strlen($prefijo1));
        }
        if (stripos($nombre, $prefijo2) === 0) {
            return substr($nombre, strlen($prefijo2));
        }

        return $nombre;
    }

    protected function construirCalendarioRuta(
        int $origenId,
        int $destinoId,
        Carbon $hoy,
        Carbon $mesInicio,
        Carbon $mesFin,
        ?string $mesSolicitado
    ): array {
        $vuelosCalendario = Vuelo::where('origen_ciudad_id', $origenId)
            ->where('destino_ciudad_id', $destinoId)
            ->where('activo', true)
            ->whereDate('fecha_salida', '>=', $hoy)
            ->with('ofertas')
            ->orderBy('fecha_salida')
            ->get();

        $preciosRealesPorMes = [];
        foreach ($vuelosCalendario as $vuelo) {
            $fechaVuelo = null;
            if ($vuelo->fecha_salida instanceof Carbon) {
                $fechaVuelo = $vuelo->fecha_salida->copy();
            } else {
                $fechaVuelo = Carbon::parse($vuelo->fecha_salida);
            }

            $precioDia = (float) $vuelo->precio;
            foreach ($vuelo->ofertas as $ofertaVuelo) {
                if ($ofertaVuelo->activo
                    && $ofertaVuelo->cupo > 0
                    && $ofertaVuelo->fecha_inicio <= now()
                    && $ofertaVuelo->fecha_fin >= now()) {
                    $precioDia = min($precioDia, (float) $ofertaVuelo->precio_promocional);
                }
            }

            $claveMes = $fechaVuelo->format('Y-m');
            $dia = (int) $fechaVuelo->format('j');

            if (!isset($preciosRealesPorMes[$claveMes])) {
                $preciosRealesPorMes[$claveMes] = [];
            }

            if (!isset($preciosRealesPorMes[$claveMes][$dia]) || $precioDia < $preciosRealesPorMes[$claveMes][$dia]) {
                $preciosRealesPorMes[$claveMes][$dia] = round($precioDia, 2);
            }
        }

        $calendarioMeses = [];

        $mesCursor = $mesInicio->copy()->startOfMonth();
        while ($mesCursor->lte($mesFin)) {
            $mes = $mesCursor->copy();
            $claveMes = $mes->format('Y-m');
            $diasMes = (int) $mes->copy()->endOfMonth()->format('j');
            $preciosMes = [];
            if (isset($preciosRealesPorMes[$claveMes])) {
                $preciosMes = $preciosRealesPorMes[$claveMes];
            }

            ksort($preciosMes);

            if (!empty($preciosMes)) {
                $minPrecioMes = min($preciosMes);
            } else {
                $minPrecioMes = null;
            }
            $diaMejorMes = null;
            if ($minPrecioMes !== null) {
                $diaMejorMes = (int) array_search($minPrecioMes, $preciosMes, true);
            }

            $calendarioMeses[$claveMes] = [
                'mes' => $mes,
                'precios' => $preciosMes,
                'min' => $minPrecioMes,
                'mejor_dia' => $diaMejorMes,
            ];

            $mesCursor->addMonthNoOverflow();
        }

        $mesSeleccionado = $mesSolicitado;
        if (!$mesSeleccionado || !isset($calendarioMeses[$mesSeleccionado])) {
            $mesSeleccionado = array_key_first($calendarioMeses);
        }

        $calendarioMes = null;
        $preciosPorDia = [];
        $mejorPrecio = null;
        $mejorDia = null;

        if ($mesSeleccionado && isset($calendarioMeses[$mesSeleccionado])) {
            $calendarioMes = $calendarioMeses[$mesSeleccionado]['mes'];
            $preciosPorDia = $calendarioMeses[$mesSeleccionado]['precios'];
            $mejorPrecio = $calendarioMeses[$mesSeleccionado]['min'];
            $mejorDia = $calendarioMeses[$mesSeleccionado]['mejor_dia'];
        }

        return [
            'calendario_mes' => $calendarioMes,
            'calendario_meses' => $calendarioMeses,
            'mes_seleccionado' => $mesSeleccionado,
            'precios_por_dia' => $preciosPorDia,
            'mejor_precio' => $mejorPrecio,
            'mejor_dia' => $mejorDia,
        ];
    }

    public function index(Request $request)
    {
        $ciudades = Ciudad::orderBy('nombre')->get();
        $origenBuscado = trim($request->input('origen_buscar', ''));
        $destinoBuscado = trim($request->input('destino_buscar', ''));
        $origen = null;
        $destinoSeleccionado = null;
        $destinos = collect();
        $calendarioMesIda = null;
        $calendarioMesesIda = [];
        $preciosPorDiaIda = [];
        $mejorDiaIda = null;
        $mejorPrecioIda = null;
        $mesSeleccionadoIda = null;
        $calendarioMesVuelta = null;
        $calendarioMesesVuelta = [];
        $preciosPorDiaVuelta = [];
        $mejorDiaVuelta = null;
        $mejorPrecioVuelta = null;
        $mesSeleccionadoVuelta = null;
        $origenOfertaSeleccionado = null;
        $origenesOfertas = collect();
        $ofertasInspirate = collect();
        $mensaje = null;
        $hoy = now()->startOfDay();
        $mesMinimo = $hoy->copy()->startOfMonth();
        $mesMaximo = Carbon::createFromDate(2027, 12, 1)->startOfMonth();
        if ($mesMaximo->lt($mesMinimo)) {
            $mesMaximo = $mesMinimo->copy();
        }

        if ($origenBuscado !== '') {
            $search = $this->normalizeSearchValue($origenBuscado);
            $origen = Ciudad::whereRaw("{$this->accentInsensitiveField('nombre')} LIKE ?", ["%{$search}%"])
                ->orWhereRaw("{$this->accentInsensitiveField('codigo_iata')} LIKE ?", ["%{$search}%"])
                ->first();
        }

        if (!$origen && $request->filled('origen_id')) {
            $origen = Ciudad::find($request->input('origen_id'));
        }

        if (!$origen && $ciudades->isNotEmpty()) {
            if ($request->filled('origen_id') || $origenBuscado !== '') {
                $origen = null;
            } else {
                $origen = $ciudades->first();
            }
        }

        if ($origen) {
            $destinoQuery = Ciudad::whereHas('vuelosDestino', function ($query) use ($origen) {
                $query->where('origen_ciudad_id', $origen->id)
                    ->where('activo', true);
            })->with(['vuelosDestino' => function ($query) use ($origen) {
                $query->where('origen_ciudad_id', $origen->id)
                    ->where('activo', true);
            }])->withCount(['vuelosDestino as vuelos_disponibles' => function ($query) use ($origen) {
                $query->where('origen_ciudad_id', $origen->id)
                    ->where('activo', true);
            }]);

            if ($destinoBuscado !== '') {
                $search = $this->normalizeSearchValue($destinoBuscado);
                $destinoQuery->where(function ($query) use ($search) {
                    $query->whereRaw("{$this->accentInsensitiveField('nombre')} LIKE ?", ["%{$search}%"])
                        ->orWhereRaw("{$this->accentInsensitiveField('pais')} LIKE ?", ["%{$search}%"])
                        ->orWhereRaw("{$this->accentInsensitiveField('codigo_iata')} LIKE ?", ["%{$search}%"]);
                });
            }

            $destinos = $destinoQuery->orderBy('nombre')->get()->transform(function ($destino) {
                $destino->precio_estimado = $destino->vuelosDestino->min('precio');
                $destino->vuelo_mas_economico = $destino->vuelosDestino->sortBy('precio')->first();
                $destino->vuelo_mas_cercano = $destino->vuelosDestino->sortBy('fecha_salida')->first();

                return $destino;
            });

            if ($request->filled('destino_id')) {
                $destinoSeleccionado = $destinos->firstWhere('id', (int) $request->input('destino_id'));
            }

            if (!$destinoSeleccionado && $destinoBuscado !== '') {
                $searchDestino = $this->normalizeSearchValue($destinoBuscado);
                $destinoSeleccionado = $destinos->first(function ($destino) use ($searchDestino) {
                    return $this->normalizeSearchValue($destino->nombre) === $searchDestino
                        || $this->normalizeSearchValue($destino->codigo_iata) === $searchDestino;
                });
            }

            if ($destinoSeleccionado) {
                $mesIdaSolicitado = $request->input('mes_ida');
                $mesVueltaSolicitado = $request->input('mes_vuelta');

                if ($mesIdaSolicitado && $mesIdaSolicitado < $mesMinimo->format('Y-m')) {
                    $mesIdaSolicitado = $mesMinimo->format('Y-m');
                }
                if ($mesIdaSolicitado && $mesIdaSolicitado > $mesMaximo->format('Y-m')) {
                    $mesIdaSolicitado = $mesMaximo->format('Y-m');
                }
                if ($mesVueltaSolicitado && $mesVueltaSolicitado > $mesMaximo->format('Y-m')) {
                    $mesVueltaSolicitado = $mesMaximo->format('Y-m');
                }
                if ($mesIdaSolicitado && $mesVueltaSolicitado && $mesVueltaSolicitado < $mesIdaSolicitado) {
                    $mesVueltaSolicitado = $mesIdaSolicitado;
                }

                    $precioEstimadoIda = 120;
                if (isset($destinoSeleccionado->precio_estimado)) {
                    $precioEstimadoIda = (float) $destinoSeleccionado->precio_estimado;
                }

                $calendarioIda = $this->construirCalendarioRuta(
                    $origen->id,
                    $destinoSeleccionado->id,
                    $hoy,
                    $mesMinimo,
                    $mesMaximo,
                    $mesIdaSolicitado,
                    $precioEstimadoIda
                );

                $calendarioVuelta = $this->construirCalendarioRuta(
                    $destinoSeleccionado->id,
                    $origen->id,
                    $hoy,
                    $mesMinimo,
                    $mesMaximo,
                    $mesVueltaSolicitado,
                    $precioEstimadoIda
                );

                $calendarioMesIda = $calendarioIda['calendario_mes'];
                $calendarioMesesIda = $calendarioIda['calendario_meses'];
                $mesSeleccionadoIda = $calendarioIda['mes_seleccionado'];
                $preciosPorDiaIda = $calendarioIda['precios_por_dia'];
                $mejorPrecioIda = $calendarioIda['mejor_precio'];
                $mejorDiaIda = $calendarioIda['mejor_dia'];

                $calendarioMesVuelta = $calendarioVuelta['calendario_mes'];
                $calendarioMesesVuelta = $calendarioVuelta['calendario_meses'];
                $mesSeleccionadoVuelta = $calendarioVuelta['mes_seleccionado'];
                $preciosPorDiaVuelta = $calendarioVuelta['precios_por_dia'];
                $mejorPrecioVuelta = $calendarioVuelta['mejor_precio'];
                $mejorDiaVuelta = $calendarioVuelta['mejor_dia'];

                if ($mesSeleccionadoIda && $mesSeleccionadoVuelta && $mesSeleccionadoVuelta < $mesSeleccionadoIda) {
                    $mesSeleccionadoVuelta = $mesSeleccionadoIda;
                }
            }


            if ($destinos->isEmpty()) {
                $mensaje = 'No hay destinos disponibles desde esta ciudad.';
            }
        } else {
            $mensaje = 'Selecciona un origen válido para consultar destinos y ofertas.';
        }

        // Mostrar todas las ciudades en el selector, tengan o no ofertas activas.
        $origenesOfertas = Ciudad::orderBy('nombre')->get();

        $origenOfertaSeleccionado = $request->input('origen_oferta_id');
        if (!$origenOfertaSeleccionado && $origen && $origenesOfertas->contains('id', $origen->id)) {
            $origenOfertaSeleccionado = $origen->id;
        }
        if (!$origenOfertaSeleccionado && $origenesOfertas->isNotEmpty()) {
            $origenOfertaSeleccionado = $origenesOfertas->first()->id;
        }

        $mesReferenciaOfertas = $request->input('mes_ida', $mesMinimo->format('Y-m'));
        if ($mesReferenciaOfertas < $mesMinimo->format('Y-m')) {
            $mesReferenciaOfertas = $mesMinimo->format('Y-m');
        }
        if ($mesReferenciaOfertas > $mesMaximo->format('Y-m')) {
            $mesReferenciaOfertas = $mesMaximo->format('Y-m');
        }

        $origenIdInspirate = (int) $origenOfertaSeleccionado;
        if ($origenIdInspirate) {
            $origenInspirate = Ciudad::find($origenIdInspirate);

            $destinosInspirate = Ciudad::whereHas('vuelosDestino', function ($query) use ($origenIdInspirate) {
                $query->where('origen_ciudad_id', $origenIdInspirate)
                    ->where('activo', true);
            })->with(['vuelosDestino' => function ($query) use ($origenIdInspirate) {
                $query->where('origen_ciudad_id', $origenIdInspirate)
                    ->where('activo', true);
            }])->orderBy('nombre')->get()->transform(function ($destino) {
                $destino->precio_estimado = $destino->vuelosDestino->min('precio');
                return $destino;
            });

            foreach ($destinosInspirate as $destinoInspirate) {
                $precioEstimadoInspirate = 120.0;
                if (isset($destinoInspirate->precio_estimado)) {
                    $precioEstimadoInspirate = (float) $destinoInspirate->precio_estimado;
                }

                $calendarioDestino = $this->construirCalendarioRuta(
                    $origenIdInspirate,
                    $destinoInspirate->id,
                    $hoy,
                    $mesMinimo,
                    $mesMaximo,
                    $mesReferenciaOfertas,
                    $precioEstimadoInspirate
                );

                $mejorPrecioDestino = $calendarioDestino['mejor_precio'];

                if ($mejorPrecioDestino === null) {
                    continue;
                }

                $ofertasInspirate->push([
                    'origen' => $origenInspirate,
                    'origen_id' => $origenIdInspirate,
                    'destino' => $destinoInspirate,
                    'destino_id' => $destinoInspirate->id,
                    'precio' => round((float) $mejorPrecioDestino, 2),
                ]);
            }

            $ofertasInspirate = $ofertasInspirate
                ->sortBy('precio')
                ->take(4)
                ->values();
        }

        $rol = null;
        if (Auth::check()) {
            $rol = strtolower(Auth::user()->rol->nombre);
        }
        $esAdmin = in_array($rol, ['admin', 'superadmin']);
        $esSuperAdmin = $rol === 'superadmin';

        return view('destinos.index', compact(
            'ciudades',
            'origen',
            'destinoSeleccionado',
            'destinos',
            'calendarioMesIda',
            'calendarioMesesIda',
            'preciosPorDiaIda',
            'mejorDiaIda',
            'mejorPrecioIda',
            'mesSeleccionadoIda',
            'calendarioMesVuelta',
            'calendarioMesesVuelta',
            'preciosPorDiaVuelta',
            'mejorDiaVuelta',
            'mejorPrecioVuelta',
            'mesSeleccionadoVuelta',
            'origenOfertaSeleccionado',
            'origenesOfertas',
            'ofertasInspirate',
            'origenBuscado',
            'destinoBuscado',
            'mensaje',
            'mesMinimo',
            'mesMaximo',
            'esAdmin',
            'esSuperAdmin'
        ));
    }

    public function show(Ciudad $destino, Request $request)
    {
        $ciudades = Ciudad::orderBy('nombre')->get();
        $origen = Ciudad::find($request->input('origen_id'));
        if (!$origen) {
            $origen = $ciudades->first();
        }

        if (!$origen) {
            return redirect()->route('destinos.index')
                ->with('error', 'No hay una ciudad de origen disponible.');
        }

        $vuelos = Vuelo::with('ofertas')
            ->where('origen_ciudad_id', $origen->id)
            ->where('destino_ciudad_id', $destino->id)
            ->where('activo', true)
            ->orderBy('fecha_salida')
            ->get();

        $ofertas = Oferta::where('activo', true)
            ->where('fecha_inicio', '<=', now())
            ->where('fecha_fin', '>=', now())
            ->where('cupo', '>', 0)
            ->whereHas('vuelo', function ($query) use ($origen, $destino) {
                $query->where('origen_ciudad_id', $origen->id)
                    ->where('destino_ciudad_id', $destino->id)
                    ->where('activo', true);
            })
            ->with('vuelo')
            ->get();

        $rol = null;
        if (Auth::check()) {
            $rol = strtolower(Auth::user()->rol->nombre);
        }
        $esAdmin = in_array($rol, ['admin', 'superadmin']);
        $esSuperAdmin = $rol === 'superadmin';
        $infoAeropuerto = $this->construirInfoAeropuerto($destino);
        $infoTuristica = $this->construirInfoTuristica($destino);

        return view('destinos.show', compact(
            'ciudades',
            'origen',
            'destino',
            'vuelos',
            'ofertas',
            'esAdmin',
            'esSuperAdmin',
            'infoAeropuerto',
            'infoTuristica'
        ));
    }

    public function buscarCiudadesOrigen(Request $request)
    {
        $busqueda = $request->input('q', '');

        if (strlen($busqueda) < 1) {
            $ciudades = Ciudad::orderBy('nombre')->get();
        } else {
            $search = $this->normalizeSearchValue($busqueda);
            $ciudades = Ciudad::where(function ($query) use ($search) {
                $query->whereRaw("{$this->accentInsensitiveField('nombre')} LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("{$this->accentInsensitiveField('codigo_iata')} LIKE ?", ["%{$search}%"]);
            })
            ->orderBy('nombre')
            ->get();
        }

        return response()->json([
            'ciudades' => $ciudades->map(function ($ciudad) {
                return [
                    'id' => $ciudad->id,
                    'nombre' => $this->limpiarNombreCiudad($ciudad->nombre),
                    'pais' => $ciudad->pais,
                    'codigo_iata' => $ciudad->codigo_iata,
                ];
            })
        ]);
    }

    public function buscarCiudadesDestino(Request $request)
    {
        $origen_id = $request->input('origen_id');
        $busqueda = $request->input('q', '');

        $origen = Ciudad::find($origen_id);
        if (!$origen) {
            return response()->json(['ciudades' => [],  'error' => 'Origen no válido'], 400);
        }

        // Obtener ciudades con vuelos directos desde el origen
        $destinoQuery = Ciudad::whereHas('vuelosDestino', function ($query) use ($origen) {
            $query->where('origen_ciudad_id', $origen->id)
                ->where('activo', true);
        });

        if (strlen($busqueda) > 0) {
            $search = $this->normalizeSearchValue($busqueda);
            $destinoQuery->where(function ($query) use ($search) {
                $query->whereRaw("{$this->accentInsensitiveField('nombre')} LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("{$this->accentInsensitiveField('pais')} LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("{$this->accentInsensitiveField('codigo_iata')} LIKE ?", ["%{$search}%"]);
            });
        }

        $ciudades = $destinoQuery->orderBy('nombre')
            ->get();

        return response()->json([
            'ciudades' => $ciudades->map(function ($ciudad) {
                return [
                    'id' => $ciudad->id,
                    'nombre' => $this->limpiarNombreCiudad($ciudad->nombre),
                    'pais' => $ciudad->pais,
                    'codigo_iata' => $ciudad->codigo_iata,
                ];
            })
        ]);
    }
}
