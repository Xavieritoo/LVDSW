@extends('cabecera')

@push('styles')
    <link href="{{ asset('css/checkin-index.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('js/checkin-index.js') }}"></script>
@endpush

@section('contenido')

<div class="row justify-content-center">
    <div class="col-12 col-xl-11 checkin-shell">
        @php
            if (Auth::check()) {
                $volverUrl = route('mis-reservas.index', ['tab' => 'proximas']);
            } else {
                $volverUrl = route('mis-viajes.index', [
                    'localizador' => request('localizador'),
                    'email_contacto' => request('email_contacto'),
                ]);
            }
        @endphp

        <div class="mb-4 checkin-hero">
            <h2 class="fw-bold mb-1">
                <i class="bi bi-clipboard-check me-2 text-primary"></i>Check-in online
            </h2>
            <p class="text-muted mb-1">
                {{ $reserva->origen }}
                <i class="bi bi-arrow-right mx-1 text-primary"></i>
                {{ $reserva->destino }} -
                {{ optional($reserva->fecha_salida)->format('d/m/Y H:i') }}
            </p>
            <div class="checkin-route-meta">
                <span class="checkin-chip"><i class="bi bi-upc-scan"></i>Localizador: {{ $reserva->localizador }}</span>
                <span class="checkin-chip"><i class="bi bi-stars"></i>{{ $planNombre }}</span>
            </div>
        </div>

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

        <div class="wizard-steps" id="wizard-steps">
            <div class="wizard-step activo" data-step="1">1. Datos</div>
            <div class="wizard-step" data-step="2">2. Asientos</div>
            <div class="wizard-step" data-step="3">3. Equipaje</div>
            <div class="wizard-step" data-step="4">4. Resumen</div>
            <div class="wizard-step" data-step="5">5. Confirmación</div>
        </div>

        @php
            if (Auth::check()) {
                $rutaCheckin = 'checkin.store';
            } else {
                $rutaCheckin = 'checkin.store.invitado';
            }

            $asientoIncluidoData = '0';
            if ($reserva->asientosIncluidosEnPlan()) {
                $asientoIncluidoData = '1';
            }
        @endphp
        <form method="POST" action="{{ route($rutaCheckin, $reserva) }}" id="form-checkin" novalidate data-initial-step="{{ session('checkin_step', 1) }}" data-asiento-incluido-plan="{{ $asientoIncluidoData }}">
            @csrf

            @unless(Auth::check())
                <input type="hidden" name="localizador" value="{{ request('localizador') }}">
                <input type="hidden" name="email_contacto" value="{{ request('email_contacto') }}">
            @endunless

            <section class="wizard-panel activo" data-step="1">
                <div class="alert alert-info mb-4">
                    <i class="bi bi-card-checklist me-2"></i>
                    {{ $documentacionMensaje }}
                </div>

                <div id="datos-validacion-error" class="alert alert-danger d-none" role="alert">
                    Debes completar todos los campos obligatorios de datos antes de continuar a asientos.
                </div>

                @foreach ($pasajeros as $pasajero)
                    @php
                        $k = "pasajero_{$pasajero->id}";
                    @endphp
                    <div class="pasajero-card p-4 mb-3">
                        <h5 class="fw-bold mb-3">Pasajero {{ $loop->iteration }}: {{ $pasajero->nombre }} {{ $pasajero->apellidos }}</h5>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Nombre *</label>
                                <input type="text" class="form-control @error("{$k}.nombre") is-invalid @enderror"
                                       name="{{ $k }}[nombre]" value="{{ old("{$k}.nombre", $pasajero->nombre) }}" required maxlength="100">
                                @error("{$k}.nombre") <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Apellidos *</label>
                                <input type="text" class="form-control @error("{$k}.apellidos") is-invalid @enderror"
                                       name="{{ $k }}[apellidos]" value="{{ old("{$k}.apellidos", $pasajero->apellidos) }}" required maxlength="150">
                                @error("{$k}.apellidos") <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Fecha de nacimiento *</label>
                                <input type="date" class="form-control @error("{$k}.fecha_nacimiento") is-invalid @enderror"
                                       name="{{ $k }}[fecha_nacimiento]"
                                       value="{{ old("{$k}.fecha_nacimiento", optional($pasajero->fecha_nacimiento)->format('Y-m-d')) }}"
                                       max="{{ now()->format('Y-m-d') }}" required>
                                @error("{$k}.fecha_nacimiento") <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Tipo de documento *</label>
                                <select class="form-select @error("{$k}.tipo_documento") is-invalid @enderror"
                                        name="{{ $k }}[tipo_documento]" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach ($tiposDocumentoPermitidos as $tipoDoc)
                                        <option value="{{ $tipoDoc }}" @selected(old("{$k}.tipo_documento") === $tipoDoc)>{{ $tipoDoc }}</option>
                                    @endforeach
                                </select>
                                @error("{$k}.tipo_documento") <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-semibold">Número de documento *</label>
                                <input type="text" class="form-control @error("{$k}.numero_documento") is-invalid @enderror"
                                       name="{{ $k }}[numero_documento]"
                                       value="{{ old("{$k}.numero_documento") }}"
                                       maxlength="15" required style="text-transform:uppercase;" data-doc-input="1">
                                @error("{$k}.numero_documento") <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-end wizard-actions">
                    <button type="button" class="btn btn-primary wizard-next" data-go-step="2">Continuar</button>
                </div>
            </section>

            <section class="wizard-panel" data-step="2">
                @php
                    $pasajerosConAsiento = $pasajeros->filter(function ($pasajero) use ($reserva) {
                        if (is_null($pasajero->fecha_nacimiento) || is_null($reserva->fecha_salida)) {
                            return true;
                        }

                        return \Carbon\Carbon::parse($pasajero->fecha_nacimiento)
                            ->diffInYears(\Carbon\Carbon::parse($reserva->fecha_salida)) >= 2;
                    });
                    $primerPasajeroConAsiento = $pasajerosConAsiento->first();
                    $primerPasajeroConAsientoId = null;
                    if ($primerPasajeroConAsiento) {
                        $primerPasajeroConAsientoId = $primerPasajeroConAsiento->id;
                    }
                @endphp

                @if ($reserva->asientosIncluidosEnPlan())
                    <div class="alert alert-success mb-4">
                        <i class="bi bi-check-circle me-2"></i>
                        <strong>Selección de asientos incluida en tu plan</strong> - En tu plan Planit Comfort, todos los asientos son gratis.
                    </div>
                @else
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Asientos disponibles a 10.00 EUR</strong> - En tu plan Planit Easy, cada asiento seleccionado cuesta 10.00 EUR. Todos los asientos tienen el mismo precio.
                    </div>
                @endif

                <div class="row g-4">
                    <div class="col-12 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="fw-bold mb-3">Pasajeros de la reserva</h5>
                                <p class="text-muted small mb-3">
                                    Selecciona un pasajero y luego pulsa su asiento en el mapa.
                                    @if (!$reserva->asientosIncluidosEnPlan())
                                        Cada asiento cuesta 10.00 EUR.
                                    @else
                                        Los asientos están incluidos sin costo extra.
                                    @endif
                                    Si prefieres no pagar asiento, deja ese pasajero sin selección.
                                </p>
                                <div class="d-grid gap-2">
                                    @foreach ($pasajeros as $pasajero)
                                        @php
                                            $esBebe = false;
                                            if (!is_null($pasajero->fecha_nacimiento) && !is_null($reserva->fecha_salida)) {
                                                $esBebe = \Carbon\Carbon::parse($pasajero->fecha_nacimiento)
                                                    ->diffInYears(\Carbon\Carbon::parse($reserva->fecha_salida)) < 2;
                                            }
                                        @endphp
                                        <button type="button"
                                                @class([
                                                    'btn text-start selector-pasajero-btn',
                                                    'btn-outline-primary' => !$esBebe,
                                                    'btn-outline-secondary' => $esBebe,
                                                    'activo' => !$esBebe && $pasajero->id === $primerPasajeroConAsientoId,
                                                ])
                                                data-pasajero-id="{{ $pasajero->id }}"
                                                data-pasajero-nombre="{{ trim($pasajero->nombre . ' ' . $pasajero->apellidos) }}"
                                                data-es-bebe="@if($esBebe)1@else 0 @endif"
                                                @disabled($esBebe)>
                                            {{ trim($pasajero->nombre . ' ' . $pasajero->apellidos) }}
                                            @if ($esBebe)
                                                <span class="d-block small text-muted">Bebé: viaja en el regazo de uno de sus padres</span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-8">
                        @if (is_null($primerPasajeroConAsientoId))
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Todos los pasajeros de esta reserva son bebés. No se asignarán asientos y viajarán en el regazo de uno de sus padres.
                            </div>
                        @endif

                        @foreach ($pasajeros as $pasajero)
                            @php
                                $k = "pasajero_{$pasajero->id}";
                                $esBebe = false;
                                if (!is_null($pasajero->fecha_nacimiento) && !is_null($reserva->fecha_salida)) {
                                    $esBebe = \Carbon\Carbon::parse($pasajero->fecha_nacimiento)
                                        ->diffInYears(\Carbon\Carbon::parse($reserva->fecha_salida)) < 2;
                                }
                                $asientoActual = old("{$k}.asiento_codigo", $pasajero->asiento_codigo);
                                if ($esBebe) {
                                    $asientoActual = '';
                                }

                                if ($esBebe) {
                                    $claseMapaPasajero = 'd-none';
                                    $claseBadgeAsiento = 'bg-info text-dark';
                                    $textoBadgeAsiento = 'Bebé: viaja en regazo, sin asiento asignado';
                                } elseif ($asientoActual) {
                                    $claseMapaPasajero = '';
                                    $claseBadgeAsiento = 'bg-primary';
                                    $textoBadgeAsiento = 'Asiento seleccionado: ' . $asientoActual;
                                } else {
                                    $claseMapaPasajero = 'd-none';
                                    $claseBadgeAsiento = 'bg-secondary';
                                    $textoBadgeAsiento = 'Sin asiento de pago seleccionado';
                                }
                                if (!$esBebe && $pasajero->id === $primerPasajeroConAsientoId) {
                                    $claseMapaPasajero = '';
                                }
                            @endphp

                            <div @class(['card border-0 shadow-sm mapa-pasajero', $claseMapaPasajero]) id="mapa-pasajero-{{ $pasajero->id }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                        <h5 class="fw-bold mb-0">Mapa de asientos - {{ trim($pasajero->nombre . ' ' . $pasajero->apellidos) }}</h5>
                                    </div>

                                    <input type="hidden" name="{{ $k }}[asiento_codigo]" id="asiento-sel-{{ $pasajero->id }}" value="{{ $asientoActual }}" data-es-bebe="@if($esBebe)1@else 0 @endif">
                                    <div class="mb-3">
                                        <span class="badge {{ $claseBadgeAsiento }} px-3 py-2" id="asiento-badge-{{ $pasajero->id }}">
                                            {{ $textoBadgeAsiento }}
                                        </span>
                                    </div>

                                    @if ($esBebe)
                                        <div class="alert alert-info mb-0">
                                            <i class="bi bi-info-circle me-2"></i>
                                            Este pasajero es un bebé y no puede ocupar asiento propio. Se embarca en el regazo de uno de sus padres.
                                        </div>
                                    @else
                                        <div class="mb-3">
                                            <button type="button" class="btn btn-sm btn-outline-secondary limpiar-asiento-btn" data-pasajero-id="{{ $pasajero->id }}">
                                                Continuar sin asiento de pago
                                            </button>
                                        </div>

                                        <div class="asiento-leyenda">
                                            <span class="asiento-leyenda-item">
                                                <span class="asiento-leyenda-dot libre"></span>Libre
                                            </span>
                                            <span class="asiento-leyenda-item">
                                                <span class="asiento-leyenda-dot seleccionado"></span>Seleccionado
                                            </span>
                                            <span class="asiento-leyenda-item">
                                                <span class="asiento-leyenda-dot ocupado"></span>Ya escogido
                                            </span>
                                            <span class="asiento-leyenda-item">
                                                <span class="asiento-leyenda-dot tipo-planit_plus"></span>PlanIT+
                                            </span>
                                            <span class="asiento-leyenda-item">
                                                <span class="asiento-leyenda-dot tipo-planit_one"></span>PlanIT One
                                            </span>
                                            <span class="asiento-leyenda-item">
                                                <span class="asiento-leyenda-dot tipo-planit_space"></span>PlanIT Space
                                            </span>
                                        </div>

                                        <div class="asiento-avion-wrapper">
                                            <div class="asiento-grid" data-pasajero-id="{{ $pasajero->id }}">

                                            <div></div>
                                            <div class="asiento-col-header">A</div>
                                            <div class="asiento-col-header">B</div>
                                            <div class="asiento-col-header">C</div>
                                            <div></div>
                                            <div class="asiento-col-header">D</div>
                                            <div class="asiento-col-header">E</div>
                                            <div class="asiento-col-header">F</div>

                                            @foreach (range(1, 30) as $fila)
                                                <div class="asiento-row-num">{{ $fila }}</div>
                                                @foreach (['A', 'B', 'C'] as $col)
                                                    @php
                                                        $cod = $fila . $col;
                                                        if (isset($asientosMap[$cod])) {
                                                            $info = $asientosMap[$cod];
                                                        } else {
                                                            $info = ['estado' => 'libre', 'tipo' => 'estandar'];
                                                        }
                                                        $estado = $info['estado'];
                                                        $tipo = $info['tipo'];
                                                        $esMio = strtoupper($asientoActual) === $cod;
                                                        if ($esMio) {
                                                            $estadoBoton = 'seleccionado';
                                                        } else {
                                                            $estadoBoton = $estado;
                                                        }
                                                        $clases = 'asiento-btn ' . $estadoBoton . ' tipo-' . $tipo;
                                                        if (in_array($estado, ['ocupado'], true) && !$esMio) {
                                                            $deshabilitado = true;
                                                        } else {
                                                            $deshabilitado = false;
                                                        }
                                                    @endphp
                                                    <button type="button"
                                                            class="{{ $clases }}"
                                                            data-codigo="{{ $cod }}"
                                                            data-estado="{{ $estadoBoton }}"
                                                            @disabled($deshabilitado)>
                                                        {{ $col }}
                                                    </button>
                                                @endforeach

                                                <div></div>

                                                @foreach (['D', 'E', 'F'] as $col)
                                                    @php
                                                        $cod = $fila . $col;
                                                        if (isset($asientosMap[$cod])) {
                                                            $info = $asientosMap[$cod];
                                                        } else {
                                                            $info = ['estado' => 'libre', 'tipo' => 'estandar'];
                                                        }
                                                        $estado = $info['estado'];
                                                        $tipo = $info['tipo'];
                                                        $esMio = strtoupper($asientoActual) === $cod;
                                                        if ($esMio) {
                                                            $estadoBoton = 'seleccionado';
                                                        } else {
                                                            $estadoBoton = $estado;
                                                        }
                                                        $clases = 'asiento-btn ' . $estadoBoton . ' tipo-' . $tipo;
                                                        if (in_array($estado, ['ocupado'], true) && !$esMio) {
                                                            $deshabilitado = true;
                                                        } else {
                                                            $deshabilitado = false;
                                                        }
                                                    @endphp
                                                    <button type="button"
                                                            class="{{ $clases }}"
                                                            data-codigo="{{ $cod }}"
                                                            data-estado="{{ $estadoBoton }}"
                                                            @disabled($deshabilitado)>
                                                        {{ $col }}
                                                    </button>
                                                @endforeach
                                            @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @error("{$k}.asiento_codigo")
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-between wizard-actions">
                    <button type="button" class="btn btn-outline-secondary wizard-prev" data-go-step="1">Volver</button>
                    <button type="button" class="btn btn-primary wizard-next" data-go-step="3">Continuar</button>
                </div>
            </section>

            <section class="wizard-panel" data-step="3">
                <div class="alert alert-light border mb-4">
                    <i class="bi bi-suitcase2 me-2"></i>
                    Equipaje extra opcional. Precio por maleta adicional: <strong>25.00 EUR</strong> (máx. 15 kg).
                </div>

                @foreach ($pasajeros as $pasajero)
                    @php
                        $k = "pasajero_{$pasajero->id}";
                        $equipajeOld = (int) old("{$k}.equipaje_extra", 0);
                    @endphp
                    <div class="pasajero-card p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <strong>{{ trim($pasajero->nombre . ' ' . $pasajero->apellidos) }}</strong>
                                <div class="text-muted small">Selecciona cuántas maletas extra deseas añadir.</div>
                            </div>
                            <div>
                                <select class="form-select" style="min-width: 190px;" name="{{ $k }}[equipaje_extra]" data-equipaje-input="1" data-pasajero-nombre="{{ trim($pasajero->nombre . ' ' . $pasajero->apellidos) }}">
                                    @foreach ([0,1,2,3] as $n)
                                        <option value="{{ $n }}" @selected($equipajeOld === $n)>{{ $n }} maleta(s) extra</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-between wizard-actions">
                    <button type="button" class="btn btn-outline-secondary wizard-prev" data-go-step="2">Volver</button>
                    <button type="button" class="btn btn-primary wizard-next" data-go-step="4">Continuar</button>
                </div>
            </section>

            <section class="wizard-panel" data-step="4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Resumen de check-in</h5>
                        <div id="resumen-pasajeros" class="small mb-3"></div>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-2">
                                <thead>
                                    <tr>
                                        <th>Concepto</th>
                                        <th class="text-end">Importe</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Asientos seleccionados</td>
                                        <td class="text-end" id="resumen-importe-asientos">0.00 EUR</td>
                                    </tr>
                                    <tr>
                                        <td>Maletas extra</td>
                                        <td class="text-end" id="resumen-importe-maletas">0.00 EUR</td>
                                    </tr>
                                    <tr class="fw-bold">
                                        <td>Total extras a pagar</td>
                                        <td class="text-end" id="resumen-importe-total">0.00 EUR</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between wizard-actions">
                    <button type="button" class="btn btn-outline-secondary wizard-prev" data-go-step="3">Volver</button>
                    <button type="button" class="btn btn-primary wizard-next" data-go-step="5">Continuar</button>
                </div>
            </section>

            <section class="wizard-panel" data-step="5">
                <div class="alert alert-success mb-4">
                    <h5 class="fw-bold mb-2"><i class="bi bi-check2-circle me-2"></i>Confirmación final</h5>
                    <p class="mb-1">Se confirmará el check-in de todos los pasajeros de la reserva.</p>
                    <p class="mb-0">Si añadiste asientos seleccionados o maletas extra, el importe se reflejará como extras a pagar.</p>
                </div>

                <div class="d-flex justify-content-between align-items-center wizard-actions">
                    <button type="button" class="btn btn-outline-secondary wizard-prev" data-go-step="4">Volver</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check2-circle me-2"></i>Confirmar check-in
                    </button>
                </div>
            </section>

            <div class="d-flex justify-content-start mt-3">
                <a href="{{ $volverUrl }}" class="btn btn-outline-secondary">
                    <i class="bi bi-box-arrow-left me-1"></i>Salir
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
