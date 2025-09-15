(function (window, $) {
    'use strict';

    var L = window.multi_inventory || {};
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

    // API global
    window.WCMLIM = window.WCMLIM || {};
    window.WCMLIM.getStates = getStates;
    window.WCMLIM.getStoresByState = getStoresByState;
    window.WCMLIM.tryOpenSelect = function (el) { tryOpenSelect($(el)); };

})(window, jQuery);