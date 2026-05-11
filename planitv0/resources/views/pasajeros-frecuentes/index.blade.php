@extends('cabecera')

@push('styles')
    <link href="{{ asset('css/pasajeros-frecuentes-index.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('js/pasajeros-frecuentes-index.js') }}"></script>
@endpush

@section('contenido')
@php
    $editModalId = session('edit_modal_id');
    $openCrearModalData = '0';
    if ($errors->any() && !$editModalId) {
        $openCrearModalData = '1';
    }
@endphp

<div class="row justify-content-center" id="pasajeros-frecuentes-page"
    data-edit-modal-id="{{ $editModalId }}"
    data-open-crear-modal="{{ $openCrearModalData }}">
    <div class="col-12 col-xl-11">

        <div class="mb-4">
            <h2 class="fw-bold mb-1">Pasajeros frecuentes</h2>
            <p class="text-muted">Gestiona los viajeros habituales de tu cuenta para agilizar reservas.</p>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-12 col-lg-3">
                @include('partials.sidebar-area-personal')
            </div>

            <div class="col-12 col-lg-9">

                @if (session('exito'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('exito') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Filtros -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header fw-semibold bg-white">
                        <i class="bi bi-funnel me-2 text-primary"></i>Filtros de búsqueda
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('pasajeros-frecuentes.index') }}" class="row g-3">

                        <div class="col-12 col-md-4">
                            <label for="nombre" class="form-label">Buscar por nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre"
                                value="{{ $filtro_nombre }}" placeholder="Nombre o apellidos...">
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="pais" class="form-label">País</label>
                            <select class="form-select" id="pais" name="pais">
                                <option value="">Todos los países</option>
                                @foreach ($paises as $p)
                                    <option value="{{ $p }}" @selected($filtro_pais === $p)>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="favorito" class="form-label">Favoritos</label>
                            <select class="form-select" id="favorito" name="favorito">
                                <option value="">Todos</option>
                                <option value="1" @selected($filtro_favorito === '1')>Solo favoritos</option>
                                <option value="0" @selected($filtro_favorito === '0')>No favoritos</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="tipo_pasajero" class="form-label">Tipo de pasajero</label>
                            <select class="form-select" id="tipo_pasajero" name="tipo_pasajero">
                                <option value="">Todos</option>
                                <option value="bebe" @selected($filtro_tipo === 'bebe')>Bebé (0-2 años)</option>
                                <option value="nino" @selected($filtro_tipo === 'nino')>Niño (>2 a <16)</option>
                                <option value="adulto" @selected($filtro_tipo === 'adulto')>Adulto (16+)</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="orden" class="form-label">Ordenar por</label>
                            <select class="form-select" id="orden" name="orden">
                                <option value="nombre_asc" @selected($orden === 'nombre_asc')>Nombre (A-Z)</option>
                                <option value="nombre_desc" @selected($orden === 'nombre_desc')>Nombre (Z-A)</option>
                                <option value="fecha_asc" @selected($orden === 'fecha_asc')>Fecha nacimiento (mayor)</option>
                                <option value="fecha_desc" @selected($orden === 'fecha_desc')>Fecha nacimiento (menor)</option>
                                <option value="favorito" @selected($orden === 'favorito')>Favoritos primero</option>
                                <option value="no_favorito" @selected($orden === 'no_favorito')>No favoritos primero</option>
                            </select>
                        </div>

                            <div class="col-12 d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-planit-primary">
                                    <i class="bi bi-search me-2"></i>Aplicar filtros
                                </button>
                                <a href="{{ route('pasajeros-frecuentes.index') }}" class="btn btn-planit-outline">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Limpiar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Listado -->
                @if (empty($pasajeros))
                    <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <strong>No hay pasajeros frecuentes</strong>
                            <p class="mb-0">Crea el primero para agilizar tus futuras reservas.</p>
                        </div>
                        <button type="button" class="btn btn-sm btn-planit-primary" data-bs-toggle="modal" data-bs-target="#crearModal">
                            <i class="bi bi-plus"></i> Nuevo pasajero
                        </button>
                    </div>
                @else
                    <div class="card mb-3 shadow-sm border-secondary-subtle">
                        <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <p class="fw-semibold mb-0">¿Deseas crear otro pasajero frecuente?</p>
                                <p class="text-muted small mb-0">Crea otro para agilizar tus futuras reservas.</p>
                            </div>
                            <button type="button" class="btn btn-planit-outline" data-bs-toggle="modal" data-bs-target="#crearModal">
                                <i class="bi bi-plus me-2"></i>Nuevo pasajero
                            </button>
                        </div>
                    </div>
                    <div class="row g-3">
                        @foreach ($pasajeros as $pasajero)
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm border-0 rounded-4 overflow-hidden">
                                    <div class="card-body p-3 p-lg-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            @php
                                                if ($pasajero->favorito) {
                                                    $textoFavorito = 'Quitar de favoritos';
                                                    $colorFavorito = '#ffc107';
                                                    $simboloFavorito = '★';
                                                } else {
                                                    $textoFavorito = 'Marcar como favorito';
                                                    $colorFavorito = '#ccc';
                                                    $simboloFavorito = '☆';
                                                }
                                            @endphp
                                            <div>
                                                <h5 class="card-title fw-bold mb-1 lh-sm">
                                                    {{ $pasajero->nombre }} {{ $pasajero->apellidos }}
                                                </h5>
                                                <p class="mb-0">
                                                    @if ($pasajero->tipo_pasajero === 'bebe')
                                                        <span class="badge rounded-pill bg-info-subtle text-dark border border-info-subtle">Bebé</span>
                                                    @elseif ($pasajero->tipo_pasajero === 'nino')
                                                        <span class="badge rounded-pill bg-success-subtle text-success-emphasis border border-success-subtle">Niño</span>
                                                    @else
                                                        <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis border border-primary-subtle">Adulto</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <form method="POST" action="{{ route('pasajeros-frecuentes.toggle-favorito', $pasajero->id) }}" class="m-0 d-flex align-items-center">
                                                @csrf
                                                <button type="submit" class="btn btn-link p-0 border-0 d-inline-flex align-items-center justify-content-center" title="{{ $textoFavorito }}" style="font-size: 1.4rem; line-height: 1; color: {{ $colorFavorito }};">
                                                    {{ $simboloFavorito }}
                                                </button>
                                            </form>
                                        </div>

                                        <div class="mb-3 p-3 bg-light rounded-3">
                                            <div class="d-flex align-items-start gap-2 mb-1">
                                                <i class="bi bi-calendar-event text-primary"></i>
                                                <small class="text-muted d-block">
                                                    @php
                                                        $edad = $pasajero->edad;
                                                        $anos = 0;
                                                        if (array_key_exists('anos', $edad)) {
                                                            $anos = (int) $edad['anos'];
                                                        }
                                                        $meses = 0;
                                                        if (array_key_exists('meses', $edad)) {
                                                            $meses = (int) $edad['meses'];
                                                        }
                                                        if ($pasajero->tipo_pasajero === 'bebe') {
                                                            echo "Edad: {$anos} años y {$meses} meses";
                                                        } else {
                                                            echo "Edad: {$anos} años";
                                                        }
                                                    @endphp
                                                </small>
                                            </div>
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="bi bi-globe-americas text-primary"></i>
                                                @php
                                                    $paisPasajero = $pasajero->pais;
                                                    if (!$paisPasajero) {
                                                        $paisPasajero = 'No especificado';
                                                    }
                                                @endphp
                                                <small class="text-muted d-block">País: {{ $paisPasajero }}</small>
                                            </div>
                                        </div>

                                        <div class="d-grid gap-2 mt-auto">
                                            <button type="button" class="btn btn-sm btn-planit-outline"
                                                data-bs-toggle="modal" data-bs-target="#verModal{{ $pasajero->id }}">
                                                <i class="bi bi-eye me-1"></i>Ver detalle
                                            </button>
                                            <button type="button" class="btn btn-sm btn-planit-outline"
                                                data-bs-toggle="modal" data-bs-target="#editarModal{{ $pasajero->id }}">
                                                <i class="bi bi-pencil me-1"></i>Editar
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#eliminarModal{{ $pasajero->id }}">
                                                Eliminar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Eliminar -->
                            <div class="modal fade" id="eliminarModal{{ $pasajero->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Eliminar pasajero</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Estás seguro de que deseas eliminar a <strong>{{ $pasajero->nombre }} {{ $pasajero->apellidos }}</strong>?</p>
                                            <p class="text-muted small">Esta acción no se puede deshacer.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <form method="POST" action="{{ route('pasajeros-frecuentes.destroy', $pasajero->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Ver detalle -->
                            <div class="modal fade" id="verModal{{ $pasajero->id }}" tabindex="-1" aria-labelledby="verModalLabel{{ $pasajero->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title d-flex align-items-center gap-3 mb-0" id="verModalLabel{{ $pasajero->id }}">
                                                <span>{{ $pasajero->nombre }} {{ $pasajero->apellidos }}</span>
                                                <form method="POST" action="{{ route('pasajeros-frecuentes.toggle-favorito', $pasajero->id) }}" class="m-0 d-flex align-items-center">
                                                    @csrf
                                                    <button type="submit" class="btn btn-link p-0 border-0 d-inline-flex align-items-center justify-content-center" title="{{ $textoFavorito }}" style="font-size: 1.4rem; line-height: 1; color: {{ $colorFavorito }};">
                                                        {{ $simboloFavorito }}
                                                    </button>
                                                </form>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Header con tipo y edad destacados -->
                                            <div class="row g-3 mb-4">
                                                <div class="col-12">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <div class="d-flex align-items-center gap-3 p-4 bg-light rounded-3">
                                                                <div class="flex-shrink-0">
                                                                    <i class="bi bi-person" style="font-size: 2rem; color: #0d6efd;"></i>
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <p class="text-muted small mb-1">Tipo de pasajero</p>
                                                                    <p class="fw-bold fs-5 mb-0">
                                                                        @if ($pasajero->tipo_pasajero === 'bebe')
                                                                            Bebé
                                                                        @elseif ($pasajero->tipo_pasajero === 'nino')
                                                                            Niño
                                                                        @else
                                                                            Adulto
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="d-flex align-items-center gap-3 p-4 bg-light rounded-3">
                                                                <div class="flex-shrink-0">
                                                                    <i class="bi bi-calendar-event" style="font-size: 2rem; color: #0d6efd;"></i>
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <p class="text-muted small mb-1">Edad</p>
                                                                    <p class="fw-bold fs-5 mb-0">
                                                                        @php
                                                                            $anos = 0;
                                                                            if (array_key_exists('anos', $pasajero->edad)) {
                                                                                $anos = (int) $pasajero->edad['anos'];
                                                                            }
                                                                            $meses = 0;
                                                                            if (array_key_exists('meses', $pasajero->edad)) {
                                                                                $meses = (int) $pasajero->edad['meses'];
                                                                            }
                                                                            if ($pasajero->tipo_pasajero === 'bebe') {
                                                                                echo "{$anos}a {$meses}m";
                                                                            } else {
                                                                                echo "{$anos} años";
                                                                            }
                                                                        @endphp
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Información personal -->
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <h6 class="text-uppercase text-muted small fw-bold mb-3">
                                                        <i class="bi bi-info-circle me-2" style="color: #0d6efd;"></i>Información personal
                                                    </h6>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="d-flex gap-2 align-items-baseline">
                                                        <i class="bi bi-calendar3" style="color: #0d6efd; flex-shrink: 0;"></i>
                                                        <div class="flex-grow-1">
                                                            <p class="text-muted small mb-1">Fecha de nacimiento</p>
                                                            <p class="mb-0">@php echo \Carbon\Carbon::parse($pasajero->fecha_nacimiento)->isoFormat('D [de] MMMM [de] YYYY'); @endphp</p>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if ($pasajero->pais)
                                                    <div class="col-md-6">
                                                        <div class="d-flex gap-2 align-items-baseline">
                                                            <i class="bi bi-globe-americas" style="color: #0d6efd; flex-shrink: 0;"></i>
                                                            <div class="flex-grow-1">
                                                                <p class="text-muted small mb-1">País</p>
                                                                <p class="mb-0">{{ $pasajero->pais }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                                            <button type="button" class="btn btn-planit-primary"
                                                data-bs-dismiss="modal"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editarModal{{ $pasajero->id }}">
                                                <i class="bi bi-pencil me-1"></i>Editar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Editar -->
                            @php $isEditing = session('edit_modal_id') == $pasajero->id; @endphp
                            <div class="modal fade" id="editarModal{{ $pasajero->id }}" tabindex="-1" aria-labelledby="editarModalLabel{{ $pasajero->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editarModalLabel{{ $pasajero->id }}">
                                                <i class="bi bi-pencil-square me-2 text-primary"></i>Editar pasajero
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </div>
                                        <form method="POST" action="{{ route('pasajeros-frecuentes.update', $pasajero->id) }}" novalidate>
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                @if ($isEditing && $errors->pasajero_edit->any())
                                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                                        <ul class="mb-0">
                                                            @foreach ($errors->pasajero_edit->all() as $error)
                                                                <li>{{ $error }}</li>
                                                            @endforeach
                                                        </ul>
                                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                                    </div>
                                                @endif

                                                <div class="row g-3">
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                                                        @php
                                                            if ($isEditing) {
                                                                $valorNombre = old('nombre', $pasajero->nombre);
                                                            } else {
                                                                $valorNombre = $pasajero->nombre;
                                                            }
                                                        @endphp
                                                        <input type="text"
                                                            @class(['form-control', 'is-invalid' => $isEditing && $errors->pasajero_edit->has('nombre')])
                                                            name="nombre"
                                                            value="{{ $valorNombre }}"
                                                            required maxlength="100">
                                                        @if ($isEditing && $errors->pasajero_edit->has('nombre'))
                                                            <div class="invalid-feedback">{{ $errors->pasajero_edit->first('nombre') }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label fw-semibold">Apellidos <span class="text-danger">*</span></label>
                                                        @php
                                                            if ($isEditing) {
                                                                $valorApellidos = old('apellidos', $pasajero->apellidos);
                                                            } else {
                                                                $valorApellidos = $pasajero->apellidos;
                                                            }
                                                        @endphp
                                                        <input type="text"
                                                            @class(['form-control', 'is-invalid' => $isEditing && $errors->pasajero_edit->has('apellidos')])
                                                            name="apellidos"
                                                            value="{{ $valorApellidos }}"
                                                            required maxlength="150">
                                                        @if ($isEditing && $errors->pasajero_edit->has('apellidos'))
                                                            <div class="invalid-feedback">{{ $errors->pasajero_edit->first('apellidos') }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label fw-semibold">Fecha de nacimiento <span class="text-danger">*</span></label>
                                                        @php
                                                            if ($isEditing) {
                                                                $valorFechaNacimiento = old('fecha_nacimiento', $pasajero->fecha_nacimiento);
                                                            } else {
                                                                $valorFechaNacimiento = $pasajero->fecha_nacimiento;
                                                            }
                                                        @endphp
                                                        <input type="date"
                                                            @class(['form-control', 'is-invalid' => $isEditing && $errors->pasajero_edit->has('fecha_nacimiento')])
                                                            name="fecha_nacimiento"
                                                            value="{{ $valorFechaNacimiento }}"
                                                            min="1910-01-01" max="{{ now()->format('Y-m-d') }}" required>
                                                        @if ($isEditing && $errors->pasajero_edit->has('fecha_nacimiento'))
                                                            <div class="invalid-feedback">{{ $errors->pasajero_edit->first('fecha_nacimiento') }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label fw-semibold">País</label>
                                                        <select class="form-select" name="pais">
                                                            <option value="">Seleccionar país...</option>
                                                            @foreach ($paises as $p)
                                                                @php
                                                                    if ($isEditing) {
                                                                        $paisActual = old('pais', $pasajero->pais);
                                                                    } else {
                                                                        $paisActual = $pasajero->pais;
                                                                    }
                                                                @endphp
                                                                <option value="{{ $p }}"
                                                                    @selected($paisActual === $p)>
                                                                    {{ $p }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-planit-primary">
                                                    <i class="bi bi-floppy me-1"></i>Guardar cambios
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>{{-- /col-lg-9 --}}
        </div>{{-- /row g-4 --}}
    </div>{{-- /col-xl-11 --}}
</div>{{-- /row justify-content-center --}}

<!-- Modal Crear pasajero -->
<div class="modal fade" id="crearModal" tabindex="-1" aria-labelledby="crearModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="crearModalLabel">
                    <i class="bi bi-plus-square me-2 text-primary"></i>Nuevo pasajero frecuente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('pasajeros-frecuentes.store') }}" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="nuevo-pasajero-intro mb-3">
                        <p class="text-muted small mb-0">
                            Completa los datos del pasajero para reutilizarlos en futuras reservas.
                        </p>
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

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nombre"
                                value="{{ old('nombre') }}" required maxlength="100">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="apellidos"
                                value="{{ old('apellidos') }}" required maxlength="150">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Fecha de nacimiento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="fecha_nacimiento"
                                value="{{ old('fecha_nacimiento') }}" min="1910-01-01" max="{{ now()->format('Y-m-d') }}" placeholder="dd/mm/aaaa" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">País</label>
                            <select class="form-select" name="pais">
                                <option value="">Seleccionar país...</option>
                                @foreach ($paises as $p)
                                    <option value="{{ $p }}" @selected(old('pais') === $p)>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-planit-primary">
                        <i class="bi bi-floppy me-1"></i>Guardar pasajero
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
