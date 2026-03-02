<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planit: Gestiona tu vuelo</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body {
            background: #f5f5f5;
        }

        .navbar {
            background: #0d6efd;
        }

        .navbar-brand,
        .nav-link {
            color: #fff !important;
            font-weight: 500;
        }

        .nav-link:hover {
            text-decoration: underline;
        }

        .btn-login,
        .btn-logout {
            background: #fff;
            color: #0d6efd;
            font-weight: 600;
            border-radius: 6px;
            border: none;
            padding: 6px 12px;
        }

        .btn-login:hover,
        .btn-logout:hover {
            background: #e9ecef;
        }

        .user-name {
            color: #fff;
            font-weight: 600;
        }

        /* Marca centrada */
        .navbar-brand-center {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }

        /* Iconos más grandes */
        .nav-icon {
            font-size: 1.2rem;
        }

        /* Dropdown icono persona */
        .dropdown-toggle::after {
            display: none;
            /* Quitar flecha por defecto */
        }

        /* Fondo dropdown */
        .dropdown-menu {
            min-width: 150px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg position-relative">
        <div class="container d-flex justify-content-between align-items-center">

            <!-- Enlaces izquierda -->
            <ul class="navbar-nav d-flex flex-row align-items-center gap-3">
                <li class="nav-item">
                    <a class="nav-link" href="#">Reservar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Mis viajes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Estado de vuelos</a>
                </li>
            </ul>

            <!-- Marca centrada -->
            <a class="navbar-brand navbar-brand-center" href="{{ route('principal') }}">
                P l a n i t
            </a>

            <!-- Enlaces derecha -->
            <ul class="navbar-nav d-flex flex-row align-items-center gap-3">
                <li class="nav-item">
                    <a class="nav-link" href="#">Ofertas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Sobre nosotros</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" title="Cambiar idioma">
                        <i class="bi bi-translate nav-icon"></i>
                    </a>
                </li>

                {{-- Usuario/login: usamos nuestra sesión propia en lugar de @auth --}}
                @php
                    $usuario = null;
                    if (Session::has('usuario_id')) {
                        $usuario = \Illuminate\Support\Facades\DB::table('usuarios')
                            ->where('id', Session::get('usuario_id'))
                            ->first();
                    }
                @endphp

                @if($usuario)
                    <!-- Dropdown con icono persona -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false" title="{{ $usuario->nombre }}">
                            <i class="bi bi-person nav-icon"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li>
                                <!-- Cambiado para apuntar a la página de configuración -->
                                <a class="dropdown-item d-flex align-items-center gap-2"
                                    href="{{ route('configuracion') }}">
                                    <i class="bi bi-gear"></i>
                                    Configurar cuenta
                                </a>
                            </li>
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
                @endif
            </ul>

            <!-- Botón responsive -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuToggler">
                <span class="navbar-toggler-icon"></span>
            </button>

        </div>
    </nav>

    <div class="container mt-4">
        @yield('contenido')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
