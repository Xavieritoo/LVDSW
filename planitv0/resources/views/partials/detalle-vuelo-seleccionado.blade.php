@php
    $detalleFiltrado = [];
    if (!empty($detalleVuelosSeleccionados['ida'])) {
        $detalleFiltrado['ida'] = $detalleVuelosSeleccionados['ida'];
    }
    if (!empty($detalleVuelosSeleccionados['vuelta'])) {
        $detalleFiltrado['vuelta'] = $detalleVuelosSeleccionados['vuelta'];
    }
@endphp

@if (!empty($detalleFiltrado))
    <section class="checkout-flight-card">
        <h5><i class="bi bi-airplane me-2"></i>Detalles del vuelo seleccionado</h5>
        <div class="row g-3">
            @foreach ($detalleFiltrado as $trayecto => $detalleVuelo)
                <div class="col-lg-6">
                    <div class="checkout-flight-leg">
                        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-2">
                            <span class="leg-badge">{{ ucfirst($trayecto) }}</span>
                            <span class="leg-zone">{{ $detalleVuelo['zona'] }}</span>
                        </div>
                        <div class="leg-route">{{ $detalleVuelo['ruta'] }}</div>
                        <div class="leg-detail"><i class="bi bi-box-arrow-up-right"></i>Salida: {{ $detalleVuelo['salida'] }}</div>
                        <div class="leg-detail"><i class="bi bi-box-arrow-in-down-left"></i>Llegada: {{ $detalleVuelo['llegada'] }}</div>
                        <div class="leg-detail"><i class="bi bi-tag"></i>Plan: {{ $detalleVuelo['plan'] }}</div>
                        <div class="leg-detail"><i class="bi bi-currency-euro"></i>Precio base: {{ $detalleVuelo['precio'] }}</div>

                        @php
                            if (isset($detalleVuelo['plan'])) {
                                $planNorm = strtoupper(trim($detalleVuelo['plan']));
                            } else {
                                $planNorm = '';
                            }
                        @endphp
                        <hr class="my-2">
                        <div class="leg-baggage-title"><i class="bi bi-luggage me-1"></i>Equipaje incluido en {{ $detalleVuelo['plan'] }}:</div>
                        <ul class="leg-baggage-list">
                            <li>Accesorio personal: 1 pieza (40x30x15 cm)</li>
                            <li>Equipaje de mano: 1 pieza hasta 10 kg (56x40x25 cm)</li>
                            @if (str_contains($planNorm, 'COMFORT'))
                                <li>Equipaje facturado: 1 pieza hasta 23 kg incluida</li>
                            @endif
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endif
