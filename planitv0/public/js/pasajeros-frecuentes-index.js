document.addEventListener('DOMContentLoaded', function () {
    const pagina = document.getElementById('pasajeros-frecuentes-page');
    if (!pagina || !window.bootstrap || !window.bootstrap.Modal) {
        return;
    }

    const idModalEditar = pagina.dataset.editModalId || '';
    const abrirModalCrear = pagina.dataset.openCrearModal === '1';

    if (idModalEditar !== '') {
        const modalEditar = document.getElementById('editarModal' + idModalEditar);
        if (modalEditar) {
            window.bootstrap.Modal.getOrCreateInstance(modalEditar).show();
        }
        return;
    }

    if (abrirModalCrear) {
        const modalCrear = document.getElementById('crearModal');
        if (modalCrear) {
            window.bootstrap.Modal.getOrCreateInstance(modalCrear).show();
        }
    }
});
