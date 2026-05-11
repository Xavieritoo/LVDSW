@extends('cabecera')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/destino-show.css') }}">
@endpush

@section('contenido')
    <div class="mb-4 destino-hero">
        <h1>{{ $infoAeropuerto['titulo'] }}</h1>
        <p class="text-muted mb-0">Ruta seleccionada: {{ $origen->nombre }} ({{ $origen->codigo_iata }}) -> {{ $destino->nombre }}
            ({{ $destino->codigo_iata }}).</p>
    </div>

    <div class="mb-4">
        <a href="{{ route('destinos.index') }}?origen_id={{ $origen->id }}" class="btn btn-outline-secondary">← Volver a destinos</a>
    </div>

    <div class="row gy-4 destino-layout">
        <div class="col-lg-8">
            <article class="card mb-4 destino-card" id="resumen-aeropuerto">
                <div class="card-body">
                    <h2>Información del aeropuerto</h2>
                    <p>{{ $infoAeropuerto['resumen'] }}</p>
                    <p class="mb-0"><strong>{{ $infoTuristica['resumen_local'] }}</strong></p>
                </div>
            </article>

            <article class="card mb-4 destino-card" id="terminales">
                <div class="card-body">
                    <h2>¿En qué terminal opera tu vuelo?</h2>
                    <p>{{ $infoAeropuerto['terminal'] }}</p>
                </div>
            </article>

            <article class="card mb-4 destino-card" id="horarios-facturacion">
                <div class="card-body">
                    <h2>Horarios de facturación y embarque</h2>
                    <p>Ten en cuenta estos tiempos orientativos para llegar al aeropuerto con margen suficiente:</p>
                    <ul>
                        @foreach ($infoAeropuerto['facturacion'] as $linea)
                            <li>{{ $linea }}</li>
                        @endforeach
                    </ul>
                    <p class="mb-0"><strong>Recomendación:</strong> en temporadas altas, festivos y operaciones con equipaje facturado, llega con
                        antelación adicional.</p>
                </div>
            </article>

            <article class="card mb-4 destino-card" id="transporte-aeropuerto">
                <div class="card-body">
                    <h2>Cómo llegar y salir del aeropuerto</h2>
                    <ul>
                        @foreach ($infoAeropuerto['transporte'] as $linea)
                            <li>{{ $linea }}</li>
                        @endforeach
                    </ul>
                </div>
            </article>

            <article class="card mb-4 destino-card" id="servicios-aeropuerto">
                <div class="card-body">
                    <h2>Servicios disponibles en el aeropuerto</h2>
                    <ul>
                        @foreach ($infoAeropuerto['servicios'] as $linea)
                            <li>{{ $linea }}</li>
                        @endforeach
                    </ul>
                    <p class="mb-0 text-muted">{{ $infoAeropuerto['nota'] }}</p>
                </div>
            </article>

            <article class="card mb-4 destino-card" id="resumen-turistico">
                <div class="card-body">
                    <h2>Información turística</h2>
                    <p>{{ $infoTuristica['intro'] }}</p>
                </div>
            </article>

            <article class="card mb-4 destino-card" id="que-visitar">
                <div class="card-body">
                    <h2>Qué visitar en {{ $destino->nombre }}</h2>
                    <ul>
                        @foreach ($infoTuristica['imprescindibles'] as $linea)
                            <li>{{ $linea }}</li>
                        @endforeach
                    </ul>
                </div>
            </article>

            <article class="card mb-4 destino-card" id="barrios-ambiente">
                <div class="card-body">
                    <h2>Barrios y ambiente local</h2>
                    <ul>
                        @foreach ($infoTuristica['barrios'] as $linea)
                            <li>{{ $linea }}</li>
                        @endforeach
                    </ul>
                </div>
            </article>

            <article class="card mb-4 destino-card" id="gastronomia">
                <div class="card-body">
                    <h2>Gastronomía y consejos prácticos</h2>
                    <h3 class="destino-subtitulo">Dónde comer y qué probar</h3>
                    <ul>
                        @foreach ($infoTuristica['gastronomia'] as $linea)
                            <li>{{ $linea }}</li>
                        @endforeach
                    </ul>

                    <h3 class="destino-subtitulo">Movilidad en destino</h3>
                    <ul>
                        @foreach ($infoTuristica['movilidad'] as $linea)
                            <li>{{ $linea }}</li>
                        @endforeach
                    </ul>

                    <p><strong>Mejor época para viajar:</strong> {{ $infoTuristica['mejor_epoca'] }}</p>
                    <p class="mb-0 text-muted">{{ $infoTuristica['nota'] }}</p>
                </div>
            </article>

        </div>

        <aside class="col-lg-4">
            <div class="card destino-toc mb-4">
                <div class="card-body">
                    <h4>Indice de contenidos</h4>
                    <a href="#resumen-aeropuerto">Resumen del aeropuerto</a>
                    <a href="#terminales">Terminales y acceso a embarque</a>
                    <a href="#horarios-facturacion">Horarios de facturación y embarque</a>
                    <a href="#transporte-aeropuerto">Cómo llegar al aeropuerto</a>
                    <a href="#servicios-aeropuerto">Servicios del aeropuerto</a>
                    <a href="#resumen-turistico">Resumen turístico</a>
                    <a href="#que-visitar">Qué visitar</a>
                    <a href="#barrios-ambiente">Barrios y ambiente local</a>
                    <a href="#gastronomia">Gastronomía y consejos prácticos</a>
                </div>
            </div>

            @if ($esAdmin)
                <div class="card">
                    <div class="card-body">
                        <h4>Operaciones</h4>
                        <p class="mb-1">Vuelos visibles: {{ $vuelos->count() }}</p>
                        <p class="mb-0">Ofertas activas: {{ $ofertas->count() }}</p>
                    </div>
                </div>
            @endif
        </aside>
    </div>
@endsection
