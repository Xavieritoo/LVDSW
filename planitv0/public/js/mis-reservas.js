document.addEventListener('DOMContentLoaded', function () {
    const pagina = document.getElementById('mis-reservas-page');
    const filtroReembolso = document.getElementById('filtro-reembolso-estado');
    const canceladasTab = document.getElementById('canceladas-tab');
    const pestanas = document.querySelectorAll('#reservasTabs button[data-bs-toggle="tab"]');
    const inputTabActiva = document.getElementById('tab-activa-input');
    const inputLocalizadorEnlace = document.getElementById('localizador_enlace_paso1');
    const inputLocalizadorEnlacePaso2 = document.getElementById('localizador_enlace_paso2');
    const inputTokenEnlace = document.getElementById('token_enlace_modal');
    const hiddenSolicitar = document.getElementById('localizador_enlace_hidden_solicitar');
    const hiddenReenviar = document.getElementById('localizador_enlace_hidden_reenviar');
    const hiddenVerificar = document.getElementById('localizador_enlace_hidden_verificar');
    const hiddenTokenVerificar = document.getElementById('token_enlace_hidden_verificar');
    const modalLocalizador = document.getElementById('modalEnlaceLocalizador');
    const modalToken = document.getElementById('modalEnlaceToken');
    const formulariosEnlace = [
        document.getElementById('form-solicitar-enlace'),
        document.getElementById('form-reenviar-enlace'),
        document.getElementById('form-verificar-enlace'),
    ].filter(Boolean);

    function actualizarVisibilidadFiltro() {
        if (!filtroReembolso || !canceladasTab) {
            return;
        }

        const mostrar = canceladasTab.classList.contains('active');
        filtroReembolso.classList.toggle('d-none', !mostrar);
    }

    pestanas.forEach(function (tab) {
        tab.addEventListener('shown.bs.tab', function (event) {
            const idTab = (event.target.getAttribute('data-bs-target') || '').replace('#', '');
            if (inputTabActiva && idTab) {
                inputTabActiva.value = idTab;
            }
            actualizarVisibilidadFiltro();
        });
    });

    function sincronizarLocalizadorEnlace() {
        if (!inputLocalizadorEnlace) {
            return;
        }

        const valor = inputLocalizadorEnlace.value;
        if (hiddenSolicitar) {
            hiddenSolicitar.value = valor;
        }
        if (hiddenReenviar) {
            hiddenReenviar.value = valor;
        }
        if (hiddenVerificar) {
            hiddenVerificar.value = valor;
        }
        if (inputLocalizadorEnlacePaso2) {
            inputLocalizadorEnlacePaso2.value = valor;
        }
    }

    function sincronizarTokenEnlace() {
        if (!inputTokenEnlace || !hiddenTokenVerificar) {
            return;
        }

        hiddenTokenVerificar.value = inputTokenEnlace.value;
    }

    if (inputLocalizadorEnlace) {
        inputLocalizadorEnlace.addEventListener('input', sincronizarLocalizadorEnlace);
        sincronizarLocalizadorEnlace();
    }

    if (inputTokenEnlace) {
        inputTokenEnlace.addEventListener('input', sincronizarTokenEnlace);
        sincronizarTokenEnlace();
    }

    formulariosEnlace.forEach(function (formulario) {
        formulario.addEventListener('submit', function () {
            sincronizarLocalizadorEnlace();
            sincronizarTokenEnlace();
        });
    });

    actualizarVisibilidadFiltro();

    if (!pagina || !window.bootstrap || !window.bootstrap.Modal) {
        return;
    }

    const modalPreferido = pagina.dataset.modalPreferido || '';
    const enlaceExitoEnSesion = pagina.dataset.enlaceExito === '1';
    const tokenEnlaceAntiguo = pagina.dataset.tokenEnlaceAntiguo === '1';
    const localizadorEnlaceAntiguo = pagina.dataset.localizadorEnlaceAntiguo === '1';
    const tieneErroresLocalizadorEnlace = pagina.dataset.tieneErroresLocalizador === '1';

    const debeAbrirModalToken = modalPreferido === 'token' || enlaceExitoEnSesion || tokenEnlaceAntiguo;
    const debeAbrirModalLocalizador = modalPreferido === 'solicitar'
        || (localizadorEnlaceAntiguo && !debeAbrirModalToken)
        || (tieneErroresLocalizadorEnlace && !debeAbrirModalToken);

    if (debeAbrirModalToken && modalToken) {
        window.bootstrap.Modal.getOrCreateInstance(modalToken).show();
    } else if (debeAbrirModalLocalizador && modalLocalizador) {
        window.bootstrap.Modal.getOrCreateInstance(modalLocalizador).show();
    }
});
