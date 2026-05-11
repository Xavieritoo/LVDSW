document.addEventListener('DOMContentLoaded', function () {
    const PRECIO_ASIENTO = 10;
    const PRECIO_MALETA = 25;

    const formularioCheckin = document.getElementById('form-checkin');
    if (!formularioCheckin) {
        return;
    }

    let PASO_INICIAL_CONFIG = 1;
    const pasoInicialRaw = formularioCheckin.getAttribute('data-initial-step');
    if (pasoInicialRaw !== null && pasoInicialRaw !== '') {
        PASO_INICIAL_CONFIG = Number(pasoInicialRaw);
    }

    let ASIENTO_INCLUIDO_PLAN = false;
    const asientoIncluidoRaw = formularioCheckin.getAttribute('data-asiento-incluido-plan');
    if (asientoIncluidoRaw === '1') {
        ASIENTO_INCLUIDO_PLAN = true;
    }

    function formatearEur(n) {
        return Number(n).toFixed(2) + ' EUR';
    }

    function irAPaso(step) {
        const pasoActual = Number(step);

        document.querySelectorAll('.wizard-panel').forEach(function (panel) {
            panel.classList.toggle('activo', Number(panel.dataset.step) === pasoActual);
        });

        document.querySelectorAll('.wizard-step').forEach(function (badge) {
            const pasoInsignia = Number(badge.dataset.step);
            badge.classList.remove('activo', 'completado');
            if (pasoInsignia < pasoActual) {
                badge.classList.add('completado');
            } else if (pasoInsignia === pasoActual) {
                badge.classList.add('activo');
            }
        });

        if (pasoActual === 4 || pasoActual === 5) {
            refrescarResumen();
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validarPasoDatos() {
        const panelDatos = document.querySelector('.wizard-panel[data-step="1"]');
        const cajaError = document.getElementById('datos-validacion-error');
        if (!panelDatos) {
            return true;
        }

        let valido = true;
        const errores = [];
        let hayCamposVacios = false;
        let hayDuplicadosDocumento = false;
        let hayErroresFormatoDocumento = false;
        const obligatorios = panelDatos.querySelectorAll('input[required], select[required], textarea[required]');

        obligatorios.forEach(function (campo) {
            const valor = (campo.value || '').trim();
            const campoValido = valor !== '';

            campo.classList.toggle('is-invalid', !campoValido);
            if (!campoValido) {
                valido = false;
                hayCamposVacios = true;
            }
        });

        const documentosVistos = new Map();
        const inputsDocumento = panelDatos.querySelectorAll('input[name$="[numero_documento]"]');

        inputsDocumento.forEach(function (inputDoc) {
            const numNorm = normalizarDocumento(inputDoc.value);
            const tipoName = inputDoc.name.replace('[numero_documento]', '[tipo_documento]');
            const tipoSelect = panelDatos.querySelector('select[name="' + tipoName + '"]');
            let tipo = '';
            if (tipoSelect && tipoSelect.value) {
                tipo = tipoSelect.value;
            }
            tipo = tipo.trim();

            if (numNorm === '') {
                return;
            }

            const errorFormato = validarFormatoDocumentoFrontend(tipo, numNorm);
            if (errorFormato) {
                valido = false;
                inputDoc.classList.add('is-invalid');
                if (tipoSelect) {
                    tipoSelect.classList.add('is-invalid');
                }
                hayErroresFormatoDocumento = true;
            }

            if (documentosVistos.has(numNorm)) {
                valido = false;
                inputDoc.classList.add('is-invalid');
                documentosVistos.get(numNorm).classList.add('is-invalid');
                hayDuplicadosDocumento = true;
            } else {
                documentosVistos.set(numNorm, inputDoc);
            }
        });

        if (!valido) {
            if (hayCamposVacios) {
                errores.push('Debes completar todos los campos obligatorios de datos antes de continuar a asientos.');
            }
            if (hayDuplicadosDocumento) {
                errores.push('No se permite repetir el mismo número de documento en la misma reserva.');
            }
            if (hayErroresFormatoDocumento) {
                errores.push('Revisa formato y tipo de documento según la ruta (Schengen o fuera de Schengen).');
            }
            if (errores.length === 0) {
                errores.push('Debes completar todos los campos obligatorios de datos antes de continuar a asientos.');
            }
        }

        if (cajaError) {
            if (valido) {
                cajaError.classList.add('d-none');
            } else {
                cajaError.classList.remove('d-none');
                cajaError.innerHTML = errores.join('<br>');
            }
        }

        if (!valido) {
            const primerInvalido = panelDatos.querySelector('.is-invalid');
            if (primerInvalido) {
                primerInvalido.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        return valido;
    }

    function normalizarDocumento(valor) {
        const texto = String(valor || '').toUpperCase();
        let resultado = '';

        for (let i = 0; i < texto.length; i++) {
            const ch = texto[i];
            if (ch !== ' ' && ch !== '\t' && ch !== '\n' && ch !== '\r') {
                resultado += ch;
            }
        }

        return resultado;
    }

    function maxLengthDocumento(tipo) {
        if (tipo === 'DNI') {
            return 9;
        }
        if (tipo === 'PASAPORTE') {
            return 15;
        }
        return 15;
    }

    function aplicarRestriccionesDocumento(panelDatos) {
        if (!panelDatos) {
            return;
        }

        const inputsDocumento = panelDatos.querySelectorAll('input[name$="[numero_documento]"]');

        inputsDocumento.forEach(function (inputDoc) {
            const tipoName = inputDoc.name.replace('[numero_documento]', '[tipo_documento]');
            const tipoSelect = panelDatos.querySelector('select[name="' + tipoName + '"]');
            let tipo = '';
            if (tipoSelect && tipoSelect.value) {
                tipo = tipoSelect.value;
            }
            tipo = tipo.trim();
            const limite = maxLengthDocumento(tipo);

            inputDoc.maxLength = limite;
            inputDoc.value = normalizarDocumento(inputDoc.value).slice(0, limite);
        });
    }

    function aplicarRestriccionInputDocumento(inputDoc, panelDatos) {
        if (!inputDoc || !panelDatos) {
            return;
        }

        const tipoName = inputDoc.name.replace('[numero_documento]', '[tipo_documento]');
        const tipoSelect = panelDatos.querySelector('select[name="' + tipoName + '"]');
        let tipo = '';
        if (tipoSelect && tipoSelect.value) {
            tipo = tipoSelect.value;
        }
        tipo = tipo.trim();
        const limite = maxLengthDocumento(tipo);

        inputDoc.maxLength = limite;
        inputDoc.value = normalizarDocumento(inputDoc.value).slice(0, limite);
    }

    function validarFormatoDocumentoFrontend(tipo, numNorm) {
        if (!tipo || !numNorm) {
            return null;
        }

        if (tipo === 'DNI' && !esDniValido(numNorm)) {
            return 'DNI invalido';
        }
        if (tipo === 'PASAPORTE' && !esPasaporteValido(numNorm)) {
            return 'Pasaporte invalido';
        }

        return null;
    }

    function esDniValido(valor) {
        if (valor.length !== 9) {
            return false;
        }

        const numeros = valor.slice(0, 8);
        const letra = valor.slice(8, 9);
        if (!soloDigitos(numeros)) {
            return false;
        }

        return esLetraMayuscula(letra);
    }

    function esPasaporteValido(valor) {
        if (valor.length < 6 || valor.length > 15) {
            return false;
        }

        for (let i = 0; i < valor.length; i++) {
            const ch = valor[i];
            if (!esLetraMayuscula(ch) && !esDigito(ch)) {
                return false;
            }
        }

        return true;
    }

    function soloDigitos(valor) {
        if (valor.length === 0) {
            return false;
        }

        for (let i = 0; i < valor.length; i++) {
            if (!esDigito(valor[i])) {
                return false;
            }
        }

        return true;
    }

    function esDigito(ch) {
        return ch >= '0' && ch <= '9';
    }

    function esLetraMayuscula(ch) {
        return ch >= 'A' && ch <= 'Z';
    }

    document.querySelectorAll('.wizard-next, .wizard-prev').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const panelActual = btn.closest('.wizard-panel');
            let pasoActual = 0;
            if (panelActual && panelActual.dataset.step) {
                pasoActual = Number(panelActual.dataset.step);
            }
            const pasoDestino = Number(btn.dataset.goStep || 0);

            if (pasoActual === 1 && pasoDestino === 2 && !validarPasoDatos()) {
                return;
            }

            irAPaso(btn.dataset.goStep);
        });
    });

    const panelDatos = document.querySelector('.wizard-panel[data-step="1"]');

    document.querySelectorAll('.wizard-panel[data-step="1"] input[required], .wizard-panel[data-step="1"] select[required], .wizard-panel[data-step="1"] textarea[required]').forEach(function (campo) {
        campo.addEventListener('input', function () {
            if ((campo.value || '').trim() !== '') {
                campo.classList.remove('is-invalid');
            }
            if (campo.name && campo.name.endsWith('[numero_documento]')) {
                aplicarRestriccionesDocumento(panelDatos);
            }
        });

        campo.addEventListener('change', function () {
            if ((campo.value || '').trim() !== '') {
                campo.classList.remove('is-invalid');
            }

            if (campo.name && (campo.name.endsWith('[tipo_documento]') || campo.name.endsWith('[numero_documento]'))) {
                aplicarRestriccionesDocumento(panelDatos);
            }
        });
    });

    if (panelDatos) {
        const docInputs = panelDatos.querySelectorAll('input[name$="[numero_documento]"]');
        const tipoSelects = panelDatos.querySelectorAll('select[name$="[tipo_documento]"]');

        docInputs.forEach(function (inputDoc) {
            aplicarRestriccionInputDocumento(inputDoc, panelDatos);

            inputDoc.addEventListener('input', function () {
                aplicarRestriccionInputDocumento(inputDoc, panelDatos);
            });

            inputDoc.addEventListener('paste', function () {
                requestAnimationFrame(function () {
                    aplicarRestriccionInputDocumento(inputDoc, panelDatos);
                });
            });
        });

        tipoSelects.forEach(function (tipoSelect) {
            tipoSelect.addEventListener('change', function () {
                const docName = tipoSelect.name.replace('[tipo_documento]', '[numero_documento]');
                const inputDoc = panelDatos.querySelector('input[name="' + docName + '"]');
                if (inputDoc) {
                    aplicarRestriccionInputDocumento(inputDoc, panelDatos);
                }
            });
        });
    }

    aplicarRestriccionesDocumento(panelDatos);

    document.querySelectorAll('.selector-pasajero-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (btn.disabled || btn.dataset.esBebe === '1') {
                return;
            }

            const pasajeroId = btn.dataset.pasajeroId;

            document.querySelectorAll('.selector-pasajero-btn').forEach(function (b) {
                b.classList.remove('activo');
            });
            btn.classList.add('activo');

            document.querySelectorAll('.mapa-pasajero').forEach(function (mapa) {
                mapa.classList.add('d-none');
            });
            const panel = document.getElementById('mapa-pasajero-' + pasajeroId);
            if (panel) {
                panel.classList.remove('d-none');
            }
        });
    });

    document.querySelectorAll('.asiento-grid').forEach(function (grid) {
        const pasajeroId = grid.dataset.pasajeroId;
        const input = document.getElementById('asiento-sel-' + pasajeroId);
        const badge = document.getElementById('asiento-badge-' + pasajeroId);

        grid.querySelectorAll('.asiento-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (btn.disabled || btn.dataset.estado === 'ocupado') {
                    return;
                }

                const codigo = btn.dataset.codigo;

                const asientoRepetido = Array.from(document.querySelectorAll('input[id^="asiento-sel-"]')).some(function (otroInput) {
                    if (otroInput.id === 'asiento-sel-' + pasajeroId) {
                        return false;
                    }
                    return (otroInput.value || '').trim().toUpperCase() === codigo;
                });

                if (asientoRepetido) {
                    window.alert('Ese asiento ya lo seleccionaste para otro pasajero. Elige uno distinto.');
                    return;
                }

                grid.querySelectorAll('.asiento-btn').forEach(function (b) {
                    if (b.dataset.estado !== 'ocupado') {
                        b.classList.remove('seleccionado');
                    }
                });

                btn.classList.add('seleccionado');
                input.value = codigo;
                badge.className = 'badge bg-primary px-3 py-2';
                badge.innerHTML = 'Asiento seleccionado: ' + codigo;
                refrescarResumen();
            });
        });
    });

    document.querySelectorAll('.limpiar-asiento-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const pasajeroId = btn.dataset.pasajeroId;
            const input = document.getElementById('asiento-sel-' + pasajeroId);
            const badge = document.getElementById('asiento-badge-' + pasajeroId);
            const mapa = document.getElementById('mapa-pasajero-' + pasajeroId);

            if (input) {
                input.value = '';
            }
            if (badge) {
                badge.className = 'badge bg-secondary px-3 py-2';
                badge.textContent = 'Sin asiento de pago seleccionado';
            }

            if (mapa) {
                mapa.querySelectorAll('.asiento-btn.seleccionado').forEach(function (botonAsiento) {
                    botonAsiento.classList.remove('seleccionado');
                });
            }

            refrescarResumen();
        });
    });

    function refrescarResumen() {
        const lineas = [];
        let asientosPagados = 0;
        let maletasExtra = 0;

        document.querySelectorAll('input[id^="asiento-sel-"]').forEach(function (input) {
            const pasajeroId = input.id.replace('asiento-sel-', '');
            const nombreBtn = document.querySelector('.selector-pasajero-btn[data-pasajero-id="' + pasajeroId + '"]');
            let nombre = 'Pasajero ' + pasajeroId;
            if (nombreBtn && nombreBtn.dataset.pasajeroNombre) {
                nombre = nombreBtn.dataset.pasajeroNombre;
            }
            const esBebe = input.dataset.esBebe === '1';
            const asiento = (input.value || '').trim();
            const eqSelect = document.querySelector('select[name="pasajero_' + pasajeroId + '[equipaje_extra]"]');
            let extra = 0;
            if (eqSelect) {
                extra = Number(eqSelect.value || 0);
            }

            if (!esBebe && asiento !== '') {
                asientosPagados++;
            }
            maletasExtra += extra;

            let asientoTexto = asiento;
            if (esBebe) {
                asientoTexto = 'regazo de uno de sus padres';
            } else if (!asientoTexto) {
                asientoTexto = 'automático';
            }
            lineas.push('<li><strong>' + nombre + '</strong>: asiento ' + asientoTexto + ', maletas extra ' + extra + '.</li>');
        });

        let importeAsientos = 0;
        if (!ASIENTO_INCLUIDO_PLAN) {
            importeAsientos = asientosPagados * PRECIO_ASIENTO;
        }
        const importeMaletas = maletasExtra * PRECIO_MALETA;
        const total = importeAsientos + importeMaletas;

        const resumenPasajeros = document.getElementById('resumen-pasajeros');
        const resumenAsientos = document.getElementById('resumen-importe-asientos');
        const resumenMaletas = document.getElementById('resumen-importe-maletas');
        const resumenTotal = document.getElementById('resumen-importe-total');

        if (resumenPasajeros) {
            resumenPasajeros.innerHTML = '<ul class="mb-0">' + lineas.join('') + '</ul>';
        }
        if (resumenAsientos) {
            if (ASIENTO_INCLUIDO_PLAN) {
                resumenAsientos.textContent = 'Incluido';
            } else {
                resumenAsientos.textContent = formatearEur(importeAsientos);
            }
        }
        if (resumenMaletas) {
            resumenMaletas.textContent = formatearEur(importeMaletas);
        }
        if (resumenTotal) {
            resumenTotal.textContent = formatearEur(total);
        }
    }

    document.querySelectorAll('select[data-equipaje-input="1"]').forEach(function (el) {
        el.addEventListener('change', refrescarResumen);
    });

    let pasoInicial = 1;
    if (PASO_INICIAL_CONFIG >= 1 && PASO_INICIAL_CONFIG <= 5) {
        pasoInicial = PASO_INICIAL_CONFIG;
    }
    irAPaso(pasoInicial);
});
