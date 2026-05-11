@extends('cabecera')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-12 col-xl-11">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Editar reserva #{{ $reserva->id }}</h2>
                <p class="text-muted mb-0">Localizador: {{ $reserva->localizador }}</p>
            </div>
            <a href="{{ route('admin.reservas.show', $reserva) }}" class="btn btn-outline-secondary">Volver al detalle</a>
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

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.reservas.update', $reserva) }}" class="row g-4">
                    @csrf
                    @method('PUT')

                    <div class="col-12 col-md-6">
                        <label for="estado" class="form-label">Estado de reserva</label>
                        <select id="estado" name="estado" class="form-select" required>
                            <option value="confirmada" @selected(old('estado', $reserva->estado) === 'confirmada')>Confirmada</option>
                            <option value="completada" @selected(old('estado', $reserva->estado) === 'completada')>Completada</option>
                            <option value="cancelada_usuario" @selected(old('estado', $reserva->estado) === 'cancelada_usuario')>Cancelada por usuario</option>
                            <option value="cancelada_aerolinea" @selected(old('estado', $reserva->estado) === 'cancelada_aerolinea')>Cancelada por aerolinea</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="checkin_estado" class="form-label">Estado de check-in</label>
                        <select id="checkin_estado" name="checkin_estado" class="form-select" required>
                            @php
                                if (isset($reserva->checkin_estado)) {
                                    $checkinVal = $reserva->checkin_estado;
                                } else {
                                    $checkinVal = 'pendiente';
                                }
                            @endphp
                            <option value="pendiente" @selected(old('checkin_estado', $checkinVal) === 'pendiente')>Pendiente</option>
                            <option value="confirmada" @selected(old('checkin_estado', $reserva->checkin_estado) === 'confirmada')>Completado</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <h5 class="fw-semibold mb-3">Pasajeros y asientos</h5>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Apellidos</th>
                                    <th>Tipo doc.</th>
                                    <th>Numero doc.</th>
                                    <th>Fecha nacimiento</th>
                                    <th>Asiento</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($pasajeros as $pasajero)
                                    @php
                                        $k = 'pasajeros.' . $loop->index;
                                    @endphp
                                    <tr>
                                        <td>
                                            <input type="hidden" name="pasajeros[{{ $loop->index }}][id]" value="{{ $pasajero->id }}">
                                            <input type="text" name="pasajeros[{{ $loop->index }}][nombre]" class="form-control"
                                                value="{{ old($k . '.nombre', $pasajero->nombre) }}" required>
                                        </td>
                                        <td>
                                            <input type="text" name="pasajeros[{{ $loop->index }}][apellidos]" class="form-control"
                                                value="{{ old($k . '.apellidos', $pasajero->apellidos) }}" required>
                                        </td>
                                        <td>
                                            <select name="pasajeros[{{ $loop->index }}][tipo_documento]" class="form-select" required>
                                                <option value="DNI" @selected(old($k . '.tipo_documento', $pasajero->tipo_documento) === 'DNI')>DNI</option>
                                                <option value="PASAPORTE" @selected(old($k . '.tipo_documento', $pasajero->tipo_documento) === 'PASAPORTE')>Pasaporte</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="pasajeros[{{ $loop->index }}][numero_documento]" class="form-control"
                                                value="{{ old($k . '.numero_documento', $pasajero->numero_documento) }}" required>
                                        </td>
                                        <td>
                                            <input type="date" name="pasajeros[{{ $loop->index }}][fecha_nacimiento]" class="form-control"
                                                value="{{ old($k . '.fecha_nacimiento', optional($pasajero->fecha_nacimiento)->format('Y-m-d')) }}" required>
                                        </td>
                                        <td>
                                            <input type="text" name="pasajeros[{{ $loop->index }}][asiento_codigo]" class="form-control"
                                                value="{{ old($k . '.asiento_codigo', $pasajero->asiento_codigo) }}"
                                                placeholder="Ej: 12C" pattern="^(?:[1-9]|[12][0-9]|30)[A-F]$"
                                                title="Formato valido: 1-30 seguido de A-F (ejemplo: 12C)">
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.reservas.show', $reserva) }}" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
