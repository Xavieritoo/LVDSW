document.addEventListener('DOMContentLoaded', function () {
    const motivo = document.getElementById('motivo');
    const comentario = document.getElementById('comentario-otro');

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
});
