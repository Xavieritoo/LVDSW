@extends('cabecera')

@section('contenido')
<div class="row justify-content-center">
    <div class="col-12 col-xl-11">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Gestión de usuarios</h2>
                <p class="text-muted mb-0">Módulo de superadmin para gestionar usuarios y admins.</p>
            </div>
            <a href="{{ route('superadmin.usuarios.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>Crear usuario
            </a>
        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <form method="GET" action="{{ route('superadmin.usuarios.index') }}" class="row g-3 align-items-end">
                    <div class="col-12 col-md-10">
                        <label for="q" class="form-label">Buscar por nombre, apellidos o email</label>
                        <input type="search" id="q" name="q" class="form-control"
                            value="@if(isset($busqueda)){{ $busqueda }}@endif" placeholder="Ej: Juan o juan@email.com">
                    </div>
                    <div class="col-12 col-md-2 d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="{{ route('superadmin.usuarios.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        @if (session('exito'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('exito') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($usuarios->isEmpty())
            <div class="alert alert-info">No hay usuarios para mostrar.</div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Activo</th>
                        <th>Verificado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->id }}</td>
                            <td>{{ $usuario->nombre }} {{ $usuario->apellidos }}</td>
                            <td>{{ $usuario->email }}</td>
                            <td>@if(!is_null(optional($usuario->rol)->nombre)){{ optional($usuario->rol)->nombre }}@else - @endif</td>
                            <td>
                                @if ($usuario->esta_activo)
                                    <span class="badge text-bg-success">Sí</span>
                                @else
                                    <span class="badge text-bg-secondary">No</span>
                                @endif
                            </td>
                            <td>
                                @if ($usuario->esta_verificado)
                                    <span class="badge text-bg-success">Sí</span>
                                @else
                                    <span class="badge text-bg-secondary">No</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('superadmin.usuarios.edit', $usuario) }}" class="btn btn-sm btn-outline-primary">Editar</a>
                                    <form method="POST" action="{{ route('superadmin.usuarios.destroy', $usuario) }}"
                                        onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $usuarios->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
