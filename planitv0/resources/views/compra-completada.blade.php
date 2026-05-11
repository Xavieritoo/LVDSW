@extends('cabecera')

@push('styles')
    <link href="{{ asset('css/checkout.css') }}" rel="stylesheet">
    <link href="{{ asset('css/resumen.css') }}" rel="stylesheet">
@endpush

@section('contenido')

    @include('partials.indicador-pasos', ['pasoActual' => 5])

    <section class="summary-card text-center">
        <div class="success-icon">
            <i class="bi bi-check-lg"></i>
        </div>
        <h4 class="fw-bold mb-1" style="color: #1e3f90;">Compra completada</h4>
        <p class="text-muted mb-4">Tu reserva se ha generado correctamente.</p>

        @php
            if (isset($compraCompletada['localizadores'])) {
                $localizadores = $compraCompletada['localizadores'];
            } else {
                $localizadores = [];
            }
            $tieneVuelta   = isset($localizadores['vuelta']);
        @endphp

        @if ($tieneVuelta)
            <div class="row g-3 mb-3 text-start">
                <div class="col-md-6">
                    <div class="localizador-box">
                        <div class="loc-label">Vuelo de ida</div>
                        <div class="loc-code">{{ $localizadores['ida'] }}</div>
                        <button type="button" class="btn btn-copiar-loc btn-copiar" data-localizador="{{ $localizadores['ida'] }}">
                            <i class="bi bi-clipboard me-1"></i>Copiar
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="localizador-box">
                        <div class="loc-label">Vuelo de vuelta</div>
                        <div class="loc-code">{{ $localizadores['vuelta'] }}</div>
                        <button type="button" class="btn btn-copiar-loc btn-copiar" data-localizador="{{ $localizadores['vuelta'] }}">
                            <i class="bi bi-clipboard me-1"></i>Copiar
                        </button>
                    </div>
                </div>
            </div>
            <div class="alert alert-warning text-start">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Es muy importante que guardes ambos localizadores. Los necesitarás para gestionar cada reserva de forma independiente.
            </div>
        @else
            <div class="localizador-box mx-auto" style="max-width: 380px;">
                <div class="loc-label">Tu localizador</div>
                <div class="loc-code">@if(isset($localizadores['ida'])){{ $localizadores['ida'] }}@endif</div>
                <button type="button" class="btn btn-copiar-loc btn-copiar" data-localizador="@if(isset($localizadores['ida'])){{ $localizadores['ida'] }}@endif">
                    <i class="bi bi-clipboard me-1"></i>Copiar localizador
                </button>
            </div>
            <div class="alert alert-warning text-start">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Es muy importante que guardes o copies este localizador. Lo necesitarás para gestionar tu reserva.
            </div>
        @endif

        @if (!empty($compraCompletada['enlazada_a_cuenta']))
            <div class="alert alert-success text-start">
                <i class="bi bi-link-45deg me-1"></i>
                @if($tieneVuelta) Las reservas han quedado enlazadas directamente a tu cuenta y ya puedes gestionarlas desde Mis viajes. @else La reserva ha quedado enlazada directamente a tu cuenta y ya puedes gestionarla desde Mis viajes. @endif
            </div>
        @else
            <div class="alert alert-info text-start">
                <i class="bi bi-info-circle me-1"></i>
                Podrás gestionar @if($tieneVuelta) estas reservas @else esta reserva @endif desde Mis viajes usando tu correo de contacto y @if($tieneVuelta) los localizadores correspondientes @else este localizador @endif.
            </div>
        @endif

        <div class="d-flex flex-wrap gap-2 justify-content-center mt-3">
            <a href="{{ $compraCompletada['gestion_url'] }}" class="btn btn-planit px-4 py-2">
                <i class="bi bi-airplane me-1"></i>Ir a Mis viajes
            </a>
            <a href="{{ route('principal') }}" class="btn btn-outline-primary px-4 py-2">
                <i class="bi bi-house me-1"></i>Ir al inicio
            </a>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.btn-copiar').forEach(function (boton) {
                boton.addEventListener('click', async function () {
                    var localizador = boton.dataset.localizador || '';
                    if (!localizador) return;

                    try {
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            await navigator.clipboard.writeText(localizador);
                        } else {
                            var inputTemporal = document.createElement('input');
                            inputTemporal.value = localizador;
                            document.body.appendChild(inputTemporal);
                            inputTemporal.select();
                            document.execCommand('copy');
                            document.body.removeChild(inputTemporal);
                        }

                        var textoOriginal = boton.innerHTML;
                        boton.classList.add('copiado');
                        boton.innerHTML = '<i class="bi bi-check-lg me-1"></i>Copiado';
                        setTimeout(function () {
                            boton.classList.remove('copiado');
                            boton.innerHTML = textoOriginal;
                        }, 1500);
                    } catch (error) {
                        boton.textContent = 'No se pudo copiar';
                    }
                });
            });
        });
    </script>
@endsection
