@extends('cabecera')

@push('styles')
    <link href="{{ asset('css/checkout.css') }}" rel="stylesheet">
    <link href="{{ asset('css/equipajes.css') }}" rel="stylesheet">
@endpush

@section('contenido')

    @include('partials.indicador-pasos', ['pasoActual' => 3])

    <section class="bags-header mb-4">
        <h2 class="h4 mb-1">Equipajes facturados</h2>
        <p class="text-muted mb-2">Añade equipaje en bodega. Máximo 1 unidad por peso y por tramo.</p>
        <span class="bags-pill">@if($tipoViaje === 'ida_vuelta') Ida y vuelta @else Solo ida @endif</span>
        <span class="bags-pill">@if($esSchengen) Ruta Schengen @else Ruta no Schengen @endif</span>
        <span class="bags-pill">Contacto: {{ $correoContacto }}</span>
        <span class="bags-pill">Pasajeros: {{ count($listaPasajeros) }}</span>
    </section>

    @php
        if (isset($detalleVuelosSeleccionados)) {
            $detalleVuelosSeleccionadosParam = $detalleVuelosSeleccionados;
        } else {
            $detalleVuelosSeleccionadosParam = [];
        }
    @endphp
    @include('partials.detalle-vuelo-seleccionado', ['detalleVuelosSeleccionados' => $detalleVuelosSeleccionadosParam])

    <div class="alert alert-info">
        Selecciona equipaje por pasajero. Cada maleta añadida se suma al total de esta seccion.
    </div>

    <form method="POST" action="{{ route('flight.baggage.store') }}" id="formularioEquipajes"
        data-total-base="{{ $precioBaseTotal }}">
        @csrf

        <div class="bags-card">
            <h5 class="mb-3">Equipaje facturado por pasajero</h5>

            @foreach ($listaPasajeros as $pasajero)
                <div class="passenger-block">
                    <div class="mb-2">
                        @php
                            if (!empty($pasajero['nombre'])) {
                                $nombrePasajero = $pasajero['nombre'];
                            } else {
                                $nombrePasajero = 'Pasajero';
                            }
                        @endphp
                        <div class="passenger-name">{{ $nombrePasajero }}</div>
                        <small class="text-muted">{{ $pasajero['tipo'] }}</small>
                    </div>

                    <h6 class="mb-2">Ida</h6>
                    <div class="row g-2 mb-3">
                        @foreach ($tarifasEquipaje as $kg => $precio)
                            <div class="col-6 col-md-3">
                                <label class="bag-option w-100">
                                    <input type="checkbox"
                                        name="equipajes[{{ $pasajero['clave'] }}][ida][{{ $kg }}]"
                                        value="1"
                                        class="form-check-input me-2 bag-check"
                                        data-precio="{{ $precio }}">
                                    <strong>{{ $kg }} kg</strong>
                                    <div class="price-tag">{{ number_format($precio, 2, ',', '.') }} EUR</div>
                                    <div class="small text-muted">Máx. 1 unidad</div>
                                </label>
                            </div>
                        @endforeach
                    </div>

                    @if ($tipoViaje === 'ida_vuelta')
                        <h6 class="mb-2">Vuelta</h6>
                        <div class="row g-2">
                            @foreach ($tarifasEquipaje as $kg => $precio)
                                <div class="col-6 col-md-3">
                                    <label class="bag-option w-100">
                                        <input type="checkbox"
                                            name="equipajes[{{ $pasajero['clave'] }}][vuelta][{{ $kg }}]"
                                            value="1"
                                            class="form-check-input me-2 bag-check"
                                            data-precio="{{ $precio }}">
                                        <strong>{{ $kg }} kg</strong>
                                        <div class="price-tag">{{ number_format($precio, 2, ',', '.') }} EUR</div>
                                        <div class="small text-muted">Máx. 1 unidad</div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="bags-card total-box">
            <div class="total-line">
                <span>Tarifa base (vuelos)</span>
                <strong id="totalBase">{{ number_format($precioBaseTotal, 2, ',', '.') }} EUR</strong>
            </div>
            <div class="total-line">
                <span>Equipajes seleccionados</span>
                <strong id="totalEquipajes">0,00 EUR</strong>
            </div>
            <hr class="my-2">
            <div class="total-line mb-0">
                <span>Total en esta sección</span>
                <strong id="totalSeccion">{{ number_format($precioBaseTotal, 2, ',', '.') }} EUR</strong>
            </div>
        </div>

        <div class="d-flex gap-2 mb-5">
            <a href="{{ route('flight.passengers') }}" class="btn btn-outline-primary">Volver a pasajeros</a>
            <button type="submit" class="btn btn-planit">Continuar</button>
        </div>
    </form>

    <script src="{{ asset('js/equipajes.js') }}"></script>
@endsection
