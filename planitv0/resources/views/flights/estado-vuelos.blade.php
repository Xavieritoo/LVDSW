@extends('cabecera')

@push('styles')
    <link href="{{ asset('css/estado-vuelos.css') }}" rel="stylesheet">
@endpush

@section('contenido')
    <div class="flights-shell">

    <div class="page-title">Estado de vuelos</div>

    <div class="hero-banner">
        <div class="hero-title">¿Qué vuelo quieres consultar?</div>

        <div class="search-container">
            <div class="search-section">
                <h3>Buscar por origen, destino y fecha</h3>
                <form action="{{ route('flights.index') }}" method="GET" class="search-form" id="formRuta">
                    <div class="input-group">
                        <label>Origen</label>
                        <button type="button" class="btn-campo-vuelo" id="btn-origen-vuelo">
                            <span id="origen-texto-vuelo">
                                @if($origen && $ciudadOrigen)
                                    {{ $ciudadOrigen->nombre }} ({{ $ciudadOrigen->codigo_iata }})
                                @else
                                    Selecciona origen
                                @endif
                            </span>
                        </button>
                        <input type="hidden" name="origen" id="origen-valor" value="{{ $origen }}">
                    </div>
                    <div class="input-group">
                        <label>Destino</label>
                        <button type="button" class="btn-campo-vuelo" id="btn-destino-vuelo">
                            <span id="destino-texto-vuelo">
                                @if($destino && $ciudadDestino)
                                    {{ $ciudadDestino->nombre }} ({{ $ciudadDestino->codigo_iata }})
                                @else
                                    Selecciona destino
                                @endif
                            </span>
                        </button>
                        <input type="hidden" name="destino" id="destino-valor" value="{{ $destino }}">
                    </div>
                    <div class="input-group">
                        <label>Mes del vuelo</label>
                        <input type="month" name="mes" id="input-mes-vuelo" value="{{ $mes }}" min="{{ $mesMinimo }}" max="{{ $mesMaximo }}" required>
                    </div>
                    <button type="submit" class="btn-search">Buscar Vuelos</button>
                </form>
            </div>

            <!-- Modal Origen Ciudad -->
            <div id="modal-origen" class="modal-ciudades">
                <div class="modal-ciudades-contenido">
                    <div class="modal-ciudades-header">
                        <h3>Selecciona ciudad de origen</h3>
                        <button class="modal-ciudades-close" data-modal="modal-origen">&times;</button>
                    </div>
                    <div class="modal-ciudades-busqueda">
                        <input type="text" id="input-buscar-origen" placeholder="Busca ciudad o código IATA...">
                    </div>
                    <div class="modal-ciudades-lista" id="lista-origen">
                    </div>
                </div>
            </div>

            <!-- Modal Destino Ciudad -->
            <div id="modal-destino" class="modal-ciudades">
                <div class="modal-ciudades-contenido">
                    <div class="modal-ciudades-header">
                        <h3>Selecciona ciudad de destino</h3>
                        <button class="modal-ciudades-close" data-modal="modal-destino">&times;</button>
                    </div>
                    <div class="modal-ciudades-busqueda">
                        <input type="text" id="input-buscar-destino" placeholder="Busca ciudad o código IATA...">
                    </div>
                    <div class="modal-ciudades-lista" id="lista-destino">
                    </div>
                </div>
            </div>

            <div class="search-section">
                <h3>Buscar por número de vuelo</h3>
                <form action="{{ route('flights.index') }}" method="GET" class="search-form">
                    <div class="input-group">
                        <label>Número de vuelo</label>
                        <input type="text" name="numeroVuelo" placeholder="Ej. VY3004" value="{{ $numeroVuelo }}" style="text-transform: uppercase;" required>
                    </div>
                    <div class="input-group">
                        <label>Fecha del vuelo</label>
                        <input type="date" name="fecha" value="{{ $fecha }}" required>
                    </div>
                    <button type="submit" class="btn-search">Buscar Vuelo</button>
                </form>
            </div>
        </div>
    </div>

    @if ($hayCalendario)
    <div class="calendario-vuelos">
        <div class="calendario-header">
            <h2>{{ $ciudadOrigen->nombre }} ({{ $origen }}) - {{ $ciudadDestino->nombre }} ({{ $destino }})</h2>
            <h3 class="cal-mes-nombre">{{ ucfirst(\Carbon\Carbon::createFromFormat('Y-m', $mes)->locale('es')->translatedFormat('F Y')) }}</h3>
            <p>Selecciona un día para ver los vuelos disponibles</p>
        </div>

        <div class="cal-semana">
            <div>Lun</div><div>Mar</div><div>Mié</div><div>Jue</div><div>Vie</div><div>Sáb</div><div>Dom</div>
        </div>
        <div class="cal-grid">
            @foreach ($calendarioDias as $dia)
                @if ($dia['vacio'])
                    <div class="cal-celda vacia"></div>
                @elseif ($dia['pasado'])
                    <div class="cal-celda pasado">
                        <span class="cal-dia">{{ $dia['dia'] }}</span>
                        <span class="cal-sin">Pasado</span>
                    </div>
                @elseif ($dia['tiene_vuelos'])
                    @php
                        if ($fecha === $dia['fecha']) {
                            $claseSeleccionDia = 'seleccionado';
                        } else {
                            $claseSeleccionDia = '';
                        }

                        if ($dia['total_vuelos'] > 1) {
                            $sufijoPlural = 's';
                        } else {
                            $sufijoPlural = '';
                        }
                    @endphp
                    <div class="cal-celda seleccionable {{ $claseSeleccionDia }}"
                         data-fecha="{{ $dia['fecha'] }}"
                         data-origen="{{ $origen }}"
                         data-destino="{{ $destino }}">
                        <span class="cal-dia">{{ $dia['dia'] }}</span>
                        <span class="cal-vuelos">{{ $dia['total_vuelos'] }} vuelo{{ $sufijoPlural }}</span>
                    </div>
                @else
                    <div class="cal-celda sin-vuelo">
                        <span class="cal-dia">{{ $dia['dia'] }}</span>
                        <span class="cal-sin">Sin vuelos</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif

    <div class="results-section">
        <div class="results-header">
            <h2>Estado de vuelos</h2>
            @if ($numeroVuelo && $vuelos->count() > 0)
                <p>VUELO {{ $numeroVuelo }} - {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</p>
            @elseif ($ciudadOrigen && $ciudadDestino && $fecha)
                <p>VUELOS {{ strtoupper($origen) }} ({{ $ciudadOrigen->nombre }}) - {{ strtoupper($destino) }} ({{ $ciudadDestino->nombre }}) PARA EL DÍA {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</p>
            @elseif ($hayCalendario)
                <p>Haz clic en un día del calendario para ver los vuelos.</p>
            @else
                <p>Selecciona los filtros para consultar vuelos.</p>
            @endif
        </div>

        @if ($vuelos->count() > 0)
            <div class="list-headers">
                <div>Nº de vuelo</div>
                <div>Origen / Destino</div>
                <div>Salida</div>
                <div>Llegada</div>
                <div>Estado</div>
            </div>

            @foreach ($vuelos as $vuelo)
                @php
                    if (!empty($vuelo->numero_vuelo)) {
                        $codigoVueloVisual = $vuelo->numero_vuelo;
                    } elseif (!empty($vuelo->codigo)) {
                        $codigoVueloVisual = $vuelo->codigo;
                    } else {
                        $codigoVueloVisual = '—';
                    }

                    if (!empty($vuelo->hora_salida_programada)) {
                        $horaSalidaVisual = \Carbon\Carbon::parse($vuelo->hora_salida_programada)->format('H:i');
                    } else {
                        $horaSalidaVisual = \Carbon\Carbon::parse($vuelo->fecha_salida)->format('H:i');
                    }

                    if (!empty($vuelo->hora_llegada_programada)) {
                        $horaLlegadaVisual = \Carbon\Carbon::parse($vuelo->hora_llegada_programada)->format('H:i');
                    } elseif (!empty($vuelo->fecha_llegada)) {
                        $horaLlegadaVisual = \Carbon\Carbon::parse($vuelo->fecha_llegada)->format('H:i');
                    } else {
                        $horaLlegadaVisual = '—';
                    }
                @endphp
                <div class="flight-card" onclick="window.location.href='{{ route('flights.detail', $vuelo->id) }}'">
                    <div class="col-flight-num">
                        {{ $codigoVueloVisual }}
                        <span>{{ $vuelo->aerolinea->nombre }}</span>
                    </div>
                    <div class="col-route">
                        <div class="route-point">
                            <strong>{{ $vuelo->ruta->aeropuertoOrigen->codigo_iata }}</strong>
                            <span>{{ $vuelo->ruta->aeropuertoOrigen->ciudad }}</span>
                        </div>
                        <div class="route-line">
                            <hr><hr>
                        </div>
                        <div class="route-point">
                            <strong>{{ $vuelo->ruta->aeropuertoDestino->codigo_iata }}</strong>
                            <span>{{ $vuelo->ruta->aeropuertoDestino->ciudad }}</span>
                        </div>
                    </div>
                    <div class="col-time">{{ $horaSalidaVisual }}</div>
                    <div class="col-time">{{ $horaLlegadaVisual }}</div>
                    <div>
                        <div class="status-badge status-{{ $vuelo->estado->nombre }}">
                            {{ ucfirst(str_replace('_', ' ', $vuelo->estado->nombre)) }}
                        </div>
                    </div>
                </div>
            @endforeach
        @elseif ($numeroVuelo || ($origen && $destino && $fecha))
            <div class="sin-vuelos">
                <p style="font-size: 1.2rem; font-weight: 700;">No hay vuelos disponibles para esta búsqueda</p>
                <p>Prueba otra combinación de origen, destino o fecha.</p>
            </div>
        @else
            <div class="sin-vuelos">
                <p style="font-size: 1.2rem; font-weight: 700;">Introduce los datos de búsqueda</p>
                <p>Busca por número de vuelo o selecciona origen, destino y fecha.</p>
            </div>
        @endif
    </div>
    </div>

    <script>
        const apiCiudades = "{{ route('api.ciudades-origen') }}";
    </script>
    <script src="{{ asset('js/estado-vuelos.js') }}"></script>

    <div style="padding-bottom: 4rem;"></div>
@endsection
