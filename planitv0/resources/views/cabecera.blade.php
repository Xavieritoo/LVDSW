<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Planit: Gestiona tu vuelo</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link href="{{ asset('css/cabecera.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body>

    @php
        $esAdminCabecera = false;
        $esSuperadminCabecera = false;
        if (auth()->check()) {
            $usuarioCabecera = \App\Models\Usuario::query()
                ->with('rol')
                ->find(Auth::id());
            $rolCabecera = mb_strtolower(trim((string) optional($usuarioCabecera?->rol)->nombre), 'UTF-8');
            $esAdminCabecera = in_array($rolCabecera, ['admin', 'superadmin'], true);
            $esSuperadminCabecera = $rolCabecera === 'superadmin';
        }
    @endphp

    <nav class="navbar navbar-expand-lg position-relative">
        <div class="container d-flex justify-content-between align-items-center">

            <!-- Enlaces izquierda -->
            @php
                if (auth()->check()) {
                    $rutaMisViajesCabecera = route('mis-reservas.index');
                } else {
                    $rutaMisViajesCabecera = route('mis-viajes.index');
                }
            @endphp
            <ul class="navbar-nav d-flex flex-row align-items-center gap-3">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('flights.index') }}">Estado de vuelos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ $rutaMisViajesCabecera }}">Mis viajes</a>
                </li>
            </ul>

            <!-- Marca centrada -->
            <a class="navbar-brand navbar-brand-center" href="{{ route('destinos.index') }}">
                <img src="{{ asset('img/planit3.png') }}" alt="Planit" height="80">
            </a>

            <!-- Enlaces derecha -->
            <ul class="navbar-nav d-flex flex-row align-items-center gap-3">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('destinos.index') }}">Planea tu vuelo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" title="Cambiar idioma">
                        <i class="bi bi-translate nav-icon"></i>
                    </a>
                </li>

                @auth
                    {{-- Desplegable usuario (icono + nombre) --}}
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle user-chip" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false" title="{{ Auth::user()->nombre }}">
                            <i class="bi bi-person-circle nav-icon"></i>
                            <span class="user-chip-name">{{ Auth::user()->nombre }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2"
                                    href="{{ route('area-personal') }}">
                                    <i class="bi bi-gear"></i>
                                    Área Personal
                                </a>
                            </li>
                            @if ($esAdminCabecera)
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2"
                                        href="{{ route('admin.vuelos.index') }}">
                                        <i class="bi bi-airplane"></i>
                                        Gestión de vuelos
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2"
                                        href="{{ route('admin.reservas.index') }}">
                                        <i class="bi bi-journal-text"></i>
                                        Gestión de reservas
                                    </a>
                                </li>
                            @endif
                            @if ($esSuperadminCabecera)
                                <li>
                                    <a class="dropdown-item d-flex align-items-center gap-2"
                                        href="{{ route('superadmin.usuarios.index') }}">
                                        <i class="bi bi-people"></i>
                                        Gestión de usuarios
                                    </a>
                                </li>
                            @endif
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2">
                                        <i class="bi bi-box-arrow-right"></i>
                                        Cerrar sesión
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @else
                    <!-- Botón Iniciar sesión -->
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="btn btn-login btn-sm">Iniciar sesión</a>
                    </li>
                @endauth
            </ul>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuToggler">
                <span class="navbar-toggler-icon"></span>
            </button>

        </div>
    </nav>

    <div class="container mt-4">
        @yield('contenido')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>
