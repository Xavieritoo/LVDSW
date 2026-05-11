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
                        <h2 class="fw-bold mb-1">Editar pasajero frecuente</h2>
                        <p class="text-muted">Actualiza los datos de este pasajero.</p>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong>Corrige los siguientes errores:</strong>
                                    <ul class="mb-0 mt-1">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('pasajeros-frecuentes.update', $pasajero['id']) }}" class="row g-3">
                                @csrf
                                @method('PUT')
                                @php
                                    $nombrePasajero = '';
                                    if (array_key_exists('nombre', $pasajero)) {
                                        $nombrePasajero = $pasajero['nombre'];
                                    }
                                    $apellidosPasajero = '';
                                    if (array_key_exists('apellidos', $pasajero)) {
                                        $apellidosPasajero = $pasajero['apellidos'];
                                    }
                                    $fechaPasajero = '';
                                    if (array_key_exists('fecha_nacimiento', $pasajero)) {
                                        $fechaPasajero = $pasajero['fecha_nacimiento'];
                                    }
                                    $paisPasajero = '';
                                    if (array_key_exists('pais', $pasajero)) {
                                        $paisPasajero = $pasajero['pais'];
                                    }
                                @endphp

                                <div class="col-12">
                                    <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre"
                                        value="{{ old('nombre', $nombrePasajero) }}" required maxlength="100">
                                </div>

                                <div class="col-12">
                                    <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('apellidos') is-invalid @enderror" id="apellidos" name="apellidos"
                                        value="{{ old('apellidos', $apellidosPasajero) }}" required maxlength="150">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror" id="fecha_nacimiento" name="fecha_nacimiento"
                                        value="{{ old('fecha_nacimiento', $fechaPasajero) }}" min="1910-01-01" max="{{ now()->format('Y-m-d') }}" required>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="pais" class="form-label">País</label>
                                    <select class="form-select @error('pais') is-invalid @enderror" id="pais" name="pais">
                                        <option value="">Seleccionar país...</option>
                                        @foreach ($paises as $p)
                                            <option value="{{ $p }}" @selected(old('pais', $paisPasajero) === $p)>{{ $p }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 d-flex justify-content-end gap-2">
                                    <a href="{{ route('pasajeros-frecuentes.show', $pasajero['id']) }}" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
