<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta</title>

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
            max-width: 500px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            width: 100%;
        }

        .login-link {
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        .login-link a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="card">
        <h2 class="text-center mb-4">Crear Cuenta</h2>

        {{-- errores de servidor (validación/rol) --}}
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="registerForm" method="POST" action="{{ route('register.submit') }}">
            @csrf

            <!-- Nombre -->
            <div class="mb-3">
                <div class="input-group">
                    <input type="text" name="nombre" id="nombre" class="form-control border-end-0"
                        placeholder="Nombre" required>

                    <span class="input-group-text bg-white border-start-0 d-none" id="nombreCheck">
                        <i class="bi bi-check-lg text-success"></i>
                    </span>
                </div>
                <small class="text-danger d-none" id="nombreError">
                    Introduce tu nombre.
                </small>
            </div>

            <!-- Apellidos -->
            <div class="mb-3">
                <div class="input-group">
                    <input type="text" name="apellidos" id="apellidos" class="form-control border-end-0"
                        placeholder="Apellidos" required>

                    <span class="input-group-text bg-white border-start-0 d-none" id="apellidosCheck">
                        <i class="bi bi-check-lg text-success"></i>
                    </span>
                </div>
                <small class="text-danger d-none" id="apellidosError">
                    Introduce tus apellidos.
                </small>
            </div>

            <!-- Email -->
            <div class="mb-3">
                <div class="input-group">
                    <input type="email" name="email" id="email" class="form-control border-end-0"
                        placeholder="Correo electrónico" required>

                    <span class="input-group-text bg-white border-start-0 d-none" id="emailCheck">
                        <i class="bi bi-check-lg text-success"></i>
                    </span>
                </div>
                <small class="text-danger d-none" id="emailError">
                    Introduce un email válido.
                </small>
            </div>

            <!-- Contraseña -->
            <div class="mb-3">
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control border-end-0"
                        placeholder="Contraseña" required>

                    <button class="input-group-text bg-white border-start-0 togglePassword" type="button"
                        data-target="password">
                        <i class="bi bi-eye-slash"></i>
                    </button>

                    <span class="input-group-text bg-white border-start-0 d-none" id="passwordCheck">
                        <i class="bi bi-check-lg text-success"></i>
                    </span>
                </div>
                <small class="text-danger d-none" id="passwordError">
                    La contraseña debe tener al menos 4 caracteres.
                </small>
            </div>

            <!-- Confirmar Contraseña -->
            <div class="mb-3">
                <div class="input-group">
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="form-control border-end-0" placeholder="Repetir Contraseña" required>

                    <button class="input-group-text bg-white border-start-0 togglePassword" type="button"
                        data-target="password_confirmation">
                        <i class="bi bi-eye-slash"></i>
                    </button>

                    <span class="input-group-text bg-white border-start-0 d-none" id="confirmPasswordCheck">
                        <i class="bi bi-check-lg text-success"></i>
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

    <script src="{{ asset('js/register.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
