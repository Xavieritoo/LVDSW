// Variables apiOrigen, apiDestino, origenInicial y destinoInicial se declaran
// en un bloque <script> inline en la vista (generadas por PHP/Blade).

let origenSeleccionado = (typeof origenInicial !== 'undefined') ? origenInicial : null;
let destinosDisponibles = [];

const pasajeros = {
    adultos: Math.max(0, parseInt(document.getElementById('input-adultos').value || '1', 10)),
    ninos: Math.max(0, parseInt(document.getElementById('input-ninos').value || '0', 10)),
    bebes: Math.max(0, parseInt(document.getElementById('input-bebes').value || '0', 10)),
};

const notificacionesDestinos = document.getElementById('notificaciones-destinos');

function mostrarNotificacion(mensaje, tipo = 'info') {
    if (window.PlanitNotificaciones && typeof window.PlanitNotificaciones.mostrar === 'function') {
        window.PlanitNotificaciones.mostrar(mensaje, tipo, 2500);
        return;
    }

    if (!notificacionesDestinos) {
        return;
    }

    const item = document.createElement('div');
    item.className = `notificacion-item ${tipo}`.trim();
    item.textContent = mensaje;
    notificacionesDestinos.appendChild(item);

    requestAnimationFrame(() => {
        item.classList.add('visible');
    });

    setTimeout(() => {
        item.classList.remove('visible');
        setTimeout(() => {
            item.remove();
        }, 220);
    }, 2500);
}

function guardarNotificacionPendiente(mensaje, tipo = 'info', duracion = 2500) {
    if (window.PlanitNotificaciones && typeof window.PlanitNotificaciones.guardarPendiente === 'function') {
        window.PlanitNotificaciones.guardarPendiente(mensaje, tipo, duracion);
        return;
    }

    try {
        sessionStorage.setItem('planit_notificacion_pendiente', JSON.stringify({ mensaje, tipo, duracion }));
    } catch (e) {
        // No interrumpir el flujo si sessionStorage no está disponible.
    }
}

// Si hay origen cargado desde PHP, mostrar su nombre en el botón
if (origenSeleccionado) {
    document.getElementById('origen-texto').textContent = origenSeleccionado.nombre;
}

// Abrir modal origen
let abrirDestinoTraSOrigen = false;

document.getElementById('btn-origen').addEventListener('click', function () {
    abrirDestinoTraSOrigen = false;
    document.getElementById('modal-origen').classList.add('activo');
    mostrarNotificacion('Abriendo selector de ciudad de origen.');
    cargarCiudadesOrigen();
});

document.getElementById('btn-destino').addEventListener('click', function () {
    if (!origenSeleccionado) {
        abrirDestinoTraSOrigen = true;
        document.getElementById('modal-origen').classList.add('activo');
        mostrarNotificacion('Selecciona primero la ciudad de origen.', 'warning');
        cargarCiudadesOrigen();
    } else {
        document.getElementById('modal-destino').classList.add('activo');
        mostrarNotificacion('Abriendo selector de ciudad de destino.');
        cargarCiudadesDestino();
    }
});

// Cerrar modales de ciudad
document.querySelectorAll('.modal-ciudades-close').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('modal-origen').classList.remove('activo');
        document.getElementById('modal-destino').classList.remove('activo');
    });
});

document.getElementById('modal-origen').addEventListener('click', function (e) {
    if (e.target === this) this.classList.remove('activo');
});

document.getElementById('modal-destino').addEventListener('click', function (e) {
    if (e.target === this) this.classList.remove('activo');
});

function cargarCiudadesOrigen(busqueda = '') {
    const url = new URL(apiOrigen, window.location.origin);
    if (busqueda) url.searchParams.append('q', busqueda);

    fetch(url)
        .then(response => response.json())
        .then(data => {
            const lista = document.getElementById('lista-origen');
            lista.innerHTML = '';
            data.ciudades.forEach(ciudad => {
                const item = document.createElement('div');
                item.className = 'ciudad-item';
                item.innerHTML = `<strong>${ciudad.nombre}</strong><small>${ciudad.pais} (${ciudad.codigo_iata})</small>`;
                item.addEventListener('click', function () { seleccionarOrigen(ciudad); });
                lista.appendChild(item);
            });
        })
        .catch(error => console.error('Error:', error));
}

function seleccionarOrigen(ciudad) {
    origenSeleccionado = ciudad;
    document.getElementById('origen-texto').textContent = ciudad.nombre;
    document.getElementById('origen-id').value = ciudad.id;
    document.getElementById('destino-id').value = '';
    document.getElementById('destino-buscar').value = '';
    document.getElementById('destino-texto').textContent = 'Selecciona ciudad';
    document.getElementById('modal-origen').classList.remove('activo');
    mostrarNotificacion(`Origen seleccionado: ${ciudad.nombre}.`, 'success');

    if (abrirDestinoTraSOrigen) {
        abrirDestinoTraSOrigen = false;
        document.getElementById('modal-destino').classList.add('activo');
        mostrarNotificacion('Ahora elige la ciudad de destino.');
        cargarCiudadesDestino();
    }
}

function cargarCiudadesDestino(busqueda = '') {
    if (!origenSeleccionado) {
        mostrarNotificacion('Por favor selecciona primero una ciudad de origen.', 'warning');
        return;
    }

    const url = new URL(apiDestino, window.location.origin);
    url.searchParams.append('origen_id', origenSeleccionado.id);
    if (busqueda) url.searchParams.append('q', busqueda);

    fetch(url)
        .then(response => response.json())
        .then(data => {
            const lista = document.getElementById('lista-destino');
            lista.innerHTML = '';

            if (data.ciudades.length === 0) {
                lista.innerHTML = '<div class="ciudad-item">No hay destinos disponibles desde esta ciudad</div>';
                return;
            }

            data.ciudades.forEach(ciudad => {
                const item = document.createElement('div');
                item.className = 'ciudad-item';
                item.innerHTML = `<strong>${ciudad.nombre}</strong><small>${ciudad.pais} (${ciudad.codigo_iata})</small>`;
                item.addEventListener('click', function () { seleccionarDestino(ciudad); });
                lista.appendChild(item);
            });
        })
        .catch(error => console.error('Error:', error));
}

function seleccionarDestino(ciudad) {
    document.getElementById('destino-texto').textContent = ciudad.nombre;
    document.getElementById('destino-id').value = ciudad.id;
    document.getElementById('destino-buscar').value = ciudad.nombre;
    document.getElementById('modal-destino').classList.remove('activo');
    mostrarNotificacion(`Destino seleccionado: ${ciudad.nombre}.`, 'success');
}

function actualizarVistaPasajeros() {
    if (pasajeros.adultos === 0 && pasajeros.bebes > 0) {
        pasajeros.bebes = 0;
    }

    document.getElementById('valor-adultos').textContent = pasajeros.adultos;
    document.getElementById('valor-ninos').textContent = pasajeros.ninos;
    document.getElementById('valor-bebes').textContent = pasajeros.bebes;

    document.getElementById('input-adultos').value = pasajeros.adultos;
    document.getElementById('input-ninos').value = pasajeros.ninos;
    document.getElementById('input-bebes').value = pasajeros.bebes;

    const total = pasajeros.adultos + pasajeros.ninos + pasajeros.bebes;
    document.getElementById('pasajeros-resumen').textContent =
        `${pasajeros.adultos} Adulto${pasajeros.adultos === 1 ? '' : 's'}, ` +
        `${pasajeros.ninos} Niño${pasajeros.ninos === 1 ? '' : 's'}, ` +
        `${pasajeros.bebes} Bebé${pasajeros.bebes === 1 ? '' : 's'}`;
    document.getElementById('pasajeros-total').textContent = `${total} pasajero${total === 1 ? '' : 's'}`;

    document.querySelector('.btn-cantidad[data-tipo="adultos"][data-op="restar"]').disabled = pasajeros.adultos <= 0;
    document.querySelector('.btn-cantidad[data-tipo="ninos"][data-op="restar"]').disabled = pasajeros.ninos <= 0;
    document.querySelector('.btn-cantidad[data-tipo="bebes"][data-op="restar"]').disabled = pasajeros.bebes <= 0;
    document.querySelector('.btn-cantidad[data-tipo="bebes"][data-op="sumar"]').disabled = pasajeros.adultos <= 0;

    actualizarResumenOfertaSeleccionada();
}

document.getElementById('btn-pasajeros').addEventListener('click', function (e) {
    e.stopPropagation();
    document.getElementById('panel-pasajeros').classList.toggle('activo');
});

document.querySelectorAll('.btn-cantidad').forEach(btn => {
    btn.addEventListener('click', function () {
        const tipo = this.dataset.tipo;
        const op = this.dataset.op;

        if (op === 'sumar') {
            if (tipo === 'bebes' && pasajeros.adultos <= 0) return;
            pasajeros[tipo] += 1;
        } else if (op === 'restar') {
            if (tipo === 'adultos' && pasajeros.adultos > 0) pasajeros.adultos -= 1;
            if (tipo === 'ninos' && pasajeros.ninos > 0) pasajeros.ninos -= 1;
            if (tipo === 'bebes' && pasajeros.bebes > 0) pasajeros.bebes -= 1;
        }

        actualizarVistaPasajeros();
        mostrarNotificacion(`Pasajeros actualizados: ${getTextoPasajerosResumen()}.`);
    });
});

document.querySelectorAll('.ayuda').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const modal = document.getElementById(this.dataset.modal);
        if (modal) modal.classList.add('activo');
    });
});

document.querySelectorAll('[data-cerrar-modal]').forEach(btn => {
    btn.addEventListener('click', function () {
        const modal = document.getElementById(this.dataset.cerrarModal);
        if (modal) modal.classList.remove('activo');
    });
});

document.querySelectorAll('.modal-info').forEach(modal => {
    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.classList.remove('activo');
    });
});

document.addEventListener('click', function (e) {
    const panel = document.getElementById('panel-pasajeros');
    const trigger = document.getElementById('btn-pasajeros');
    if (!panel.contains(e.target) && !trigger.contains(e.target)) {
        panel.classList.remove('activo');
    }
});

// Búsqueda en tiempo real en modales de ciudad
document.getElementById('input-buscar-origen').addEventListener('input', function () {
    cargarCiudadesOrigen(this.value);
});

document.getElementById('input-buscar-destino').addEventListener('input', function () {
    cargarCiudadesDestino(this.value);
});

// Inicializar destino en el DOM si ya venía seleccionado desde PHP
if (typeof destinoInicial !== 'undefined' && destinoInicial) {
    document.getElementById('destino-texto').textContent = destinoInicial.nombre;
    document.getElementById('destino-id').value = destinoInicial.id;
    document.getElementById('destino-buscar').value = destinoInicial.nombre;
}

// Navegar hacia el destino
function irADestino(url, origenId) {
    window.location.href = url + '?origen_id=' + origenId;
}

// Selección de día en calendario de ofertas
const resumenPrecio = document.getElementById('resumen-precio');
const resumenFechaIda = document.getElementById('resumen-fecha-ida');
const resumenFechaVuelta = document.getElementById('resumen-fecha-vuelta');
const resumenPasajerosTexto = document.getElementById('resumen-pasajeros-texto');
const inputFechaIda = document.getElementById('input-fecha-ida');
const inputFechaVuelta = document.getElementById('input-fecha-vuelta');
const inputMesIda = document.getElementById('input-mes-ida');
const inputMesVuelta = document.getElementById('input-mes-vuelta');
const btnQuitarVuelta = document.getElementById('btn-quitar-vuelta');

function getCeldasSeleccionables() {
    return Array.from(document.querySelectorAll('.cal-celda[data-seleccionable="1"]'));
}

function getPreciosMesCabecera() {
    return Array.from(document.querySelectorAll('.cal-mes-precio[data-precio-base][data-trayecto]'));
}

const seleccionPorTrayecto = {
    ida: { precio: 0, fecha: null, fechaIso: null },
    vuelta: { precio: 0, fecha: null, fechaIso: null },
};

function formatearPrecio(valor) {
    return valor.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function parsearFechaIso(fechaIso) {
    if (!fechaIso || !/^\d{4}-\d{2}-\d{2}$/.test(fechaIso)) {
        return null;
    }
    const [y, m, d] = fechaIso.split('-').map(v => parseInt(v, 10));
    return new Date(y, m - 1, d);
}

function esVueltaAnteriorAIda(fechaVueltaIso, fechaIdaIso) {
    const vuelta = parsearFechaIso(fechaVueltaIso);
    const ida = parsearFechaIso(fechaIdaIso);
    if (!vuelta || !ida) {
        return false;
    }
    return vuelta < ida;
}

function actualizarRestriccionesMesVuelta() {
    // Restricción removida: permitir cualquier mes de vuelta
    return;
}

function getTotalPasajerosParaPrecio() {
    const total = pasajeros.adultos + pasajeros.ninos + pasajeros.bebes;
    return total > 0 ? total : 1;
}

function getTextoPasajerosResumen() {
    return `${pasajeros.adultos} Adulto${pasajeros.adultos === 1 ? '' : 's'}, ` +
           `${pasajeros.ninos} Niño${pasajeros.ninos === 1 ? '' : 's'}, ` +
           `${pasajeros.bebes} Bebé${pasajeros.bebes === 1 ? '' : 's'}`;
}

function actualizarPreciosCalendarioPorPasajeros() {
    const factor = getTotalPasajerosParaPrecio();
    const celdasSeleccionables = getCeldasSeleccionables();
    const preciosMesCabecera = getPreciosMesCabecera();

    celdasSeleccionables.forEach(celda => {
        const base = parseFloat(celda.dataset.precio || '0');
        const precioNodo = celda.querySelector('.cal-precio');
        if (precioNodo) {
            precioNodo.textContent = `${formatearPrecio(base * factor)} EUR`;
        }
    });

    preciosMesCabecera.forEach(nodo => {
        const base = parseFloat(nodo.dataset.precioBase || '0');
        nodo.textContent = `${formatearPrecio(base * factor)} EUR`;
    });
}

function actualizarResumenOfertaSeleccionada() {
    const factor = getTotalPasajerosParaPrecio();

    const totalBase = (seleccionPorTrayecto.ida.precio || 0) + (seleccionPorTrayecto.vuelta.precio || 0);

    if (resumenPrecio) {
        resumenPrecio.textContent = `${formatearPrecio(totalBase * factor)} EUR`;
    }
    if (resumenFechaIda) {
        resumenFechaIda.textContent = seleccionPorTrayecto.ida.fecha || '--';
    }
    if (resumenFechaVuelta) {
        resumenFechaVuelta.textContent = seleccionPorTrayecto.vuelta.fecha || '--';
    }
    if (resumenPasajerosTexto) {
        resumenPasajerosTexto.textContent = getTextoPasajerosResumen();
    }
}

function deseleccionarVuelta(mostrarToast = true) {
    seleccionPorTrayecto.vuelta = { precio: 0, fecha: null, fechaIso: null };
    if (inputFechaVuelta) {
        inputFechaVuelta.value = '';
    }

    getCeldasSeleccionables().forEach(celda => {
        if (celda.dataset.trayecto === 'vuelta') {
            celda.classList.remove('seleccionado');
        }
    });

    actualizarResumenOfertaSeleccionada();

    if (mostrarToast) {
        mostrarNotificacion('Vuelta deseleccionada. Continuaras con viaje solo ida.', 'success');
    }
}

function seleccionarDiaOferta(celda, opciones = {}) {
    const { mostrarAvisoInvalidacion = true, mostrarToastSeleccion = true } = opciones;
    const trayecto = celda.dataset.trayecto;
    const celdasSeleccionables = getCeldasSeleccionables();
    if (!trayecto || !seleccionPorTrayecto[trayecto]) {
        return false;
    }

    const fechaIsoSeleccion = celda.dataset.fechaIso || null;
    if (trayecto === 'vuelta' && esVueltaAnteriorAIda(fechaIsoSeleccion, seleccionPorTrayecto.ida.fechaIso)) {
        if (mostrarAvisoInvalidacion) {
            mostrarNotificacion('La fecha de vuelta no puede ser anterior a la fecha de ida.', 'warning');
        }
        return false;
    }

    celdasSeleccionables.forEach(item => {
        if (item.dataset.trayecto === trayecto) {
            item.classList.remove('seleccionado');
        }
    });
    celda.classList.add('seleccionado');

    seleccionPorTrayecto[trayecto].precio = parseFloat(celda.dataset.precio || '0');
    seleccionPorTrayecto[trayecto].fecha = celda.dataset.fecha || null;
    seleccionPorTrayecto[trayecto].fechaIso = fechaIsoSeleccion;

    if (trayecto === 'ida') {
        if (inputFechaIda) inputFechaIda.value = seleccionPorTrayecto.ida.fechaIso || '';
        if (inputMesIda && seleccionPorTrayecto.ida.fechaIso) {
            inputMesIda.value = seleccionPorTrayecto.ida.fechaIso.substring(0, 7);
        }

        if (esVueltaAnteriorAIda(seleccionPorTrayecto.vuelta.fechaIso, seleccionPorTrayecto.ida.fechaIso)) {
            const candidataVuelta = Array.from(celdasSeleccionables).find(item => {
                return item.dataset.trayecto === 'vuelta'
                    && item.dataset.fechaIso
                    && !esVueltaAnteriorAIda(item.dataset.fechaIso, seleccionPorTrayecto.ida.fechaIso);
            });

            if (candidataVuelta) {
                seleccionarDiaOferta(candidataVuelta, { mostrarAvisoInvalidacion: false, mostrarToastSeleccion: false });
            } else {
                seleccionPorTrayecto.vuelta = { precio: 0, fecha: null, fechaIso: null };
                if (inputFechaVuelta) inputFechaVuelta.value = '';
            }
        }

        actualizarRestriccionesMesVuelta();
    }

    if (trayecto === 'vuelta') {
        if (inputFechaVuelta) inputFechaVuelta.value = seleccionPorTrayecto.vuelta.fechaIso || '';
        if (inputMesVuelta && seleccionPorTrayecto.vuelta.fechaIso) {
            inputMesVuelta.value = seleccionPorTrayecto.vuelta.fechaIso.substring(0, 7);
        }
    }

    actualizarResumenOfertaSeleccionada();

    if (mostrarToastSeleccion && seleccionPorTrayecto[trayecto].fecha) {
        mostrarNotificacion(`${trayecto === 'ida' ? 'Ida' : 'Vuelta'} seleccionada: ${seleccionPorTrayecto[trayecto].fecha}.`);
    }

    return true;
}

getCeldasSeleccionables().forEach(celda => {
    celda.addEventListener('click', function () { seleccionarDiaOferta(this); });
});

if (btnQuitarVuelta) {
    btnQuitarVuelta.addEventListener('click', function () {
        deseleccionarVuelta(true);
    });
}

// Botón CONTINUAR → redirigir al proceso de compra
const btnContinuar = document.querySelector('.btn-continuar-oferta');
if (btnContinuar) {
    btnContinuar.addEventListener('click', function () {
        const fechaIda = seleccionPorTrayecto.ida.fechaIso;
        if (!fechaIda) {
            mostrarNotificacion('Selecciona una fecha de ida para continuar.', 'warning');
            return;
        }
        if (!origenSeleccionado || !destinoInicial) {
            mostrarNotificacion('Selecciona origen y destino para continuar.', 'warning');
            return;
        }

        const fechaVuelta = seleccionPorTrayecto.vuelta.fechaIso || null;
        const tipoViaje = fechaVuelta ? 'ida_vuelta' : 'solo_ida';

        const params = new URLSearchParams({
            tipo_viaje: tipoViaje,
            origen: origenSeleccionado.nombre,
            destino: destinoInicial.nombre,
            fecha_ida: fechaIda,
            adultos: pasajeros.adultos,
            menores: pasajeros.ninos,
            infantes: pasajeros.bebes,
            zona: 'all',
        });

        if (fechaVuelta) {
            params.set('fecha_vuelta', fechaVuelta);
        }

        window.location.href = urlResultados + '?' + params.toString();
    });
}

if (getCeldasSeleccionables().length) {
    const celdasSeleccionables = getCeldasSeleccionables();
    const candidatasIda = Array.from(celdasSeleccionables).filter(celda => celda.dataset.trayecto === 'ida');
    const candidatasVuelta = Array.from(celdasSeleccionables).filter(celda => celda.dataset.trayecto === 'vuelta');

    if (candidatasIda.length) {
        const celdaIdaPorDefecto = candidatasIda.find(celda => celda.classList.contains('mejor')) || candidatasIda[0];
        seleccionarDiaOferta(celdaIdaPorDefecto, { mostrarAvisoInvalidacion: false, mostrarToastSeleccion: false });
    }

    if (candidatasVuelta.length) {
        const mejorVueltaValida = candidatasVuelta.find(celda => {
            const esMejor = celda.classList.contains('mejor');
            return esMejor && !esVueltaAnteriorAIda(celda.dataset.fechaIso || null, seleccionPorTrayecto.ida.fechaIso);
        });

        const primeraVueltaValida = candidatasVuelta.find(celda => {
            return !esVueltaAnteriorAIda(celda.dataset.fechaIso || null, seleccionPorTrayecto.ida.fechaIso);
        });

        const celdaVueltaPorDefecto = mejorVueltaValida || primeraVueltaValida;
        if (celdaVueltaPorDefecto) {
            seleccionarDiaOferta(celdaVueltaPorDefecto, { mostrarAvisoInvalidacion: false, mostrarToastSeleccion: false });
        }
    }
}

if (inputMesVuelta) {
    inputMesVuelta.dataset.minGeneral = inputMesVuelta.min || '';
}

if (inputMesIda) {
    inputMesIda.addEventListener('change', function () {
        actualizarRestriccionesMesVuelta();
        mostrarNotificacion(`Mes de ida actualizado: ${this.value || 'sin seleccionar'}.`);
    });
}

if (inputMesVuelta) {
    inputMesVuelta.addEventListener('change', function () {
        mostrarNotificacion(`Mes de vuelta actualizado: ${this.value || 'sin seleccionar'}.`);
    });
}

actualizarRestriccionesMesVuelta();

// Selector personalizado de origen para bloque "Ofertas desde"
const btnSelectorOrigen = document.getElementById('btn-selector-origen');
const listaOrigenOferta = document.getElementById('lista-origen-oferta');
const inputOrigenOfertaId = document.getElementById('input-origen-oferta-id');
const textoSelectorOrigen = document.getElementById('texto-selector-origen');
const formInspirate = document.getElementById('form-inspirate');

if (btnSelectorOrigen && listaOrigenOferta && inputOrigenOfertaId && textoSelectorOrigen && formInspirate) {
    btnSelectorOrigen.addEventListener('click', function (e) {
        e.stopPropagation();
        listaOrigenOferta.classList.toggle('activa');
    });

    listaOrigenOferta.querySelectorAll('.opcion-origen-oferta').forEach(opcion => {
        opcion.addEventListener('click', function () {
            inputOrigenOfertaId.value = this.dataset.id;
            textoSelectorOrigen.textContent = this.dataset.nombre;
            listaOrigenOferta.classList.remove('activa');
            guardarNotificacionPendiente(`Mostrando ofertas desde ${this.dataset.nombre}.`, 'info', 3000);
            // Navegar con hash para que el scroll aterrice en la sección, no en el top
            const params = new URLSearchParams(new FormData(formInspirate));
            window.location.href = formInspirate.action + '?' + params.toString() + '#inspirate-ofertas';
        });
    });

    document.addEventListener('click', function (e) {
        if (!btnSelectorOrigen.contains(e.target) && !listaOrigenOferta.contains(e.target)) {
            listaOrigenOferta.classList.remove('activa');
        }
    });
}

const formDestinos = document.getElementById('destinos-form');
if (formDestinos) {
    formDestinos.addEventListener('submit', function (e) {
        if (formDestinos.dataset.enviando === '1') {
            return;
        }

        e.preventDefault();
        formDestinos.dataset.enviando = '1';
        mostrarNotificacion('Aplicando filtros de búsqueda en esta misma página...', 'success');

        // Damos un pequeño margen para que el usuario vea el aviso antes de recargar.
        setTimeout(() => {
            formDestinos.submit();
        }, 1000);
    });
}

document.querySelectorAll('.cal-mes-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        guardarNotificacionPendiente('Cambiando el mes del calendario...', 'info', 3000);
    });
});

document.querySelectorAll('.inspirate-link').forEach(link => {
    link.addEventListener('click', function () {
        guardarNotificacionPendiente('Aplicando esta oferta al buscador principal...', 'success', 3200);
    });
});

document.querySelectorAll('.inspirate-detalle').forEach(link => {
    link.addEventListener('click', function () {
        guardarNotificacionPendiente('Abriendo el detalle del destino...', 'success', 3200);
    });
});

actualizarVistaPasajeros();
actualizarResumenOfertaSeleccionada();
