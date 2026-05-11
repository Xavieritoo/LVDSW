@extends('cabecera')

@push('styles')
    <link href="{{ asset('css/estado-vuelos.css') }}" rel="stylesheet">
@endpush

@section('contenido')
    @php
        if (!empty($vuelo->numero_vuelo)) {
            $codigoCabecera = $vuelo->numero_vuelo;
        } elseif (!empty($vuelo->codigo)) {
            $codigoCabecera = $vuelo->codigo;
        } else {
            $codigoCabecera = 'Sin código';
        }

        if (!empty($vuelo->avion_matricula)) {
            $matriculaVisual = $vuelo->avion_matricula;
        } else {
            $matriculaVisual = 'No asignada';
        }

        if (isset($vuelo->tripulacion_cantidad)) {
            $tripulacionVisual = $vuelo->tripulacion_cantidad;
        } else {
            $tripulacionVisual = 'Sin datos';
        }

        if (!empty($vuelo->hora_salida_programada)) {
            $salidaProgramada = \Carbon\Carbon::parse($vuelo->hora_salida_programada)->format('H:i');
        } else {
            $salidaProgramada = \Carbon\Carbon::parse($vuelo->fecha_salida)->format('H:i');
        }

        if (!empty($vuelo->hora_llegada_programada)) {
            $llegadaProgramada = \Carbon\Carbon::parse($vuelo->hora_llegada_programada)->format('H:i');
        } elseif (!empty($vuelo->fecha_llegada)) {
            $llegadaProgramada = \Carbon\Carbon::parse($vuelo->fecha_llegada)->format('H:i');
        } else {
            $llegadaProgramada = 'Sin datos';
        }

        if (isset($vuelo->pasajeros_confirmados)) {
            $pasajerosConfirmadosVisual = $vuelo->pasajeros_confirmados;
        } else {
            $pasajerosConfirmadosVisual = 'Sin datos';
        }

        if (isset($vuelo->terminal_salida)) {
            $terminalSalidaVisual = $vuelo->terminal_salida;
        } else {
            $terminalSalidaVisual = 'Sin asignar';
        }

        if (isset($vuelo->terminal_llegada)) {
            $terminalLlegadaVisual = $vuelo->terminal_llegada;
        } else {
            $terminalLlegadaVisual = 'Sin asignar';
        }
    @endphp

    <div class="flight-detail-shell">

    <div class="detail-card">
        <div class="detail-header">
            <div>
                <p class="text-uppercase text-muted mb-2">Detalle de vuelo</p>
                <h1 class="header-title">{{ $codigoCabecera }}</h1>
                <p class="text-muted">{{ $vuelo->ruta->aeropuertoOrigen->codigo_iata }} - {{ $vuelo->ruta->aeropuertoDestino->codigo_iata }} | {{ \Carbon\Carbon::parse($vuelo->fecha_salida)->format('d/m/Y') }}</p>
            </div>
            <div class="badge-status badge-{{ $vuelo->estado->nombre }}">
                {{ ucfirst(str_replace('_', ' ', $vuelo->estado->nombre)) }}
            </div>
        </div>

        <div class="flight-data">
            <div class="flight-section">
                <h3>Aerolínea y avión</h3>
                <p><strong>Aerolínea:</strong> {{ $vuelo->aerolinea->nombre }}</p>
                <p><strong>Matricula:</strong> {{ $matriculaVisual }}</p>
                <p><strong>Tripulación:</strong> {{ $tripulacionVisual }}</p>
            </div>

            <div class="flight-section">
                <h3>Información temporal</h3>
                <p><strong>Salida programada:</strong> {{ $salidaProgramada }}</p>
                <p><strong>Llegada programada:</strong> {{ $llegadaProgramada }}</p>
                <p><strong>Hora real salida:</strong> @if($vuelo->hora_salida_real){{ \Carbon\Carbon::parse($vuelo->hora_salida_real)->format('H:i') }}@else Pendiente @endif</p>
                <p><strong>Hora real llegada:</strong> @if($vuelo->hora_llegada_real){{ \Carbon\Carbon::parse($vuelo->hora_llegada_real)->format('H:i') }}@else Pendiente @endif</p>
            </div>

            <div class="flight-section">
                <h3>Rutas</h3>
                <p><strong>Origen:</strong> {{ $vuelo->ruta->aeropuertoOrigen->ciudad }} ({{ $vuelo->ruta->aeropuertoOrigen->codigo_iata }})</p>
                <p><strong>Destino:</strong> {{ $vuelo->ruta->aeropuertoDestino->ciudad }} ({{ $vuelo->ruta->aeropuertoDestino->codigo_iata }})</p>
                <p><strong>Pasajeros confirmados:</strong> {{ $pasajerosConfirmadosVisual }}</p>
            </div>

            <div class="flight-section">
                <h3>Terminales</h3>
                <p><strong>Terminal salida:</strong> {{ $terminalSalidaVisual }}</p>
                <p><strong>Terminal llegada:</strong> {{ $terminalLlegadaVisual }}</p>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('flights.index') }}" class="back-link">← Volver al estado de vuelos</a>
        </div>
    </div>
    </div>
@endsection
