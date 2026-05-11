(() => {
    const formulario         = document.getElementById('formularioEquipajes');
    const checkboxesEquipaje = document.querySelectorAll('.bag-check');
    const elTotalEquipajes   = document.getElementById('totalEquipajes');
    const elTotalSeccion     = document.getElementById('totalSeccion');

    if (!formulario || !elTotalEquipajes || !elTotalSeccion) {
        return;
    }

    const totalBase = Number(formulario.dataset.totalBase || 0);

    function formatearEuros(valor) {
        return new Intl.NumberFormat('es-ES', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(valor) + ' EUR';
    }

    function actualizarTotales() {
        let totalEquipajes = 0;

        checkboxesEquipaje.forEach((check) => {
            if (check.checked) {
                totalEquipajes += Number(check.dataset.precio || 0);
            }
        });

        elTotalEquipajes.textContent = formatearEuros(totalEquipajes);
        elTotalSeccion.textContent = formatearEuros(totalBase + totalEquipajes);
    }

    checkboxesEquipaje.forEach((check) => {
        check.addEventListener('change', actualizarTotales);
    });

    actualizarTotales();
})();
