@extends('cabecera')

@push('scripts')
    <script src="{{ asset('js/baja-cuenta.js') }}"></script>
@endpush

@section('contenido')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm mt-5">
            <div class="card-header bg-danger text-white fw-bold">
                <i class="bi bi-person-x me-2"></i>Eliminar cuenta
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('area-personal.baja-cuenta.procesar') }}" id="form-baja-cuenta">
                    @csrf
                    <div class="alert alert-warning">
                        <strong>¿Estás seguro de que deseas eliminar tu cuenta?</strong><br>
                        Tu cuenta quedará inactiva y no podrá iniciar sesión mientras permanezca así.
                        Si vuelves a registrarte con el mismo correo, el sistema puede reactivarla.
                    </div>

                    <div class="mb-3">
                        <label for="motivo" class="form-label fw-semibold">Motivo de la baja <span class="text-danger">*</span></label>
                        <select class="form-select @error('motivo') is-invalid @enderror" id="motivo" name="motivo" required>
                            <option value="">-- Selecciona --</option>
                            <option value="problemas_web" @selected(old('motivo') == 'problemas_web')>Problemas con la web</option>
                            <option value="atencion_cliente" @selected(old('motivo') == 'atencion_cliente')>Servicio de atención al cliente</option>
                            <option value="no_necesito" @selected(old('motivo') == 'no_necesito')>No necesito la cuenta</option>
                            <option value="otro" @selected(old('motivo') == 'otro')>Otra razón</option>
                        </select>
                        @error('motivo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3" id="comentario-otro" style="display: none;">
                        <label for="comentario" class="form-label">Detalle el motivo (opcional)</label>
                        <textarea class="form-control @error('comentario') is-invalid @enderror" id="comentario" name="comentario" rows="2" maxlength="500">{{ old('comentario') }}</textarea>
                        @error('comentario')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Confirma tu contraseña <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required autocomplete="current-password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if ($errors->has('baja'))
                        <div class="alert alert-danger">{{ $errors->first('baja') }}</div>
                    @endif

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-danger px-5">
                            <i class="bi bi-person-x me-2"></i>Eliminar cuenta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
