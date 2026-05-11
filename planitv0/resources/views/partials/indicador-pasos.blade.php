{{-- Stepper del proceso de compra. Variable $pasoActual: 1=Vuelos, 2=Pasajeros, 3=Equipaje, 4=Resumen, 5=Completado --}}
@php
    $pasos = [
        1 => ['label' => 'Vuelos',    'icon' => 'bi-airplane'],
        2 => ['label' => 'Pasajeros', 'icon' => 'bi-people'],
        3 => ['label' => 'Equipaje',  'icon' => 'bi-luggage'],
        4 => ['label' => 'Resumen',   'icon' => 'bi-receipt'],
        5 => ['label' => 'Listo',     'icon' => 'bi-check-circle'],
    ];
@endphp

<nav class="checkout-stepper" aria-label="Progreso de compra">
    @foreach ($pasos as $num => $paso)
        @php
            $clase = '';
            if ($num < $pasoActual) {
                $clase = 'completed';
            }
            if ($num === $pasoActual) {
                $clase = 'active';
            }
        @endphp

        @if ($num > 1)
            <div class="stepper-divider @if($num <= $pasoActual) completed @endif"></div>
        @endif

        <div class="stepper-step {{ $clase }}">
            <span class="stepper-number">
                @if ($num < $pasoActual)
                    <i class="bi bi-check-lg"></i>
                @else
                    {{ $num }}
                @endif
            </span>
            <span class="stepper-label">{{ $paso['label'] }}</span>
        </div>
    @endforeach
</nav>
