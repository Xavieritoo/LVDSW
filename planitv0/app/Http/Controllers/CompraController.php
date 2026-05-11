<?php

namespace App\Http\Controllers;

use App\Models\Equipaje;
use App\Models\Pago;
use App\Models\Pasajero;
use App\Models\Reserva;
use App\Models\ReservaPasajero;
use App\Models\Vuelo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompraController extends Controller
{
    public function resumen()
    {
        $reserva = session('reserva');

        if (!$reserva) {
            return redirect()->route('principal')
                ->with('error', 'Sesión expirada. Inicia la búsqueda de nuevo.');
        }

        $adultos   = $reserva['pasajeros']['adultos'];
        $menores   = $reserva['pasajeros']['menores'];
        $infantes  = $reserva['pasajeros']['infantes'];
        $equipajes = $reserva['equipajes'];
        $seleccion = $reserva['seleccion'];
        $tipoViaje = $reserva['tipo_viaje'];

        $precios = [20 => 120, 25 => 130, 30 => 140];

        $precioEquipajes  = 0;
        $resumenEquipajes = [];
        $nombresPasajeros = [];

        foreach ($adultos  as $i => $p) {
            $nomA = '';
            if (isset($p['nombre'])) {
                $nomA = $p['nombre'];
            }
            $apeA = '';
            if (isset($p['apellidos'])) {
                $apeA = $p['apellidos'];
            }
            $nombresPasajeros['adulto_'  . $i] = trim($nomA . ' ' . $apeA);
        }
        foreach ($menores  as $i => $p) {
            $nomM = '';
            if (isset($p['nombre'])) {
                $nomM = $p['nombre'];
            }
            $apeM = '';
            if (isset($p['apellidos'])) {
                $apeM = $p['apellidos'];
            }
            $nombresPasajeros['menor_'   . $i] = trim($nomM . ' ' . $apeM);
        }
        foreach ($infantes as $i => $p) {
            $nomI = '';
            if (isset($p['nombre'])) {
                $nomI = $p['nombre'];
            }
            $apeI = '';
            if (isset($p['apellidos'])) {
                $apeI = $p['apellidos'];
            }
            $nombresPasajeros['infante_' . $i] = trim($nomI . ' ' . $apeI);
        }

        foreach ($equipajes as $clave => $porTrayecto) {
            foreach ($porTrayecto as $trayecto => $pesos) {
                foreach ($pesos as $peso => $v) {
                    if (isset($precios[$peso])) {
                        $precioEquipajes += $precios[$peso];
                        $nombrePasajero = 'Pasajero';
                        if (isset($nombresPasajeros[$clave])) {
                            $nombrePasajero = $nombresPasajeros[$clave];
                        }
                        $resumenEquipajes[] = [
                            'pasajero' => $nombrePasajero,
                            'tramo'    => $trayecto,
                            'peso'     => (int) $peso,
                            'precio'   => $precios[$peso],
                        ];
                    }
                }
            }
        }

        $personasPagadas = count($adultos) + count($menores);

        // Precios calculados desde la BD para evitar manipulaci\u00f3n
        $vueloIdaResumen = Vuelo::findOrFail((int) $seleccion['vuelo_ida_id']);
        if ($vueloIdaResumen->precio_base > 0) {
            $precioBaseIdaR = (float) $vueloIdaResumen->precio_base;
        } else {
            $precioBaseIdaR = (float) $vueloIdaResumen->precio;
        }

        if ($seleccion['plan_ida'] === 'PLANIT COMFORT') {
            $base = $precioBaseIdaR + 70;
        } else {
            $base = $precioBaseIdaR;
        }

        if ($tipoViaje === 'ida_vuelta' && !empty($seleccion['vuelo_vuelta_id'])) {
            $vueloVueltaResumen = Vuelo::findOrFail((int) $seleccion['vuelo_vuelta_id']);
            if ($vueloVueltaResumen->precio_base > 0) {
                $precioBaseVueltaR = (float) $vueloVueltaResumen->precio_base;
            } else {
                $precioBaseVueltaR = (float) $vueloVueltaResumen->precio;
            }

            if ($seleccion['plan_vuelta'] === 'PLANIT COMFORT') {
                $base += $precioBaseVueltaR + 70;
            } else {
                $base += $precioBaseVueltaR;
            }
        }
        $precioBase  = $base * $personasPagadas;
        $totalFinal  = $precioBase + $precioEquipajes;

        return view('resumen', [
            'tipoViaje'       => $tipoViaje,
            'seleccion'       => $seleccion,
            'correoContacto'  => $reserva['correo_contacto'],
            'detalleVuelosSeleccionados' => $this->obtenerDetalleVuelosSeleccionados($seleccion, $tipoViaje),
            'numAdultos'      => count($adultos),
            'numMenores'      => count($menores),
            'numInfantes'     => count($infantes),
            'resumenEquipajes' => $resumenEquipajes,
            'precioBase'      => $precioBase,
            'precioEquipajes' => $precioEquipajes,
            'totalFinal'      => $totalFinal,
        ]);
    }

    public function pagar(Request $request)
    {
        $isJson = $request->wantsJson();

        $reserva = session('reserva');

        if (!$reserva) {
            if ($isJson) {
                return response()->json(['success' => false, 'message' => 'Sesión expirada. Inicia la búsqueda de nuevo.'], 422);
            }
            return redirect()->route('principal')
                ->with('error', 'Sesión expirada. Inicia la búsqueda de nuevo.');
        }

        $adultos   = $reserva['pasajeros']['adultos'];
        $menores   = $reserva['pasajeros']['menores'];
        $infantes  = $reserva['pasajeros']['infantes'];
        $equipajes = $reserva['equipajes'];
        $seleccion = $reserva['seleccion'];
        $tipoViaje = $reserva['tipo_viaje'];
        $emailContacto = '';
        if (isset($reserva['correo_contacto'])) {
            $emailContacto = (string) $reserva['correo_contacto'];
        }
        $usuarioAutenticado = Auth::check();
        $vueloIda = Vuelo::with(['ciudadOrigen', 'ciudadDestino'])->findOrFail((int) $seleccion['vuelo_ida_id']);
        $vueloVuelta = null;
        if ($tipoViaje === 'ida_vuelta' && !empty($seleccion['vuelo_vuelta_id'])) {
            $vueloVuelta = Vuelo::with(['ciudadOrigen', 'ciudadDestino'])->findOrFail((int) $seleccion['vuelo_vuelta_id']);
        }

        // Precios calculados desde la BD (no desde sesión) para evitar manipulación de precios
        if ($vueloIda->precio_base > 0) {
            $precioBaseIda = (float) $vueloIda->precio_base;
        } else {
            $precioBaseIda = (float) $vueloIda->precio;
        }

        if ($seleccion['plan_ida'] === 'PLANIT COMFORT') {
            $precioRealIda = $precioBaseIda + 70;
        } else {
            $precioRealIda = $precioBaseIda;
        }

        $precioRealVuelta = 0;
        if ($vueloVuelta) {
            if ($vueloVuelta->precio_base > 0) {
                $precioBaseVuelta = (float) $vueloVuelta->precio_base;
            } else {
                $precioBaseVuelta = (float) $vueloVuelta->precio;
            }

            if ($seleccion['plan_vuelta'] === 'PLANIT COMFORT') {
                $precioRealVuelta = $precioBaseVuelta + 70;
            } else {
                $precioRealVuelta = $precioBaseVuelta;
            }
        }

        $precios = [20 => 120, 25 => 130, 30 => 140];

        $totalEquipajes = 0;
        foreach ($equipajes as $pasajero) {
            foreach ($pasajero as $trayecto) {
                foreach ($trayecto as $peso => $v) {
                    if (isset($precios[$peso])) {
                        $totalEquipajes += $precios[$peso];
                    }
                }
            }
        }

        $personasPagadas = count($adultos) + count($menores);
        $base = $precioRealIda;
        if ($tipoViaje === 'ida_vuelta') {
            $base += $precioRealVuelta;
        }
        if ($seleccion['plan_ida'] === 'PLANIT COMFORT') {
            $planIda = 'comfort';
        } else {
            $planIda = 'easy';
        }
        $planVuelta = null;
        if ($tipoViaje === 'ida_vuelta') {
            if ($seleccion['plan_vuelta'] === 'PLANIT COMFORT') {
                $planVuelta = 'comfort';
            } else {
                $planVuelta = 'easy';
            }
        }

        try {
            $localizadoresGenerados = [];

            DB::transaction(function () use ($seleccion, $tipoViaje, $adultos, $menores, $infantes, $equipajes, $precios, $planIda, $planVuelta, $emailContacto, $usuarioAutenticado, $vueloIda, $vueloVuelta, $precioRealIda, $precioRealVuelta, &$localizadoresGenerados) {

            // Calcular costes de equipaje por trayecto
            $costoEquipajesIda    = 0;
            $costoEquipajesVuelta = 0;
            foreach ($equipajes as $data) {
                foreach ((array) $data as $trayecto => $pesos) {
                    foreach (array_keys((array) $pesos) as $peso) {
                        if (isset($precios[$peso])) {
                            if ($trayecto === 'vuelta') {
                                $costoEquipajesVuelta += $precios[$peso];
                            } else {
                                $costoEquipajesIda += $precios[$peso];
                            }
                        }
                    }
                }
            }

            $personasPagadas = count($adultos) + count($menores);
            $totalIda        = ($precioRealIda * $personasPagadas) + $costoEquipajesIda;
            $origenIda = $vueloIda->origen;
            if (!$origenIda) {
                $ciudadOrigenIda = $vueloIda->ciudadOrigen;
                $origenIda = '';
                if ($ciudadOrigenIda) {
                    $origenIda = $ciudadOrigenIda->nombre;
                }
            }
            $destinoIda = $vueloIda->destino;
            if (!$destinoIda) {
                $ciudadDestinoIda = $vueloIda->ciudadDestino;
                $destinoIda = '';
                if ($ciudadDestinoIda) {
                    $destinoIda = $ciudadDestinoIda->nombre;
                }
            }

            $fechaSalidaIda = $vueloIda->hora_salida_programada;
            if (!$fechaSalidaIda) {
                $fechaSalidaIda = $vueloIda->fecha_salida;
            }
            $fechaLlegadaIda = $vueloIda->fecha_llegada;
            if (!$fechaLlegadaIda) {
                $fechaLlegadaIda = $vueloIda->hora_llegada_programada;
            }

            $climaOpciones = ['Soleado', 'Parcialmente nublado', 'Despejado', 'Lluvia ligera', 'Nublado', 'Viento y fresco'];
            $meteorologiaIda = $climaOpciones[array_rand($climaOpciones)];

            $equipajeContadoresIda = [];
            foreach ($equipajes as $data) {
                foreach ((array) $data as $trayecto => $pesos) {
                    if ($trayecto !== 'vuelta') {
                        foreach (array_keys((array) $pesos) as $peso) {
                            if (!isset($equipajeContadoresIda[(int) $peso])) {
                                $equipajeContadoresIda[(int) $peso] = 0;
                            }
                            $equipajeContadoresIda[(int) $peso] = $equipajeContadoresIda[(int) $peso] + 1;
                        }
                    }
                }
            }
            $equipajePartsIda = [];
            foreach ($equipajeContadoresIda as $kg => $cnt) {
                $equipajePartsIda[] = $cnt . 'x ' . $kg . ' kg';
            }
            // Equipaje incluido según plan
            $incluidoIda = 'Mano: 1x 10 kg';
            if ($planIda === 'comfort') {
                $incluidoIda .= ' + Facturado: 1x 23 kg';
            }
            $equipajeResumenIda = $incluidoIda;
            if (!empty($equipajePartsIda)) {
                $equipajeResumenIda .= ' | Extra: ' . implode(', ', $equipajePartsIda);
            }

            $enlazadaEnIda = null;
            if ($usuarioAutenticado) {
                $enlazadaEnIda = now();
            }

            $reservaIda = Reserva::create([
                'user_id'              => Auth::id(),
                'enlazada_en'          => $enlazadaEnIda,
                'localizador'          => strtoupper(Str::random(8)),
                'vuelo_id'             => $seleccion['vuelo_ida_id'],
                'vuelo_vuelta_id'      => null,
                'plan_tarifa'          => 'planit_' . $planIda,
                'precio_total'         => $totalIda,
                'estado'               => 'confirmada',
                'email_contacto'       => $emailContacto,
                'origen'               => $origenIda,
                'destino'              => $destinoIda,
                'fecha_salida'         => $fechaSalidaIda,
                'fecha_llegada'        => $fechaLlegadaIda,
                'equipaje_resumen'     => $equipajeResumenIda,
                'asientos_resumen'     => 'Se asignan tras el check-in online',
                'meteorologia_resumen' => $meteorologiaIda,
            ]);

            $reservaVuelta = null;
            $totalVuelta   = 0;
            if ($tipoViaje === 'ida_vuelta' && $vueloVuelta) {
                $totalVuelta   = ($precioRealVuelta * $personasPagadas) + $costoEquipajesVuelta;

                $origenVuelta = $vueloVuelta->origen;
                if (!$origenVuelta) {
                    $ciudadOrigenVuelta = $vueloVuelta->ciudadOrigen;
                    $origenVuelta = '';
                    if ($ciudadOrigenVuelta) {
                        $origenVuelta = $ciudadOrigenVuelta->nombre;
                    }
                }
                $destinoVuelta = $vueloVuelta->destino;
                if (!$destinoVuelta) {
                    $ciudadDestinoVuelta = $vueloVuelta->ciudadDestino;
                    $destinoVuelta = '';
                    if ($ciudadDestinoVuelta) {
                        $destinoVuelta = $ciudadDestinoVuelta->nombre;
                    }
                }

                $fechaSalidaVuelta = $vueloVuelta->hora_salida_programada;
                if (!$fechaSalidaVuelta) {
                    $fechaSalidaVuelta = $vueloVuelta->fecha_salida;
                }
                $fechaLlegadaVuelta = $vueloVuelta->fecha_llegada;
                if (!$fechaLlegadaVuelta) {
                    $fechaLlegadaVuelta = $vueloVuelta->hora_llegada_programada;
                }
                $meteorologiaVuelta = $climaOpciones[array_rand($climaOpciones)];

                $equipajeContadoresVuelta = [];
                foreach ($equipajes as $data) {
                    foreach ((array) $data as $trayecto => $pesos) {
                        if ($trayecto === 'vuelta') {
                            foreach (array_keys((array) $pesos) as $peso) {
                                if (!isset($equipajeContadoresVuelta[(int) $peso])) {
                                    $equipajeContadoresVuelta[(int) $peso] = 0;
                                }
                                $equipajeContadoresVuelta[(int) $peso] = $equipajeContadoresVuelta[(int) $peso] + 1;
                            }
                        }
                    }
                }
                $equipajePartsVuelta = [];
                foreach ($equipajeContadoresVuelta as $kg => $cnt) {
                    $equipajePartsVuelta[] = $cnt . 'x ' . $kg . ' kg';
                }
                // Equipaje incluido según plan
                $incluidoVuelta = 'Mano: 1x 10 kg';
                if ($planVuelta === 'comfort') {
                    $incluidoVuelta .= ' + Facturado: 1x 23 kg';
                }
                $equipajeResumenVuelta = $incluidoVuelta;
                if (!empty($equipajePartsVuelta)) {
                    $equipajeResumenVuelta .= ' | Extra: ' . implode(', ', $equipajePartsVuelta);
                }

                $enlazadaEnVuelta = null;
                if ($usuarioAutenticado) {
                    $enlazadaEnVuelta = now();
                }

                $reservaVuelta = Reserva::create([
                    'user_id'              => Auth::id(),
                    'enlazada_en'          => $enlazadaEnVuelta,
                    'localizador'          => strtoupper(Str::random(8)),
                    'vuelo_id'             => $seleccion['vuelo_vuelta_id'],
                    'vuelo_vuelta_id'      => null,
                    'plan_tarifa'          => 'planit_' . $planVuelta,
                    'precio_total'         => $totalVuelta,
                    'estado'               => 'confirmada',
                    'email_contacto'       => $emailContacto,
                    'origen'               => $origenVuelta,
                    'destino'              => $destinoVuelta,
                    'fecha_salida'         => $fechaSalidaVuelta,
                    'fecha_llegada'        => $fechaLlegadaVuelta,
                    'equipaje_resumen'     => $equipajeResumenVuelta,
                    'asientos_resumen'     => 'Se asignan tras el check-in online',
                    'meteorologia_resumen' => $meteorologiaVuelta,
                ]);
            }

            // Pasajeros: se crean de forma independiente para cada reserva
            $pasajerosConAsientoIda    = [];
            $pasajerosPorClaveIda      = [];
            $pasajerosConAsientoVuelta = [];
            $pasajerosPorClaveVuelta   = [];

            foreach ($adultos as $index => $p) {
                $fechaNacAdulto = null;
                if (isset($p['fecha_nacimiento'])) {
                    $fechaNacAdulto = $p['fecha_nacimiento'];
                }
                $docIdAdulto = null;
                if (isset($p['documento_identidad'])) {
                    $docIdAdulto = $p['documento_identidad'];
                }
                $nacAdulto = null;
                if (isset($p['nacionalidad'])) {
                    $nacAdulto = $p['nacionalidad'];
                }

                $pasajeroIda = Pasajero::create([
                    'reserva_id'          => $reservaIda->id,
                    'nombre'              => $p['nombre'],
                    'apellidos'           => $p['apellidos'],
                    'fecha_nacimiento'    => $fechaNacAdulto,
                    'tipo'                => 'adulto',
                    'documento_identidad' => $docIdAdulto,
                    'nacionalidad'        => $nacAdulto,
                ]);
                ReservaPasajero::create([
                    'reserva_id'       => $reservaIda->id,
                    'nombre'           => $p['nombre'],
                    'apellidos'        => $p['apellidos'],
                    'fecha_nacimiento' => $fechaNacAdulto,
                ]);
                $pasajerosConAsientoIda[] = $pasajeroIda;
                $pasajerosPorClaveIda['adulto_' . $index] = $pasajeroIda;
                if ($reservaVuelta) {
                    $pasajeroVuelta = Pasajero::create([
                        'reserva_id'          => $reservaVuelta->id,
                        'nombre'              => $p['nombre'],
                        'apellidos'           => $p['apellidos'],
                        'fecha_nacimiento'    => $fechaNacAdulto,
                        'tipo'                => 'adulto',
                        'documento_identidad' => $docIdAdulto,
                        'nacionalidad'        => $nacAdulto,
                    ]);
                    ReservaPasajero::create([
                        'reserva_id'       => $reservaVuelta->id,
                        'nombre'           => $p['nombre'],
                        'apellidos'        => $p['apellidos'],
                        'fecha_nacimiento' => $fechaNacAdulto,
                    ]);
                    $pasajerosConAsientoVuelta[] = $pasajeroVuelta;
                    $pasajerosPorClaveVuelta['adulto_' . $index] = $pasajeroVuelta;
                }
            }

            foreach ($menores as $index => $p) {
                $docIdMenor = null;
                if (isset($p['documento_identidad'])) {
                    $docIdMenor = $p['documento_identidad'];
                }
                $nacMenor = null;
                if (isset($p['nacionalidad'])) {
                    $nacMenor = $p['nacionalidad'];
                }
                $fechaNacMenor = null;
                if (isset($p['fecha_nacimiento'])) {
                    $fechaNacMenor = $p['fecha_nacimiento'];
                }

                $pasajeroIda = Pasajero::create([
                    'reserva_id'          => $reservaIda->id,
                    'nombre'              => $p['nombre'],
                    'apellidos'           => $p['apellidos'],
                    'tipo'                => 'nino',
                    'fecha_nacimiento'    => $p['fecha_nacimiento'],
                    'documento_identidad' => $docIdMenor,
                    'nacionalidad'        => $nacMenor,
                ]);
                ReservaPasajero::create([
                    'reserva_id'       => $reservaIda->id,
                    'nombre'           => $p['nombre'],
                    'apellidos'        => $p['apellidos'],
                    'fecha_nacimiento' => $fechaNacMenor,
                ]);
                $pasajerosConAsientoIda[] = $pasajeroIda;
                $pasajerosPorClaveIda['menor_' . $index] = $pasajeroIda;
                if ($reservaVuelta) {
                    $pasajeroVuelta = Pasajero::create([
                        'reserva_id'          => $reservaVuelta->id,
                        'nombre'              => $p['nombre'],
                        'apellidos'           => $p['apellidos'],
                        'tipo'                => 'nino',
                        'fecha_nacimiento'    => $p['fecha_nacimiento'],
                        'documento_identidad' => $docIdMenor,
                        'nacionalidad'        => $nacMenor,
                    ]);
                    ReservaPasajero::create([
                        'reserva_id'       => $reservaVuelta->id,
                        'nombre'           => $p['nombre'],
                        'apellidos'        => $p['apellidos'],
                        'fecha_nacimiento' => $fechaNacMenor,
                    ]);
                    $pasajerosConAsientoVuelta[] = $pasajeroVuelta;
                    $pasajerosPorClaveVuelta['menor_' . $index] = $pasajeroVuelta;
                }
            }

            foreach ($infantes as $index => $p) {
                $docIdInfante = null;
                if (isset($p['documento_identidad'])) {
                    $docIdInfante = $p['documento_identidad'];
                }
                $nacInfante = null;
                if (isset($p['nacionalidad'])) {
                    $nacInfante = $p['nacionalidad'];
                }
                $fechaNacInfante = null;
                if (isset($p['fecha_nacimiento'])) {
                    $fechaNacInfante = $p['fecha_nacimiento'];
                }

                $pasajeroIda = Pasajero::create([
                    'reserva_id'          => $reservaIda->id,
                    'nombre'              => $p['nombre'],
                    'apellidos'           => $p['apellidos'],
                    'tipo'                => 'bebe',
                    'fecha_nacimiento'    => $p['fecha_nacimiento'],
                    'documento_identidad' => $docIdInfante,
                    'nacionalidad'        => $nacInfante,
                ]);
                ReservaPasajero::create([
                    'reserva_id'       => $reservaIda->id,
                    'nombre'           => $p['nombre'],
                    'apellidos'        => $p['apellidos'],
                    'fecha_nacimiento' => $fechaNacInfante,
                ]);
                $pasajerosPorClaveIda['infante_' . $index] = $pasajeroIda;
                if ($reservaVuelta) {
                    $pasajeroVuelta = Pasajero::create([
                        'reserva_id'          => $reservaVuelta->id,
                        'nombre'              => $p['nombre'],
                        'apellidos'           => $p['apellidos'],
                        'tipo'                => 'bebe',
                        'fecha_nacimiento'    => $p['fecha_nacimiento'],
                        'documento_identidad' => $docIdInfante,
                        'nacionalidad'        => $nacInfante,
                    ]);
                    ReservaPasajero::create([
                        'reserva_id'       => $reservaVuelta->id,
                        'nombre'           => $p['nombre'],
                        'apellidos'        => $p['apellidos'],
                        'fecha_nacimiento' => $fechaNacInfante,
                    ]);
                    $pasajerosPorClaveVuelta['infante_' . $index] = $pasajeroVuelta;
                }
            }

            // Los asientos se asignan durante el check-in online, no en la compra

            // Equipajes: según trayecto van a su reserva correspondiente
            foreach ($equipajes as $key => $data) {
                foreach ((array) $data as $trayecto => $pesos) {
                    if ($trayecto === 'vuelta' && $reservaVuelta) {
                        $pasajeroMap     = $pasajerosPorClaveVuelta;
                        $vueloEquipajeId = (int) $seleccion['vuelo_vuelta_id'];
                    } else {
                        $pasajeroMap     = $pasajerosPorClaveIda;
                        $vueloEquipajeId = (int) $seleccion['vuelo_ida_id'];
                    }
                    if (!isset($pasajeroMap[$key])) {
                        continue;
                    }
                    foreach (array_keys((array) $pesos) as $peso) {
                        if (!isset($precios[$peso])) {
                            continue;
                        }
                        Equipaje::create([
                            'pasajero_id' => $pasajeroMap[$key]->id,
                            'vuelo_id'    => $vueloEquipajeId,
                            'tipo'        => 'facturado',
                            'peso'        => (string) $peso,
                            'cantidad'    => 1,
                            'precio'      => $precios[$peso],
                        ]);
                    }
                }
            }

            // Un pago por reserva
            Pago::create([
                'reserva_id' => $reservaIda->id,
                'metodo'     => 'tarjeta',
                'cantidad'   => $totalIda,
                'estado'     => 'completado',
                'fecha_pago' => now(),
            ]);
            if ($reservaVuelta) {
                Pago::create([
                    'reserva_id' => $reservaVuelta->id,
                    'metodo'     => 'tarjeta',
                    'cantidad'   => $totalVuelta,
                    'estado'     => 'completado',
                    'fecha_pago' => now(),
                ]);
            }

            $localizadoresGenerados = ['ida' => $reservaIda->localizador];
            if ($reservaVuelta) {
                $localizadoresGenerados['vuelta'] = $reservaVuelta->localizador;
            }
        });

        $localizadorPrincipal = '';
        if (isset($localizadoresGenerados['ida'])) {
            $localizadorPrincipal = $localizadoresGenerados['ida'];
        }
        if ($usuarioAutenticado) {
            $gestionUrl = route('mis-reservas.index');
        } else {
            $gestionUrl = route('mis-viajes.index', [
                'localizador'    => $localizadorPrincipal,
                'email_contacto' => $emailContacto,
            ]);
        }

        session()->forget('reserva');
        session()->put('compra_completada', [
            'localizadores'     => $localizadoresGenerados,
            'gestion_url'       => $gestionUrl,
            'enlazada_a_cuenta' => $usuarioAutenticado,
            'correo_contacto'   => $emailContacto,
        ]);

        if ($isJson) {
            return response()->json([
                'success'           => true,
                'redirect'          => route('flight.completed'),
                'localizador'       => $localizadorPrincipal,
                'gestion_url'       => $gestionUrl,
                'gestion_label'     => 'Ir a Mis viajes',
                'enlazada_a_cuenta' => $usuarioAutenticado,
            ]);
        }

        $mensajeExito = 'Reserva completada.';
        if ($localizadorPrincipal) {
            $mensajeExito .= ' Localizador ida: ' . $localizadorPrincipal;
        }
        if ($usuarioAutenticado) {
            $mensajeExito .= ' Las reservas han quedado enlazadas a tu cuenta.';
        }

        return redirect()->route('flight.completed')->with('success', $mensajeExito);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en pago: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            if ($isJson) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ha ocurrido un error al procesar el pago. Inténtalo de nuevo.',
                ], 500);
            }

            return redirect()->route('flight.summary')
                ->with('error', 'Ha ocurrido un error al procesar el pago.');
        }
    }

    public function completada()
    {
        $compraCompletada = session('compra_completada');

        if (!$compraCompletada) {
            return redirect()->route('principal');
        }

        return view('compra-completada', [
            'compraCompletada' => $compraCompletada,
        ]);
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
