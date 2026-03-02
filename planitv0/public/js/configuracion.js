document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("configCuentaForm");
    if (!form) return;

    function mostrarValido(check, error = null) {
        if (error) error.classList.add("d-none");
        check.classList.remove("d-none");
    }

    function mostrarInvalido(check, error = null) {
        if (error) error.classList.remove("d-none");
        check.classList.add("d-none");
    }

    function ocultarTodo(check, error = null) {
        if (error) error.classList.add("d-none");
        check.classList.add("d-none");
    }

    form.addEventListener("submit", function (e) {
        let valid = true;

        // === CAMPOS OBLIGATORIOS ===
        const nombre = document.getElementById("nombre");
        const apellidos = document.getElementById("apellidos");
        const email = document.getElementById("email");

        const nombreCheck = document.getElementById("nombreCheck");
        const apellidosCheck = document.getElementById("apellidosCheck");
        const emailCheck = document.getElementById("emailCheck");

        const nombreError = document.getElementById("nombreError");
        const apellidosError = document.getElementById("apellidosError");
        const emailError = document.getElementById("emailError");

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

        // Nombre
        if (nombre.value.trim() === "") {
            mostrarInvalido(nombreCheck, nombreError);
            valid = false;
        } else {
            mostrarValido(nombreCheck, nombreError);
        }

        // Apellidos
        if (apellidos.value.trim() === "") {
            mostrarInvalido(apellidosCheck, apellidosError);
            valid = false;
        } else {
            mostrarValido(apellidosCheck, apellidosError);
        }

        // Email
        if (!emailPattern.test(email.value)) {
            mostrarInvalido(emailCheck, emailError);
            valid = false;
        } else {
            mostrarValido(emailCheck, emailError);
        }

        // === CAMPOS OPCIONALES ===
        const telefono = document.getElementById("telefono");
        const pais = document.getElementById("pais");
        const codigo = document.getElementById("codigo_postal");
        const poblacion = document.getElementById("poblacion");
        const direccion = document.getElementById("direccion");
        const fecha = document.getElementById("fecha_nacimiento");
        const documento = document.getElementById("documento_identidad");

        const telefonoCheck = document.getElementById("telefonoCheck");
        const paisCheck = document.getElementById("paisCheck");
        const codigoCheck = document.getElementById("codigoCheck");
        const poblacionCheck = document.getElementById("poblacionCheck");
        const direccionCheck = document.getElementById("direccionCheck");
        const fechaCheck = document.getElementById("fechaCheck");
        const documentoCheck = document.getElementById("documentoCheck");

        const telefonoError = document.getElementById("telefonoError");
        const codigoError = document.getElementById("codigoError");
        const fechaError = document.getElementById("fechaError");
        const documentoError = document.getElementById("documentoError");

        const telefonoPattern = /^[0-9+\s]{7,15}$/;
        const codigoPattern = /^[0-9]{4,10}$/;

        // Teléfono
        if (telefono.value.trim() === "") {
            ocultarTodo(telefonoCheck, telefonoError);
        } else if (!telefonoPattern.test(telefono.value)) {
            mostrarInvalido(telefonoCheck, telefonoError);
            valid = false;
        } else {
            mostrarValido(telefonoCheck, telefonoError);
        }

        // Código Postal
        if (codigo.value.trim() === "") {
            ocultarTodo(codigoCheck, codigoError);
        } else if (!codigoPattern.test(codigo.value)) {
            mostrarInvalido(codigoCheck, codigoError);
            valid = false;
        } else {
            mostrarValido(codigoCheck, codigoError);
        }

        // País
        if (pais.value.trim() === "") {
            ocultarTodo(paisCheck);
        } else {
            mostrarValido(paisCheck);
        }

        // Población
        if (poblacion.value.trim() === "") {
            ocultarTodo(poblacionCheck);
        } else {
            mostrarValido(poblacionCheck);
        }

        // Dirección
        if (direccion.value.trim() === "") {
            ocultarTodo(direccionCheck);
        } else {
            mostrarValido(direccionCheck);
        }

        // Fecha de nacimiento (opcional)
        if (fecha.value.trim() === "") {
            ocultarTodo(fechaCheck, fechaError);
        } else {
            // Validamos formato de fecha YYYY-MM-DD
            const fechaValidaFormato = /^\d{4}-\d{2}-\d{2}$/.test(fecha.value);
            const fechaObj = new Date(fecha.value);
            const minFecha = new Date("1900-01-01");

            if (!fechaValidaFormato || fechaObj < minFecha) {
                mostrarInvalido(fechaCheck, fechaError);
                valid = false;
            } else {
                mostrarValido(fechaCheck, fechaError);
            }
        }

        // Documento identificativo (opcional)
        if (documento.value.trim() === "") {
            ocultarTodo(documentoCheck, documentoError);
        } else {
            if (documento.value.trim().length < 5) {
                mostrarInvalido(documentoCheck, documentoError);
                valid = false;
            } else {
                mostrarValido(documentoCheck, documentoError);
            }
        }

        if (!valid) {
            e.preventDefault();
        }
    });
});