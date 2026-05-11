@extends('cabecera')

@push('styles')
    <link href="{{ asset('css/checkout.css') }}" rel="stylesheet">
    <link href="{{ asset('css/resumen.css') }}" rel="stylesheet">
@endpush

@section('contenido')

    @include('partials.indicador-pasos', ['pasoActual' => 4])

    <section class="summary-header mb-4">
        <h2 class="h4 mb-1">Resumen de tu reserva</h2>
        <p class="text-muted mb-0">Revisa tu selección antes del pago. Puedes volver a pasos anteriores para modificar.</p>
    </section>

    @php
        if (isset($detalleVuelosSeleccionados)) {
            $detalleVuelosSeleccionadosParam = $detalleVuelosSeleccionados;
        } else {
            $detalleVuelosSeleccionadosParam = [];
        }
    @endphp
    @include('partials.detalle-vuelo-seleccionado', ['detalleVuelosSeleccionados' => $detalleVuelosSeleccionadosParam])

    <section class="summary-card">
        <h5 class="mb-2">Vuelos y tarifa</h5>
        <span class="chip">Tipo: @if($tipoViaje === 'ida_vuelta') Ida y vuelta @else Solo ida @endif</span>
        <span class="chip">Plan ida: {{ $seleccion['plan_ida'] }}</span>
        <span class="chip">Precio ida: {{ number_format($seleccion['precio_ida'], 2, ',', '.') }} EUR</span>
        @if ($tipoViaje === 'ida_vuelta')
            <span class="chip">Plan vuelta: {{ $seleccion['plan_vuelta'] }}</span>
            <span class="chip">Precio vuelta: {{ number_format($seleccion['precio_vuelta'], 2, ',', '.') }} EUR</span>
        @endif
    </section>

    <section class="summary-card">
        <h5 class="mb-2">Pasajeros</h5>
        <div class="summary-line"><span>Adultos</span><strong>{{ $numAdultos }}</strong></div>
        <div class="summary-line"><span>Niños</span><strong>{{ $numMenores }}</strong></div>
        <div class="summary-line mb-0"><span>Bebés</span><strong>{{ $numInfantes }}</strong></div>
        <hr>
        <div><strong>Contacto:</strong> {{ $correoContacto }}</div>
    </section>

    <section class="summary-card">
        <h5 class="mb-2">Equipaje extra facturado</h5>
        <p class="text-muted small mb-2">Maletas adicionales a las ya incluidas en tu plan. Consulta el equipaje incluido en los detalles de cada vuelo.</p>
        @if (count($resumenEquipajes) === 0)
            <div class="text-muted">No has añadido equipaje facturado extra.</div>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Pasajero</th>
                            <th>Tramo</th>
                            <th>Peso</th>
                            <th class="text-end">Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resumenEquipajes as $bag)
                            <tr>
                                <td>{{ $bag['pasajero'] }}</td>
                                <td>{{ ucfirst($bag['tramo']) }}</td>
                                <td>{{ $bag['peso'] }} kg</td>
                                <td class="text-end">{{ number_format($bag['precio'], 2, ',', '.') }} EUR</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="summary-card">
        <h5 class="mb-3">Total</h5>
        <div class="summary-line">
            <span>Tarifa base vuelos</span>
            <strong>{{ number_format($precioBase, 2, ',', '.') }} EUR</strong>
        </div>
        <div class="summary-line">
            <span>Equipajes</span>
            <strong>{{ number_format($precioEquipajes, 2, ',', '.') }} EUR</strong>
        </div>
        <hr>
        <div class="summary-line summary-total mb-0">
            <span>Total reserva</span>
            <strong>{{ number_format($totalFinal, 2, ',', '.') }} EUR</strong>
        </div>
    </section>

    <form id="payment-form" method="POST" action="{{ route('flight.pay') }}">
        @csrf
        <div id="payment-status" role="alert" style="display:none;" class="mb-3"></div>
        <div class="d-flex flex-wrap gap-2 mb-5">
            <a href="{{ route('principal') }}" class="btn btn-outline-primary">Modificar búsqueda</a>
            <a href="{{ route('flight.passengers') }}" class="btn btn-outline-primary">Modificar pasajeros</a>
            <a href="{{ route('flight.baggage') }}" class="btn btn-outline-primary">Modificar equipajes</a>
            <button id="pay-btn" type="submit" class="btn btn-planit">Ir al pago</button>
        </div>
    </form>

@endsection

@push('scripts')
<script src="{{ asset('js/pago.js') }}" defer></script>
@endpush
