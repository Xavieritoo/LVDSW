<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FlightsSeeder extends Seeder
{
    // Alimenta tablas de referencia (estados_vuelo, aeropuertos, aerolineas, etc.)
    // y enriquece vuelos existentes de DestinosSeeder con campos de estado-vuelos.
    //
    // NO crea vuelos nuevos. Solo actualiza los existentes.
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Eliminar vuelos huerfanos del antiguo FlightsSeeder (tienen ruta_id pero no ciudad)
        $oldIds = DB::table('vuelos')->whereNotNull('ruta_id')->whereNull('origen_ciudad_id')->pluck('id');
        if ($oldIds->isNotEmpty()) {
            DB::table('asientos_vuelo')->whereIn('vuelo_id', $oldIds)->delete();
            DB::table('vuelos')->whereIn('id', $oldIds)->delete();
        }

        // Eliminar vuelos huerfanos del antiguo VuelosConAsientosSeeder (tienen codigo PLN pero no ciudad)
        $oldPln = DB::table('vuelos')->where('codigo', 'like', 'PLN%')->whereNull('origen_ciudad_id')->pluck('id');
        if ($oldPln->isNotEmpty()) {
            DB::table('asientos_vuelo')->whereIn('vuelo_id', $oldPln)->delete();
            DB::table('vuelos')->whereIn('id', $oldPln)->delete();
        }

        // Resetear campos estado-vuelos en vuelos de DestinosSeeder para re-asignar
        DB::table('vuelos')->whereNotNull('origen_ciudad_id')->update([
            'numero_vuelo' => null, 'aerolinea_id' => null, 'ruta_id' => null,
            'avion_id' => null, 'estado_id' => null,
            'hora_salida_programada' => null, 'hora_salida_real' => null,
            'hora_llegada_programada' => null, 'hora_llegada_real' => null,
            'puerta_salida' => null, 'puerta_llegada' => null,
            'terminal_salida' => null, 'terminal_llegada' => null,
            'pasajeros_confirmados' => null, 'tripulacion_cantidad' => null,
        ]);

        // Limpiar tablas de referencia para poder re-ejecutar
        DB::table('rutas')->truncate();
        DB::table('aviones')->truncate();
        DB::table('tipos_aviones')->truncate();
        DB::table('aeropuertos')->truncate();
        DB::table('aerolineas')->truncate();
        DB::table('estados_vuelo')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ── Estados de vuelos ──────────────────────────────────────────
        DB::table('estados_vuelo')->insert([
            ['nombre' => 'programado',  'descripcion' => 'Vuelo programado',       'color_badge' => '#28a745', 'icono' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'embarcando',  'descripcion' => 'En proceso de embarque', 'color_badge' => '#17a2b8', 'icono' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'en_vuelo',    'descripcion' => 'Actualmente en vuelo',   'color_badge' => '#007bff', 'icono' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'retrasado',   'descripcion' => 'Vuelo retrasado',        'color_badge' => '#ffc107', 'icono' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'aterrizado',  'descripcion' => 'Vuelo finalizado',       'color_badge' => '#6c757d', 'icono' => null, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'cancelado',   'descripcion' => 'Vuelo cancelado',        'color_badge' => '#dc3545', 'icono' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── Aeropuertos (83 - uno por cada ciudad) ────────────────────
        // Se insertan vinculados a ciudades via ciudad_id
        $ciudades = DB::table('ciudades')->get();
        $aeropuertosData = [
            'MAD' => ['nombre' => 'Aeropuerto Adolfo Suarez Madrid-Barajas',  'icao' => 'LEMD'],
            'BCN' => ['nombre' => 'Aeropuerto de Barcelona-El Prat',          'icao' => 'LEBL'],
            'CDG' => ['nombre' => 'Aeropuerto Charles de Gaulle',             'icao' => 'LFPG'],
            'LHR' => ['nombre' => 'Aeropuerto de Heathrow',                   'icao' => 'EGLL'],
            'VLC' => ['nombre' => 'Aeropuerto de Valencia',                   'icao' => 'LEVC'],
            'SVQ' => ['nombre' => 'Aeropuerto de Sevilla-San Pablo',          'icao' => 'LEZL'],
            'ZAZ' => ['nombre' => 'Aeropuerto de Zaragoza',                   'icao' => 'LEZG'],
            'AGP' => ['nombre' => 'Aeropuerto de Malaga-Costa del Sol',       'icao' => 'LEMG'],
            'LPA' => ['nombre' => 'Aeropuerto de Gran Canaria',               'icao' => 'GCLP'],
            'TFN' => ['nombre' => 'Aeropuerto de Tenerife Norte',             'icao' => 'GCXO'],
            'TFS' => ['nombre' => 'Aeropuerto de Tenerife Sur',               'icao' => 'GCTS'],
            'ACE' => ['nombre' => 'Aeropuerto de Lanzarote',                  'icao' => 'GCRR'],
            'SPC' => ['nombre' => 'Aeropuerto de La Palma',                   'icao' => 'GCLA'],
            'FUE' => ['nombre' => 'Aeropuerto de Fuerteventura',              'icao' => 'GCFV'],
            'SXB' => ['nombre' => 'Aeropuerto de Estrasburgo',                'icao' => 'LFST'],
            'ORY' => ['nombre' => 'Aeropuerto de Paris-Orly',                 'icao' => 'LFPO'],
            'LYS' => ['nombre' => 'Aeropuerto de Lyon-Saint Exupery',         'icao' => 'LFLL'],
            'NCE' => ['nombre' => 'Aeropuerto de Niza-Costa Azul',            'icao' => 'LFMN'],
            'NTE' => ['nombre' => 'Aeropuerto de Nantes Atlantique',          'icao' => 'LFRS'],
            'BIA' => ['nombre' => 'Aeropuerto de Bastia-Poretta',             'icao' => 'LFKB'],
            'TLS' => ['nombre' => 'Aeropuerto de Toulouse-Blagnac',           'icao' => 'LFBO'],
            'MRS' => ['nombre' => 'Aeropuerto de Marsella-Provenza',          'icao' => 'LFML'],
            'BOD' => ['nombre' => 'Aeropuerto de Burdeos-Merignac',           'icao' => 'LFBD'],
            'VCE' => ['nombre' => 'Aeropuerto Marco Polo de Venecia',         'icao' => 'LIPZ'],
            'NAP' => ['nombre' => 'Aeropuerto de Napoles-Capodichino',        'icao' => 'LIRN'],
            'MXP' => ['nombre' => 'Aeropuerto de Milan-Malpensa',             'icao' => 'LIMC'],
            'LIN' => ['nombre' => 'Aeropuerto de Milan-Linate',               'icao' => 'LIML'],
            'CTA' => ['nombre' => 'Aeropuerto de Catania-Fontanarossa',       'icao' => 'LICC'],
            'BLQ' => ['nombre' => 'Aeropuerto Guglielmo Marconi de Bologna',  'icao' => 'LIPE'],
            'FCO' => ['nombre' => 'Aeropuerto Leonardo da Vinci-Fiumicino',   'icao' => 'LIRF'],
            'PSA' => ['nombre' => 'Aeropuerto Galileo Galilei de Pisa',       'icao' => 'LIRP'],
            'PMO' => ['nombre' => 'Aeropuerto Falcone-Borsellino de Palermo', 'icao' => 'LICJ'],
            'FLR' => ['nombre' => 'Aeropuerto de Florencia-Peretola',         'icao' => 'LIRQ'],
            'GOA' => ['nombre' => 'Aeropuerto de Genova-Cristoforo Colombo',  'icao' => 'LIMJ'],
            'FNC' => ['nombre' => 'Aeropuerto de Madeira-Cristiano Ronaldo',  'icao' => 'LPMA'],
            'OPO' => ['nombre' => 'Aeropuerto Francisco Sa Carneiro',         'icao' => 'LPPR'],
            'FAO' => ['nombre' => 'Aeropuerto de Faro',                       'icao' => 'LPFR'],
            'LIS' => ['nombre' => 'Aeropuerto Humberto Delgado',              'icao' => 'LPPT'],
            'HER' => ['nombre' => 'Aeropuerto de Heraclion-Kazantzakis',      'icao' => 'LGIR'],
            'JTR' => ['nombre' => 'Aeropuerto de Santorini',                  'icao' => 'LGSR'],
            'ATH' => ['nombre' => 'Aeropuerto Internacional de Atenas',       'icao' => 'LGAV'],
            'FEZ' => ['nombre' => 'Aeropuerto de Fez-Saiss',                  'icao' => 'GMFF'],
            'CZL' => ['nombre' => 'Aeropuerto Mohamed Boudiaf',               'icao' => 'DABC'],
            'NDR' => ['nombre' => 'Aeropuerto Internacional de Nador',        'icao' => 'GMMW'],
            'RAK' => ['nombre' => 'Aeropuerto de Marrakech-Menara',           'icao' => 'GMMX'],
            'TNG' => ['nombre' => 'Aeropuerto Ibn Battouta de Tanger',        'icao' => 'GMTT'],
            'CMN' => ['nombre' => 'Aeropuerto Mohammed V de Casablanca',      'icao' => 'GMMN'],
            'NUE' => ['nombre' => 'Aeropuerto de Nuremberg',                  'icao' => 'EDDN'],
            'MUC' => ['nombre' => 'Aeropuerto Franz Josef Strauss de Munich',  'icao' => 'EDDM'],
            'DUS' => ['nombre' => 'Aeropuerto de Dusseldorf',                 'icao' => 'EDDL'],
            'HAJ' => ['nombre' => 'Aeropuerto de Hanover-Langenhagen',        'icao' => 'EDDV'],
            'HAM' => ['nombre' => 'Aeropuerto de Hamburgo',                   'icao' => 'EDDH'],
            'FRA' => ['nombre' => 'Aeropuerto de Frankfurt',                  'icao' => 'EDDF'],
            'BER' => ['nombre' => 'Aeropuerto de Berlin-Brandenburgo',        'icao' => 'EDDB'],
            'STR' => ['nombre' => 'Aeropuerto de Stuttgart',                  'icao' => 'EDDS'],
            'DBV' => ['nombre' => 'Aeropuerto de Dubrovnik',                  'icao' => 'LDDU'],
            'SPU' => ['nombre' => 'Aeropuerto de Split',                      'icao' => 'LDSP'],
            'ARN' => ['nombre' => 'Aeropuerto de Estocolmo-Arlanda',          'icao' => 'ESSA'],
            'GOT' => ['nombre' => 'Aeropuerto de Gotemburgo-Landvetter',      'icao' => 'ESGG'],
            'ZRH' => ['nombre' => 'Aeropuerto de Zurich',                     'icao' => 'LSZH'],
            'BSL' => ['nombre' => 'Aeropuerto de Basilea-Mulhouse',           'icao' => 'LFSB'],
            'GVA' => ['nombre' => 'Aeropuerto de Ginebra',                    'icao' => 'LSGG'],
            'CPH' => ['nombre' => 'Aeropuerto de Copenhague-Kastrup',         'icao' => 'EKCH'],
            'BLL' => ['nombre' => 'Aeropuerto de Billund',                    'icao' => 'EKBI'],
            'OTP' => ['nombre' => 'Aeropuerto Henri Coanda de Bucarest',      'icao' => 'LROP'],
            'CLJ' => ['nombre' => 'Aeropuerto de Cluj-Napoca',                'icao' => 'LRCL'],
            'TSR' => ['nombre' => 'Aeropuerto Traian Vuia de Timisoara',      'icao' => 'LRTR'],
            'IAS' => ['nombre' => 'Aeropuerto de Iasi',                       'icao' => 'LRIA'],
            'OSL' => ['nombre' => 'Aeropuerto de Oslo-Gardermoen',            'icao' => 'ENGM'],
            'BGO' => ['nombre' => 'Aeropuerto de Bergen-Flesland',            'icao' => 'ENBR'],
            'SVG' => ['nombre' => 'Aeropuerto de Stavanger-Sola',             'icao' => 'ENZV'],
            'TOS' => ['nombre' => 'Aeropuerto de Tromso-Langnes',             'icao' => 'ENTC'],
            'IST' => ['nombre' => 'Aeropuerto de Estambul',                   'icao' => 'LTFM'],
            'AMS' => ['nombre' => 'Aeropuerto de Schiphol',                   'icao' => 'EHAM'],
            'DUB' => ['nombre' => 'Aeropuerto de Dublin',                     'icao' => 'EIDW'],
            'VIE' => ['nombre' => 'Aeropuerto Internacional de Viena',        'icao' => 'LOWW'],
            'BRU' => ['nombre' => 'Aeropuerto de Bruselas-Zaventem',          'icao' => 'EBBR'],
            'PRG' => ['nombre' => 'Aeropuerto Vaclav Havel de Praga',         'icao' => 'LKPR'],
            'BUD' => ['nombre' => 'Aeropuerto Ferenc Liszt de Budapest',      'icao' => 'LHBP'],
            'CAI' => ['nombre' => 'Aeropuerto Internacional de El Cairo',     'icao' => 'HECA'],
            'MLA' => ['nombre' => 'Aeropuerto Internacional de Malta',        'icao' => 'LMML'],
            'KEF' => ['nombre' => 'Aeropuerto Internacional de Keflavik',     'icao' => 'BIKF'],
            'TIA' => ['nombre' => 'Aeropuerto Internacional de Tirana',       'icao' => 'LATI'],
        ];

        foreach ($ciudades as $ciudad) {
            $info = $aeropuertosData[$ciudad->codigo_iata] ?? null;
            DB::table('aeropuertos')->insert([
                'codigo_iata'  => $ciudad->codigo_iata,
                'codigo_icao'  => $info['icao'] ?? null,
                'nombre'       => $info['nombre'] ?? ('Aeropuerto de ' . $ciudad->nombre),
                'ciudad'       => $ciudad->nombre,
                'pais'         => $ciudad->pais,
                'ciudad_id'    => $ciudad->id,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // ── Aerolíneas (1 - solo Planit) ──────────────────────────────
        DB::table('aerolineas')->insert([
            ['codigo_iata' => 'PT', 'codigo_icao' => 'PLT', 'nombre' => 'Planit', 'descripcion' => 'Aerolinea oficial Planit', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── Tipos de aviones (4) ───────────────────────────────────────
        DB::table('tipos_aviones')->insert([
            ['codigo' => 'A320', 'modelo' => 'Airbus A320',  'fabricante' => 'Airbus',  'capacidad_pasajeros' => 180, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => 'A321', 'modelo' => 'Airbus A321',  'fabricante' => 'Airbus',  'capacidad_pasajeros' => 220, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => 'B737', 'modelo' => 'Boeing 737',   'fabricante' => 'Boeing',  'capacidad_pasajeros' => 189, 'created_at' => now(), 'updated_at' => now()],
            ['codigo' => 'E195', 'modelo' => 'Embraer E195', 'fabricante' => 'Embraer', 'capacidad_pasajeros' => 146, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── Aviones (8 - todos Planit, aerolinea_id=1) ──────────────
        DB::table('aviones')->insert([
            ['matricula' => 'EC-MXV', 'tipo_avion_id' => 1, 'aerolinea_id' => 1, 'año_fabricacion' => 2018, 'horas_vuelo' => 0, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
            ['matricula' => 'EC-MXW', 'tipo_avion_id' => 1, 'aerolinea_id' => 1, 'año_fabricacion' => 2019, 'horas_vuelo' => 0, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
            ['matricula' => 'EC-MXX', 'tipo_avion_id' => 2, 'aerolinea_id' => 1, 'año_fabricacion' => 2020, 'horas_vuelo' => 0, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
            ['matricula' => 'EC-MXY', 'tipo_avion_id' => 3, 'aerolinea_id' => 1, 'año_fabricacion' => 2017, 'horas_vuelo' => 0, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
            ['matricula' => 'EC-MXZ', 'tipo_avion_id' => 4, 'aerolinea_id' => 1, 'año_fabricacion' => 2021, 'horas_vuelo' => 0, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
            ['matricula' => 'EC-NAA', 'tipo_avion_id' => 1, 'aerolinea_id' => 1, 'año_fabricacion' => 2019, 'horas_vuelo' => 0, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
            ['matricula' => 'EC-NAB', 'tipo_avion_id' => 3, 'aerolinea_id' => 1, 'año_fabricacion' => 2020, 'horas_vuelo' => 0, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
            ['matricula' => 'G-EZAB', 'tipo_avion_id' => 1, 'aerolinea_id' => 1, 'año_fabricacion' => 2022, 'horas_vuelo' => 0, 'estado' => 'activo', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── Rutas (23) ─────────────────────────────────────────────────
        // Lookup aeropuerto_id por codigo_iata
        $apIds = DB::table('aeropuertos')->pluck('id', 'codigo_iata');
        $r = fn($o, $d, $dur, $km) => [
            'aerolinea_id' => 1,
            'aeropuerto_origen_id'  => $apIds[$o],
            'aeropuerto_destino_id' => $apIds[$d],
            'duracion_estimada' => $dur,
            'distancia_km'      => $km,
            'estado'            => true,
            'created_at'        => now(),
            'updated_at'        => now(),
        ];
        DB::table('rutas')->insert([
            $r('BCN','LPA',155,1860), $r('LPA','BCN',155,1860),
            $r('MAD','LPA',165,1770), $r('BCN','MAD',80,483),
            $r('MAD','CDG',130,1053), $r('BCN','LHR',145,1138),
            $r('LIS','BCN',140,1005), $r('CDG','MAD',130,1053),
            $r('BCN','LIS',150,1005), $r('MAD','BCN',80,483),
            $r('BCN','AMS',145,1236), $r('AMS','BCN',145,1236),
            $r('MAD','DUB',165,1451), $r('DUB','MAD',165,1451),
            $r('BCN','FCO',120,861),  $r('FCO','BCN',120,861),
            $r('MAD','ZRH',140,1233), $r('ZRH','MAD',140,1233),
            $r('BCN','DUB',165,1479), $r('MAD','FCO',150,1364),
            $r('MAD','AMS',160,1461), $r('LHR','BCN',145,1138),
            $r('LPA','MAD',165,1770),
        ]);

        // ── Enriquecer vuelos existentes con datos de estado ───────────
        $this->asignarEstadoVuelos();
    }

    // Busca vuelos creados por DestinosSeeder cuya ruta (origen_ciudad → destino_ciudad)
    // coincida con alguna de las 23 rutas. Les asigna numero_vuelo, aerolinea_id, ruta_id,
    // avion_id, estado_id y campos operativos (puertas, terminales, horarios reales).
    private function asignarEstadoVuelos(): void
    {
        // Mapa ciudad_id → codigo_iata
        $ciudadesIata = DB::table('ciudades')->pluck('codigo_iata', 'id');

        // Construir lookup de rutas: "origenIATA-destinoIATA" → ruta
        $rutas = DB::table('rutas')
            ->join('aeropuertos as o', 'rutas.aeropuerto_origen_id', '=', 'o.id')
            ->join('aeropuertos as d', 'rutas.aeropuerto_destino_id', '=', 'd.id')
            ->select('rutas.*', 'o.codigo_iata as origen_iata', 'd.codigo_iata as destino_iata')
            ->get();

        $rutaLookup = [];
        foreach ($rutas as $ruta) {
            $rutaLookup[$ruta->origen_iata . '-' . $ruta->destino_iata] = $ruta;
        }

        // Configuracion por ruta_id: [prefijo, numero_base, avion_id, term_sal, term_lleg, tripulacion]
        $rutasConfig = [
            1  => ['PT', 3004, 1, 'T1', 'T2', 6],
            2  => ['PT', 3010, 2, 'T1', 'T2', 6],
            3  => ['PT', 3020, 3, 'T4', 'T2', 6],
            4  => ['PT', 3030, 1, 'T1', 'T4', 6],
            5  => ['PT', 2001, 4, 'T4', 'T2E', 7],
            6  => ['PT', 5001, 5, 'T1', 'T5', 6],
            7  => ['PT', 3040, 2, 'T1', 'T1', 6],
            8  => ['PT', 2003, 4, 'T2E', 'T4', 7],
            9  => ['PT', 5005, 8, 'T1', 'T1', 6],
            10 => ['PT', 2010, 7, 'T4', 'T1', 7],
            11 => ['PT', 3050, 1, 'T1', 'T3', 6],
            12 => ['PT', 3055, 2, 'T3', 'T1', 6],
            13 => ['PT', 2020, 4, 'T4', 'T1', 7],
            14 => ['PT', 2025, 7, 'T1', 'T4', 7],
            15 => ['PT', 4001, 6, 'T2', 'T3', 5],
            16 => ['PT', 4005, 6, 'T3', 'T2', 5],
            17 => ['PT', 2030, 4, 'T4', 'TA', 7],
            18 => ['PT', 2035, 7, 'TA', 'T4', 7],
            19 => ['PT', 5010, 5, 'T1', 'T1', 6],
            20 => ['PT', 2040, 7, 'T4', 'T3', 7],
            21 => ['PT', 2050, 4, 'T4', 'T3', 7],
            22 => ['PT', 5015, 8, 'T5', 'T1', 6],
            23 => ['PT', 3060, 3, 'T2', 'T4', 6],
        ];

        $hoy = Carbon::today();

        // Obtener vuelos del DestinosSeeder (tienen origen_ciudad_id)
        $vuelos = DB::table('vuelos')
            ->whereNotNull('origen_ciudad_id')
            ->orderBy('fecha_salida')
            ->get();

        $contadorPorRuta = [];

        foreach ($vuelos as $vuelo) {
            if (isset($ciudadesIata[$vuelo->origen_ciudad_id])) {
                $origenIata = $ciudadesIata[$vuelo->origen_ciudad_id];
            } else {
                $origenIata = null;
            }
            if (isset($ciudadesIata[$vuelo->destino_ciudad_id])) {
                $destinoIata = $ciudadesIata[$vuelo->destino_ciudad_id];
            } else {
                $destinoIata = null;
            }

            if (!$origenIata || !$destinoIata) {
                continue;
            }

            $key  = $origenIata . '-' . $destinoIata;
            if (isset($rutaLookup[$key])) {
                $ruta = $rutaLookup[$key];
            } else {
                $ruta = null;
            }

            if (!$ruta) {
                // Vuelo sin ruta equivalente: asignar datos operativos genericos
                $seq = $vuelo->id % 100;
                $fechaVuelo = Carbon::parse($vuelo->fecha_salida);
                $estadoId = $this->determinarEstado($fechaVuelo, $hoy, $seq);
                $avionId = (($vuelo->id % 8) + 1);
                $numeroVuelo = 'PT' . str_pad($vuelo->id % 9999, 4, '0', STR_PAD_LEFT);

                $salidaReal = null;
                $llegadaReal = null;
                if ($fechaVuelo->lt($hoy)) {
                    $minutosDesvio = $seq % 7;
                    $salidaReal = Carbon::parse($vuelo->fecha_salida)->addMinutes($minutosDesvio)->format('Y-m-d H:i:s');
                    if ($estadoId === 5) {
                        $llegadaReal = $vuelo->fecha_llegada ? Carbon::parse($vuelo->fecha_llegada)->addMinutes($minutosDesvio + 1)->format('Y-m-d H:i:s') : null;
                    }
                }

                DB::table('vuelos')->where('id', $vuelo->id)->update([
                    'numero_vuelo'            => $numeroVuelo,
                    'aerolinea_id'            => 1,
                    'avion_id'                => $avionId,
                    'estado_id'               => $estadoId,
                    'hora_salida_programada'  => $vuelo->fecha_salida,
                    'hora_salida_real'        => $salidaReal,
                    'hora_llegada_programada' => $vuelo->fecha_llegada,
                    'hora_llegada_real'       => $llegadaReal,
                    'puerta_salida'           => null,
                    'puerta_llegada'          => null,
                    'terminal_salida'         => 'T' . (($seq % 3) + 1),
                    'terminal_llegada'        => $estadoId === 6 ? null : 'T' . (($seq % 3) + 1),
                    'pasajeros_confirmados'   => 0,
                    'tripulacion_cantidad'    => 6 + ($seq % 3),
                ]);
                continue;
            }

            if (isset($rutasConfig[$ruta->id])) {
                $config = $rutasConfig[$ruta->id];
            } else {
                $config = null;
            }
            if (!$config) {
                continue;
            }

            [$prefijo, $numBase, $avionId, $termSalida, $termLlegada, $tripulacion] = $config;

            if (isset($contadorPorRuta[$ruta->id])) {
                $contadorPorRuta[$ruta->id] = $contadorPorRuta[$ruta->id] + 1;
            } else {
                $contadorPorRuta[$ruta->id] = 1;
            }
            $seq = $contadorPorRuta[$ruta->id];

            $fechaVuelo = Carbon::parse($vuelo->fecha_salida);
            $estadoId   = $this->determinarEstado($fechaVuelo, $hoy, $seq);

            $numeroVuelo = $prefijo . $numBase;

            // Horarios: usar los que ya tiene el vuelo del DestinosSeeder
            $salidaProg  = $vuelo->fecha_salida;
            $llegadaProg = $vuelo->fecha_llegada;

            $salidaReal  = null;
            $llegadaReal = null;

            // Vuelos pasados: hora real con ligera variacion
            if ($fechaVuelo->lt($hoy)) {
                $minutosDesvio = ($seq * $ruta->id) % 7;
                $salidaReal = Carbon::parse($salidaProg)
                    ->addMinutes($minutosDesvio)
                    ->format('Y-m-d H:i:s');

                if ($estadoId === 5) { // aterrizado
                    $llegadaReal = Carbon::parse($llegadaProg)
                        ->addMinutes($minutosDesvio + 1)
                        ->format('Y-m-d H:i:s');
                }
            }

            // Pasajeros: siempre 0, se incrementa con check-in online
            $pasajeros = 0;

            if ($estadoId === 6) {
                $termLlegadaFinal = null;
            } else {
                $termLlegadaFinal = $termLlegada;
            }

            DB::table('vuelos')->where('id', $vuelo->id)->update([
                'numero_vuelo'            => $numeroVuelo,
                'aerolinea_id'            => 1,
                'ruta_id'                 => $ruta->id,
                'avion_id'                => $avionId,
                'estado_id'               => $estadoId,
                'hora_salida_programada'  => $salidaProg,
                'hora_salida_real'        => $salidaReal,
                'hora_llegada_programada' => $llegadaProg,
                'hora_llegada_real'       => $llegadaReal,
                'puerta_salida'           => null,
                'puerta_llegada'          => null,
                'terminal_salida'         => $termSalida,
                'terminal_llegada'        => $termLlegadaFinal,
                'pasajeros_confirmados'   => $pasajeros,
                'tripulacion_cantidad'    => $tripulacion,
            ]);
        }
    }

    // Determina el estado_id de un vuelo segun su fecha.
    // 1=programado, 2=embarcando, 3=en_vuelo, 4=retrasado, 5=aterrizado, 6=cancelado
    private function determinarEstado(Carbon $fechaVuelo, Carbon $hoy, int $seq): int
    {
        if ($fechaVuelo->lt($hoy)) {
            if ($seq % 11 === 0) {
                return 6;
            }
            return 5;
        }

        if ($fechaVuelo->eq($hoy)) {
            $variante = $seq % 6;
            return match ($variante) {
                0 => 2, // embarcando
                1 => 3, // en_vuelo
                2 => 4, // retrasado
                3 => 5, // aterrizado
                default => 1, // programado
            };
        }

        return 1; // futuro → programado
    }
}
