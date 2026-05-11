<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
</head>

<body>

    <div class="card">
        <h2 class="text-center mb-2">Restablecer contraseña</h2>
        <p class="text-center text-muted mb-4">Introduce el código de recuperación y tu nueva contraseña.</p>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="resetForm" method="POST" action="{{ route('password.update') }}" novalidate>
            @csrf

            <div class="mb-3">
                @php
                    $emailValue = request()->email;
                    if (!$emailValue) {
                        $emailValue = session('email_recuperacion');
                    }
                @endphp

                @if ($emailValue)
                    <input type="email" name="email" class="form-control"
                        value="{{ $emailValue }}"
                        placeholder="Correo electrónico" readonly style="background-color:#e9ecef;">
                @else
                    <input type="email" name="email" class="form-control"
                        value="{{ old('email') }}"
                        placeholder="Correo electrónico" required>
                    <small class="text-muted">Introduce el correo que usaste para solicitar el código.</small>
                @endif
            </div>

            <div class="mb-3">
                <input type="text" name="token" class="form-control"
                    value="{{ old('token') }}"
                    placeholder="Código de recuperación" required>
            </div>

            <div class="mb-3">
                <input type="password" name="password" class="form-control"
                    placeholder="Nueva contraseña" required>
            </div>

            <div class="mb-3">
                <input type="password" name="password_confirmation" class="form-control"
                    placeholder="Confirmar contraseña" required>
            </div>

            <div class="d-flex justify-content-end mb-2">
                <button type="submit" class="btn btn-primary btn-sm" style="width: 170px;">
                    Cambiar contraseña
                </button>
            </div>

            <p class="mt-3 mb-2 text-center">
                ¿No recibiste el código? <a href="{{ route('password.reset.resend') }}">Reenviar código</a>
            </p>
        </form>

        <div class="mt-2 text-center">
            <a href="{{ route('login') }}">← Volver a iniciar sesión</a>
        </div>
    </div>

    <script src="{{ asset('js/auth-access.js?v=' . time()) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
