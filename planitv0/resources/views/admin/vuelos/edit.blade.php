@extends('cabecera')

@section('contenido')
@php
    $minFechaHora = now()->format('Y-m-d\\TH:i');
@endphp
<div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Editar vuelo #{{ $vuelo->id }}</h2>
            <a href="{{ route('admin.vuelos.index') }}" class="btn btn-outline-secondary">Volver</a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($bloquearRuta)
            <div class="alert alert-warning" role="alert">
                Este vuelo tiene reservas confirmadas. No puedes modificar origen ni destino.
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.vuelos.update', $vuelo) }}" class="row g-3">
                    @csrf
                    @method('PUT')

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="origen_ciudad_id">Ciudad origen</label>
                        <select class="form-select" id="origen_ciudad_id" name="origen_ciudad_id" required @disabled($bloquearRuta)>
                            <option value="">Selecciona origen</option>
                            @foreach ($ciudades as $ciudad)
                                <option value="{{ $ciudad->id }}" @selected(old('origen_ciudad_id', $vuelo->origen_ciudad_id) == $ciudad->id)>{{ $ciudad->nombre }}</option>
                            @endforeach
                        </select>
                        @if ($bloquearRuta)
                            <input type="hidden" name="origen_ciudad_id" value="{{ old('origen_ciudad_id', $vuelo->origen_ciudad_id) }}">
                        @endif
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="destino_ciudad_id">Ciudad destino</label>
                        <select class="form-select" id="destino_ciudad_id" name="destino_ciudad_id" required @disabled($bloquearRuta)>
                            <option value="">Selecciona destino</option>
                            @foreach ($ciudades as $ciudad)
                                <option value="{{ $ciudad->id }}" @selected(old('destino_ciudad_id', $vuelo->destino_ciudad_id) == $ciudad->id)>{{ $ciudad->nombre }}</option>
                            @endforeach
                        </select>
                        @if ($bloquearRuta)
                            <input type="hidden" name="destino_ciudad_id" value="{{ old('destino_ciudad_id', $vuelo->destino_ciudad_id) }}">
                        @endif
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="fecha_salida">Fecha y hora de salida</label>
                        <input type="datetime-local" class="form-control" id="fecha_salida" name="fecha_salida"
                            value="{{ old('fecha_salida', optional($vuelo->fecha_salida)->format('Y-m-d\TH:i')) }}" min="{{ $minFechaHora }}" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="fecha_llegada">Fecha y hora de llegada</label>
                        <input type="datetime-local" class="form-control" id="fecha_llegada" name="fecha_llegada"
                            value="{{ old('fecha_llegada', optional($vuelo->fecha_llegada)->format('Y-m-d\TH:i')) }}" min="{{ $minFechaHora }}" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="precio">Precio</label>
                        <input type="number" min="0" step="0.01" class="form-control" id="precio" name="precio"
                            value="{{ old('precio', $vuelo->precio) }}" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="asientos_disponibles">Asientos disponibles</label>
                        <input type="number" min="0" step="1" class="form-control" id="asientos_disponibles"
                            name="asientos_disponibles" value="{{ old('asientos_disponibles', $vuelo->asientos_disponibles) }}" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="terminal">Terminal</label>
                        <input type="text" class="form-control" id="terminal" name="terminal"
                            value="{{ old('terminal', $vuelo->terminal) }}" maxlength="20">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="tipo_tarifa">Tipo tarifa</label>
                        <input type="text" class="form-control" id="tipo_tarifa" name="tipo_tarifa"
                            value="{{ old('tipo_tarifa', $vuelo->tipo_tarifa) }}" maxlength="50">
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-check mt-md-4 pt-md-2">
                            <input class="form-check-input" type="checkbox" id="es_schengen" name="es_schengen" value="1"
                                @checked(old('es_schengen', $vuelo->es_schengen))>
                            <label class="form-check-label" for="es_schengen">Vuelo Schengen</label>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-check mt-md-4 pt-md-2">
                            <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1"
                                @checked(old('activo', $vuelo->activo))>
                            <label class="form-check-label" for="activo">Vuelo activo</label>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('admin.vuelos.index') }}" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar vuelo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
