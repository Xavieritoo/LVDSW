document.addEventListener('DOMContentLoaded', function () {
    const motivo = document.getElementById('motivo');
    const comentario = document.getElementById('comentario-otro');
    const pagina = document.getElementById('area-personal-page');

    function alternarComentario() {
        if (!motivo || !comentario) {
            return;
        }

        if (motivo.value === 'otro') {
            comentario.style.display = 'block';
        } else {
            comentario.style.display = 'none';
        }
    }

    if (motivo) {
        motivo.addEventListener('change', alternarComentario);
        alternarComentario();
    }

    if (!pagina || !window.bootstrap || !window.bootstrap.Modal) {
        return;
    }

    if (pagina.dataset.openPasswordModal === '1') {
        const modalCambioContrasena = document.getElementById('modalCambiarPassword');
        if (modalCambioContrasena) {
            window.bootstrap.Modal.getOrCreateInstance(modalCambioContrasena).show();
        }
    }
});
