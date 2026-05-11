<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
</head>

<body>

    <div class="card">
        <h2 class="text-center mb-2">¿Has olvidado tu contraseña?</h2>
        <p class="text-center text-muted mb-4">Te enviaremos un código de recuperación a tu correo.</p>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="forgotForm" method="POST" action="{{ route('password.email') }}" novalidate>
            @csrf

            <div class="mb-3">
                <input type="email" name="email" class="form-control"
                    value="{{ old('email') }}"
                    placeholder="Correo electrónico" required>
            </div>

            <div class="d-flex justify-content-end mb-2">
                <button type="submit" class="btn btn-primary btn-sm" style="width: 210px;">
                    Enviar código de recuperación
                </button>
            </div>
        </form>

        <div class="mt-2 text-center">
            <a href="{{ route('login') }}">← Volver a iniciar sesión</a>
        </div>
    </div>

    <script src="{{ asset('js/auth-access.js?v=' . time()) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
