(function (window, $) {
    'use strict';

    const L = window.multi_inventory_store || {};
    function q(url, v) { return v ? url + '?v=' + encodeURIComponent(v) : url; }

    // Devuelve un jqXHR (Promise-like)
    function getStates() {
        if (!L.stores || !L.stores.states || !L.stores.states.url) {
            return $.Deferred().resolve([]).promise();
        }
        return $.ajax({
            url: q(L.stores.states.url, L.stores.states.v),
            dataType: 'json',
            cache: false
        });
    }

    // Carga index fresco y luego el shard state-{id}.json con v
    function getStoresByState(stateId) {
        if (!L.stores || !L.stores.index || !L.stores.index.url) {
            return $.Deferred().resolve([]).promise();
        }
        return $.ajax({
            url: q(L.stores.index.url, L.stores.index.v),
            dataType: 'json',
            cache: false
        }).then(function (idx) {
            var entry = idx[String(stateId)];
            if (!entry || !entry.url) return [];
            return $.ajax({
                url: q(entry.url, entry.v || L.stores && L.stores.dirV),
                dataType: 'json',
                cache: true
            });
        });
    }

    // Intenta abrir un <select> nativo (no siempre posible en todos los browsers)
    function tryOpenSelect($el) {
        var el = $el[0];
        if (!el) return;
        try { el.focus({ preventScroll: true }); } catch (e) { el.focus(); }

        if (typeof el.showPicker === 'function') {
            try { el.showPicker(); return; } catch (e) { }
        }
        try { $el.trigger('mousedown').trigger('click'); } catch (e) { }
        setTimeout(function () {
            $el.trigger($.Event('keydown', { key: 'ArrowDown', altKey: true, bubbles: true }));
        }, 0);
    }

    // Insertamos una cookie
    function setCookie(key, value, days) {
        const expires = days
            ? "; expires=" + new Date(Date.now() + days * 864e5).toUTCString()
            : "";
        document.cookie = key + "=" + encodeURIComponent(value) + expires + "; path=/";
    }

    // Obtenemos la cookie
    function getCookie(key) {
        const cookies = document.cookie.split("; ");
        for (const c of cookies) {
            const [k, v] = c.split("=");
            if (k === key) return decodeURIComponent(v || "");
        }
        return null;
    }

    function buildOptionsHTML(items, valueKey, textKey, placeholder) {
        let html = `<option value="" disabled selected>${placeholder}</option>`;
        for (const it of items) {
            const v = it?.[valueKey] ?? '';
            const t = it?.[textKey] ?? '';
            html += `<option value="${String(v)}">${String(t)}</option>`;
        }
        return html;
    };

    // Obtener geolocalización del navegador
    const getUserPosition = async (opts = {}) => {
        if (!('geolocation' in navigator)) {
            throw new Error('Geolocalización no disponible');
        }

        const options = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0,
            ...opts
        };

        // Envolvemos el API basado en callbacks en una Promesa
        const pos = await new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(resolve, reject, options);
        });

        return {
            lat: pos.coords.latitude,
            lng: pos.coords.longitude,
            accuracy: pos.coords.accuracy
        };
    };

    function deleteCookie(name, path) {
        try {
            // Básico
            document.cookie = encodeURIComponent(name) + '=; Max-Age=0; path=' + path + ';';
            document.cookie = encodeURIComponent(name) + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=' + path + ';';

            // Intento con dominio (evita localhost)
            const host = location.hostname.replace(/^www\./, '');
            if (!/^(localhost|127\.0\.0\.1)$/.test(host)) {
                document.cookie = encodeURIComponent(name) + '=; Max-Age=0; path=' + path + '; domain=.' + host + ';';
            }
        } catch (_) { }
    }

    const DEBOUNCE_MS = 250;
    let inFlight = false;
    let lastTryTs = 0;

    async function doLocate($btn) {
        const now = Date.now();
        if (inFlight || (now - lastTryTs) < DEBOUNCE_MS) return false;
        lastTryTs = now;

        inFlight = true;
        $btn.prop('disabled', true).addClass('is-busy').css('pointer-events', 'none');

        try {
            const pos = await getUserPosition({
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
            // guarda con timestamp (útil para TTLs)
            setCookie?.('wcmlim_location_geo_json', JSON.stringify({ ...pos, ts: Date.now() }), 30);
            return true;
        } catch (err) {
            console.log("Geo: ", err);
            if (err && (err.code === 1 || /denied/i.test(err.message || ''))) {
                deleteCookie?.('wcmlim_location_geo_json', '/');
                alert(
                    'Para continuar, habilita el acceso a tu ubicación:\n' +
                    '• Abre los permisos del sitio (icono de candado o botón “i” junto a la barra de direcciones).\n' +
                    '• Activa “Ubicación” o “Permitir” para este sitio.\n' +
                    '• En teléfono: Ajustes del navegador → Permisos del sitio → Ubicación.\n' +
                    'Luego vuelve a tocar “Compartir ubicación”.'
                );
            } else {
                alert('No se pudo obtener tu ubicación. Intenta nuevamente.');
            }
            return false;
        } finally {
            inFlight = false;
            $btn.prop('disabled', false).removeClass('is-busy').css('pointer-events', '');
        }
    }

    const fmtKm = (km) =>
        new Intl.NumberFormat('es-MX', { maximumFractionDigits: 2 }).format(km);

    // API global
    window.WCMLIM = window.WCMLIM || {};
    window.WCMLIM.getStates = getStates;
    window.WCMLIM.getStoresByState = getStoresByState;
    window.WCMLIM.tryOpenSelect = function (el) { tryOpenSelect($(el)); };
    window.WCMLIM.setCookie = setCookie;
    window.WCMLIM.getCookie = getCookie;
    window.WCMLIM.buildOptionsHTML = buildOptionsHTML;
    window.WCMLIM.getUserPosition = getUserPosition;
    window.WCMLIM.deleteCookie = deleteCookie;
    window.WCMLIM.doLocate = doLocate;
    window.WCMLIM.fmtKm = fmtKm;
})(window, jQuery);