document.addEventListener("DOMContentLoaded", function () {

    const formulario = document.getElementById("loginForm");
    if (!formulario) return;

    const correoInput = document.getElementById("email");
    const contrasenaInput = document.getElementById("password");
    const correoError = document.getElementById("emailError");
    const contrasenaError = document.getElementById("passwordError");
    const correoCheck = document.getElementById("emailCheck");
    const contrasenaCheck = document.getElementById("passwordCheck");
    const alternarContrasena = document.getElementById("togglePassword");

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

    formulario.onsubmit = function (e) {
        let correo = correoInput.value;
        let contrasena = contrasenaInput.value;
        let valido = true;

        // Validacion correo
        if (!esEmailBasicoValido(correo)) {
            correoError.classList.remove("d-none");
            correoCheck.classList.add("d-none");
            valido = false;
        } else {
            correoError.classList.add("d-none");
            correoCheck.classList.remove("d-none");
        }

        // Validacion contrasena
        if (contrasena === "") {
            contrasenaError.classList.remove("d-none");
            contrasenaCheck.classList.add("d-none");
            valido = false;
        } else {
            contrasenaError.classList.add("d-none");
            contrasenaCheck.classList.remove("d-none");
        }

        if (!valido) e.preventDefault();
    };

    // Boton contrasena
    if (alternarContrasena) {
        alternarContrasena.onclick = function () {
            if (contrasenaInput.type === "password") {
                contrasenaInput.type = "text";
                this.querySelector("i").className = "bi bi-eye";
            } else {
                contrasenaInput.type = "password";
                this.querySelector("i").className = "bi bi-eye-slash";
            }
        };
    }

});
