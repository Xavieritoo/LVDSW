@extends('cabecera')

@push('styles')
    <link href="{{ asset('css/checkout.css') }}" rel="stylesheet">
    <link href="{{ asset('css/pasajeros.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('js/pasajeros.js') }}"></script>
@endpush

@section('contenido')

    @include('partials.indicador-pasos', ['pasoActual' => 2])

    @php
        $parametrosVueltaResultados = session('reserva.busqueda', []);
        if (!empty($parametrosVueltaResultados)) {
            $urlVueltaResultados = route('flight.results', $parametrosVueltaResultados);
        } else {
            $urlVueltaResultados = route('flight.results');
        }
    @endphp

    <section class="passengers-header mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <div>
                <h2 class="h4 mb-1">Datos de pasajeros</h2>
                <p class="text-muted mb-2">Completa los datos de las {{ $totalPasajeros }} personas para continuar.</p>
                <span class="summary-badge">@if($esSchengen) Ruta Schengen @else Ruta no Schengen @endif</span>
                <span class="summary-badge">Adultos: {{ $adultos }}</span>
                <span class="summary-badge">Niños: {{ $menores }}</span>
                <span class="summary-badge">Bebés: {{ $infantes }}</span>
                <span class="summary-badge">@if($tipoViaje === 'ida_vuelta') Ida y vuelta @else Solo ida @endif</span>
            </div>
        </div>
    </section>

    @php
        if (isset($detalleVuelosSeleccionados)) {
            $detalleVuelosSeleccionadosParam = $detalleVuelosSeleccionados;
        } else {
            $detalleVuelosSeleccionadosParam = [];
        }
    @endphp
    @include('partials.detalle-vuelo-seleccionado', ['detalleVuelosSeleccionados' => $detalleVuelosSeleccionadosParam])

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>No se pudo continuar:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (!$esSchengen)
        <div class="alert alert-warning" role="alert">
            Esta ruta es fuera del espacio Schengen. La confirmación de pasaporte/documentación se realizará durante el check-in antes de viajar.
        </div>
    @endif

    <form method="POST" action="{{ route('flight.passengers.store') }}">
        @csrf

        @if ($adultos > 0)
            <h5 class="section-title">Adultos</h5>
            @for ($i = 0; $i < $adultos; $i++)
                @include('partials.ficha-pasajero', ['tipo' => 'adultos', 'titulo' => 'Adulto', 'numero' => $i + 1, 'index' => $i])
            @endfor
        @endif

        @if ($menores > 0)
            <h5 class="section-title">Niños (2 a 15 años)</h5>
            @for ($i = 0; $i < $menores; $i++)
                @include('partials.ficha-pasajero', ['tipo' => 'menores', 'titulo' => 'Niño', 'numero' => $i + 1, 'index' => $i])
            @endfor
        @endif

        @if ($infantes > 0)
            <h5 class="section-title">Bebés (0 a 2 años)</h5>
            @for ($i = 0; $i < $infantes; $i++)
                @include('partials.ficha-pasajero', ['tipo' => 'infantes', 'titulo' => 'Bebé', 'numero' => $i + 1, 'index' => $i])
            @endfor
        @endif

        <div class="passenger-card mt-3">
            <h6>Correo de contacto</h6>
            <div class="alert alert-info mb-3" role="alert">
                Es muy importante que indiques un correo válido: a ese correo se asociará la reserva para que puedas gestionarla desde el apartado Mis viajes, junto con el localizador que se generará al finalizar la compra.
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="field-label" for="correo_contacto">Correo electrónico</label>
                    <input type="email" class="form-control" id="correo_contacto" name="correo_contacto"
                        value="{{ old('correo_contacto') }}" required placeholder="ejemplo@correo.com">
                    @error('correo_contacto')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4 mb-5">
            <a href="{{ $urlVueltaResultados }}" class="btn btn-back-step px-4 py-2">Volver a selección de plan</a>
            <button type="submit" class="btn btn-planit px-4 py-2">Continuar</button>
        </div>
    </form>

@endsection

@push('scripts')
    <script src="{{ asset('js/pasajeros.js') }}"></script>
@endpush
