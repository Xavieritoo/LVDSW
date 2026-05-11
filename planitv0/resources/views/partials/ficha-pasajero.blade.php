<div class="passenger-card">
    @php
        if (isset($pasajerosFrecuentesPorTipo[$tipo])) {
            $frecuentesTipo = $pasajerosFrecuentesPorTipo[$tipo];
        } else {
            $frecuentesTipo = [];
        }
        $modalId = 'modal_frecuentes_' . $tipo . '_' . $index;
    @endphp

    <h6>{{ $titulo }} {{ $numero }}</h6>
    <div class="row g-3">

        @if (!empty($frecuentesTipo))
            <div class="col-12">
                <button type="button"
                    class="btn btn-outline-primary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#{{ $modalId }}">
                    Añadir pasajero frecuente
                </button>
            </div>
        @endif

        <div class="col-md-6">
            <label class="field-label" for="{{ $tipo }}_nombre_{{ $index }}">Nombre</label>
            <input type="text" class="form-control" id="{{ $tipo }}_nombre_{{ $index }}"
                name="{{ $tipo }}[{{ $index }}][nombre]" value="{{ old("$tipo.$index.nombre") }}" required>
            @error("$tipo.$index.nombre")
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="field-label" for="{{ $tipo }}_apellidos_{{ $index }}">Apellidos</label>
            <input type="text" class="form-control" id="{{ $tipo }}_apellidos_{{ $index }}"
                name="{{ $tipo }}[{{ $index }}][apellidos]" value="{{ old("$tipo.$index.apellidos") }}" required>
            @error("$tipo.$index.apellidos")
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

        <div class="col-md-6">
            <label class="field-label" for="{{ $tipo }}_fecha_{{ $index }}">Fecha de nacimiento</label>
            <input type="date" class="form-control" id="{{ $tipo }}_fecha_{{ $index }}"
                name="{{ $tipo }}[{{ $index }}][fecha_nacimiento]"
                value="{{ old("$tipo.$index.fecha_nacimiento") }}" required>
            @error("$tipo.$index.fecha_nacimiento")
                <small class="text-danger">{{ $message }}</small>
            @enderror
        </div>

    </div>

    @if (!empty($frecuentesTipo))
        <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Selecciona pasajero frecuente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="list-group">
                            @foreach ($frecuentesTipo as $frecuente)
                                <button type="button"
                                    class="list-group-item list-group-item-action js-seleccionar-frecuente"
                                    data-bs-dismiss="modal"
                                    data-tipo="{{ $tipo }}"
                                    data-index="{{ $index }}"
                                    data-id="{{ $frecuente['id'] }}"
                                    data-nombre="{{ $frecuente['nombre'] }}"
                                    data-apellidos="{{ $frecuente['apellidos'] }}"
                                    data-fecha-nacimiento="@if(isset($frecuente['fecha_nacimiento'])){{ $frecuente['fecha_nacimiento'] }}@endif">
                                    <div class="fw-semibold">{{ $frecuente['nombre'] }} {{ $frecuente['apellidos'] }}</div>
                                    @if (!empty($frecuente['fecha_nacimiento']))
                                        <small class="text-muted">Nacimiento: {{ \Carbon\Carbon::parse($frecuente['fecha_nacimiento'])->format('d/m/Y') }}</small>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
