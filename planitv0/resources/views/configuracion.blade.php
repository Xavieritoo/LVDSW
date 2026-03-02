@extends('cabecera')

@section('contenido')
    <div class="card mx-auto p-4" style="max-width:600px;">
        <h2 class="text-center mb-4">Configurar cuenta</h2>

        {{-- Mensaje éxito --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Errores --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="configCuentaForm" method="POST" action="{{ route('configuracion.submit') }}" novalidate>
            @csrf

            <!-- Nombre -->
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <div class="input-group">
                    <input type="text" name="nombre" id="nombre"
                        class="form-control bg-light text-muted border-end-0" value="{{ $usuario->nombre ?? '' }}" readonly>
                    <span class="input-group-text bg-white border-start-0 d-none" id="nombreCheck">
                        <i class="bi bi-check-lg text-success"></i>
                    </span>
                </div>
                <small id="nombreError" class="text-danger d-none">Nombre obligatorio.</small>
            </div>

            <!-- Apellidos -->
            <div class="mb-3">
                <label for="apellidos" class="form-label">Apellidos</label>
                <div class="input-group">
                    <input type="text" name="apellidos" id="apellidos"
                        class="form-control bg-light text-muted border-end-0" value="{{ $usuario->apellidos ?? '' }}"
                        readonly>
                    <span class="input-group-text bg-white border-start-0 d-none" id="apellidosCheck">
                        <i class="bi bi-check-lg text-success"></i>
                    </span>
                </div>
                <small id="apellidosError" class="text-danger d-none">Apellidos obligatorios.</small>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico</label>
                <div class="input-group">
                    <input type="email" name="email" id="email"
                        class="form-control bg-light text-muted border-end-0" value="{{ $usuario->email ?? '' }}" readonly>
                    <span class="input-group-text bg-white border-start-0 d-none" id="emailCheck">
                        <i class="bi bi-check-lg text-success"></i>
                    </span>
                </div>
                <small id="emailError" class="text-danger d-none">Email inválido.</small>
            </div>

            <!-- Fecha de nacimiento -->
            <div class="mb-3">
                <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento</label>
                <div class="input-group">
                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control border-end-0"
                        value="{{ old('fecha_nacimiento', $usuario->fecha_nacimiento ? \Carbon\Carbon::parse($usuario->fecha_nacimiento)->format('Y-m-d') : '') }}">
                    <span class="input-group-text bg-white border-start-0 d-none" id="fechaCheck">
                        <i class="bi bi-check-lg text-success"></i>
                    </span>
                </div>
                <small id="fechaError" class="text-danger d-none">Fecha inválida.</small>
            </div>

            <!-- Documento identificativo -->
            <div class="mb-3">
                <label for="documento_identidad" class="form-label">Documento de identidad</label>
                <div class="input-group">
                    <input type="text" name="documento_identidad" id="documento_identidad"
                        class="form-control border-end-0"
                        value="{{ old('documento_identidad', $usuario->documento_identidad ?? '') }}">
                    <span class="input-group-text bg-white border-start-0 d-none" id="documentoCheck">
                        <i class="bi bi-check-lg text-success"></i>
                    </span>
                </div>
                <small id="documentoError" class="text-danger d-none">Documento inválido.</small>
            </div>

            <!-- Prefijo y teléfono -->
            <div class="mb-3">
                <label for="prefijo" class="form-label">Prefijo telefónico</label>
                <div class="input-group mb-2">
                    <select name="prefijo" id="prefijo" class="form-select border-end-0">
                        <option value="">Selecciona un prefijo</option>
                        @foreach ($paises as $codigo => $info)
                            <option value="{{ $info['prefijo'] }}"
                                {{ old('prefijo', $prefijoSeleccionado ?? '') === $info['prefijo'] ? 'selected' : '' }}>
                                {{ $info['prefijo'] }} ({{ $info['nombre'] }})
                            </option>
                        @endforeach
                    </select>
                    <span class="input-group-text bg-white border-start-0 d-none" id="telefonoCheck">
                        <i class="bi bi-check-lg text-success"></i>
                    </span>
                </div>

                <label for="telefono" class="form-label">Número de teléfono</label>
                <div class="input-group">
                    <input type="text" name="telefono" id="telefono" class="form-control border-end-0"
                        value="{{ old('telefono', $telefonoNumero ?? '') }}">
                    <span class="input-group-text bg-white border-start-0 d-none" id="telefonoCheck">
                        <i class="bi bi-check-lg text-success"></i>
                    </span>
                </div>
                <small id="telefonoError" class="text-danger d-none">Teléfono inválido.</small>
            </div>

            <!-- País -->
            <div class="mb-3">
                <label for="pais" class="form-label">País</label>
                <div class="input-group">
                    <select name="pais" id="pais" class="form-select border-end-0">
                        <option value="">Selecciona un país</option>
                        @foreach ($paises as $codigo => $info)
                            <option value="{{ $codigo }}"
                                {{ old('pais', $paisSeleccionado ?? $usuario->pais) === $codigo ? 'selected' : '' }}>
                                {{ $info['nombre'] }}
                            </option>
                        @endforeach
                    </select>
                    <span class="input-group-text bg-white border-start-0 d-none" id="paisCheck">
                        <i class="bi bi-check-lg text-success"></i>
                    </span>
                </div>
            </div>

            <!-- Código postal -->
            <div class="mb-3">
                <label for="codigo_postal" class="form-label">Código postal</label>
                <div class="input-group">
                    <input type="text" name="codigo_postal" id="codigo_postal" class="form-control border-end-0"
                        value="{{ old('codigo_postal', $usuario->codigo_postal ?? '') }}">
                    <span class="input-group-text bg-white border-start-0 d-none" id="codigoCheck">
                        <i class="bi bi-check-lg text-success"></i>
                    </span>
                </div>
                <small id="codigoError" class="text-danger d-none">Código postal inválido.</small>
            </div>

            <!-- Población -->
            <div class="mb-3">
                <label for="poblacion" class="form-label">Población</label>
                <div class="input-group">
                    <input type="text" name="poblacion" id="poblacion" class="form-control border-end-0"
                        value="{{ old('poblacion', $usuario->poblacion ?? '') }}">
                    <span class="input-group-text bg-white border-start-0 d-none" id="poblacionCheck">
                        <i class="bi bi-check-lg text-success"></i>
                    </span>
                </div>
            </div>

            <!-- Dirección -->
            <div class="mb-3">
                <label for="direccion" class="form-label">Dirección</label>
                <div class="input-group">
                    <input type="text" name="direccion" id="direccion" class="form-control border-end-0"
                        value="{{ old('direccion', $usuario->direccion ?? '') }}">
                    <span class="input-group-text bg-white border-start-0 d-none" id="direccionCheck">
                        <i class="bi bi-check-lg text-success"></i>
                    </span>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('principal') }}" class="btn btn-outline-secondary w-50">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-primary w-50">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>

    <script src="{{ asset('js/configuracion.js') }}"></script>
@endsection
