{{--
    Tarjeta de un plan de vuelo (PLANIT EASY o PLANIT COMFORT).

    Variables recibidas:
      $tramo      → tramo del vuelo: 'ida' o 'vuelta'
      $vuelo      → objeto Vuelo
      $nombrePlan → nombre del plan: 'PLANIT EASY' o 'PLANIT COMFORT'
      $precio     → precio por persona (número decimal)
--}}
<div class="plan-card">
    @php
        if (isset($vuelo->hora_salida_programada)) {
            $salidaReferencia = $vuelo->hora_salida_programada;
        } else {
            $salidaReferencia = $vuelo->fecha_salida;
        }
        if ($salidaReferencia) {
            $horaSalidaPlan = \Carbon\Carbon::parse($salidaReferencia)->format('H:i');
        } else {
            $horaSalidaPlan = '';
        }
        if (!empty($vuelo->origen)) {
            $rutaOrigen = $vuelo->origen;
        } elseif (!empty(optional($vuelo->ciudadOrigen)->nombre)) {
            $rutaOrigen = optional($vuelo->ciudadOrigen)->nombre;
        } else {
            $rutaOrigen = 'Origen';
        }

        if (!empty($vuelo->destino)) {
            $rutaDestino = $vuelo->destino;
        } elseif (!empty(optional($vuelo->ciudadDestino)->nombre)) {
            $rutaDestino = optional($vuelo->ciudadDestino)->nombre;
        } else {
            $rutaDestino = 'Destino';
        }
    @endphp
    <h6>{{ $nombrePlan }}</h6>
    <ul class="plan-list">
        {{-- Incluido en todos los planes --}}
        <li>Accesorio personal: 1 pieza debajo del asiento delantero (40x30x15 cm)</li>
        <li>Equipaje de mano: 1 pieza hasta 10 kg (56x40x25 cm)</li>

        @if ($nombrePlan === 'PLANIT EASY')
            <li>Check-in disponible desde 24 horas antes del vuelo</li>
            <li>Asiento estándar</li>
            <li>Selección de asiento desde 24h antes del vuelo</li>
        @else
            <li>Equipaje facturado: incluida 1 pieza de hasta 23 kg</li>
            <li>Asiento estándar con selección anticipada</li>
            <li>Early check-in desde el momento de compra</li>
            <li>Embarque prioritario</li>
        @endif
    </ul>

    <button type="button" class="btn btn-outline-primary btn-sm w-100 plan-action"
        data-tramo="{{ $tramo }}"
        data-id-vuelo="{{ $vuelo->id }}"
        data-codigo-vuelo="{{ $vuelo->codigo }}"
        data-ruta="{{ $rutaOrigen }} -> {{ $rutaDestino }}"
        data-hora-salida="{{ $horaSalidaPlan }}"
        data-plan="{{ $nombrePlan }}"
        data-precio="{{ number_format((float) $precio, 2, '.', '') }}">
        {{ number_format($precio, 2, ',', '.') }} EUR/por persona
    </button>
</div>
