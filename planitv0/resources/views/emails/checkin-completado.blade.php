<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-in completado</title>
    @php
        $emailCssPath = public_path('css/email-checkin-completado.css');
        $emailCss = '';
        if (is_file($emailCssPath)) {
            $emailCss = file_get_contents($emailCssPath);
        }
    @endphp
    <style>
        {!! $emailCss !!}
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>Check-in completado</h1>
            <p>Tus tarjetas de embarque están adjuntas a este correo.</p>
        </div>
        <div class="body">
            <div class="vuelo-box">
                <div class="ruta">{{ $reserva->origen }} → {{ $reserva->destino }}</div>
                <div class="detalle">
                    <strong>Localizador:</strong> {{ $reserva->localizador }}
                    &nbsp;|&nbsp;
                    <strong>Salida:</strong> {{ optional($reserva->fecha_salida)->format('d/m/Y H:i') }}
                </div>
            </div>

            <p style="margin-bottom:12px;font-size:14px;">
                El check-in se ha completado correctamente para los siguientes pasajeros:
            </p>

            <ul class="pasajero-list">
                @foreach ($pasajeros as $p)
                    <li>
                        <strong>{{ $p->nombre }} {{ $p->apellidos }}</strong>
                        @if ($p->asiento_codigo)
                            <span class="asiento-badge">Asiento {{ $p->asiento_codigo }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>

            <div class="aviso">
                <strong>Importante:</strong> Las tarjetas de embarque están adjuntas en PDF.
                Puedes descargarlas también desde <em>Mis Reservas</em> en la plataforma en cualquier momento.
            </div>

            <p style="font-size:13px;color:#6b7280;margin:0;">
                Si no realizaste este check-in o tienes alguna duda, contacta con nosotros
                respondiendo a este correo.
            </p>
        </div>
        <div class="footer">
            © {{ date('Y') }} Planit &mdash; Este mensaje se ha generado automáticamente.
        </div>
    </div>
</body>
</html>
