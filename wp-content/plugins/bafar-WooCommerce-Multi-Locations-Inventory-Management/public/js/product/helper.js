(function (window, $) {
    'use strict';

    // ===== Config / Endpoint =====
    const L = window.multi_inventory_product || {};
    const endpoint = (L && L.api && L.api.endpoint) ? L.api.endpoint : '';

    /**
     * Obtiene productos best seller o normales según el storeId.
     * @param {number} storeId
     * @param {object} options - { perPage, page, orderby, order }
     * @returns {jqXHR|Promise} Respuesta de la API
     */
    function getProductsBestSeller(storeId = 0, options = {}) {
        const {
            perPage = 20,
            page = 1,
            orderby = 'date',
            order = 'DESC'
        } = options;

        const params = {
            idStore: storeId,
            per_page: perPage,
            page,
            orderby,
            order
        };

        if (storeId > 0) {
            params.top_sellers = true;
        }

        if (!endpoint) {
            console.warn('[WCMLIM] Endpoint no definido en multi_inventory_product.api.endpoint');
            // Devuelve un Deferred resuelto para no romper el flujo
            return $.Deferred().resolve({ data: [] }).promise();
        }

        return $.ajax({
            url: endpoint,   // <-- corregido
            method: 'GET',
            data: params,
            dataType: 'json'
        });
    }

    /**
     * Pinta <li> dentro de un <ul class="products columns-X"> ya existente.
     * Replica las clases/estructura del loop de WooCommerce.
     * @param {jQuery} $ul - UL existente (p.ej. $('#slb_best_sellers_shortcode'))
     * @param {Array} products - Array de productos
     */
    function renderProductsIntoList($ul, products) {
        if (!$ul || !$ul.length) return;

        // Detecta columnas desde 'columns-X'
        const m = ($ul.attr('class') || '').match(/columns-(\d+)/);
        const columns = m ? parseInt(m[1], 10) : 4;

        // Utilidad para slugs cuando solo viene el nombre
        const slugify = s => String(s || '')
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');

        $ul.empty();

        const list = Array.isArray(products) ? products : [];
        if (!list.length) {
            $ul.append('<li class="product no-products">No products found.</li>');
            return;
        }

        list.forEach((p, i) => {
            const liClasses = [
                'product',
                'type-product',
                p.id ? `post-${p.id}` : null,
                'status-publish',
                (i % columns === 0) ? 'first' : ((i % columns === columns - 1) ? 'last' : null),
                (p.stock_status === 'outofstock') ? 'outofstock' : 'instock',
                // Categorías y tags
                ...(Array.isArray(p.categories) ? p.categories.map(c => `product_cat-${c.slug || slugify(c.name || c)}`) : []),
                ...(Array.isArray(p.tags) ? p.tags.map(t => `product_tag-${t.slug || slugify(t.name || t)}`) : []),
                (p.image ? 'has-post-thumbnail' : null),
                (p.shipping_taxable === false ? null : 'shipping-taxable'),
                (p.purchasable === false ? null : 'purchasable'),
                `product-type-${p.type || 'simple'}`
            ].filter(Boolean);

            const $li = $('<li/>', { class: liClasses.join(' ') });

            const $a = $('<a/>', {
                href: p.permalink || '#',
                class: 'woocommerce-LoopProduct-link woocommerce-loop-product__link'
            });

            const $img = $('<img/>', {
                src: p.image || '',
                class: 'wp-post-image wp-post-image',
                alt: p.image_alt || '',
                loading: 'lazy',
                decoding: 'async'
            });

            if (p.srcset) $img.attr('srcset', p.srcset);
            if (p.sizes) $img.attr('sizes', p.sizes);
            if (!p.sizes && p.srcset) {
                $img.attr('sizes', '(max-width:480px) 45vw, (max-width:768px) 30vw, 183px');
            }

            const $title = $('<h2/>', {
                class: 'woocommerce-loop-product__title',
                text: p.name || ''
            });

            const currencySymbol = (typeof p.currency_symbol === 'string') ? p.currency_symbol : '$';
            const priceHtml = (typeof p.price_html === 'string' && p.price_html.trim())
                ? p.price_html
                : `<span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">${currencySymbol}</span>${p.price ?? ''}</bdi></span>`;

            const $price = $('<span/>', { class: 'price', html: priceHtml });
            const $flag = $('<div/>', { class: 'flag-producto-card' });

            $a.append($img, $title, $price, $flag);

            const $qty = $(`
                <div class="quantity-wrapper">
                    <button class="quantity-decrease">-</button>
                    <input type="number" class="quantity-input" value="1" min="1" aria-label="Cantidad">
                    <button class="quantity-increase">+</button>
                    <button class="add-to-cart" data-product-id="${p.id || ''}">Añadir al carrito</button>
                </div>
            `);

            $li.append($a, $qty);
            $ul.append($li);
        });
    }

    // ===== Util =====
    function isHomePage(urlObj) {
        const url = urlObj || window.location;
        return (
            url.hostname === 'carnemart-performance.local' &&
            (url.pathname === '/' || url.pathname === '')
        );
    }

    // ===== API Global =====
    window.WCMLIM = window.WCMLIM || {};
    window.WCMLIMProducts = window.WCMLIM.Products || {};
    window.WCMLIM.getProductsBestSeller = getProductsBestSeller;
    window.WCMLIM.renderProductsList = renderProductsIntoList;
    window.WCMLIM.isHomePage = isHomePage;
})(window, jQuery);