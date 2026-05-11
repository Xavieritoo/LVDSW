<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
</head>

<body>

    <div class="card">
        <h2 class="text-center mb-4">Iniciar sesión</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="loginForm" method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <!-- Email -->
            <div class="mb-3">
                <div class="input-group position-relative">
                    <input type="email" name="email" id="email" class="form-control"
                        placeholder="Correo electrónico" required>
                    <span id="emailCheck" class="d-none position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%);">
                        <i class="bi bi-check2 text-success" style="font-size: 1.2rem;"></i>
                    </span>
                </div>
                <small class="text-danger d-none" id="emailError">Introduce un correo electrónico válido.</small>
            </div>

            <!-- Contraseña -->
            <div class="mb-3">
                <div class="input-group position-relative">
                    <input type="password" name="password" id="password" class="form-control border-end-0"
                        placeholder="Contraseña" required>

                    <button class="input-group-text bg-white border-start-0" type="button" id="togglePassword">
                        <i class="bi bi-eye-slash"></i>
                    </button>

                    <span id="passwordCheck" class="d-none position-absolute" style="right: 40px; top: 50%; transform: translateY(-50%);">
                        <i class="bi bi-check2 text-success" style="font-size: 1.2rem;"></i>
                    </span>
                </div>
                <small class="text-danger d-none" id="passwordError">Se necesita una contraseña para iniciar sesión.</small>
            </div>

            {{-- Fila con enlace y botón --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="{{ route('password.request') }}" class="small text-primary">
                    ¿Olvidaste tu contraseña?
                </a>
                <button type="submit" class="btn btn-primary btn-sm" style="width: 120px;">
                    Iniciar sesión
                </button>
            </div>
        </form>

        <div class="register-link text-center">
            ¿Aún no tienes cuenta?
            <a href="{{ route('register') }}">Crear Cuenta</a>
        </div>
        <div class="mt-3 text-center">
            <a href="{{ route('principal') }}">← Volver a la página principal</a>
        </div>
    </div>

    <script src="{{ asset('js/login.js?v=' . time()) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
