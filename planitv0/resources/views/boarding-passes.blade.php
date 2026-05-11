<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tarjetas de embarque {{ $reserva->localizador }}</title>
    @php
        $boardingCssPath = public_path('css/boarding-passes.css');
        $boardingCss = '';
        if (is_file($boardingCssPath)) {
            $boardingCss = file_get_contents($boardingCssPath);
        }
    @endphp
    <style>
        {!! $boardingCss !!}
    </style>
</head>
<body>
    @foreach ($pasajeros as $pasajero)
        @php
            $soloLetras = function (string $texto): string {
                $resultado = '';
                $longitud = mb_strlen($texto, 'UTF-8');
                for ($i = 0; $i < $longitud; $i++) {
                    $char = mb_substr($texto, $i, 1, 'UTF-8');
                    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $char);
                    if ($ascii === false || $ascii === '') {
                        continue;
                    }

                    $ascii = strtoupper(substr($ascii, 0, 1));
                    if (ctype_alpha($ascii)) {
                        $resultado .= $ascii;
                    }
                }

                return $resultado;
            };

            $origenCode = substr($soloLetras((string) $reserva->origen), 0, 3);
            $destinoCode = substr($soloLetras((string) $reserva->destino), 0, 3);
            $esBebe = false;
            if (!is_null($pasajero->fecha_nacimiento) && !is_null($reserva->fecha_salida)) {
                $esBebe = \Carbon\Carbon::parse($pasajero->fecha_nacimiento)
                    ->diffInYears(\Carbon\Carbon::parse($reserva->fecha_salida)) < 2;
            }

            $seat = $pasajero->asiento_codigo;
            if ($esBebe) {
                $seat = 'REGAZO';
            } elseif (!$seat) {
                $seat = 'AUTO';
            }

            $seatTextoLargo = $seat;
            if ($esBebe) {
                $seatTextoLargo = 'REGAZO';
            }
            $boardingTime = optional($reserva->fecha_salida)->copy()?->subMinutes(40);
            $docRaw = '';
            if ($pasajero->numero_documento) {
                $docRaw = (string) $pasajero->numero_documento;
            }
            $doc = trim($docRaw);
            $origenCodeVisual = $origenCode;
            if ($origenCodeVisual === '') {
                $origenCodeVisual = '---';
            }
            $destinoCodeVisual = $destinoCode;
            if ($destinoCodeVisual === '') {
                $destinoCodeVisual = '---';
            }
            if ($doc !== '') {
                $docVisual = $doc;
            } else {
                $docVisual = 'No informado';
            }
            if ($boardingTime) {
                $boardingTimeCompleto = $boardingTime->format('d/m/Y H:i');
                $boardingTimeCorto = $boardingTime->format('H:i');
            } else {
                $boardingTimeCompleto = 'No disponible';
                $boardingTimeCorto = '--:--';
            }
            $equipajeVisual = $reserva->equipaje_resumen;
            if (!$equipajeVisual) {
                $equipajeVisual = 'Solo cabina';
            }

            $terminalVisual = optional($reserva->vueloIda)->terminal_salida ?? '-';
        @endphp

        <div class="page">
            <table class="ticket">
                <tr>
                    <td class="ticket-main">
                        <table class="ticket-top">
                            <tr>
                                <td>
                                    <div class="brand">Planit</div>
                                    <div class="sub">Tarjeta de embarque</div>
                                </td>
                                <td style="text-align:right;">
                                    <div class="label">Localizador</div>
                                    <div class="value" style="font-size:18px;">{{ $reserva->localizador }}</div>
                                </td>
                            </tr>
                        </table>

                        <table class="route-table">
                            <tr>
                                <td style="width:42%;">
                                    <div class="airport-code">{{ $origenCodeVisual }}</div>
                                    <div class="airport-city">{{ $reserva->origen }}</div>
                                </td>
                                <td class="route-arrow">&#8594;</td>
                                <td style="width:42%; text-align:right;">
                                    <div class="airport-code">{{ $destinoCodeVisual }}</div>
                                    <div class="airport-city">{{ $reserva->destino }}</div>
                                </td>
                            </tr>
                        </table>

                        <table class="details">
                            <tr>
                                <td>
                                    <div class="label">Pasajero</div>
                                    <div class="value">{{ $pasajero->nombre }} {{ $pasajero->apellidos }}</div>
                                </td>
                                <td>
                                    <div class="label">Documento</div>
                                    <div class="value">{{ $docVisual }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="label">Salida</div>
                                    <div class="value">{{ optional($reserva->fecha_salida)->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <div class="label">Llegada</div>
                                    <div class="value">{{ optional($reserva->fecha_llegada)->format('d/m/Y H:i') }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="label">Embarque</div>
                                    <div class="value">{{ $boardingTimeCompleto }}</div>
                                </td>
                                <td>
                                    <div class="label">Duración</div>
                                    <div class="value">{{ $reserva->duracionTexto() }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="label">Equipaje</div>
                                    <div class="value">{{ $equipajeVisual }}</div>
                                </td>
                                <td>
                                    <div class="label">Plan</div>
                                    <div class="value">{{ $reserva->nombrePlanTarifa() }}</div>
                                </td>
                            </tr>
                        </table>

                        <div class="notice">
                            Presenta esta tarjeta de embarque junto con tu documento identificativo.
                            Se recomienda llegar al aeropuerto con un mínimo de 90 minutos de antelación.
                            La terminal indicada es orientativa y podría cambiar. Consulta los paneles informativos del aeropuerto para confirmar la terminal y puerta de embarque definitivas.
                        </div>
                    </td>

                    <td class="ticket-stub">
                        <div class="seat-box">
                            <div class="mini">Asiento</div>
                            <div class="seat-code">{{ $seatTextoLargo }}</div>
                        </div>

                        <table class="stub-grid">
                            <tr>
                                <td>
                                    <div class="mini">Vuelo</div>
                                    <div class="mini-val">{{ $origenCodeVisual }}{{ $destinoCodeVisual }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="mini">Terminal</div>
                                    <div class="mini-val">{{ $terminalVisual }}</div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="mini">Zona</div>
                                    <div class="mini-val">General</div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="mini">Hora embarque</div>
                                    <div class="mini-val">{{ $boardingTimeCorto }}</div>
                                </td>
                            </tr>
                        </table>

                        <div class="code-box">{{ strtoupper($reserva->localizador . '-' . $seat) }}</div>
                    </td>
                </tr>
            </table>
        </div>
    @endforeach
</body>
</html>
