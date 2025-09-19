(function ($, window) {
    'use strict';
    /** ======================================================================
     *  CONSTANTES / SELECTORES (solo referencias, nada de lógica aquí)
     *  ==================================================================== */
    const NS = '.wcmlim';
    const Helper = window.WCMLIM || {};
    const DEBUG = false;

    // REST base
    const API_ROOT = (window.wpApiSettings?.root || (location.origin + '/wp-json/')).replace(/\/$/, '');
    const API_NS = 'wcmlim/v1';

    // Bootstrap modal
    const $modal = $('#modalSeleccionTienda');
    const modalEl = $modal[0];
    const modal = modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;

    // Banda/controles
    const $band = $('.buscador-contenido');
    const $btnToggle = $('#btnBuscadorTienda');
    const $btnOpen = $('#btnAbrirTienda');
    const $icon = $('.icon-down');

    // Selects
    const $state = $('#selectState');
    const $store = $('#selectStore');

    // Bloque “Entrega en:”
    const $storeName = $('#wcmlim-store-name');
    const $street = $('#wcmlim_street_number_h');
    const $route = $('#wcmlim_route_h');
    const $cpText = $('#wcmlim_postal_code_h');
    const $locality = $('#wcmlim_locality_h');
    const $href = $('#wcmlim_href_h');

    // Bloque Recolección/Geo
    const $msgDelivery = $('#message-delivery');
    const $msgNoGeo = $('#message-no-geo');
    const $btnShareGeo = $('#wcmlim_href_h_geo');

    // Modal: compartir ubicación
    const $sharedGeoModal = $('#shareGeoModal');

    // Código Postal (CP)
    const $showPostalCode = $('#showPostalCode');
    const $inputCP = $('#inputCodePostal');

    // Contenedor de resultados (lista de tiendas)
    const $storesWrap = $('#content-search-stores');

    // Contenedor de productos (best sellers)
    const $prodContainer = $('#slb_best_sellers_shortcode');

    // URL BASE
    const urlPage = window.location;

    // UI timings
    const DEBOUNCE_MS = 600;
    const DURATION = 300;
    const EASING = 'swing';


    /** ======================================================================
     *  ESTADO / CACHES (nada de DOM ni AJAX aquí)
     *  ==================================================================== */
    let running = false;            // guard animación de la banda
    let statesCache = null;         // lista de estados
    const storesCache = new Map();  // stateId -> stores[]
    let lastQueryIdCP = 0;          // para descartar respuestas obsoletas (CP)
    const storeCacheByCP = new Map(); // cp -> stores[]
    const storeCacheByGeo = new Map(); // "lat,lng" -> stores[]

    /** ======================================================================
     *  HELPERS GENERALES (utilidades puras, sin efectos secundarios)
     *  ==================================================================== */
    const debounce = (fn, wait) => {
        let t;
        return (...args) => { clearTimeout(t); t = setTimeout(() => fn.apply(null, args), wait); };
    };

    const toRad = (d) => d * Math.PI / 180;
    const toNum = (v) => { const n = Number(v); return Number.isFinite(n) ? n : null; };

    // Texto o value según tag
    const setText = ($el, value) => {
        $el.is('input,textarea,select') ? $el.val(value ?? '') : $el.text(value ?? '');
    };

    // Sanitiza texto simple para evitar XSS
    const esc = (str) => String(str ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');

    // URL REST
    const restUrl = (endpoint) => API_ROOT + '/' + API_NS + '/' + String(endpoint).replace(/^\/+/, '');

    // Formatea distancia (km → "1,2 km" o "250 m")
    const formatDistance = (km) => {
        if (km == null || isNaN(km)) return '';
        const n = Number(km);
        if (n < 1) return Math.round(n * 1000).toLocaleString('es-MX') + ' m';
        return n.toLocaleString('es-MX', { minimumFractionDigits: 1, maximumFractionDigits: 2 }) + ' km';
    };

    // Href Google Maps
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

    /** ======================================================================
     *  COOKIES / ESTADO PERSISTENTE (lectura/escritura cookies)
     *  ==================================================================== */
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
    // Escapado para Atributo (sirve también para texto)
    const attrEsc = (v) => String(v ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');

    /** ======================================================================
     *  REST / API (solo peticiones y normalización de errores)
     *  ==================================================================== */
    function apiGetStoresByCoords(lat, lng) {
        return $.ajax({
            url: restUrl('get-stores-by-coordinates'),
            method: 'GET',
            dataType: 'json',
            cache: false,
            timeout: 15000,
            data: { lat, lng }
        }).then((json) => {
            if (json && json.code && json.message) {
                return $.Deferred().reject({ responseJSON: json }).promise();
            }
            return json;
        });
    }

    function apiGetStoreByPostal(cp) {
        // Normaliza: solo dígitos, a 5 chars
        let cleaned = String(cp ?? '').trim().replace(/\D+/g, '');
        if (!cleaned) {
            return $.Deferred().reject({ responseJSON: { code: 'invalid_cp', message: 'cp requerido' } }).promise();
        }
        cleaned = cleaned.slice(0, 5).padStart(5, '0');

        return $.ajax({
            url: restUrl('get-store-by-postal'),
            method: 'GET',
            dataType: 'json',
            cache: false,
            timeout: 15000,
            data: { cp: cleaned }
        }).then((json) => {
            if (json && json.code && json.message) {
                return $.Deferred().reject({ responseJSON: json }).promise();
            }
            return json;
        });
    }

    /** ======================================================================
     *  RENDER UTILS (utilidades de UI compartidas por módulos)
     *  ==================================================================== */
    function uiSetStoresLoading(text) {
        if (!$storesWrap.length) return;
        $storesWrap.addClass('stores-list').html(
            `<div class="stores-status"><span class="spinner"></span><span>${text || 'Cargando tiendas…'}</span></div>`
        );
    }

    function uiSetStoresMessage(text) {
        if (!$storesWrap.length) return;
        $storesWrap.addClass('stores-list').html(`<div class="stores-status">${text || 'Sin resultados'}</div>`);
    }

    /** ======================================================================
     *  MÓDULO: DETALLES DE TIENDA (“Entrega en: …”)
     *  - Lógica y renderización del bloque de detalles
     *  ==================================================================== */
    function detailsStoreRender() {
        const data = readSelectedStoreCookie();

        // Sin tienda → limpia UI
        if (!data) {
            setText($street, ''); setText($route, ''); setText($cpText, ''); setText($locality, '');
            $href.removeAttr('href').text('').hide();
            $msgDelivery.hide(); $msgNoGeo.hide();
            return;
        }

        const name = data.name ?? '';
        const street = data.street_number ?? '';
        const route = data.route ?? '';
        const cpVal = data.postal_code ?? '';
        const loc = data.locality ?? '';
        const href = data.url || buildMapsHref(data);

        setText($storeName, name || 'Tienda Desconocida');

        const nbsp = '\u00A0';
        setText($street, street);
        setText($route, route ? ` ${route},` : '');
        setText($cpText, cpVal ? ` C.P.${nbsp}${cpVal}` : '');
        setText($locality, loc ? ` ${loc}` : '');

        if (href) {
            $href.attr('href', href).text('Ver en Google Maps').show();
        } else {
            $href.removeAttr('href').text('').hide();
        }

        GEO_render(); // depende de tienda seleccionada
    }

    /** ======================================================================
     *  MÓDULO: LISTADO / SELECCIÓN POR ESTADO
     *  - Lógica para cargar estados, cargar tiendas por estado y seleccionar tienda
     *  ==================================================================== */
    function ST_initStates() {
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
    }

    function ST_renderStoresDropdown(stores) {
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
            const term_id = this.value;
            if (!term_id || isNaN(+term_id)) return;

            const $sel = $(this).find('option:selected');
            const storeData = {
                name: $sel.data('name') || '',
                street_number: $sel.data('street-number') || '',
                route: $sel.data('route') || '',
                locality: $sel.data('locality') || '',
                postal_code: $sel.data('postal-code') || '',
                lat: $sel.data('lat') || '',
                lng: $sel.data('lng') || ''
            };

            if (DEBUG) {
                console.log('ST → seleccionar tienda term_id=', val);
                console.table(storeData);
            }

            Helper.setCookie?.('wcmlim_selected_location_json', JSON.stringify(storeData), 30);
            Helper.setCookie?.('wcmlim_selected_location_termid', term_id, 30);
            Helper.setCookie?.('wcmlim_selected_location_group_termid', $state.val(), 30);
            // Refresca UI
            detailsStoreRender();
            // Cierra modal si aplica
            (bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl)).hide();
            // Limpiamos la list-stores, en caso haya algo
            $storesWrap.html('');
            $storesWrap.removeClass('stores-list');
            // Oculta input CP si estaba visible
            $inputCP.hide();

            //  Llamamos a obtener los productos best seller
            if (Helper?.getProductsBestSeller && Helper?.isHomePage(urlPage)) {
                Helper.getProductsBestSeller(term_id).then((data) => {
                    Helper.renderProductsList($prodContainer, data.products);
                });
            }
        });
    }

    function ST_loadStores(stateId) {
        if (!stateId) return;
        if ($store.length) $store.html('<option value="" disabled selected>Cargando…</option>');

        if (storesCache.has(stateId)) return ST_renderStoresDropdown(storesCache.get(stateId));
        if (!Helper.getStoresByState) return ST_renderStoresDropdown([]);

        Helper.getStoresByState(stateId).then((stores) => {
            const list = Array.isArray(stores) ? stores : [];
            // si el cache tiene más de 10 items, elimina el ultimo
            if (storesCache.size >= 10) {
                const keys = Array.from(storesCache.keys());
                storesCache.delete(keys[0]);
            }
            storesCache.set(stateId, list);
            ST_renderStoresDropdown(list);
        });
    }

    function ST_bind() {
        // Cambio de estado → carga sucursales
        $state.off('change' + NS).on('change' + NS, function () {
            const stateId = String(this.value || '');
            if (!stateId) return;
            ST_loadStores(stateId);
        });
    }

    /** ======================================================================
     *  MÓDULO: GEO / COBERTURA
     *  - Distancia, render de mensajes, compartir ubicación, tiendas cercanas
     *  ==================================================================== */
    function GEO_haversineMeters(lat1, lng1, lat2, lng2) {
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

    function GEO_isWithinKm(latUser, lngUser, latStore, lngStore, kmLimit = 8) {
        const m = GEO_haversineMeters(latUser, lngUser, latStore, lngStore);
        if (m == null) return { ok: false, meters: null, km: null };
        const km = m / 1000;
        return { ok: km <= kmLimit, meters: m, km };
    }

    function GEO_render() {
        const geo = readGeoCookie();              // { lat, lng, accuracy }
        const store = readSelectedStoreCookie();  // { lat, lng, ... }

        if (!store || !Number(store.lat) || !Number(store.lng)) {
            $msgDelivery.show().text('Servicio a domicilio no disponible.');
            $msgNoGeo.hide();
            return;
        }

        if (!geo || !Number(geo.lat) || !Number(geo.lng)) {
            $msgDelivery.hide();
            $msgNoGeo.show();
            return;
        }

        const res = GEO_isWithinKm(geo.lat, geo.lng, store.lat, store.lng, 8);
        $msgNoGeo.hide();

        if (res.meters == null) {
            $msgDelivery.show().text('No se pudo calcular la distancia.');
            return;
        }

        if (res.ok) {
            $msgDelivery.show().text('Servicio adomicilio disponible');
        } else {
            $msgDelivery.show().text(`Ups, estás a ${Helper.fmtKm ? Helper.fmtKm(res.km) : res.km.toFixed(1)} km de la tienda. Nuestro envío llega hasta 8 km.`);
            const cookieMsg = Helper.getCookie?.('wcmlim_msg_type');
            if (!cookieMsg) Helper.setCookie?.('wcmlim_msg_type', 1, 30);
        }
    }

    function GEO_renderStoresNearby(stores) {
        if (!$storesWrap.length) return;

        $storesWrap.addClass('stores-list');

        if (!Array.isArray(stores) || !stores.length) {
            uiSetStoresMessage('No se encontraron tiendas.');
            return;
        }

        const html = stores.map((s) => {
            // Normaliza campos
            const name = s.name || 'Tienda';
            const state = s.state || '';
            const id_state = s.state_id || '';
            const locality = s.locality || '';
            const route = s.route || '';
            const streetNum = s.streetNumber || '';
            const postalCode = s.postalCode || '';
            const lat = s.lat || '';
            const lng = s.lng || '';
            const slug = s.slug || '';
            const termId = s.term_id ?? '';
            const distanceKm = s.distance_km ?? null;

            const distTxt = distanceKm != null ? formatDistance(distanceKm) : '';
            const chipDist = distTxt ? `<span class="store-item__dist">${esc(distTxt)}</span>` : '';

            return `
                    <div class="store-item"
                        data-term-id="${attrEsc(termId)}"
                        data-name="${attrEsc(name)}"
                        data-state="${attrEsc(state)}"
                        data-locality="${attrEsc(locality)}"
                        data-route="${attrEsc(route)}"
                        data-street-number="${attrEsc(streetNum)}"
                        data-postal-code="${attrEsc(postalCode)}"
                        data-lat="${attrEsc(lat)}"
                        data-lng="${attrEsc(lng)}"
                        data-distance-km="${attrEsc(distanceKm)}"
                        data-slug="${attrEsc(slug)}"
                        role="button" tabindex="0">
                        <div class="store-item__left">
                        <div class="store-item__state">${esc(state)}</div>
                        <div class="store-item__head">
                            <div class="store-item__name" data-id-state="${attrEsc(id_state)}">${esc(name)}</div>${chipDist}
                        </div>
                        <div class="store-item__meta">
                            <div class="store-item__meta-line">
                            <span style="font-weight:600;color:#374151;">Ruta:</span> ${esc(route)}
                            &nbsp;•&nbsp;
                            <span style="font-weight:600;color:#374151;">Ciudad:</span> ${esc(locality)}
                            </div>
                            <div class="store-item__meta-line">
                            <span style="font-weight:600;color:#374151;">Dirección:</span> ${esc(streetNum)}
                            </div>
                        </div>
                        </div>
                        <div class="store-item__right"></div>
                    </div>`;
        }).join('');

        $storesWrap.html(html);

        $storesWrap.off('click.storeItem').on('click.storeItem', '.store-item', function () {
            const $item = $(this);
            const ds = $item.data();

            const term_id = ds.termId ?? $item.attr('data-term-id') ?? '';
            const id_state = ds.stateId ?? $item.attr('data-id-state') ?? '';
            const storeData = {
                name: ds.name || '',
                street_number: ds.streetNumber || '',
                route: ds.route || '',
                locality: ds.locality || '',
                postal_code: ds.postalCode || '',
                lat: ds.lat || '',
                lng: ds.lng || '',
            };
            if (DEBUG) {
                console.log('GEO → seleccionar tienda term_id=', term_id);
                console.table(storeData);
            }

            // Persistir selección
            Helper.setCookie?.('wcmlim_selected_location_json', JSON.stringify(storeData), 30);
            Helper.setCookie?.('wcmlim_selected_location_termid', term_id, 30);
            if (id_state) {
                Helper.setCookie?.('wcmlim_selected_location_group_termid', id_state, 30);
            }

            // Refrescar UI
            detailsStoreRender();
            (bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl)).hide();
            $storesWrap.empty().removeClass('stores-list');
            $inputCP.hide();

            // Cargamos los productos best seller
            if (Helper?.getProductsBestSeller && Helper?.isHomePage(urlPage)) {
                Helper.getProductsBestSeller(term_id).then((data) => {
                    Helper.renderProductsList($prodContainer, data.products);
                });
            }
        });
    }

    function GEO_bind() {
        // Botón "Compartir ubicación" (bloque principal)
        $btnShareGeo.off('click' + NS).on('click' + NS, async function (e) {
            e.preventDefault();
            await Helper.doLocate?.($(this)); // espera a que termine
            GEO_render();
        });

        // Acción de compartir ubicación, vía modal/listado
        $sharedGeoModal.off('click' + NS).on('click' + NS, async function (e) {
            e.preventDefault();
            const $btn = $(this).prop('disabled', true).addClass('is-busy');

            try {
                uiSetStoresLoading('Obteniendo tu ubicación…');
                await Helper.doLocate?.($btn);
                const geo = readGeoCookie();
                if (!geo || !geo.lat || !geo.lng) {
                    uiSetStoresMessage('No pudimos obtener tus coordenadas. Revisa permisos de ubicación.');
                    return;
                }
                uiSetStoresLoading('Buscando tiendas cercanas…');
                // verificamos caché
                if (storeCacheByGeo.has(`${geo.lat},${geo.lng}`)) {
                    GEO_renderStoresNearby(storeCacheByGeo.get(`${geo.lat},${geo.lng}`));
                    return;
                }
                const res = await apiGetStoresByCoords(geo.lat, geo.lng);
                if (res?.ok && Array.isArray(res.stores)) {
                    // si el store cache, pasa de 5, eliminamos el ultimo indice, y agregamos el nuevo
                    const key = `${geo.lat},${geo.lng}`;
                    if (storeCacheByGeo.size >= 5) {
                        const keys = Array.from(storeCacheByGeo.keys());
                        storeCacheByGeo.delete(keys[0]);
                    }
                    storeCacheByGeo.set(key, res.stores);
                    GEO_renderStoresNearby(res.stores);
                    if (DEBUG) console.log('Tiendas cercanas:', res.stores);
                } else {
                    uiSetStoresMessage('No encontramos tiendas cercanas en este momento.');
                }
            } catch (err) {
                const msg = err?.responseJSON?.message || err?.statusText || 'No pudimos consultar las tiendas cercanas.';
                uiSetStoresMessage(msg);
            } finally {
                $btn.prop('disabled', false).removeClass('is-busy');
            }
        });
    }

    /** ======================================================================
     *  MÓDULO: CÓDIGO POSTAL (CP)
     *  - Lógica + render listado por CP + bindings del input
     *  ==================================================================== */
    const CP_read = () => String(($inputCP.val() || '')).trim();
    const CP_isValid = (cp) => /^\d{5}$/.test(cp);

    function CP_renderList(stores) {
        if (!$storesWrap.length) return;

        $storesWrap.addClass('stores-list');

        if (!Array.isArray(stores) || !stores.length) {
            uiSetStoresMessage('No se encontró una tienda con ese C.P.');
            return;
        }

        const html = stores.map((s) => {
            // Normaliza campos desde la API
            const termId = s.term_id ?? s.id ?? '';
            const name = s.store_name || s.name || s.title || 'Tienda';
            const state = s.state || s.group_name || s.region || '';
            const stateId = s.state_id || s.group_term_id || '';
            const locality = s.locality || s.city || s.meta?.wcmlim_locality || '';
            const route = s.route || s.meta?.wcmlim_route || '';
            const streetNum = s.streetNumber || s.address || s.meta?.wcmlim_street_number || '';
            const postalCode = s.postalCode || s.cp || s.meta?.wcmlim_postal_code || '';
            const lat = s.lat || s.meta?.wcmlim_lat || '';
            const lng = s.lng || s.meta?.wcmlim_lng || '';
            const distKm = s.distance_km ?? s.km ?? null;

            const distTxt = distKm != null ? formatDistance(distKm) : '';
            const chipDist = distTxt ? `<span class="store-item__dist">${esc(distTxt)}</span>` : '';

            return `
                <div class="store-item"
                    data-term-id="${esc(termId)}"
                    data-name="${esc(name)}"
                    data-state="${esc(state)}"
                    data-state-id="${esc(stateId)}"
                    data-route="${esc(route)}"
                    data-locality="${esc(locality)}"
                    data-street-number="${esc(streetNum)}"
                    data-postal-code="${esc(postalCode)}"
                    data-lat="${esc(lat)}"
                    data-lng="${esc(lng)}"
                    data-distance-km="${esc(distKm ?? '')}"
                    role="button" tabindex="0">
                    <div class="store-item__left">
                    <div class="store-item__state">${esc(state)}</div>
                    <div class="store-item__head">
                        <div class="store-item__name">${esc(name)}</div>${chipDist}
                    </div>
                    <div class="store-item__meta">
                        <div class="store-item__meta-line">
                        <span style="font-weight:600;color:#374151;">Ruta:</span> ${esc(route)}
                        &nbsp;•&nbsp;
                        <span style="font-weight:600;color:#374151;">Ciudad:</span> ${esc(locality)}
                        </div>
                        <div class="store-item__meta-line">
                        <span style="font-weight:600;color:#374151;">Dirección:</span> ${esc(streetNum)}
                        </div>
                    </div>
                    </div>
                    <div class="store-item__right"></div>
                </div>`;
        }).join('');

        $storesWrap.html(html);

        // Click en tarjeta → seleccionar tienda
        $storesWrap.off('click.storeItemCP').on('click.storeItemCP', '.store-item', function () {
            const $item = $(this);
            const termId = String($item.data('term-id') || '');
            const stateId = String($item.data('state-id') || '');

            const storeData = {
                name: $item.data('name') || '',
                street_number: $item.data('street-number') || '',
                route: $item.data('route') || '',
                locality: $item.data('locality') || '',
                postal_code: $item.data('postal-code') || '',
                lat: $item.data('lat') || '',
                lng: $item.data('lng') || ''
            };

            if (DEBUG) {
                console.log('CP → seleccionar tienda term_id=', termId, 'state_id=', stateId);
                console.table(storeData);
            }
            // Persistir selección
            Helper.setCookie?.('wcmlim_selected_location_json', JSON.stringify(storeData), 30);
            Helper.setCookie?.('wcmlim_selected_location_termid', termId, 30);
            if (stateId) {
                Helper.setCookie?.('wcmlim_selected_location_group_termid', stateId, 30);
            }

            // Refrescar UI y cerrar modal
            detailsStoreRender();
            (bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl)).hide();

            // Limpiar listado, ocultar input de CP
            $storesWrap.empty().removeClass('stores-list');
            $inputCP.hide().removeClass('invalid');

            //  Llamamos a obtener los productos best seller
            if (Helper?.getProductsBestSeller && Helper?.isHomePage(urlPage)) {
                Helper.getProductsBestSeller(termId).then((data) => {
                    Helper.renderProductsList($prodContainer, data.products);
                });
            }
        });
    }

    async function CP_runSearch(cp) {
        const queryId = ++lastQueryIdCP;
        uiSetStoresLoading('Buscando tienda');
        // verificamos caché
        if (storeCacheByCP.has(cp)) {
            CP_renderList(storeCacheByCP.get(cp));
            return;
        }
        try {
            const res = await apiGetStoreByPostal(cp);

            // Evita race conditions con búsquedas viejas
            if (queryId !== lastQueryIdCP) return;

            if (res?.ok && Array.isArray(res.store) && res.store.length) {
                // si el store cache, pasa de 10, eliminamos el ultimo indice, y agregamos el nuevo
                if (storeCacheByCP.size >= 10) {
                    const keys = Array.from(storeCacheByCP.keys());
                    storeCacheByCP.delete(keys[0]);
                }
                storeCacheByCP.set(cp, res.store);
                CP_renderList(res.store);
            } else {
                CP_renderList([]); // pinta “sin resultados”
            }
        } catch (err) {
            if (queryId !== lastQueryIdCP) return;
            const msg = err?.responseJSON?.message || err?.statusText ||
                'No se ha podido buscar la tienda con el código postal';
            uiSetStoresMessage(msg);
        }
    }

    function CP_runSearchFromInput() {
        const raw = CP_read();
        if (!raw) {
            $inputCP.removeClass('invalid');
            $inputCP.next('.error-msg').hide();
            return;
        }

        if (!CP_isValid(raw)) {
            $inputCP.addClass('invalid');
            $inputCP.next('.error-msg').show().focus();
            return;
        }
        $inputCP.removeClass('invalid');
        $inputCP.next('.error-msg').hide();
        CP_runSearch(raw);
    }

    function CP_bind() {
        // Toggle del input de CP
        $inputCP.hide();
        $showPostalCode.off('click' + NS).on('click' + NS, function (e) {
            e.preventDefault();
            $inputCP.val('');
            $inputCP.removeClass('invalid');
            $inputCP.toggle();
            if ($inputCP.is(':visible')) {
                $inputCP.trigger('focus');
            }
        });

        // Debounce al escribir
        const debouncedCPRunSearch = debounce(CP_runSearchFromInput, DEBOUNCE_MS);
        $inputCP.off('input' + NS).on('input' + NS, debouncedCPRunSearch);

        // Enter para buscar
        $inputCP.off('keydown' + NS).on('keydown' + NS, function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                CP_runSearchFromInput();
            }
        });
    }

    /** ======================================================================
     *  MÓDULO: UI CONTENEDORA (banda azul, modal)
     *  - Solo comportamiento de contenedores generales
     *  ==================================================================== */
    function UI_bindModal() {
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
    }

    function UI_bindBand() {
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
    }

    /** ======================================================================
     *  INIT (orquesta todo)
     *  ==================================================================== */
    function init() {
        if (!$modal.length) return;
        if ($modal.data('wcmlim-init')) return;
        $modal.data('wcmlim-init', true);

        // Render inicial
        detailsStoreRender();
        ST_initStates();

        // Bindings por módulo
        UI_bindModal();
        UI_bindBand();
        ST_bind();
        GEO_bind();
        CP_bind();
    }
    // DOM ready
    $(init);
})(jQuery, window);