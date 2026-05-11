document.addEventListener("DOMContentLoaded", function () {

    let formulario = document.getElementById("registerForm");
    if (!formulario) return;

    formulario.onsubmit = function (e) {

        let nombre = document.getElementById("nombre").value.trim();
        let apellidos = document.getElementById("apellidos").value.trim();
        let correo = document.getElementById("email").value;
        let contrasena = document.getElementById("password").value;
        let confirmarContrasena = document.getElementById("password_confirmation").value;

        let valido = true;

        // Nombre
        if (nombre === "") {
            document.getElementById("nombreError").classList.remove("d-none");
            document.getElementById("nombreCheck").classList.add("d-none");
            valido = false;
        } else {
            document.getElementById("nombreError").classList.add("d-none");
            document.getElementById("nombreCheck").classList.remove("d-none");
        }

        // Apellidos
        if (apellidos === "") {
            document.getElementById("apellidosError").classList.remove("d-none");
            document.getElementById("apellidosCheck").classList.add("d-none");
            valido = false;
        } else {
            document.getElementById("apellidosError").classList.add("d-none");
            document.getElementById("apellidosCheck").classList.remove("d-none");
        }

        // Correo
        if (!esEmailBasicoValido(correo)) {
            document.getElementById("emailError").classList.remove("d-none");
            document.getElementById("emailCheck").classList.add("d-none");
            valido = false;
        } else {
            document.getElementById("emailError").classList.add("d-none");
            document.getElementById("emailCheck").classList.remove("d-none");
        }

        // Contrasena: minimo 5, una mayuscula y un numero
        const tieneMayuscula = contieneMayuscula(contrasena);
        const tieneNumero = contieneNumero(contrasena);

        if (contrasena.length < 5 || !tieneMayuscula || !tieneNumero) {
            document.getElementById("passwordError").classList.remove("d-none");
            document.getElementById("passwordCheck").classList.add("d-none");
            valido = false;
        } else {
            document.getElementById("passwordError").classList.add("d-none");
            document.getElementById("passwordCheck").classList.remove("d-none");
        }

        // Confirmar contraseña
        if (confirmarContrasena !== contrasena || confirmarContrasena === "") {
            document.getElementById("confirmPasswordError").classList.remove("d-none");
            document.getElementById("confirmPasswordCheck").classList.add("d-none");
            valido = false;
        } else {
            document.getElementById("confirmPasswordError").classList.add("d-none");
            document.getElementById("confirmPasswordCheck").classList.remove("d-none");
        }

        if (!valido) e.preventDefault();
    };

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

    function esEmailBasicoValido(valor) {
        if (!valor) {
            return false;
        }

        var correo = valor.trim();
        var posArroba = correo.indexOf("@");
        if (posArroba <= 0) {
            return false;
        }

        var ultimoArroba = correo.lastIndexOf("@");
        if (ultimoArroba !== posArroba) {
            return false;
        }

        if (correo.indexOf(" ") !== -1) {
            return false;
        }

        var dominio = correo.slice(posArroba + 1);
        var puntoDominio = dominio.indexOf(".");
        if (puntoDominio <= 0 || puntoDominio === dominio.length - 1) {
            return false;
        }

        return true;
    }

    // Botón contraseñas
    let botonesAlternar = document.querySelectorAll(".togglePassword");

    botonesAlternar.forEach(boton => {
        boton.onclick = function () {
            let campo = document.getElementById(this.dataset.target);
            let icono = this.querySelector("i");

            if (campo.type === "password") {
                campo.type = "text";
                icono.className = "bi bi-eye";
            } else {
                campo.type = "password";
                icono.className = "bi bi-eye-slash";
            }
        };
    });

});
