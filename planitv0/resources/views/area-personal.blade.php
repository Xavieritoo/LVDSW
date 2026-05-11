@extends('cabecera')

@push('styles')
    <link href="{{ asset('css/area-personal.css') }}" rel="stylesheet">
@endpush

@push('scripts')
    <script src="{{ asset('js/area-personal.js') }}"></script>
    <script src="{{ asset('js/cambiar-password.js') }}"></script>
    <script src="{{ asset('js/area-personal-view.js') }}"></script>
@endpush

@section('contenido')

@php
    $openPasswordModalData = '0';
    if ($errors->password->any()) {
        $openPasswordModalData = '1';
    }
@endphp

<div class="row justify-content-center" id="area-personal-page" data-open-password-modal="{{ $openPasswordModalData }}">
    <div class="col-12 col-xl-11">

        {{-- Título --}}
        <div class="mb-4">
            <h2 class="fw-bold mb-1">Área Personal</h2>
            <p class="text-muted">Gestiona tu información personal y de contacto.</p>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-12 col-lg-3">
                @include('partials.sidebar-area-personal')
            </div>

            <div class="col-12 col-lg-9">

        {{-- Alertas --}}
        @if (session('exito'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('exito') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Corrige los siguientes errores:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Formulario --}}
        <form id="form-perfil" method="POST" action="{{ route('area-personal.actualizar') }}" novalidate>
            @csrf
            @method('PUT')

            {{-- ── Datos personales ──────────────────────────────────────── --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header fw-semibold bg-white">
                    <i class="bi bi-person-lines-fill me-2 text-primary"></i>Datos Personales
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        {{-- Nombre --}}
                        <div class="col-12 col-md-6">
                            <label for="nombre" class="form-label fw-semibold">
                                Nombre <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                id="nombre" name="nombre"
                                value="{{ old('nombre', $usuario->nombre) }}"
                                maxlength="100" autocomplete="given-name">
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Apellidos --}}
                        <div class="col-12 col-md-6">
                            <label for="apellidos" class="form-label fw-semibold">
                                Apellidos <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('apellidos') is-invalid @enderror"
                                id="apellidos" name="apellidos"
                                value="{{ old('apellidos', $usuario->apellidos) }}"
                                maxlength="150" autocomplete="family-name">
                            @error('apellidos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label fw-semibold">
                                Correo electrónico <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="email" name="email"
                                value="{{ old('email', $usuario->email) }}"
                                maxlength="150" autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Fecha de nacimiento --}}
                        <div class="col-12 col-md-6">
                            <label for="fecha_nacimiento" class="form-label fw-semibold">Fecha de nacimiento</label>
                            @php
                                $fechaNacimientoBase = '';
                                if ($perfil && $perfil->fecha_nacimiento) {
                                    $fechaNacimientoBase = $perfil->fecha_nacimiento->format('Y-m-d');
                                }
                            @endphp
                            <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                                id="fecha_nacimiento" name="fecha_nacimiento"
                                value="{{ old('fecha_nacimiento', $fechaNacimientoBase) }}"
                                min="1905-01-01" max="{{ now()->format('Y-m-d') }}" required>
                            @error('fecha_nacimiento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Prefijo telefónico --}}
                        <div class="col-12 col-md-6">
                            <label for="telefono_prefijo" class="form-label fw-semibold">Prefijo telefónico</label>
                            <select class="form-select @error('telefono_prefijo') is-invalid @enderror"
                                id="telefono_prefijo" name="telefono_prefijo">
                                <option value="">-- Selecciona --</option>
                                @php
                                    $paisesConPrefijo = [
                                        ['pais' => 'España', 'prefijo' => '+34'],
                                        ['pais' => 'Alemania', 'prefijo' => '+49'],
                                        ['pais' => 'Francia', 'prefijo' => '+33'],
                                        ['pais' => 'Italia', 'prefijo' => '+39'],
                                        ['pais' => 'Portugal', 'prefijo' => '+351'],
                                        ['pais' => 'Reino Unido', 'prefijo' => '+44'],
                                        ['pais' => 'Países Bajos', 'prefijo' => '+31'],
                                        ['pais' => 'Bélgica', 'prefijo' => '+32'],
                                        ['pais' => 'Suiza', 'prefijo' => '+41'],
                                        ['pais' => 'Austria', 'prefijo' => '+43'],
                                        ['pais' => 'Suecia', 'prefijo' => '+46'],
                                        ['pais' => 'Noruega', 'prefijo' => '+47'],
                                        ['pais' => 'Dinamarca', 'prefijo' => '+45'],
                                        ['pais' => 'Finlandia', 'prefijo' => '+358'],
                                        ['pais' => 'Polonia', 'prefijo' => '+48'],
                                        ['pais' => 'Rumanía', 'prefijo' => '+40'],
                                        ['pais' => 'Hungría', 'prefijo' => '+36'],
                                        ['pais' => 'Chequia', 'prefijo' => '+420'],
                                        ['pais' => 'Grecia', 'prefijo' => '+30'],
                                        ['pais' => 'Bulgaria', 'prefijo' => '+359'],
                                        ['pais' => 'Croacia', 'prefijo' => '+385'],
                                        ['pais' => 'Eslovaquia', 'prefijo' => '+421'],
                                        ['pais' => 'Eslovenia', 'prefijo' => '+386'],
                                        ['pais' => 'Estonia', 'prefijo' => '+372'],
                                        ['pais' => 'Letonia', 'prefijo' => '+371'],
                                        ['pais' => 'Lituania', 'prefijo' => '+370'],
                                        ['pais' => 'Luxemburgo', 'prefijo' => '+352'],
                                        ['pais' => 'Malta', 'prefijo' => '+356'],
                                        ['pais' => 'Chipre', 'prefijo' => '+357'],
                                        ['pais' => 'Irlanda', 'prefijo' => '+353'],
                                        ['pais' => 'Estados Unidos', 'prefijo' => '+1'],
                                        ['pais' => 'Canadá', 'prefijo' => '+1'],
                                        ['pais' => 'México', 'prefijo' => '+52'],
                                        ['pais' => 'Argentina', 'prefijo' => '+54'],
                                        ['pais' => 'Brasil', 'prefijo' => '+55'],
                                        ['pais' => 'Chile', 'prefijo' => '+56'],
                                        ['pais' => 'Colombia', 'prefijo' => '+57'],
                                        ['pais' => 'Venezuela', 'prefijo' => '+58'],
                                        ['pais' => 'Perú', 'prefijo' => '+51'],
                                        ['pais' => 'Ecuador', 'prefijo' => '+593'],
                                        ['pais' => 'Bolivia', 'prefijo' => '+591'],
                                        ['pais' => 'Paraguay', 'prefijo' => '+595'],
                                        ['pais' => 'Uruguay', 'prefijo' => '+598'],
                                        ['pais' => 'Japón', 'prefijo' => '+81'],
                                        ['pais' => 'China', 'prefijo' => '+86'],
                                        ['pais' => 'Corea del Sur', 'prefijo' => '+82'],
                                        ['pais' => 'India', 'prefijo' => '+91'],
                                        ['pais' => 'Emiratos Árabes Unidos', 'prefijo' => '+971'],
                                        ['pais' => 'Arabia Saudí', 'prefijo' => '+966'],
                                        ['pais' => 'Israel', 'prefijo' => '+972'],
                                        ['pais' => 'Turquía', 'prefijo' => '+90'],
                                        ['pais' => 'Egipto', 'prefijo' => '+20'],
                                        ['pais' => 'Sudáfrica', 'prefijo' => '+27'],
                                        ['pais' => 'Marruecos', 'prefijo' => '+212'],
                                        ['pais' => 'Australia', 'prefijo' => '+61'],
                                        ['pais' => 'Nueva Zelanda', 'prefijo' => '+64'],
                                        ['pais' => 'Rusia', 'prefijo' => '+7'],
                                        ['pais' => 'Ucrania', 'prefijo' => '+380'],
                                    ];
                                    usort($paisesConPrefijo, function ($a, $b) {
                                        return strcmp(mb_strtolower($a['pais']), mb_strtolower($b['pais']));
                                    });
                                    $telefonoPrefijoBase = $telefonoPrefijo;
                                    if (!$telefonoPrefijoBase) {
                                        $telefonoPrefijoBase = '';
                                    }
                                    $paisBase = '';
                                    if ($perfil && $perfil->pais) {
                                        $paisBase = $perfil->pais;
                                    }
                                    $prefActual = old('telefono_prefijo', $telefonoPrefijoBase);
                                    $paisActual = old('pais', $paisBase);
                                    $prefijoSeleccionadoAplicado = false;
                                @endphp
                                @foreach ($paisesConPrefijo as $item)
                                    @php
                                        $isSelected = !$prefijoSeleccionadoAplicado && $prefActual === $item['prefijo'];
                                        if ($isSelected) {
                                            $prefijoSeleccionadoAplicado = true;
                                        }
                                    @endphp
                                    <option value="{{ $item['prefijo'] }}" @selected($isSelected)>
                                        {{ $item['pais'] }} ({{ $item['prefijo'] }})
                                    </option>
                                @endforeach
                            </select>
                            @error('telefono_prefijo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Teléfono móvil --}}
                        <div class="col-12 col-md-6">
                            <label for="telefono_numero" class="form-label fw-semibold">Teléfono móvil</label>
                            @php
                                $telefonoNumeroBase = $telefonoNumero;
                                if (!$telefonoNumeroBase) {
                                    $telefonoNumeroBase = '';
                                }
                            @endphp
                            <input type="text" class="form-control @error('telefono_numero') is-invalid @enderror"
                                id="telefono_numero" name="telefono_numero"
                                value="{{ old('telefono_numero', $telefonoNumeroBase) }}"
                                placeholder="612345678" maxlength="10" inputmode="numeric">
                            @error('telefono_numero')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- País --}}
                        <div class="col-12 col-md-6">
                            <label for="pais" class="form-label fw-semibold">País de residencia</label>
                            <select class="form-select @error('pais') is-invalid @enderror"
                                id="pais" name="pais">
                                <option value="">-- Selecciona un país --</option>
                                @foreach ($paisesConPrefijo as $item)
                                    <option value="{{ $item['pais'] }}" @selected($paisActual === $item['pais'])>
                                        {{ $item['pais'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pais')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Ciudad --}}
                        <div class="col-12 col-md-6">
                            <label for="ciudad" class="form-label fw-semibold">Ciudad</label>
                            @php
                                $ciudadBase = '';
                                if ($perfil && $perfil->ciudad) {
                                    $ciudadBase = $perfil->ciudad;
                                }
                            @endphp
                            <input type="text" class="form-control @error('ciudad') is-invalid @enderror"
                                id="ciudad" name="ciudad"
                                value="{{ old('ciudad', $ciudadBase) }}"
                                maxlength="100" placeholder="Madrid">
                            @error('ciudad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Dirección --}}
                        <div class="col-12 col-md-6">
                            <label for="direccion" class="form-label fw-semibold">Dirección</label>
                            @php
                                $direccionBase = '';
                                if ($perfil && $perfil->direccion) {
                                    $direccionBase = $perfil->direccion;
                                }
                            @endphp
                            <input type="text" class="form-control @error('direccion') is-invalid @enderror"
                                id="direccion" name="direccion"
                                value="{{ old('direccion', $direccionBase) }}"
                                maxlength="150" placeholder="Calle Ejemplo, 1, 2º A">
                            @error('direccion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Código postal --}}
                        <div class="col-12 col-md-6">
                            <label for="codigo_postal" class="form-label fw-semibold">Código postal</label>
                            @php
                                $codigoPostalBase = '';
                                if ($perfil && $perfil->codigo_postal) {
                                    $codigoPostalBase = $perfil->codigo_postal;
                                }
                            @endphp
                            <input type="text" class="form-control @error('codigo_postal') is-invalid @enderror"
                                id="codigo_postal" name="codigo_postal"
                                value="{{ old('codigo_postal', $codigoPostalBase) }}"
                                maxlength="10" placeholder="28001"
                                style="text-transform: uppercase">
                            @error('codigo_postal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    {{-- Botón guardar --}}
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary px-5">
                            <i class="bi bi-floppy me-2"></i>Guardar cambios
                        </button>
                    </div>
                </div>
            </div>

        </form>

        {{-- ── Cambio de contraseña ──────────────────────────────────────── --}}
        <div class="card mb-3 shadow-sm border-secondary-subtle">
            <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <p class="fw-semibold mb-0">¿Desea cambiar su contraseña?</p>
                    <p class="text-muted small mb-0">
                        Mantén tu cuenta segura actualizando tu contraseña regularmente.
                    </p>
                </div>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalCambiarPassword">
                    <i class="bi bi-shield-lock me-2"></i>Cambiar contraseña
                </button>
            </div>
        </div>

            {{-- ── Baja de cuenta ──────────────────────────────────────── --}}

            <div class="card mb-4 shadow-sm border-danger-subtle">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <p class="fw-semibold mb-0 text-danger">¿Desea eliminar su cuenta?</p>
                        <p class="text-muted small mb-0">
                            Esta acción desactiva el acceso de tu cuenta. Podrás gestionar tus reservas como invitado tras la baja.
                        </p>
                    </div>
                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalBajaCuenta">
                        <i class="bi bi-person-x me-2"></i>Eliminar cuenta
                    </button>
                </div>
            </div>

            {{-- Modal: Baja de cuenta --}}

            <div class="modal fade" id="modalBajaCuenta" tabindex="-1" aria-labelledby="modalBajaCuentaLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalBajaCuentaLabel">
                                <i class="bi bi-person-x me-2 text-danger"></i>Eliminar cuenta
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <form method="POST" action="{{ route('area-personal.baja-cuenta.procesar') }}" id="form-baja-cuenta">
                            @csrf
                            <div class="modal-body">
                                <p class="text-muted small mb-3">
                                    Confirma tu decisión. Tu cuenta quedará inactiva y no podrá iniciar sesión mientras permanezca así.
                                    Si vuelves a registrarte con el mismo correo, el sistema puede reactivarla.
                                </p>

                                <div class="mb-3">
                                    <label for="motivo" class="form-label fw-semibold">Motivo de la baja</label>
                                    <select class="form-select @error('motivo') is-invalid @enderror" id="motivo" name="motivo">
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
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required autocomplete="current-password">
                                        <button class="btn btn-outline-secondary toggle-pass" type="button" data-target="password">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                @if ($errors->has('baja'))
                                    <div class="alert alert-danger">{{ $errors->first('baja') }}</div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger px-4">
                                    <i class="bi bi-person-x me-2"></i>Eliminar cuenta
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            </div>
        </div>

    </div>
</div>

{{-- Modal: Cambiar contraseña --}}
<div class="modal fade" id="modalCambiarPassword" tabindex="-1" aria-labelledby="modalCambiarPasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCambiarPasswordLabel">
                    <i class="bi bi-shield-lock me-2 text-primary"></i>Cambiar contraseña
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="form-cambiar-password" method="POST"
                action="{{ route('area-personal.cambiar-password.submit') }}" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Introduce tu contraseña actual y elige una nueva que cumpla los requisitos de seguridad.
                    </p>

                    @if ($errors->password->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <ul class="mb-0">
                                @foreach ($errors->password->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Contraseña actual --}}
                    <div class="mb-3">
                        <label for="password_actual" class="form-label fw-semibold">
                            Contraseña actual <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password"
                                @class(['form-control', 'is-invalid' => $errors->password->has('password_actual')])
                                id="password_actual" name="password_actual"
                                autocomplete="current-password">
                            <button class="btn btn-outline-secondary toggle-pass" type="button"
                                data-target="password_actual" tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                            @if ($errors->password->has('password_actual'))
                                <div class="invalid-feedback">{{ $errors->password->first('password_actual') }}</div>
                            @endif
                        </div>
                    </div>

                    {{-- Nueva contraseña --}}
                    <div class="mb-3">
                        <label for="password_nuevo" class="form-label fw-semibold">
                            Nueva contraseña <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password"
                                @class(['form-control', 'is-invalid' => $errors->password->has('password_nuevo')])
                                id="password_nuevo" name="password_nuevo"
                                autocomplete="new-password">
                            <button class="btn btn-outline-secondary toggle-pass" type="button"
                                data-target="password_nuevo" tabindex="-1">
                                <i class="bi bi-eye"></i>
                            </button>
                            @if ($errors->password->has('password_nuevo'))
                                <div class="invalid-feedback">{{ $errors->password->first('password_nuevo') }}</div>
                            @endif
                        </div>
                        <ul class="list-unstyled mt-2 small password-rules" id="pass-requisitos">
                            <li id="req-len" class="text-secondary"><i class="bi bi-x-circle me-1"></i>Mínimo 5 caracteres</li>
                            <li id="req-may" class="text-secondary"><i class="bi bi-x-circle me-1"></i>Al menos una mayúscula</li>
                            <li id="req-num" class="text-secondary"><i class="bi bi-x-circle me-1"></i>Al menos un número</li>
                        </ul>
                    </div>

                    {{-- Confirmar nueva contraseña --}}
                    <div class="mb-2">
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btn-cambiar">
                        <i class="bi bi-shield-check me-2"></i>Cambiar contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
