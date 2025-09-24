<?php

function mostrar_icono_carrito()
{
    if (class_exists('WooCommerce')) {
        ob_start();
        ?>
        <button class="cart-contents" id="ctaOpenSideCarrito" aria-label="Carrito de compras">
            <i class="bi bi-cart"></i>
            <span class="cart-contents-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
        </button>
        <?php
        return ob_get_clean();
    }
}
add_shortcode('icono_carrito', 'mostrar_icono_carrito');

add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    ob_start();
    ?>
    <span class="cart-contents-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
    <?php
    $fragments['.cart-contents-count'] = ob_get_clean();
    return $fragments;
});

function mostrar_carrito_lateral_con_imagen()
{
    ob_start();
    ?>
    <div class="site__sidebar-carrito" id="barraCarrito">
        <div class="side__sidebar-carrito-wrapper">
            <div class="side__sidebar-carrito-header">
                <button class="site__sidebar-cta-cerrar-carrito" id="ctaCloseBarCarrito" aria-label="Cerrar"><i
                        class="bi bi-x"></i></button>
                <p>Tu carrito</p>
            </div>
            <div class="site__sidebar-carrito-listado">
                <?php
                if (WC()->cart->get_cart_contents_count() > 0) {
                    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                        $_product = $cart_item['data'];
                        $product_name = $_product->get_name();
                        $quantity = $cart_item['quantity'];
                        $price = wc_price($_product->get_price());
                        $product_permalink = $_product->is_visible() ? $_product->get_permalink() : '';
                        $product_image = $_product->get_image('thumbnail');
                        echo '<div class="site__sidebar-carrito-item">';
                        echo '<figure class="side__sidebar-carrito-figure">';
                        echo '<a href="' . esc_url($product_permalink) . '">' . $product_image . '</a>';
                        echo '</figure>';
                        echo '<div class="site__sidebar-carrito-detalles">';
                        echo '<a class="side__sidebar-carrito-titulo" href="' . esc_url($product_permalink) . '">' . esc_html($product_name) . '</a>';
                        echo '<div class="site__sidebar-carrito-precios">';
                        echo ' x ' . $quantity . ' - ' . $price;
                        echo '</div>';
                        echo '</div>';
                        echo '<a href="#" class="eliminar-producto" data-cart-item-key="' . esc_attr($cart_item_key) . '"><i class="bi bi-x-circle-fill"></i></a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>Tu carrito está vacío.</p>';
                }
                ?>
            </div>
            <div class="side__sidebar-carrito-costo-total">
                <strong>Total:</strong> <span><?php echo WC()->cart->get_cart_total(); ?></span>
            </div>
            <div class="side__sidebar-carrito-botonera">
                <a class="side__sidebar-carrito-btn-ir" href="<?php echo wc_get_cart_url(); ?>"><span>Ver carrito</span></a>
                <a class="side__sidebar-carrito-pago" href="<?php echo wc_get_checkout_url(); ?>"><span>Ir a
                        pagar</span></a>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('carrito_lateral', 'mostrar_carrito_lateral_con_imagen');

add_action('wp_ajax_remove_cart_item', 'eliminar_producto_del_carrito');
add_action('wp_ajax_nopriv_remove_cart_item', 'eliminar_producto_del_carrito');

function eliminar_producto_del_carrito()
{
    if (isset($_POST['cart_item_key'])) {
        $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
        if (WC()->cart->remove_cart_item($cart_item_key)) {
            wp_send_json_success(['message' => 'Producto eliminado correctamente.']);
        } else {
            wp_send_json_error(['message' => 'No se pudo eliminar el producto.']);
        }
    } else {
        wp_send_json_error(['message' => 'Falta la clave del producto.']);
    }
    wp_die();
}

add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    ob_start();
    echo do_shortcode('[carrito_lateral]');
    $fragments['.site__sidebar-carrito'] = ob_get_clean();
    ob_start();
    ?>
    <span><?php echo WC()->cart->get_cart_total(); ?></span>
    <?php
    $fragments['.side__sidebar-carrito-costo-total span'] = ob_get_clean();
    return $fragments;
});


add_action('woocommerce_before_shop_loop', function () {
    $selected = $_COOKIE['wcmlim_selected_location_termid'] ?? '';
    if (!empty($selected) && $selected !== "undefined")
        return;
    echo '<div class="cm-store-required" style="
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
            display: flex;
            align-items: center;
            gap: 16px;
            color: white;
            position: relative;
            overflow: hidden;
        ">
            <div class="cm-sr-icon" style="
                font-size: 24px;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 50%;
                width: 48px;
                height: 48px;
                display: flex;
                align-items: center;
                justify-content: center;
                backdrop-filter: blur(10px);
            " aria-hidden="true">📍</div>
            <div class="cm-sr-text" style="flex: 1;">
                <strong style="
                    display: block;
                    font-size: 18px;
                    font-weight: 600;
                    margin-bottom: 4px;
                ">Selecciona tu tienda</strong>
                <span style="
                    font-size: 14px;
                    opacity: 0.9;
                    line-height: 1.4;
                ">Así verás precios y disponibilidad cercanos a ti</span>
            </div>
            <div style="
                position: absolute;
                top: -50%;
                right: -20px;
                width: 100px;
                height: 100px;
                background: rgba(255, 255, 255, 0.1);
                border-radius: 50%;
                z-index: 0;
            "></div>
          </div>';
}, 5);


add_action('woocommerce_after_shop_loop_item', 'personalizar_botones_cantidad', 15);
function personalizar_botones_cantidad()
{
    global $product;
    if ($product->is_type('simple')) {
        // Verificar si hay tienda seleccionada
        $selected_location = isset($_COOKIE['wcmlim_selected_location_termid']) ? $_COOKIE['wcmlim_selected_location_termid'] : '';

        if (empty($selected_location)) {
            return;
        }

        // Obtener valores dinámicos del postmeta
        $product_step = get_post_meta($product->get_id(), 'product_step', true);
        $min_quantity = get_post_meta($product->get_id(), 'min_quantity', true);

        // Valores por defecto si no están definidos
        $step = !empty($product_step) ? $product_step : 1;
        $min = !empty($min_quantity) ? $min_quantity : 1;

        echo '<div class="quantity-wrapper">';
        echo '<button class="quantity-decrease">-</button>';
        echo '<input type="number" class="quantity-input" value="' . esc_attr($min) . '" min="' . esc_attr($min) . '" step="' . esc_attr($step) . '" aria-label="Cantidad">';
        echo '<button class="quantity-increase">+</button>';
        echo '<button class="add-to-cart" data-product-id="' . esc_attr($product->get_id()) . '">Añadir al carrito</button>';
        echo '</div>';
    }
}

remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);

add_action('wp_ajax_remove_cart_item', 'remove_cart_item_ajax_handler');
add_action('wp_ajax_nopriv_remove_cart_item', 'remove_cart_item_ajax_handler');

function remove_cart_item_ajax_handler()
{
    if (!isset($_POST['cart_item_key'])) {
        wp_send_json_error(['message' => 'La clave del producto no está definida.']);
        return;
    }
    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    if (WC()->cart->remove_cart_item($cart_item_key)) {
        wp_send_json_success(['message' => 'Producto eliminado correctamente.']);
    } else {
        wp_send_json_error(['message' => 'No se pudo eliminar el producto del carrito.']);
    }
}

add_action('wp_ajax_get_cart_total', 'custom_get_cart_total');
add_action('wp_ajax_nopriv_get_cart_total', 'custom_get_cart_total');

function custom_get_cart_total()
{
    if (!WC()->cart) {
        wp_send_json_error('El carrito no está disponible.');
        return;
    }
    $total = WC()->cart->get_total();
    wp_send_json_success(['total' => $total]);
}

add_filter('acf/load_field/name=listado_cat_check', function ($field) {
    $args = array(
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
    );
    $product_categories = get_terms($args);
    $field['choices'] = array();
    if (!empty($product_categories) && !is_wp_error($product_categories)) {
        foreach ($product_categories as $category) {
            $field['choices'][$category->term_id] = $category->name;
        }
    }
    return $field;
});

remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
remove_action('woocommerce_shop_loop_header', 'woocommerce_product_taxonomy_archive_header', 10);
