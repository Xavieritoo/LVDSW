let idaSeleccionada = false;
let idaId        = '';
let idaCodigo    = '';
let idaRuta      = '';
let idaPlan      = '';
let idaPrecio    = 0;
let idaHoraSalida = '';

let vueltaSeleccionada = false;
let vueltaId      = '';
let vueltaCodigo  = '';
let vueltaRuta    = '';
let vueltaPlan    = '';
let vueltaPrecio  = 0;
let vueltaHoraSalida = '';

const seccionResumen = document.getElementById('seccionResumen');
const tipoViaje      = seccionResumen.getAttribute('data-tipo-viaje');
const urlPasajeros   = seccionResumen.getAttribute('data-url-pasajeros');
const numAdultos     = Number(seccionResumen.getAttribute('data-num-adultos'));
const numMenores     = Number(seccionResumen.getAttribute('data-num-menores'));
const numInfantes    = Number(seccionResumen.getAttribute('data-num-infantes'));
const origenBusqueda = seccionResumen.getAttribute('data-origen') || '';
const destinoBusqueda = seccionResumen.getAttribute('data-destino') || '';
const fechaIdaBusqueda = seccionResumen.getAttribute('data-fecha-ida') || '';
const fechaVueltaBusqueda = seccionResumen.getAttribute('data-fecha-vuelta') || '';
const zonaBusqueda = seccionResumen.getAttribute('data-zona') || 'all';
const pasajerosFacturables = numAdultos + numMenores;

function mostrarSoloTarjetaSeleccionada(tramo, vueloIdSeleccionado) {
    const selectorColumna = tramo === 'ida' ? '#columnaResultadosIda' : '#columnaResultadosVuelta';
    const columna = document.querySelector(selectorColumna);
    if (!columna) return;

    columna.querySelectorAll('.result-card').forEach((card) => {
        const accionTramo = card.querySelector('.plan-action[data-tramo="' + tramo + '"]');
        const idCard = accionTramo ? accionTramo.getAttribute('data-id-vuelo') : null;
        card.classList.toggle('d-none', !idCard || idCard !== String(vueloIdSeleccionado));
    });
}

function restaurarListadoTramo(tramo) {
    const selectorColumna = tramo === 'ida' ? '#columnaResultadosIda' : '#columnaResultadosVuelta';
    const columna = document.querySelector(selectorColumna);
    if (!columna) return;

    columna.querySelectorAll('.result-card').forEach((card) => {
        card.classList.remove('d-none');
    });
}

function mostrarAviso(mensaje) {
    if (window.PlanitNotificaciones && typeof window.PlanitNotificaciones.mostrar === 'function') {
        window.PlanitNotificaciones.mostrar(mensaje, 'warning', 3000);
        return;
    }
    window.alert(mensaje);
}

function limpiarVueltaSeleccion(restaurarListado = true) {
    vueltaSeleccionada = false;
    document.getElementById('vueltaVuelo').textContent = 'No has seleccionado vuelo de vuelta.';
    document.getElementById('vueltaPlan').textContent   = 'Tarifa: -';
    document.getElementById('vueltaPrecio').textContent  = 'Precio vuelta: -';
    vueltaId = '';
    vueltaCodigo = '';
    vueltaRuta = '';
    vueltaPlan = '';
    vueltaPrecio = 0;
    vueltaHoraSalida = '';

    if (restaurarListado) {
        restaurarListadoTramo('vuelta');
    }
}

// Ocultar la columna de vuelta si el viaje es solo ida
if (tipoViaje !== 'ida_vuelta') {
    document.getElementById('columnaVuelta').classList.add('d-none');
}

document.addEventListener('click', function(e) {
    const boton = e.target.closest('.plan-action');
    if (!boton) return;

    const tramo = boton.getAttribute('data-tramo');

    if (tramo === 'ida') {
        // Guardar los datos del vuelo de ida
        idaId     = boton.getAttribute('data-id-vuelo');
        idaCodigo = boton.getAttribute('data-codigo-vuelo');
        idaRuta   = boton.getAttribute('data-ruta');
        idaHoraSalida = boton.getAttribute('data-hora-salida') || '';
        idaPlan   = boton.getAttribute('data-plan');
        idaPrecio = parseFloat(boton.getAttribute('data-precio'));
        idaSeleccionada = true;

        // Actualizar el resumen de ida
        document.getElementById('idaVuelo').textContent = idaCodigo + ' | ' + idaRuta;
        document.getElementById('idaPlan').textContent   = 'Tarifa: ' + idaPlan;
        document.getElementById('idaPrecio').textContent  = 'Precio ida: ' + formatearEuros(idaPrecio * pasajerosFacturables);
        mostrarSoloTarjetaSeleccionada('ida', idaId);
    }

    if (tramo === 'vuelta') {
        // Guardar los datos del vuelo de vuelta
        vueltaId     = boton.getAttribute('data-id-vuelo');
        vueltaCodigo = boton.getAttribute('data-codigo-vuelo');
        vueltaRuta   = boton.getAttribute('data-ruta');
        vueltaHoraSalida = boton.getAttribute('data-hora-salida') || '';
        vueltaPlan   = boton.getAttribute('data-plan');
        vueltaPrecio = parseFloat(boton.getAttribute('data-precio'));
        vueltaSeleccionada = true;

        // Actualizar el resumen de vuelta
        document.getElementById('vueltaVuelo').textContent = vueltaCodigo + ' | ' + vueltaRuta;
        document.getElementById('vueltaPlan').textContent   = 'Tarifa: ' + vueltaPlan;
        document.getElementById('vueltaPrecio').textContent  = 'Precio vuelta: ' + formatearEuros(vueltaPrecio * pasajerosFacturables);
        mostrarSoloTarjetaSeleccionada('vuelta', vueltaId);
    }

    // Mostrar la sección de resumen, actualizar total y botón
    seccionResumen.classList.remove('d-none');
    actualizarPrecioTotal();
    actualizarBotonContinuar();
    seccionResumen.scrollIntoView({ behavior: 'smooth', block: 'start' });
});

document.getElementById('enlaceCambiarIda').addEventListener('click', function(e) {
    e.preventDefault();

    idaSeleccionada = false;
    document.getElementById('idaVuelo').textContent = 'No has seleccionado vuelo de ida.';
    document.getElementById('idaPlan').textContent   = 'Tarifa: -';
    document.getElementById('idaPrecio').textContent  = 'Precio ida: -';
    idaId = '';
    idaCodigo = '';
    idaRuta = '';
    idaPlan = '';
    idaPrecio = 0;
    idaHoraSalida = '';
    restaurarListadoTramo('ida');

    actualizarPrecioTotal();
    actualizarBotonContinuar();
    document.getElementById('columnaResultadosIda').scrollIntoView({ behavior: 'smooth', block: 'start' });
});

const enlaceCambiarVuelta = document.getElementById('enlaceCambiarVuelta');
if (enlaceCambiarVuelta) {
    enlaceCambiarVuelta.addEventListener('click', function(e) {
        e.preventDefault();

        limpiarVueltaSeleccion(true);

        actualizarPrecioTotal();
        actualizarBotonContinuar();
        const columnaVueltaResultados = document.getElementById('columnaResultadosVuelta');
        if (columnaVueltaResultados) {
            columnaVueltaResultados.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
}

document.getElementById('btnContinuar').addEventListener('click', function() {
    const url = urlPasajeros
        + '?adultos='          + numAdultos
        + '&menores='          + numMenores
        + '&infantes='         + numInfantes
        + '&tipo_viaje='       + tipoViaje
        + '&origen='           + encodeURIComponent(origenBusqueda)
        + '&destino='          + encodeURIComponent(destinoBusqueda)
        + '&fecha_ida='        + encodeURIComponent(fechaIdaBusqueda)
        + '&fecha_vuelta='     + encodeURIComponent(fechaVueltaBusqueda)
        + '&zona='             + encodeURIComponent(zonaBusqueda)
        + '&vuelo_ida_id='     + idaId
        + '&plan_ida='         + encodeURIComponent(idaPlan)
        + '&precio_ida='       + idaPrecio
        + '&vuelo_vuelta_id='  + vueltaId
        + '&plan_vuelta='      + encodeURIComponent(vueltaPlan)
        + '&precio_vuelta='    + vueltaPrecio;

    window.location.href = url;
});


function actualizarPrecioTotal() {
    let total = 0;

    if (idaSeleccionada) {
        total += idaPrecio * pasajerosFacturables;
    }
    if (vueltaSeleccionada && tipoViaje === 'ida_vuelta') {
        total += vueltaPrecio * pasajerosFacturables;
    }

    document.getElementById('precioTotal').textContent = total > 0 ? formatearEuros(total) : '-';
}

function actualizarBotonContinuar() {
    const boton = document.getElementById('btnContinuar');

    if (tipoViaje === 'ida_vuelta') {
        boton.disabled = !(idaSeleccionada && vueltaSeleccionada);
    } else {
        boton.disabled = !idaSeleccionada;
    }
}

function formatearEuros(precio) {
    return precio.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' EUR';
}
