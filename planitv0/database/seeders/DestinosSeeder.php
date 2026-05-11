<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DestinosSeeder extends Seeder
{
    public function run(): void
    {
        // Solo limpia ofertas y vuelos de destinos/ofertas (origen_ciudad_id != null).
        // NO se truncan ciudades ni vuelos completos para no borrar reservas existentes.
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        // Eliminar asientos de vuelos de destinos antes de borrar los vuelos
        $idsVuelosDestinos = DB::table('vuelos')->whereNotNull('origen_ciudad_id')->pluck('id');
        if ($idsVuelosDestinos->isNotEmpty()) {
            DB::table('asientos_vuelo')->whereIn('vuelo_id', $idsVuelosDestinos)->delete();
        }
        DB::table('vuelos')->whereNotNull('origen_ciudad_id')->delete();
        DB::table('ciudades')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $madridId = DB::table('ciudades')->insertGetId([
            'nombre' => 'Madrid',
            'pais' => 'Espana',
            'codigo_iata' => 'MAD',
            'imagen' => '/img/destinos/MAD.jpg',
            'latitud' => 40.416775,
            'longitud' => -3.703790,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $barcelonaId = DB::table('ciudades')->insertGetId([
            'nombre' => 'Barcelona',
            'pais' => 'Espana',
            'codigo_iata' => 'BCN',
            'imagen' => '/img/destinos/BCN.jpg',
            'latitud' => 41.385064,
            'longitud' => 2.173404,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $parisId = DB::table('ciudades')->insertGetId([
            'nombre' => 'Paris',
            'pais' => 'Francia',
            'codigo_iata' => 'CDG',
            'imagen' => '/img/destinos/CDG.jpg',
            'latitud' => 49.009690,
            'longitud' => 2.547924,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $londresId = DB::table('ciudades')->insertGetId([
            'nombre' => 'Londres',
            'pais' => 'Reino Unido',
            'codigo_iata' => 'LHR',
            'imagen' => '/img/destinos/LHR.jpg',
            'latitud' => 51.470020,
            'longitud' => -0.454295,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ciudades')->insert([
            ['nombre' => 'Valencia', 'pais' => 'Espana', 'codigo_iata' => 'VLC', 'imagen' => '/img/destinos/VLC.jpg', 'latitud' => 39.489915, 'longitud' => -0.381382, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Sevilla', 'pais' => 'Espana', 'codigo_iata' => 'SVQ', 'imagen' => '/img/destinos/SVQ.jpg', 'latitud' => 37.418003, 'longitud' => -5.893056, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Zaragoza', 'pais' => 'Espana', 'codigo_iata' => 'ZAZ', 'imagen' => '/img/destinos/ZAZ.jpg', 'latitud' => 41.666958, 'longitud' => -0.889386, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Malaga', 'pais' => 'Espana', 'codigo_iata' => 'AGP', 'imagen' => '/img/destinos/AGP.jpg', 'latitud' => 36.675068, 'longitud' => -4.499265, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Gran Canaria', 'pais' => 'Espana', 'codigo_iata' => 'LPA', 'imagen' => '/img/destinos/LPA.jpg', 'latitud' => 27.931189, 'longitud' => -15.386694, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Tenerife Norte', 'pais' => 'Espana', 'codigo_iata' => 'TFN', 'imagen' => '/img/destinos/TFN.jpg', 'latitud' => 28.482777, 'longitud' => -16.341111, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Tenerife Sur', 'pais' => 'Espana', 'codigo_iata' => 'TFS', 'imagen' => '/img/destinos/TFS.jpg', 'latitud' => 28.044444, 'longitud' => -16.572778, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Lanzarote', 'pais' => 'Espana', 'codigo_iata' => 'ACE', 'imagen' => '/img/destinos/ACE.jpg', 'latitud' => 29.713333, 'longitud' => -13.528611, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'La Palma', 'pais' => 'Espana', 'codigo_iata' => 'SPC', 'imagen' => '/img/destinos/SPC.jpg', 'latitud' => 28.626944, 'longitud' => -17.755833, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Fuerteventura', 'pais' => 'Espana', 'codigo_iata' => 'FUE', 'imagen' => '/img/destinos/FUE.jpg', 'latitud' => 28.452778, 'longitud' => -13.863333, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Estrasburgo', 'pais' => 'Francia', 'codigo_iata' => 'SXB', 'imagen' => '/img/destinos/SXB.jpg', 'latitud' => 48.545556, 'longitud' => 7.628056, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Paris - Orly', 'pais' => 'Francia', 'codigo_iata' => 'ORY', 'imagen' => '/img/destinos/ORY.jpg', 'latitud' => 48.723333, 'longitud' => 2.379444, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Lyon', 'pais' => 'Francia', 'codigo_iata' => 'LYS', 'imagen' => '/img/destinos/LYS.jpg', 'latitud' => 45.725556, 'longitud' => 5.081111, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Niza', 'pais' => 'Francia', 'codigo_iata' => 'NCE', 'imagen' => '/img/destinos/NCE.jpg', 'latitud' => 43.658333, 'longitud' => 7.215, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Nantes', 'pais' => 'Francia', 'codigo_iata' => 'NTE', 'imagen' => '/img/destinos/NTE.jpg', 'latitud' => 47.153889, 'longitud' => -1.610833, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Bastia - Corsega', 'pais' => 'Francia', 'codigo_iata' => 'BIA', 'imagen' => '/img/destinos/BIA.jpg', 'latitud' => 42.552778, 'longitud' => 9.483333, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Toulouse', 'pais' => 'Francia', 'codigo_iata' => 'TLS', 'imagen' => '/img/destinos/TLS.jpg', 'latitud' => 43.629444, 'longitud' => 1.363611, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Marsella', 'pais' => 'Francia', 'codigo_iata' => 'MRS', 'imagen' => '/img/destinos/MRS.jpg', 'latitud' => 43.436667, 'longitud' => 5.221111, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Burdeos', 'pais' => 'Francia', 'codigo_iata' => 'BOD', 'imagen' => '/img/destinos/BOD.jpg', 'latitud' => 44.828056, 'longitud' => -0.715556, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Venecia', 'pais' => 'Italia', 'codigo_iata' => 'VCE', 'imagen' => '/img/destinos/VCE.jpg', 'latitud' => 45.505278, 'longitud' => 12.351944, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Napoles', 'pais' => 'Italia', 'codigo_iata' => 'NAP', 'imagen' => '/img/destinos/NAP.jpg', 'latitud' => 40.886111, 'longitud' => 14.290556, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Milan', 'pais' => 'Italia', 'codigo_iata' => 'MXP', 'imagen' => '/img/destinos/MXP.jpg', 'latitud' => 45.630556, 'longitud' => 8.723056, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Milan - Linate', 'pais' => 'Italia', 'codigo_iata' => 'LIN', 'imagen' => '/img/destinos/LIN.jpg', 'latitud' => 45.446667, 'longitud' => 9.276944, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Catania', 'pais' => 'Italia', 'codigo_iata' => 'CTA', 'imagen' => '/img/destinos/CTA.jpg', 'latitud' => 37.466667, 'longitud' => 15.066667, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Bologna', 'pais' => 'Italia', 'codigo_iata' => 'BLQ', 'imagen' => '/img/destinos/BLQ.jpg', 'latitud' => 44.535556, 'longitud' => 11.289444, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Roma - Fiumicino', 'pais' => 'Italia', 'codigo_iata' => 'FCO', 'imagen' => '/img/destinos/FCO.jpg', 'latitud' => 41.800278, 'longitud' => 12.238889, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Pisa', 'pais' => 'Italia', 'codigo_iata' => 'PSA', 'imagen' => '/img/destinos/PSA.jpg', 'latitud' => 43.683333, 'longitud' => 10.3925, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Palermo', 'pais' => 'Italia', 'codigo_iata' => 'PMO', 'imagen' => '/img/destinos/PMO.jpg', 'latitud' => 38.175556, 'longitud' => 13.091111, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Florencia', 'pais' => 'Italia', 'codigo_iata' => 'FLR', 'imagen' => '/img/destinos/FLR.jpg', 'latitud' => 43.810556, 'longitud' => 11.205556, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Genova', 'pais' => 'Italia', 'codigo_iata' => 'GOA', 'imagen' => '/img/destinos/GOA.jpg', 'latitud' => 44.413889, 'longitud' => 8.838056, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Madeira', 'pais' => 'Portugal', 'codigo_iata' => 'FNC', 'imagen' => '/img/destinos/FNC.jpg', 'latitud' => 32.695556, 'longitud' => -16.774167, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Oporto', 'pais' => 'Portugal', 'codigo_iata' => 'OPO', 'imagen' => '/img/destinos/OPO.jpg', 'latitud' => 41.248056, 'longitud' => -8.681389, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Faro', 'pais' => 'Portugal', 'codigo_iata' => 'FAO', 'imagen' => '/img/destinos/FAO.jpg', 'latitud' => 37.014444, 'longitud' => -7.966111, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Lisboa', 'pais' => 'Portugal', 'codigo_iata' => 'LIS', 'imagen' => '/img/destinos/LIS.jpg', 'latitud' => 38.774167, 'longitud' => -9.134167, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Creta', 'pais' => 'Grecia', 'codigo_iata' => 'HER', 'imagen' => '/img/destinos/HER.jpg', 'latitud' => 35.339444, 'longitud' => 25.180556, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Santorini', 'pais' => 'Grecia', 'codigo_iata' => 'JTR', 'imagen' => '/img/destinos/JTR.jpg', 'latitud' => 36.399167, 'longitud' => 25.479444, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Atenas', 'pais' => 'Grecia', 'codigo_iata' => 'ATH', 'imagen' => '/img/destinos/ATH.jpg', 'latitud' => 37.936389, 'longitud' => 23.947222, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Fez', 'pais' => 'Marruecos', 'codigo_iata' => 'FEZ', 'imagen' => '/img/destinos/FEZ.jpg', 'latitud' => 33.9275, 'longitud' => -4.977778, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Constantina', 'pais' => 'Argelia', 'codigo_iata' => 'CZL', 'imagen' => '/img/destinos/CZL.jpg', 'latitud' => 36.276111, 'longitud' => 6.620833, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Nador', 'pais' => 'Marruecos', 'codigo_iata' => 'NDR', 'imagen' => '/img/destinos/NDR.jpg', 'latitud' => 35.168611, 'longitud' => -2.955556, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Marrakech', 'pais' => 'Marruecos', 'codigo_iata' => 'RAK', 'imagen' => '/img/destinos/RAK.jpg', 'latitud' => 31.606389, 'longitud' => -8.036389, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Tanger', 'pais' => 'Marruecos', 'codigo_iata' => 'TNG', 'imagen' => '/img/destinos/TNG.jpg', 'latitud' => 35.726389, 'longitud' => -5.9175, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Casablanca', 'pais' => 'Marruecos', 'codigo_iata' => 'CMN', 'imagen' => '/img/destinos/CMN.jpg', 'latitud' => 33.3675, 'longitud' => -7.589972, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Nuremberg', 'pais' => 'Alemania', 'codigo_iata' => 'NUE', 'imagen' => '/img/destinos/NUE.jpg', 'latitud' => 49.498333, 'longitud' => 11.076944, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Munich', 'pais' => 'Alemania', 'codigo_iata' => 'MUC', 'imagen' => '/img/destinos/MUC.jpg', 'latitud' => 48.353783, 'longitud' => 11.786086, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Dusseldorf', 'pais' => 'Alemania', 'codigo_iata' => 'DUS', 'imagen' => '/img/destinos/DUS.jpg', 'latitud' => 51.289444, 'longitud' => 6.766944, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Hanover', 'pais' => 'Alemania', 'codigo_iata' => 'HAJ', 'imagen' => '/img/destinos/HAJ.jpg', 'latitud' => 52.461111, 'longitud' => 9.685278, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Hamburgo', 'pais' => 'Alemania', 'codigo_iata' => 'HAM', 'imagen' => '/img/destinos/HAM.jpg', 'latitud' => 53.633056, 'longitud' => 9.988333, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Frankfurt', 'pais' => 'Alemania', 'codigo_iata' => 'FRA', 'imagen' => '/img/destinos/FRA.jpg', 'latitud' => 50.037933, 'longitud' => 8.562152, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Berlin-Brandenburgo', 'pais' => 'Alemania', 'codigo_iata' => 'BER', 'imagen' => '/img/destinos/BER.jpg', 'latitud' => 52.366667, 'longitud' => 13.503333, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Stuttgart', 'pais' => 'Alemania', 'codigo_iata' => 'STR', 'imagen' => '/img/destinos/STR.jpg', 'latitud' => 48.689939, 'longitud' => 9.221839, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Dubrovnik', 'pais' => 'Croacia', 'codigo_iata' => 'DBV', 'imagen' => '/img/destinos/DBV.jpg', 'latitud' => 42.561111, 'longitud' => 18.268611, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Split', 'pais' => 'Croacia', 'codigo_iata' => 'SPU', 'imagen' => '/img/destinos/SPU.jpg', 'latitud' => 43.538611, 'longitud' => 16.297222, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Estocolmo', 'pais' => 'Suecia', 'codigo_iata' => 'ARN', 'imagen' => '/img/destinos/ARN.jpg', 'latitud' => 59.651944, 'longitud' => 17.918611, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Gotemburgo', 'pais' => 'Suecia', 'codigo_iata' => 'GOT', 'imagen' => '/img/destinos/GOT.jpg', 'latitud' => 57.666944, 'longitud' => 12.279444, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Zurich', 'pais' => 'Suiza', 'codigo_iata' => 'ZRH', 'imagen' => '/img/destinos/ZRH.jpg', 'latitud' => 47.458056, 'longitud' => 8.555556, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Basilea', 'pais' => 'Suiza', 'codigo_iata' => 'BSL', 'imagen' => '/img/destinos/BSL.jpg', 'latitud' => 47.590833, 'longitud' => 7.529444, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Ginebra', 'pais' => 'Suiza', 'codigo_iata' => 'GVA', 'imagen' => '/img/destinos/GVA.jpg', 'latitud' => 46.238056, 'longitud' => 6.108056, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Copenhague', 'pais' => 'Dinamarca', 'codigo_iata' => 'CPH', 'imagen' => '/img/destinos/CPH.jpg', 'latitud' => 55.618056, 'longitud' => 12.656111, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Billund', 'pais' => 'Dinamarca', 'codigo_iata' => 'BLL', 'imagen' => '/img/destinos/BLL.jpg', 'latitud' => 55.740556, 'longitud' => 9.150556, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Bucarest - Henri Coanda', 'pais' => 'Rumania', 'codigo_iata' => 'OTP', 'imagen' => '/img/destinos/OTP.jpg', 'latitud' => 44.571389, 'longitud' => 26.085556, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Cluj-Napoca', 'pais' => 'Rumania', 'codigo_iata' => 'CLJ', 'imagen' => '/img/destinos/CLJ.jpg', 'latitud' => 46.785, 'longitud' => 23.686389, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Timisoara', 'pais' => 'Rumania', 'codigo_iata' => 'TSR', 'imagen' => '/img/destinos/TSR.jpg', 'latitud' => 45.809167, 'longitud' => 21.336111, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Iasi', 'pais' => 'Rumania', 'codigo_iata' => 'IAS', 'imagen' => '/img/destinos/IAS.jpg', 'latitud' => 47.178611, 'longitud' => 27.620278, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Oslo', 'pais' => 'Noruega', 'codigo_iata' => 'OSL', 'imagen' => '/img/destinos/OSL.jpg', 'latitud' => 59.651944, 'longitud' => 10.686944, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Bergen', 'pais' => 'Noruega', 'codigo_iata' => 'BGO', 'imagen' => '/img/destinos/BGO.jpg', 'latitud' => 60.293333, 'longitud' => 5.218611, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Stavanger', 'pais' => 'Noruega', 'codigo_iata' => 'SVG', 'imagen' => '/img/destinos/SVG.jpg', 'latitud' => 58.876389, 'longitud' => 5.6375, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Tromso', 'pais' => 'Noruega', 'codigo_iata' => 'TOS', 'imagen' => '/img/destinos/TOS.jpg', 'latitud' => 69.683333, 'longitud' => 18.918611, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Estambul', 'pais' => 'Turquia', 'codigo_iata' => 'IST', 'imagen' => '/img/destinos/IST.jpg', 'latitud' => 41.275278, 'longitud' => 28.751944, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Amsterdam', 'pais' => 'Paises Bajos', 'codigo_iata' => 'AMS', 'imagen' => '/img/destinos/AMS.jpg', 'latitud' => 52.310539, 'longitud' => 4.768274, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Dublin', 'pais' => 'Irlanda', 'codigo_iata' => 'DUB', 'imagen' => '/img/destinos/DUB.jpg', 'latitud' => 53.421333, 'longitud' => -6.270075, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Viena', 'pais' => 'Austria', 'codigo_iata' => 'VIE', 'imagen' => '/img/destinos/VIE.jpg', 'latitud' => 48.110278, 'longitud' => 16.569722, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Bruselas', 'pais' => 'Belgica', 'codigo_iata' => 'BRU', 'imagen' => '/img/destinos/BRU.jpg', 'latitud' => 50.901389, 'longitud' => 4.484444, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Praga', 'pais' => 'Republica Checa', 'codigo_iata' => 'PRG', 'imagen' => '/img/destinos/PRG.jpg', 'latitud' => 50.100833, 'longitud' => 14.26, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Budapest', 'pais' => 'Hungria', 'codigo_iata' => 'BUD', 'imagen' => '/img/destinos/BUD.jpg', 'latitud' => 47.436944, 'longitud' => 19.255556, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'El Cairo', 'pais' => 'Egipto', 'codigo_iata' => 'CAI', 'imagen' => '/img/destinos/CAI.jpg', 'latitud' => 30.121944, 'longitud' => 31.405556, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Malta', 'pais' => 'Malta', 'codigo_iata' => 'MLA', 'imagen' => '/img/destinos/MLA.jpg', 'latitud' => 35.8575, 'longitud' => 14.4775, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Reikiavik', 'pais' => 'Islandia', 'codigo_iata' => 'KEF', 'imagen' => '/img/destinos/KEF.jpg', 'latitud' => 63.985, 'longitud' => -22.605556, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Tirana', 'pais' => 'Albania', 'codigo_iata' => 'TIA', 'imagen' => '/img/destinos/TIA.jpg', 'latitud' => 41.414444, 'longitud' => 19.720556, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Recuperar todos los IDs de ciudades por codigo IATA
        $ids = DB::table('ciudades')->pluck('id', 'codigo_iata');

        // Mapa IATA → nombre de ciudad para rellenar campos de texto
        $nombres = DB::table('ciudades')->pluck('nombre', 'codigo_iata');

        // Pares de rutas directas: [origen, destino, precio, dias_desde_hoy, asientos, terminal, tarifa]
        // Para cada par se crean vuelo de ida Y vuelo de vuelta
        $pares = [
            // ---- ESPANA DOMESTICO (Madrid) ----
            ['MAD', 'BCN',  80,  1, 150, 'T4', 'planit_easy'],
            ['MAD', 'VLC',  75,  2,  80, 'T4', 'planit_easy'],
            ['MAD', 'SVQ',  70,  1,  90, 'T4', 'planit_easy'],
            ['MAD', 'AGP',  90,  3,  70, 'T4', 'planit_easy'],
            ['MAD', 'ZAZ',  60,  1,  50, 'T2', 'planit_easy'],
            ['MAD', 'LPA', 130,  5,  80, 'T4', 'planit_easy'],
            ['MAD', 'TFN', 125,  4,  80, 'T4', 'planit_easy'],
            ['MAD', 'TFS', 120,  6,  90, 'T4', 'planit_easy'],
            ['MAD', 'ACE', 135,  7,  60, 'T4', 'planit_easy'],
            ['MAD', 'FUE', 130,  8,  70, 'T4', 'planit_easy'],
            ['MAD', 'SPC', 140,  9,  40, 'T4', 'planit_easy'],
            // ---- ESPANA DOMESTICO (Barcelona) ----
            ['BCN', 'VLC',  65,  1,  80, 'T1', 'planit_easy'],
            ['BCN', 'SVQ',  95,  2,  70, 'T1', 'planit_easy'],
            ['BCN', 'AGP', 100,  3,  60, 'T1', 'planit_easy'],
            ['BCN', 'ZAZ',  55,  1,  50, 'T1', 'planit_easy'],
            ['BCN', 'LPA', 150,  5,  70, 'T1', 'planit_easy'],
            ['BCN', 'TFN', 145,  4,  75, 'T1', 'planit_easy'],
            ['BCN', 'TFS', 145,  6,  80, 'T1', 'planit_easy'],
            ['BCN', 'ACE', 155,  7,  55, 'T1', 'planit_easy'],
            ['BCN', 'FUE', 150,  8,  60, 'T1', 'planit_easy'],
            ['BCN', 'SPC', 160,  9,  35, 'T1', 'planit_easy'],
            // ---- MADRID - FRANCIA ----
            ['MAD', 'CDG', 160,  5,  55, 'T4', 'planit_easy'],
            ['MAD', 'ORY', 155,  6,  60, 'T4', 'planit_easy'],
            ['MAD', 'TLS', 110,  3,  40, 'T4', 'planit_easy'],
            ['MAD', 'BOD', 120,  4,  45, 'T4', 'planit_easy'],
            ['MAD', 'LYS', 140,  7,  50, 'T4', 'planit_easy'],
            ['MAD', 'NCE', 130,  8,  40, 'T4', 'planit_easy'],
            ['MAD', 'MRS', 125,  5,  45, 'T4', 'planit_easy'],
            ['MAD', 'NTE', 135,  6,  40, 'T4', 'planit_easy'],
            // ---- BARCELONA - FRANCIA ----
            ['BCN', 'CDG', 135,  2,  45, 'T1', 'planit_easy'],
            ['BCN', 'ORY', 130,  3,  50, 'T1', 'planit_easy'],
            ['BCN', 'TLS',  75,  1,  35, 'T1', 'planit_easy'],
            ['BCN', 'MRS',  80,  2,  40, 'T1', 'planit_easy'],
            ['BCN', 'LYS',  90,  4,  45, 'T1', 'planit_easy'],
            ['BCN', 'NCE',  85,  3,  40, 'T1', 'planit_easy'],
            ['BCN', 'NTE', 120,  5,  35, 'T1', 'planit_easy'],
            ['BCN', 'BOD', 110,  4,  40, 'T1', 'planit_easy'],
            ['BCN', 'SXB',  95,  3,  30, 'T1', 'planit_easy'],
            ['BCN', 'BIA', 140,  6,  25, 'T1', 'planit_easy'],
            // ---- OTRAS CIUDADES ESPANOLAS - FRANCIA ----
            ['SVQ', 'CDG', 160,  6,  40, 'T4', 'planit_easy'],
            ['SVQ', 'ORY', 155,  7,  35, 'T4', 'planit_easy'],
            ['VLC', 'CDG', 140,  5,  35, 'T1', 'planit_easy'],
            ['VLC', 'ORY', 135,  6,  30, 'T1', 'planit_easy'],
            ['AGP', 'CDG', 150,  7,  45, 'T4', 'planit_easy'],
            ['AGP', 'ORY', 145,  8,  40, 'T4', 'planit_easy'],
            // ---- MADRID - PORTUGAL ----
            ['MAD', 'LIS', 100,  2,  90, 'T4', 'planit_easy'],
            ['MAD', 'OPO', 110,  3,  70, 'T4', 'planit_easy'],
            ['MAD', 'FAO', 105,  4,  50, 'T4', 'planit_easy'],
            ['MAD', 'FNC', 180,  8,  45, 'T4', 'planit_easy'],
            // ---- BARCELONA - PORTUGAL ----
            ['BCN', 'LIS', 130,  3,  65, 'T1', 'planit_easy'],
            ['BCN', 'OPO', 135,  4,  55, 'T1', 'planit_easy'],
            ['BCN', 'FAO', 140,  5,  45, 'T1', 'planit_easy'],
            ['BCN', 'FNC', 195,  9,  40, 'T1', 'planit_easy'],
            // ---- OTRAS CIUDADES ESPANOLAS - PORTUGAL ----
            ['SVQ', 'LIS',  80,  1,  60, 'T4', 'planit_easy'],
            ['SVQ', 'OPO',  90,  2,  50, 'T4', 'planit_easy'],
            ['VLC', 'LIS', 115,  3,  35, 'T1', 'planit_easy'],
            ['AGP', 'LIS', 120,  4,  40, 'T4', 'planit_easy'],
            // ---- MADRID - REINO UNIDO ----
            ['MAD', 'LHR', 220,  7,  50, 'T4', 'planit_comfort'],
            // ---- BARCELONA - REINO UNIDO ----
            ['BCN', 'LHR', 120,  3,  55, 'T1', 'planit_easy'],
            // ---- OTRAS CIUDADES ESPANOLAS - REINO UNIDO ----
            ['SVQ', 'LHR', 150,  5,  40, 'T4', 'planit_easy'],
            ['AGP', 'LHR', 130,  4,  45, 'T4', 'planit_easy'],
            ['VLC', 'LHR', 145,  5,  40, 'T1', 'planit_easy'],
            ['LPA', 'LHR', 175,  6,  45, 'T4', 'planit_easy'],
            ['TFS', 'LHR', 170,  7,  50, 'T4', 'planit_easy'],
            ['ACE', 'LHR', 180,  8,  40, 'T4', 'planit_easy'],
            ['FUE', 'LHR', 175,  7,  45, 'T4', 'planit_easy'],
            // ---- MADRID - ITALIA ----
            ['MAD', 'FCO', 160,  4,  60, 'T4', 'planit_easy'],
            ['MAD', 'MXP', 150,  5,  55, 'T4', 'planit_easy'],
            ['MAD', 'VCE', 170,  6,  50, 'T4', 'planit_easy'],
            ['MAD', 'NAP', 165,  7,  45, 'T4', 'planit_easy'],
            ['MAD', 'BLQ', 155,  5,  40, 'T4', 'planit_easy'],
            ['MAD', 'PSA', 160,  6,  35, 'T4', 'planit_easy'],
            ['MAD', 'FLR', 165,  7,  35, 'T4', 'planit_easy'],
            ['MAD', 'CTA', 180,  8,  35, 'T4', 'planit_easy'],
            ['MAD', 'PMO', 175,  9,  40, 'T4', 'planit_easy'],
            // ---- BARCELONA - ITALIA ----
            ['BCN', 'FCO', 140,  3,  55, 'T1', 'planit_easy'],
            ['BCN', 'MXP', 120,  2,  60, 'T1', 'planit_easy'],
            ['BCN', 'VCE', 130,  4,  55, 'T1', 'planit_easy'],
            ['BCN', 'NAP', 135,  5,  50, 'T1', 'planit_easy'],
            ['BCN', 'BLQ', 115,  3,  45, 'T1', 'planit_easy'],
            ['BCN', 'CTA', 155,  7,  40, 'T1', 'planit_easy'],
            ['BCN', 'PMO', 150,  8,  35, 'T1', 'planit_easy'],
            ['BCN', 'LIN', 110,  2,  55, 'T1', 'planit_easy'],
            ['BCN', 'PSA', 120,  3,  45, 'T1', 'planit_easy'],
            ['BCN', 'FLR', 125,  4,  40, 'T1', 'planit_easy'],
            ['BCN', 'GOA', 110,  3,  45, 'T1', 'planit_easy'],
            // ---- OTRAS CIUDADES ESPANOLAS - ITALIA ----
            ['VLC', 'FCO', 155,  5,  35, 'T1', 'planit_easy'],
            ['VLC', 'MXP', 145,  4,  30, 'T1', 'planit_easy'],
            ['SVQ', 'FCO', 165,  6,  35, 'T4', 'planit_easy'],
            ['SVQ', 'MXP', 155,  5,  30, 'T4', 'planit_easy'],
            ['AGP', 'FCO', 160,  5,  30, 'T4', 'planit_easy'],
            ['AGP', 'MXP', 150,  4,  30, 'T4', 'planit_easy'],
            // ---- MADRID - ALEMANIA ----
            ['MAD', 'FRA', 185,  5,  55, 'T4', 'planit_easy'],
            ['MAD', 'MUC', 195,  6,  50, 'T4', 'planit_easy'],
            ['MAD', 'BER', 190,  7,  45, 'T4', 'planit_easy'],
            ['MAD', 'DUS', 185,  5,  40, 'T4', 'planit_easy'],
            ['MAD', 'HAM', 200,  8,  35, 'T4', 'planit_easy'],
            ['MAD', 'STR', 185,  6,  40, 'T4', 'planit_easy'],
            ['MAD', 'NUE', 190,  7,  35, 'T4', 'planit_easy'],
            ['MAD', 'HAJ', 195,  8,  30, 'T4', 'planit_easy'],
            // ---- BARCELONA - ALEMANIA ----
            ['BCN', 'FRA', 165,  4,  55, 'T1', 'planit_easy'],
            ['BCN', 'MUC', 170,  5,  50, 'T1', 'planit_easy'],
            ['BCN', 'BER', 175,  6,  45, 'T1', 'planit_easy'],
            ['BCN', 'DUS', 165,  4,  40, 'T1', 'planit_easy'],
            ['BCN', 'HAM', 180,  7,  35, 'T1', 'planit_easy'],
            ['BCN', 'STR', 155,  5,  40, 'T1', 'planit_easy'],
            ['BCN', 'NUE', 160,  6,  35, 'T1', 'planit_easy'],
            ['BCN', 'HAJ', 170,  7,  30, 'T1', 'planit_easy'],
            // ---- OTRAS CIUDADES ESPANOLAS - ALEMANIA ----
            ['VLC', 'FRA', 180,  5,  30, 'T1', 'planit_easy'],
            ['VLC', 'MUC', 185,  6,  30, 'T1', 'planit_easy'],
            ['VLC', 'BER', 190,  7,  25, 'T1', 'planit_easy'],
            ['VLC', 'DUS', 185,  5,  30, 'T1', 'planit_easy'],
            ['SVQ', 'FRA', 190,  6,  30, 'T4', 'planit_easy'],
            ['SVQ', 'MUC', 195,  7,  25, 'T4', 'planit_easy'],
            ['SVQ', 'BER', 195,  8,  25, 'T4', 'planit_easy'],
            ['AGP', 'FRA', 190,  6,  30, 'T4', 'planit_easy'],
            ['AGP', 'MUC', 195,  7,  25, 'T4', 'planit_easy'],
            ['AGP', 'BER', 195,  8,  25, 'T4', 'planit_easy'],
            ['AGP', 'DUS', 185,  5,  30, 'T4', 'planit_easy'],
            // ---- MADRID - PAISES BAJOS ----
            ['MAD', 'AMS', 195,  5,  55, 'T4', 'planit_easy'],
            // ---- BARCELONA - PAISES BAJOS ----
            ['BCN', 'AMS', 170,  4,  50, 'T1', 'planit_easy'],
            // ---- OTRAS CIUDADES ESPANOLAS - PAISES BAJOS ----
            ['SVQ', 'AMS', 200,  6,  40, 'T4', 'planit_easy'],
            ['VLC', 'AMS', 195,  5,  35, 'T1', 'planit_easy'],
            ['AGP', 'AMS', 200,  6,  35, 'T4', 'planit_easy'],
            // ---- MADRID - BELGICA ----
            ['MAD', 'BRU', 190,  5,  45, 'T4', 'planit_easy'],
            // ---- BARCELONA - BELGICA ----
            ['BCN', 'BRU', 165,  4,  40, 'T1', 'planit_easy'],
            // ---- OTRAS CIUDADES ESPANOLAS - BELGICA ----
            ['SVQ', 'BRU', 195,  6,  35, 'T4', 'planit_easy'],
            ['VLC', 'BRU', 185,  5,  30, 'T1', 'planit_easy'],
            ['AGP', 'BRU', 195,  6,  30, 'T4', 'planit_easy'],
            // ---- MADRID - SUIZA ----
            ['MAD', 'ZRH', 200,  6,  45, 'T4', 'planit_easy'],
            ['MAD', 'GVA', 195,  5,  50, 'T4', 'planit_easy'],
            ['MAD', 'BSL', 200,  6,  35, 'T4', 'planit_easy'],
            // ---- BARCELONA - SUIZA ----
            ['BCN', 'ZRH', 175,  5,  45, 'T1', 'planit_easy'],
            ['BCN', 'GVA', 170,  4,  50, 'T1', 'planit_easy'],
            ['BCN', 'BSL', 165,  3,  40, 'T1', 'planit_easy'],
            // ---- MADRID - ESCANDINAVIA ----
            ['MAD', 'ARN', 250,  8,  40, 'T4', 'planit_easy'],
            ['MAD', 'OSL', 245,  7,  35, 'T4', 'planit_easy'],
            ['MAD', 'CPH', 235,  6,  45, 'T4', 'planit_easy'],
            ['MAD', 'BLL', 240,  7,  30, 'T4', 'planit_easy'],
            // ---- BARCELONA - ESCANDINAVIA ----
            ['BCN', 'ARN', 230,  7,  40, 'T1', 'planit_easy'],
            ['BCN', 'OSL', 225,  6,  35, 'T1', 'planit_easy'],
            ['BCN', 'CPH', 220,  5,  45, 'T1', 'planit_easy'],
            ['BCN', 'GOT', 225,  7,  30, 'T1', 'planit_easy'],
            ['BCN', 'BLL', 220,  6,  35, 'T1', 'planit_easy'],
            // ---- MADRID - IRLANDA ----
            ['MAD', 'DUB', 180,  5,  50, 'T4', 'planit_easy'],
            // ---- BARCELONA - IRLANDA ----
            ['BCN', 'DUB', 160,  4,  45, 'T1', 'planit_easy'],
            // ---- OTRAS CIUDADES ESPANOLAS - IRLANDA ----
            ['SVQ', 'DUB', 185,  6,  35, 'T4', 'planit_easy'],
            ['VLC', 'DUB', 175,  5,  30, 'T1', 'planit_easy'],
            ['AGP', 'DUB', 180,  6,  30, 'T4', 'planit_easy'],
            // ---- MADRID - AUSTRIA ----
            ['MAD', 'VIE', 200,  6,  45, 'T4', 'planit_easy'],
            // ---- BARCELONA - AUSTRIA ----
            ['BCN', 'VIE', 185,  5,  40, 'T1', 'planit_easy'],
            // ---- MADRID - REP. CHECA ----
            ['MAD', 'PRG', 195,  6,  40, 'T4', 'planit_easy'],
            // ---- BARCELONA - REP. CHECA ----
            ['BCN', 'PRG', 180,  5,  45, 'T1', 'planit_easy'],
            // ---- MADRID - HUNGRIA ----
            ['MAD', 'BUD', 200,  7,  40, 'T4', 'planit_easy'],
            // ---- BARCELONA - HUNGRIA ----
            ['BCN', 'BUD', 185,  6,  40, 'T1', 'planit_easy'],
            // ---- MADRID - RUMANIA ----
            ['MAD', 'OTP', 195,  7,  45, 'T4', 'planit_easy'],
            ['MAD', 'CLJ', 200,  8,  35, 'T4', 'planit_easy'],
            ['MAD', 'TSR', 200,  8,  30, 'T4', 'planit_easy'],
            ['MAD', 'IAS', 205,  9,  25, 'T4', 'planit_easy'],
            // ---- BARCELONA - RUMANIA ----
            ['BCN', 'OTP', 190,  6,  45, 'T1', 'planit_easy'],
            ['BCN', 'CLJ', 195,  7,  35, 'T1', 'planit_easy'],
            ['BCN', 'TSR', 190,  8,  30, 'T1', 'planit_easy'],
            ['BCN', 'IAS', 195,  9,  25, 'T1', 'planit_easy'],
            // ---- MADRID - GRECIA ----
            ['MAD', 'ATH', 220,  7,  50, 'T4', 'planit_easy'],
            ['MAD', 'HER', 230,  8,  40, 'T4', 'planit_easy'],
            // ---- BARCELONA - GRECIA ----
            ['BCN', 'ATH', 200,  6,  50, 'T1', 'planit_easy'],
            ['BCN', 'HER', 215,  7,  40, 'T1', 'planit_easy'],
            ['BCN', 'JTR', 225,  8,  35, 'T1', 'planit_comfort'],
            // ---- MADRID - CROACIA ----
            ['MAD', 'DBV', 210,  7,  40, 'T4', 'planit_easy'],
            ['MAD', 'SPU', 205,  6,  35, 'T4', 'planit_easy'],
            // ---- BARCELONA - CROACIA ----
            ['BCN', 'DBV', 190,  6,  45, 'T1', 'planit_easy'],
            ['BCN', 'SPU', 185,  5,  40, 'T1', 'planit_easy'],
            // ---- MADRID - MARRUECOS ----
            ['MAD', 'CMN', 120,  3,  60, 'T4', 'planit_easy'],
            ['MAD', 'RAK', 130,  4,  55, 'T4', 'planit_easy'],
            ['MAD', 'TNG', 100,  2,  70, 'T4', 'planit_easy'],
            ['MAD', 'FEZ', 125,  4,  45, 'T4', 'planit_easy'],
            ['MAD', 'NDR', 115,  3,  50, 'T4', 'planit_easy'],
            // ---- BARCELONA - MARRUECOS ----
            ['BCN', 'CMN', 130,  3,  55, 'T1', 'planit_easy'],
            ['BCN', 'RAK', 140,  4,  50, 'T1', 'planit_easy'],
            ['BCN', 'TNG', 120,  2,  65, 'T1', 'planit_easy'],
            ['BCN', 'FEZ', 130,  4,  45, 'T1', 'planit_easy'],
            ['BCN', 'NDR', 125,  3,  50, 'T1', 'planit_easy'],
            // ---- OTRAS CIUDADES ESPANOLAS - MARRUECOS ----
            ['SVQ', 'CMN', 110,  2,  55, 'T4', 'planit_easy'],
            ['SVQ', 'TNG', 100,  2,  60, 'T4', 'planit_easy'],
            ['SVQ', 'RAK', 120,  3,  45, 'T4', 'planit_easy'],
            ['AGP', 'CMN', 100,  2,  50, 'T4', 'planit_easy'],
            ['AGP', 'TNG',  90,  1,  55, 'T4', 'planit_easy'],
            ['AGP', 'RAK', 115,  3,  45, 'T4', 'planit_easy'],
            ['VLC', 'CMN', 130,  3,  40, 'T1', 'planit_easy'],
            ['VLC', 'TNG', 120,  2,  45, 'T1', 'planit_easy'],
            ['VLC', 'RAK', 135,  4,  35, 'T1', 'planit_easy'],
            // ---- MARRUECOS - BELGICA/PAISES BAJOS ----
            ['FEZ', 'BRU', 140,  4,  50, 'T1', 'planit_easy'],
            ['NDR', 'BRU', 135,  3,  55, 'T1', 'planit_easy'],
            ['TNG', 'BRU', 130,  3,  55, 'T1', 'planit_easy'],
            ['FEZ', 'AMS', 145,  5,  45, 'T1', 'planit_easy'],
            ['NDR', 'AMS', 140,  4,  50, 'T1', 'planit_easy'],
            // ---- MADRID - ARGELIA ----
            ['MAD', 'CZL', 130,  4,  45, 'T4', 'planit_easy'],
            // ---- BARCELONA - ARGELIA ----
            ['BCN', 'CZL', 120,  3,  40, 'T1', 'planit_easy'],
            // ---- MADRID - TURQUIA ----
            ['MAD', 'IST', 250,  8,  50, 'T4', 'planit_easy'],
            // ---- BARCELONA - TURQUIA ----
            ['BCN', 'IST', 235,  7,  45, 'T1', 'planit_easy'],
            // ---- MADRID - MALTA ----
            ['MAD', 'MLA', 180,  6,  40, 'T4', 'planit_easy'],
            // ---- BARCELONA - MALTA ----
            ['BCN', 'MLA', 165,  5,  45, 'T1', 'planit_easy'],
            // ---- MADRID - ISLANDIA ----
            ['MAD', 'KEF', 310, 10,  30, 'T4', 'planit_easy'],
            // ---- BARCELONA - ISLANDIA ----
            ['BCN', 'KEF', 305, 10,  25, 'T1', 'planit_easy'],
            // ---- MADRID - ALBANIA ----
            ['MAD', 'TIA', 195,  7,  35, 'T4', 'planit_easy'],
            // ---- BARCELONA - ALBANIA ----
            ['BCN', 'TIA', 180,  6,  30, 'T1', 'planit_easy'],
            // ---- MADRID - EGIPTO ----
            ['MAD', 'CAI', 300,  9,  45, 'T4', 'planit_easy'],
            // ---- BARCELONA - EGIPTO ----
            ['BCN', 'CAI', 285,  8,  40, 'T1', 'planit_easy'],
            // ---- ISLAS (Canarias, Madeira) ----
            ['LPA', 'FRA', 215,  5,  40, 'T1', 'planit_easy'],
            ['LPA', 'MUC', 220,  6,  35, 'T1', 'planit_easy'],
            ['LPA', 'AMS', 225,  7,  35, 'T1', 'planit_easy'],
            ['TFS', 'FRA', 210,  5,  40, 'T1', 'planit_easy'],
            ['TFS', 'MUC', 215,  6,  35, 'T1', 'planit_easy'],
            ['FNC', 'LIS', 100,  2,  50, 'T1', 'planit_easy'],
            ['FNC', 'MAD', 180,  8,  45, 'T4', 'planit_easy'],
            // ---- EUROPA DEL ESTE - CIUDADES PRINCIPALES ----
            ['OTP', 'LHR', 210,  5,  45, 'T1', 'planit_easy'],
            ['OTP', 'CDG', 200,  5,  45, 'T1', 'planit_easy'],
            ['OTP', 'FCO', 195,  5,  40, 'T1', 'planit_easy'],
            ['CLJ', 'LHR', 215,  6,  35, 'T1', 'planit_easy'],
            ['CLJ', 'CDG', 205,  6,  35, 'T1', 'planit_easy'],
            ['CLJ', 'FCO', 200,  6,  30, 'T1', 'planit_easy'],
            ['TSR', 'LHR', 215,  6,  35, 'T1', 'planit_easy'],
            ['TSR', 'CDG', 205,  6,  30, 'T1', 'planit_easy'],
            ['IAS', 'LHR', 215,  6,  30, 'T1', 'planit_easy'],
            ['IAS', 'CDG', 210,  6,  30, 'T1', 'planit_easy'],
            ['IAS', 'FCO', 205,  7,  25, 'T1', 'planit_easy'],
            // ---- RUTAS INTER-EUROPEAS (Paris) ----
            ['CDG', 'LHR', 180,  3,  70, 'T2', 'planit_easy'],
            ['CDG', 'AMS', 170,  2,  65, 'T2', 'planit_easy'],
            ['CDG', 'FRA', 180,  4,  60, 'T2', 'planit_easy'],
            ['CDG', 'MUC', 185,  5,  55, 'T2', 'planit_easy'],
            ['CDG', 'BER', 190,  6,  50, 'T2', 'planit_easy'],
            ['CDG', 'MXP', 175,  4,  55, 'T2', 'planit_easy'],
            ['CDG', 'FCO', 180,  5,  50, 'T2', 'planit_easy'],
            ['CDG', 'NAP', 185,  6,  45, 'T2', 'planit_easy'],
            ['CDG', 'VCE', 175,  4,  50, 'T2', 'planit_easy'],
            ['CDG', 'ATH', 220,  7,  45, 'T2', 'planit_easy'],
            ['CDG', 'HER', 225,  8,  40, 'T2', 'planit_easy'],
            ['CDG', 'JTR', 235,  9,  35, 'T2', 'planit_comfort'],
            ['CDG', 'CMN', 130,  3,  55, 'T2', 'planit_easy'],
            ['CDG', 'RAK', 140,  4,  50, 'T2', 'planit_easy'],
            ['CDG', 'TNG', 125,  3,  60, 'T2', 'planit_easy'],
            ['CDG', 'FEZ', 135,  4,  45, 'T2', 'planit_easy'],
            ['CDG', 'NDR', 130,  3,  50, 'T2', 'planit_easy'],
            ['CDG', 'ARN', 200,  6,  50, 'T2', 'planit_easy'],
            ['CDG', 'OSL', 195,  5,  50, 'T2', 'planit_easy'],
            ['CDG', 'CPH', 185,  4,  55, 'T2', 'planit_easy'],
            ['CDG', 'VIE', 185,  5,  50, 'T2', 'planit_easy'],
            ['CDG', 'BRU', 110,  1,  70, 'T2', 'planit_easy'],
            ['CDG', 'PRG', 180,  5,  45, 'T2', 'planit_easy'],
            ['CDG', 'BUD', 185,  6,  45, 'T2', 'planit_easy'],
            ['CDG', 'IST', 245,  8,  50, 'T2', 'planit_easy'],
            ['CDG', 'CAI', 310,  9,  40, 'T2', 'planit_easy'],
            // ---- RUTAS INTER-EUROPEAS (Londres) ----
            ['LHR', 'AMS', 160,  2,  65, 'T5', 'planit_easy'],
            ['LHR', 'FRA', 170,  3,  65, 'T5', 'planit_easy'],
            ['LHR', 'MUC', 175,  4,  60, 'T5', 'planit_easy'],
            ['LHR', 'BER', 180,  5,  55, 'T5', 'planit_easy'],
            ['LHR', 'DUS', 165,  3,  60, 'T5', 'planit_easy'],
            ['LHR', 'HAM', 170,  4,  55, 'T5', 'planit_easy'],
            ['LHR', 'STR', 165,  3,  50, 'T5', 'planit_easy'],
            ['LHR', 'NUE', 170,  4,  45, 'T5', 'planit_easy'],
            ['LHR', 'FCO', 175,  4,  60, 'T5', 'planit_easy'],
            ['LHR', 'MXP', 170,  3,  60, 'T5', 'planit_easy'],
            ['LHR', 'VCE', 175,  4,  55, 'T5', 'planit_easy'],
            ['LHR', 'NAP', 180,  5,  50, 'T5', 'planit_easy'],
            ['LHR', 'ATH', 210,  6,  50, 'T5', 'planit_easy'],
            ['LHR', 'HER', 215,  7,  45, 'T5', 'planit_easy'],
            ['LHR', 'JTR', 220,  7,  40, 'T5', 'planit_comfort'],
            ['LHR', 'CMN', 195,  5,  45, 'T5', 'planit_easy'],
            ['LHR', 'RAK', 200,  6,  40, 'T5', 'planit_easy'],
            ['LHR', 'TNG', 190,  4,  50, 'T5', 'planit_easy'],
            ['LHR', 'ARN', 180,  5,  55, 'T5', 'planit_easy'],
            ['LHR', 'CPH', 160,  3,  60, 'T5', 'planit_easy'],
            ['LHR', 'OSL', 175,  4,  55, 'T5', 'planit_easy'],
            ['LHR', 'GOT', 185,  5,  45, 'T5', 'planit_easy'],
            ['LHR', 'BGO', 195,  6,  40, 'T5', 'planit_easy'],
            ['LHR', 'OTP', 210,  6,  45, 'T5', 'planit_easy'],
            ['LHR', 'IST', 230,  7,  50, 'T5', 'planit_easy'],
            ['LHR', 'CAI', 290,  8,  45, 'T5', 'planit_easy'],
            ['LHR', 'DUB', 100,  1,  80, 'T5', 'planit_easy'],
            // ---- RUTAS INTER-EUROPEAS (Frankfurt) ----
            ['FRA', 'VIE', 160,  4,  55, 'T1', 'planit_easy'],
            ['FRA', 'PRG', 145,  3,  55, 'T1', 'planit_easy'],
            ['FRA', 'BUD', 150,  4,  50, 'T1', 'planit_easy'],
            ['FRA', 'ZRH', 135,  2,  60, 'T1', 'planit_easy'],
            ['FRA', 'GVA', 140,  3,  55, 'T1', 'planit_easy'],
            ['FRA', 'BSL', 130,  2,  55, 'T1', 'planit_easy'],
            ['FRA', 'CPH', 160,  3,  55, 'T1', 'planit_easy'],
            ['FRA', 'ARN', 175,  5,  50, 'T1', 'planit_easy'],
            ['FRA', 'OSL', 170,  4,  50, 'T1', 'planit_easy'],
            ['FRA', 'BGO', 185,  5,  40, 'T1', 'planit_easy'],
            ['FRA', 'SVG', 180,  5,  35, 'T1', 'planit_easy'],
            ['FRA', 'AMS', 155,  3,  60, 'T1', 'planit_easy'],
            ['FRA', 'BRU', 150,  2,  65, 'T1', 'planit_easy'],
            ['FRA', 'FCO', 165,  4,  55, 'T1', 'planit_easy'],
            ['FRA', 'MXP', 155,  3,  55, 'T1', 'planit_easy'],
            ['FRA', 'VCE', 160,  4,  50, 'T1', 'planit_easy'],
            ['FRA', 'NAP', 165,  5,  45, 'T1', 'planit_easy'],
            ['FRA', 'BLQ', 155,  4,  45, 'T1', 'planit_easy'],
            ['FRA', 'PSA', 160,  4,  40, 'T1', 'planit_easy'],
            ['FRA', 'ATH', 215,  7,  45, 'T1', 'planit_easy'],
            ['FRA', 'HER', 220,  7,  40, 'T1', 'planit_easy'],
            ['FRA', 'CMN', 180,  5,  45, 'T1', 'planit_easy'],
            ['FRA', 'RAK', 185,  6,  40, 'T1', 'planit_easy'],
            ['FRA', 'IST', 235,  7,  50, 'T1', 'planit_easy'],
            ['FRA', 'OTP', 175,  5,  45, 'T1', 'planit_easy'],
            ['FRA', 'CAI', 295,  9,  40, 'T1', 'planit_easy'],
            // ---- RUTAS INTER-EUROPEAS (Amsterdam) ----
            ['AMS', 'LHR', 160,  2,  65, 'T3', 'planit_easy'],
            ['AMS', 'FRA', 155,  3,  60, 'T3', 'planit_easy'],
            ['AMS', 'MUC', 160,  4,  55, 'T3', 'planit_easy'],
            ['AMS', 'BER', 165,  5,  50, 'T3', 'planit_easy'],
            ['AMS', 'DUS', 115,  1,  70, 'T3', 'planit_easy'],
            ['AMS', 'HAM', 130,  2,  65, 'T3', 'planit_easy'],
            ['AMS', 'FCO', 170,  4,  55, 'T3', 'planit_easy'],
            ['AMS', 'MXP', 160,  3,  55, 'T3', 'planit_easy'],
            ['AMS', 'VCE', 165,  4,  50, 'T3', 'planit_easy'],
            ['AMS', 'NAP', 170,  5,  45, 'T3', 'planit_easy'],
            ['AMS', 'BLQ', 160,  4,  45, 'T3', 'planit_easy'],
            ['AMS', 'LIS', 165,  4,  50, 'T3', 'planit_easy'],
            ['AMS', 'OPO', 170,  5,  45, 'T3', 'planit_easy'],
            ['AMS', 'ATH', 215,  7,  45, 'T3', 'planit_easy'],
            ['AMS', 'CMN', 185,  5,  45, 'T3', 'planit_easy'],
            ['AMS', 'RAK', 190,  6,  40, 'T3', 'planit_easy'],
            ['AMS', 'VIE', 155,  4,  55, 'T3', 'planit_easy'],
            ['AMS', 'PRG', 150,  3,  55, 'T3', 'planit_easy'],
            ['AMS', 'BUD', 155,  4,  50, 'T3', 'planit_easy'],
            ['AMS', 'BRU', 115,  1,  70, 'T3', 'planit_easy'],
            ['AMS', 'CPH', 145,  3,  55, 'T3', 'planit_easy'],
            ['AMS', 'ARN', 155,  4,  50, 'T3', 'planit_easy'],
            ['AMS', 'OSL', 150,  3,  55, 'T3', 'planit_easy'],
            ['AMS', 'IST', 225,  7,  50, 'T3', 'planit_easy'],
            ['AMS', 'OTP', 185,  5,  40, 'T3', 'planit_easy'],
            // ---- RUTAS INTER-EUROPEAS (Roma) ----
            ['FCO', 'ATH', 190,  5,  50, 'T1', 'planit_easy'],
            ['FCO', 'IST', 225,  6,  50, 'T1', 'planit_easy'],
            ['FCO', 'CMN', 175,  4,  45, 'T1', 'planit_easy'],
            ['FCO', 'TNG', 170,  4,  50, 'T1', 'planit_easy'],
            ['FCO', 'AMS', 170,  4,  55, 'T1', 'planit_easy'],
            ['FCO', 'VIE', 155,  3,  55, 'T1', 'planit_easy'],
            ['FCO', 'PRG', 165,  4,  45, 'T1', 'planit_easy'],
            ['FCO', 'BUD', 160,  4,  45, 'T1', 'planit_easy'],
            ['FCO', 'BRU', 170,  4,  50, 'T1', 'planit_easy'],
            // ---- RUTAS INTER-EUROPEAS (Milan) ----
            ['MXP', 'ATH', 185,  5,  50, 'T1', 'planit_easy'],
            ['MXP', 'IST', 220,  6,  50, 'T1', 'planit_easy'],
            ['MXP', 'CMN', 170,  4,  45, 'T1', 'planit_easy'],
            ['MXP', 'AMS', 165,  3,  55, 'T1', 'planit_easy'],
            ['MXP', 'VIE', 145,  3,  55, 'T1', 'planit_easy'],
            ['MXP', 'PRG', 155,  4,  45, 'T1', 'planit_easy'],
            ['MXP', 'BUD', 155,  4,  45, 'T1', 'planit_easy'],
            ['MXP', 'BRU', 165,  3,  50, 'T1', 'planit_easy'],
            // ---- RUTAS ITALIA MENOR (desde destinos italianos secundarios) ----
            ['VCE', 'LHR', 175,  4,  50, 'T1', 'planit_easy'],
            ['VCE', 'CDG', 170,  4,  50, 'T1', 'planit_easy'],
            ['VCE', 'FRA', 155,  3,  50, 'T1', 'planit_easy'],
            ['NAP', 'LHR', 180,  5,  45, 'T1', 'planit_easy'],
            ['NAP', 'CDG', 175,  5,  45, 'T1', 'planit_easy'],
            ['NAP', 'FRA', 165,  4,  45, 'T1', 'planit_easy'],
            ['BLQ', 'LHR', 175,  4,  40, 'T1', 'planit_easy'],
            ['BLQ', 'CDG', 170,  4,  45, 'T1', 'planit_easy'],
            ['BLQ', 'FRA', 160,  3,  45, 'T1', 'planit_easy'],
            ['PSA', 'LHR', 175,  4,  35, 'T1', 'planit_easy'],
            ['PSA', 'CDG', 170,  4,  40, 'T1', 'planit_easy'],
            ['PSA', 'FRA', 165,  4,  35, 'T1', 'planit_easy'],
            ['PMO', 'CDG', 175,  5,  35, 'T1', 'planit_easy'],
            ['PMO', 'FRA', 170,  5,  30, 'T1', 'planit_easy'],
            ['PMO', 'LHR', 180,  5,  35, 'T1', 'planit_easy'],
            // ---- GRECIA (Santorini, Creta) ----
            ['HER', 'LHR', 210,  6,  40, 'T1', 'planit_easy'],
            ['HER', 'CDG', 205,  6,  40, 'T1', 'planit_easy'],
            ['HER', 'FRA', 200,  5,  40, 'T1', 'planit_easy'],
            ['HER', 'MUC', 200,  5,  40, 'T1', 'planit_easy'],
            ['HER', 'BER', 205,  6,  35, 'T1', 'planit_easy'],
            ['JTR', 'LHR', 215,  7,  30, 'T1', 'planit_comfort'],
            ['JTR', 'CDG', 210,  7,  30, 'T1', 'planit_comfort'],
            ['JTR', 'FRA', 205,  6,  30, 'T1', 'planit_comfort'],
            ['JTR', 'MUC', 205,  6,  25, 'T1', 'planit_comfort'],
            // ---- RUTAS DESDE LYON, MARSELLA, TOULOUSE, BURDEOS, NANTES ----
            ['LYS', 'FCO', 145,  3,  40, 'T1', 'planit_easy'],
            ['LYS', 'MXP', 120,  2,  45, 'T1', 'planit_easy'],
            ['LYS', 'FRA', 145,  3,  40, 'T1', 'planit_easy'],
            ['LYS', 'MUC', 150,  4,  35, 'T1', 'planit_easy'],
            ['LYS', 'LHR', 155,  3,  45, 'T1', 'planit_easy'],
            ['MRS', 'FCO', 140,  3,  40, 'T1', 'planit_easy'],
            ['MRS', 'MXP', 125,  2,  40, 'T1', 'planit_easy'],
            ['MRS', 'FRA', 150,  4,  35, 'T1', 'planit_easy'],
            ['MRS', 'LHR', 155,  3,  40, 'T1', 'planit_easy'],
            ['TLS', 'FCO', 150,  4,  35, 'T1', 'planit_easy'],
            ['TLS', 'MXP', 145,  3,  35, 'T1', 'planit_easy'],
            ['TLS', 'FRA', 160,  4,  30, 'T1', 'planit_easy'],
            ['TLS', 'LHR', 155,  3,  35, 'T1', 'planit_easy'],
            ['BOD', 'FCO', 150,  4,  30, 'T1', 'planit_easy'],
            ['BOD', 'MXP', 145,  3,  30, 'T1', 'planit_easy'],
            ['BOD', 'FRA', 160,  4,  30, 'T1', 'planit_easy'],
            ['BOD', 'LHR', 140,  3,  35, 'T1', 'planit_easy'],
            ['NTE', 'FCO', 155,  4,  30, 'T1', 'planit_easy'],
            ['NTE', 'FRA', 165,  4,  30, 'T1', 'planit_easy'],
            ['NTE', 'LHR', 135,  2,  40, 'T1', 'planit_easy'],
            // ---- OTRAS CONEXIONES INTER-EUROPEAS ----
            ['VIE', 'IST', 185,  4,  55, 'T1', 'planit_easy'],
            ['VIE', 'ATH', 180,  4,  50, 'T1', 'planit_easy'],
            ['PRG', 'IST', 210,  5,  45, 'T1', 'planit_easy'],
            ['BUD', 'IST', 200,  4,  50, 'T1', 'planit_easy'],
            ['BRU', 'IST', 225,  6,  45, 'T1', 'planit_easy'],

            // ---- BILLUND (BLL) ----
            ['BLL', 'LHR', 165,  3,  40, 'T1', 'planit_easy'],
            ['BLL', 'CDG', 160,  4,  35, 'T1', 'planit_easy'],
            ['BLL', 'FRA', 155,  3,  40, 'T1', 'planit_easy'],
            ['BLL', 'AMS', 135,  2,  45, 'T1', 'planit_easy'],
            ['BLL', 'BER', 145,  4,  35, 'T1', 'planit_easy'],
            ['BLL', 'MUC', 150,  5,  30, 'T1', 'planit_easy'],
            ['BLL', 'CPH',  65,  1,  60, 'T1', 'planit_easy'],
            ['BLL', 'ARN', 110,  2,  40, 'T1', 'planit_easy'],
            ['BLL', 'OSL', 120,  3,  35, 'T1', 'planit_easy'],

            // ---- ESTRASBURGO (SXB) ----
            ['SXB', 'CDG', 110,  2,  40, 'T1', 'planit_easy'],
            ['SXB', 'FRA',  95,  1,  45, 'T1', 'planit_easy'],
            ['SXB', 'LHR', 145,  3,  35, 'T1', 'planit_easy'],
            ['SXB', 'AMS', 130,  2,  40, 'T1', 'planit_easy'],
            ['SXB', 'MUC', 110,  2,  40, 'T1', 'planit_easy'],
            ['SXB', 'ZRH',  90,  1,  45, 'T1', 'planit_easy'],

            // ---- PARIS ORLY (ORY) ----
            ['ORY', 'LHR', 150,  2,  60, 'T2', 'planit_easy'],
            ['ORY', 'FRA', 160,  3,  55, 'T2', 'planit_easy'],
            ['ORY', 'AMS', 145,  2,  60, 'T2', 'planit_easy'],
            ['ORY', 'FCO', 160,  4,  50, 'T2', 'planit_easy'],
            ['ORY', 'MXP', 150,  3,  50, 'T2', 'planit_easy'],
            ['ORY', 'BER', 175,  5,  45, 'T2', 'planit_easy'],
            ['ORY', 'MUC', 170,  4,  45, 'T2', 'planit_easy'],
            ['ORY', 'ATH', 210,  6,  40, 'T2', 'planit_easy'],
            ['ORY', 'IST', 235,  7,  45, 'T2', 'planit_easy'],

            // ---- NIZA (NCE) ----
            ['NCE', 'CDG', 120,  2,  50, 'T1', 'planit_easy'],
            ['NCE', 'LHR', 155,  3,  45, 'T1', 'planit_easy'],
            ['NCE', 'FRA', 150,  3,  40, 'T1', 'planit_easy'],
            ['NCE', 'AMS', 165,  4,  35, 'T1', 'planit_easy'],
            ['NCE', 'FCO', 130,  2,  50, 'T1', 'planit_easy'],
            ['NCE', 'MXP', 110,  1,  55, 'T1', 'planit_easy'],
            ['NCE', 'BRU', 140,  3,  40, 'T1', 'planit_easy'],

            // ---- BASTIA CORCEGA (BIA) ----
            ['BIA', 'CDG', 115,  2,  40, 'T1', 'planit_easy'],
            ['BIA', 'ORY', 110,  2,  45, 'T1', 'planit_easy'],
            ['BIA', 'MRS',  90,  1,  50, 'T1', 'planit_easy'],
            ['BIA', 'LYS', 100,  2,  40, 'T1', 'planit_easy'],
            ['BIA', 'NCE',  80,  1,  45, 'T1', 'planit_easy'],
            ['BIA', 'FCO', 120,  2,  35, 'T1', 'planit_easy'],

            // ---- MILAN LINATE (LIN) ----
            ['LIN', 'CDG', 140,  2,  55, 'T1', 'planit_easy'],
            ['LIN', 'LHR', 155,  3,  50, 'T1', 'planit_easy'],
            ['LIN', 'FRA', 130,  2,  55, 'T1', 'planit_easy'],
            ['LIN', 'AMS', 150,  3,  45, 'T1', 'planit_easy'],
            ['LIN', 'BER', 155,  4,  40, 'T1', 'planit_easy'],
            ['LIN', 'FCO',  80,  1,  65, 'T1', 'planit_easy'],
            ['LIN', 'ZRH',  95,  1,  55, 'T1', 'planit_easy'],
            ['LIN', 'BRU', 145,  3,  45, 'T1', 'planit_easy'],

            // ---- CATANIA (CTA) ----
            ['CTA', 'FCO',  95,  1,  60, 'T1', 'planit_easy'],
            ['CTA', 'MXP', 105,  2,  55, 'T1', 'planit_easy'],
            ['CTA', 'CDG', 170,  4,  45, 'T1', 'planit_easy'],
            ['CTA', 'LHR', 180,  5,  40, 'T1', 'planit_easy'],
            ['CTA', 'FRA', 175,  4,  40, 'T1', 'planit_easy'],
            ['CTA', 'AMS', 180,  5,  35, 'T1', 'planit_easy'],
            ['CTA', 'BER', 185,  6,  30, 'T1', 'planit_easy'],

            // ---- FLORENCIA (FLR) ----
            ['FLR', 'CDG', 155,  3,  40, 'T1', 'planit_easy'],
            ['FLR', 'LHR', 165,  4,  35, 'T1', 'planit_easy'],
            ['FLR', 'FRA', 155,  3,  40, 'T1', 'planit_easy'],
            ['FLR', 'AMS', 165,  4,  35, 'T1', 'planit_easy'],
            ['FLR', 'MUC', 145,  3,  40, 'T1', 'planit_easy'],
            ['FLR', 'FCO',  75,  1,  65, 'T1', 'planit_easy'],

            // ---- GENOVA (GOA) ----
            ['GOA', 'CDG', 145,  3,  35, 'T1', 'planit_easy'],
            ['GOA', 'LHR', 160,  4,  30, 'T1', 'planit_easy'],
            ['GOA', 'FRA', 145,  3,  35, 'T1', 'planit_easy'],
            ['GOA', 'AMS', 155,  4,  30, 'T1', 'planit_easy'],
            ['GOA', 'FCO',  90,  1,  55, 'T1', 'planit_easy'],
            ['GOA', 'MXP',  75,  1,  60, 'T1', 'planit_easy'],

            // ---- ZARAGOZA (ZAZ) ----
            ['ZAZ', 'LHR', 150,  4,  25, 'T1', 'planit_easy'],

            // ---- TENERIFE NORTE (TFN) ----
            ['TFN', 'LHR', 175,  5,  50, 'T1', 'planit_easy'],
            ['TFN', 'FRA', 210,  6,  45, 'T1', 'planit_easy'],
            ['TFN', 'AMS', 215,  7,  40, 'T1', 'planit_easy'],
            ['TFN', 'CDG', 205,  6,  45, 'T1', 'planit_easy'],
            ['TFN', 'MUC', 215,  7,  40, 'T1', 'planit_easy'],

            // ---- LA PALMA (SPC) ----
            ['SPC', 'LHR', 185,  6,  35, 'T1', 'planit_easy'],
            ['SPC', 'FRA', 215,  7,  30, 'T1', 'planit_easy'],
            ['SPC', 'AMS', 220,  8,  25, 'T1', 'planit_easy'],
            ['SPC', 'MUC', 215,  7,  25, 'T1', 'planit_easy'],

            // ---- LANZAROTE (ACE) ----
            ['ACE', 'FRA', 215,  6,  45, 'T1', 'planit_easy'],
            ['ACE', 'AMS', 220,  7,  40, 'T1', 'planit_easy'],
            ['ACE', 'MUC', 215,  6,  40, 'T1', 'planit_easy'],
            ['ACE', 'BER', 220,  7,  35, 'T1', 'planit_easy'],
            ['ACE', 'CDG', 210,  6,  45, 'T1', 'planit_easy'],

            // ---- FUERTEVENTURA (FUE) ----
            ['FUE', 'FRA', 210,  6,  45, 'T1', 'planit_easy'],
            ['FUE', 'AMS', 215,  7,  40, 'T1', 'planit_easy'],
            ['FUE', 'MUC', 210,  6,  40, 'T1', 'planit_easy'],
            ['FUE', 'BER', 215,  7,  35, 'T1', 'planit_easy'],
            ['FUE', 'CDG', 205,  6,  45, 'T1', 'planit_easy'],

            // ---- OPORTO (OPO) ----
            ['OPO', 'LHR', 145,  3,  55, 'T1', 'planit_easy'],
            ['OPO', 'CDG', 145,  3,  50, 'T1', 'planit_easy'],
            ['OPO', 'FRA', 160,  4,  45, 'T1', 'planit_easy'],
            ['OPO', 'AMS', 165,  4,  45, 'T1', 'planit_easy'],
            ['OPO', 'BRU', 155,  4,  40, 'T1', 'planit_easy'],
            ['OPO', 'BER', 175,  5,  35, 'T1', 'planit_easy'],
            ['OPO', 'MUC', 175,  5,  35, 'T1', 'planit_easy'],

            // ---- FARO (FAO) ----
            ['FAO', 'LHR', 140,  3,  50, 'T1', 'planit_easy'],
            ['FAO', 'CDG', 150,  4,  45, 'T1', 'planit_easy'],
            ['FAO', 'FRA', 165,  5,  40, 'T1', 'planit_easy'],
            ['FAO', 'AMS', 170,  5,  35, 'T1', 'planit_easy'],
            ['FAO', 'BRU', 160,  4,  40, 'T1', 'planit_easy'],
            ['FAO', 'BER', 180,  6,  30, 'T1', 'planit_easy'],

            // ---- LISBOA (LIS) ----
            ['LIS', 'LHR', 150,  3,  65, 'T1', 'planit_easy'],
            ['LIS', 'CDG', 155,  3,  60, 'T1', 'planit_easy'],
            ['LIS', 'FRA', 170,  4,  55, 'T1', 'planit_easy'],
            ['LIS', 'AMS', 175,  4,  50, 'T1', 'planit_easy'],
            ['LIS', 'BRU', 165,  4,  50, 'T1', 'planit_easy'],
            ['LIS', 'BER', 185,  5,  45, 'T1', 'planit_easy'],
            ['LIS', 'FCO', 175,  5,  45, 'T1', 'planit_easy'],
            ['LIS', 'MUC', 185,  5,  40, 'T1', 'planit_easy'],

            // ---- MADEIRA (FNC) ----
            ['FNC', 'LHR', 185,  5,  40, 'T1', 'planit_easy'],
            ['FNC', 'CDG', 190,  5,  35, 'T1', 'planit_easy'],
            ['FNC', 'FRA', 200,  6,  30, 'T1', 'planit_easy'],
            ['FNC', 'AMS', 200,  6,  35, 'T1', 'planit_easy'],

            // ---- ATENAS (ATH) ----
            ['ATH', 'IST', 145,  3,  55, 'T1', 'planit_easy'],
            ['ATH', 'CAI', 190,  4,  45, 'T1', 'planit_easy'],
            ['ATH', 'BER', 205,  5,  45, 'T1', 'planit_easy'],
            ['ATH', 'MUC', 195,  5,  50, 'T1', 'planit_easy'],
            ['ATH', 'CPH', 225,  6,  35, 'T1', 'planit_easy'],
            ['ATH', 'ZRH', 200,  5,  40, 'T1', 'planit_easy'],

            // ---- CONSTANTINA (CZL) ----
            ['CZL', 'CDG', 150,  3,  45, 'T1', 'planit_easy'],
            ['CZL', 'ORY', 145,  3,  50, 'T1', 'planit_easy'],
            ['CZL', 'MRS', 120,  2,  50, 'T1', 'planit_easy'],
            ['CZL', 'LYS', 130,  3,  45, 'T1', 'planit_easy'],

            // ---- TANGER (TNG) ----
            ['TNG', 'CDG', 145,  3,  55, 'T1', 'planit_easy'],
            ['TNG', 'LHR', 160,  4,  45, 'T1', 'planit_easy'],
            ['TNG', 'FRA', 160,  4,  45, 'T1', 'planit_easy'],
            ['TNG', 'AMS', 160,  4,  40, 'T1', 'planit_easy'],

            // ---- FEZ (FEZ) ----
            ['FEZ', 'CDG', 135,  3,  50, 'T1', 'planit_easy'],
            ['FEZ', 'ORY', 130,  3,  55, 'T1', 'planit_easy'],
            ['FEZ', 'LYS', 125,  3,  45, 'T1', 'planit_easy'],
            ['FEZ', 'FRA', 155,  4,  40, 'T1', 'planit_easy'],
            ['FEZ', 'LHR', 160,  4,  40, 'T1', 'planit_easy'],

            // ---- NADOR (NDR) ----
            ['NDR', 'CDG', 130,  3,  50, 'T1', 'planit_easy'],
            ['NDR', 'ORY', 125,  2,  55, 'T1', 'planit_easy'],
            ['NDR', 'LYS', 120,  2,  50, 'T1', 'planit_easy'],
            ['NDR', 'FRA', 150,  4,  40, 'T1', 'planit_easy'],

            // ---- NUREMBERG (NUE) ----
            ['NUE', 'LHR', 165,  3,  45, 'T1', 'planit_easy'],
            ['NUE', 'CDG', 160,  3,  45, 'T1', 'planit_easy'],
            ['NUE', 'AMS', 150,  3,  45, 'T1', 'planit_easy'],
            ['NUE', 'ZRH', 105,  2,  50, 'T1', 'planit_easy'],
            ['NUE', 'VIE', 130,  2,  50, 'T1', 'planit_easy'],
            ['NUE', 'FCO', 160,  4,  40, 'T1', 'planit_easy'],
            ['NUE', 'MXP', 150,  3,  40, 'T1', 'planit_easy'],
            ['NUE', 'PRG', 120,  2,  45, 'T1', 'planit_easy'],
            ['NUE', 'BUD', 130,  2,  40, 'T1', 'planit_easy'],
            ['NUE', 'BRU', 155,  3,  40, 'T1', 'planit_easy'],

            // ---- MUNICH (MUC) ----
            ['MUC', 'LHR', 175,  3,  60, 'T1', 'planit_easy'],
            ['MUC', 'CDG', 165,  3,  60, 'T1', 'planit_easy'],
            ['MUC', 'AMS', 160,  3,  55, 'T1', 'planit_easy'],
            ['MUC', 'FCO', 155,  3,  55, 'T1', 'planit_easy'],
            ['MUC', 'ATH', 180,  4,  50, 'T1', 'planit_easy'],
            ['MUC', 'IST', 195,  5,  50, 'T1', 'planit_easy'],
            ['MUC', 'VIE', 110,  1,  65, 'T1', 'planit_easy'],
            ['MUC', 'PRG', 115,  1,  60, 'T1', 'planit_easy'],
            ['MUC', 'ZRH', 110,  1,  60, 'T1', 'planit_easy'],
            ['MUC', 'BRU', 165,  3,  50, 'T1', 'planit_easy'],
            ['MUC', 'CPH', 180,  4,  45, 'T1', 'planit_easy'],
            ['MUC', 'ARN', 190,  5,  40, 'T1', 'planit_easy'],
            ['MUC', 'OSL', 185,  4,  40, 'T1', 'planit_easy'],
            ['MUC', 'BER', 130,  2,  60, 'T1', 'planit_easy'],
            ['MUC', 'HAM', 140,  2,  55, 'T1', 'planit_easy'],
            ['MUC', 'FRA', 120,  1,  65, 'T1', 'planit_easy'],

            // ---- DUSSELDORF (DUS) ----
            ['DUS', 'LHR', 145,  2,  65, 'T1', 'planit_easy'],
            ['DUS', 'CDG', 150,  2,  60, 'T1', 'planit_easy'],
            ['DUS', 'FCO', 175,  4,  50, 'T1', 'planit_easy'],
            ['DUS', 'MXP', 165,  3,  50, 'T1', 'planit_easy'],
            ['DUS', 'ATH', 205,  5,  45, 'T1', 'planit_easy'],
            ['DUS', 'IST', 215,  5,  45, 'T1', 'planit_easy'],
            ['DUS', 'ZRH', 130,  2,  55, 'T1', 'planit_easy'],
            ['DUS', 'VIE', 155,  3,  50, 'T1', 'planit_easy'],
            ['DUS', 'FRA', 115,  1,  70, 'T1', 'planit_easy'],
            ['DUS', 'MUC', 125,  1,  65, 'T1', 'planit_easy'],
            ['DUS', 'BER', 130,  2,  60, 'T1', 'planit_easy'],
            ['DUS', 'HAM', 110,  1,  65, 'T1', 'planit_easy'],

            // ---- HANOVER (HAJ) ----
            ['HAJ', 'LHR', 155,  3,  50, 'T1', 'planit_easy'],
            ['HAJ', 'CDG', 155,  3,  50, 'T1', 'planit_easy'],
            ['HAJ', 'AMS', 130,  2,  55, 'T1', 'planit_easy'],
            ['HAJ', 'FCO', 175,  4,  40, 'T1', 'planit_easy'],
            ['HAJ', 'ZRH', 130,  2,  50, 'T1', 'planit_easy'],
            ['HAJ', 'VIE', 150,  3,  45, 'T1', 'planit_easy'],
            ['HAJ', 'FRA', 110,  1,  65, 'T1', 'planit_easy'],
            ['HAJ', 'MUC', 120,  1,  60, 'T1', 'planit_easy'],
            ['HAJ', 'BER', 110,  1,  65, 'T1', 'planit_easy'],
            ['HAJ', 'HAM',  90,  1,  70, 'T1', 'planit_easy'],

            // ---- HAMBURGO (HAM) ----
            ['HAM', 'CDG', 155,  3,  55, 'T1', 'planit_easy'],
            ['HAM', 'FCO', 180,  4,  50, 'T1', 'planit_easy'],
            ['HAM', 'MXP', 170,  4,  50, 'T1', 'planit_easy'],
            ['HAM', 'ZRH', 140,  2,  55, 'T1', 'planit_easy'],
            ['HAM', 'VIE', 155,  3,  50, 'T1', 'planit_easy'],
            ['HAM', 'CPH', 105,  1,  70, 'T1', 'planit_easy'],
            ['HAM', 'ARN', 155,  3,  45, 'T1', 'planit_easy'],
            ['HAM', 'OSL', 160,  3,  45, 'T1', 'planit_easy'],
            ['HAM', 'FRA', 115,  1,  70, 'T1', 'planit_easy'],
            ['HAM', 'MUC', 135,  2,  60, 'T1', 'planit_easy'],
            ['HAM', 'BER', 110,  1,  70, 'T1', 'planit_easy'],
            ['HAM', 'DUS', 110,  1,  65, 'T1', 'planit_easy'],

            // ---- BERLIN (BER) ----
            ['BER', 'FCO', 180,  4,  55, 'T1', 'planit_easy'],
            ['BER', 'MXP', 170,  3,  55, 'T1', 'planit_easy'],
            ['BER', 'ATH', 210,  5,  50, 'T1', 'planit_easy'],
            ['BER', 'IST', 215,  5,  50, 'T1', 'planit_easy'],
            ['BER', 'VIE', 145,  2,  60, 'T1', 'planit_easy'],
            ['BER', 'PRG', 130,  2,  60, 'T1', 'planit_easy'],
            ['BER', 'BUD', 145,  3,  55, 'T1', 'planit_easy'],
            ['BER', 'ZRH', 155,  3,  55, 'T1', 'planit_easy'],
            ['BER', 'CPH', 130,  2,  60, 'T1', 'planit_easy'],
            ['BER', 'ARN', 155,  3,  50, 'T1', 'planit_easy'],
            ['BER', 'OSL', 165,  4,  45, 'T1', 'planit_easy'],
            ['BER', 'FRA', 120,  1,  70, 'T1', 'planit_easy'],
            ['BER', 'MUC', 130,  2,  65, 'T1', 'planit_easy'],
            ['BER', 'HAM', 110,  1,  70, 'T1', 'planit_easy'],
            ['BER', 'DUS', 130,  2,  60, 'T1', 'planit_easy'],
            ['BER', 'STR', 125,  2,  60, 'T1', 'planit_easy'],
            ['BER', 'BRU', 165,  3,  50, 'T1', 'planit_easy'],

            // ---- STUTTGART (STR) ----
            ['STR', 'CDG', 145,  3,  45, 'T1', 'planit_easy'],
            ['STR', 'AMS', 155,  3,  45, 'T1', 'planit_easy'],
            ['STR', 'FCO', 160,  3,  45, 'T1', 'planit_easy'],
            ['STR', 'MXP', 140,  2,  50, 'T1', 'planit_easy'],
            ['STR', 'ZRH',  90,  1,  60, 'T1', 'planit_easy'],
            ['STR', 'VIE', 140,  2,  50, 'T1', 'planit_easy'],
            ['STR', 'FRA',  95,  1,  65, 'T1', 'planit_easy'],
            ['STR', 'MUC', 110,  1,  60, 'T1', 'planit_easy'],

            // ---- DUBROVNIK (DBV) ----
            ['DBV', 'LHR', 205,  5,  45, 'T1', 'planit_easy'],
            ['DBV', 'CDG', 200,  5,  45, 'T1', 'planit_easy'],
            ['DBV', 'FRA', 195,  4,  40, 'T1', 'planit_easy'],
            ['DBV', 'AMS', 205,  5,  35, 'T1', 'planit_easy'],
            ['DBV', 'VIE', 170,  3,  45, 'T1', 'planit_easy'],
            ['DBV', 'BER', 195,  5,  35, 'T1', 'planit_easy'],
            ['DBV', 'MUC', 175,  4,  40, 'T1', 'planit_easy'],
            ['DBV', 'ZRH', 180,  4,  35, 'T1', 'planit_easy'],

            // ---- SPLIT (SPU) ----
            ['SPU', 'LHR', 200,  5,  45, 'T1', 'planit_easy'],
            ['SPU', 'CDG', 195,  4,  45, 'T1', 'planit_easy'],
            ['SPU', 'FRA', 185,  4,  40, 'T1', 'planit_easy'],
            ['SPU', 'AMS', 200,  5,  35, 'T1', 'planit_easy'],
            ['SPU', 'VIE', 165,  3,  50, 'T1', 'planit_easy'],
            ['SPU', 'BER', 190,  4,  35, 'T1', 'planit_easy'],
            ['SPU', 'MUC', 170,  3,  40, 'T1', 'planit_easy'],

            // ---- ESTOCOLMO (ARN) ----
            ['ARN', 'BER', 165,  4,  50, 'T1', 'planit_easy'],
            ['ARN', 'MUC', 185,  5,  45, 'T1', 'planit_easy'],
            ['ARN', 'VIE', 190,  5,  40, 'T1', 'planit_easy'],
            ['ARN', 'PRG', 185,  5,  40, 'T1', 'planit_easy'],
            ['ARN', 'BUD', 195,  6,  35, 'T1', 'planit_easy'],
            ['ARN', 'CPH', 115,  1,  65, 'T1', 'planit_easy'],
            ['ARN', 'OSL', 120,  1,  60, 'T1', 'planit_easy'],
            ['ARN', 'HER', 230,  7,  30, 'T1', 'planit_easy'],

            // ---- GOTEMBURGO (GOT) ----
            ['GOT', 'CDG', 185,  4,  35, 'T1', 'planit_easy'],
            ['GOT', 'FRA', 175,  4,  35, 'T1', 'planit_easy'],
            ['GOT', 'AMS', 165,  3,  40, 'T1', 'planit_easy'],
            ['GOT', 'BER', 155,  3,  40, 'T1', 'planit_easy'],
            ['GOT', 'CPH', 100,  1,  55, 'T1', 'planit_easy'],
            ['GOT', 'ARN',  85,  1,  60, 'T1', 'planit_easy'],
            ['GOT', 'OSL', 110,  2,  50, 'T1', 'planit_easy'],

            // ---- ZURICH (ZRH) ----
            ['ZRH', 'LHR', 145,  2,  65, 'T1', 'planit_easy'],
            ['ZRH', 'CDG', 130,  2,  65, 'T1', 'planit_easy'],
            ['ZRH', 'AMS', 140,  2,  60, 'T1', 'planit_easy'],
            ['ZRH', 'FCO', 150,  3,  55, 'T1', 'planit_easy'],
            ['ZRH', 'MXP', 120,  1,  65, 'T1', 'planit_easy'],
            ['ZRH', 'ATH', 200,  5,  45, 'T1', 'planit_easy'],
            ['ZRH', 'IST', 220,  5,  45, 'T1', 'planit_easy'],
            ['ZRH', 'VIE', 125,  2,  60, 'T1', 'planit_easy'],
            ['ZRH', 'BER', 150,  3,  55, 'T1', 'planit_easy'],
            ['ZRH', 'MUC', 110,  1,  65, 'T1', 'planit_easy'],
            ['ZRH', 'BRU', 135,  2,  55, 'T1', 'planit_easy'],

            // ---- BASILEA (BSL) ----
            ['BSL', 'LHR', 150,  2,  50, 'T1', 'planit_easy'],
            ['BSL', 'CDG', 130,  2,  55, 'T1', 'planit_easy'],
            ['BSL', 'AMS', 140,  2,  50, 'T1', 'planit_easy'],
            ['BSL', 'FCO', 155,  3,  45, 'T1', 'planit_easy'],
            ['BSL', 'MXP', 115,  1,  60, 'T1', 'planit_easy'],
            ['BSL', 'VIE', 140,  3,  50, 'T1', 'planit_easy'],
            ['BSL', 'MUC', 120,  2,  55, 'T1', 'planit_easy'],

            // ---- GINEBRA (GVA) ----
            ['GVA', 'LHR', 145,  2,  60, 'T1', 'planit_easy'],
            ['GVA', 'CDG', 125,  2,  65, 'T1', 'planit_easy'],
            ['GVA', 'AMS', 145,  2,  55, 'T1', 'planit_easy'],
            ['GVA', 'FCO', 150,  3,  50, 'T1', 'planit_easy'],
            ['GVA', 'MXP', 110,  1,  65, 'T1', 'planit_easy'],
            ['GVA', 'BRU', 130,  2,  55, 'T1', 'planit_easy'],
            ['GVA', 'BER', 155,  3,  50, 'T1', 'planit_easy'],
            ['GVA', 'VIE', 145,  3,  50, 'T1', 'planit_easy'],

            // ---- COPENHAGUE (CPH) ----
            ['CPH', 'LHR', 160,  3,  60, 'T1', 'planit_easy'],
            ['CPH', 'BER', 130,  2,  60, 'T1', 'planit_easy'],
            ['CPH', 'MUC', 175,  4,  50, 'T1', 'planit_easy'],
            ['CPH', 'FCO', 195,  5,  45, 'T1', 'planit_easy'],
            ['CPH', 'VIE', 175,  4,  50, 'T1', 'planit_easy'],
            ['CPH', 'ARN', 110,  1,  65, 'T1', 'planit_easy'],
            ['CPH', 'OSL', 115,  1,  65, 'T1', 'planit_easy'],
            ['CPH', 'GOT', 100,  1,  60, 'T1', 'planit_easy'],
            ['CPH', 'BLL',  65,  1,  70, 'T1', 'planit_easy'],
            ['CPH', 'ZRH', 165,  3,  50, 'T1', 'planit_easy'],
            ['CPH', 'IST', 230,  6,  40, 'T1', 'planit_easy'],

            // ---- OSLO (OSL) ----
            ['OSL', 'BER', 165,  4,  50, 'T1', 'planit_easy'],
            ['OSL', 'MUC', 185,  5,  45, 'T1', 'planit_easy'],
            ['OSL', 'CPH', 115,  1,  65, 'T1', 'planit_easy'],
            ['OSL', 'ARN', 120,  1,  60, 'T1', 'planit_easy'],
            ['OSL', 'BGO',  85,  1,  70, 'T1', 'planit_easy'],
            ['OSL', 'SVG',  90,  1,  65, 'T1', 'planit_easy'],
            ['OSL', 'ZRH', 185,  4,  40, 'T1', 'planit_easy'],
            ['OSL', 'VIE', 195,  5,  40, 'T1', 'planit_easy'],
            ['OSL', 'FCO', 210,  5,  40, 'T1', 'planit_easy'],

            // ---- BERGEN (BGO) ----
            ['BGO', 'AMS', 185,  4,  35, 'T1', 'planit_easy'],
            ['BGO', 'BER', 185,  4,  35, 'T1', 'planit_easy'],
            ['BGO', 'OSL',  85,  1,  70, 'T1', 'planit_easy'],
            ['BGO', 'CPH', 130,  2,  50, 'T1', 'planit_easy'],
            ['BGO', 'ARN', 120,  2,  55, 'T1', 'planit_easy'],

            // ---- STAVANGER (SVG) ----
            ['SVG', 'LHR', 165,  4,  40, 'T1', 'planit_easy'],
            ['SVG', 'CDG', 175,  4,  35, 'T1', 'planit_easy'],
            ['SVG', 'AMS', 160,  3,  40, 'T1', 'planit_easy'],
            ['SVG', 'OSL',  90,  1,  65, 'T1', 'planit_easy'],
            ['SVG', 'CPH', 130,  2,  50, 'T1', 'planit_easy'],

            // ---- TROMSO (TOS) ----
            ['TOS', 'LHR', 220,  6,  25, 'T1', 'planit_easy'],
            ['TOS', 'OSL',  95,  1,  60, 'T1', 'planit_easy'],
            ['TOS', 'ARN', 110,  2,  55, 'T1', 'planit_easy'],
            ['TOS', 'CPH', 145,  3,  45, 'T1', 'planit_easy'],

            // ---- ESTAMBUL (IST) ----
            ['IST', 'FCO', 215,  5,  55, 'T1', 'planit_easy'],
            ['IST', 'MXP', 210,  5,  50, 'T1', 'planit_easy'],
            ['IST', 'ATH', 145,  3,  60, 'T1', 'planit_easy'],
            ['IST', 'CAI', 200,  4,  55, 'T1', 'planit_easy'],
            ['IST', 'BER', 220,  5,  50, 'T1', 'planit_easy'],
            ['IST', 'MUC', 210,  4,  50, 'T1', 'planit_easy'],
            ['IST', 'ZRH', 215,  5,  45, 'T1', 'planit_easy'],
            ['IST', 'CPH', 240,  6,  40, 'T1', 'planit_easy'],
            ['IST', 'ARN', 255,  7,  35, 'T1', 'planit_easy'],

            // ---- DUBLIN (DUB) ----
            ['DUB', 'CDG', 155,  3,  55, 'T1', 'planit_easy'],
            ['DUB', 'FRA', 165,  3,  50, 'T1', 'planit_easy'],
            ['DUB', 'AMS', 155,  3,  55, 'T1', 'planit_easy'],
            ['DUB', 'BRU', 145,  2,  55, 'T1', 'planit_easy'],
            ['DUB', 'BER', 175,  4,  45, 'T1', 'planit_easy'],
            ['DUB', 'FCO', 185,  4,  45, 'T1', 'planit_easy'],
            ['DUB', 'MXP', 180,  4,  45, 'T1', 'planit_easy'],

            // ---- VIENA (VIE) ----
            ['VIE', 'LHR', 185,  4,  55, 'T1', 'planit_easy'],
            ['VIE', 'CDG', 180,  4,  55, 'T1', 'planit_easy'],
            ['VIE', 'AMS', 175,  3,  55, 'T1', 'planit_easy'],
            ['VIE', 'FCO', 160,  3,  55, 'T1', 'planit_easy'],
            ['VIE', 'MXP', 150,  2,  60, 'T1', 'planit_easy'],
            ['VIE', 'BER', 145,  2,  60, 'T1', 'planit_easy'],
            ['VIE', 'MUC', 110,  1,  65, 'T1', 'planit_easy'],
            ['VIE', 'ZRH', 125,  2,  60, 'T1', 'planit_easy'],
            ['VIE', 'PRG', 105,  1,  65, 'T1', 'planit_easy'],
            ['VIE', 'BUD',  90,  1,  70, 'T1', 'planit_easy'],
            ['VIE', 'ARN', 200,  5,  45, 'T1', 'planit_easy'],
            ['VIE', 'CPH', 185,  4,  50, 'T1', 'planit_easy'],

            // ---- PRAGA (PRG) ----
            ['PRG', 'LHR', 175,  4,  50, 'T1', 'planit_easy'],
            ['PRG', 'CDG', 170,  3,  50, 'T1', 'planit_easy'],
            ['PRG', 'FRA', 145,  2,  55, 'T1', 'planit_easy'],
            ['PRG', 'AMS', 160,  3,  50, 'T1', 'planit_easy'],
            ['PRG', 'FCO', 175,  4,  45, 'T1', 'planit_easy'],
            ['PRG', 'ATH', 210,  5,  40, 'T1', 'planit_easy'],
            ['PRG', 'ZRH', 145,  2,  55, 'T1', 'planit_easy'],
            ['PRG', 'VIE', 105,  1,  65, 'T1', 'planit_easy'],
            ['PRG', 'BUD', 120,  2,  60, 'T1', 'planit_easy'],
            ['PRG', 'BRU', 165,  3,  45, 'T1', 'planit_easy'],

            // ---- BUDAPEST (BUD) ----
            ['BUD', 'LHR', 185,  4,  50, 'T1', 'planit_easy'],
            ['BUD', 'CDG', 180,  4,  50, 'T1', 'planit_easy'],
            ['BUD', 'FRA', 155,  3,  55, 'T1', 'planit_easy'],
            ['BUD', 'AMS', 170,  3,  50, 'T1', 'planit_easy'],
            ['BUD', 'FCO', 175,  4,  45, 'T1', 'planit_easy'],
            ['BUD', 'VIE',  90,  1,  70, 'T1', 'planit_easy'],
            ['BUD', 'PRG', 120,  2,  60, 'T1', 'planit_easy'],
            ['BUD', 'ATH', 200,  5,  40, 'T1', 'planit_easy'],
            ['BUD', 'ZRH', 155,  3,  50, 'T1', 'planit_easy'],
            ['BUD', 'BRU', 175,  4,  45, 'T1', 'planit_easy'],

            // ---- BRUSELAS (BRU) ----
            ['BRU', 'LHR', 130,  2,  65, 'T1', 'planit_easy'],
            ['BRU', 'CDG', 110,  1,  70, 'T1', 'planit_easy'],
            ['BRU', 'FRA', 145,  2,  60, 'T1', 'planit_easy'],
            ['BRU', 'AMS', 110,  1,  70, 'T1', 'planit_easy'],
            ['BRU', 'FCO', 175,  4,  50, 'T1', 'planit_easy'],
            ['BRU', 'MXP', 160,  3,  50, 'T1', 'planit_easy'],
            ['BRU', 'ATH', 215,  5,  45, 'T1', 'planit_easy'],
            ['BRU', 'ZRH', 135,  2,  55, 'T1', 'planit_easy'],
            ['BRU', 'VIE', 170,  3,  50, 'T1', 'planit_easy'],
            ['BRU', 'BER', 165,  3,  50, 'T1', 'planit_easy'],
            ['BRU', 'CPH', 160,  3,  55, 'T1', 'planit_easy'],

            // ---- EL CAIRO (CAI) ----
            ['CAI', 'ATH', 190,  4,  50, 'T1', 'planit_easy'],
            ['CAI', 'IST', 200,  4,  55, 'T1', 'planit_easy'],
            ['CAI', 'AMS', 280,  6,  40, 'T1', 'planit_easy'],
            ['CAI', 'FCO', 250,  5,  45, 'T1', 'planit_easy'],
            ['CAI', 'BER', 295,  7,  35, 'T1', 'planit_easy'],
            ['CAI', 'VIE', 265,  6,  40, 'T1', 'planit_easy'],

            // ---- MALTA (MLA) ----
            ['MLA', 'LHR', 175,  4,  45, 'T1', 'planit_easy'],
            ['MLA', 'CDG', 170,  4,  45, 'T1', 'planit_easy'],
            ['MLA', 'FRA', 175,  4,  40, 'T1', 'planit_easy'],
            ['MLA', 'AMS', 180,  4,  40, 'T1', 'planit_easy'],
            ['MLA', 'FCO', 145,  3,  50, 'T1', 'planit_easy'],
            ['MLA', 'ATH', 185,  4,  40, 'T1', 'planit_easy'],
            ['MLA', 'VIE', 185,  4,  35, 'T1', 'planit_easy'],

            // ---- REIKIAVIK (KEF) ----
            ['KEF', 'LHR', 215,  5,  40, 'T1', 'planit_easy'],
            ['KEF', 'CDG', 225,  6,  35, 'T1', 'planit_easy'],
            ['KEF', 'FRA', 230,  6,  30, 'T1', 'planit_easy'],
            ['KEF', 'AMS', 220,  5,  35, 'T1', 'planit_easy'],
            ['KEF', 'CPH', 220,  5,  35, 'T1', 'planit_easy'],
            ['KEF', 'ARN', 215,  5,  35, 'T1', 'planit_easy'],
            ['KEF', 'OSL', 210,  5,  35, 'T1', 'planit_easy'],

            // ---- TIRANA (TIA) ----
            ['TIA', 'LHR', 195,  5,  35, 'T1', 'planit_easy'],
            ['TIA', 'CDG', 195,  5,  35, 'T1', 'planit_easy'],
            ['TIA', 'FRA', 190,  4,  35, 'T1', 'planit_easy'],
            ['TIA', 'AMS', 195,  5,  30, 'T1', 'planit_easy'],
            ['TIA', 'FCO', 160,  3,  45, 'T1', 'planit_easy'],
            ['TIA', 'VIE', 170,  3,  45, 'T1', 'planit_easy'],
            ['TIA', 'IST', 185,  4,  40, 'T1', 'planit_easy'],
            ['TIA', 'BRU', 200,  5,  30, 'T1', 'planit_easy'],

            // ---- BUCAREST (OTP) ----
            ['OTP', 'FRA', 185,  4,  45, 'T1', 'planit_easy'],
            ['OTP', 'AMS', 190,  5,  40, 'T1', 'planit_easy'],
            ['OTP', 'VIE', 160,  3,  55, 'T1', 'planit_easy'],
            ['OTP', 'BER', 185,  4,  40, 'T1', 'planit_easy'],
            ['OTP', 'MUC', 185,  4,  40, 'T1', 'planit_easy'],
            ['OTP', 'ATH', 190,  4,  45, 'T1', 'planit_easy'],
            ['OTP', 'IST', 195,  4,  50, 'T1', 'planit_easy'],
            ['OTP', 'BRU', 195,  5,  40, 'T1', 'planit_easy'],
            ['OTP', 'ZRH', 195,  5,  35, 'T1', 'planit_easy'],

            // ---- CLUJ-NAPOCA (CLJ) ----
            ['CLJ', 'FRA', 190,  4,  35, 'T1', 'planit_easy'],
            ['CLJ', 'AMS', 195,  5,  30, 'T1', 'planit_easy'],
            ['CLJ', 'VIE', 155,  3,  45, 'T1', 'planit_easy'],
            ['CLJ', 'BER', 190,  5,  30, 'T1', 'planit_easy'],
            ['CLJ', 'MUC', 180,  4,  35, 'T1', 'planit_easy'],
            ['CLJ', 'BRU', 195,  5,  30, 'T1', 'planit_easy'],

            // ---- TIMI?OARA (TSR) ----
            ['TSR', 'FRA', 185,  4,  35, 'T1', 'planit_easy'],
            ['TSR', 'AMS', 190,  5,  30, 'T1', 'planit_easy'],
            ['TSR', 'VIE', 150,  3,  45, 'T1', 'planit_easy'],
            ['TSR', 'MUC', 180,  4,  35, 'T1', 'planit_easy'],
            ['TSR', 'BER', 185,  4,  30, 'T1', 'planit_easy'],

            // ---- IASI (IAS) ----
            ['IAS', 'FRA', 190,  4,  30, 'T1', 'planit_easy'],
            ['IAS', 'AMS', 195,  5,  25, 'T1', 'planit_easy'],
            ['IAS', 'VIE', 160,  3,  40, 'T1', 'planit_easy'],
            ['IAS', 'MUC', 185,  4,  30, 'T1', 'planit_easy'],
            ['IAS', 'BRU', 195,  5,  25, 'T1', 'planit_easy'],
        ];

        // Codigos IATA de ciudades fuera del espacio Schengen
        $noSchengen = ['DUB', 'LHR', 'LGW', 'STN', 'MAN', 'EDI', // Irlanda y Reino Unido
                       'IST', 'SAW',                               // Turquía
                       'CAI', 'HRG', 'SSH',                       // Egipto
                       'CMN', 'RAK', 'TNG', 'FEZ', 'NDR', 'CZL', // Marruecos
                       'TIA',                                       // Albania
                       'BEG', 'PRN',                               // Serbia, Kosovo
                       'SKP',                                       // Macedonia del Norte
                       'SJJ', 'TGD', 'DBV',                       // Bosnia, Montenegro
                      ];

        $minutosValidos = [0, 15, 30, 45];
        $vuelos = [];
        $codigoContador = 1000;

        // Rutas principales (las que usa ReservasSeeder + enlace)
        // Para estas se generan 2 vuelos extra en abril y 2 en mayo
        $rutasPrincipales = [
            'MAD-FCO', 'MAD-CDG', 'MAD-BER', 'MAD-LIS', 'MAD-LHR', 'MAD-AMS', 'MAD-BRU',
            'BCN-FCO', 'BCN-CDG', 'BCN-BER', 'BCN-LIS', 'BCN-LHR', 'BCN-AMS', 'BCN-BRU',
            'VLC-FCO', 'VLC-CDG', 'VLC-BER', 'VLC-LIS', 'VLC-LHR', 'VLC-AMS', 'VLC-BRU',
            'SVQ-FCO', 'SVQ-CDG', 'SVQ-BER', 'SVQ-LIS', 'SVQ-LHR', 'SVQ-AMS', 'SVQ-BRU',
            'TFN-LHR', 'TFN-FRA', 'TFN-AMS', 'TFN-CDG',
            'LPA-FRA', 'LPA-LHR', 'LPA-AMS',
            'AMS-BRU', 'AMS-DUS', 'AMS-HAM', 'AMS-FRA', 'AMS-BER', 'AMS-MUC',
            'AMS-FCO', 'AMS-MXP', 'AMS-LIS', 'AMS-LHR', 'AMS-CPH', 'AMS-PRG',
            'AMS-VIE', 'AMS-BUD', 'AMS-ARN', 'AMS-OSL', 'AMS-ATH', 'AMS-IST',
        ];

        // Rutas con múltiples salidas el mismo día (3-4 horarios diferentes)
        $rutasConMultipleHorarios = [
            'AMS-BRU', 'BRU-AMS', 'AMS-DUS', 'DUS-AMS', 'AMS-HAM', 'HAM-AMS',
            'AMS-FRA', 'FRA-AMS', 'AMS-BER', 'BER-AMS',
        ];

        foreach ($pares as $par) {
            [$orig, $dest, $precio, $dias, $asientos, $terminal, $tarifa] = $par;

            if (!isset($ids[$orig]) || !isset($ids[$dest])) {
                continue;
            }

            $esSchengenIda    = !in_array($orig, $noSchengen) && !in_array($dest, $noSchengen);
            $esSchengenVuelta = !in_array($dest, $noSchengen) && !in_array($orig, $noSchengen);
            $claveRuta = $orig . '-' . $dest;
            $esPrincipal = in_array($claveRuta, $rutasPrincipales) || in_array($dest . '-' . $orig, $rutasPrincipales);

            // Vuelo original (1 ida + 1 vuelta) — para todas las rutas
            $horaIda = rand(6, 21);
            $minIda  = $minutosValidos[array_rand($minutosValidos)];
            $fechaIda = Carbon::parse('2027-01-01')->addDays($dias)->setTime($horaIda, $minIda, 0);
            $duracionIda = rand(90, 270);
            $fechaLlegadaIda = (clone $fechaIda)->addMinutes($duracionIda);

            do {
                $horaVuelta = rand(6, 21);
                $minVuelta  = $minutosValidos[array_rand($minutosValidos)];
            } while ($horaVuelta === $horaIda && $minVuelta === $minIda);
            $fechaVuelta = Carbon::parse('2027-01-01')->addDays($dias + 7)->setTime($horaVuelta, $minVuelta, 0);
            $duracionVuelta = rand(max(60, $duracionIda - 30), $duracionIda + 30);
            $fechaLlegadaVuelta = (clone $fechaVuelta)->addMinutes($duracionVuelta);

            $vuelos[] = [
                'codigo'              => 'PLT' . ($codigoContador++),
                'origen'              => $nombres[$orig],
                'destino'             => $nombres[$dest],
                'precio_base'         => $precio,
                'origen_ciudad_id'    => $ids[$orig],
                'destino_ciudad_id'   => $ids[$dest],
                'fecha_salida'        => $fechaIda,
                'fecha_llegada'       => $fechaLlegadaIda,
                'precio'              => $precio,
                'asientos_disponibles'=> 180,
                'activo'              => true,
                'es_schengen'         => $esSchengenIda,
                'terminal'            => $terminal,
                'tipo_tarifa'         => $tarifa,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            $precioVueltaBase = round($precio * (1 + (rand(-5, 10) / 100)), 2);
            $vuelos[] = [
                'codigo'              => 'PLT' . ($codigoContador++),
                'origen'              => $nombres[$dest],
                'destino'             => $nombres[$orig],
                'precio_base'         => $precioVueltaBase,
                'origen_ciudad_id'    => $ids[$dest],
                'destino_ciudad_id'   => $ids[$orig],
                'fecha_salida'        => $fechaVuelta,
                'fecha_llegada'       => $fechaLlegadaVuelta,
                'precio'              => $precioVueltaBase,
                'asientos_disponibles'=> 180,
                'activo'              => true,
                'es_schengen'         => $esSchengenVuelta,
                'terminal'            => $terminal,
                'tipo_tarifa'         => $tarifa,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            // Vuelos extra solo para rutas principales: 2 en abril + 2 en mayo
            if ($esPrincipal) {
                $mesesExtra = [
                    ['2027-04', 4, 2027],
                    ['2027-05', 5, 2027],
                ];

                $tieneMulriplesHorarios = in_array($claveRuta, $rutasConMultipleHorarios);

                foreach ($mesesExtra as [$claveMes, $numMes, $anio]) {
                    // Evitar generar vuelos extra el mismo dia que el vuelo original
                    $diasUsados = [];
                    if ($fechaIda->month === $numMes && $fechaIda->year === $anio) {
                        $diasUsados[] = $fechaIda->day;
                    }
                    if ($fechaVuelta->month === $numMes && $fechaVuelta->year === $anio) {
                        $diasUsados[] = $fechaVuelta->day;
                    }

                    // Si es ruta con múltiples horarios: 4 salidas el MISMO DÍA
                    // Si es ruta normal: 2 vuelos en días diferentes
                    $numVuelosExtra = $tieneMulriplesHorarios ? 1 : 2;

                    for ($v = 0; $v < $numVuelosExtra; $v++) {
                        do {
                            $diaRand = rand(1, 28);
                        } while (in_array($diaRand, $diasUsados));
                        $diasUsados[] = $diaRand;

                        // Para rutas con múltiples horarios: 4 salidas diferentes el mismo día
                        // Para rutas normales: 1 salida por ciclo
                        $horariosDelDia = $tieneMulriplesHorarios
                            ? [7, 10.5, 14, 18]  // 07:00, 10:30, 14:00, 18:00
                            : [rand(6, 21)];     // 1 horario aleatorio

                        foreach ($horariosDelDia as $hora) {
                            $fechaExtra = \Carbon\Carbon::create($anio, $numMes, $diaRand, floor($hora), ($hora - floor($hora)) * 60, 0);
                            // Solo futuro
                            if ($fechaExtra->lt(now())) {
                                continue;
                            }
                            $duracion = rand(90, 270);
                            $variacion = (rand(-8, 12) / 100);

                            // Ida
                            $precioExtraIda = round($precio * (1 + $variacion), 2);
                            $vuelos[] = [
                                'codigo'              => 'PLT' . ($codigoContador++),
                                'origen'              => $nombres[$orig],
                                'destino'             => $nombres[$dest],
                                'precio_base'         => $precioExtraIda,
                                'origen_ciudad_id'    => $ids[$orig],
                                'destino_ciudad_id'   => $ids[$dest],
                                'fecha_salida'        => $fechaExtra,
                                'fecha_llegada'       => (clone $fechaExtra)->addMinutes($duracion),
                                'precio'              => $precioExtraIda,
                                'asientos_disponibles'=> 180,
                                'activo'              => true,
                                'es_schengen'         => $esSchengenIda,
                                'terminal'            => $terminal,
                                'tipo_tarifa'         => $tarifa,
                                'created_at'          => now(),
                                'updated_at'          => now(),
                            ];

                            // Vuelta - solo para rutas sin múltiples horarios (para evitar duplicar)
                            if (!$tieneMulriplesHorarios || $hora === $horariosDelDia[0]) {
                                $fechaExtraVuelta = \Carbon\Carbon::create($anio, $numMes, $diaRand, rand(6, 21), $minutosValidos[array_rand($minutosValidos)], 0);
                                if ($fechaExtraVuelta->gte(now())) {
                                    $duracionV = rand(max(60, $duracion - 30), $duracion + 30);
                                    $precioExtraVuelta = round($precio * (1 + (rand(-5, 10) / 100)), 2);
                                    $vuelos[] = [
                                        'codigo'              => 'PLT' . ($codigoContador++),
                                        'origen'             => $nombres[$dest],
                                        'destino'            => $nombres[$orig],
                                        'precio_base'        => $precioExtraVuelta,
                                        'origen_ciudad_id'   => $ids[$dest],
                                        'destino_ciudad_id'  => $ids[$orig],
                                        'fecha_salida'       => $fechaExtraVuelta,
                                        'fecha_llegada'      => (clone $fechaExtraVuelta)->addMinutes($duracionV),
                                        'precio'             => $precioExtraVuelta,
                                        'asientos_disponibles' => 180,
                                        'activo'             => true,
                                        'es_schengen'        => $esSchengenVuelta,
                                        'terminal'           => $terminal,
                                        'tipo_tarifa'        => $tarifa,
                                        'created_at'         => now(),
                                        'updated_at'         => now(),
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        // Insertar en bloques para no sobrepasar limites de MySQL
        foreach (array_chunk($vuelos, 50) as $bloque) {
            DB::table('vuelos')->insert($bloque);
        }

        // Los asientos se generan bajo demanda en CheckinController::generarAsientosSiNoExisten()
        // No se crean aquí para ahorrar espacio en la base de datos

        // Ofertas de ejemplo sobre rutas destacadas
    }
}


