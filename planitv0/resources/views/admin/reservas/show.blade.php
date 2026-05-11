@extends('cabecera')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-12 col-xl-11">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Reserva #{{ $reserva->id }}</h2>
                <p class="text-muted mb-0">Localizador: {{ $reserva->localizador }}</p>
            </div>
            <div class="d-flex gap-2">
                @if ($esSuperadmin)
                    <a href="{{ route('admin.reservas.edit', $reserva) }}" class="btn btn-outline-primary">Editar reserva</a>
                @endif
                <a href="{{ route('admin.reservas.index') }}" class="btn btn-outline-secondary">Volver</a>
            </div>
        </div>

        @if (session('exito'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('exito') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">Resumen de estado</h5>
                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <small class="text-muted d-block">Estado reserva</small>
                        <span class="badge text-bg-secondary">{{ $reserva->estado }}</span>
                    </div>
                    <div class="col-12 col-md-3">
                        <small class="text-muted d-block">Estado check-in</small>
                        @if ($reserva->checkin_estado === 'confirmada' || !is_null($reserva->checkin_realizado_en))
                            <span class="badge text-bg-success">Completado</span>
                        @else
                            <span class="badge text-bg-warning">Pendiente</span>
                        @endif
                    </div>
                    <div class="col-12 col-md-3">
                        <small class="text-muted d-block">Salida</small>
                        <span>{{ optional($reserva->fecha_salida)->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="col-12 col-md-3">
                        <small class="text-muted d-block">Llegada</small>
                        <span>{{ optional($reserva->fecha_llegada)->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">Vuelos asociados</h5>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <small class="text-muted d-block">Vuelo de ida</small>
                        @if ($reserva->vueloIda)
                            <span>
                                @if(isset($reserva->vueloIda->numero_vuelo)){{ $reserva->vueloIda->numero_vuelo }}@else N/D @endif -
                                @if(isset($reserva->vueloIda->origen)){{ $reserva->vueloIda->origen }}@else N/D @endif a @if(isset($reserva->vueloIda->destino)){{ $reserva->vueloIda->destino }}@else N/D @endif
                            </span>
                        @else
                            <span>-</span>
                        @endif
                    </div>
                    <div class="col-12 col-md-6">
                        <small class="text-muted d-block">Vuelo de vuelta</small>
                        @if ($reserva->vueloVuelta)
                            <span>
                                @if(isset($reserva->vueloVuelta->numero_vuelo)){{ $reserva->vueloVuelta->numero_vuelo }}@else N/D @endif -
                                @if(isset($reserva->vueloVuelta->origen)){{ $reserva->vueloVuelta->origen }}@else N/D @endif a @if(isset($reserva->vueloVuelta->destino)){{ $reserva->vueloVuelta->destino }}@else N/D @endif
                            </span>
                        @else
                            <span>-</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <h5 class="fw-semibold mb-0">Información de pasajeros</h5>
                    <span class="badge text-bg-dark">Datos protegidos</span>
                </div>

                @if ($pasajeros->isEmpty())
                    <div class="alert alert-info mb-0">No hay pasajeros asociados a esta reserva.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle mb-0">
                            <thead>
                            <tr>
                                <th>Origen de dato</th>
                                <th>Nombre</th>
                                <th>Apellidos</th>
                                <th>Documento</th>
                                <th>Fecha nacimiento</th>
                                <th>Check-in</th>
                                <th>Asiento</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($pasajeros as $pasajero)
                                <tr>
                                    <td>{{ $pasajero['fuente'] }}</td>
                                    <td>{{ $pasajero['nombre'] }}</td>
                                    <td>{{ $pasajero['apellidos'] }}</td>
                                    <td>{{ $pasajero['documento'] }}</td>
                                    <td>@if(isset($pasajero['fecha_nacimiento'])){{ $pasajero['fecha_nacimiento'] }}@else - @endif</td>
                                    <td>
                                        @if ($pasajero['checkin_completado'])
                                            <span class="badge text-bg-success">Completado</span>
                                        @else
                                            <span class="badge text-bg-warning">Pendiente</span>
                                        @endif
                                    </td>
                                    <td>@if(isset($pasajero['asiento'])){{ $pasajero['asiento'] }}@else - @endif</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
