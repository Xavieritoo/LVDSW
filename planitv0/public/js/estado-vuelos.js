// estado-vuelos.js — Modales de selección de ciudad (origen/destino)

let origenSeleccionado = null;
let destinoSeleccionado = null;

// Abrir modal origen
document.getElementById('btn-origen-vuelo').addEventListener('click', function () {
    document.getElementById('modal-origen').classList.add('activo');
    document.getElementById('input-buscar-origen').value = '';
    cargarCiudades('origen');
    setTimeout(function () {
        document.getElementById('input-buscar-origen').focus();
    }, 100);
});

// Abrir modal destino
document.getElementById('btn-destino-vuelo').addEventListener('click', function () {
    document.getElementById('modal-destino').classList.add('activo');
    document.getElementById('input-buscar-destino').value = '';
    cargarCiudades('destino');
    setTimeout(function () {
        document.getElementById('input-buscar-destino').focus();
    }, 100);
});

// Cerrar modales
document.querySelectorAll('.modal-ciudades-close').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var modalId = btn.getAttribute('data-modal');
        document.getElementById(modalId).classList.remove('activo');
    });
});

document.getElementById('modal-origen').addEventListener('click', function (e) {
    if (e.target === this) this.classList.remove('activo');
});

document.getElementById('modal-destino').addEventListener('click', function (e) {
    if (e.target === this) this.classList.remove('activo');
});

// Búsqueda en tiempo real (origen)
var timerBuscarOrigen = null;
document.getElementById('input-buscar-origen').addEventListener('input', function () {
    clearTimeout(timerBuscarOrigen);
    var valor = this.value;
    timerBuscarOrigen = setTimeout(function () {
        cargarCiudades('origen', valor);
    }, 250);
});

// Búsqueda en tiempo real (destino)
var timerBuscarDestino = null;
document.getElementById('input-buscar-destino').addEventListener('input', function () {
    clearTimeout(timerBuscarDestino);
    var valor = this.value;
    timerBuscarDestino = setTimeout(function () {
        cargarCiudades('destino', valor);
    }, 250);
});

// Cargar ciudades desde la API
function cargarCiudades(tipo, busqueda) {
    var url = new URL(apiCiudades, window.location.origin);
    if (busqueda) {
        url.searchParams.append('q', busqueda);
    }

    var listaId = (tipo === 'origen') ? 'lista-origen' : 'lista-destino';

    fetch(url)
        .then(function (response) { return response.json(); })
        .then(function (data) {
            var lista = document.getElementById(listaId);
            lista.innerHTML = '';

            if (!data.ciudades || data.ciudades.length === 0) {
                lista.innerHTML = '<div class="ciudad-item">No se encontraron ciudades</div>';
                return;
            }

            var ciudades = data.ciudades;
            if (tipo === 'destino' && origenSeleccionado) {
                ciudades = ciudades.filter(function (c) {
                    return c.codigo_iata !== origenSeleccionado.codigo_iata;
                });
            }
            if (tipo === 'origen' && destinoSeleccionado) {
                ciudades = ciudades.filter(function (c) {
                    return c.codigo_iata !== destinoSeleccionado.codigo_iata;
                });
            }

            ciudades.forEach(function (ciudad) {
                var item = document.createElement('div');
                item.className = 'ciudad-item';
                item.innerHTML = '<strong>' + ciudad.nombre + '</strong><small>' + ciudad.pais + ' (' + ciudad.codigo_iata + ')</small>';
                item.addEventListener('click', function () {
                    if (tipo === 'origen') {
                        seleccionarOrigen(ciudad);
                    } else {
                        seleccionarDestino(ciudad);
                    }
                });
                lista.appendChild(item);
            });
        })
        .catch(function (error) {
            console.error('Error cargando ciudades:', error);
        });
}

// Seleccionar origen
function seleccionarOrigen(ciudad) {
    origenSeleccionado = ciudad;
    document.getElementById('origen-texto-vuelo').textContent = ciudad.nombre + ' (' + ciudad.codigo_iata + ')';
    document.getElementById('origen-valor').value = ciudad.codigo_iata;
    document.getElementById('modal-origen').classList.remove('activo');

    if (destinoSeleccionado && destinoSeleccionado.codigo_iata === ciudad.codigo_iata) {
        destinoSeleccionado = null;
        document.getElementById('destino-texto-vuelo').textContent = 'Selecciona destino';
        document.getElementById('destino-valor').value = '';
    }
}

// Seleccionar destino
function seleccionarDestino(ciudad) {
    destinoSeleccionado = ciudad;
    document.getElementById('destino-texto-vuelo').textContent = ciudad.nombre + ' (' + ciudad.codigo_iata + ')';
    document.getElementById('destino-valor').value = ciudad.codigo_iata;
    document.getElementById('modal-destino').classList.remove('activo');
}

// Validar antes de enviar
document.getElementById('formRuta').addEventListener('submit', function (e) {
    var origenVal = document.getElementById('origen-valor').value;
    var destinoVal = document.getElementById('destino-valor').value;

    if (!origenVal || !destinoVal) {
        e.preventDefault();
        alert('Selecciona ciudades de origen y destino.');
        return false;
    }

    if (origenVal === destinoVal) {
        e.preventDefault();
        alert('Selecciona ciudades de origen y destino diferentes.');
        return false;
    }
});

// Calendario — clic en día con vuelos
document.querySelectorAll('.cal-celda.seleccionable').forEach(function (celda) {
    celda.addEventListener('click', function () {
        var fecha = this.getAttribute('data-fecha');
        var origen = this.getAttribute('data-origen');
        var destino = this.getAttribute('data-destino');
        var mesInput = document.getElementById('input-mes-vuelo');
        var mes = mesInput ? mesInput.value : '';

        var url = new URL(window.location.href.split('?')[0], window.location.origin);
        url.searchParams.set('origen', origen);
        url.searchParams.set('destino', destino);
        url.searchParams.set('fecha', fecha);
        url.searchParams.set('mes', mes);
        window.location.href = url.toString();
    });
});
