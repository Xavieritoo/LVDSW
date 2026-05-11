@extends('cabecera')

@section('contenido')
@php
    $minFechaHora = now()->format('Y-m-d\\TH:i');
@endphp
<div class="row justify-content-center">
    <div class="col-12 col-xl-11">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Gestión de vuelos</h2>
                <p class="text-muted mb-0">CRUD sencillo para administración de vuelos.</p>
            </div>
            <a href="{{ route('admin.vuelos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Crear vuelo
            </a>
        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.vuelos.index') }}" class="row g-3 align-items-end">
                    <div class="col-12 col-md-5">
                        <label for="origen" class="form-label">Buscar por origen</label>
                        <input type="search" id="origen" name="origen" class="form-control"
                            value="@if(isset($filtroOrigen)){{ $filtroOrigen }}@endif" placeholder="Ej: Barcelona">
                    </div>
                    <div class="col-12 col-md-5">
                        <label for="destino" class="form-label">Buscar por destino</label>
                        <input type="search" id="destino" name="destino" class="form-control"
                            value="@if(isset($filtroDestino)){{ $filtroDestino }}@endif" placeholder="Ej: Madrid">
                    </div>
                    <div class="col-12 col-md-2 d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="{{ route('admin.vuelos.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        @if (session('exito'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('exito') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($vuelos->isEmpty())
            <div class="alert alert-info">No hay vuelos registrados.</div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Salida</th>
                        <th>Llegada</th>
                        <th>Precio</th>
                        <th>Asientos</th>
                        <th>Activo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($vuelos as $vuelo)
                        <tr>
                            <td>{{ $vuelo->id }}</td>
                            <td>@if(!is_null(optional($vuelo->ciudadOrigen)->nombre)){{ optional($vuelo->ciudadOrigen)->nombre }}@elseif(isset($vuelo->origen)){{ $vuelo->origen }}@else - @endif</td>
                            <td>@if(!is_null(optional($vuelo->ciudadDestino)->nombre)){{ optional($vuelo->ciudadDestino)->nombre }}@elseif(isset($vuelo->destino)){{ $vuelo->destino }}@else - @endif</td>
                            <td>{{ optional($vuelo->fecha_salida)->format('d/m/Y H:i') }}</td>
                            <td>{{ optional($vuelo->fecha_llegada)->format('d/m/Y H:i') }}</td>
                            <td>{{ number_format((float) $vuelo->precio, 2, ',', '.') }} EUR</td>
                            <td>{{ $vuelo->asientos_disponibles }}</td>
                            <td>
                                @if ($vuelo->activo)
                                    <span class="badge text-bg-success">Sí</span>
                                @else
                                    <span class="badge text-bg-secondary">No</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.vuelos.edit', $vuelo) }}" class="btn btn-sm btn-outline-primary">
                                        Editar
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                        data-bs-target="#horariosModal{{ $vuelo->id }}">
                                        Horarios
                                    </button>
                                    <form method="POST" action="{{ route('admin.vuelos.destroy', $vuelo) }}"
                                        onsubmit="return confirm('¿Seguro que deseas eliminar este vuelo?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="horariosModal{{ $vuelo->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Definir horarios - Vuelo #{{ $vuelo->id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="{{ route('admin.vuelos.horarios', $vuelo) }}">
                                        @csrf
                                        @method('PATCH')
                                        <div class="modal-body">
                                            @php
                                                if ($vuelo->hora_salida_programada) {
                                                    $valSalida = optional($vuelo->hora_salida_programada)->format('Y-m-d\TH:i');
                                                } else {
                                                    $valSalida = optional($vuelo->fecha_salida)->format('Y-m-d\TH:i');
                                                }
                                                if ($vuelo->hora_llegada_programada) {
                                                    $valLlegada = optional($vuelo->hora_llegada_programada)->format('Y-m-d\TH:i');
                                                } else {
                                                    $valLlegada = optional($vuelo->fecha_llegada)->format('Y-m-d\TH:i');
                                                }
                                            @endphp
                                            <div class="mb-3">
                                                <label class="form-label">Salida programada</label>
                                                <input type="datetime-local" name="hora_salida_programada" class="form-control"
                                                    value="{{ old('hora_salida_programada', $valSalida) }}" min="{{ $minFechaHora }}" required>
                                            </div>
                                            <div>
                                                <label class="form-label">Llegada programada</label>
                                                <input type="datetime-local" name="hora_llegada_programada" class="form-control"
                                                    value="{{ old('hora_llegada_programada', $valLlegada) }}" min="{{ $minFechaHora }}" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Guardar horarios</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $vuelos->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
