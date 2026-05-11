@extends('cabecera')

@push('styles')
    <link href="{{ asset('css/checkout.css') }}" rel="stylesheet">
    <link href="{{ asset('css/resultados.css') }}" rel="stylesheet">
@endpush

@section('contenido')

    @include('partials.indicador-pasos', ['pasoActual' => 1])

    @php
        if (!empty($busqueda['origen'])) {
            $origenTexto = $busqueda['origen'];
        } else {
            $origenTexto = 'Origen sin definir';
        }

        if (!empty($busqueda['destino'])) {
            $destinoTexto = $busqueda['destino'];
        } else {
            $destinoTexto = 'Destino sin definir';
        }

        if (!empty($busqueda['fecha_ida'])) {
            $fechaIdaTexto = $busqueda['fecha_ida'];
        } else {
            $fechaIdaTexto = 'Sin fecha ida';
        }

        if (!empty($busqueda['fecha_vuelta'])) {
            $fechaVueltaTexto = $busqueda['fecha_vuelta'];
        } else {
            $fechaVueltaTexto = 'Sin fecha vuelta';
        }
    @endphp

    {{-- Cabecera con resumen de la búsqueda --}}
    <section class="results-header mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
            <div>
                <h2 class="h4 mb-2">Resultados de vuelos</h2>
                <div>
                    <span class="search-tag">@if($busqueda['tipo_viaje'] === 'ida_vuelta') Ida y vuelta @else Solo ida @endif</span>
                    <span class="search-tag">
                        {{ $origenTexto }}
                        <i class="bi bi-arrow-right"></i>
                        {{ $destinoTexto }}
                    </span>
                    <span class="search-tag">{{ $totalPasajeros }} pasajeros</span>
                    <span class="search-tag">{{ $fechaIdaTexto }}</span>
                    @if ($busqueda['tipo_viaje'] === 'ida_vuelta')
                        <span class="search-tag">{{ $fechaVueltaTexto }}</span>
                    @endif
                </div>
            </div>
            <div class="d-flex align-items-start">
                <a href="{{ route('principal') }}" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Modificar búsqueda
                </a>
            </div>
        </div>
    </section>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Hay errores en la búsqueda:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section id="seccionResumen" class="trip-summary mb-4 d-none"
        data-tipo-viaje="{{ $busqueda['tipo_viaje'] }}"
        data-url-pasajeros="{{ route('flight.passengers') }}"
        data-num-adultos="{{ (int) $busqueda['adultos'] }}"
        data-num-menores="{{ (int) $busqueda['menores'] }}"
        data-num-infantes="{{ (int) $busqueda['infantes'] }}"
        data-origen="@if(isset($busqueda['origen'])){{ $busqueda['origen'] }}@endif"
        data-destino="@if(isset($busqueda['destino'])){{ $busqueda['destino'] }}@endif"
        data-fecha-ida="@if(isset($busqueda['fecha_ida'])){{ $busqueda['fecha_ida'] }}@endif"
        data-fecha-vuelta="@if(isset($busqueda['fecha_vuelta'])){{ $busqueda['fecha_vuelta'] }}@endif"
        data-zona="@if(isset($busqueda['zona'])){{ $busqueda['zona'] }}@else all @endif">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Resumen de tu viaje</h5>
            <small class="text-muted">{{ $totalPasajeros }} @if($totalPasajeros !== 1) pasajeros @else pasajero @endif</small>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-md-6">
                <div class="summary-leg" id="bloqueIda">
                    <h6>Ida</h6>
                    <div id="idaVuelo" class="fw-semibold">No has seleccionado vuelo de ida.</div>
                    <div id="idaPlan" class="text-muted">Tarifa: -</div>
                    <div id="idaPrecio" class="text-primary fw-bold">Precio ida: -</div>
                    <a href="#" id="enlaceCambiarIda" class="summary-change-link">Cambiar ida</a>
                </div>
            </div>
            <div class="col-md-6" id="columnaVuelta">
                <div class="summary-leg" id="bloqueVuelta">
                    <h6>Vuelta</h6>
                    <div id="vueltaVuelo" class="fw-semibold">No has seleccionado vuelo de vuelta.</div>
                    <div id="vueltaPlan" class="text-muted">Tarifa: -</div>
                    <div id="vueltaPrecio" class="text-primary fw-bold">Precio vuelta: -</div>
                    <a href="#" id="enlaceCambiarVuelta" class="summary-change-link">Cambiar vuelta</a>
                </div>
            </div>
        </div>

        <div class="summary-total d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="text-muted">Precio total</div>
                <div id="precioTotal" class="h5 mb-0 text-primary">-</div>
            </div>
            <button type="button" id="btnContinuar" class="btn btn-planit" disabled>
                Continuar compra
            </button>
        </div>

    </section>

    <section class="flight-results">
        <div class="row g-4">
            <div class="col-lg-6" id="columnaResultadosIda">
                <h5 class="mb-3">Vuelos de ida</h5>
                @if (!empty($avisoIda))
                    <div class="alert alert-warning py-2 px-3 small"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $avisoIda }}</div>
                @endif
                @forelse ($vuelosIda as $vuelo)
                    @include('partials.tarjeta-vuelo', ['vuelo' => $vuelo, 'tramo' => 'ida'])
                @empty
                    <div class="alert alert-light border">No se han encontrado vuelos de ida con estos filtros.</div>
                @endforelse
            </div>

            @if ($busqueda['tipo_viaje'] === 'ida_vuelta')
                <div class="col-lg-6" id="columnaResultadosVuelta">
                    <h5 class="mb-3">Vuelos de vuelta</h5>
                    @if (!empty($avisoVuelta))
                        <div class="alert alert-warning py-2 px-3 small"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $avisoVuelta }}</div>
                    @endif
                    @forelse ($vuelosVuelta as $vuelo)
                        @include('partials.tarjeta-vuelo', ['vuelo' => $vuelo, 'tramo' => 'vuelta'])
                    @empty
                        <div class="alert alert-light border">No se han encontrado vuelos de vuelta con estos filtros.</div>
                    @endforelse
                </div>
            @endif
        </div>
    </section>

    <script src="{{ asset('js/resultados.js') }}"></script>

@endsection
