/**
 * cambiar-password.js
 * Validación frontend del formulario de cambio de contraseña
 */

document.addEventListener('DOMContentLoaded', function () {

    const formulario = document.getElementById('form-cambiar-password');
    const campoContrasenaNueva = document.getElementById('password_nuevo');
    const campoContrasenaConfirmacion = document.getElementById('password_nuevo_confirmation');
    const botonEnviar = document.getElementById('btn-cambiar');
    const errorConfirmacion = document.getElementById('confirm-error');

    // Indicadores de política
    const requisitoLongitud = document.getElementById('req-len');
    const requisitoMayuscula = document.getElementById('req-may');
    const requisitoNumero = document.getElementById('req-num');

    // Mostrar/Ocultar contraseña
    document.querySelectorAll('.toggle-pass').forEach(function (boton) {
        boton.addEventListener('click', function () {
            const idObjetivo = this.getAttribute('data-target');
            const campoObjetivo = document.getElementById(idObjetivo);
            if (!campoObjetivo) return;

            if (campoObjetivo.type === 'password') {
                campoObjetivo.type = 'text';
                this.querySelector('i').className = 'bi bi-eye-slash';
            } else {
                campoObjetivo.type = 'password';
                this.querySelector('i').className = 'bi bi-eye';
            }
        });
    });

    // Indicadores de política en tiempo real
    if (campoContrasenaNueva) {
        campoContrasenaNueva.addEventListener('input', actualizarIndicadores);
    }

    function actualizarIndicadores() {
        let valor = '';
        if (campoContrasenaNueva) {
            valor = campoContrasenaNueva.value;
        }

        const cumpleLongitud = valor.length >= 5;
        const cumpleMayuscula = contieneMayuscula(valor);
        const cumpleNumero = contieneNumero(valor);

        setIndicador(requisitoLongitud, cumpleLongitud, 'Minimo 5 caracteres');
        setIndicador(requisitoMayuscula, cumpleMayuscula, 'Al menos una mayuscula');
        setIndicador(requisitoNumero, cumpleNumero, 'Al menos un numero');
    }

    function setIndicador(elemento, cumple, texto) {
        if (!elemento) return;
        if (cumple) {
            elemento.className = 'text-success';
            elemento.innerHTML = '<i class="bi bi-check-circle me-1"></i>' + texto;
        } else {
            elemento.className = 'text-secondary';
            elemento.innerHTML = '<i class="bi bi-x-circle me-1"></i>' + texto;
        }
    }

    // Validacion coincidencia de contraseñas
    function verificarCoincidencia() {
        if (!campoContrasenaNueva || !campoContrasenaConfirmacion || !errorConfirmacion) return true;

        const coincide = campoContrasenaNueva.value === campoContrasenaConfirmacion.value;

        if (campoContrasenaConfirmacion.value.length > 0) {
            if (!coincide) {
                errorConfirmacion.classList.remove('d-none');
                campoContrasenaConfirmacion.classList.add('is-invalid');
                campoContrasenaConfirmacion.classList.remove('is-valid');
            } else {
                errorConfirmacion.classList.add('d-none');
                campoContrasenaConfirmacion.classList.remove('is-invalid');
                campoContrasenaConfirmacion.classList.add('is-valid');
            }
        }

        return coincide;
    }

    if (campoContrasenaConfirmacion) campoContrasenaConfirmacion.addEventListener('input',  verificarCoincidencia);
    if (campoContrasenaNueva) campoContrasenaNueva.addEventListener('input', verificarCoincidencia);

    // Validacion al enviar
    if (formulario) {
        formulario.addEventListener('submit', function (e) {
            let valido = true;
            let valor = '';
            if (campoContrasenaNueva) {
                valor = campoContrasenaNueva.value;
            }

            // Política de contraseña
            if (valor.length < 5 || !contieneMayuscula(valor) || !contieneNumero(valor)) {
                valido = false;
                if (campoContrasenaNueva) {
                    campoContrasenaNueva.classList.add('is-invalid');
                }
            }

            // Coincidencia
            if (!verificarCoincidencia()) {
                valido = false;
            }

            if (!valido) {
                e.preventDefault();
            }
        });
    }

    function contieneMayuscula(texto) {
        if (!texto) {
            return false;
        }

        for (let i = 0; i < texto.length; i++) {
            const ch = texto[i];
            if (ch >= 'A' && ch <= 'Z') {
                return true;
            }
        }

        return false;
    }

    function contieneNumero(texto) {
        if (!texto) {
            return false;
        }

        for (let i = 0; i < texto.length; i++) {
            const ch = texto[i];
            if (ch >= '0' && ch <= '9') {
                return true;
            }
        }

        return false;
    }

});
