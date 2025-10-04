document.addEventListener('DOMContentLoaded', () => {
    // Importación dinámica de módulos necesarios al cargar el DOM
    import("./wcmlim_getcookies.js").then(module => {
        const getCookie = module.getCookie;
        if (!getCookie('wcmlim_selected_location_termid')) {
            if (getCookie('geolocation_accepted')) {
                if (document.querySelector('#set-def-store-popup-btn')) {
                    document.querySelector('#set-def-store-popup-btn').click();
                }
            } else {
                // if (document.querySelector('#set-def-store-popup-btn')) {
                //     document.querySelector('#set-def-store-popup-btn').click();
                // }
            }
        }
    });

    import("./wcmlim_success_callback.js").then(module => {
        window.alies_sucCalbk = module;
    });
    import("./wcmlim_error_callback.js").then(module => {
        window.alies_errCalbk = module;
    });
    import("./wcmlim_setcookies.js").then(module => {
        window.alies_setcookies = module;
    });

    const modal = document.getElementById('geolocation-modal');
    const reminderModal = document.getElementById('geolocation-reminder-modal');
    const isSafari = (() => {
        const ua = navigator.userAgent;
        const isSafariUA = /^((?!chrome|chromium|android).)*safari/i.test(ua);
        const isAppleVendor = navigator.vendor && navigator.vendor.includes('Apple');
        return isSafariUA && isAppleVendor;
    })();

    if (isSafari && getCookie('safari_alert_shown') !== 'true') {
        const intervalID = setInterval(() => {
            const loader = document.getElementById('msgLoading');

            // Si el loader no existe o ya no está visible
            if (!loader || loader.style.display === 'none') {
                clearInterval(intervalID); // Detener el intervalo

                // Mostrar alerta una sola vez y registrar en cookie
                Swal.fire({
                    icon: "info",
                    title: "Estás usando Safari",
                    text: "Si la ubicación no se activa, asegúrate de haber dado permisos manualmente en la configuración del navegador.",
                    timer: 5000,
                    timerProgressBar: true,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                });

                setCookie('safari_alert_shown', 'true', 7); // Guardar cookie por 7 días
            }
        }, 300); // Verifica cada 300ms
    }

    const loader = document.getElementById('msgLoading');

    // --- Cookies Helper ---

    /**
     * Establece una cookie en el navegador.
     * @param {string} name - Nombre de la cookie.
     * @param {string} value - Valor de la cookie.
     * @param {number} days - Número de días que durará la cookie (por defecto 30).
     */
    function setCookie(name, value, days = 30) {
        const d = new Date();
        d.setTime(d.getTime() + (days * 86400000));
        document.cookie = `${name}=${value}; expires=${d.toUTCString()}; path=/`;
    }

    /**
     * Obtiene el valor de una cookie por su nombre.
     * @param {string} name - Nombre de la cookie.
     * @returns {string|null} Valor de la cookie o null si no existe.
     */
    function getCookie(name) {
        const nameEQ = name + "=";
        const cookies = document.cookie.split(';');
        for (let c of cookies) {
            while (c.charAt(0) === ' ') c = c.substring(1);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length);
        }
        return null;
    }

    /**
     * Elimina una cookie estableciendo una fecha de expiración pasada.
     * @param {string} name - Nombre de la cookie.
     */
    function deleteCookie(name) {
        document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
    }

    // --- Alertas ---

    /**
     * Muestra una alerta usando SweetAlert2 si el usuario ha bloqueado la geolocalización.
     * Solo se muestra una vez gracias al uso de una cookie.
     */
    function SwalDenied() {
        if (getCookie('deniedUserLocation') !== 'shown') {
            Swal.fire({
                icon: "error",
                title: 'Ubicación bloqueada',
                text: "Has bloqueado la ubicación. Habilítala en tu navegador para mejorar la experiencia.",
                footer: '<a href="https://support.apple.com/es-es/HT203033" target="_blank">¿Cómo habilito la ubicación?</a>',
                confirmButtonText: 'Entendido'
            });
            setCookie('deniedUserLocation', 'shown');
        }
    }

    // --- Solicitar ubicación ---

    /**
     * Solicita al usuario su ubicación mediante la API de geolocalización del navegador.
     * Si se obtiene correctamente, se llama al callback de éxito.
     * Si falla, se manejan los distintos errores posibles.
     */
    function ejecutarGeolocalizacion() {
        if (!navigator.geolocation) {
            Swal.fire({ icon: "error", text: "Tu navegador no soporta geolocalización." });
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                alies_sucCalbk.successCallback(position);
            },
            (error) => {
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        SwalDenied();
                        break;
                    case error.TIMEOUT:
                        Swal.fire({ icon: "error", text: "La solicitud tardó demasiado. Intenta nuevamente." });
                        break;
                    default:
                        Swal.fire({
                            icon: "error",
                            text: "No pudimos obtener tu ubicación.",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            showCancelButton: false,
                            didOpen: () => {
                                setCookie('geolocation_accepted', 'false');
                                setTimeout(() => {
                                    window.location.search = '?r=refresh';
                                }, 1000);
                            }
                        });

                }
                if (loader) {
                    loader.style.display = 'none';
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 20000,
                maximumAge: 0
            }
        );
    }

    // --- Verificar permiso de geolocalización ---

    /**
     * Verifica el estado actual del permiso de geolocalización del navegador.
     * Muestra el modal si el usuario aún no ha aceptado o ha denegado el permiso.
     */
    async function verificarPermisoGeolocalizacion() {
        const cookieValor = getCookie('geolocation_accepted');

        // Para navegadores que no soportan Permissions API
        if (!navigator.permissions || typeof navigator.permissions.query !== 'function') {
            if (cookieValor === null || cookieValor === 'false') {
                modal.style.display = 'flex';
            }
            return;
        }

        try {
            const status = await navigator.permissions.query({ name: 'geolocation' });

            status.onchange = () => {
                //  console.log("Estado de permiso cambiado a:", status.state);
                handlePermissionChange(status.state);
            };

            // Verificación directa inicial
            if (status.state === 'denied' && cookieValor === 'true') {
                deleteCookie('geolocation_accepted');
                deleteCookie('deniedUserLocation');
                modal.style.display = 'flex';
            } else {
                handlePermissionChange(status.state);
            }

        } catch (e) {
            console.warn("Permisos de navegador no soportados.");
        }
    }

    /**
     * Maneja los cambios en el estado de permiso de geolocalización.
     * @param {string} state - Estado actual del permiso: 'granted', 'prompt', 'denied'.
     */
    function handlePermissionChange(state) {
        switch (state) {
            case 'granted':
                setCookie('geolocation_accepted', 'true');
                break;
            case 'prompt':
                if (getCookie('geolocation_accepted') === null) {
                    modal.style.display = 'flex';
                }
                break;
            case 'denied':
                SwalDenied();
                break;
        }
    }

    // --- Eventos de botones del modal ---

    /**
     * Botón para permitir geolocalización manualmente desde el modal.
     * Muestra loader y ejecuta la solicitud.
     */
    document.getElementById('allow-geolocation').addEventListener('click', () => {
        if (loader) {
            loader.style.display = 'flex';
        }
        modal.style.display = 'none';
        ejecutarGeolocalizacion();
    });

    /**
     * Botón para denegar la geolocalización manualmente desde el modal.
     * Muestra un mensaje y evita que se vuelva a mostrar el modal.
     */
    document.getElementById('deny-geolocation').addEventListener('click', () => {
        modal.style.display = 'none';
        const modalNoPermiso = new bootstrap.Modal(document.getElementById('modalNoPermiso'));
        modalNoPermiso.show();

        // Detectar cuando el usuario hace click en el botón de cerrar
        document.querySelector('#modalNoPermiso [data-bs-dismiss="modal"]').addEventListener('click', () => {
            setCookie('geolocation_accepted', 'false');
            setTimeout(() => {
                window.location.href = window.location.pathname;
            }, 300);
        });

        // Swal.fire({
        //     icon: "error",
        //     text: "Has decidido no compartir tu ubicación. No volveremos a pedirla.",
        //     allowOutsideClick: false,
        //     allowEscapeKey: false,
        //     allowOutsideClick: false,
        //     allowEscapeKey: false
        // }).then(() => {
        //     setCookie('geolocation_accepted', 'false');
        //     setTimeout(() => {
        //         window.location.href = window.location.pathname;
        //     }, 300);
        // });
    });

    /**
     * Botón del modal de recordatorio para cerrar el mensaje informativo.
     */
    document.getElementById('understand-reminder').addEventListener('click', () => {
        reminderModal.style.display = 'none';
    });

    // --- Inicio automático ---
    verificarPermisoGeolocalizacion();
});

// // // Mostrar error específico para Safari
// function showSafariBrowser(error) {
//     switch (error.code) {
//         case error.PERMISSION_DENIED:
//             Swal.fire({
//                 icon: "error",
//                 text: "Has decidido no compartir tu ubicación, pero está bien. No volveremos a pedirlo.",
//             });
//             alies_setcookies.setcookie("wcmlim_nearby_location", " ");
//             break;
//         case error.POSITION_UNAVAILABLE:
//             Swal.fire({
//                 icon: "error",
//                 text: "La información de ubicación no está disponible.",
//             });
//             break;
//         case error.TIMEOUT:
//             Swal.fire({
//                 icon: "error",
//                 text: "La solicitud para obtener la ubicación ha expirado.",
//             });
//             break;
//         case error.UNKNOWN_ERROR:
//             Swal.fire({
//                 icon: "error",
//                 text: "Ocurrió un error desconocido.",
//             });
//             break;
//     }
// }

