document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("loginForm");
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");
    const emailError = document.getElementById("emailError");
    const passwordError = document.getElementById("passwordError");
    const emailCheck = document.getElementById("emailCheck");
    const passwordCheck = document.getElementById("passwordCheck");
    const togglePassword = document.getElementById("togglePassword");
    const iconPassword = togglePassword.querySelector("i");

    // Validación al enviar el formulario
    form.addEventListener("submit", function (e) {
        let valid = true;

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

        // Password mínimo 4 caracteres
        if (passwordInput.value.length < 4) {
            passwordError.classList.remove("d-none");
            passwordCheck.classList.add("d-none");
            valid = false;
        } else {
            passwordError.classList.add("d-none");
            passwordCheck.classList.remove("d-none");
        }

        if (!valid) e.preventDefault();
    });

    // Toggle contraseña
    togglePassword.addEventListener("click", function () {
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            iconPassword.classList.remove("bi-eye-slash");
            iconPassword.classList.add("bi-eye");
        } else {
            passwordInput.type = "password";
            iconPassword.classList.remove("bi-eye");
            iconPassword.classList.add("bi-eye-slash");
        }
    });

});
