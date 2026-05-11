@extends('cabecera')

@section('contenido')

<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">

        {{-- Encabezado --}}
        <div class="mb-4">
            <a href="{{ route('area-personal') }}" class="text-decoration-none text-secondary small">
                <i class="bi bi-arrow-left me-1"></i>Volver al área personal
            </a>
            <h3 class="fw-bold mt-2 mb-1">Cambiar contraseña</h3>
            <p class="text-muted small">
                Introduce tu contraseña actual y elige una nueva que cumpla los requisitos de seguridad.
            </p>
        </div>

        {{-- Alerta de éxito --}}
        @if (session('exito'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('exito') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Errores generales --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Formulario --}}
        <form id="form-cambiar-password" method="POST"
            action="{{ route('area-personal.cambiar-password.submit') }}" novalidate>
            @csrf
            @method('PUT')

            {{-- Contraseña actual --}}
            <div class="mb-3">
                <label for="password_actual" class="form-label fw-semibold">
                    Contraseña actual <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="password"
                        class="form-control @error('password_actual') is-invalid @enderror"
                        id="password_actual" name="password_actual"
                        autocomplete="current-password">
                    <button class="btn btn-outline-secondary toggle-pass" type="button"
                        data-target="password_actual" tabindex="-1">
                        <i class="bi bi-eye"></i>
                    </button>
                    @error('password_actual')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Nueva contraseña --}}
            <div class="mb-3">
                <label for="password_nuevo" class="form-label fw-semibold">
                    Nueva contraseña <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="password"
                        class="form-control @error('password_nuevo') is-invalid @enderror"
                        id="password_nuevo" name="password_nuevo"
                        autocomplete="new-password">
                    <button class="btn btn-outline-secondary toggle-pass" type="button"
                        data-target="password_nuevo" tabindex="-1">
                        <i class="bi bi-eye"></i>
                    </button>
                    @error('password_nuevo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                {{-- Indicadores de política --}}
                <ul class="list-unstyled mt-2 small" id="pass-requisitos">
                    <li id="req-len"  class="text-secondary"><i class="bi bi-x-circle me-1"></i>Mínimo 5 caracteres</li>
                    <li id="req-may"  class="text-secondary"><i class="bi bi-x-circle me-1"></i>Al menos una mayúscula</li>
                    <li id="req-num"  class="text-secondary"><i class="bi bi-x-circle me-1"></i>Al menos un número</li>
                </ul>
            </div>

            {{-- Confirmar nueva contraseña --}}
            <div class="mb-4">
                <label for="password_nuevo_confirmation" class="form-label fw-semibold">
                    Confirmar nueva contraseña <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="password"
                        class="form-control"
                        id="password_nuevo_confirmation" name="password_nuevo_confirmation"
                        autocomplete="new-password">
                    <button class="btn btn-outline-secondary toggle-pass" type="button"
                        data-target="password_nuevo_confirmation" tabindex="-1">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div id="confirm-error" class="text-danger small mt-1 d-none">
                    Las contraseñas no coinciden.
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100" id="btn-cambiar">
                <i class="bi bi-shield-check me-2"></i>Cambiar contraseña
            </button>

        </form>
    </div>
</div>

<script src="{{ asset('js/cambiar-password.js') }}"></script>

@endsection
