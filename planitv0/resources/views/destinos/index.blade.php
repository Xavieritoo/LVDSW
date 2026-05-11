@extends('cabecera')

@push('styles')
    <link href="{{ asset('css/destinos.css') }}" rel="stylesheet">
@endpush

@section('contenido')
    @php
        $minMesVueltaFiltro = $mesMinimo->copy();
    @endphp

    <div id="notificaciones-destinos" class="notificaciones-destinos" aria-live="polite" aria-atomic="true"></div>

    <!-- Buscador de vuelos -->
    <div class="busqueda-vuelos">
        <h2>Planea tu vuelo</h2>
        <form id="destinos-form" method="GET" action="{{ route('destinos.index') }}" class="campos-busqueda">
            <!-- Campo Origen -->
            <div class="campo-busqueda">
                <label>De</label>
                <button type="button" class="btn-campo" id="btn-origen" data-toggle="modal" data-target="#modal-origen">
                    <span id="origen-texto">Selecciona ciudad</span>
                </button>
                <input type="hidden" id="origen-id" name="origen_id" value="@if(isset(optional($origen)->id)){{ optional($origen)->id }}@endif">
                <input type="hidden" id="origen-buscar" name="origen_buscar" value="{{ $origenBuscado }}">
            </div>

            <!-- Campo Destino -->
            <div class="campo-busqueda">
                <label>A</label>
                <button type="button" class="btn-campo" id="btn-destino" data-toggle="modal" data-target="#modal-destino">
                    <span id="destino-texto">@if($destinoSeleccionado){{ preg_replace('/^Aeropuerto\s+(de[l]?\s+)?/i', '', $destinoSeleccionado->nombre) }}@else Selecciona ciudad @endif</span>
                </button>
                <input type="hidden" id="destino-id" name="destino_id" value="@if(isset(optional($destinoSeleccionado)->id)){{ optional($destinoSeleccionado)->id }}@endif">
                <input type="hidden" id="destino-buscar" name="destino_buscar" value="{{ $destinoBuscado }}">
            </div>

            <!-- Campo Ida -->
            <div class="campo-busqueda">
                <label>Ida</label>
                <input
                    type="month"
                    id="input-mes-ida"
                    name="mes_ida"
                    class="form-control"
                    value="{{ $mesSeleccionadoIda }}"
                    min="{{ $mesMinimo->format('Y-m') }}"
                    max="{{ $mesMaximo->format('Y-m') }}"
                >
            </div>

            <!-- Campo Vuelta -->
            <div class="campo-busqueda">
                <label>Vuelta</label>
                <input
                    type="month"
                    id="input-mes-vuelta"
                    name="mes_vuelta"
                    class="form-control"
                    value="{{ $mesSeleccionadoVuelta }}"
                    min="{{ $mesMinimo->format('Y-m') }}"
                    max="{{ $mesMaximo->format('Y-m') }}"
                >
            </div>

            <input type="hidden" id="input-fecha-ida" name="fecha_ida" value="{{ request('fecha_ida', '') }}">
            <input type="hidden" id="input-fecha-vuelta" name="fecha_vuelta" value="{{ request('fecha_vuelta', '') }}">

            <!-- Campo Pasajeros -->
            <div class="campo-busqueda pasajeros-wrapper">
                <label>Pasajeros</label>
                <button type="button" id="btn-pasajeros" class="btn-pasajeros">
                    <small id="pasajeros-resumen">1 Adulto, 0 Niños, 0 Bebés</small>
                    <span id="pasajeros-total">1 pasajero</span>
                </button>

                <div id="panel-pasajeros" class="panel-pasajeros">
                    <div class="panel-pasajeros-header">Pasajeros</div>

                    <div class="pasajero-row">
                        <div class="pasajero-info">
                            <strong>Adultos</strong>
                            <div class="edad">16 años o más al volar</div>
                        </div>
                        <div class="contador">
                            <button type="button" class="btn-cantidad" data-tipo="adultos" data-op="restar">-</button>
                            <span class="cantidad-valor" id="valor-adultos">1</span>
                            <button type="button" class="btn-cantidad" data-tipo="adultos" data-op="sumar">+</button>
                        </div>
                    </div>

                    <div class="pasajero-row">
                        <div class="pasajero-info">
                            <strong>Niños</strong>
                            <div class="edad">2 - 15 años al volar</div>
                            <button type="button" class="ayuda" data-modal="modal-menores">Menores no acompañados ⓘ</button>
                        </div>
                        <div class="contador">
                            <button type="button" class="btn-cantidad" data-tipo="ninos" data-op="restar">-</button>
                            <span class="cantidad-valor" id="valor-ninos">0</span>
                            <button type="button" class="btn-cantidad" data-tipo="ninos" data-op="sumar">+</button>
                        </div>
                    </div>

                    <div class="pasajero-row">
                        <div class="pasajero-info">
                            <strong>Bebés</strong>
                            <div class="edad">Hasta 2 años al volar</div>
                            <button type="button" class="ayuda" data-modal="modal-bebes">Más info ⓘ</button>
                        </div>
                        <div class="contador">
                            <button type="button" class="btn-cantidad" data-tipo="bebes" data-op="restar">-</button>
                            <span class="cantidad-valor" id="valor-bebes">0</span>
                            <button type="button" class="btn-cantidad" data-tipo="bebes" data-op="sumar">+</button>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="adultos" id="input-adultos" value="{{ max(0, (int) request('adultos', 1)) }}">
                <input type="hidden" name="ninos" id="input-ninos" value="{{ max(0, (int) request('ninos', 0)) }}">
                <input type="hidden" name="bebes" id="input-bebes" value="{{ max(0, (int) request('bebes', 0)) }}">
            </div>

            <!-- Botón Buscar -->
            <div class="campo-busqueda">
                <button type="submit" class="btn btn-buscar">Buscar</button>
            </div>
        </form>
    </div>

    <!-- Modal Origen -->
    <div id="modal-origen" class="modal-ciudades">
        <div class="modal-ciudades-contenido">
            <div class="modal-ciudades-header">
                <h3>Selecciona ciudad de origen</h3>
                <button class="modal-ciudades-close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-ciudades-busqueda">
                <input type="text" id="input-buscar-origen" placeholder="Busca ciudad o código IATA...">
            </div>
            <div class="modal-ciudades-lista" id="lista-origen">
                <!-- Cargado por JavaScript -->
            </div>
        </div>
    </div>

    <!-- Modal Destino -->
    <div id="modal-destino" class="modal-ciudades">
        <div class="modal-ciudades-contenido">
            <div class="modal-ciudades-header">
                <h3>Selecciona ciudad de destino</h3>
                <button class="modal-ciudades-close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-ciudades-busqueda">
                <input type="text" id="input-buscar-destino" placeholder="Busca ciudad o código IATA...">
            </div>
            <div class="modal-ciudades-lista" id="lista-destino">
                <!-- Cargado por JavaScript -->
            </div>
        </div>
    </div>

    @if (!$origen)
        <div class="alert alert-info">Selecciona una ciudad de origen para consultar vuelos y calendario.</div>
    @endif

    @if ($origen && $destinoSeleccionado)
        @php
            $hoyCalendario = now()->startOfDay();
            $hayCalendarioIda = $calendarioMesIda && !empty($calendarioMesesIda);
            $hayCalendarioVuelta = $calendarioMesVuelta && !empty($calendarioMesesVuelta);
        @endphp
        @if ($hayCalendarioIda || $hayCalendarioVuelta)
            <div class="calendario-ofertas">
                <div class="calendario-doble-grid">
                    @if ($hayCalendarioIda)
                        @php
                            $inicioMesIda = $calendarioMesIda->copy()->startOfMonth();
                            $diasMesIda = (int) $calendarioMesIda->copy()->endOfMonth()->format('j');
                            $primerDiaSemanaIda = (int) $inicioMesIda->isoWeekday();
                        @endphp
                        <section class="calendario-panel">
                            <div class="calendario-header">
                                <div>
                                    <h3 class="calendario-ruta">
                                        {{ preg_replace('/^Aeropuerto\s+(de[l]?\s+)?/i', '', $origen->nombre) }}
                                        ({{ $origen->codigo_iata }}) -
                                        {{ preg_replace('/^Aeropuerto\s+(de[l]?\s+)?/i', '', $destinoSeleccionado->nombre) }}
                                        ({{ $destinoSeleccionado->codigo_iata }})
                                    </h3>
                                    <p class="calendario-mes">Ida</p>
                                </div>
                            </div>

                            <div class="cal-meses" data-trayecto="ida">
                                @foreach ($calendarioMesesIda as $claveMes => $mesData)
                                    @php
                                        $paramsMes = [
                                            'origen_id' => optional($origen)->id,
                                            'origen_buscar' => $origenBuscado,
                                            'destino_id' => optional($destinoSeleccionado)->id,
                                            'destino_buscar' => $destinoBuscado,
                                            'adultos' => request('adultos', 1),
                                            'ninos' => request('ninos', 0),
                                            'bebes' => request('bebes', 0),
                                            'mes_ida' => $claveMes,
                                            'mes_vuelta' => $mesSeleccionadoVuelta,
                                        ];
                                    @endphp
                                    <a href="{{ route('destinos.index', $paramsMes) }}" class="cal-mes-tab @if($mesSeleccionadoIda === $claveMes) activo @endif">
                                        <div class="cal-mes-precio" data-trayecto="ida" @if($mesData['min'] !== null) data-precio-base="{{ number_format($mesData['min'], 2, '.', '') }}" @endif>
                                            @if($mesData['min'] !== null) {{ number_format($mesData['min'], 2, ',', '.') . ' EUR' }} @else -- @endif
                                        </div>
                                        <div class="cal-mes-info">
                                            {{ $mesData['mes']->translatedFormat('M') }}<br>{{ $mesData['mes']->translatedFormat('Y') }}
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            <div class="cal-semana">
                                <span>Lun</span><span>Mar</span><span>Mie</span><span>Jue</span><span>Vie</span><span>Sab</span><span>Dom</span>
                            </div>

                            <div class="cal-grid" data-trayecto="ida">
                                @for ($i = 1; $i < $primerDiaSemanaIda; $i++)
                                    <div class="cal-celda vacia"></div>
                                @endfor

                                @for ($dia = 1; $dia <= $diasMesIda; $dia++)
                                    @php
                                        $fechaCelda = $calendarioMesIda->copy()->day($dia)->startOfDay();
                                        $esPasado = $fechaCelda->lt($hoyCalendario);
                                        if (isset($preciosPorDiaIda[$dia])) {
                                            $precioDia = $preciosPorDiaIda[$dia];
                                        } else {
                                            $precioDia = null;
                                        }
                                        $esMejorDia = !$esPasado && $precioDia !== null && $mejorDiaIda === $dia;
                                        $sinVuelo = !$esPasado && $precioDia === null;
                                        $esSeleccionable = !$esPasado && $precioDia !== null;
                                    @endphp
                                    <div
                                        class="cal-celda @if($esPasado) pasado @endif @if($esMejorDia) mejor @endif @if($sinVuelo) sin-vuelo @endif @if($esSeleccionable) seleccionable @endif"
                                        @if ($esSeleccionable)
                                            data-seleccionable="1"
                                            data-trayecto="ida"
                                            data-dia="{{ $dia }}"
                                            data-precio="{{ number_format($precioDia, 2, '.', '') }}"
                                            data-fecha="{{ $fechaCelda->format('d/m/Y') }}"
                                            data-fecha-iso="{{ $fechaCelda->format('Y-m-d') }}"
                                        @endif
                                    >
                                        <span class="cal-dia">{{ $dia }}</span>
                                        @if ($esPasado)
                                            <span class="cal-sin">&nbsp;</span>
                                        @elseif ($precioDia !== null)
                                            <span class="cal-precio">{{ number_format($precioDia, 2, ',', '.') }} EUR</span>
                                        @else
                                            <span class="cal-sin">No hay vuelos disponibles</span>
                                        @endif
                                    </div>
                                @endfor
                            </div>
                        </section>
                    @endif

                    @if ($hayCalendarioVuelta)
                        @php
                            $inicioMesVuelta = $calendarioMesVuelta->copy()->startOfMonth();
                            $diasMesVuelta = (int) $calendarioMesVuelta->copy()->endOfMonth()->format('j');
                            $primerDiaSemanaVuelta = (int) $inicioMesVuelta->isoWeekday();
                        @endphp
                        <section class="calendario-panel">
                            <div class="calendario-header">
                                <div>
                                    <h3 class="calendario-ruta">
                                        {{ preg_replace('/^Aeropuerto\s+(de[l]?\s+)?/i', '', $destinoSeleccionado->nombre) }}
                                        ({{ $destinoSeleccionado->codigo_iata }}) -
                                        {{ preg_replace('/^Aeropuerto\s+(de[l]?\s+)?/i', '', $origen->nombre) }}
                                        ({{ $origen->codigo_iata }})
                                    </h3>
                                    <p class="calendario-mes">Vuelta</p>
                                </div>
                            </div>

                            <div class="cal-meses" data-trayecto="vuelta">
                                @foreach ($calendarioMesesVuelta as $claveMes => $mesData)
                                    @php
                                        $paramsMes = [
                                            'origen_id' => optional($origen)->id,
                                            'origen_buscar' => $origenBuscado,
                                            'destino_id' => optional($destinoSeleccionado)->id,
                                            'destino_buscar' => $destinoBuscado,
                                            'adultos' => request('adultos', 1),
                                            'ninos' => request('ninos', 0),
                                            'bebes' => request('bebes', 0),
                                            'mes_ida' => $mesSeleccionadoIda,
                                            'mes_vuelta' => $claveMes,
                                        ];
                                    @endphp
                                    <a href="{{ route('destinos.index', $paramsMes) }}" class="cal-mes-tab @if($mesSeleccionadoVuelta === $claveMes) activo @endif">
                                        <div class="cal-mes-precio" data-trayecto="vuelta" @if($mesData['min'] !== null) data-precio-base="{{ number_format($mesData['min'], 2, '.', '') }}" @endif>
                                            @if($mesData['min'] !== null) {{ number_format($mesData['min'], 2, ',', '.') . ' EUR' }} @else -- @endif
                                        </div>
                                        <div class="cal-mes-info">
                                            {{ $mesData['mes']->translatedFormat('M') }}<br>{{ $mesData['mes']->translatedFormat('Y') }}
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            <div class="cal-semana">
                                <span>Lun</span><span>Mar</span><span>Mie</span><span>Jue</span><span>Vie</span><span>Sab</span><span>Dom</span>
                            </div>

                            <div class="cal-grid" data-trayecto="vuelta">
                                @for ($i = 1; $i < $primerDiaSemanaVuelta; $i++)
                                    <div class="cal-celda vacia"></div>
                                @endfor

                                @for ($dia = 1; $dia <= $diasMesVuelta; $dia++)
                                    @php
                                        $fechaCelda = $calendarioMesVuelta->copy()->day($dia)->startOfDay();
                                        $esPasado = $fechaCelda->lt($hoyCalendario);
                                        if (isset($preciosPorDiaVuelta[$dia])) {
                                            $precioDia = $preciosPorDiaVuelta[$dia];
                                        } else {
                                            $precioDia = null;
                                        }
                                        $esMejorDia = !$esPasado && $precioDia !== null && $mejorDiaVuelta === $dia;
                                        $sinVuelo = !$esPasado && $precioDia === null;
                                        $esSeleccionable = !$esPasado && $precioDia !== null;
                                    @endphp
                                    <div
                                        class="cal-celda @if($esPasado) pasado @endif @if($esMejorDia) mejor @endif @if($sinVuelo) sin-vuelo @endif @if($esSeleccionable) seleccionable @endif"
                                        @if ($esSeleccionable)
                                            data-seleccionable="1"
                                            data-trayecto="vuelta"
                                            data-dia="{{ $dia }}"
                                            data-precio="{{ number_format($precioDia, 2, '.', '') }}"
                                            data-fecha="{{ $fechaCelda->format('d/m/Y') }}"
                                            data-fecha-iso="{{ $fechaCelda->format('Y-m-d') }}"
                                        @endif
                                    >
                                        <span class="cal-dia">{{ $dia }}</span>
                                        @if ($esPasado)
                                            <span class="cal-sin">&nbsp;</span>
                                        @elseif ($precioDia !== null)
                                            <span class="cal-precio">{{ number_format($precioDia, 2, ',', '.') }} EUR</span>
                                        @else
                                            <span class="cal-sin">No hay vuelos disponibles</span>
                                        @endif
                                    </div>
                                @endfor
                            </div>
                        </section>
                    @endif
                </div>

                <div class="resumen-oferta-seleccionada" id="resumen-oferta-seleccionada">
                    <div class="resumen-ruta">
                        <h4>{{ preg_replace('/^Aeropuerto\s+(de[l]?\s+)?/i', '', $origen->nombre) }} - {{ preg_replace('/^Aeropuerto\s+(de[l]?\s+)?/i', '', $destinoSeleccionado->nombre) }}</h4>
                        <div id="resumen-pasajeros-texto"></div>
                    </div>
                    <div class="resumen-col">
                        <small>Ida:</small>
                        <div id="resumen-fecha-ida">@if($hayCalendarioIda && $mejorDiaIda){{ $calendarioMesIda->copy()->day($mejorDiaIda)->format('d/m/Y') }}@else -- @endif</div>
                    </div>
                    <div class="resumen-col">
                        <small>Vuelta:</small>
                        <div id="resumen-fecha-vuelta">@if($hayCalendarioVuelta && $mejorDiaVuelta){{ $calendarioMesVuelta->copy()->day($mejorDiaVuelta)->format('d/m/Y') }}@else -- @endif</div>
                        @if ($hayCalendarioVuelta)
                            <button type="button" id="btn-quitar-vuelta" class="btn-quitar-vuelta">Quitar vuelta</button>
                        @endif
                    </div>
                    <div class="resumen-col resumen-total">
                        <small>Total:</small>
                        <strong id="resumen-precio">-- EUR</strong>
                    </div>
                    <button type="button" class="btn-continuar-oferta">CONTINUAR</button>
                </div>
            </div>
        @else
            <div class="alert alert-secondary">No hay vuelos disponibles desde hoy para esta ruta.</div>
        @endif
    @endif

    <section class="tips-vuelos">
        <h3>Consejos para conseguir los mejores precios en tus vuelos</h3>
        <p class="tips-subtitulo">
            En PlanIt te damos algunas recomendaciones para que puedas volar gastando lo menos posible. Pequeños cambios en tu forma de buscar pueden marcar la diferencia.
        </p>

        <div class="tips-grid">
            <article class="tip-item">
                <h4><span class="tip-icono"><i class="bi bi-check-lg"></i></span> Juega con las fechas</h4>
                <p>Si tu viaje no depende de días concretos, prueba a mover las fechas unos días antes o después. A veces un solo día de diferencia cambia bastante el precio.</p>
            </article>

            <article class="tip-item">
                <h4><span class="tip-icono"><i class="bi bi-check-lg"></i></span> Estate atento a las promos</h4>
                <p>De vez en cuando publicamos descuentos y promociones especiales. Revisa la sección de ofertas con frecuencia porque suelen tener plazas limitadas.</p>
            </article>

            <article class="tip-item">
                <h4><span class="tip-icono"><i class="bi bi-check-lg"></i></span> Reserva con tiempo</h4>
                <p>Cuanto antes compres el billete, más probable es que encuentres un buen precio. A medida que se acerca la fecha del vuelo, las tarifas tienden a subir.</p>
            </article>

            <article class="tip-item">
                <h4><span class="tip-icono"><i class="bi bi-check-lg"></i></span> Evita las fechas punta</h4>
                <p>Fuera de puentes, festivos y vacaciones escolares los precios bajan considerablemente. Si puedes viajar en temporada baja, tu bolsillo lo notará.</p>
            </article>

            <article class="tip-item">
                <h4><span class="tip-icono"><i class="bi bi-check-lg"></i></span> Horarios y días con menos demanda</h4>
                <p>Los vuelos entre semana y en horarios intermedios suelen tener menos pasajeros y, por tanto, precios más ajustados que los de fin de semana o primera hora.</p>
            </article>

            <article class="tip-item">
                <h4><span class="tip-icono"><i class="bi bi-check-lg"></i></span> Añade tu equipaje durante el check-in</h4>
                <p>Si sabes que vas a necesitar maleta extra, contrátala online antes del vuelo. Hacerlo en el aeropuerto el mismo día suele salir bastante más caro.</p>
            </article>
        </div>
    </section>

    <section class="inspirate-ofertas" id="inspirate-ofertas">
        <form method="GET" action="{{ route('destinos.index') }}" id="form-inspirate">
            <input type="hidden" name="origen_id" value="{{ request('origen_id') }}">
            <input type="hidden" name="origen_buscar" value="{{ $origenBuscado }}">
            <input type="hidden" name="destino_id" value="{{ request('destino_id') }}">
            <input type="hidden" name="destino_buscar" value="{{ $destinoBuscado }}">
            <input type="hidden" name="adultos" value="{{ request('adultos', 1) }}">
            <input type="hidden" name="ninos" value="{{ request('ninos', 0) }}">
            <input type="hidden" name="bebes" value="{{ request('bebes', 0) }}">
            <input type="hidden" name="mes_ida" value="{{ $mesSeleccionadoIda }}">
            <input type="hidden" name="mes_vuelta" value="{{ $mesSeleccionadoVuelta }}">
            <input type="hidden" name="origen_oferta_id" id="input-origen-oferta-id" value="{{ $origenOfertaSeleccionado }}">

            <div class="inspirate-cabecera">
                <h3>Ofertas desde</h3>
                @php
                    $ciudadSeleccionadaOferta = $origenesOfertas->firstWhere('id', (int) $origenOfertaSeleccionado);
                @endphp
                <div class="selector-origen-oferta" id="selector-origen-oferta">
                    <button type="button" class="btn-selector-origen" id="btn-selector-origen">
                        <span id="texto-selector-origen">@if($ciudadSeleccionadaOferta) {{ str_replace(['Aeropuerto de ', 'Aeropuerto del ', 'Aeropuerto '], '', $ciudadSeleccionadaOferta->nombre) }} @else Selecciona ciudad @endif</span>
                        <span>&#9662;</span>
                    </button>
                    <div class="lista-origen-oferta" id="lista-origen-oferta">
                        @foreach ($origenesOfertas as $ciudadOrigenOferta)
                            <button
                                type="button"
                                class="opcion-origen-oferta"
                                data-id="{{ $ciudadOrigenOferta->id }}"
                                data-nombre="{{ preg_replace('/^Aeropuerto\s+(de[l]?\s+)?/i', '', $ciudadOrigenOferta->nombre) }}"
                            >
                                {{ preg_replace('/^Aeropuerto\s+(de[l]?\s+)?/i', '', $ciudadOrigenOferta->nombre) }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </form>

        @if ($ofertasInspirate->isNotEmpty())
            <div class="inspirate-grid">
                @foreach ($ofertasInspirate as $ofertaInspirate)
                    @php
                        $destinoOferta = $ofertaInspirate['destino'];
                        $nombreDestino = strtoupper(preg_replace('/^Aeropuerto\s+(de[l]?\s+)?/i', '', $destinoOferta->nombre));
                        $hue = abs(crc32($destinoOferta->codigo_iata)) % 360;
                        $fondo = "linear-gradient(135deg, hsl({$hue}, 72%, 56%), hsl(" . (($hue + 36) % 360) . ", 72%, 64%))";
                        $urlAplicarOferta = route('destinos.index', [
                            'origen_id' => $ofertaInspirate['origen_id'],
                            'destino_id' => $destinoOferta->id,
                            'adultos' => request('adultos', 1),
                            'ninos' => request('ninos', 0),
                            'bebes' => request('bebes', 0),
                            'mes_ida' => $mesSeleccionadoIda,
                            'mes_vuelta' => $mesSeleccionadoVuelta,
                            'origen_oferta_id' => $origenOfertaSeleccionado,
                        ]);
                    @endphp
                    <article class="inspirate-card">
                        <div class="inspirate-imagen"
                             @if($destinoOferta->imagen)
                             style="background-image: url('{{ $destinoOferta->imagen }}'); background-size: cover; background-position: center;"
                             @else
                             style="background: {{ $fondo }};"
                             @endif
                             aria-label="{{ $nombreDestino }}"></div>
                        <div class="inspirate-contenido">
                            <h4 class="inspirate-destino">{{ $nombreDestino }}</h4>
                            <div class="inspirate-bottom">
                                <div>
                                    <div class="inspirate-precio">{{ number_format($ofertaInspirate['precio'], 2, ',', '.') }} EUR</div>
                                </div>
                                <div class="inspirate-acciones">
                                    <a class="inspirate-detalle" href="{{ route('destinos.show', $destinoOferta->id) }}?origen_id={{ $ofertaInspirate['origen_id'] }}">Detalles del destino</a>
                                    <a class="inspirate-link" href="{{ $urlAplicarOferta }}" aria-label="Aplicar oferta al buscador">&rsaquo;</a>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="alert alert-secondary">No hay ofertas activas para la ciudad de origen seleccionada.</div>
        @endif
    </section>



    <div id="modal-menores" class="modal-info">
        <div class="modal-info-box">
            <div class="modal-info-titulo">Menores no acompañados</div>
            <div class="modal-info-contenido">
                <p>
                    Los menores que en el momento del vuelo tengan <strong>menos de 12 años deben viajar siempre acompañados por un adulto</strong>.
                </p>
                <p>
                    Si en el momento del vuelo el menor no cumple los requisitos indicados no podrá embarcar en el vuelo.
                </p>
            </div>
            <div class="modal-info-acciones">
                <button type="button" class="btn-aceptar-info" data-cerrar-modal="modal-menores">ENTENDIDO</button>
            </div>
        </div>
    </div>

    <div id="modal-bebes" class="modal-info">
        <div class="modal-info-box">
            <div class="modal-info-titulo">Volar con bebés</div>
            <div class="modal-info-contenido">
                <p>
                    <strong>Los menores de 2 años deben viajar siempre acompañados por un adulto</strong> y vuelan sobre su regazo.
                    El día del vuelo nuestro personal te entregará un cinturón especial para el pequeño.
                </p>
            </div>
            <div class="modal-info-acciones">
                <button type="button" class="btn-aceptar-info" data-cerrar-modal="modal-bebes">ACEPTAR</button>
            </div>
        </div>
    </div>

    <script>
        const apiOrigen = "{{ route('api.ciudades-origen') }}";
        const apiDestino = "{{ route('api.ciudades-destino') }}";
        const urlResultados = "{{ route('flight.results') }}";
        const origenInicial = @if($origen) {
            id: {{ $origen->id }},
            nombre: "{{ addslashes(preg_replace('/^Aeropuerto\s+(de[l]?\s+)?/i', '', $origen->nombre)) }}",
            pais: "{{ addslashes($origen->pais) }}",
            codigo_iata: "{{ $origen->codigo_iata }}"
        } @else null @endif;
        const destinoInicial = @if($destinoSeleccionado) {
            id: {{ $destinoSeleccionado->id }},
            nombre: "{{ addslashes(preg_replace('/^Aeropuerto\s+(de[l]?\s+)?/i', '', $destinoSeleccionado->nombre)) }}"
        } @else null @endif;
    </script>

@push('scripts')
    <script src="{{ asset('js/destinos.js') }}"></script>
@endpush

@endsection
