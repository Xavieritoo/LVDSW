@extends('cabecera')

@push('styles')
    <link href="{{ asset('css/mis-reservas.css') }}" rel="stylesheet">
@endpush

@section('contenido')

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="mis-viajes-hero mb-4">
            <div class="mis-viajes-hero-copy">
                <span class="mis-viajes-kicker">Area personal de reservas</span>
                <h2 class="fw-bold mb-2">Mis viajes</h2>
                <p class="mis-viajes-lead mb-2">
                    Consulta y gestiona todas tus reservas de vuelo en un mismo lugar.
                </p>
                <div class="mis-viajes-summary">
                    <p class="mb-0">Desde aqui puedes ver detalles, realizar el check-in online, descargar tarjetas de embarque, cancelar vuelos o consultar el estado de tus viajes.</p>
                    <p class="mb-0">Si compraste como invitado, accede a tu reserva usando el localizador y el email de contacto.</p>
                </div>
            </div>
            <div class="mis-viajes-hero-panel">
                <div class="hero-panel-item">
                    <i class="bi bi-search"></i>
                    <span>Localiza tu reserva</span>
                </div>
                <div class="hero-panel-item">
                    <i class="bi bi-clipboard-check"></i>
                    <span>Haz check-in online</span>
                </div>
                <div class="hero-panel-item">
                    <i class="bi bi-file-earmark-pdf"></i>
                    <span>Descarga tus tarjetas</span>
                </div>
                <div class="hero-panel-item">
                    <i class="bi bi-airplane"></i>
                    <span>Consulta el estado del viaje</span>
                </div>
            </div>
        </div>

        @if (session('exito'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('exito') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="guest-search-box card mb-4 shadow-sm border-0">
            <div class="card-header fw-semibold bg-white border-0 pb-2" style="background: linear-gradient(90deg,#f6f8ff 0%,#fff 100%); border-radius: 1rem 1rem 0 0;">
                <div class="guest-search-heading">
                    <span><i class="bi bi-search me-2 text-primary"></i>Buscar reserva de invitado</span>
                    <small>Accede con tu localizador y el email usado en la compra.</small>
                </div>
            </div>
            <div class="card-body pt-3">
                <form method="GET" action="{{ route('mis-viajes.index') }}" class="guest-search-form">
                    @php
                        $localizadorFiltro = '';
                        if (array_key_exists('localizador', $filtros)) {
                            $localizadorFiltro = $filtros['localizador'];
                        }
                        $emailFiltro = '';
                        if (array_key_exists('email_contacto', $filtros)) {
                            $emailFiltro = $filtros['email_contacto'];
                        }
                    @endphp
                    <div class="guest-search-fields">
                        <div class="guest-search-field">
                            <label for="localizador" class="form-label">Localizador</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-upc-scan text-primary"></i></span>
                                <input type="text" class="form-control rounded-end input-localizador-enlace" id="localizador" name="localizador"
                                    value="{{ old('localizador', $localizadorFiltro) }}" maxlength="20" placeholder="ABC123" required>
                            </div>
                            <div class="form-text">Introduce el codigo de reserva tal y como aparece en tu confirmacion.</div>
                        </div>
                        <div class="guest-search-field">
                            <label for="email_contacto" class="form-label">Email de contacto</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-envelope text-primary"></i></span>
                                <input type="email" class="form-control rounded-end" id="email_contacto" name="email_contacto"
                                    value="{{ old('email_contacto', $emailFiltro) }}" maxlength="150" placeholder="correo@ejemplo.com" required>
                            </div>
                            <div class="form-text">Usa el mismo email asociado a la compra de la reserva.</div>
                        </div>
                    </div>
                    <div class="guest-search-actions">
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm">
                            <i class="bi bi-search me-2"></i>Buscar reserva
                        </button>
                        <a href="{{ route('mis-viajes.index') }}" class="btn btn-outline-secondary px-4 py-2 fw-semibold">
                            <i class="bi bi-arrow-clockwise me-1"></i>Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        @if ($reserva)
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                        <div class="flex-grow-1">
                            <h5 class="mb-3 fw-bold">
                                {{ $reserva->origen }}
                                <i class="bi bi-arrow-right mx-2 text-primary"></i>
                                {{ $reserva->destino }}
                            </h5>
                            <div class="text-muted small d-flex align-items-center flex-wrap gap-3">
                                <span class="d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-upc-scan text-primary"></i>
                                    <span>Localizador: <strong class="fw-semibold">{{ $reserva->localizador }}</strong></span>
                                </span>
                                <span class="d-inline-flex align-items-center gap-1">
                                    <i class="bi bi-stars text-primary"></i>
                                    <strong class="fw-semibold">{{ $reserva->nombrePlanTarifa() }}</strong>
                                </span>
                            </div>
                        </div>
                        <div class="text-end">
                            @if (in_array($reserva->estado, ['confirmada', 'datos_pendientes'], true))
                                @if ($reserva->checkinRealizado())
                                    <span class="badge text-bg-success px-3 py-2">Checking online completado</span>
                                @else
                                    <span class="badge text-bg-warning px-3 py-2">Checking online pendiente</span>
                                @endif
                            @elseif ($reserva->estado === 'completada')
                                <span class="badge text-bg-secondary px-3 py-2">Completada</span>
                            @elseif ($reserva->estado === 'cancelada_usuario')
                                <span class="badge text-bg-danger px-3 py-2">Cancelada por usuario</span>
                            @elseif ($reserva->estado === 'cancelada_aerolinea')
                                <span class="badge text-bg-dark px-3 py-2">Cancelada por aerolínea</span>
                            @endif
                        </div>
                    </div>

                    <div class="row g-3 small mb-2">
                        <div class="col-12 col-md-6">
                            <div class="p-2 rounded-3 bg-light h-100 d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-event text-primary"></i>
                                <div><strong>Salida:</strong> {{ optional($reserva->fecha_salida)->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-2 rounded-3 bg-light h-100 d-flex align-items-center gap-2">
                                <i class="bi bi-calendar-check text-primary"></i>
                                <div><strong>Llegada:</strong> {{ optional($reserva->fecha_llegada)->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-2 rounded-3 bg-light h-100 d-flex align-items-center gap-2">
                                <i class="bi bi-clock text-primary"></i>
                                <div><strong>Duración:</strong> {{ $reserva->duracionTexto() }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-2 rounded-3 bg-light h-100 d-flex align-items-center gap-2">
                                <i class="bi bi-clipboard-check text-primary"></i>
                                <div>
                                    <strong>Check-in:</strong>
                                    @if ($reserva->checkinRealizado())
                                        <span class="text-success">Completado</span>
                                    @else
                                        <span class="text-muted">Pendiente</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 rounded-3 border bg-white">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="bi bi-people text-primary"></i>
                                    <strong>Pasajeros ({{ $reserva->pasajeros->count() }})</strong>
                                </div>
                                @if ($reserva->pasajeros->isEmpty())
                                    <span class="text-muted">Sin pasajeros registrados.</span>
                                @else
                                    <ul class="list-unstyled mb-0 d-flex flex-wrap gap-2">
                                        @foreach ($reserva->pasajeros as $p)
                                            <li class="badge rounded-pill text-bg-light border text-dark fw-normal px-3 py-2">
                                                {{ $p->nombre }} {{ $p->apellidos }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($tipo === 'canceladas')
                        @php
                            $motivoCancelacion = 'No disponible';
                            if ($reserva->cancelacion && $reserva->cancelacion->motivo) {
                                $motivoCancelacion = $reserva->cancelacion->motivo;
                            }
                            $estadoReembolso = 'no_aplicable';
                            if ($reserva->reembolso && $reserva->reembolso->estado) {
                                $estadoReembolso = $reserva->reembolso->estado;
                            }
                        @endphp
                        <div class="border-top pt-2 mt-2 small">
                            <div><strong>Motivo cancelación:</strong> {{ $motivoCancelacion }}</div>
                            <div>
                                <strong>Estado reembolso:</strong>
                                {{ ucfirst(str_replace('_', ' ', $estadoReembolso)) }}
                            </div>
                        </div>
                    @endif

                    <div class="border-top pt-3 mt-3 d-flex flex-wrap gap-2 justify-content-end">
                        <button type="button" class="btn btn-reserva-info btn-sm"
                            data-bs-toggle="modal" data-bs-target="#detalleReservaModal{{ $reserva->id }}">
                            <i class="bi bi-info-circle me-1"></i>Información completa
                        </button>

                        @if ($tipo === 'proximas')
                            @if ($reserva->tarjetas_emitidas)
                                <a href="{{ route('checkin.tarjetas.invitado', $reserva) }}?localizador={{ urlencode($filtros['localizador']) }}&email_contacto={{ urlencode($filtros['email_contacto']) }}"
                                   class="btn btn-reserva-check btn-sm">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>Imprimir tarjetas de embarque
                                </a>
                            @elseif ($reserva->checkinRealizado())
                                <button type="button" class="btn btn-reserva-check btn-sm" disabled
                                        title="El check-in está completado. Las tarjetas estarán disponibles cuando finalice su emisión.">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>Check-in completado
                                </button>
                            @elseif ($reserva->checkinDisponibleAhora())
                                <a href="{{ route('checkin.show.invitado', $reserva) }}?localizador={{ urlencode($filtros['localizador']) }}&email_contacto={{ urlencode($filtros['email_contacto']) }}"
                                   class="btn btn-reserva-check btn-sm">
                                    <i class="bi bi-clipboard-check me-1"></i>Check-in online
                                </a>
                            @else
                                @php
                                    $textoDisponibilidadCheckin = $reserva->checkinTiempoRestanteTexto();
                                    if ($textoDisponibilidadCheckin) {
                                        $textoDisponibilidadCheckin = 'en ' . $textoDisponibilidadCheckin;
                                    } else {
                                        $textoDisponibilidadCheckin = 'próximamente';
                                    }
                                @endphp
                                <button type="button" class="btn btn-reserva-check btn-sm" disabled
                                        title="El check-in estará disponible {{ $textoDisponibilidadCheckin }}.">
                                    <i class="bi bi-clipboard-check me-1"></i>Check-in no disponible aún
                                </button>
                            @endif

                            <button type="button" class="btn btn-reserva-cancel btn-sm"
                                data-bs-toggle="modal" data-bs-target="#cancelarReservaModal{{ $reserva->id }}">
                                <i class="bi bi-x-circle me-1"></i>Cancelar reserva
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="modal fade" id="detalleReservaModal{{ $reserva->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header border-0 pb-0">
                            <div>
                                <h5 class="modal-title fw-bold">Reserva {{ $reserva->localizador }}</h5>
                                <p class="text-muted small mb-0">Detalles completos de tu vuelo</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-4 p-4 bg-light rounded-3">
                                <div class="row align-items-center g-2">
                                    <div class="col-auto">
                                        <div class="text-center">
                                            <p class="text-muted small mb-1">Origen</p>
                                            <p class="fw-bold fs-5 mb-0">{{ $reserva->origen }}</p>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-arrow-right" style="color: #0d6efd; font-size: 1.5rem;"></i>
                                    </div>
                                    <div class="col-auto">
                                        <div class="text-center">
                                            <p class="text-muted small mb-1">Destino</p>
                                            <p class="fw-bold fs-5 mb-0">{{ $reserva->destino }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="d-flex gap-2 align-items-baseline">
                                        <i class="bi bi-calendar3" style="color: #0d6efd; flex-shrink: 0;"></i>
                                        <div class="flex-grow-1">
                                            <p class="text-muted small mb-1">Salida</p>
                                            <p class="mb-0">{{ optional($reserva->fecha_salida)->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex gap-2 align-items-baseline">
                                        <i class="bi bi-calendar3" style="color: #0d6efd; flex-shrink: 0;"></i>
                                        <div class="flex-grow-1">
                                            <p class="text-muted small mb-1">Llegada</p>
                                            <p class="mb-0">{{ optional($reserva->fecha_llegada)->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex gap-2 align-items-baseline">
                                        <i class="bi bi-clock" style="color: #0d6efd; flex-shrink: 0;"></i>
                                        <div class="flex-grow-1">
                                            <p class="text-muted small mb-1">Duración total</p>
                                            <p class="mb-0">{{ $reserva->duracionTexto() }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex gap-2 align-items-baseline">
                                        <i class="bi bi-upc-scan" style="color: #0d6efd; flex-shrink: 0;"></i>
                                        <div class="flex-grow-1">
                                            <p class="text-muted small mb-1">Localizador</p>
                                            <p class="mb-0"><span class="fw-semibold">Localizador:</span> <span class="fw-monospace">{{ $reserva->localizador }}</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3">

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <div class="d-flex gap-2 align-items-baseline">
                                        <i class="bi bi-info-circle" style="color: #0d6efd; flex-shrink: 0;"></i>
                                        <div class="flex-grow-1">
                                            <p class="text-muted small mb-1">Estado</p>
                                            <p class="mb-0">
                                                @if (in_array($reserva->estado, ['confirmada', 'datos_pendientes'], true))
                                                    @if ($reserva->checkinRealizado())
                                                        Checking online completado
                                                    @else
                                                        Checking online pendiente
                                                    @endif
                                                @elseif ($reserva->estado === 'completada')
                                                    Completada
                                                @elseif ($reserva->estado === 'cancelada_usuario')
                                                    Cancelada por usuario
                                                @else
                                                    Cancelada por aerolínea
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex gap-2 align-items-baseline">
                                        <i class="bi bi-stars" style="color: #0d6efd; flex-shrink: 0;"></i>
                                        <div class="flex-grow-1">
                                            <p class="text-muted small mb-1">Plan</p>
                                            <p class="mb-0 fw-semibold">{{ $reserva->nombrePlanTarifa() }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex gap-2 align-items-baseline">
                                        <i class="bi bi-envelope" style="color: #0d6efd; flex-shrink: 0;"></i>
                                        <div class="flex-grow-1">
                                            <p class="text-muted small mb-1">Email de contacto</p>
                                            <p class="mb-0">{{ $reserva->email_contacto }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-3">

                            <h6 class="text-uppercase text-muted small fw-bold mb-3">
                                <i class="bi bi-bag" style="color: #0d6efd;"></i> Servicios
                            </h6>
                            <div class="row g-3 mb-4">
                                @php
                                    $equipajeResumen = $reserva->equipaje_resumen;
                                    if (!$equipajeResumen) {
                                        $equipajeResumen = 'Pendiente de asignación.';
                                    }
                                    $asientosResumen = $reserva->asientos_resumen;
                                    if (!$asientosResumen) {
                                        $asientosResumen = 'Pendientes de asignación.';
                                    }
                                    $meteorologiaResumen = $reserva->meteorologia_resumen;
                                    if (!$meteorologiaResumen) {
                                        $meteorologiaResumen = 'No disponible.';
                                    }
                                @endphp
                                <div class="col-md-6">
                                    <div class="d-flex gap-2 align-items-baseline">
                                        <i class="bi bi-box" style="color: #0d6efd; flex-shrink: 0;"></i>
                                        <div class="flex-grow-1">
                                            <p class="text-muted small mb-1">Equipaje</p>
                                            <p class="mb-0 small">{{ $equipajeResumen }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex gap-2 align-items-baseline">
                                        <i class="bi bi-chair" style="color: #0d6efd; flex-shrink: 0;"></i>
                                        <div class="flex-grow-1">
                                            <p class="text-muted small mb-1">Asientos</p>
                                            <p class="mb-0 small">{{ $asientosResumen }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex gap-2 align-items-baseline">
                                        <i class="bi bi-cloud" style="color: #0d6efd; flex-shrink: 0;"></i>
                                        <div class="flex-grow-1">
                                            <p class="text-muted small mb-1">Meteorología</p>
                                            <p class="mb-0 small">{{ $meteorologiaResumen }}</p>
                                        </div>
                                    </div>
                                </div>
                                @if ($tipo === 'proximas')
                                    <div class="col-md-6">
                                        <div class="d-flex gap-2 align-items-baseline">
                                            <i class="bi bi-check-circle" style="color: #0d6efd; flex-shrink: 0;"></i>
                                            <div class="flex-grow-1">
                                                <p class="text-muted small mb-1">Check-in</p>
                                                <p class="mb-0 small">
                                                    @if ($reserva->checkinRealizado())
                                                        Completado
                                                    @else
                                                        Pendiente
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if ($tipo === 'canceladas')
                                @php
                                    $estadoReembolso = 'no_aplicable';
                                    if ($reserva->reembolso && $reserva->reembolso->estado) {
                                        $estadoReembolso = $reserva->reembolso->estado;
                                    }
                                    $motivoCancelacion = 'No disponible';
                                    if ($reserva->cancelacion && $reserva->cancelacion->motivo) {
                                        $motivoCancelacion = $reserva->cancelacion->motivo;
                                    }
                                @endphp
                                <hr class="my-3">
                                <h6 class="text-uppercase text-muted small fw-bold mb-3">
                                    <i class="bi bi-exclamation-circle" style="color: #0d6efd;"></i> Información de cancelación
                                </h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <div class="d-flex gap-2 align-items-baseline">
                                            <i class="bi bi-cash-coin" style="color: #0d6efd; flex-shrink: 0;"></i>
                                            <div class="flex-grow-1">
                                                <p class="text-muted small mb-1">Reembolso</p>
                                                <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $estadoReembolso)) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex gap-2 align-items-baseline">
                                            <i class="bi bi-file-text" style="color: #0d6efd; flex-shrink: 0;"></i>
                                            <div class="flex-grow-1">
                                                <p class="text-muted small mb-1">Motivo</p>
                                                <p class="mb-0 small">{{ $motivoCancelacion }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <hr class="my-3">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3">
                                <i class="bi bi-people" style="color: #0d6efd;"></i> Pasajeros
                            </h6>
                            @if ($reserva->pasajeros->isEmpty())
                                <div class="alert alert-info small mb-0">
                                    <i class="bi bi-info-circle me-2"></i>Sin pasajeros registrados.
                                </div>
                            @else
                                <div class="row g-2">
                                    @foreach ($reserva->pasajeros as $p)
                                        <div class="col-12">
                                            <div class="d-flex gap-2 align-items-baseline p-3 border rounded">
                                                <i class="bi bi-person" style="color: #0d6efd; flex-shrink: 0;"></i>
                                                <div class="flex-grow-1">
                                                    @php
                                                        $edadAnios = 0;
                                                        $edadMeses = 0;

                                                        if ($p->fecha_nacimiento) {
                                                            try {
                                                                $fechaNacimiento = \Carbon\Carbon::parse($p->fecha_nacimiento);
                                                                if (!$fechaNacimiento->isFuture()) {
                                                                    $nacimiento = new \DateTimeImmutable($fechaNacimiento->format('Y-m-d'));
                                                                    $hoy = new \DateTimeImmutable(now()->format('Y-m-d'));
                                                                    $interval = $nacimiento->diff($hoy);
                                                                    $edadAnios = (int) $interval->y;
                                                                    $edadMeses = (int) $interval->m;
                                                                }
                                                            } catch (\Throwable $e) {
                                                                $edadAnios = 0;
                                                                $edadMeses = 0;
                                                            }
                                                        }

                                                        if ($edadAnios <= 2) {
                                                            $tipoPasajero = 'Bebé';
                                                        } elseif ($edadAnios < 16) {
                                                            $tipoPasajero = 'Niño';
                                                        } else {
                                                            $tipoPasajero = 'Adulto';
                                                        }
                                                    @endphp

                                                    <p class="fw-semibold mb-0">{{ $p->nombre }} {{ $p->apellidos }}</p>
                                                    <p class="mb-1">{{ $tipoPasajero }}</p>
                                                    <p class="text-muted small mb-0">
                                                        Edad:
                                                        @if ($tipoPasajero === 'Bebé')
                                                            {{ $edadAnios }} años y {{ $edadMeses }} meses
                                                        @else
                                                            {{ $edadAnios }} años
                                                        @endif
                                                    </p>
                                                    @php
                                                        $paisPasajero = $p->pais;
                                                        if (!$paisPasajero) {
                                                            $paisPasajero = 'España';
                                                        }
                                                    @endphp
                                                    <p class="text-muted small mb-0">País: {{ $paisPasajero }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            @if ($tipo === 'proximas')
                <div class="modal fade" id="cancelarReservaModal{{ $reserva->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Cancelar reserva {{ $reserva->localizador }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('mis-viajes.cancelar', $reserva) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="localizador" value="{{ $filtros['localizador'] }}">
                                <input type="hidden" name="email_contacto" value="{{ $filtros['email_contacto'] }}">
                                <div class="modal-body">
                                    @php
                                        $horasRestantes = now()->diffInHours($reserva->fecha_salida, false);
                                        if ($horasRestantes >= 168) {
                                            $politica = 'Reembolso completo (en gestión).';
                                        } elseif ($horasRestantes >= 72) {
                                            $politica = 'Reembolso parcial (en gestión).';
                                        } else {
                                            $politica = 'Tarifa no reembolsable.';
                                        }
                                    @endphp
                                    <p class="mb-2">Esta acción moverá la reserva a Canceladas.</p>
                                    <div class="alert alert-warning small py-2">
                                        <strong>Condiciones aplicables:</strong> {{ $politica }}
                                    </div>
                                    <label for="motivo_{{ $reserva->id }}" class="form-label">Motivo de cancelación</label>
                                    <select class="form-select" name="motivo" id="motivo_{{ $reserva->id }}" required>
                                        <option value="">-- Selecciona --</option>
                                        <option value="Cambio de planes de viaje">Cambio de planes de viaje</option>
                                        <option value="Problemas personales o familiares">Problemas personales o familiares</option>
                                        <option value="Motivos laborales o académicos">Motivos laborales o académicos</option>
                                        <option value="Problemas de salud">Problemas de salud</option>
                                        <option value="Error en la reserva">Error en la reserva</option>
                                        <option value="Otros motivos personales">Otros motivos personales</option>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                    <button type="submit" class="btn btn-danger">Confirmar cancelación</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

@endsection
