/**
 * area-personal.js
 * Validación frontend del formulario de perfil (sección 1.6.1)
 */

document.addEventListener('DOMContentLoaded', function () {

    const formulario = document.getElementById('form-perfil');
    const campoCorreo = document.getElementById('email');
    const campoFechaNacimiento = document.getElementById('fecha_nacimiento');
    const campoTelefono = document.getElementById('telefono_numero');
    const campoCiudad = document.getElementById('ciudad');
    const campoDireccion = document.getElementById('direccion');
    const campoCodigoPostal = document.getElementById('codigo_postal');

    if (campoCorreo) {
        campoCorreo.addEventListener('blur', function () {
            const valor = this.value.trim();
            if (!valor) {
                establecerEstadoCampo(this, 'El correo electrónico es obligatorio.');
                return;
            }

            const valido = esEmailBasicoValido(valor);
            if (valido) {
                establecerEstadoCampo(this, null);
            } else {
                establecerEstadoCampo(this, 'Correo electrónico no válido.');
            }
        });
    }

    // Fecha de nacimiento (type="date")
    if (campoFechaNacimiento) {
        campoFechaNacimiento.addEventListener('change', validarFecha);
        campoFechaNacimiento.addEventListener('blur', validarFecha);
    }

    function validarFecha() {
        if (!campoFechaNacimiento) return true;

        const textoFecha = campoFechaNacimiento.value.trim();
        if (!textoFecha) {
            establecerEstadoCampo(campoFechaNacimiento, 'La fecha de nacimiento es obligatoria.');
            return false;
        }

        const fecha = new Date(textoFecha);
        const anio = fecha.getFullYear();
        const mes = fecha.getMonth();
        const dia = fecha.getDate();

        // Validar que sea una fecha válida
        if (isNaN(fecha.getTime())) {
            establecerEstadoCampo(campoFechaNacimiento, 'La fecha no es válida.');
            return false;
        }

        // Validar que la fecha exista
        if (fecha.getFullYear() !== parseInt(textoFecha.split('-')[0]) ||
            fecha.getMonth() !== parseInt(textoFecha.split('-')[1]) - 1 ||
            fecha.getDate() !== parseInt(textoFecha.split('-')[2])) {
            establecerEstadoCampo(campoFechaNacimiento, 'La fecha no existe.');
            return false;
        }

        // Validar que no sea anterior a 1905-01-01
        const fechaMinima = new Date('1905-01-01');
        if (fecha < fechaMinima) {
            establecerEstadoCampo(campoFechaNacimiento, 'La fecha no puede ser anterior a 1905.');
            return false;
        }

        // Validar que no sea en el futuro
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        if (fecha > hoy) {
            establecerEstadoCampo(campoFechaNacimiento, 'La fecha de nacimiento no puede ser en el futuro.');
            return false;
        }

        // Validar edad razonable (máximo 120 años)
        let ajuste = 0;
        if (hoy < new Date(hoy.getFullYear(), mes, dia)) {
            ajuste = 1;
        }
        const edad = hoy.getFullYear() - anio - ajuste;

        if (edad < 0 || edad > 120) {
            establecerEstadoCampo(campoFechaNacimiento, 'La fecha de nacimiento no es válida.');
            return false;
        }

        establecerEstadoCampo(campoFechaNacimiento, null);
        return true;
    }

    // Telefono
    if (campoTelefono) {
        campoTelefono.addEventListener('input', function () {
            this.value = soloDigitosTexto(this.value);
        });

        campoTelefono.addEventListener('blur', function () {
            const valor = this.value.trim();
            if (!valor) {
                establecerEstadoCampo(this, null);
                return;
            }

            if (!esLongitudDigitosValida(valor, 9, 10)) {
                establecerEstadoCampo(this, 'El teléfono debe tener 9 o 10 dígitos.');
            } else {
                establecerEstadoCampo(this, null);
            }
        });
    }

    // Ciudad
    if (campoCiudad) {
        campoCiudad.addEventListener('blur', function () {
            const valor = this.value.trim();
            if (!valor) {
                establecerEstadoCampo(this, null);
                return;
            }

            if (valor.length < 2 || valor.length > 100) {
                establecerEstadoCampo(this, 'La ciudad debe tener entre 2 y 100 caracteres.');
                return;
            }

            if (!soloLetrasEspaciosGuiones(valor)) {
                establecerEstadoCampo(this, 'La ciudad solo puede contener letras, espacios y guiones.');
                return;
            }

            establecerEstadoCampo(this, null);
        });
    }

    // Direccion
    if (campoDireccion) {
        campoDireccion.addEventListener('blur', function () {
            const valor = this.value.trim();
            if (!valor) {
                establecerEstadoCampo(this, null);
                return;
            }

            if (valor.length < 5 || valor.length > 150) {
                establecerEstadoCampo(this, 'La dirección debe tener entre 5 y 150 caracteres.');
            } else {
                establecerEstadoCampo(this, null);
            }
        });
    }

    // Codigo postal
    if (campoCodigoPostal) {
        campoCodigoPostal.addEventListener('input', function () {
            this.value = soloAlfanumericoMayus(this.value);
        });

        campoCodigoPostal.addEventListener('blur', function () {
            const valor = this.value.trim();
            if (!valor) {
                establecerEstadoCampo(this, null);
                return;
            }

            if (valor.length < 4 || valor.length > 10) {
                establecerEstadoCampo(this, 'El código postal debe tener entre 4 y 10 caracteres.');
            } else {
                establecerEstadoCampo(this, null);
            }
        });
    }

    // Validacion al enviar
    if (formulario) {
        formulario.addEventListener('submit', function (e) {
            let valido = true;

            // Nombre y apellidos requeridos
            ['nombre', 'apellidos'].forEach(function (id) {
                const campo = document.getElementById(id);
                if (campo && !campo.value.trim()) {
                    establecerEstadoCampo(campo, 'Este campo es obligatorio.');
                    valido = false;
                }
            });

            if (campoCorreo) {
                const valorCorreo = campoCorreo.value.trim();
                if (!valorCorreo) {
                    establecerEstadoCampo(campoCorreo, 'El correo electrónico es obligatorio.');
                    valido = false;
                } else if (!esEmailBasicoValido(valorCorreo)) {
                    establecerEstadoCampo(campoCorreo, 'Correo electrónico no válido.');
                    valido = false;
                }
            }

            if (!validarFecha()) {
                valido = false;
            }

            if (!valido) {
                e.preventDefault();
                // Ir al primer error
                const primerError = formulario.querySelector('.is-invalid');
                if (primerError) {
                    primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }

    // Utilidad
    function establecerEstadoCampo(campo, mensajeError) {
        // Quitar mensaje previo
        const mensajeAnterior = campo.parentElement.querySelector('.js-feedback');
        if (mensajeAnterior) {
            mensajeAnterior.remove();
        }

        if (mensajeError === null) {
            campo.classList.remove('is-invalid');
            campo.classList.add('is-valid');
        } else {
            campo.classList.add('is-invalid');
            campo.classList.remove('is-valid');
            const mensaje = document.createElement('div');
            mensaje.className = 'invalid-feedback js-feedback';
            mensaje.textContent = mensajeError;
            campo.after(mensaje);
        }
    }

    function esEmailBasicoValido(valor) {
        if (!valor) {
            return false;
        }

        const correo = valor.trim();
        const posArroba = correo.indexOf('@');
        if (posArroba <= 0) {
            return false;
        }

        const ultimoArroba = correo.lastIndexOf('@');
        if (ultimoArroba !== posArroba) {
            return false;
        }

        if (correo.indexOf(' ') !== -1) {
            return false;
        }

        const dominio = correo.slice(posArroba + 1);
        const puntoDominio = dominio.indexOf('.');
        if (puntoDominio <= 0 || puntoDominio === dominio.length - 1) {
            return false;
        }

        return true;
    }

    function soloDigitosTexto(valor) {
        let salida = '';
        for (let i = 0; i < valor.length; i++) {
            const caracter = valor[i];
            if (caracter >= '0' && caracter <= '9') {
                salida += caracter;
            }
        }
        return salida;
    }

    function esLongitudDigitosValida(valor, min, max) {
        if (valor.length < min || valor.length > max) {
            return false;
        }
        return soloDigitosTexto(valor) === valor;
    }

    function soloLetrasEspaciosGuiones(valor) {
        for (let i = 0; i < valor.length; i++) {
            const caracter = valor[i];
            if (caracter === ' ' || caracter === '-') {
                continue;
            }

            if (caracter.toLowerCase() === caracter.toUpperCase()) {
                return false;
            }
        }

        return true;
    }

    function soloAlfanumericoMayus(valor) {
        const texto = String(valor || '').toUpperCase();
        let salida = '';

        for (let i = 0; i < texto.length; i++) {
            const caracter = texto[i];
            const esDigito = caracter >= '0' && caracter <= '9';
            const esMayuscula = caracter >= 'A' && caracter <= 'Z';
            if (esDigito || esMayuscula) {
                salida += caracter;
            }
        }

        return salida;
    }

});
