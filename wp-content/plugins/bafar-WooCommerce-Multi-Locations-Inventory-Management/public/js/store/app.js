(function ($, window) {
    'use strict';

    const NS = '.wcmlim';
    const Helper = window.WCMLIM || {};

    $(function () {
        const $modal = $('#modalSeleccionTienda');
        if (!$modal.length) return;

        // 🛡️ idempotencia: no re-inicializar si ya corrió
        if ($modal.data('wcmlim-init')) return;
        $modal.data('wcmlim-init', true);

        const modalEl = $modal[0];
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

        const $state = $('#selectState');
        const $store = $('#selectStore');
        const $band = $('.buscador-contenido');

        // ====== Apertura del modal por JS (no Data-API) ======
        $('#btnAbrirTienda')
            .off('click' + NS)
            .on('click' + NS, (e) => {
                e.preventDefault();
                if (!$modal.hasClass('show')) modal.show();
            });

        // Sincroniza banda azul con el ciclo del modal (evita animaciones peleándose)
        $modal
            .off('show.bs.modal' + NS + ' shown.bs.modal' + NS + ' hide.bs.modal' + NS + ' hidden.bs.modal' + NS)
            .on('show.bs.modal' + NS, () => $band.stop(true, true))
            .on('shown.bs.modal' + NS, () => $band.stop(true, true).slideUp(200))
            .on('hide.bs.modal' + NS, () => $band.stop(true, true))
            .on('hidden.bs.modal' + NS, () => $band.stop(true, true).slideDown(200));

        // ====== Data helpers ======
        const buildOptionsHTML = (items, valueKey, textKey, placeholder) => {
            let html = `<option value="" disabled selected>${placeholder}</option>`;
            for (const it of items) {
                const v = it?.[valueKey] ?? '';
                const t = it?.[textKey] ?? '';
                html += `<option value="${String(v)}">${String(t)}</option>`;
            }
            return html;
        };

        // caches simples para evitar refetch
        let statesCache = null;
        const storesCache = new Map(); // stateId -> stores[]

        // ====== Init estados ======
        const initStates = () => {
            if (!$state.length) return;

            if (statesCache) {
                $state.html(buildOptionsHTML(statesCache, 'term_id', 'group_name', 'Selecciona estado'));
                return;
            }

            if (!Helper.getStates) {
                $state.html('<option value="" disabled selected>Sin estados</option>');
                return;
            }

            Helper.getStates().then((states) => {
                if (Array.isArray(states) && states.length) {
                    statesCache = states;
                    $state.html(buildOptionsHTML(states, 'term_id', 'group_name', 'Selecciona estado'));
                } else {
                    $state.html('<option value="" disabled selected>Sin estados</option>');
                }
            });
        };

        // ====== Cargar sucursales por estado ======
        const loadStores = (stateId) => {
            if (!stateId) return;
            if ($store.length) $store.html('<option value="" disabled selected>Cargando…</option>');

            if (storesCache.has(stateId)) {
                renderStores(storesCache.get(stateId));
                return;
            }

            if (!Helper.getStoresByState) {
                renderStores([]);
                return;
            }

            Helper.getStoresByState(stateId).then((stores) => {
                const list = Array.isArray(stores) ? stores : [];
                storesCache.set(stateId, list);
                renderStores(list);
            });
        };

        // ====== Render de sucursales + cierre del modal al elegir ======
        const renderStores = (stores) => {
            if (!$store.length) return;

            if (!stores.length) {
                $store.html('<option value="" disabled selected>Sin sucursales</option>');
                return;
            }

            let html = '<option value="" disabled selected>Selecciona sucursal</option>';
            for (const s of stores) {
                html += `<option value="${String(s.id)}">${String(s.name)}</option>`;
            }
            $store.html(html);

            // intentar abrir el select (si el navegador lo permite)
            if (Helper.tryOpenSelect) Helper.tryOpenSelect($store);

            // bind único de change
            $store.off('change' + NS).on('change' + NS, function () {
                const val = this.value;
                if (!val || isNaN(+val)) return;
                (bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl)).hide();
            });
        };

        // ====== Eventos ======
        $state.off('change' + NS).on('change' + NS, function () {
            const stateId = String(this.value || '');
            if (!stateId) return;
            loadStores(stateId);
        });

        const $btn = $('#btnBuscadorTienda');
        const $panel = $('.buscador-contenido');
        const $icon = $('.icon-down');

        const DURATION = 220;     // 180–220 ms se siente fluido
        const EASING = 'swing'; // mejor que 'linear'

        let running = false;

        $btn.off('click.wcmlim').on('click.wcmlim', (e) => {
            e.preventDefault();

            if (running || $panel.is(':animated')) return;
            running = true;

            // estado final esperado (abrir si ahora está oculto)
            const willOpen = !$panel.is(':visible');

            // bloqueo visual opcional
            $btn.addClass('is-busy').css('pointer-events', 'none');

            // gira el icono hacia el estado FINAL mientras corre la animación
            $icon.toggleClass('giro', willOpen);

            // anima panel
            $panel
                .stop(true, false)                // no encolas; no saltas al final
                .slideToggle(DURATION, EASING)
                .promise()
                .done(() => {
                    running = false;
                    $btn.removeClass('is-busy').css('pointer-events', '');
                });
        });

        // run
        initStates();
    });
})(jQuery, window);