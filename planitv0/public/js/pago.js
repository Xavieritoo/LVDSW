(function () {
    const form      = document.getElementById('payment-form');
    const btn       = document.getElementById('pay-btn');
    const statusBox = document.getElementById('payment-status');

    function showStatus(type, message, asHtml) {
        statusBox.className = 'mb-3 alert alert-' + type;
        if (asHtml) {
            statusBox.innerHTML = message;
        } else {
            statusBox.textContent = message;
        }
        statusBox.style.display = 'block';
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        btn.disabled    = true;
        btn.textContent = 'Procesando pago...';
        showStatus('info', 'Procesando pago...');

        const csrfToken = form.querySelector('input[name="_token"]').value;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: new FormData(form),
            });

            const data = await response.json();

            if (data.success) {
                showStatus('success', 'Pago completado correctamente. Redirigiendo a la confirmación...');
                btn.textContent = 'Pago completado';
                window.location.href = data.redirect || '/';
            } else {
                showStatus('danger', data.message || 'Ha ocurrido un error al procesar el pago.');
                btn.disabled    = false;
                btn.textContent = 'Ir al pago';
            }
        } catch (err) {
            showStatus('danger', 'Error de conexión. Por favor, inténtalo de nuevo.');
            btn.disabled    = false;
            btn.textContent = 'Ir al pago';
        }
    });
})();
