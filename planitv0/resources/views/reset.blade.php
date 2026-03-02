@extends('cabecera')

@section('contenido')
    <div class="text-center py-5">
        <h2 class="mb-4">Restablecer contraseña</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ request()->token }}">
            <input type="hidden" name="email" value="{{ request()->email }}">

            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Nueva contraseña" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password_confirmation" class="form-control" placeholder="Confirmar contraseña"
                    required>
            </div>
            <button type="submit" class="btn btn-primary">Cambiar contraseña</button>
        </form>
    </div>
@endsection
