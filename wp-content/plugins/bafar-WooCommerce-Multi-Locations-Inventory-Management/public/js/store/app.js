(function ($, window) {
    'use strict';

    /** =========================
     *  Constantes / Selectores
     *  ========================= */
    const NS = '.wcmlim';
    const Helper = window.WCMLIM || {};
    const DEBUG = false;
    const log = (...a) => DEBUG && console.log('[wcmlim]', ...a);

    const $modal = $('#modalSeleccionTienda');
    const modalEl = $modal[0];
    const modal = modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;

    const $band = $('.buscador-contenido');
    const $btnToggle = $('#btnBuscadorTienda');
    const $btnOpen = $('#btnAbrirTienda');

    const $state = $('#selectState');
    const $store = $('#selectStore');

    // Bloque “Entrega en:”
    const $storeName = $('#wcmlim-store-name');
    const $street = $('#wcmlim_street_number_h');
    const $route = $('#wcmlim_route_h');
    const $cp = $('#wcmlim_postal_code_h');
    const $locality = $('#wcmlim_locality_h');
    const $href = $('#wcmlim_href_h');

    // Bloque Recolección 
    const $msgDelivery = $('#message-delivery');
    const $msgNoGeo = $('#message-no-geo');
    const $btnShareGeo = $('#wcmlim_href_h_geo');

    // Icono del desplegable (banda azul)
    const $icon = $('.icon-down');

    // Animación banda azul
    const DURATION = 300;
    const EASING = 'swing';

    /** =========================
     *  Estado / Caches
     *  ========================= */
    let running = false;            // guard de animación
    let statesCache = null;         // lista de estados
    const storesCache = new Map();  // stateId -> stores[]

    /** =========================
     *  Helpers
     *  ========================= */

    // Texto o value según tag
    const setText = ($el, value) => {
        $el.is('input,textarea,select') ? $el.val(value ?? '') : $el.text(value ?? '');
    };

    // Cookies JSON
    const readSelectedStoreCookie = () => {
        const raw = Helper.getCookie?.('wcmlim_selected_location_json');
        if (!raw) return null;
        try { return JSON.parse(raw); } catch { return null; }
    };
    const readGeoCookie = () => {
        const raw = Helper.getCookie?.('wcmlim_location_geo_json');
        if (!raw) return null;
        try { return JSON.parse(raw); } catch { return null; }
    };

    // Href de Google Maps (lat/lng preferente)
    const buildMapsHref = (data) => {
        const lat = data?.lat ?? data?.wcmlim_lat;
        const lng = data?.lng ?? data?.wcmlim_lng;
        if (lat && lng) return `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`;

        const nbsp = '\u00A0';
        const cp = data?.postal_code ?? data?.wcmlim_postal_code ?? '';
        const parts = [
            [data?.street_number ?? data?.wcmlim_street_number, data?.route ?? data?.wcmlim_route].filter(Boolean).join(' '),
            data?.locality ?? data?.wcmlim_locality ?? '',
            [cp ? `C.P.${nbsp}${cp}` : '', data?.city ?? data?.wcmlim_city ?? ''].filter(Boolean).join(' ')
        ].filter(Boolean);

        return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(parts.join(', '))}`;
    };

    /** =========================
     *  Render
     *  ========================= */

    // Bloque “Entrega en:”
    const renderDetailsStore = () => {
        const data = readSelectedStoreCookie();

        // Sin tienda seleccionada → limpia UI
        if (!data) {
            setText($street, '');
            setText($route, '');
            setText($cp, '');
            setText($locality, '');
            $href.removeAttr('href').text('').hide();
            $msgDelivery.hide();
            $msgNoGeo.hide();
            return;
        }

        // Datos “limpios”
        const name = data.name ?? '';
        const street = data.street_number ?? '';
        const route = data.route ?? '';
        const cp = data.postal_code ?? '';
        const loc = data.locality ?? '';
        const href = data.url || buildMapsHref(data);

        // Pintar
        setText($storeName, name || 'Tienda Desconocida');

        const nbsp = '\u00A0';
        setText($street, street);
        setText($route, route ? ` ${route},` : '');
        setText($cp, cp ? ` C.P.${nbsp}${cp}` : '');
        setText($locality, loc ? ` ${loc}` : '');

        if (href) {
            $href.attr('href', href).text('Ver en Google Maps').show();
        } else {
            $href.removeAttr('href').text('').hide();
        }
        renderGeo();
    };

    // Estados
    const initStates = () => {
        if (!$state.length) return;

        const paint = (states) => {
            if (Array.isArray(states) && states.length) {
                $state.html(Helper.buildOptionsHTML(states, 'term_id', 'group_name', 'Selecciona estado'));
            } else {
                $state.html('<option value="" disabled selected>Sin estados</option>');
            }
        };

        if (statesCache) return paint(statesCache);
        if (!Helper.getStates) return paint([]);

        Helper.getStates().then((states) => {
            statesCache = states || [];
            paint(statesCache);
        });
    };

    // Sucursales por estado
    const renderStores = (stores) => {
        if (!$store.length) return;

        if (!stores.length) {
            $store.html('<option value="" disabled selected>Sin sucursales</option>');
            return;
        }

        let html = '<option value="" disabled selected>Selecciona sucursal</option>';
        for (const s of stores) {
            html += `<option 
        value="${String(s.id)}"
        data-name="${String(s.name)}"
        data-slug="${String(s.slug || '')}"
        data-street-number="${String(s.meta?.wcmlim_street_number || '')}"
        data-route="${String(s.meta?.wcmlim_route || '')}"
        data-locality="${String(s.meta?.wcmlim_locality || '')}"
        data-postal-code="${String(s.meta?.wcmlim_postal_code || '')}"
        data-lat="${String(s.meta?.wcmlim_lat || '')}"
        data-lng="${String(s.meta?.wcmlim_lng || '')}"
      >${String(s.name)}</option>`;
        }
        $store.html(html);

        if (Helper.tryOpenSelect) Helper.tryOpenSelect($store);

        $store.off('change' + NS).on('change' + NS, function () {
            const val = this.value;
            if (!val || isNaN(+val)) return;

            const $sel = $(this).find('option:selected');
            const storeData = {
                name: $sel.data('name') || '',
                slug: $sel.data('slug') || '',
                street_number: $sel.data('street-number') || '',
                route: $sel.data('route') || '',
                locality: $sel.data('locality') || '',
                postal_code: $sel.data('postal-code') || '',
                lat: $sel.data('lat') || '',
                lng: $sel.data('lng') || ''
            };

            Helper.setCookie?.('wcmlim_selected_location_json', JSON.stringify(storeData), 30);
            Helper.setCookie?.('wcmlim_selected_location_termid', val, 30);
            Helper.setCookie?.('wcmlim_selected_location_group_termid', $state.val(), 30);

            renderDetailsStore();
            (bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl)).hide();
        });
    };

    const loadStores = (stateId) => {
        if (!stateId) return;
        if ($store.length) $store.html('<option value="" disabled selected>Cargando…</option>');

        if (storesCache.has(stateId)) return renderStores(storesCache.get(stateId));
        if (!Helper.getStoresByState) return renderStores([]);

        Helper.getStoresByState(stateId).then((stores) => {
            const list = Array.isArray(stores) ? stores : [];
            storesCache.set(stateId, list);
            renderStores(list);
        });
    };

    // Render de mensajes de geo
    function renderGeo() {
        const geo = readGeoCookie();              // { lat, lng, accuracy }
        const store = readSelectedStoreCookie();  // { lat, lng, ... }

        // Sin tienda -> oculta mensajes
        if (!store || !Number(store.lat) || !Number(store.lng)) {
            $msgDelivery.show().text('Servicio a domicilio no disponible.');
            $msgNoGeo.hide();
            return;
        }

        // Sin geo -> pide compartir
        if (!geo || !Number(geo.lat) || !Number(geo.lng)) {
            $msgDelivery.hide();
            $msgNoGeo.show();
            return;
        }

        const res = isWithinKm(geo.lat, geo.lng, store.lat, store.lng, 8);

        $msgNoGeo.hide();

        if (res.meters == null) {
            $msgDelivery.show().text('No se pudo calcular la distancia.');
            return;
        }

        if (res.ok) {
            $msgDelivery.show().text('Servicio adomicilio disponible');
        } else {
            $msgDelivery
                .show()
                .text(`Ups, estás a ${Helper.fmtKm(res.km)} km de la tienda. Nuestro envío llega hasta 8 km.`);
            const cookieMsg = Helper.getCookie('wcmlim_msg_type');
            if (!cookieMsg) {
                Helper.setCookie('wcmlim_msg_type', 1, 30);
            }
        }
    }

    /** =========================
     *  Distancia (Haversine)
     *  ========================= */
    function isWithinKm(latUser, lngUser, latStore, lngStore, kmLimit = 8) {
        const m = haversineMeters(latUser, lngUser, latStore, lngStore);
        if (m == null) return { ok: false, meters: null, km: null };
        const km = m / 1000;
        return { ok: km <= kmLimit, meters: m, km };
    }
    function haversineMeters(lat1, lng1, lat2, lng2) {
        const aLat = toNum(lat1), aLng = toNum(lng1), bLat = toNum(lat2), bLng = toNum(lng2);
        if (aLat == null || aLng == null || bLat == null || bLng == null) return null;

        const R = 6371000;
        const dLat = toRad(bLat - aLat);
        const dLng = toRad(bLng - aLng);
        const s =
            Math.sin(dLat / 2) ** 2 +
            Math.cos(toRad(aLat)) * Math.cos(toRad(bLat)) * Math.sin(dLng / 2) ** 2;

        return 2 * R * Math.asin(Math.sqrt(s));
    }
    const toRad = (d) => d * Math.PI / 180;
    const toNum = (v) => {
        const n = Number(v);
        return Number.isFinite(n) ? n : null;
    };

    /** =========================
     *  Eventos UI
     *  ========================= */

    // Abrir modal (evita dobles)
    const bindOpenModal = () => {
        if (!modal) return;
        $btnOpen.off('click' + NS).on('click' + NS, (e) => {
            e.preventDefault();
            if (!$modal.hasClass('show')) modal.show();
        });

        $modal
            .off('show.bs.modal' + NS + ' shown.bs.modal' + NS + ' hide.bs.modal' + NS + ' hidden.bs.modal' + NS)
            .on('show.bs.modal' + NS, () => $band.stop(true, true))
            .on('shown.bs.modal' + NS, () => $band.stop(true, true).slideUp(200))
            .on('hide.bs.modal' + NS, () => $band.stop(true, true))
            .on('hidden.bs.modal' + NS, () => $band.stop(true, true).slideDown(200));
    };

    // Banda azul
    const bindBandToggle = () => {
        $btnToggle.off('click' + NS).on('click' + NS, (e) => {
            e.preventDefault();
            if (running || $band.is(':animated')) return;
            running = true;

            const willOpen = !$band.is(':visible');
            $btnToggle.addClass('is-busy').css('pointer-events', 'none');
            $icon.toggleClass('giro', willOpen);

            $band.stop(true, false).slideToggle(DURATION, EASING).promise().done(() => {
                running = false;
                $btnToggle.removeClass('is-busy').css('pointer-events', '');
            });
        });
    };

    // Cambio de estado → sucursales
    const bindStateChange = () => {
        $state.off('change' + NS).on('change' + NS, function () {
            const stateId = String(this.value || '');
            if (!stateId) return;
            loadStores(stateId);
        });
    };

    // Botón "Compartir ubicación"
    $btnShareGeo.off('click' + NS).on('click' + NS, async function (e) {
        e.preventDefault();
        const ok = await Helper.doLocate($(this));   // ¡espera a que termine!
        renderGeo();
    });

    /** =========================
     *  Init
     *  ========================= */
    const init = () => {
        if (!$modal.length) return;
        if ($modal.data('wcmlim-init')) return;
        $modal.data('wcmlim-init', true);

        renderDetailsStore();
        initStates();
        bindOpenModal();
        bindBandToggle();
        bindStateChange();
    };

    // DOM ready
    $(init);
})(jQuery, window);