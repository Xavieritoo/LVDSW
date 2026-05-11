@if ($reservas->isEmpty())
    <div class="card shadow-sm mb-3">
        <div class="card-body text-center text-muted py-4">
            <i class="bi bi-inbox fs-4 d-block mb-2"></i>
            No hay reservas en esta sección con los filtros actuales.
        </div>
    </div>
@else
    @foreach ($reservas as $reserva)
        @php
            $estadoVisualTexto = 'Estado no disponible';
            $estadoVisualIcono = 'bi-info-circle';
            $estadoVisualClase = 'status-default';
            $estadoVisualAyuda = null;

            if (in_array($reserva->estado, ['confirmada', 'datos_pendientes'], true)) {
                if ($reserva->checkinRealizado()) {
                    $estadoVisualTexto = 'Check-in online completado';
                    $estadoVisualIcono = 'bi-check-circle-fill';
                    $estadoVisualClase = 'status-checkin-ok';
                    $estadoVisualAyuda = 'Tarjetas de embarque listas para descargar.';
                } else {
                    $estadoVisualTexto = 'Check-in online pendiente';
                    $estadoVisualIcono = 'bi-hourglass-split';
                    $estadoVisualClase = 'status-checkin-pendiente';
                    $estadoVisualAyuda = 'Completa el check-in para emitir tarjetas.';
                }
            } elseif ($reserva->estado === 'completada') {
                $estadoVisualTexto = 'Completada';
                $estadoVisualIcono = 'bi-flag-fill';
                $estadoVisualClase = 'status-completada';
            } elseif ($reserva->estado === 'cancelada_usuario') {
                $estadoVisualTexto = 'Cancelada por usuario';
                $estadoVisualIcono = 'bi-person-x-fill';
                $estadoVisualClase = 'status-cancelada-usuario';
            } elseif ($reserva->estado === 'cancelada_aerolinea') {
                $estadoVisualTexto = 'Cancelada por aerolínea';
                $estadoVisualIcono = 'bi-exclamation-octagon-fill';
                $estadoVisualClase = 'status-cancelada-aerolinea';
            }
        @endphp
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
                        <span class="reserva-status-chip {{ $estadoVisualClase }}">
                            <i class="bi {{ $estadoVisualIcono }}"></i>
                            <span>{{ $estadoVisualTexto }}</span>
                        </span>
                        @if ($estadoVisualAyuda)
                            <div class="reserva-status-help">{{ $estadoVisualAyuda }}</div>
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
                                    <span class="reserva-inline-state state-ok">Completado</span>
                                @else
                                    <span class="reserva-inline-state state-pending">Pendiente</span>
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

                @if ($tipo === 'proximas')
                    <div class="border-top pt-3 mt-3 d-flex flex-wrap gap-2 justify-content-end">
                        <button type="button" class="btn btn-reserva-info btn-sm"
                            data-bs-toggle="modal" data-bs-target="#detalleReservaModal{{ $reserva->id }}">
                            <i class="bi bi-info-circle me-1"></i>Información completa
                        </button>
                        @if ($reserva->tarjetas_emitidas)
                            <a href="{{ route('checkin.tarjetas', $reserva) }}"
                               class="btn btn-reserva-check btn-sm">
                                <i class="bi bi-file-earmark-pdf me-1"></i>Imprimir tarjetas de embarque
                            </a>
                        @elseif ($reserva->checkinDisponibleAhora())
                            <a href="{{ route('checkin.show', $reserva) }}"
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
                    </div>
                @else
                    <div class="border-top pt-3 mt-3 d-flex flex-wrap gap-2 justify-content-end">
                        <button type="button" class="btn btn-reserva-info btn-sm"
                            data-bs-toggle="modal" data-bs-target="#detalleReservaModal{{ $reserva->id }}">
                            <i class="bi bi-info-circle me-1"></i>Información completa
                        </button>
                    </div>
                @endif
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
                        <!-- Ruta de vuelo destacada -->
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

                        <!-- Información de vuelo en grid -->
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

                        <!-- Estado y plan -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="d-flex gap-2 align-items-baseline">
                                    <i class="bi bi-info-circle" style="color: #0d6efd; flex-shrink: 0;"></i>
                                    <div class="flex-grow-1">
                                        <p class="text-muted small mb-1">Estado</p>
                                        <p class="mb-0">
                                            <span class="reserva-status-chip {{ $estadoVisualClase }}">
                                                <i class="bi {{ $estadoVisualIcono }}"></i>
                                                <span>{{ $estadoVisualTexto }}</span>
                                            </span>
                                            @if ($estadoVisualAyuda)
                                                <span class="reserva-status-help d-block mt-1">{{ $estadoVisualAyuda }}</span>
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
                        </div>

                        <hr class="my-3">

                        <!-- Servicios incluidos -->
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

                        <!-- Pasajeros -->
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
                        <form method="POST" action="{{ route('mis-reservas.cancelar', $reserva) }}">
                            @csrf
                            @method('PUT')
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
    @endforeach
@endif
