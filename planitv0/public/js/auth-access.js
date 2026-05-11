document.addEventListener('DOMContentLoaded', function () {
    var formularioOlvido = document.getElementById('forgotForm');
    if (formularioOlvido) {
        formularioOlvido.addEventListener('submit', function (e) {
            var campoCorreo = formularioOlvido.querySelector('input[name="email"]');
            var valor = '';
            if (campoCorreo && campoCorreo.value) {
                valor = campoCorreo.value;
            }
            valor = valor.trim();
            var valido = valor.includes('@') && valor.includes('.');

            if (!valido) {
                e.preventDefault();
                alert('Introduce un correo electronico valido.');
            }
        });
    }

    var formularioVerificacion = document.getElementById('verifyForm');
    if (formularioVerificacion) {
        formularioVerificacion.addEventListener('submit', function (e) {
            var campoCodigo = formularioVerificacion.querySelector('input[name="codigo"]');
            var codigo = '';
            if (campoCodigo && campoCodigo.value) {
                codigo = campoCodigo.value;
            }
            codigo = codigo.trim();
            var codigoValido = codigo.length >= 6 && codigo.length <= 10 && esAlfanumerico(codigo);

            if (!codigoValido) {
                e.preventDefault();
                alert('Introduce un codigo valido (6-10 caracteres alfanumericos).');
            }
        });
    }

    var formularioReset = document.getElementById('resetForm');
    if (formularioReset) {
        formularioReset.addEventListener('submit', function (e) {
            var campoToken = formularioReset.querySelector('input[name="token"]');
            var campoContrasena = formularioReset.querySelector('input[name="password"]');
            var campoConfirmarContrasena = formularioReset.querySelector('input[name="password_confirmation"]');

            var token = '';
            if (campoToken && campoToken.value) {
                token = campoToken.value;
            }
            token = token.trim();

            var contrasena = '';
            if (campoContrasena && campoContrasena.value) {
                contrasena = campoContrasena.value;
            }

            var confirmarContrasena = '';
            if (campoConfirmarContrasena && campoConfirmarContrasena.value) {
                confirmarContrasena = campoConfirmarContrasena.value;
            }

            var tieneMayuscula = contieneMayuscula(contrasena);
            var tieneNumero = contieneNumero(contrasena);
            var contrasenaValida = contrasena.length >= 5 && tieneMayuscula && tieneNumero;
            var tokenValido = token.length >= 6;
            var confirmacionValida = contrasena === confirmarContrasena;

            if (!tokenValido || !contrasenaValida || !confirmacionValida) {
                e.preventDefault();
                alert('Revisa el formulario: token valido, contrasena segura y confirmacion correcta.');
            }
        });
    }

    function esAlfanumerico(texto) {
        if (!texto) {
            return false;
        }

        for (var i = 0; i < texto.length; i++) {
            var ch = texto.charAt(i);
            var esDigito = ch >= '0' && ch <= '9';
            var esMayuscula = ch >= 'A' && ch <= 'Z';
            var esMinuscula = ch >= 'a' && ch <= 'z';
            if (!esDigito && !esMayuscula && !esMinuscula) {
                return false;
            }
        }

        return true;
    }

    function contieneMayuscula(texto) {
        if (!texto) {
            return false;
        }

        for (var i = 0; i < texto.length; i++) {
            var ch = texto.charAt(i);
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

        for (var i = 0; i < texto.length; i++) {
            var ch = texto.charAt(i);
            if (ch >= '0' && ch <= '9') {
                return true;
            }
        }

        return false;
    }
});
