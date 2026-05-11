{{--
    Tarjeta de un vuelo con su selector de planes.

    Variables recibidas:
      $vuelo → objeto Vuelo
      $tramo → tramo: 'ida' o 'vuelta'
--}}
@php
    if ($vuelo->precio_base && $vuelo->precio_base > 0) {
        $precioTarjeta = $vuelo->precio_base;
    } else {
        if (isset($vuelo->precio)) {
            $precioTarjeta = $vuelo->precio;
        } else {
            $precioTarjeta = 0;
        }
    }
    if (!empty($vuelo->origen)) {
        $origenTexto = $vuelo->origen;
    } elseif (!empty(optional($vuelo->ciudadOrigen)->nombre)) {
        $origenTexto = optional($vuelo->ciudadOrigen)->nombre;
    } else {
        $origenTexto = '—';
    }

    if (!empty($vuelo->destino)) {
        $destinoTexto = $vuelo->destino;
    } elseif (!empty(optional($vuelo->ciudadDestino)->nombre)) {
        $destinoTexto = optional($vuelo->ciudadDestino)->nombre;
    } else {
        $destinoTexto = '—';
    }

    if (!empty($vuelo->codigo)) {
        $codigoVuelo = $vuelo->codigo;
    } else {
        $codigoVuelo = $vuelo->numero_vuelo;
    }
    if (isset($vuelo->hora_llegada_programada)) {
        $fechaLlegada = $vuelo->hora_llegada_programada;
    } else {
        $fechaLlegada = $vuelo->fecha_llegada;
    }
@endphp
<article class="result-card">

    {{-- Código y precio base --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="route-pill">{{ $codigoVuelo }}</span>
        <strong class="text-primary">{{ number_format($precioTarjeta, 2, ',', '.') }} EUR</strong>
    </div>

    {{-- Ruta y horario --}}
    <div class="fw-bold">
        {{ $origenTexto }} <i class="bi bi-arrow-right"></i> {{ $destinoTexto }}
    </div>
    <small class="text-muted d-block mb-2">
        Salida: {{ \Carbon\Carbon::parse($vuelo->fecha_salida)->format('d/m/Y H:i') }}
        @if($fechaLlegada)
            | Llegada: {{ \Carbon\Carbon::parse($fechaLlegada)->format('d/m/Y H:i') }}
        @endif
    </small>

    {{-- Tipo de ruta (Schengen o internacional) --}}
    <span class="badge @if($vuelo->es_schengen) text-bg-success @else text-bg-warning @endif">
        @if($vuelo->es_schengen) Doméstico / Schengen @else Internacional fuera Schengen @endif
    </span>

    {{-- Botón para expandir planes --}}
    <div class="mt-3">
        <button class="btn btn-sm btn-outline-primary" type="button"
            data-bs-toggle="collapse"
            data-bs-target="#planes-{{ $tramo }}-{{ $vuelo->id }}">
            Seleccionar vuelo y ver planes
        </button>
    </div>

    {{-- Planes: EASY y COMFORT --}}
    <div class="collapse" id="planes-{{ $tramo }}-{{ $vuelo->id }}">
        <div class="plan-picker">
            <div class="row g-3">
                <div class="col-md-6">
                    @include('partials.plan', [
                        'tramo'      => $tramo,
                        'vuelo'      => $vuelo,
                        'nombrePlan' => 'PLANIT EASY',
                        'precio'     => $precioTarjeta,
                    ])
                </div>
                <div class="col-md-6">
                    @include('partials.plan', [
                        'tramo'      => $tramo,
                        'vuelo'      => $vuelo,
                        'nombrePlan' => 'PLANIT COMFORT',
                        'precio'     => $precioTarjeta + 70,
                    ])
                </div>
            </div>
        </div>
    </div>

</article>
