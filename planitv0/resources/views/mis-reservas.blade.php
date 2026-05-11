@extends('cabecera')

@push('styles')
    <link href="{{ asset('css/mis-reservas.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('js/mis-reservas.js') }}"></script>
@endpush

@section('contenido')

@php
    $tabSolicitada = request('tab');
    if (in_array($tabSolicitada, ['proximas', 'voladas', 'canceladas'], true)) {
        $tabActiva = $tabSolicitada;
    } else {
        $tabActiva = 'proximas';
    }
    $enlaceErrorKeys = ['enlace', 'localizador_enlace', 'token_enlace'];
    $enlaceLocalizador = old('localizador_enlace', session('enlace_localizador', ''));
    $enlaceModalPreferido = session('enlace_modal');
    $enlaceExitoData = '0';
    if (session()->has('enlace_exito')) {
        $enlaceExitoData = '1';
    }
    $tokenEnlaceAntiguoData = '0';
    if (old('token_enlace')) {
        $tokenEnlaceAntiguoData = '1';
    }
    $localizadorEnlaceAntiguoData = '0';
    if (old('localizador_enlace')) {
        $localizadorEnlaceAntiguoData = '1';
    }
    $tieneErroresLocalizadorData = '0';
    if ($errors->has('localizador_enlace')) {
        $tieneErroresLocalizadorData = '1';
    }
    $enlaceErrors = collect($enlaceErrorKeys)
        ->flatMap(fn ($key) => $errors->get($key))
        ->filter()
        ->values();
    $erroresGenerales = collect($errors->keys())
        ->reject(fn ($key) => in_array($key, $enlaceErrorKeys, true))
        ->flatMap(fn ($key) => $errors->get($key))
        ->filter()
        ->values();
@endphp

<div class="row justify-content-center" id="mis-reservas-page"
    data-modal-preferido="{{ $enlaceModalPreferido }}"
    data-enlace-exito="{{ $enlaceExitoData }}"
    data-token-enlace-antiguo="{{ $tokenEnlaceAntiguoData }}"
    data-localizador-enlace-antiguo="{{ $localizadorEnlaceAntiguoData }}"
    data-tiene-errores-localizador="{{ $tieneErroresLocalizadorData }}">
    <div class="col-12 col-xl-11">

        <div class="mb-4">
            <h2 class="fw-bold mb-1">Mis Reservas</h2>
            <p class="text-muted">Consulta y gestiona la actividad de tus vuelos.</p>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-12 col-lg-3">
                <div class="card shadow-sm sticky-top account-sidebar-sticky">
                    <div class="card-header fw-semibold bg-white">
                        <i class="bi bi-layout-text-sidebar-reverse me-2 text-primary"></i>Gestionar cuenta
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="{{ url('/area-personal') }}"
                            class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-person-lines-fill"></i>
                            <span>Datos personales</span>
                        </a>
                        <a href="{{ route('mis-reservas.index') }}"
                            class="list-group-item list-group-item-action d-flex align-items-center gap-2 active"
                            aria-current="true">
                            <i class="bi bi-journal-text"></i>
                            <span>Mis Reservas</span>
                        </a>
                        <a href="{{ route('pasajeros-frecuentes.index') }}"
                            class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                            <i class="bi bi-people"></i>
                            <span>Pasajeros frecuentes</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-9">
                @if (session('exito'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('exito') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($erroresGenerales->isNotEmpty())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Corrige los siguientes errores:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach ($erroresGenerales as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="card mb-4 shadow-sm">
                    <div class="card-header fw-semibold bg-white">
                        <span><i class="bi bi-funnel me-2 text-primary"></i>Filtros de búsqueda</span>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('mis-reservas.index') }}" class="row g-3">
                            @php
                                $filtroLocalizador = '';
                                if (array_key_exists('localizador', $filtros)) {
                                    $filtroLocalizador = $filtros['localizador'];
                                }
                                $filtroFecha = '';
                                if (array_key_exists('fecha', $filtros)) {
                                    $filtroFecha = $filtros['fecha'];
                                }
                                $filtroOrigen = '';
                                if (array_key_exists('origen', $filtros)) {
                                    $filtroOrigen = $filtros['origen'];
                                }
                                $filtroDestino = '';
                                if (array_key_exists('destino', $filtros)) {
                                    $filtroDestino = $filtros['destino'];
                                }
                                $filtroReembolsoEstado = '';
                                if (array_key_exists('reembolso_estado', $filtros)) {
                                    $filtroReembolsoEstado = $filtros['reembolso_estado'];
                                }
                            @endphp
                            <input type="hidden" name="tab" id="tab-activa-input" value="{{ $tabActiva }}">
                            <div class="col-12 col-md-6">
                                <label for="localizador" class="form-label">Localizador</label>
                                <input type="text" class="form-control" id="localizador" name="localizador"
                                    value="{{ $filtroLocalizador }}" maxlength="20" placeholder="ABC123">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="fecha" class="form-label">Fecha de salida</label>
                                <input type="date" class="form-control" id="fecha" name="fecha"
                                    value="{{ $filtroFecha }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="origen" class="form-label">Origen</label>
                                <input type="text" class="form-control" id="origen" name="origen"
                                    value="{{ $filtroOrigen }}" maxlength="100" placeholder="Madrid">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="destino" class="form-label">Destino</label>
                                <input type="text" class="form-control" id="destino" name="destino"
                                    value="{{ $filtroDestino }}" maxlength="100" placeholder="Roma">
                            </div>
                            <div class="col-12 col-md-6 d-none" id="filtro-reembolso-estado">
                                <label for="reembolso_estado" class="form-label">Estado reembolso (canceladas)</label>
                                <select class="form-select" id="reembolso_estado" name="reembolso_estado">
                                    <option value="">Todos</option>
                                    <option value="pendiente" @selected($filtroReembolsoEstado === 'pendiente')>Pendiente</option>
                                    <option value="completado" @selected($filtroReembolsoEstado === 'completado')>Completado</option>
                                    <option value="no_aplicable" @selected($filtroReembolsoEstado === 'no_aplicable')>No aplicable</option>
                                </select>
                            </div>
                            <div class="col-12 d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-2"></i>Aplicar filtros
                                </button>
                                <a href="{{ route('mis-reservas.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card mb-3 shadow-sm border-secondary-subtle">
                    <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <p class="fw-semibold mb-0">¿Deseas enlazar una reserva a tu cuenta?</p>
                            <p class="text-muted small mb-0">Si hiciste una reserva en modo invitado, valídala con token para gestionarla desde esta sección.</p>
                        </div>
                        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalEnlaceLocalizador">
                            <i class="bi bi-link-45deg me-2"></i>Enlazar reserva
                        </button>
                    </div>
                </div>

                <div class="modal fade" id="modalEnlaceLocalizador" tabindex="-1" aria-labelledby="modalEnlaceLocalizadorLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalEnlaceLocalizadorLabel">
                                    <i class="bi bi-link-45deg me-2 text-primary"></i>Enlazar reserva a tu cuenta
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="enlace-intro-box mb-3">
                                    <p class="text-muted small mb-0">
                                        Introduce el localizador de la reserva para enviar el token al correo de contacto asociado.
                                    </p>
                                </div>

                                @if ($errors->has('localizador_enlace') || ($errors->has('enlace') && $enlaceModalPreferido !== 'token'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        <strong>Revisa el localizador:</strong>
                                        <ul class="mb-0 mt-1">
                                            @php
                                                $erroresLocalizador = collect($errors->get('localizador_enlace'));
                                                if ($enlaceModalPreferido !== 'token') {
                                                    $erroresLocalizador = $erroresLocalizador->merge($errors->get('enlace'));
                                                }
                                            @endphp
                                            @foreach ($erroresLocalizador->filter() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label for="localizador_enlace_modal" class="form-label fw-semibold">Localizador de reserva</label>
                                    <input
                                        type="text"
                                        @class(['form-control input-localizador-enlace', 'is-invalid' => $errors->has('localizador_enlace')])
                                        id="localizador_enlace_paso1"
                                        value="{{ $enlaceLocalizador }}"
                                        maxlength="20"
                                        placeholder="ABC123"
                                        required>
                                    @if ($errors->has('localizador_enlace'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('localizador_enlace') }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <form id="form-solicitar-enlace" method="POST" action="{{ route('mis-reservas.enlace.solicitar') }}">
                                    @csrf
                                    <input type="hidden" name="localizador_enlace" id="localizador_enlace_hidden_solicitar">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-send me-1"></i>Enviar token
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalEnlaceToken" tabindex="-1" aria-labelledby="modalEnlaceTokenLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalEnlaceTokenLabel">Verificar token de enlace</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <p class="text-muted small mb-3">
                                    Introduce el token recibido y pulsa Enlazar. Si no te llega, puedes reenviarlo desde aquí.
                                </p>

                                @if (session('enlace_exito'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('enlace_exito') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                @if ($errors->has('token_enlace') || ($errors->has('enlace') && $enlaceModalPreferido === 'token'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                        <strong>Revisa el token:</strong>
                                        <ul class="mb-0 mt-1">
                                            @php
                                                $erroresToken = collect($errors->get('token_enlace'));
                                                if ($enlaceModalPreferido === 'token') {
                                                    $erroresToken = $erroresToken->merge($errors->get('enlace'));
                                                }
                                            @endphp
                                            @foreach ($erroresToken->filter() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label for="localizador_enlace_paso2" class="form-label">Localizador de reserva</label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="localizador_enlace_paso2"
                                        value="{{ $enlaceLocalizador }}"
                                        maxlength="20"
                                        readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="token_enlace_modal" class="form-label">Token</label>
                                    <input
                                        type="text"
                                        @class(['form-control', 'is-invalid' => $errors->has('token_enlace')])
                                        id="token_enlace_modal"
                                        value="{{ old('token_enlace') }}"
                                        maxlength="20"
                                        placeholder="123456"
                                        required>
                                    @if ($errors->has('token_enlace'))
                                        <div class="invalid-feedback d-block">{{ $errors->first('token_enlace') }}</div>
                                    @endif
                                </div>

                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <form id="form-reenviar-enlace" method="POST" action="{{ route('mis-reservas.enlace.reenviar') }}">
                                        @csrf
                                        <input type="hidden" name="localizador_enlace" id="localizador_enlace_hidden_reenviar">
                                        <span class="text-muted small">¿No recibiste el código?</span>
                                        <button type="submit" class="btn btn-link btn-sm p-0 align-baseline text-decoration-none">
                                            Reenviar código
                                        </button>
                                    </form>
                                </div>

                                <p class="small text-muted mt-3 mb-0">Token de un solo uso, 15 min de validez, 5 intentos maximos y bloqueo de 5 minutos.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <form id="form-verificar-enlace" method="POST" action="{{ route('mis-reservas.enlace.verificar') }}">
                                    @csrf
                                    <input type="hidden" name="localizador_enlace" id="localizador_enlace_hidden_verificar">
                                    <input type="hidden" name="token_enlace" id="token_enlace_hidden_verificar">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-shield-check me-1"></i>Enlazar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-pills nav-fill gap-2 mb-3 reservas-tabs" id="reservasTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button @class(['nav-link', 'active' => $tabActiva === 'proximas']) id="proximas-tab" data-bs-toggle="tab" data-bs-target="#proximas" type="button" role="tab">
                            Próximas reservas <span class="badge text-bg-primary ms-1">{{ $proximas->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button @class(['nav-link', 'active' => $tabActiva === 'voladas']) id="voladas-tab" data-bs-toggle="tab" data-bs-target="#voladas" type="button" role="tab">
                            Reservas voladas <span class="badge text-bg-secondary ms-1">{{ $voladas->count() }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button @class(['nav-link', 'active' => $tabActiva === 'canceladas']) id="canceladas-tab" data-bs-toggle="tab" data-bs-target="#canceladas" type="button" role="tab">
                            Reservas canceladas <span class="badge text-bg-dark ms-1">{{ $canceladas->count() }}</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="reservasTabsContent">
                    <div @class(['tab-pane fade', 'show active' => $tabActiva === 'proximas']) id="proximas" role="tabpanel" aria-labelledby="proximas-tab">
                        @include('partials.reservas-lista', ['reservas' => $proximas, 'tipo' => 'proximas'])
                    </div>
                    <div @class(['tab-pane fade', 'show active' => $tabActiva === 'voladas']) id="voladas" role="tabpanel" aria-labelledby="voladas-tab">
                        @include('partials.reservas-lista', ['reservas' => $voladas, 'tipo' => 'voladas'])
                    </div>
                    <div @class(['tab-pane fade', 'show active' => $tabActiva === 'canceladas']) id="canceladas" role="tabpanel" aria-labelledby="canceladas-tab">
                        @include('partials.reservas-lista', ['reservas' => $canceladas, 'tipo' => 'canceladas'])
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
