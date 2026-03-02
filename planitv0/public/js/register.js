document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("registerForm");

    // ⚠️ Comprobar que el formulario existe
    if (!form) return;

    const nombreInput = document.getElementById("nombre");
    const apellidosInput = document.getElementById("apellidos");
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("password_confirmation");

    const nombreError = document.getElementById("nombreError");
    const apellidosError = document.getElementById("apellidosError");
    const emailError = document.getElementById("emailError");
    const passwordError = document.getElementById("passwordError");
    const confirmPasswordError = document.getElementById("confirmPasswordError");

    const nombreCheck = document.getElementById("nombreCheck");
    const apellidosCheck = document.getElementById("apellidosCheck");
    const emailCheck = document.getElementById("emailCheck");
    const passwordCheck = document.getElementById("passwordCheck");
    const confirmPasswordCheck = document.getElementById("confirmPasswordCheck");

    const toggleButtons = document.querySelectorAll(".togglePassword");

    // ✅ Validación solo al enviar
    form.addEventListener("submit", function (e) {

        let valid = true;

        // Nombre
        if (nombreInput.value.trim() === "") {
            nombreError.classList.remove("d-none");
            nombreCheck.classList.add("d-none");
            valid = false;
        } else {
            nombreError.classList.add("d-none");
            nombreCheck.classList.remove("d-none");
        }

        // Apellidos
        if (apellidosInput.value.trim() === "") {
            apellidosError.classList.remove("d-none");
            apellidosCheck.classList.add("d-none");
            valid = false;
        } else {
            apellidosError.classList.add("d-none");
            apellidosCheck.classList.remove("d-none");
        }

        // Email
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
        if (!emailPattern.test(emailInput.value)) {
            emailError.classList.remove("d-none");
            emailCheck.classList.add("d-none");
            valid = false;
        } else {
            emailError.classList.add("d-none");
            emailCheck.classList.remove("d-none");
        }

        // Contraseña mínimo 4 caracteres
        if (passwordInput.value.length < 4) {
            passwordError.classList.remove("d-none");
            passwordCheck.classList.add("d-none");
            valid = false;
        } else {
            passwordError.classList.add("d-none");
            passwordCheck.classList.remove("d-none");
        }

        // Confirmar contraseña
        if (confirmPasswordInput.value !== passwordInput.value || confirmPasswordInput.value === "") {
            confirmPasswordError.classList.remove("d-none");
            confirmPasswordCheck.classList.add("d-none");
            valid = false;
        } else {
            confirmPasswordError.classList.add("d-none");
            confirmPasswordCheck.classList.remove("d-none");
        }

        if (!valid) {
            e.preventDefault();
        }

    });

    // ✅ Mostrar / ocultar contraseña (para ambos campos)
    toggleButtons.forEach(button => {
        button.addEventListener("click", function () {

            const targetId = this.getAttribute("data-target");
            const input = document.getElementById(targetId);
            const icon = this.querySelector("i");

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            } else {
                input.type = "password";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            }

        });
    });

});