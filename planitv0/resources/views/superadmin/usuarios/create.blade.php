@extends('cabecera')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Crear usuario</h2>
            <a href="{{ route('superadmin.usuarios.index') }}" class="btn btn-outline-secondary">Volver</a>
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

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('superadmin.usuarios.store') }}" class="row g-3">
                    @csrf

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="nombre">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="apellidos">Apellidos</label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos" value="{{ old('apellidos') }}" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="password">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="rol_id">Rol</label>
                        <select class="form-select" id="rol_id" name="rol_id" required>
                            <option value="">Selecciona rol</option>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id }}" @selected((int) old('rol_id') === (int) $rol->id)>
                                    {{ ucfirst($rol->nombre) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="form-check mt-md-4 pt-md-2">
                            <input class="form-check-input" type="checkbox" id="esta_activo" name="esta_activo" value="1" @checked(old('esta_activo', true))>
                            <label class="form-check-label" for="esta_activo">Activo</label>
                        </div>
                    </div>

                    <div class="col-12 col-md-3">
                        <div class="form-check mt-md-4 pt-md-2">
                            <input class="form-check-input" type="checkbox" id="esta_verificado" name="esta_verificado" value="1" @checked(old('esta_verificado', true))>
                            <label class="form-check-label" for="esta_verificado">Verificado</label>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('superadmin.usuarios.index') }}" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
