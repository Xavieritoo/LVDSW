@extends('cabecera')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-12 col-xl-11">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Gestión de reservas</h2>
                <p class="text-muted mb-0">Supervisión operativa de reservas y estado de check-in.</p>
            </div>
        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.reservas.index') }}" class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label for="localizador" class="form-label">Localizador</label>
                        <input type="search" id="localizador" name="localizador" class="form-control"
                            value="@if(isset($filtros['localizador'])){{ $filtros['localizador'] }}@endif" placeholder="Ej: AB12CD">
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="estado" class="form-label">Estado de reserva</label>
                        <select id="estado" name="estado" class="form-select">
                            <option value="">Todos</option>
                            <option value="confirmada" @selected(isset($filtros['estado']) && $filtros['estado'] === 'confirmada')>Confirmada</option>
                            <option value="completada" @selected(isset($filtros['estado']) && $filtros['estado'] === 'completada')>Completada</option>
                            <option value="cancelada_usuario" @selected(isset($filtros['estado']) && $filtros['estado'] === 'cancelada_usuario')>Cancelada por usuario</option>
                            <option value="cancelada_aerolinea" @selected(isset($filtros['estado']) && $filtros['estado'] === 'cancelada_aerolinea')>Cancelada por aerolínea</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="checkin" class="form-label">Estado de check-in</label>
                        <select id="checkin" name="checkin" class="form-select">
                            <option value="">Todos</option>
                            <option value="completado" @selected(isset($filtros['checkin']) && $filtros['checkin'] === 'completado')>Completado</option>
                            <option value="pendiente" @selected(isset($filtros['checkin']) && $filtros['checkin'] === 'pendiente')>Pendiente</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="{{ route('admin.reservas.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        @if ($reservas->isEmpty())
            <div class="alert alert-info">No hay reservas con los filtros aplicados.</div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Localizador</th>
                        <th>Estado reserva</th>
                        <th>Estado check-in</th>
                        <th>Salida</th>
                        <th>Llegada</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($reservas as $reserva)
                        @php
                            $checkinCompletado = $reserva->checkin_estado === 'confirmada' || !is_null($reserva->checkin_realizado_en);
                        @endphp
                        <tr>
                            <td>{{ $reserva->id }}</td>
                            <td>{{ $reserva->localizador }}</td>
                            <td>
                                <span class="badge text-bg-secondary">{{ $reserva->estado }}</span>
                            </td>
                            <td>
                                @if ($checkinCompletado)
                                    <span class="badge text-bg-success">Completado</span>
                                @else
                                    <span class="badge text-bg-warning">Pendiente</span>
                                @endif
                            </td>
                            <td>{{ optional($reserva->fecha_salida)->format('d/m/Y H:i') }}</td>
                            <td>{{ optional($reserva->fecha_llegada)->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.reservas.show', $reserva) }}" class="btn btn-sm btn-outline-primary">
                                        Ver detalle
                                    </a>
                                    @if ($esSuperadmin)
                                        <a href="{{ route('admin.reservas.edit', $reserva) }}" class="btn btn-sm btn-outline-secondary">
                                            Editar
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $reservas->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
