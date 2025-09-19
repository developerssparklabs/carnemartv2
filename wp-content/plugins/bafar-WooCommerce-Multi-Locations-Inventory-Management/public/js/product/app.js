(function ($, window) {
    'use strict';
    const HelperProducts = window.WCMLIM || {};
    function initProductsByPage(type) {
        switch (type) {
            case 'best_sellers':
                const term_id_store = HelperProducts.getCookie('wcmlim_selected_location_termid') ?? 0;
                const $container = $('#slb_best_sellers_shortcode');
                HelperProducts.getProductsBestSeller(term_id_store)
                    .then((response) => {
                        HelperProducts.renderProductsList($container, response.products);
                    });
                break;
            default:
                break;
        }
    }
    /** ======================================================================
     *  INIT (orquesta todo)
     *  ==================================================================== */
    function init() {
        const urlPage = window.location;
        if (HelperProducts.isHomePage(urlPage)) {
            initProductsByPage('best_sellers');
        }
    }
    // DOM ready
    $(init);
}(jQuery, window));