<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar cuenta</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
</head>

<body>

    <div class="card">
        <h2 class="text-center mb-2">Verificar cuenta</h2>
        <p class="text-center text-muted mb-4">
            Te hemos enviado un código de verificación a tu correo para confirmar que esta es tu cuenta real de correo electrónico y no la de otra persona.
        </p>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <form id="verifyForm" method="POST" action="{{ route('register.verify.submit') }}" novalidate>
            @csrf

            <div class="mb-3">
                <input type="text" name="codigo" class="form-control"
                    value="{{ old('codigo') }}"
                    placeholder="Introduce el código de verificación" required>
            </div>

            <div class="d-flex justify-content-end mb-2">
                <button type="submit" class="btn btn-primary btn-sm" style="width: 150px;">
                    Verificar cuenta
                </button>
            </div>
        </form>

        <p class="mt-3 mb-2 text-center">
            ¿No recibiste el código? <a href="{{ route('register.verify.resend') }}">Reenviar código</a>
        </p>

        <div class="mt-2 text-center">
            <a href="{{ route('login') }}">← Volver a iniciar sesión</a>
        </div>
    </div>

    <script src="{{ asset('js/auth-access.js?v=' . time()) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
