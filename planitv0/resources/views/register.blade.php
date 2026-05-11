<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link href="{{ asset('css/register.css') }}" rel="stylesheet">
</head>

<body>

    <div class="card">
        <h2 class="text-center mb-4">Crear Cuenta</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="registerForm" method="POST" action="{{ route('register.submit') }}" novalidate>
            @csrf

            <!-- Nombre -->
            <div class="mb-3">
                <div class="input-group position-relative">
                    <input type="text" name="nombre" id="nombre" class="form-control"
                        placeholder="Nombre">
                    <span id="nombreCheck" class="d-none position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);">
                        <i class="bi bi-check2 text-success" style="font-size: 1.2rem;"></i>
                    </span>
                </div>
                <small class="text-danger d-none" id="nombreError">
                    Introduce tu nombre.
                </small>
            </div>

            <!-- Apellidos -->
            <div class="mb-3">
                <div class="input-group position-relative">
                    <input type="text" name="apellidos" id="apellidos" class="form-control"
                        placeholder="Apellidos">
                    <span id="apellidosCheck" class="d-none position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);">
                        <i class="bi bi-check2 text-success" style="font-size: 1.2rem;"></i>
                    </span>
                </div>
                <small class="text-danger d-none" id="apellidosError">
                    Introduce tus apellidos.
                </small>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <div class="input-group position-relative">
                    <input type="email" name="email" id="email" class="form-control"
                        placeholder="Correo electrónico">
                    <span id="emailCheck" class="d-none position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);">
                        <i class="bi bi-check2 text-success" style="font-size: 1.2rem;"></i>
                    </span>
                </div>
                <small class="text-danger d-none" id="emailError">
                    Introduce un correo electrónico válido.
                </small>
            </div>

            <!-- Contraseña -->
            <div class="mb-3">
                <div class="input-group position-relative">
                    <input type="password" name="password" id="password" class="form-control border-end-0"
                        placeholder="Contraseña">

                    <button class="input-group-text bg-white border-start-0 togglePassword" type="button"
                        data-target="password">
                        <i class="bi bi-eye-slash"></i>
                    </button>

                    <span id="passwordCheck" class="d-none position-absolute" style="right: 40px; top: 50%; transform: translateY(-50%);">
                        <i class="bi bi-check2 text-success" style="font-size: 1.2rem;"></i>
                    </span>
                </div>
                <small class="text-danger d-none" id="passwordError">
                    Minimo 5 caracteres, al menos una mayuscula y un numero.
                </small>
            </div>

            <!-- Confirmar Contraseña -->
            <div class="mb-3">
                <div class="input-group position-relative">
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="form-control border-end-0" placeholder="Repetir Contraseña">

                    <button class="input-group-text bg-white border-start-0 togglePassword" type="button"
                        data-target="password_confirmation">
                        <i class="bi bi-eye-slash"></i>
                    </button>

                    <span id="confirmPasswordCheck" class="d-none position-absolute" style="right: 40px; top: 50%; transform: translateY(-50%);">
                        <i class="bi bi-check2 text-success" style="font-size: 1.2rem;"></i>
                    </span>
                </div>
                <small class="text-danger d-none" id="confirmPasswordError">
                    Las contraseñas no coinciden.
                </small>
            </div>

            <button type="submit" class="btn btn-primary">Crear Cuenta</button>
        </form>

        <div class="login-link text-center">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}">Iniciar sesión</a>
        </div>
    </div>

    <script src="{{ asset('js/register.js?v=' . time()) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
