@extends('cabecera')

@section('contenido')

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar (área personal) -->
        <div class="col-12 col-lg-3 mb-4">
            @include('partials.sidebar-area-personal')
        </div>

        <!-- Contenido principal -->
        <div class="col-12 col-lg-9">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-6">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h2 class="fw-bold mb-1">{{ $pasajero->nombre }} {{ $pasajero->apellidos }}</h2>
                                <p class="text-muted">Detalles del pasajero frecuente</p>
                            </div>
                            @if ($pasajero->favorito)
                                <span class="badge bg-warning text-dark">★ Favorito</span>
                            @endif
                        </div>
                    </div>

                    @if (session('exito'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('exito') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Tipo de pasajero -->
                                <div class="col-12 col-md-6">
                                    <div class="p-3 bg-light rounded">
                                        <p class="text-muted mb-1 small">Tipo de pasajero</p>
                                        <p class="fw-bold mb-0">
                                            @if ($pasajero->tipo_pasajero === 'bebe')
                                                <span class="badge bg-info text-dark">Bebé</span>
                                            @elseif ($pasajero->tipo_pasajero === 'nino')
                                                <span class="badge bg-success">Niño</span>
                                            @else
                                                <span class="badge bg-primary">Adulto</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <!-- Edad -->
                                <div class="col-12 col-md-6">
                                    <div class="p-3 bg-light rounded">
                                        <p class="text-muted mb-1 small">Edad</p>
                                        <p class="fw-bold mb-0">
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
                                                    echo "{$anos} años y {$meses} meses";
                                                } else {
                                                    echo "{$anos} años";
                                                }
                                            @endphp
                                        </p>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <hr>
                                </div>

                                <!-- Fecha de nacimiento -->
                                <div class="col-12">
                                    <p class="text-muted mb-1 small">Fecha de nacimiento</p>
                                    <p class="mb-3">
                                        @php
                                            echo \Carbon\Carbon::parse($pasajero->fecha_nacimiento)->format('d \d\e F \d\e Y');
                                        @endphp
                                    </p>
                                </div>

                                <!-- País -->
                                @if ($pasajero->pais)
                                    <div class="col-12">
                                        <p class="text-muted mb-1 small">País</p>
                                        <p class="mb-3">{{ $pasajero->pais }}</p>
                                    </div>
                                @endif

                                <div class="col-12">
                                    <hr>
                                </div>

                                <!-- Botones -->
                                <div class="col-12 d-flex justify-content-end gap-2">
                                    <a href="{{ route('pasajeros-frecuentes.index') }}" class="btn btn-secondary">Volver</a>
                                    <a href="{{ route('pasajeros-frecuentes.edit', $pasajero->id) }}" class="btn btn-primary">
                                        Editar
                                    </a>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#eliminarModal">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Historial de cambios (JOIN logs_cambios + usuarios) -->
                    @if (count($historial) > 0)
                        <div class="card shadow-sm mt-4">
                            <div class="card-body">
                                <h5 class="fw-bold mb-3">Historial de cambios</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Acción</th>
                                                <th>Descripción</th>
                                                <th>Responsable</th>
                                                <th>Fecha</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($historial as $evento)
                                                <tr>
                                                    <td>
                                                        @if ($evento->accion === 'INSERT')
                                                            <span class="badge bg-success">Creación</span>
                                                        @elseif ($evento->accion === 'UPDATE')
                                                            <span class="badge bg-warning text-dark">Edición</span>
                                                        @elseif ($evento->accion === 'DELETE')
                                                            <span class="badge bg-danger">Eliminación</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $evento->accion }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $evento->descripcion }}</td>
                                                    <td>{{ $evento->usuario_responsable }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($evento->fecha_operacion)->format('d/m/Y H:i') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="eliminarModal" tabindex="-1">
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
                <form method="POST" action="{{ route('pasajeros-frecuentes.destroy', $pasajero->id) }}" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Sí, eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
