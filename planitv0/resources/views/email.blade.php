@extends('cabecera')

@section('contenido')
<div class="text-center py-5">
    <h2 class="mb-4">¿Has olvidado tu contraseña?</h2>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <input type="email" name="email" class="form-control" placeholder="Tu correo electrónico" required>
        </div>
        <button type="submit" class="btn btn-primary">Enviar enlace de recuperación</button>
    </form>
</div>
@endsection