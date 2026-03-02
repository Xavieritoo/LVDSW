<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            padding: 2rem;
            border-radius: 10px;
            border: 2px solid #0d6efd;
            background: #fff;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            width: 100%;
        }

        .register-link {
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        .register-link a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="card">
        <h2 class="text-center mb-4">Iniciar sesión</h2>

        {{-- mostrar errores del servidor (credenciales, validación) --}}
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="loginForm" method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <!-- Email -->
            <div class="mb-3">
                <div class="input-group">
                    <input type="email" name="email" id="email" class="form-control border-end-0"
                        placeholder="Correo electrónico" required>
                    <span class="input-group-text bg-white border-start-0 d-none" id="emailCheck">
                        <i class="bi bi-check-lg text-success"></i>
                    </span>
                </div>
                <small class="text-danger d-none" id="emailError">Introduce un email válido.</small>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control border-end-0"
                        placeholder="Contraseña" required>

                    <button class="input-group-text bg-white border-start-0" type="button" id="togglePassword">
                        <i class="bi bi-eye-slash"></i>
                    </button>

                    <span class="input-group-text bg-white border-start-0 d-none" id="passwordCheck">
                        <i class="bi bi-check-lg text-success"></i>
                    </span>
                </div>
                <small class="text-danger d-none" id="passwordError">La contraseña debe tener al menos 4
                    caracteres.</small>
            </div>

            <!-- Row con enlace y botón -->
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

    <script src="{{ asset('js/login.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
