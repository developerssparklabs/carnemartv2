document.addEventListener("DOMContentLoaded", function () {
    // Lista de modales con sus configuraciones
    var modals = [
        {
            id: "modal-mayoria-edad", // ID de la modal de mayoría de edad
            cookieName: "age_verified",
            duration: 30 // Duración en días
        },
        {
            id: "aviso-privacidad", // ID de la modal de cookies
            cookieName: "cookies_accepted",
            duration: 365 // Duración en días
        },
        {
            id: "mcpModal", // ID de la modal de promociones
            cookieName: "mcp_modal_shown",
            duration: 7 // Duración en días
        }
    ];

    // Función para mostrar la siguiente modal
    function showNextModal(index) {
        if (index >= modals.length) return; // Termina si no hay más modales

        var modalConfig = modals[index];
        var modalElement = document.getElementById(modalConfig.id);

        // Verifica si la modal existe y si no tiene la cookie correspondiente
        if (modalElement && !getCookie(modalConfig.cookieName)) {
            var modal = new bootstrap.Modal(modalElement);
            modal.show();

            // Cierra la modal y establece la cookie
            modalElement.addEventListener("hidden.bs.modal", function () {
                setCookie(modalConfig.cookieName, "true", modalConfig.duration);
                showNextModal(index + 1); // Muestra la siguiente modal
            });
            return; // Detenemos aquí, ya que estamos mostrando una modal
        }

        // Si no se mostró la modal, pasa a la siguiente
        showNextModal(index + 1);
    }

    // Inicia mostrando la primera modal
    showNextModal(0);

    // Función para establecer cookies
    function setCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    // Función para obtener cookies
    function getCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }
});
