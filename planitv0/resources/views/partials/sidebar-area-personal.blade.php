<div class="card shadow-sm sticky-top" style="top: 90px;">
    <div class="card-header fw-semibold bg-white">
        <i class="bi bi-layout-text-sidebar-reverse me-2 text-primary"></i>Gestionar cuenta
    </div>
    <div class="list-group list-group-flush">
        @php
            $areaPersonalActiva = request()->routeIs('area-personal') || request()->routeIs('area-personal.actualizar');
            $misReservasActiva = request()->routeIs('mis-reservas.*');
            $pasajerosActiva = request()->routeIs('pasajeros-frecuentes.*');
        @endphp
        <a href="{{ url('/area-personal') }}"
            @class(['list-group-item list-group-item-action d-flex align-items-center gap-2', 'active' => $areaPersonalActiva])
            @if ($areaPersonalActiva) aria-current="true" @endif>
            <i class="bi bi-person-lines-fill"></i>
            <span>Datos personales</span>
        </a>
        <a href="{{ route('mis-reservas.index') }}"
            @class(['list-group-item list-group-item-action d-flex align-items-center gap-2', 'active' => $misReservasActiva])
            @if ($misReservasActiva) aria-current="true" @endif>
            <i class="bi bi-journal-text"></i>
            <span>Mis Reservas</span>
        </a>
        <a href="{{ route('pasajeros-frecuentes.index') }}"
            @class(['list-group-item list-group-item-action d-flex align-items-center gap-2', 'active' => $pasajerosActiva])
            @if ($pasajerosActiva) aria-current="true" @endif>
            <i class="bi bi-people"></i>
            <span>Pasajeros frecuentes</span>
        </a>
    </div>
</div>
