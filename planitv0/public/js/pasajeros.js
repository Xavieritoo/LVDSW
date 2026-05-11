document.addEventListener('DOMContentLoaded', function () {
	var seleccionados = {};

	function aplicarPasajeroFrecuenteDesdeDatos(datos) {
		var tipo = datos.tipo;
		var index = datos.index;
		var nombreInput = document.getElementById(tipo + '_nombre_' + index);
		var apellidosInput = document.getElementById(tipo + '_apellidos_' + index);
		var fechaInput = document.getElementById(tipo + '_fecha_' + index);

		if (nombreInput) {
			nombreInput.value = datos.nombre || '';
		}

		if (apellidosInput) {
			apellidosInput.value = datos.apellidos || '';
		}

		if (fechaInput && datos.fechaNacimiento) {
			fechaInput.value = datos.fechaNacimiento;
		}
	}

	function actualizarBotones() {
		var idsUsados = Object.values(seleccionados);
		document.querySelectorAll('.js-seleccionar-frecuente').forEach(function (btn) {
			var id = btn.dataset.id;
			if (idsUsados.indexOf(id) !== -1) {
				btn.disabled = true;
				btn.classList.add('disabled');
				btn.title = 'Ya asignado a otro pasajero';
			} else {
				btn.disabled = false;
				btn.classList.remove('disabled');
				btn.title = '';
			}
		});
	}

	document.addEventListener('click', function (event) {
		var botonSeleccion = event.target.closest('.js-seleccionar-frecuente');
		if (!botonSeleccion) {
			return;
		}

		var tipo = botonSeleccion.dataset.tipo;
		var index = botonSeleccion.dataset.index;
		var slotKey = tipo + '_' + index;

		// Liberar la selección anterior de este slot (si había)
		delete seleccionados[slotKey];

		// Registrar la nueva selección
		seleccionados[slotKey] = botonSeleccion.dataset.id;

		aplicarPasajeroFrecuenteDesdeDatos({
			tipo: tipo,
			index: index,
			nombre: botonSeleccion.dataset.nombre,
			apellidos: botonSeleccion.dataset.apellidos,
			fechaNacimiento: botonSeleccion.dataset.fechaNacimiento,
		});

		actualizarBotones();
	});
});
