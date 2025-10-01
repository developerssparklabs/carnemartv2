<?php

/**
 *
 * 
 *   ██████  ██▓███   ▄▄▄       ██▀███   ██ ▄█▀    ██▓  ██████     ██░ ██ ▓█████  ██▀███  ▓█████ 
 * ▒██    ▒ ▓██░  ██▒▒████▄    ▓██ ▒ ██▒ ██▄█▒    ▓██▒▒██    ▒    ▓██░ ██▒▓█   ▀ ▓██ ▒ ██▒▓█   ▀ 
 * ░ ▓██▄   ▓██░ ██▓▒▒██  ▀█▄  ▓██ ░▄█ ▒▓███▄░    ▒██▒░ ▓██▄      ▒██▀▀██░▒███   ▓██ ░▄█ ▒▒███   
 *   ▒   ██▒▒██▄█▓▒ ▒░██▄▄▄▄██ ▒██▀▀█▄  ▓██ █▄    ░██░  ▒   ██▒   ░▓█ ░██ ▒▓█  ▄ ▒██▀▀█▄  ▒▓█  ▄ 
 * ▒██████▒▒▒██▒ ░  ░ ▓█   ▓██▒░██▓ ▒██▒▒██▒ █▄   ░██░▒██████▒▒   ░▓█▒░██▓░▒████▒░██▓ ▒██▒░▒████▒
 * ▒ ▒▓▒ ▒ ░▒▓▒░ ░  ░ ▒▒   ▓▒█░░ ▒▓ ░▒▓░▒ ▒▒ ▓▒   ░▓  ▒ ▒▓▒ ▒ ░    ▒ ░░▒░▒░░ ▒░ ░░ ▒▓ ░▒▓░░░ ▒░ ░
 * ░ ░▒  ░ ░░▒ ░       ▒   ▒▒ ░  ░▒ ░ ▒░░ ░▒ ▒░    ▒ ░░ ░▒  ░ ░    ▒ ░▒░ ░ ░ ░  ░  ░▒ ░ ▒░ ░ ░  ░
 * ░  ░  ░  ░░         ░   ▒     ░░   ░ ░ ░░ ░     ▒ ░░  ░  ░      ░  ░░ ░   ░     ░░   ░    ░   
 *       ░                 ░  ░   ░     ░  ░       ░        ░      ░  ░  ░   ░  ░   ░        ░  ░
 *
 * Incluímos el archivo de funciones para extender multilocations
 */

require_once get_stylesheet_directory() . '/includes/multilocations-extended.php';

require_once get_stylesheet_directory() . '/includes/empty-cart.php';

require_once get_stylesheet_directory() . '/includes/account-extended.php';
//Widgets

//Widgets
add_action('widgets_init', 'new_widget_area');

function new_widget_area()
{
    register_sidebar(array(
        'name' => 'Filtros Woo',
        'id' => 'woo-bloques',
        'class' => 'woo-bloques',
    ));
}

//add_action('wp_footer', 'forzar_recarga_sin_cache');
function forzar_recarga_sin_cache()
{
    echo '
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const yaRecargado = localStorage.getItem("recarga_forzada");

        // Si no se ha recargado y no estamos ya en ?nocache y no estamos en la URL específica
        if (!yaRecargado && !window.location.href.includes("nocache=") && !window.location.href.includes("mi-cuenta/lost-password")) {
            localStorage.setItem("recarga_forzada", "1");

            // Forzar recarga sin cache (con parámetro único)
            const nuevaURL = window.location.href.split("?")[0] + "?nocache=" + Date.now();
            window.location.replace(nuevaURL); // Reemplaza sin guardar en historial
        }
    });
    </script>
    ';
}

function woocommerce_product_tag_filter2($atts)
{
    $atts = shortcode_atts(array(
        'ismenu' => 'false',
    ), $atts, 'menus_giro_negocio');

    $isMenu = filter_var($atts['ismenu'], FILTER_VALIDATE_BOOLEAN);

    // Traer tags
    $tags = get_terms(array(
        'taxonomy' => 'product_tag',
        'hide_empty' => true,
        'orderby' => 'name',
        'order' => 'ASC',
    ));

    if (empty($tags) || is_wp_error($tags)) {
        return '<p>Sin etiquetas</p>';
    }

    // Contenedor <ul> con clases
    $ul_class = $isMenu ? 'product-tags-filter-menu' : 'product-tags-filter';
    $ul_class .= ' wp-cat-check-list';

    $output = '<ul class="' . esc_attr($ul_class) . '">';

    $shown = []; // para evitar duplicados (por slug)

    foreach ($tags as $tag) {
        // Soporte a nombres con comas (tu caso)
        $names = array_map('trim', explode(',', $tag->name));

        foreach ($names as $name) {
            if ($name === '')
                continue;

            // Buscar el término real por nombre
            $term_obj = get_term_by('name', $name, 'product_tag');
            if (!$term_obj || is_wp_error($term_obj)) {
                continue;
            }

            $slug = $term_obj->slug;
            if (in_array($slug, $shown, true)) {
                continue; // ya renderizado
            }

            $term_id = (int) $term_obj->term_id;
            $input_id = 'tag-check-' . $term_id;
            $label_txt = $term_obj->name;

            // IMPORTANTE: clase "tag-filter-checkbox" y name "tag_p_filter[]"
            $output .= "<li class='cat-item cat-item-{$term_id} wp-cat-check-item'>";
            $output .= "<label class='wp-cat-check-label' for='" . esc_attr($input_id) . "'>";
            $output .= "<input type='checkbox' id='" . esc_attr($input_id) . "' class='tag-filter-checkbox' name='tag_p_filter[]' value='" . esc_attr($term_id) . "' data-slug='" . esc_attr($slug) . "'>";
            $output .= "<span class='cat-filter-text' style='margin-left:10px;'>" . esc_html($label_txt) . "</span>";
            $output .= "</label>";
            $output .= "</li>\n";

            $shown[] = $slug;
        }
    }

    $output .= '</ul>';

    return $output;
}

add_shortcode('menus_giro_negocio', 'woocommerce_product_tag_filter2');

function add_canonical_tag()
{
    if (is_singular()) {
        global $post;
        $canonical_url = get_permalink($post->ID);
        echo '<link rel="canonical" href="' . esc_url($canonical_url) . '" />' . "\n";
    } elseif (is_home() || is_archive() || is_category() || is_tag()) {
        echo '<link rel="canonical" href="' . esc_url(get_pagenum_link()) . '" />' . "\n";
    }
}
add_action('wp_head', 'add_canonical_tag');


/*
    // Log: Inicio del proceso
    error_log('Inicio del proceso de actualización de ri_quantity_step');

    $args = [
        'post_type' => 'product',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ];

    $products = get_posts($args);

    // Log: Número de productos encontrados
    error_log('Productos encontrados: ' . count($products));

    foreach ($products as $product_id) {
        // Log: Procesando producto
        error_log('Procesando producto ID: ' . $product_id);

        $qty_step = get_post_meta($product_id, 'ri_quantity_step', true); // Obtener el valor de ri_quantity_step
        error_log('Valor actual de ri_quantity_step: ' . ($qty_step ?: 'no definido'));

        $product_step = get_post_meta($product_id, 'product_step', true);
        error_log('Valor actual de product_step: ' . ($product_step ?: 'no definido'));

        $min_quantity = get_post_meta($product_id, 'min_quantity', true);
        error_log('Valor actual de min_quantity: ' . ($min_quantity ?: 'no definido'));

        if (empty($product_step) || empty($min_quantity)) {
            // Si faltan valores, actualizarlos con el valor de ri_quantity_step
            update_post_meta($product_id, 'product_step', $qty_step);
            error_log('Actualizado product_step a: ' . $qty_step);

            update_post_meta($product_id, 'min_quantity', $qty_step);
            error_log('Actualizado min_quantity a: ' . $qty_step);
        } else {
            // Log: No se requiere actualización
            error_log('No se requiere actualización para el producto ID: ' . $product_id);
        }
    }

    // Log: Fin del proceso
    error_log('Fin del proceso de actualización de ri_quantity_step');
*/

// Añadir botones de más y menos en cualquier campo de cantidad en todo el sitio
function custom_quantity_buttons_global()
{
    if (is_product()) { // Verificar si estamos en una página de producto
        global $product;
        $existeSucursal = sucural_presente();
        $qty_step = apply_filters('woocommerce_quantity_input_step', 1, $product);
        $quantity_step_label = get_post_meta($product->get_id(), 'ri_quantity_step_label', true);
        // Obtener el ID del producto actual
        $product_id = $product->get_id();
        // Obtener la cantidad en el carrito
        $cantidad_en_carrito = cantidad_producto_carrito($product_id);
        $stock_producto = obtener_stock_por_sucursal($product_id);

        // obtener el sku
        $sku = $product->get_sku();

        $price = floatval($product->get_regular_price());

        $term_id = isset($_COOKIE['wcmlim_selected_location_termid'])
            ? intval($_COOKIE['wcmlim_selected_location_termid'])
            : 0;
        if (!$term_id) {
            // Si no hay tienda, no mostramos nada
            return '';
        }

        if ($term_id) {
            // fallback a precio por tienda
            $price = floatval(get_post_meta($product->get_id(), "wcmlim_regular_price_at_{$term_id}", true));
            $cg = intval(get_term_meta($term_id, 'customer_group', true));
            $json_key = "eib2bpro_price_tiers_group_{$cg}";
            $tiers_json = get_post_meta($product->get_id(), $json_key, true);
            $tiers_arr = $tiers_json ? json_decode($tiers_json, true) : [];
            $tier_price = (is_array($tiers_arr) && !empty($tiers_arr))
                ? floatval(reset($tiers_arr))
                : null;
        } else {
            $tier_price = null;
        }


        // Encontrado producto con promocion
        $isBoleanFoundPromotion = false;

        $isLimiteMax = false;
        // $descuentos = obtener_configuracion_descuentos();

        // if (isset($descuentos[$sku])) {
        //     $isBoleanFoundPromotion = true;
        //     $config = $descuentos[$sku];
        //     $precio_final = $config['precio_final'];
        //     $limite = $config['limite'];

        //     if (($cantidad_en_carrito + $qty_step) >= $limite) {
        //         $isLimiteMax = true;
        //     }
        // }
        ?>
        <style>
            #globMsg>b {
                margin-right: 0.5em;
            }

            /* Ocultar flechas en Chrome, Safari, Edge, Opera */
            input[type="number"]::-webkit-inner-spin-button,
            input[type="number"]::-webkit-outer-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }

            /* Ocultar flechas en Firefox */
            input[type="number"] {
                -moz-appearance: textfield;
            }
        </style>
        <script>
            var isBooleanFoundPromotion = <?php echo json_encode($isBoleanFoundPromotion); ?>;
            var limite = <?php echo json_encode($limite ?? 0); ?>;
            var isLimiteMax = <?php echo json_encode($isLimiteMax); ?>;
            var price_general = <?php echo json_encode($price); ?>;
            var primer_precio_escalado = <?php echo json_encode($tier_price); ?>;

            function showSpecialPriceNotice() {
                // Verificamos si ya existe el mensaje antes de agregarlo
                if (jQuery('.wrapper-table-price').length && jQuery('.oferton-message').length === 0) {
                    jQuery('.wrapper-table-price').before(
                        '<div class="oferton-message" style="background-color: #ffe066; color: #4d3f00; border-left: 6px solid #ffb300; padding: 12px 20px; border-radius: 6px; margin-bottom: 15px; font-weight: 700; font-size: 17px; text-align: center; width: 80%">' +
                        'Precio especial. No aplican descuentos por volumen.' +
                        '</div>'
                    );
                }
            }

            function existOferton() {
                const priceElements = document.querySelectorAll('.price.wcmlim_product_price .woocommerce-Price-amount');
                if (priceElements.length === 2) return true;
                return false;
            }

            // globMsg b

        </script>
        <?php if ($stock_producto != 0 && $stock_producto != $cantidad_en_carrito): ?>
            <style>
                @keyframes shimmer {
                    0% {
                        background-position: -200% 0;
                    }

                    100% {
                        background-position: 200% 0;
                    }
                }

                .cart-quantity-notice {
                    display: flex;
                    align-items: center;
                    background: #f0f8f5;
                    /* muy suave */
                    border-left: 4px solid #0a8a2a;
                    /* tu verde de marca */
                    border-radius: 4px;
                    padding: 10px 16px;
                    margin: 16px 0;
                    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
                    font-size: 1rem;
                    color: #031b6d;
                    /* tu azul oscuro */
                }

                .cart-quantity-icon {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    color: #fff;
                    border-radius: 50%;
                    width: 24px;
                    height: 24px;
                    margin-right: 12px;
                    font-size: 1.1rem;
                }

                .cart-quantity-text p {
                    margin: 0;
                    line-height: 1.2;
                }

                .cart-quantity-text strong {
                    color: #0052cc;
                }
            </style>
            <script type="text/javascript">
                jQuery(window).on('load', function () {
                    jQuery(document).ready(function ($) {

                        var unidad = '<?php echo $quantity_step_label; ?>';
                        var totalCarrito = <?php echo $cantidad_en_carrito; ?>;
                        var stockReal = <?php echo json_encode($stock_producto); ?>;
                        var existSucursal = <?php echo $existeSucursal; ?>;

                        if (["Pza.", "Pzas."].includes(unidad)) {
                            unidad = totalCarrito >= 1.5 ? "Pzas." : "Pza.";
                        } else if (["Kg.", "Kgs."].includes(unidad)) {
                            unidad = totalCarrito >= 1.5 ? "Kgs." : "Kg.";
                        }

                        $("#tipo-unidad").text(unidad);

                        function redondearDecimal(num, decimales = 2) {
                            return Number(Math.round(num + 'e' + decimales) + 'e-' + decimales);
                        }

                        function verifylimit(limit, qtyCart) {
                            if (qtyCart > limit && limit != 0) {
                                return [false, 'exceeded'];
                            }

                            if (qtyCart == limit && limit != 0) {
                                mostrarAlerta(`No puedes comprar más de este producto.<br>El producto solo se puede comprar en múltiplos de <strong>${limit} ${unidad}</strong> y actualmente el inventario restante no permite agregar otra unidad.<br>Ya tienes <strong>${qtyCart} ${unidad}</strong> en tu carrito y agregar <strong>${limit}</strong> más superaría el inventario disponible.`, 1);
                                return [false, 'limit_stock'];
                            }

                            return [true, 'within_limit'];
                        }

                        $(document).on('click', '.plus, .minus', function () {

                            var $qty = $(this).siblings('.qty');
                            let currentVal = parseFloat($qty.val());
                            let max = $qty.attr('max') ? parseFloat($qty.attr('max')) : 9999;
                            let min = $qty.attr('min') ? parseFloat($qty.attr('min')) : 0;
                            let step = $qty.attr('step') ? parseFloat($qty.attr('step')) : 1;

                            let newVal = currentVal;

                            if ($(this).hasClass('plus')) {
                                if (!max || currentVal < max) {
                                    newVal = redondearDecimal(currentVal + step);
                                    $qty.val(newVal).change();
                                }
                            } else {
                                if (!min || currentVal > min) {
                                    newVal = redondearDecimal(currentVal - step);
                                    $qty.val(newVal).change();
                                }
                            }

                            if (unidad == "Pza." || unidad == "Pzas.") {
                                unidad = totalCarrito >= 1.5 ? "Pzas." : "Pza.";
                            } else if (unidad == "Kg." || unidad == "Kgs.") {
                                unidad = totalCarrito >= 1.5 ? "Kgs." : "Kg.";
                            }

                            updateTotalWeight($qty);
                        });

                        $('div.quantity').each(function () {
                            if ($(this).find('.minus').length === 0) {
                                $(this).find('input[type="number"]').before('<button type="button" class="minus">-</button>');
                            }
                            if ($(this).find('.plus').length === 0) {
                                $(this).find('input[type="number"]').after('<button type="button" class="plus">+</button>');
                            }
                        });

                        function updateTotalWeight($qtyTag = null) {
                            let statusOferton = existOferton();
                            let quantityCart = <?php echo $cantidad_en_carrito ?>;
                            let inventoryProduct = <?php echo $stock_producto ?>;

                            let quantity = getQuantityFromInput();
                            let priceValue = getPriceValue();
                            let priceElement = getPriceElement(priceValue);
                            // ⚠️ Nueva validación de límite de cantidad
                            let step = parseFloat(document.querySelector('.qty').getAttribute('step')) || 1;

                            // if (!isBooleanFoundPromotion) {
                            //     const [isValid, reason] = validateQuantityLimit(quantityCart, inventoryProduct, step);
                            //     if (!isValid) {
                            //         if (reason === 'nothing') {
                            //             return;
                            //         }
                            //         if (reason !== 'continue') {
                            //             quantity = 0;
                            //         }
                            //     }
                            // }

                            quantity = adjustQuantity(quantity, quantityCart);
                            //checkInventoryAndDisableButton(quantityCart, inventoryProduct);
                            updateMaxQuantity(quantityCart, inventoryProduct);

                            // if (isBooleanFoundPromotion) {
                            //     const [isValid, reason] = verifylimit(limite, quantity);
                            //     if (isLimiteMax) {

                            //         if (reason == 'limit_stock') {
                            //             quantity = quantity;
                            //             mostrarAlerta(
                            //                 `Has alcanzado el límite de <strong>${limite} ${unidad}</strong>, que también coincide con el stock disponible en esta tienda.<br>
                //                 Solo puedes realizar una última adición antes de completar el máximo permitido. Por favor, continúa para finalizar tu compra.`,
                            //                 1
                            //             );
                            //         } else {
                            //             quantity = quantityCart;
                            //         }

                            //     } else {
                            //         if (!isValid) {
                            //             let currentVal = $qtyTag.val();

                            //             if (reason == 'limit_stock') {
                            //                 quantity = quantity;
                            //                 $qtyTag.val(currentVal);
                            //                 mostrarAlerta(
                            //                     `Has alcanzado el límite de <strong>${limite} ${unidad}</strong>, que coincide con el stock disponible para esta tienda.<br>
                //                     Ya no puedes agregar más unidades a tu carrito para este producto.`,
                            //                     1
                            //                 );
                            //             } else if (reason == 'exceeded') {
                            //                 quantity = quantity - step;
                            //                 $qtyTag.val(currentVal - step);
                            //                 mostrarAlerta(`Has superado el límite de ${limite} unidades. Se ha ajustado la cantidad a ${currentVal - step} ${unidad}`, 1);
                            //             }

                            //             document.querySelector('button.plus').disabled = true;
                            //         } else {
                            //             document.querySelector('button.plus').disabled = false;
                            //             ocultarAlerta();
                            //         }
                            //     }
                            // }

                            // Se elimino ya que no queremos que bloquee la logica del escalado
                            //if (!statusOferton) {
                            priceValue = updatePriceBasedOnQuantity(quantity, priceValue, priceElement);

                            //}

                            // if (isBooleanFoundPromotion) {
                            //     // Lógica adicional si se encontró una promoción
                            //     priceValue = <?php echo json_encode($precio_final ?? 0); ?>;
                            // }

                            updateTotalDisplay(quantity, priceValue, priceElement, quantityCart);
                        }

                        function getQuantityFromInput() {
                            return parseFloat($('.qty').val()) || 0;
                        }

                        function getPriceValue() {
                            const priceElements = document.querySelectorAll('.price.wcmlim_product_price .woocommerce-Price-amount');
                            let priceElement = priceElements.length === 2 ? priceElements[1] : priceElements[0];

                            const priceText = priceElement ? priceElement.innerText : null;
                            return priceText ? parseFloat(priceText.replace('$', '').replace(',', '')) : 0;
                        }

                        function getPriceElement(priceValue) {
                            const priceElements = document.querySelectorAll('.price.wcmlim_product_price .woocommerce-Price-amount');
                            let priceElement = priceElements.length === 2 ? priceElements[1] : priceElements[0];

                            if (priceElement && !priceElement.hasAttribute("data-price")) {
                                priceElement.setAttribute("data-price", priceValue.toFixed(2));
                            }

                            return priceElement;
                        }

                        function adjustQuantity(quantity, quantityCart) {
                            if (!isNaN(quantityCart) && quantityCart > 0) {
                                quantity += quantityCart;
                            }
                            return quantity;
                        }

                        // function checkInventoryAndDisableButton(quantityCart, inventoryProduct) {
                        //     if (quantityCart == inventoryProduct) {
                        //         // $(".single_add_to_cart_button").prop("disabled", true).css("opacity", "0.5");
                        //     }
                        // }

                        function updateMaxQuantity(quantityCart, inventoryProduct) {
                            const totalWeightElement = document.getElementById("totalWeight");

                            if (totalWeightElement) {
                                const quantityContainer = totalWeightElement.nextElementSibling;

                                if (quantityContainer && quantityContainer.classList.contains("quantity")) {
                                    const quantityInput = quantityContainer.querySelector("input[type='number']");

                                    if (quantityInput) {
                                        let maxInput = inventoryProduct - quantityCart;

                                        if (maxInput != 0) {
                                            maxInput = Math.round(maxInput * 100) / 100;
                                            quantityInput.setAttribute("max", maxInput);
                                        } else {
                                            quantityInput.setAttribute("max", 0);
                                            quantityInput.value = 0;
                                        }
                                    }
                                }
                            }
                        }

                        function updatePriceBasedOnQuantity(quantity, priceValue, priceElement) {
                            const wrapper = document.querySelector('.wrapper-table-price');
                            if (!wrapper) return priceValue;

                            // 1) Recojo y convierto en array
                            const tiers = Object.values(gatherPricingData(wrapper));

                            // 2) Ordeno de mayor a menor según el umbral mínimo
                            tiers.sort((a, b) => b.quantityRange[0] - a.quantityRange[0]);

                            // 3) Recorro y me detengo en la primera que encaje
                            let found = false;
                            for (const item of tiers) {
                                const [min, max] = item.quantityRange;
                                if (quantity >= min && (max === undefined || quantity <= max)) {
                                    priceValue = item.price;
                                    found = true;
                                    break;  // ¡importante!
                                }
                            }

                            // 4) Si ninguno encaja, uso el precio base
                            if (!found) {
                                priceValue = parseFloat(priceElement.getAttribute("data-price"));
                            }
                            return priceValue;
                        }


                        function gatherPricingData(quantityFather) {
                            let pricingData = {};
                            const quantityTable = quantityFather.querySelector('.eib2bpro_price_tiers_table');
                            const tbody = quantityTable ? quantityTable.querySelector('tbody') : null;

                            if (tbody) {
                                const rows = tbody.querySelectorAll('tr');

                                rows.forEach((row, index) => {
                                    const cells = row.querySelectorAll('td');
                                    if (cells.length >= 2) {
                                        const quantityText = cells[0].textContent.trim();
                                        const quantityRange = quantityText.split('-').map(num => parseInt(num.trim()));

                                        const priceText = cells[1].querySelector('.woocommerce-Price-amount bdi').textContent.trim();
                                        const price = parseFloat(priceText.replace('$', '').trim());

                                        pricingData[index] = {
                                            quantityRange,
                                            price
                                        };
                                    }
                                });
                            }

                            return pricingData;
                        }

                        function updateTotalDisplay(quantity, priceValue, priceElement, quantityCart) {
                            var totalWeight = quantity.toFixed(2);

                            if (["Pza.", "Pzas."].includes(unidad)) {
                                unidad = totalWeight >= 1.5 ? "Pzas." : "Pza.";
                            } else if (["Kg.", "Kgs."].includes(unidad)) {
                                unidad = totalWeight >= 1.5 ? "Kgs." : "Kg.";
                            }

                            var totalPagar = priceValue * quantity;
                            var formattedPrice = totalPagar ? `$${totalPagar.toFixed(2)}` : '0';

                            $('#totalWeight').text(`Total a pedir ${totalWeight} ${unidad}`);
                            $('#totalPagar').text(`Total por pagar ${formattedPrice}`);

                            priceElement.innerText = `$${priceValue.toFixed(2)}`;
                        }

                        function validateQuantityLimit(quantityCart, inventoryProduct, step) {
                            const quantityInput = document.querySelector('.qty');
                            let currentInput = parseFloat(quantityInput.value);

                            if (isNaN(currentInput)) return;

                            let total = quantityCart + currentInput;
                            let restante = inventoryProduct - quantityCart;

                            // Calcular el último múltiplo de step permitido
                            let maxPermitidoReal = Math.floor(restante / step) * step;

                            if (total == inventoryProduct) {
                                if (quantityCart > 0) {
                                    mostrarAlerta(`Límite de disponibilidad alcanzado.<br>Puedes agregar hasta ${maxPermitidoReal.toFixed(2)} ${unidad} (ya tienes ${quantityCart} en el carrito)`);
                                } else {
                                    mostrarAlerta(`Límite de disponibilidad alcanzado.<br>Puedes agregar hasta ${maxPermitidoReal.toFixed(2)} ${unidad}`);
                                }
                                return [false, 'continue'];
                            } else {
                                ocultarAlerta();
                            }

                            if (total > inventoryProduct || restante < step) {
                                quantityInput.value = maxPermitidoReal.toFixed(2);
                                if (maxPermitidoReal == 0) {
                                    mostrarAlerta(`
                                                        No puedes comprar más de este producto.<br>
                                                        El producto solo se puede comprar en múltiplos de <strong>${step.toFixed(2)} ${unidad}</strong> y 
                                                        actualmente el inventario restante no permite agregar otra unidad.<br>
                                                        Ya tienes <strong>${quantityCart.toFixed(2)} ${unidad}</strong> en tu carrito 
                                                        y agregar <strong>${step.toFixed(2)}</strong> más superaría el inventario disponible.
                                                    `, 1);
                                } else {
                                    if (total > inventoryProduct) {
                                        mostrarAlerta(`No puedes comprar más de lo permitido.<br>Máximo permitido: ${maxPermitidoReal.toFixed(2)} ${unidad} (${quantityCart} en tu carrito)`, 0);
                                        return [false, 'nothing'];
                                    }
                                }

                                $(".single_add_to_cart_button").prop("disabled", true).css("opacity", "0.5");
                                return [false, 'disabled'];
                            } else {

                                $(".single_add_to_cart_button").prop("disabled", false).css("opacity", "1");
                                return [true, 'enabled'];
                            }
                        }

                        let alertaTimeout;
                        let alertaActiva = false;

                        function mostrarAlerta(mensaje) {
                            // Siempre limpiamos el contenido anterior
                            if (alertaActiva) {
                                $('#text-max-quantity').html(mensaje);
                            } else {
                                $('#content-quantiy-max').html(
                                    `<div class="max-stock-warning" style="background-color: #FFF3CD; border-left: 5px solid #FFC107; padding: 15px; border-radius: 4px;">
                                                        <p style="color: #856404; text-align: center; margin-bottom: 15px;" id="text-max-quantity">${mensaje}</p>
                                                    </div>`
                                ).fadeIn(300);

                                alertaActiva = true;
                            }
                        }

                        // Oculta alerta manualmente
                        function ocultarAlerta() {
                            $('#content-quantiy-max').fadeOut(300, () => {
                                alertaActiva = false;
                            });
                        }

                        // Ejecutar cuando el DOM esté listo
                        jQuery(function ($) {
                            const $btnAddToCart = $('.single_add_to_cart_button.button.alt');
                            const $inputQty = $('input.qty');
                            const stock = parseFloat($('#globMsg b').text()); // Stock real desde el DOM

                            // Asegura que el contenedor esté en el lugar correcto
                            if (!$('#content-quantiy-max').length) {
                                $btnAddToCart.before('<div id="content-quantiy-max" style="display:none;"></div>');
                            }
                        });


                        function showEsqueleto() {
                            const target = document.querySelector('.price.wcmlim_product_price');
                            if (target && !document.getElementById('price-skeleton')) {
                                const skeleton = document.createElement('div');
                                skeleton.id = 'price-skeleton';
                                skeleton.style = `
                                    width: 120px;
                                    height: 30px;
                                    border-radius: 5px;
                                    background: linear-gradient(90deg, #eee 25%, #ddd 50%, #eee 75%);
                                    background-size: 200% 100%;
                                    animation: shimmer 1.5s infinite;
                                    margin-bottom: 10px;
                                `;
                                target.parentNode.insertBefore(skeleton, target);
                                target.style.visibility = 'hidden';
                            }
                        }

                        function showDetailsProduct() {
                            if (existSucursal && stockReal != 0) {
                                // Añadir Totales si aún no existen
                                if (!document.getElementById('totalWeight')) {
                                    $('div.quantity').before('<div id="totalWeight" style="font-weight:bold;margin-top:10px;color:green;font-size:23px;"></div>');
                                }
                                if (!document.getElementById('totalPagar')) {
                                    if (!isLimiteMax || totalCarrito != '0') {
                                        $('.price.wcmlim_product_price').after('<div id="totalPagar" style="font-weight:bold;margin-bottom:10px;font-size:26px;color:#031b6d;">Total a pagar: $0.00</div>');
                                    }
                                }
                                if (!$('#carrito_cantidad').length && totalCarrito > 0) {
                                    $('#totalPagar').after(
                                        `<div id="carrito_cantidad" class="cart-quantity-notice">
                                            <div class="cart-quantity-icon">
                                                <svg 
                                                class="e-font-icon-svg e-eicon-cart-medium" 
                                                viewBox="0 0 1000 1000" 
                                                xmlns="http://www.w3.org/2000/svg"
                                                style="width:100%; height:auto;"
                                                >
                                                    <path d="M740 854C740 883 763 906 792 906S844 883 844 854 820 802 792 802 740 825 740 854ZM217 156H958C977 156 992 173 989 191L957 452C950 509 901 552 843 552H297L303 581C311 625 350 656 395 656H875C892 656 906 670 906 687S892 719 875 719H394C320 719 255 666 241 593L141 94H42C25 94 10 80 10 62S25 31 42 31H167C182 31 195 42 198 56L217 156ZM230 219L284 490H843C869 490 891 470 895 444L923 219H230ZM677 854C677 791 728 740 792 740S906 791 906 854 855 969 792 969 677 918 677 854ZM260 854C260 791 312 740 375 740S490 791 490 854 438 969 375 969 260 918 260 854ZM323 854C323 883 346 906 375 906S427 883 427 854 404 802 375 802 323 825 323 854Z"></path>
                                                </svg>
                                            </div>
                                            <div class="cart-quantity-text">
                                                <p>Cantidad en carrito: <strong>${totalCarrito}</strong></p>
                                            </div>
                                        </div>`
                                    );
                                }

                                // let statusOferton = existOferton();
                                // if (statusOferton || isBooleanFoundPromotion) showSpecialPriceNotice();

                                // Verificamos si el contenedor existe
                                const $stockEl = $('#globMsg b');
                                if ($stockEl.length) {
                                    $stockEl.text(stockReal);
                                }
                            }
                        }

                        function observePriceChangeAndUpdate() {
                            const priceContainer = document.querySelector('.price.wcmlim_product_price');
                            if (!priceContainer) return;

                            // Observador de mutaciones para detectar el cambio real del precio
                            const observer = new MutationObserver((mutationsList) => {
                                for (const mutation of mutationsList) {
                                    if (mutation.type === 'childList' || mutation.type === 'characterData') {
                                        // Confirmamos que ya se haya actualizado el precio
                                        if (priceContainer.textContent.trim() !== '') {
                                            // Mostrar el precio
                                            // priceContainer.style.visibility = 'visible';

                                            // // Quitar el esqueleto si existe
                                            // const skeleton = document.getElementById('price-skeleton');
                                            // if (skeleton) skeleton.remove();


                                            // Actualizar total
                                            if (typeof updateTotalWeight === 'function') {
                                                if (primer_precio_escalado != null) {
                                                    if (primer_precio_escalado < price_general) {
                                                        var priceEl = document.querySelector('p.price.wcmlim_product_price');
                                                        if (!priceEl) return;

                                                        // Creamos el <del> con estilos inline
                                                        var del = document.createElement('del');
                                                        del.style.color = '#888';
                                                        del.style.marginRight = '0.5em';
                                                        del.style.fontSize = '1.45rem';   // tamaño aumentado
                                                        del.style.lineHeight = '1';
                                                        del.textContent = price_general.toLocaleString('es-MX', {
                                                            style: 'currency',
                                                            currency: 'MXN',
                                                            minimumFractionDigits: 2
                                                        });

                                                        // Insertamos el tachado antes del precio actual
                                                        priceEl.insertBefore(del, priceEl.firstChild);
                                                    }
                                                }
                                                showDetailsProduct();
                                                updateTotalWeight();
                                            }

                                            // Detener observador
                                            observer.disconnect();
                                        }
                                    }
                                }
                            });

                            observer.observe(priceContainer, {
                                childList: true,
                                characterData: true,
                                subtree: true
                            });

                            // Fallback de seguridad por si MutationObserver falla
                            // setTimeout(() => {
                            //     if (priceContainer.textContent.trim() !== '') {
                            //         priceContainer.style.visibility = 'visible';
                            //         const skeleton = document.getElementById('price-skeleton');
                            //         if (skeleton) skeleton.remove();
                            //         if (typeof updateTotalWeight === 'function') {
                            //             updateTotalWeight();
                            //         }
                            //         observer.disconnect();
                            //     }
                            // }, 3000); // máximo de espera 3 segundos
                        }

                        // showEsqueleto();
                        observePriceChangeAndUpdate();
                    });
                });
            </script>
        <?php else: ?>
            <style>
            </style>
        <?php endif; ?>
        <?php if ($stock_producto == $cantidad_en_carrito): ?>
            <style>
                @keyframes shimmer {
                    0% {
                        background-position: -200% 0;
                    }

                    100% {
                        background-position: 200% 0;
                    }
                }

                .cart-quantity-notice {
                    display: flex;
                    align-items: center;
                    background: #f0f8f5;
                    /* muy suave */
                    border-left: 4px solid #0a8a2a;
                    /* tu verde de marca */
                    border-radius: 4px;
                    padding: 10px 16px;
                    margin: 16px 0;
                    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
                    font-size: 1rem;
                    color: #031b6d;
                    /* tu azul oscuro */
                }

                .cart-quantity-icon {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    color: #fff;
                    border-radius: 50%;
                    width: 24px;
                    height: 24px;
                    margin-right: 12px;
                    font-size: 1.1rem;
                }

                .cart-quantity-text p {
                    margin: 0;
                    line-height: 1.2;
                }

                .cart-quantity-text strong {
                    color: #0052cc;
                }
            </style>
            <script>
                jQuery(window).on('load', function () {
                    jQuery(document).ready(function ($) {
                        var totalCarrito = <?php echo $cantidad_en_carrito; ?>;
                        var stockReal = <?php echo json_encode($stock_producto); ?>;
                        var existSucursal = <?php echo $existeSucursal; ?>;

                        function observePriceChangeAndUpdate() {
                            const interval = setInterval(() => {
                                const priceContainer = document.querySelector('.price.wcmlim_product_price');
                                if (!priceContainer) return;

                                // Mostrar precio y quitar skeleton
                                priceContainer.style.visibility = 'visible';
                                const skeleton = document.getElementById('price-skeleton');
                                if (skeleton) skeleton.remove();

                                if (existSucursal && stockReal != 0) {
                                    // Añadir Totales si aún no existen
                                    if (!$('#carrito_cantidad').length && totalCarrito > 0) {
                                        $('.price.wcmlim_product_price').after(`
                                            <div id="carrito_cantidad" class="cart-quantity-notice">
                                                <div class="cart-quantity-icon">
                                                    <svg 
                                                    class="e-font-icon-svg e-eicon-cart-medium" 
                                                    viewBox="0 0 1000 1000" 
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    style="width:100%; height:auto;"
                                                    >
                                                        <path d="M740 854C740 883 763 906 792 906S844 883 844 854 820 802 792 802 740 825 740 854ZM217 156H958C977 156 992 173 989 191L957 452C950 509 901 552 843 552H297L303 581C311 625 350 656 395 656H875C892 656 906 670 906 687S892 719 875 719H394C320 719 255 666 241 593L141 94H42C25 94 10 80 10 62S25 31 42 31H167C182 31 195 42 198 56L217 156ZM230 219L284 490H843C869 490 891 470 895 444L923 219H230ZM677 854C677 791 728 740 792 740S906 791 906 854 855 969 792 969 677 918 677 854ZM260 854C260 791 312 740 375 740S490 791 490 854 438 969 375 969 260 918 260 854ZM323 854C323 883 346 906 375 906S427 883 427 854 404 802 375 802 323 825 323 854Z"></path>
                                                    </svg>
                                                </div>
                                                <div class="cart-quantity-text">
                                                    <p>Cantidad en carrito: <strong>${totalCarrito}</strong></p>
                                                </div>
                                            </div>
                                        `);
                                    }

                                    let statusOferton = existOferton();

                                    if (statusOferton || isBooleanFoundPromotion) {

                                    }

                                    // Verificamos si el contenedor existe
                                    const $stockEl = $('#globMsg b');
                                    if ($stockEl.length) {
                                        $stockEl.text(stockReal);
                                    }
                                }

                                clearInterval(interval);
                            }, 200);
                        }

                        // Iniciar
                        observePriceChangeAndUpdate();
                    });
                });
            </script>
        <?php endif ?>
    <?php
    } else {
        ?>
        <?php if (!is_cart()): ?>
            <style>
                /* Ocultar flechas en Chrome, Safari, Edge, Opera */
                input[type="number"]::-webkit-inner-spin-button,
                input[type="number"]::-webkit-outer-spin-button {
                    -webkit-appearance: none;
                    margin: 0;
                }

                /* Ocultar flechas en Firefox */
                input[type="number"] {
                    -moz-appearance: textfield;
                }
            </style>
        <?php endif ?>
        <script>
            jQuery(document).on('click', '.plus, .minus', function () {

                // Encuentra el input de cantidad más cercano
                var $qty = jQuery(this).siblings('.qty');
                var currentVal = parseFloat($qty.val());
                var max = parseFloat($qty.attr('max'));
                var min = parseFloat($qty.attr('min'));
                var step = parseFloat($qty.attr('step'));

                // Incrementa o decrementa el valor según el botón presionado
                if (jQuery(this).hasClass('plus')) {
                    if (!max || currentVal < max) {
                        $qty.val(currentVal + step).change();
                    }
                } else {
                    if (!min || currentVal > min) {
                        $qty.val(currentVal - step).change();
                    }
                }

                // Actualiza el valor de currentVal después del cambio
                currentVal = parseFloat($qty.val());

                let container = this.closest(".quantity");
                let stepInput = container.querySelector("input[id^='quantity_step_']");
                let idProducto = container.querySelector("input[id^='id_producto_']");
                let unidad = container.querySelector("input[id^='unidad_']").value;
                let stepValue = parseFloat(stepInput.value.replace('&quot;', ''));

                updateTotalWeight();

                function updateTotalWeight() {
                    var quantity = parseFloat(stepValue);
                    var totalWeight = (quantity * currentVal).toFixed(2);

                    jQuery("#tipo-unidad").text(unidad)
                    jQuery("#totalWeight_quantity_" + idProducto.value).text("Total a pedir " + totalWeight + " " + unidad);

                }
            });
        </script>
        <?php
    }
}

// add_action('wp_footer', 'custom_quantity_buttons_global');

/**
 * Obtenemos la cantidad del producto en el carrito
 * @param mixed $product_id
 */
function cantidad_producto_carrito($product_id)
{

    // Obtener el carrito
    $cart = WC()->cart;

    // Recorrer los productos en el carrito
    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        // Verificar si el producto coincide con el ID que buscamos
        if ($cart_item['product_id'] == $product_id) {
            // Devolver la cantidad en el carrito
            return $cart_item['quantity'];
        }
    }

    // Si no se encuentra el producto, devolver 0
    return 0;
}

/**
 * Obtiene el stock disponible de un producto en la ubicación actual
 * 
 * @param int $product_id ID del producto
 * @return int Stock disponible en la ubicación actual
 */
function obtener_stock_por_sucursal($product_id)
{
    // Obtener el ID de la sucursal actual desde la cookie
    $sucursal_id = isset($_COOKIE['wcmlim_selected_location_termid']) ?
        intval($_COOKIE['wcmlim_selected_location_termid']) :
        0;

    if ($sucursal_id === 0) {
        return 0; // Si no hay sucursal seleccionada
    }

    // Obtener el stock para esta sucursal (puede ser decimal)
    $stock = get_post_meta($product_id, "wcmlim_stock_at_{$sucursal_id}", true);

    return ($stock !== '' && $stock !== false) ? (float) $stock : 0;
}

function get_locations_lists_front()
{
    $terms = get_terms(array(
        'taxonomy' => 'locations',
        'hide_empty' => false,
        'parent' => 0
    ));

    $result = [];

    if (!is_array($terms)) {
        return $result;
    }

    foreach ($terms as $term) {
        $centro_activo = get_term_meta($term->term_id, 'centro_activo', true);
        if ($centro_activo !== "1") {
            continue;
        }

        $term_meta = get_option("taxonomy_{$term->term_id}");

        if (!is_array($term_meta)) {
            $term_meta = []; // Si no es un array, inicializamos vacío
        }

        $term_meta = array_map(function ($value) {
            return is_array($value) ? '' : $value;
        }, $term_meta);

        $result[$term->term_id] = [
            'term_id' => $term->term_id,
            'location_address' => implode(" ", array_filter($term_meta)),
            'location_name' => $term->name,
        ];
    }

    return $result;
}


/**
 * Obtener ubicacion en base a la cookie
 */
function get_current_location()
{
    // Primero intentamos con la cookie nueva (termid)
    $location_id = isset($_COOKIE['wcmlim_selected_location_termid']) ?
        $_COOKIE['wcmlim_selected_location_termid'] :
        null;

    if (!$location_id) {
        return null;
    }

    $locations = get_locations_lists_front();

    // Buscar por term_id (nuevo método)
    if (isset($locations[$location_id])) {
        return $locations[$location_id];
    }

    // Compatibilidad hacia atrás: buscar en valores (antiguo método)
    foreach ($locations as $location) {
        if ($location['term_id'] == $location_id) {
            return $location;
        }
    }

    return null;
}

function get_locations_lists()
{
    $terms = get_terms([
        'taxonomy' => 'locations',
        'hide_empty' => false,
        'parent' => 0
    ]);
    $result = [];
    $i = 0;
    foreach ($terms as $term) {
        $centro_activo = get_term_meta($term->term_id, 'centro_activo', true);
        if ($centro_activo != "1") {
            //continue;
        }
        $term_meta = get_option("taxonomy_$term->term_id");
        if (!is_array($term_meta)) {
            $term_meta = [];
        }
        $term_meta = array_map(function ($value) {
            return !is_array($value) ? $value : '';
        }, $term_meta);
        $result[$i]['location_address'] = implode(" ", array_filter($term_meta));
        $result[$i]['location_name'] = $term->name;
        $i++;
    }
    return $result;
}

function get_current_location_back()
{
    $locations_list = get_locations_lists();
    $selected_key = $_COOKIE['wcmlim_selected_location'] ?? null;
    $select_location = $selected_key !== null ? ($locations_list[$selected_key] ?? null) : null;
    if (!$select_location) {
        return null;
    }
    return $select_location;
}

/**
 * Verifica la ubicación y muestra alertas si es necesario
 * @return array|false Retorna los datos de ubicación o false si hay error
 */
function get_verified_location()
{
    static $location_data = null; // Cache para evitar múltiples ejecuciones

    if ($location_data !== null) {
        return $location_data;
    }

    $location_new = get_current_location();
    $location_old = get_current_location_back();

    $has_error = (!$location_new || !$location_old) ||
        ($location_new['location_name'] !== $location_old['location_name']);

    $location_data = [
        'new' => $location_new,
        'old' => $location_old,
        'is_valid' => !$has_error
    ];

    if ($has_error) {
        // add_action('wp_footer', function () use ($location_new, $location_old) {
        //     echo '<script>
        //     document.addEventListener("DOMContentLoaded", function() {
        //         window.addEventListener("load", function() {
        //             ';

        //     if ($location_new && $location_old) {
        //        // echo 'alert("¡Atención! Hay discrepancia con tu ubicación:\\n\\n' .
        //        //     'Sistema nuevo: ' . esc_js($location_new['location_name']) . '\\n' .
        //          //   'Sistema anterior: ' . esc_js($location_old['location_name']) . '\\n\\n' .
        //            // 'Por favor selecciona nuevamente tu tienda para evitar errores.");';
        //     } else {
        //         // echo 'alert("¡Atención! No se detectó tu ubicación. '.
        //         //     'Por favor selecciona una tienda para continuar.");';
        //     }

        //     echo '
        //         });
        //     });
        //     </script>';
        // });
    }

    return $location_data;
}

// Verificación global de ubicación
add_action('wp', function () {
    get_verified_location();
});

function sucural_presente()
{
    $sucursal_id = isset($_COOKIE['wcmlim_selected_location_termid']) ?
        intval($_COOKIE['wcmlim_selected_location_termid']) :
        0;
    return $sucursal_id;
}


// cargar home_productos

add_action('rest_api_init', function () {
    register_rest_route('mi_tienda/v1', '/productos/', [
        'methods' => 'GET',
        'callback' => 'obtener_productos_json',
        'permission_callback' => '__return_true'
    ]);
});

function obtener_productos_json($data)
{
    $args = [
        'post_type' => 'product',
        'posts_per_page' => $data['total_products'] ?? 20, // Default 20 si no se especifica
        'post_status' => 'publish',
        'orderby' => 'rand',
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => '_stock_status',
                'value' => 'instock',
                'compare' => '='
            ],
            [
                'key' => '_stock',
                'value' => '1',
                'compare' => '>',
                'type' => 'NUMERIC'
            ],
            [
                'key' => 'product_step',
                'value' => '.',
                'compare' => 'NOT LIKE'
            ],
        ],
    ];

    // Añade condiciones adicionales si hay un término específico
    if (!empty($data['term_id'])) {
        $args['meta_query'][] = [
            'key' => "wcmlim_stock_at_{$data['term_id']}",
            'value' => '0',
            'compare' => '>',
            'type' => 'NUMERIC'
        ];
    }

    $loop = new WP_Query($args);
    $productos = [];

    if ($loop->have_posts()) {
        while ($loop->have_posts()) {
            $loop->the_post();
            global $product;
            $productos[] = [
                'id' => $product->get_id(),
                'nombre' => get_the_title(),
                'precio' => $product->get_price(),
                'imagen' => get_the_post_thumbnail_url($product->get_id(), 'medium')
            ];
        }
    }

    return new WP_REST_Response($productos, 200);
}

/* 
  ____                  _    
 / ___| _ __   __ _ _ __| | __
 \___ \| '_ \ / _` | '__| |/ /
  ___) | |_) | (_| | |  |   < 
 |____/| .__/ \__,_|_|  |_|\_\
       |_|                    

 * Scripts personalizados javacript
 */

// Agregar loader fullpage en WordPress
function add_controllable_loader()
{
    echo '
    <style>
    .fullpage-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,.6) !important
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        backdrop-filter: blur(1px);
    }
    
    .loader-spinner {
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    </style>
    
    <div id="fullpage-loader" class="fullpage-loader" style="display: none">
        <div class="loader-spinner"></div>
    </div>
    
    <script>
    // Funciones mejoradas con transiciones
    window.showLoader = function() {
        const loader = document.getElementById("fullpage-loader");
        loader.style.display = "flex";
        setTimeout(() => {
            loader.style.opacity = "1";
        }, 10);
    };
    
    window.hideLoader = function() {
        const loader = document.getElementById("fullpage-loader");
        loader.style.opacity = "0";
        setTimeout(() => {
            loader.style.display = "none";
        }, 300);
    };
    
    // Inicializar con opacidad 0 para la transición
    // document.addEventListener("DOMContentLoaded", function() {
    //     const loader = document.getElementById("fullpage-loader");
    //     loader.style.opacity = "0";
    //     loader.style.transition = "opacity 0.1s ease";
    // });
    </script>
    ';
}
add_action('wp_footer', 'add_controllable_loader');

function evitar_reenvio_formulario()
{
    echo '
    <script>
    // Evitar reenvío del formulario al recargar
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    </script>
    ';
}
add_action('wp_footer', 'evitar_reenvio_formulario');


function enqueue_custom_scripts()
{
    // Registra y encola el script personalizado
    wp_register_script('custom-click-handler', '', [], false, true);

    // Agrega el código JavaScript directamente
    wp_add_inline_script('custom-click-handler', "
        document.addEventListener('DOMContentLoaded', function() {
            // Selecciona el botón que desencadenará el clic
            var triggerButton = document.getElementById('btn_ciudad');
            // Selecciona el enlace cuyo clic deseas simular
            var targetLink = document.getElementById('set-def-store-popup-btn');
            
            // Verifica si ambos elementos existen
            if (triggerButton && targetLink) {
                // Agregar un evento click al botón de la ciudad
                triggerButton.addEventListener('click', function() {
                    // Simula el clic en el enlace de destino
                    targetLink.click();
                });
            } else {
                console.error('No se encontraron uno o ambos elementos: btn_ciudad o set-def-store-popup-btn.');
            }
        });
    ");

    // Encola el script
    // wp_enqueue_script('custom-click-handler');
}

// Hook para encolar el script en el frontend
//add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');


//Mostrar el usuario en el header//
//
// function mi_shortcode_iniciar_sesion_o_mostrar_menu()
// {
//     // Verificar si el usuario está logueado
//     if (is_user_logged_in()) {
//         // Obtener los detalles del usuario
//         $usuario = wp_get_current_user();
//         $nombre_usuario = $usuario->display_name;
//         $logout_url = wp_logout_url(home_url('/mi-cuenta/'));

//         // Mostrar contenido si el usuario está logueado
//         return '<span class="enlace-inicio-sesion">Bienvenido <a href="/mi-cuenta/" class="enlacemyAccount">' . esc_html($nombre_usuario) . '!,</a> <a href="' . esc_url($logout_url) . '" class="enlaceOut">Salir</a></span>';
//     } else {
//         // Mostrar formulario de inicio de sesión si no está logueado
//         return '<a href="/mi-cuenta/" class="enlace-inicio-sesion">Iniciar sesión</a>';
//     }
// }
// // Registrar el shortcode
// add_shortcode('iniciar_sesion_o_mostrar_menu', 'mi_shortcode_iniciar_sesion_o_mostrar_menu');


//AOG 17 enero
function custom_woocommerce_text_strings($translated_text, $text, $domain)
{
    // Revisa si el texto coincide
    if ($domain === 'woocommerce') {
        if ($text === 'An account is already registered with %s. Please log in or use a different email address.') {
            // Personaliza el mensaje manteniendo el correo electrónico
            $translated_text = 'El correo electrónico %s ya está registrado. Por favor, inicia sesión o utiliza otra dirección de correo.';
        }
    }
    return $translated_text;
}
add_filter('gettext', 'custom_woocommerce_text_strings', 10, 3);





add_shortcode('mostrar_variaciones', 'mostrar_variaciones_producto');
function mostrar_producto_relacionado_shortcode($atts)
{
    // Obtén los parámetros del shortcode
    $atts = shortcode_atts(array(
        'post_id' => get_the_ID(), // Por defecto, usa el ID del post actual
    ), $atts, 'mostrar_producto_relacionado');

    // Obtén el ID del post
    $post_id = $atts['post_id'];

    // Obtén el campo de relación de ACF
    $productos_relacionados = get_field('ingredientes_recetas', $post_id);

    if ($productos_relacionados) {
        $output = '<div class="row-variaciones">';
        foreach ($productos_relacionados as $producto) {
            // Obtén los datos del producto
            $product_id = $producto->ID;
            $product_title = get_the_title($product_id);
            $product_image = get_the_post_thumbnail($product_id, 'thumbnail');

            // Construye el HTML de salida
            $output .= '<a href="' . get_permalink($product_id) . '" class="variaciones--card">';
            $output .= '<span class="variaciones--figure">';
            $output .= $product_image;
            $output .= '</span>';
            $output .= '<span class="variaciones--content">';
            $output .= '<h5 class="variaciones--title">' . $product_title . '</h5>';
            $output .= '</span>';
            $output .= '</a>';
        }
        $output .= '</div>';
    } else {
        $output = '<p>No hay productos relacionados.</p>';
    }

    return $output;
}
add_shortcode('mostrar_producto_relacionado', 'mostrar_producto_relacionado_shortcode');


// Agregar clase personalizada al body con el nombre de la página
function agregar_clase_nombre_pagina($classes)
{
    if (is_page()) {
        global $post;
        // Obtener el slug de la página y agregarlo como clase
        $classes[] = 'pagina-' . $post->post_name;
    }
    return $classes;
}
add_filter('body_class', 'agregar_clase_nombre_pagina');


/** funcion del plugin product-quantity */
function custom_quantity_input_args($args, $product)
{
    //se forza a 1 para que sea mejor ux!
    // Obtiene el paso de cantidad desde el campo meta del producto
    $qty_step = get_post_meta($product->get_id(), 'ri_quantity_step', true);
    //$qty_step = 1;

    // Configura el paso y el valor mínimo del input de cantidad
    $args['step'] = $qty_step;
    $args['min_value'] = $qty_step;
    $args['input_value'] = $qty_step;

    return $args;
}
add_filter('woocommerce_quantity_input_args', 'custom_quantity_input_args', 10, 2);

// Se agrega modal de loading al footer sin agregar el bloque al footer por si se actualiza el tema.


function agregar_loader_en_head()
{
    ?>
    <div class="loading-box" id="msgLoading" style="z-index: 2147483647">
        <div class="loading-box__animaciones">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/letra2.svg" style="width: 100px; height: 100px;"
                class="img-up">
            <img src="<?php echo get_stylesheet_directory_uri(); ?>/images/fondo-animado.svg"
                style="width: 100px; height: 100px;" class="img-down">
        </div>
    </div>
    <?php
}
add_action('wp_body_open', 'agregar_loader_en_head');

function mostrar_loader_en_navegacion_real()
{
    ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const loader = document.getElementById('msgLoading');
            if (!loader) return;

            // Detectar clicks en <a> que recargan la página
            document.querySelectorAll('a[href]').forEach(function (link) {
                link.addEventListener('click', function (e) {
                    const link = e.target.closest('a');

                    let esExcluido = true;

                    if (link && link.classList.contains('btnManualmente')) {
                        loader.style.display = 'none';
                        return;
                    }

                    if (link.closest('.resultados__ubicacion-caja')) {
                        return;
                    }

                    const url = new URL(link.href, window.location.href);
                    const esInterno = url.hostname === window.location.hostname;
                    const esAncla = url.hash && url.pathname === window.location.pathname;

                    if (window.location.pathname.includes('/checkout')) {
                        if (link.classList.contains('showlogin') ||
                            link.classList.contains('showcoupon')) {
                            return;
                        }
                    } else {
                        esExcluido = link.classList.contains('material-wos') &&
                            link.classList.contains('woocommerce-loop-product__title') &&
                            link.classList.contains('elementor-button-link') &&
                            link.classList.contains('elementor-size-sm');

                        <?php
                        if (is_cart()) {
                            ?>
                            if (link.classList.contains('remove')) {
                                esExcluido = true;
                            }
                            <?php
                        }
                        ?>
                    }

                    if (esInterno && !esAncla && !esExcluido) {
                        loader.style.display = 'flex';
                    }
                });
            });

            // Mostrar loader inmediatamente si la página está cargando
            if (document.readyState !== 'complete') {
                loader.style.display = 'flex';
            }

            // Ocultar cuando termine de cargar
            window.addEventListener('load', function () {
                loader.style.display = 'none';
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'mostrar_loader_en_navegacion_real', 100);


function detectar_f5_y_mostrar_loader()
{
    ?>
    <script>
        window.addEventListener('beforeunload', function (e) {
            const loader = document.getElementById('msgLoading');
            if (loader) {
                loader.style.display = 'flex'; // Muestra el loader.
            }
        });
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                const loader = document.getElementById('msgLoading');
                if (loader) {
                    loader.style.display = 'none';
                }
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'F5' || (event.keyCode === 116)) {
                event.preventDefault(); // Previene la recarga estándar de la tecla F5.
                const loader = document.getElementById('msgLoading');
                if (loader) {
                    loader.style.display = 'flex'; // Muestra el loader.
                }
                setTimeout(function () {
                    location.reload(true); // Recarga la página después de un breve retraso.
                }, 100); // Ajusta este tiempo según sea necesario para ver el loader.
            }
        });
    </script>
    <?php
}
add_action('wp_head', 'detectar_f5_y_mostrar_loader');

?>
<?php

add_action('wp_head', function () {
    // Verifica el tipo de entorno
    if (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'dev') {
        echo "
        <style>
        body::before {
           content: '(DEV)';
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 5rem; /* Ajusta el tamaño */
            color: rgba(255, 0, 0, 0.2); /* Ajusta el color y la opacidad */
            z-index: 9999;
            pointer-events: none; /* No interfiere con clics */
            font-family: Arial, sans-serif;
            font-weight: bold;
            white-space: nowrap;
        }
        </style>
        ";
    }
});
function cargar_fontawesome_cdn()
{
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
        array(),
        '5.15.4'
    );
}
add_action('wp_enqueue_scripts', 'cargar_fontawesome_cdn');

add_action('woocommerce_after_order_notes', 'custom_pickup_fields');

function custom_pickup_fields()
{
    echo '<div id="pickup_details" style="display:none;">';

    echo '<span><h3>' . esc_html__('Seleccione su horario de preferencia', 'woocommerce') . '</h3></span>';

    // aviso importante
    echo '<div role="note" aria-live="polite"
            style="
                margin:14px 0 18px;
                padding:12px 14px;
                background:#f6faff;
                border:1px solid #dbe7ff;
                border-left:4px solid #1e4fbf;
                border-radius:10px;
                display:flex;
                align-items:flex-start;
                gap:10px;
                font-size:14px;
                line-height:1.45;
            ">
            <span aria-hidden="true"
                style="font-size:20px;line-height:1;color:#1e4fbf;margin-top:0px;">&#9432;</span>
            <span style="color:#0f1d3a;">
            <strong style="display:block;color:#0f1d3a;margin-bottom:2px;">IMPORTANTE</strong>
            ' . esc_html__('La tienda resguarda tu pedido por un máximo de 24 horas a partir de la hora elegida.', 'woocommerce') . '
            </span>
        </div>';

    // Fecha
    woocommerce_form_field('pickup_date', array(
        'type' => 'date',
        'class' => array('form-row-wide', 'pickup-date'),
        'label' => __('Fecha de recogida', 'woocommerce'),
        'required' => true,
        'custom_attributes' => array(
            'min' => date('Y-m-d'),
            'max' => date('Y-m-d', strtotime('+1 day')),
        ),
    ));

    // Hora (se poblará por JS según la fecha)
    woocommerce_form_field('pickup_time', array(
        'type' => 'select',
        'class' => array('form-row-wide', 'pickup-time'),
        'label' => __('Hora de recogida', 'woocommerce'),
        'required' => true,
        'options' => array(
            '' => __('Seleccione una hora', 'woocommerce'),
        ),
    ));

    // Comentarios
    woocommerce_form_field('pickup_comments', array(
        'type' => 'textarea',
        'class' => array('form-row-wide'),
        'label' => __('Comentarios adicionales', 'woocommerce'),
        'required' => false,
    ));

    if (!is_user_logged_in()) {

        // Aviso
        echo '<p class="form-row form-row-wide" style="margin-top:1.5em;"><em>' .
            esc_html__('Estos datos solo se solicitan una vez. Al completar su pedido se creará automáticamente una cuenta con la información proporcionada y estos campos no volverán a aparecer.', 'woocommerce') .
            '</em></p>';

        // Tipo de uso
        echo '<p class="form-row form-row-wide">';
        echo '<label>' . esc_html__('Tipo de uso', 'woocommerce') . ' <span class="required">*</span></label><br />';
        echo '<div style="display:flex; align-items:center;">';
        echo '<label style="margin-right:12px;">';
        echo '<input type="radio" name="tipo_uso" value="Customer" id="uso_personal" required /> ' . esc_html__('Uso personal', 'woocommerce');
        echo '</label>';
        echo '<label>';
        // Negocio seleccionado por defecto (checked)
        echo '<input type="radio" name="tipo_uso" value="Business" id="uso_negocio" required style="margin-left:10px;" checked /> ' . esc_html__('Negocio', 'woocommerce');
        echo '</label>';
        echo '</div>';
        echo '</p>';

        // Giro (la etiqueta y el placeholder cambian según el tipo)
        echo '<p class="form-row form-row-wide">';
        echo '<label id="giro_empresa_label" for="giro_empresa">' . esc_html__('Giro de la empresa', 'woocommerce') . ' <span class="required">*</span></label>';
        echo '<select name="giro_empresa" id="giro_empresa" class="input-select" required>';
        echo '<option value="">' . esc_html__('Selecciona el giro de la empresa', 'woocommerce') . '</option>';
        echo '</select>';
        echo '</p>';

        echo '<p class="form-row form-row-wide" id="billing_company_field">';
        echo '<label for="billing_company">' . __( "Nombre de la empresa", "woocommerce" ) . ' <span class="required">*</span></label>';
        echo '<input type="text" class="input-text" name="billing_company" id="billing_company" placeholder="" value="" data-required="1" autocomplete="organization" required aria-required="true" aria-invalid="true">';
        echo '</p>';
    }

    echo '</div>'; // #pickup_details
    ?>

    <script>
        jQuery(function ($) {

            /* ============================
            Configuración de horarios
            ============================ */
            const MX_TZ = 'America/Mexico_City';
            const OPEN_HOUR = 10;
            const LAST_START = 17;
            const LEAD_HOURS = 2;

            let $date = jQuery(); // se asignan en DOM ready
            let $time = jQuery();

            /* -------- Utilidades en TZ de CDMX (sin reconstruir Date local) -------- */
            function nowMxParts() {
                const parts = new Intl.DateTimeFormat('en-CA', {
                    timeZone: MX_TZ,
                    year: 'numeric', month: '2-digit', day: '2-digit',
                    hour: '2-digit', minute: '2-digit', second: '2-digit',
                    hour12: false
                }).formatToParts(new Date());
                return Object.fromEntries(parts.map(p => [p.type, p.value]));
            }

            function todayMxYmd() {
                const m = nowMxParts();
                return `${m.year}-${m.month}-${m.day}`;
            }
            function currentMxHour() { return parseInt(nowMxParts().hour, 10); }
            function currentMxMinute() { return parseInt(nowMxParts().minute, 10); }

            function formatMxTime(dateObj = new Date()) {
                return new Intl.DateTimeFormat('es-MX', {
                    timeZone: MX_TZ, hour: '2-digit', minute: '2-digit', hour12: false
                }).format(dateObj);
            }

            /* Sumar días a un YYYY-MM-DD sin depender de zona local */
            function ymdAddDays(ymdStr, days) {
                const [y, m, d] = ymdStr.split('-').map(Number);
                const dt = new Date(Date.UTC(y, m - 1, d));
                dt.setUTCDate(dt.getUTCDate() + days);
                const y2 = dt.getUTCFullYear();
                const m2 = String(dt.getUTCMonth() + 1).padStart(2, '0');
                const d2 = String(dt.getUTCDate()).padStart(2, '0');
                return `${y2}-${m2}-${d2}`;
            }

            function buildSlots(startHour) {
                const slots = [];
                for (let h = startHour; h <= LAST_START; h++) {
                    const next = (h + 1);
                    const label = `${String(h).padStart(2, '0')}:00 - ${String(next).padStart(2, '0')}:00`;
                    slots.push({ value: String(h), label });
                }
                return slots;
            }

            /* Actualiza el label cada vez (no lo cacheamos) */
            function updatePickupLabel() {
                const currLabel = formatMxTime() + ' hrs';
                const html =
                    '<?php echo esc_js(__('Hora de recogida', 'woocommerce')); ?>' +
                    '&nbsp;<abbr class="required" title="required">*</abbr>' +
                    '<small> <?php echo esc_js(__('Hora actual (CDMX):', 'woocommerce')); ?> ' + currLabel + '</small>';
                const $lbl = jQuery('#pickup_time_field label');
                if ($lbl.length) $lbl.html(html);
            }

            function populateTimesFor(selectedYmd) {
                updatePickupLabel();

                const today = todayMxYmd();
                const hourNow = currentMxHour();

                const isToday = (selectedYmd === today);
                let slots = [];

                if (isToday) {
                    // Si ya pasó de las 6:00 pm en CDMX (LAST_START + 1), no hay disponibilidad hoy
                    if (hourNow >= (LAST_START + 1)) {
                        $time.empty()
                            .append('<option value="" disabled><?php echo esc_js(__('No hay horarios disponibles hoy. Selecciona mañana.', 'woocommerce')); ?></option>')
                            .prop('disabled', true);
                        return;
                    }

                    // Primer bloque permitido: 2 horas después de la hora actual (misma lógica)
                    const earliest = Math.max(OPEN_HOUR, hourNow + LEAD_HOURS);
                    slots = buildSlots(earliest);
                } else {
                    // Día siguiente o futuro: siempre 10:00–17:00
                    slots = buildSlots(OPEN_HOUR);
                }

                $time.empty().append('<option value="" disabled selected><?php echo esc_js(__('Seleccione una hora', 'woocommerce')); ?></option>');

                if (slots.length) {
                    slots.forEach(s => $time.append(`<option value="${s.value}">${s.label}</option>`));
                    $time.prop('disabled', false);
                } else {
                    $time.append('<option value="" disabled><?php echo esc_js(__('No hay horarios disponibles', 'woocommerce')); ?></option>')
                        .prop('disabled', true);
                }
            }

            /* -------- DOM Ready + inicialización -------- */
            jQuery(function ($) {
                // Seleccionamos cuando el DOM ya existe
                $date = $('#pickup_date');
                $time = $('#pickup_time');

                (function initDateConstraints() {
                    const m = nowMxParts();
                    const hourMx = parseInt(m.hour, 10);
                    const today = `${m.year}-${m.month}-${m.day}`;
                    const tomorrow = ymdAddDays(today, 1);

                    // Si ya pasó de 6:00 pm (LAST_START+1) en CDMX, mínimo = mañana; si no, hoy
                    const minDate = (hourMx >= (LAST_START + 1)) ? tomorrow : today;

                    $date.attr('min', minDate).attr('max', tomorrow);

                    const currentVal = $date.val();
                    if (!currentVal || currentVal < minDate || currentVal > tomorrow) {
                        $date.val(minDate);
                    }

                    // Pre-carga de horarios y label al entrar
                    populateTimesFor($date.val());
                })();

                // Cambio de fecha → recalcular horas
                $(document).on('change', '#pickup_date', function () {
                    populateTimesFor($(this).val());
                });

                // Si WooCommerce re-renderiza el checkout, vuelve a pintar el label/horarios
                $(document.body).on('updated_checkout', function () {
                    $date = $('#pickup_date');
                    $time = $('#pickup_time');
                    populateTimesFor($date.val() || todayMxYmd());
                });
            });

            /* -------- Inicialización de la fecha con reglas de CDMX (sin doble offset) -------- */
            (function initDateConstraints() {
                const m = nowMxParts();
                const hourMx = parseInt(m.hour, 10);
                const today = `${m.year}-${m.month}-${m.day}`;
                const tomorrow = ymdAddDays(today, 1);

                // Si ya pasó de 6:00 pm (LAST_START+1) en CDMX, mínimo = mañana; de lo contrario, hoy
                const minDate = (hourMx >= (LAST_START + 1)) ? tomorrow : today;

                $date.attr('min', minDate).attr('max', tomorrow);

                // Ajusta si el valor actual está fuera del rango
                const currentVal = $date.val();
                if (!currentVal || currentVal < minDate || currentVal > tomorrow) {
                    $date.val(minDate);
                }

                // Pre-carga de horarios para la fecha seleccionada
                populateTimesFor($date.val());
            })();

            /* Cambio de fecha → recalcular horas */
            $(document).on('change', '#pickup_date', function () {
                populateTimesFor($(this).val());
            });

            /* ---------- CATÁLOGOS (uso personal/negocio) + ETIQUETA/PLACEHOLDER DINÁMICOS ---------- */
            // PHP → JS
            var catalogoPersonal = <?php echo wp_json_encode([["name" => "Ocasión de Consumo", "sin_tilde" => "Ocasion de Consumo"]]); ?>;
            var catalogoNegocio = <?php echo wp_json_encode([
                ["name" => "Pizzas", "sin_tilde" => "Pizzas"],
                ["name" => "Fondas o Cocinas", "sin_tilde" => "Fondas o Cocinas"],
                ["name" => "Hamburguesas", "sin_tilde" => "Hamburguesas"],
                ["name" => "Tortas", "sin_tilde" => "Tortas"],
                ["name" => "Taqueria", "sin_tilde" => "Taqueria"],
                ["name" => "Restaurante", "sin_tilde" => "Restaurante"],
                ["name" => "Hot dogs", "sin_tilde" => "Hot dogs"],
                ["name" => "Gorditas", "sin_tilde" => "Gorditas"],
                ["name" => "Cooperativas", "sin_tilde" => "Cooperativas"],
                ["name" => "Comedores Industriales", "sin_tilde" => "Comedores Industriales"],
                ["name" => "Banquetes", "sin_tilde" => "Banquetes"],
                ["name" => "Pollerias", "sin_tilde" => "Pollerias"],
                ["name" => "Bares y cantinas", "sin_tilde" => "Bares y cantinas"],
                ["name" => "Guarderias", "sin_tilde" => "Guarderias"],
                ["name" => "Carnitas", "sin_tilde" => "Carnitas"],
                ["name" => "Tendero o Cremerias", "sin_tilde" => "Tendero o Cremerias"],
                ["name" => "Carniceria", "sin_tilde" => "Carniceria"],
                ["name" => "Distribuidor", "sin_tilde" => "Distribuidor"],
                ["name" => "Tianguis", "sin_tilde" => "Tianguis"],
                ["name" => "Hoteles", "sin_tilde" => "Hoteles"],
                ["name" => "Burreros", "sin_tilde" => "Burreros"],
                ["name" => "Ferias", "sin_tilde" => "Ferias"],
                ["name" => "Dependencia de Gobierno", "sin_tilde" => "Dependencia de Gobierno"],
                ["name" => "Expendio de Pescados y Mariscos", "sin_tilde" => "Expendio de Pescados y Mariscos"],
                ["name" => "Sushi", "sin_tilde" => "Sushi"],
                ["name" => "Snack", "sin_tilde" => "Snack"],
                ["name" => "Eloteros", "sin_tilde" => "Eloteros"],
                ["name" => "Minisuper", "sin_tilde" => "Minisuper"],
                ["name" => "Barcos", "sin_tilde" => "Barcos"],
                ["name" => "Hospitales", "sin_tilde" => "Hospitales"],
                ["name" => "Cafeterias", "sin_tilde" => "Cafeterias"],
                ["name" => "Birrieria o Barbacoa", "sin_tilde" => "Birrieria o Barbacoa"],
                ["name" => "Menuderia", "sin_tilde" => "Menuderia"],
                ["name" => "Tamales", "sin_tilde" => "Tamales"],
                ["name" => "Restaurante de Mariscos", "sin_tilde" => "Restaurante de Mariscos"],
                ["name" => "Tortilleria o Panaderia", "sin_tilde" => "Tortilleria o Panaderia"],
            ]); ?>;

            // Textos dinámicos
            var labelPersonal = '<?php echo esc_js(__('Giro personal', 'woocommerce')); ?> <span class="required">*</span>';
            var labelEmpresa = '<?php echo esc_js(__('Giro de la empresa', 'woocommerce')); ?> <span class="required">*</span>';
            var phPersonal = '<?php echo esc_js(__('Selecciona el giro personal', 'woocommerce')); ?>';
            var phEmpresa = '<?php echo esc_js(__('Selecciona el giro de la empresa', 'woocommerce')); ?>';

            function poblarGiro(lista, seleccionado, esPersonal) {
                var $sel = $('#giro_empresa');
                var $label = $('#giro_empresa_label');

                // wrappers / inputs del campo "Nombre de la empresa"
                var $companyWrap = $('#billing_company_field'); // <p id="billing_company_field">
                var $companyInput = $('#billing_company');       // <input id="billing_company">

                // Mostrar/ocultar por display y limpiar valor si es personal
                if (esPersonal) {
                    $companyWrap.css('display', 'none');      // o $companyWrap.hide();
                    $companyInput.val('').trigger('change');  // limpia el valor
                    // (opcional) quitar required cuando es personal:
                    $companyInput.prop('required', false).attr('aria-required', 'false');
                } else {
                    $companyWrap.css('display', 'block');     // o $companyWrap.show();
                    // (opcional) volver a exigirlo cuando es negocio:
                    $companyInput.prop('required', true).attr('aria-required', 'true');
                }

                // Cambia etiqueta
                $label.html(esPersonal ? labelPersonal : labelEmpresa);

                // Placeholder y opciones
                var placeholder = esPersonal ? phPersonal : phEmpresa;
                $sel.empty().append(new Option(placeholder, ''));

                lista.forEach(function (item) {
                    $sel.append(new Option(item.sin_tilde, item.name, false, item.name === seleccionado));
                });

                // Si hay 1 sola opción, autoselecciona
                if (!seleccionado && lista.length === 1) {
                    $sel.val(lista[0].name).trigger('change');
                }
            }

            // Al cambiar el tipo de uso
            $(document).on('change', 'input[name="tipo_uso"]', function () {
                if ($('#uso_personal').is(':checked')) {
                    poblarGiro(catalogoPersonal, '', true);
                } else if ($('#uso_negocio').is(':checked')) {
                    poblarGiro(catalogoNegocio, '', false);
                }
            });

            poblarGiro(catalogoNegocio, '', false);

            /* ---------- TELÉFONO 10 dígitos ---------- */
            var phone = document.getElementById('billing_phone');
            if (!phone) return;

            // Ayudas de UX en móviles
            phone.setAttribute('inputmode', 'numeric');
            phone.setAttribute('autocomplete', 'tel-national');
            phone.setAttribute('pattern', '\\d{10}');
            phone.setAttribute('maxlength', '10');
            if (!phone.placeholder) phone.placeholder = '10 dígitos (México)';

            // Mantén sólo números y máximo 10
            function sanitize() {
                var cleaned = phone.value.replace(/\D/g, '').slice(0, 10);
                phone.value = cleaned;
            }

            phone.addEventListener('input', sanitize);

            phone.addEventListener('paste', function (e) {
                e.preventDefault();
                var paste = (e.clipboardData || window.clipboardData).getData('text') || '';
                var cleaned = paste.replace(/\D/g, '').slice(0, 10);
                // Inserta el texto limpio
                var start = phone.selectionStart, end = phone.selectionEnd;
                var v = phone.value;
                phone.value = (v.slice(0, start) + cleaned + v.slice(end)).slice(0, 10);
                // recoloca el cursor
                var pos = Math.min((start + cleaned.length), 10);
                phone.setSelectionRange(pos, pos);
            });

            // Bloquea teclas no numéricas (permite navegación/edición)
            phone.addEventListener('keydown', function (e) {
                if (e.ctrlKey || e.metaKey || e.altKey) return;
                var code = e.keyCode;
                var allowed = [8, 9, 13, 27, 37, 39, 46]; // backspace, tab, enter, esc, flechas, delete
                if (allowed.indexOf(code) !== -1) return;
                // Sólo dígitos 0-9
                if (!/^\d$/.test(e.key)) e.preventDefault();
                // Respeta el máximo de 10
                if (phone.value.replace(/\D/g, '').length >= 10 && phone.selectionStart === phone.selectionEnd) {
                    e.preventDefault();
                }
            });

            // Al salir del campo, avisa si no son 10 (si escribió algo)
            phone.addEventListener('blur', function () {
                if (phone.value && phone.value.length !== 10) {
                    alert('El teléfono debe tener exactamente 10 dígitos (México), sin +52 ni espacios.');
                    phone.focus();
                }
            });

            // Evita enviar formularios si no son 10
            function assertValid() {
                sanitize();
                if (phone.value.length !== 10) {
                    alert('El teléfono debe tener exactamente 10 dígitos (México).');
                    phone.focus();
                    return false;
                }
                return true;
            }

            document.querySelectorAll('form.checkout, form.register, form.edit-account').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    if (!assertValid()) e.preventDefault();
                });
            });
        });
    </script>
    <?php
}

add_action('woocommerce_checkout_process', 'cm_validar_tipo_uso_y_giro');
function cm_validar_tipo_uso_y_giro()
{

    // Solo para invitados (si quieres también para logueados, quita este return)
    if (is_user_logged_in()) {
        return;
    }

    $tipo = isset($_POST['tipo_uso']) ? sanitize_text_field($_POST['tipo_uso']) : '';
    $company = isset($_POST['billing_company']) ? trim(wp_unslash($_POST['billing_company'])) : '';
    $giro = isset($_POST['giro_empresa']) ? sanitize_text_field($_POST['giro_empresa']) : '';

    // 1) Debe elegir tipo de uso
    if ($tipo === '') {
        wc_add_notice(__('Por favor, selecciona el tipo de uso.', 'woocommerce'), 'error');
        return; // corto aquí para no mostrar otros mensajes dependientes
    }

    // 2) Si es Negocio/Business, exijo Empresa y Giro
    $is_business = (strcasecmp($tipo, 'Business') === 0) || (strcasecmp($tipo, 'Negocio') === 0);

    if ($is_business) {
        if ($giro === '') {
            wc_add_notice(__('Por favor, selecciona el giro de la empresa.', 'woocommerce'), 'error');
        }

        if ($company === '') {
            wc_add_notice(__('Por favor, ingresa el nombre de la empresa.', 'woocommerce'), 'error');
        } elseif (mb_strlen($company) < 3) {
            wc_add_notice(__('El nombre de la empresa debe tener al menos 3 caracteres.', 'woocommerce'), 'error');
        }
    }
}


add_filter('gettext', 'cm_traducir_temporary_password_notice', 20, 3);
function cm_traducir_temporary_password_notice($translated, $text, $domain)
{
    if (
        $domain === 'woocommerce' &&
        stripos($text, 'Your account with %1$s is using a temporary password') !== false
    ) {

        /* Nota: el %1$s se sustituye por el nombre de tu tienda */
        return 'Tu cuenta en %1$s utiliza una contraseña temporal. ' .
            'Te hemos enviado un enlace a tu correo para que la cambies.';
    }
    return $translated;
}

add_action('wp_footer', 'forzar_traducciones_checkout');
function forzar_traducciones_checkout()
{
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const reemplazos = {
                'Billing Details': 'Detalles de facturación',
                'Order Review': 'Resumen del pedido',
                'Back To Cart': 'Volver al carrito',
                'Ship to a different address?': '¿Enviar a una dirección diferente?',
                'Payment Method': 'Métodos de pago',
                'Coupon code': 'Código de cupón',
                'Returning customer?': '¿Ya eres cliente?',
                'Click here to login': ' Haz clic aquí para iniciar sesión',
                'If you have shopped with us before, please enter your details below. If you are a new customer, please proceed to the Billing section.':
                    'Si ya has comprado con nosotros, ingresa tus datos a continuación. Si eres nuevo, continúa con la sección de facturación.',
                'Username or email': 'Usuario o correo electrónico',
                'Password': 'Contraseña',
                'Remember me': 'Recuérdame',
                'Login': 'Iniciar sesión',
                'Lost your password?': '¿Olvidaste tu contraseña?',
                'If you have a coupon code, please apply it below.': 'Si tienes un código de cupón, ingrésalo a continuación.',
                'Apply Coupon': 'Aplicar cupón',
                'Back To Cart': 'Volver al carrito'
            };

            function traducirNodo(node) {
                if (node.nodeType === Node.TEXT_NODE) {
                    let texto = node.nodeValue.trim();
                    for (const [ingles, espanol] of Object.entries(reemplazos)) {
                        if (texto.includes(ingles)) {
                            node.nodeValue = texto.replace(ingles, espanol);
                        }
                    }
                } else if (node.nodeType === Node.ELEMENT_NODE) {
                    const atributos = ['placeholder', 'title', 'aria-label'];
                    atributos.forEach(attr => {
                        if (node.hasAttribute(attr)) {
                            let valor = node.getAttribute(attr);
                            for (const [ingles, espanol] of Object.entries(reemplazos)) {
                                if (valor.includes(ingles)) {
                                    node.setAttribute(attr, valor.replace(ingles, espanol));
                                }
                            }
                        }
                    });
                    node.childNodes.forEach(traducirNodo);
                }
            }

            traducirNodo(document.body);
        });
    </script>
    <?php
}

add_action('wp_footer', 'expandir_login_checkout_forzado', 100);
function expandir_login_checkout_forzado()
{
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let intentos = 0;

            const abrirFormularioLogin = () => {
                const loginForm = document.querySelector('.woocommerce-form-login');
                const seccion = document.querySelector('.woocommerce-info a[href*="showlogin"]');

                if (loginForm && loginForm.style.display !== 'block') {
                    loginForm.style.display = 'block';
                }

                // Opcional: también ocultar el mensaje "¿Ya eres cliente?"
                // if (seccion?.parentElement) {
                //     seccion.parentElement.style.display = 'none';
                // }
            };

            const intervalo = setInterval(() => {
                abrirFormularioLogin();
                intentos++;
                if (intentos >= 10) clearInterval(intervalo);
            }, 300);
        });
    </script>
    <?php
}


add_action('wp_footer', 'conditional_pickup_fields_script');
function conditional_pickup_fields_script()
{
    if (is_checkout()) {
        ?>
        <script>
            jQuery(function ($) {
                // Función para mostrar/ocultar el formulario según el método de envío seleccionado
                function togglePickupDetails() {
                    const selectedMethod = $('input[name="shipping_method[0]"]:checked').val();
                    if (selectedMethod === 'local_pickup:2') { // Ajustar al nuevo valor del método de envío
                        $('#pickup_details').slideDown(); // Mostrar el formulario
                    } else {
                        $('#pickup_details').slideUp(); // Ocultar el formulario
                    }
                    $('#pickup_details').slideDown();
                }

                // Listener cuando se cambia el método de envío manualmente
                $(document).on('change', 'input[name="shipping_method[0]"]', function () {
                    togglePickupDetails();
                });

                // Listener para actualizaciones dinámicas del método de envío (por WooCommerce)
                $(document.body).on('updated_shipping_method', function () {
                    togglePickupDetails();
                });

                // Ejecutar al cargar la página
                togglePickupDetails();

                $('form.checkout').on('change', 'input[name^="shipping_method"]', function () {
                    let selectedMethod = $('input[name^="shipping_method"]:checked').val();

                    if (selectedMethod === 'flat_rate:1') {
                        $('label[for="pickup_date"]').text('Fecha de entrega');
                        $('label[for="pickup_time"]').text('Hora de entrega');
                        $('#pickup_title').text('Seleccione su horario de entrega');
                    } else {
                        $('label[for="pickup_date"]').text('Fecha de recogida');
                        $('label[for="pickup_time"]').text('Hora de recogida');
                        $('#pickup_title').text('Seleccione su horario de preferencia');
                    }
                });

                $('form.checkout input[name^="shipping_method"]:checked').trigger('change');
            });
        </script>
        <?php
    }
}

/* ===========================================================
 *  Reglas de recogida (back-end) — CDMX
 *  - Zona horaria fija: America/Mexico_City
 *  - Ventana: 10:00–17:00 (bloques de 1h, último inicio 17:00)
 *  - Anticipación mínima: 2 horas
 *  - Fecha permitida: hoy o mañana (según hora CDMX)
 * =========================================================== */

if (!function_exists('cm_pickup_constants')) {
    function cm_pickup_constants()
    {
        return [
            'tz' => new DateTimeZone('America/Mexico_City'),
            'OPEN_HOUR' => 10,
            'LAST_START' => 17,
            'LEAD_HOURS' => 2,
        ];
    }
}

/** Parsear fecha del POST a Y-m-d (acepta "YYYY-mm-dd", "dd/mm/YYYY", "dd - mm - YYYY", etc.) */
if (!function_exists('cm_parse_pickup_date_ymd')) {
    function cm_parse_pickup_date_ymd($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '')
            return null;

        // 1) Ya viene en ISO
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            return $raw;
        }
        // 2) Cualquier separador entre d m Y
        if (preg_match('/(\d{1,2})\D+(\d{1,2})\D+(\d{4})/', $raw, $m)) {
            // d m Y -> Y-m-d
            $d = (int) $m[1];
            $mo = (int) $m[2];
            $y = (int) $m[3];
            if (checkdate($mo, $d, $y)) {
                return sprintf('%04d-%02d-%02d', $y, $mo, $d);
            }
        }
        return null;
    }
}

/** Extraer la hora (entera) del POST. Acepta "14" o "14:00 - 15:00" */
if (!function_exists('cm_parse_pickup_hour')) {
    function cm_parse_pickup_hour($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '')
            return null;
        if (preg_match('/^\d{1,2}$/', $raw)) {
            return (int) $raw;
        }
        if (preg_match('/^\s*(\d{1,2})\s*:\s*\d{2}/', $raw, $m)) {
            return (int) $m[1];
        }
        return null;
    }
}

/** Añadir días a Y-m-d en UTC (no depende de TZ del servidor) */
if (!function_exists('cm_ymd_add_days')) {
    function cm_ymd_add_days($ymd, $days)
    {
        $dt = DateTime::createFromFormat('!Y-m-d', $ymd, new DateTimeZone('UTC'));
        if (!$dt)
            return null;
        $dt->modify(($days >= 0 ? '+' : '') . (int) $days . ' days');
        return $dt->format('Y-m-d');
    }
}

/** Validación en checkout (muestra error y bloquea el pedido si no cumple) */
add_action('woocommerce_after_checkout_validation', function ($data, $errors) {
    $C = cm_pickup_constants();
    $tz = $C['tz'];

    // Solo cuando el método elegido es local_pickup:2
    $chosen = WC()->session ? (WC()->session->get('chosen_shipping_methods')[0] ?? '') : '';
    if (strpos($chosen, 'local_pickup:2') === false) {
        return;
    }

    $date_raw = isset($_POST['pickup_date']) ? wp_unslash($_POST['pickup_date']) : '';
    $time_raw = isset($_POST['pickup_time']) ? wp_unslash($_POST['pickup_time']) : '';

    $date_ymd = cm_parse_pickup_date_ymd($date_raw);
    $hour = cm_parse_pickup_hour($time_raw);

    if (!$date_ymd) {
        $errors->add('pickup_date', __('Selecciona una fecha de recogida válida.', 'woocommerce'));
        return;
    }
    if ($hour === null) {
        //$errors->add('pickup_time', __('Selecciona una hora de recogida válida.', 'woocommerce'));
        return;
    }

    // "Ahora" en CDMX
    $now = new DateTime('now', $tz);
    $today = $now->format('Y-m-d');
    $tomorrow = (clone $now)->modify('+1 day')->format('Y-m-d');
    $hourNow = (int) $now->format('G');   // 0..23
    $OPEN = (int) $C['OPEN_HOUR'];
    $LAST = (int) $C['LAST_START'];
    $LEAD = (int) $C['LEAD_HOURS'];

    // Rango de fecha permitido: hoy o mañana
    if (!in_array($date_ymd, [$today, $tomorrow], true)) {
        $errors->add('pickup_date', __('La fecha debe ser hoy o mañana (hora de CDMX).', 'woocommerce'));
        return;
    }

    if ($date_ymd === $today) {
        // Si ya pasó de 18:00 (LAST_START+1), no hay disponibilidad hoy
        if ($hourNow >= ($LAST + 1)) {
            $errors->add('pickup_time', __('Ya no hay horarios disponibles hoy. Por favor selecciona mañana.', 'woocommerce'));
            return;
        }
        // Primer horario permitido hoy
        $earliest = max($OPEN, $hourNow + $LEAD);

        if ($hour < $earliest || $hour > $LAST) {
            $errors->add(
                'pickup_time',
                sprintf(
                    /* translators: 1: hour start, 2: hour end */
                    __('El primer horario disponible hoy es %1$02d:00–%2$02d:00 (CDMX).', 'woocommerce'),
                    $earliest,
                    $earliest + 1
                )
            );
            return;
        }
    } else { // mañana
        if ($hour < $OPEN || $hour > $LAST) {
            $errors->add('pickup_time', __('Selecciona una hora entre 10:00 y 18:00 (CDMX).', 'woocommerce'));
            return;
        }
    }

    // Si todo OK, guardamos en sesión versiones normalizadas para usarlas al guardar la orden
    WC()->session->set('cm_pickup_date_ymd', $date_ymd);
    WC()->session->set('cm_pickup_hour', (int) $hour);
}, 10, 2);

/**
 * Modificado: 14 abril 2025
 * @author: spark-jesus upd
 */
/** Guardado en la orden (usa los normalizados si existen; si no, re-intenta parsear) */
add_action('woocommerce_checkout_update_order_meta', function ($order_id) {
    $order = wc_get_order($order_id);

    $date_ymd = WC()->session ? WC()->session->get('cm_pickup_date_ymd') : null;
    $hour = WC()->session ? WC()->session->get('cm_pickup_hour') : null;

    // Fallback por si llegaran sin sesión
    if (!$date_ymd && !empty($_POST['pickup_date'])) {
        $date_ymd = cm_parse_pickup_date_ymd(wp_unslash($_POST['pickup_date']));
    }
    if ($hour === null && !empty($_POST['pickup_time'])) {
        $hour = cm_parse_pickup_hour(wp_unslash($_POST['pickup_time']));
    }

    // Guarda si hay valores válidos
    if ($date_ymd) {
        $order->update_meta_data('lp_pickup_date', sanitize_text_field($date_ymd));
    }
    if ($hour !== null) {
        // Guardamos legible y, opcional, la hora entera
        $label = sprintf('%02d:00 - %02d:00', (int) $hour, (int) $hour + 1);
        $order->update_meta_data('lp_pickup_time_completed', sanitize_text_field($label));
        $order->update_meta_data('lp_pickup_time', (int) $hour);
    }

    // Comentarios
    if (!empty($_POST['pickup_comments'])) {
        $order->update_meta_data('pickup_comments', sanitize_textarea_field(wp_unslash($_POST['pickup_comments'])));
    }

    // Método de entrega
    $chosen_method = WC()->session->get('chosen_shipping_methods')[0] ?? '';
    if (strpos($chosen_method, 'local_pickup:2') !== false) {
        $order->update_meta_data('metodo_entrega', '01');
    }

    $order->save();

    // Limpia sesión
    if (WC()->session) {
        WC()->session->__unset('cm_pickup_date_ymd');
        WC()->session->__unset('cm_pickup_hour');
    }
});

//hacks de limpieza despues
add_filter('woocommerce_ship_to_different_address_checked', '__return_false');



/********Configuracion de URL API********/
// Crear el menú de configuración para url de API
function custom_plugin_settings_menu()
{
    add_menu_page(
        'Configuración de APIs',
        'Config APIs',
        'manage_options',
        'custom-plugin-settings',
        'custom_plugin_settings_page',
        'dashicons-admin-generic',
        99
    );
}
add_action('admin_menu', 'custom_plugin_settings_menu');

// Mostrar la página de configuración
function custom_plugin_settings_page()
{
    // Guardar los valores cuando se envía el formulario
    if (isset($_POST['save_settings'])) {

        //POS URL
        update_option('pos_url', sanitize_text_field($_POST['pos_url']));


        // API Connect
        update_option('custom_plugin_api_connect_url', sanitize_text_field($_POST['api_connect_url']));
        update_option('custom_plugin_api_connect_username', sanitize_text_field($_POST['api_connect_username']));
        update_option('custom_plugin_api_connect_password', sanitize_text_field($_POST['api_connect_password']));

        // API EIB2BPro
        update_option('custom_plugin_api_eib2bpro_url', sanitize_text_field($_POST['api_eib2bpro_url']));

        // API Multiservicio (Crear Productos y Mostrar Stock)
        update_option('custom_plugin_api_multiservicio_create_url', sanitize_text_field($_POST['api_multiservicio_create_url']));
        update_option('custom_plugin_api_multiservicio_stock_url', sanitize_text_field($_POST['api_multiservicio_stock_url']));

        // API Rappi
        update_option('custom_plugin_api_rappi_validate_url', sanitize_text_field($_POST['api_rappi_validate_url']));
        update_option('custom_plugin_api_rappi_create_url', sanitize_text_field($_POST['api_rappi_create_url']));

        // API Salesforce
        update_option('custom_plugin_api_salesforce_url', sanitize_text_field($_POST['api_salesforce_url']));
        update_option('custom_plugin_api_salesforce_clientsecret', sanitize_text_field($_POST['api_salesforce_clientsecret']));
        update_option('custom_plugin_api_salesforce_clientid', sanitize_text_field($_POST['api_salesforce_clientid']));

        // API Cleaersales
        update_option('custom_plugin_api_clearsales_url', sanitize_text_field($_POST['api_clearsales_url']));
        update_option('custom_plugin_api_clearsales_clientsecret', sanitize_text_field($_POST['api_clearsales_clientsecret']));
        update_option('custom_plugin_api_clearsales_clientid', sanitize_text_field($_POST['api_clearsales_clientid']));
        update_option('custom_plugin_api_clearsales_apikey', sanitize_text_field($_POST['api_clearsales_apikey']));


        echo '<div class="updated"><p>Configuración guardada correctamente.</p></div>';
    }

    // Obtener valores actuales
    $api_connect_url = get_option('custom_plugin_api_connect_url', '');
    $pos_url = get_option('pos_url', '');
    $api_connect_username = get_option('custom_plugin_api_connect_username', '');
    $api_connect_password = get_option('custom_plugin_api_connect_password', '');

    $api_eib2bpro_url = get_option('custom_plugin_api_eib2bpro_url', '');
    $api_multiservicio_create_url = get_option('custom_plugin_api_multiservicio_create_url', '');
    $api_multiservicio_stock_url = get_option('custom_plugin_api_multiservicio_stock_url', '');
    $api_rappi_url_validate = get_option('custom_plugin_api_rappi_validate_url', '');
    $api_rappi_url_create = get_option('custom_plugin_api_rappi_create_url', '');
    $api_salesforce_url = get_option('custom_plugin_api_salesforce_url', '');
    $api_salesforce_clientsecret = get_option('custom_plugin_api_salesforce_clientsecret', '');
    $api_salesforce_clientid = get_option('custom_plugin_api_salesforce_clientid ', '');

    $api_clearsales_url = get_option('custom_plugin_api_clearsales_url', '');
    $api_clearsales_apikey = get_option('custom_plugin_api_clearsales_apikey', '');
    $api_clearsales_clientsecret = get_option('custom_plugin_api_clearsales_clientsecret', '');
    $api_clearsales_clientid = get_option('custom_plugin_api_clearsales_clientid ', '');

    ?>
    <div class="wrap">
        <h1>Configuración de APIs</h1>
        <form method="post" action="">

            <h2>POS</h2>
            <table class="form-table">
                <tr>
                    <th><label for="pos_url">Endpoint POS</label></th>
                    <td><input type="text" id="pos_url" name="pos_url" value="<?php echo esc_attr($pos_url); ?>"
                            class="regular-text" required></td>
                </tr>

            </table>

            <h2>Ganamás redimir</h2>
            <table class="form-table">
                <tr>
                    <th><label for="api_connect_url">Endpoint</label></th>
                    <td><input type="text" id="api_connect_url" name="api_connect_url"
                            value="<?php echo esc_attr($api_connect_url); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="api_connect_username">Usuario</label></th>
                    <td><input type="text" id="api_connect_username" name="api_connect_username"
                            value="<?php echo esc_attr($api_connect_username); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="api_connect_password">Contraseña</label></th>
                    <td><input type="text" id="api_connect_password" name="api_connect_password"
                            value="<?php echo esc_attr($api_connect_password); ?>" class="regular-text" required></td>
                </tr>
            </table>

            <h2>API EIB2BPro</h2>
            <table class="form-table">
                <tr>
                    <th><label for="api_eib2bpro_url">Endpoint</label></th>
                    <td><input type="text" id="api_eib2bpro_url" name="api_eib2bpro_url"
                            value="<?php echo esc_attr($api_eib2bpro_url); ?>" class="regular-text" required></td>
                </tr>
            </table>

            <h2>API Multiservicio</h2>
            <table class="form-table">
                <tr>
                    <th><label for="api_multiservicio_create_url">Crear Productos (Endpoint)</label></th>
                    <td><input type="text" id="api_multiservicio_create_url" name="api_multiservicio_create_url"
                            value="<?php echo esc_attr($api_multiservicio_create_url); ?>" class="regular-text" required>
                    </td>
                    *Para importacion de tags
                </tr>
                <tr>
                    <th><label for="api_multiservicio_stock_url">Mostrar Stock (Endpoint)</label></th>
                    <td><input type="text" id="api_multiservicio_stock_url" name="api_multiservicio_stock_url"
                            value="<?php echo esc_attr($api_multiservicio_stock_url); ?>" class="regular-text" required>
                    </td>
                    *Para el stock en tiempo real
                </tr>
            </table>

            <h2>API Rappi</h2>
            <table class="form-table">
                <tr>
                    <th><label for="api_rappi_validate_url">Validar Orden (Endpoint)</label></th>
                    <td><input type="text" id="api_rappi_validate_url" name="api_rappi_validate_url"
                            value="<?php echo esc_attr(get_option('custom_plugin_api_rappi_validate_url', '')); ?>"
                            class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="api_rappi_create_url">Crear Orden (Endpoint)</label></th>
                    <td><input type="text" id="api_rappi_create_url" name="api_rappi_create_url"
                            value="<?php echo esc_attr(get_option('custom_plugin_api_rappi_create_url', '')); ?>"
                            class="regular-text" required></td>
                </tr>
            </table>

            <h2>API Salesforce</h2>
            <table class="form-table">
                <tr>
                    <th><label for="api_salesforce_url">Endpoint</label></th>
                    <td><input type="text" id="api_salesforce_url" name="api_salesforce_url"
                            value="<?php echo esc_attr($api_salesforce_url); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="api_salesforce_clientsecret">Client Secret</label></th>
                    <td><input type="text" id="api_salesforce_clientsecret" name="api_salesforce_clientsecret"
                            value="<?php echo esc_attr($api_salesforce_clientsecret); ?>" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="api_salesforce_clientid">Cliente ID</label></th>
                    <td><input type="text" id="api_salesforce_clientid" name="api_salesforce_clientid"
                            value="<?php echo esc_attr($api_salesforce_clientid); ?>" class="regular-text" required></td>
                </tr>
            </table>

            <h2>API Clearsales</h2>
            <table class="form-table">
                <tr>
                    <th><label for="api_clearsales_url">Endpoint</label></th>
                    <td><input type="text" id="api_clearsales_url" name="api_clearsales_url"
                            value="<?php echo esc_attr($api_clearsales_url); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="api_clearsales_apikey">API KEY</label></th>
                    <td><input type="text" id="api_clearsales_apikey" name="api_clearsales_apikey"
                            value="<?php echo esc_attr($api_clearsales_apikey); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="api_clearsales_clientsecret">Client Secret</label></th>
                    <td><input type="text" id="api_clearsales_clientsecret" name="api_clearsales_clientsecret"
                            value="<?php echo esc_attr($api_clearsales_clientsecret); ?>" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="api_clearsales_clientid">Cliente ID</label></th>
                    <td><input type="text" id="api_clearsales_clientid" name="api_clearsales_clientid"
                            value="<?php echo esc_attr($api_clearsales_clientid); ?>" class="regular-text" required></td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="save_settings" id="save_settings" class="button button-primary"
                    value="Guardar cambios">
            </p>
        </form>
    </div>
    <?php
}

/********Configuracion de URL API********/
//actualiza el checkout forzado

// // Eliminar el mensaje de su posición actual
// remove_action('woocommerce_review_order_before_payment', 'wc_print_notices', 10);

// // Agregar el mensaje arriba del formulario de checkout
// add_action('woocommerce_before_checkout_form', 'wc_print_notices', 10);
function custom_woocommerce_checkout_styles()
{
    $custom_css = "
        /* Asegurar que los mensajes se posicionen bien y sean visibles */
        .woocommerce-error, .woocommerce-message {
            display: block;
            margin: 20px auto;
            width: 100%;
            max-width: 800px; /* Evita que el mensaje sea demasiado ancho */
            text-align: center;
            font-weight: bold;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Estilo específico para mensajes de error */
        .woocommerce-error {
            background-color: #ff4d4d; /* Rojo */
            color: white;
            border-left: 5px solid #cc0000;
        }

        /* Estilo para mensajes de éxito */
        .woocommerce-message {
            background-color: #4CAF50; /* Verde */
            color: white;
            border-left: 5px solid #2e7d32;
        }

        /* Asegurar que el mensaje esté arriba del checkout */
        .woocommerce-notices-wrapper {
            order: -1; /* Mueve los mensajes arriba del formulario */
        }
    ";

    wp_add_inline_style('woocommerce-general', $custom_css);
}
add_action('wp_enqueue_scripts', 'custom_woocommerce_checkout_styles');
/*
 *  permisos para usuario gestor de tiendas y pueda editar contenido
 */
function add_custom_permissions_for_shop_manager()
{
    // Obtén el rol 'shop_manager'
    $shop_manager = get_role('shop_manager');

    if ($shop_manager) {
        // Permisos para Páginas
        $shop_manager->add_cap('edit_pages');
        $shop_manager->add_cap('edit_others_pages');
        $shop_manager->add_cap('edit_published_pages');
        $shop_manager->add_cap('publish_pages');
        $shop_manager->add_cap('delete_pages');
        $shop_manager->add_cap('delete_others_pages');
        $shop_manager->add_cap('delete_published_pages');

        // Permisos para Posts (Entradas)
        $shop_manager->add_cap('edit_posts');
        $shop_manager->add_cap('edit_others_posts');
        $shop_manager->add_cap('edit_published_posts');
        $shop_manager->add_cap('publish_posts');
        $shop_manager->add_cap('delete_posts');
        $shop_manager->add_cap('delete_others_posts');
        $shop_manager->add_cap('delete_published_posts');

        // Permisos Elementor (si el error 500 está relacionado con restricciones de Elementor)
        $shop_manager->add_cap('edit_elementor');
        $shop_manager->add_cap('edit_elementor_content'); // Para editar contenido Elementor
    }
}
add_action('admin_init', 'add_custom_permissions_for_shop_manager');

function custom_loader_styles()
{
    ?>
    <style>
        #customLoader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loader-container {
            text-align: center;
        }

        .loader {
            border: 6px solid #f3f3f3;
            border-top: 6px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
    <?php
}
add_action('wp_head', 'custom_loader_styles');

// Programar la tarea cron si no existe
function custom_clear_cart_schedule()
{
    if (!wp_next_scheduled('custom_clear_cart_event')) {
        wp_schedule_event(time(), 'hourly', 'custom_clear_cart_event');
    }
}
add_action('wp', 'custom_clear_cart_schedule');

// Función para limpiar los carritos inactivos
function custom_clear_cart()
{
    if (!WC()->session) {
        wc_load_cart();
    }

    if (!WC()->session) {
        return; // Si después de cargar el carrito sigue sin existir la sesión, salir.
    }

    $cutoff_time = time() - (60 * 60 * 2); // 2 horas de inactividad

    $sessions = WC()->session->get_session_data();
    foreach ($sessions as $key => $session) {
        if (isset($session['cart']) && isset($session['last_activity'])) {
            if ($session['last_activity'] < $cutoff_time) {
                WC()->session->destroy_session($key);
            }
        }
    }
}

add_filter('woocommerce_quantity_input_args', 'ajustar_input_value_en_carrito', 20, 2);
function ajustar_input_value_en_carrito($args, $product)
{
    // Solo aplica en el carrito
    if (is_cart()) {
        // Obtén el carrito completo
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if ($cart_item['data']->get_id() === $product->get_id()) {
                $args['input_value'] = $cart_item['quantity']; // Fuerza la cantidad real
                break;
            }
        }
    }

    return $args;
}

add_filter('woocommerce_quantity_input_args', 'forzar_maximo_stock_por_tienda', PHP_INT_MAX, 2);

function forzar_maximo_stock_por_tienda($args, $product)
{
    if (!is_cart())
        return $args;

    $term_id = $_COOKIE['wcmlim_selected_location_termid'] ?? null;
    if (!$term_id)
        return $args;

    foreach (WC()->cart->get_cart() as $cart_item) {
        if ($cart_item['data']->get_id() === $product->get_id()) {
            $stock_real = get_post_meta($cart_item['product_id'], "wcmlim_stock_at_{$term_id}", true);
            if (!empty($stock_real)) {
                $args['max_value'] = floatval($stock_real); // ¡Reescribe con fuerza!

                add_action('woocommerce_before_quantity_input_field', function () use ($cart_item, $stock_real) {
                    echo "<!-- MAX FINAL {$cart_item['product_id']}: {$stock_real} -->";
                });
            }
            break;
        }
    }

    return $args;
}

// Agregar el hook para llamar la función vía AJAX
add_action('wp_ajax_export_stock_csv', 'export_stock_csv');
add_action('`wp_ajax_nopriv_export_stock_csv`', 'export_stock_csv');
add_action('custom_clear_cart_event', 'custom_clear_cart');
function export_stock_csv()
{
    global $wpdb;

    // Definir la consulta SQL
    $query = "SELECT 
    sku.meta_value AS product_sku,
    p.post_title AS product_name,
    pm.meta_key AS stock_meta_key,
    pm.meta_value AS stock_value,
    t.name AS store_name,
    pm_centro_location.meta_value AS centro_location_value 
FROM 
    wp_postmeta pm
JOIN 
    wp_posts p ON pm.post_id = p.ID
JOIN 
    wp_terms t ON REPLACE(pm.meta_key, 'wcmlim_stock_at_', '') = t.term_id
LEFT JOIN 
    wp_postmeta sku ON sku.post_id = p.ID AND sku.meta_key = '_sku' 
LEFT JOIN 
    wp_termmeta pm_centro_location ON pm_centro_location.meta_key = 'centro_location'
    AND pm_centro_location.term_id = t.term_id
WHERE 
    pm.meta_key LIKE 'wcmlim_stock_at_%'
    AND pm.meta_value IS NOT NULL
    AND pm.meta_value <> ''
    AND pm.meta_value > 0
    AND t.name LIKE '%CMT%';";

    // Obtener los resultados
    $results = $wpdb->get_results($query, ARRAY_A);

    if (empty($results)) {
        wp_die('No se encontraron datos para exportar.');
    }

    // Definir nombre del archivo CSV
    $filename = 'stock_export_' . date('Y-m-d') . '.csv';

    // Configurar cabeceras para descarga
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Abrir el output en modo escritura
    $output = fopen('php://output', 'w');

    // Escribir encabezados del CSV (se agregaron centro_value y centro_location_value)
    fputcsv($output, array('SKU', 'Product Name', 'Meta Key', 'Stock Value', 'Centro'));

    // Escribir filas con los datos
    foreach ($results as $row) {
        fputcsv($output, array(
            $row['product_sku'] ?? '',
            $row['product_name'],
            $row['stock_meta_key'],
            $row['stock_value'],
            $row['centro_location_value'] ?? 'N/A'
        ));
    }

    fclose($output);
    exit;
}

add_action('init', function () {
    if (isset($_GET['auto_export_stock']) && $_GET['auto_export_stock'] === '1') {
        export_stock_csv();
    }
});

/**permisos para plugin redirecciones seo*/

add_filter('redirection_role', function ($role) {
    if (current_user_can('administrator') || current_user_can('wpseo_manager')) {
        return 'edit_posts'; // Permiso base para acceder al plugin
    }
    return 'manage_options'; // Restringe acceso a otros roles
});

add_filter('redirection_capability_check', function ($capability, $permission_name) {
    if (current_user_can('administrator')) {
        return 'manage_options'; // Administradores tienen acceso completo
    }

    if (current_user_can('wpseo_manager')) {
        // SEO Manager solo puede ver y agregar redirecciones
        if (
            in_array($permission_name, [
                'redirection_cap_redirect_manage',
                'redirection_cap_redirect_add'
            ])
        ) {
            return 'edit_posts';
        }
    }

    return 'manage_options'; // Restringe acceso a otras funciones
}, 10, 2);


/**
 * ============================================================
 * Checkout – Lógica personalizada para WooCommerce
 * ------------------------------------------------------------
 * Archivo dedicado exclusivamente a:
 * - Incluir validaciones en JS personalizadas para el checkout
 * - Cargar validaciones en PHP solo si WooCommerce está activo
 * 
 * Autor: Dens - Spark
 * Fecha: 05/05/2025
 * ============================================================
 */

/**
 * *******************************************************
 * Encolar archivo JavaScript personalizado para checkout
 * *******************************************************
 * Solo se carga en la página de checkout
 */
function enqueue_custom_checkout_js(): void
{
    if (is_checkout()) {
        wp_enqueue_script(
            'custom-js-script',
            get_stylesheet_directory_uri() . '/assets/js/woocommerce/checkout/validation-v8.js',
            array('jquery'),     // Dependencia
            '1.0.8',             // Versión
            true                 // Cargar en footer
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_custom_checkout_js');
/**
 * *******************************************************
 * Cargar validaciones en PHP para WooCommerce Checkout
 * *******************************************************
 * Solo si WooCommerce está activo. El archivo incluye:
 * - Filtros de mensaje personalizado
 * - Validaciones específicas para campos del formulario
 */
if (class_exists('WooCommerce')) {
    require_once get_stylesheet_directory() . '/includes/woocommerce/checkout/validation.php';
}

/**
 * ============================================================
 * Lógica personalizada – Limpieza del carrito WooCommerce (REST API)
 * ------------------------------------------------------------
 * Este archivo está dedicado exclusivamente a:
 * - Registrar un endpoint REST personalizado para vaciar el carrito
 *   de WooCommerce mediante una petición POST desde el frontend.
 * - Permitir que usuarios logueados o no logueados puedan limpiar
 *   su carrito de manera segura.
 *
 * ✅ Optimizado para rendimiento y compatibilidad moderna.
 *
 * Desarrollo específico para complementar la funcionalidad del plugin:
 * "WooCommerce".
 *
 * Autor: Dens - Spark
 * Fecha: 07/05/2025
 * ============================================================
 */

/**
 * Registrar la ruta personalizada en la API REST de WordPress
 * Endpoint: /wp-json/carnemart/v1/clear-cart
 *
 * Este endpoint permite limpiar el carrito del usuario actual.
 * Está habilitado para usuarios logueados y no logueados (visitantes).
 *
 * @link https://developer.wordpress.org/rest-api/
 */
add_action('rest_api_init', function (): void {
    register_rest_route('carnemart/v1', '/clear-cart', [
        'methods' => 'POST',
        'callback' => 'clear_cart_api_handler',
        'permission_callback' => '__return_true' // Público, sin autenticación requerida
    ]);
});

/**
 * Incluir el manejador de la lógica que vacía el carrito.
 * Se encuentra en: includes/woocommerce/cart/custom-clear-cart.php
 */
require_once get_stylesheet_directory() . '/includes/woocommerce/cart/custom-clear-cart.php';


add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'block-inspections-js',
        get_stylesheet_directory_uri() . '/assets/js/web/blockInspection.js',
        array('jquery'),
        '1.0.0',
        true
    );
});

//funcion para que funcione igual que el POS de bafar el calculo
add_filter('woocommerce_coupon_get_discount_amount', 'wc_pos_style_per_unit_coupon', 10, 5);
function wc_pos_style_per_unit_coupon($discount, $discounting_amount, $cart_item, $single, $coupon)
{

    // Solo interferimos en cupones de porcentaje
    if ($coupon->get_discount_type() !== 'percent') {
        return $discount;
    }

    // 1) Precio unitario del producto (sin impuestos)
    $product = $cart_item['data'];
    $qty = $cart_item['quantity'];
    // Elastic: si quieres precio con impuesto, usa get_price() en lugar de get_price_excluding_tax()
    $unit_price = round($product->get_price_excluding_tax(), 2);

    // 2) Porcentaje del cupón (por ejemplo, 15 → 0.15)
    $pct = floatval($coupon->get_amount()) / 100;

    // 3) Descuento **unitario**, redondeado a 2 decimales
    $unit_discount = round($unit_price * $pct, 2);

    // 4) Descuento total de la línea = unit_discount × cantidad, redondeado a 2 decimales
    $line_discount = round($unit_discount * $qty, 2);

    return $line_discount;
}



/**
 * Plugin Name: CarneMart – Precios por Tienda en Loop
 * Description: Sustituye el badge y el precio por defecto de WooCommerce en el loop de productos con precios y ofertas específicas
 *              según la tienda seleccionada por cookie. Añade un badge “OFERTÓN” y muestra el precio regular tachado junto al precio de oferta.
 * Version:     1.0
 * Author:      Dens
 * Date:        2025-05-19
 */

/**
 * 1) Quitamos el badge y el precio por defecto de WooCommerce en el loop de productos.
 */
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);

/**
 * 1) Enganchamos nuestro badge “OFERTÓN” usando la misma lógica de precios.
 */
//add_action('woocommerce_before_shop_loop_item_title', 'bafar_custom_sale_flash', 10);
// function bafar_custom_sale_flash()
// {
//     global $product;
//     $data = bafar_get_price_data($product);

//     if ($data['has_offer']) {
//         echo '<span class="onsale" style="
//             position:absolute;
//             top:0;
//             left:0;
//             background:#e53935;
//             color:#fff;
//             padding:0.5em 1em;
//             font-weight:bold;
//             text-transform:uppercase;
//             z-index:10;
//         ">OFERTÓN</span>';
//     }
// }


/* ===== Helpers ===== */
if (!function_exists('sb_get_current_store_term_id')) {
    function sb_get_current_store_term_id(): int
    {
        if (!empty($_COOKIE['wcmlim_selected_location_termid']) && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined') {
            return (int) $_COOKIE['wcmlim_selected_location_termid'];
        }
        return 0;
    }
}

if (!function_exists('sb_array_first_kv')) {
    function sb_array_first_kv(array $arr): array
    {
        if (function_exists('array_key_first')) {
            $k = array_key_first($arr);
            return [$k, $arr[$k]];
        }
        // Fallback PHP <7.3
        $v = reset($arr);
        $k = key($arr);
        return [$k, $v];
    }
}
if (!function_exists('sb_parse_qty_from_key')) {
    // "5.50 (95.90) 🔥 Promo" -> 5.5
    function sb_parse_qty_from_key(string $key): ?float
    {
        if (preg_match('/^\s*([\d\.,]+)/', $key, $m)) {
            return (float) str_replace(',', '.', $m[1]);
        }
        return null;
    }
}
if (!function_exists('sb_parse_paren_price_from_key')) {
    // "5.50 (95.90) 🔥 Promo" -> 95.90 ; "14.00 (Precio regular)" -> null
    function sb_parse_paren_price_from_key(string $key): ?float
    {
        if (preg_match('/\(([\d\.,]+)\)/', $key, $m)) {
            return (float) str_replace(',', '.', $m[1]);
        }
        return null;
    }
}

if (!function_exists('sb_get_tiers_meta')) {
    // Busca el meta de tiers. Ajusta candidatos si usas otro nombre.
    function sb_get_tiers_meta(int $product_id, int $term_id): array
    {
        $customer_group = get_customer_group_from_location($term_id);
        // Tu código original usa este meta:
        $candidates = ["eib2bpro_price_tiers_group_{$customer_group}"];
        foreach ($candidates as $meta_key) {
            $json = get_post_meta($product_id, $meta_key, true);
            if ($json) {
                $arr = json_decode($json, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($arr) && !empty($arr)) {
                    return $arr; // ["5.50 (95.90) ..."=>95.9, "14.00 (Precio regular)"=>41.5]
                }
            }
        }
        return [];
    }
}

if (!function_exists('sb_format_qty')) {
    function sb_format_qty(float $q): string
    {
        $is_int = abs($q - round($q)) < 0.00001;
        return $is_int ? (string) (int) round($q) : rtrim(rtrim(number_format($q, 3, '.', ''), '0'), '.');
    }
}

/* ===== Render precio + volumen ===== */
if (!function_exists('bafar_custom_loop_price')) {
    add_action('woocommerce_after_shop_loop_item_title', 'bafar_custom_loop_price', 10);

    if (!function_exists('sb_get_first_step')) {
        function sb_get_first_step($tiers)
        {
            if (empty($tiers)) {
                return null;
            }

            $first_key = array_key_first($tiers);
            if ($first_key === null) {
                return null;
            }

            // Extract the first numeric value from the key
            if (preg_match('/(\d+(?:\.\d+)?)/', $first_key, $matches)) {
                return (float) $matches[1];
            }

            return null;
        }
    }

    function bafar_custom_loop_price()
    {
        global $product;
        if (empty($product) || !($product instanceof WC_Product))
            return;

        $pid = $product->get_id();
        $unit = get_post_meta($pid, 'ri_quantity_step_label', true);
        $unit_txt = $unit ? ' <span class="por-label" style="color:#6c757d;font-size:15px;">por <b>' . esc_html($unit) . '</b></span>' : '';

        $term_id = sb_get_current_store_term_id();

        // Precio base por tienda o nativo
        $price_base_tienda = $term_id ? get_post_meta($pid, "wcmlim_regular_price_at_{$term_id}", true) : '';
        $price_base_tienda = is_numeric($price_base_tienda) ? (float) $price_base_tienda : null;
        $price_base_nativo = (float) $product->get_regular_price();

        // Tiers (array asociativo "key" => price)
        $tiers = $term_id ? sb_get_tiers_meta($pid, $term_id) : [];

        $show_del = false;
        $regular = null;
        $sale = null;

        $showOfert = false;

        if (!empty($tiers)) {
            // Tomar PRIMER tier (clave + valor)
            [$first_key, $first_val] = sb_array_first_kv($tiers);
            $first_val = is_numeric($first_val) ? (float) $first_val : null;

            // Precio entre paréntesis en la clave => regular del tier
            $paren_reg = sb_parse_paren_price_from_key((string) $first_key);

            $first_step = sb_get_first_step($tiers);
            $step_meta = get_post_meta($pid, 'product_step', true); // p.ej. "0.5"


            if ($first_step <= $step_meta) {
                $showOfert = true;
            }

            // 1) Regular
            if ($paren_reg !== null && $paren_reg > 0) {
                $regular = $paren_reg;
            } else {
                // si no hay numérico entre paréntesis, usamos el base de tienda si existe, si no nativo
                $regular = $price_base_tienda !== null ? $price_base_tienda : $price_base_nativo;
            }

            // 2) Sale (el valor del primer tier, solo si es menor al regular)
            if ($first_val !== null && $regular !== null && $first_val < $regular) {
                $sale = $first_val;
                $show_del = true;
            }

        } else {
            // Sin tiers: usa base tienda o nativo
            $regular = $price_base_tienda !== null ? $price_base_tienda : $price_base_nativo;
        }

        // Pintar precio
        echo '<span class="price"><span class="woocommerce-Price-amount amount"><bdi>';
        if ($show_del && $regular && $showOfert) {
            echo '<del style="color:#888;margin-right:.5em;">' . wc_price($regular) . '</del>';
            echo '<ins style="color:#0866FD;text-decoration:none;font-weight:600;">' . wc_price($sale) . '</ins>';
        } else {
            echo '<ins style="color:#0866FD;text-decoration:none;font-weight:600;">' . wc_price($regular) . '</ins>';
        }
        echo $unit_txt . '</bdi></span></span>';

        // ===== Acordeón "Ver precios por volumen" (del SEGUNDO tier en adelante) =====
        if (!empty($tiers)) {
            $first_step = sb_get_first_step($tiers);
            $step_meta = get_post_meta($pid, 'product_step', true); // p.ej. "0.5"

            $saltar_first_tier = ($first_step <= $step_meta);

            // Cortamos el primer tier
            $rest = $saltar_first_tier ? array_slice($tiers, 1, null, true) : $tiers;
            if (!empty($rest)) {
                static $css_done = false;
                if (!$css_done) {
                    echo '<style>
                    .bafar-tiers{margin:.25rem 0 .5rem}
                    .bafar-tiers>summary{cursor:pointer;color:#0866FD;font-weight:600;list-style:none;outline:none}
                    .bafar-tiers>summary::-webkit-details-marker{display:none}
                    .bafar-tiers-table{width:100%;margin-top:.35rem;border-collapse:collapse;font-size:.9rem}
                    .bafar-tiers-table td{padding:.15rem 0}
                    .bafar-tiers-table td.qty{color:#555}
                    .bafar-tiers-table td.price{text-align:right;font-weight:600}
                    .bafar-tiers-bullet{display:inline-block;width:.45rem;height:.45rem;border-radius:50%;background:#0866FD;margin-right:.35rem;vertical-align:middle}
                    </style>';
                    $css_done = true;
                }

                $id = 'tiers-' . $pid;
                echo '<details class="bafar-tiers" id="' . esc_attr($id) . '">';
                echo '<summary>Ver precios por volumen</summary>';
                echo '<table class="bafar-tiers-table" aria-describedby="' . esc_attr($id) . '">';
                foreach ($rest as $k => $v) {
                    if (!is_numeric($v))
                        continue;
                    $v = (float) $v;

                    $qty = sb_parse_qty_from_key((string) $k);
                    $paren_txt = null;
                    // Si quieres mostrar también el texto entre paréntesis (cuando no es numérico), puedes extraerlo:
                    if (preg_match('/\(([^)]+)\)/', (string) $k, $m)) {
                        // Mostrar solo si NO es un número (para no duplicar información)
                        if (!preg_match('/^[\d\.,]+$/', $m[1])) {
                            $paren_txt = trim($m[1]);
                        }
                    }

                    echo '<tr>';
                    echo '<td class="qty"><span class="bafar-tiers-bullet"></span>desde ' . esc_html(sb_format_qty((float) $qty)) . ($unit ? ' ' . esc_html($unit) : '');
                    if ($paren_txt) {
                        echo '<div style="color:#999;font-size:.8rem;margin-top:2px;">' . esc_html($paren_txt) . '</div>';
                    }
                    echo '</td>';
                    echo '<td class="price">' . wc_price($v) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                echo '</details>';
            }
        }
    }
}



function sparklabs_filtrar_busqueda_productos($query)
{
    if (!is_admin() && $query->is_main_query() && $query->is_search()) {
        $query->set('post_type', 'product');
    }
}
add_action('pre_get_posts', 'sparklabs_filtrar_busqueda_productos');

add_action('pre_get_posts', 'ocultar_productos_precio_cero_en_tienda', 20);
function ocultar_productos_precio_cero_en_tienda(\WP_Query $query)
{
    // 1) Solo front, query principal
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // 2) Contextos donde aplicarlo
    if (
        !(is_shop()
            || is_product_category()
            || is_product_tag()
            || is_search()
            || is_post_type_archive('product')
        )
    ) {
        return;
    }

    // 3) Recuperamos cookie de sucursal
    $term_id = isset($_COOKIE['wcmlim_selected_location_termid'])
        && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined'
        ? intval($_COOKIE['wcmlim_selected_location_termid'])
        : 0;

    // 4) Preparamos (o rescatamos) el meta_query
    $meta_query = $query->get('meta_query', []);
    // Le decimos que todas las condiciones sean AND
    $meta_query['relation'] = 'AND';

    // 5) Lógica de filtrado:
    if ($term_id) {
        // — Stock en tienda > 0
        $meta_query[] = [
            'key' => "wcmlim_stock_at_{$term_id}",
            'value' => 0,
            'compare' => '>',
            'type' => 'NUMERIC',
        ];
        // — Precio regular en tienda > 0
        $meta_query[] = [
            'key' => "wcmlim_regular_price_at_{$term_id}",
            'value' => 0,
            'compare' => '>',
            'type' => 'NUMERIC',
        ];
        $meta_query[] = [
            'key' => '_regular_price',
            'value' => 0,
            'compare' => '>',
            'type' => 'NUMERIC',
        ];
    }
    // — Sin tienda: precio regular base > 0
    $meta_query[] = [
        'key' => '_regular_price',
        'value' => 0,
        'compare' => '>',
        'type' => 'NUMERIC',
    ];


    // 6) Asignamos el meta_query modificado
    $query->set('meta_query', $meta_query);
}



function keikos_qty_decimal_script()
{
    if (is_product_category()) {
        ?>
        <script>
            document.addEventListener('click', function (e) {
                if (e.target.classList.contains('plus') || e.target.classList.contains('minus')) {
                    const button = e.target;
                    const quantityDiv = button.closest('.quantity');
                    const input = quantityDiv.querySelector('input[type="number"]');

                    let currentValue = parseFloat(input.value);
                    if (!isNaN(currentValue)) {
                        input.value = (Math.round(currentValue * 100) / 100).toFixed(2);
                    }
                }
            });
        </script>
        <?php
    }
}
add_action('wp_footer', 'keikos_qty_decimal_script');


/**
 * Custom endpoint para obtener los metas de una orden de WooCommerce
 */
/**
 * Custom endpoint abierto para obtener los metas de una orden de WooCommerce
 */
add_action('rest_api_init', function () {
    register_rest_route('wc/v3', '/public/order-meta/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'get_wc_order_meta_public',
        'args' => array(
            'id' => array(
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                }
            ),
        ),
    ));
});

/**
 * Callback para el endpoint público de metas de orden
 */
function get_wc_order_meta_public(WP_REST_Request $request)
{
    $order_id = $request->get_param('id');
    $order = wc_get_order($order_id);

    if (!$order) {
        return new WP_Error('order_not_found', 'Orden no encontrada', array('status' => 404));
    }

    // Obtener todos los meta datos de la orden
    $meta_data = $order->get_meta_data();
    $formatted_meta = array();

    foreach ($meta_data as $meta) {
        $formatted_meta[$meta->key] = $meta->value;
    }

    // Añadir información básica de la orden
    $response = array(
        'order_id' => $order_id,
        'status' => $order->get_status(),
        'meta_data' => $formatted_meta,
    );

    return new WP_REST_Response($response, 200);
}



/*
 *
 * Bloqueo de venta de productos en 0.00 pesos.
 *
 */
add_action('wp_footer', 'bloquear_si_tier_price_es_cero');
function bloquear_si_tier_price_es_cero()
{
    if (is_product()):
        ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const rows = document.querySelectorAll('.eib2bpro_price_tiers_table .eib2bpro_tier_price');
                let hayPrecioCero = false;

                rows.forEach(function (cell) {
                    const text = cell.innerText.trim().replace('$', '').replace(',', '.');
                    if (parseFloat(text) === 0) {
                        hayPrecioCero = true;
                    }
                });

                if (hayPrecioCero) {
                    const addToCart = document.querySelector('form.cart');
                    if (addToCart) {
                        addToCart.innerHTML = "<div class='woocommerce-error'>Este producto tiene un precio inválido y no puede ser comprado en este momento.</div>";
                    }
                }
            });
        </script>
        <?php
    endif;
}
// add_filter('woocommerce_add_to_cart_validation', 'validar_precio_tiers_en_add_to_cart', 10, 5);
// function validar_precio_tiers_en_add_to_cart($passed, $product_id, $quantity, $variation_id = null, $variations = null)
// {
//     // Obtener todos los metacampos
//     $meta_data = get_post_meta($product_id);

//     foreach ($meta_data as $key => $value) {
//         // Revisar solo los que empiezan con eib2bpro_price_tiers_group_
//         if (strpos($key, 'eib2bpro_price_tiers_group_') === 0) {
//             $json = json_decode($value[0], true);
//             if (is_array($json)) {
//                 foreach ($json as $qty => $price) {
//                     if (floatval(trim($price)) <= 0) {
//                         wc_add_notice('Este producto tiene un precio inválido por volumen. No se puede añadir al carrito.', 'error');
//                         return false;
//                     }
//                 }
//             }
//         }
//     }

//     return $passed;
// }
/*
 *
 * Bloqueo de venta de productos en 0.00 pesos: eof
 *
 */
/*** Canonical en buscador ***/
add_action('wpseo_head', 'agregar_canonical_a_resultados_busqueda');

function agregar_canonical_a_resultados_busqueda()
{
    if (is_search()) {
        $url_canonical = home_url('/?s=' . urlencode(get_search_query()));
        echo '<link rel="canonical" href="' . esc_url($url_canonical) . '"/>' . "\n";
    }
}
/*** Canonical en buscador ***/

add_action('wp_footer', 'forzar_traducciones_con_js');
function forzar_traducciones_con_js()
{
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const reemplazos = {
                'Billing Details': 'Detalles de facturación',
                'Order Review': 'Resumen del pedido',
                'Back To Cart': 'Volver al carrito',
                'Ship to a different address?': '¿Enviar a una dirección diferente?',
                'Payment Method': 'Métodos de pago',
                'Coupon code': 'Código de cupón'
            };

            function traducirNodo(node) {
                if (node.nodeType === Node.TEXT_NODE) {
                    let texto = node.nodeValue;
                    for (const [ingles, espanol] of Object.entries(reemplazos)) {
                        if (texto.includes(ingles)) {
                            node.nodeValue = texto.replace(ingles, espanol);
                        }
                    }
                } else if (node.nodeType === Node.ELEMENT_NODE) {
                    // Traducir atributos comunes
                    const atributos = ['placeholder', 'title', 'aria-label'];
                    atributos.forEach(attr => {
                        if (node.hasAttribute(attr)) {
                            let valor = node.getAttribute(attr);
                            for (const [ingles, espanol] of Object.entries(reemplazos)) {
                                if (valor.includes(ingles)) {
                                    node.setAttribute(attr, valor.replace(ingles, espanol));
                                }
                            }
                        }
                    });

                    // Seguir traduciendo el contenido de los nodos hijos
                    node.childNodes.forEach(traducirNodo);
                }
            }

            traducirNodo(document.body);
        });
    </script>
    <?php
}

/**
 * =========================================================================
 * Lógica personalizada – Reglas dinámicas de precios por tienda en WooCommerce
 * -------------------------------------------------------------------------
 * Este archivo gestiona la lógica de cálculo de precios en WooCommerce 
 * basada en:
 * 
 * - Tienda seleccionada por el usuario (cookie `wcmlim_selected_location_termid`)
 * - Precio regular personalizado por tienda (metakey dinámico)
 * - Aplicación de precios en oferta (ofertón) definidos por tienda
 * - Descuentos directos por SKU con límite de cantidad
 * - Precios escalonados por grupo de tienda (tiers)
 *
 * ✨ Prioridad de reglas aplicadas:
 *    1. Precio ofertón por tienda
 *    2. Descuento directo configurado
 *    3. Precio escalonado por grupo
 *    4. Precio regular personalizado (fallback global)
 *
 * ✅ Compatible con carrito, checkout y vista previa de montos.
 * ✅ Pensado para integraciones multilocalización y promociones.
 *
 * Autor: Dens Spark
 * Fecha: 05/06/2025 a 06/06/2025
 * =========================================================================
 */

/*
 *
 * Descuentos promociones junio
 *
 */
function obtener_configuracion_descuentos()
{
    return [];
}

/**
 * ============================================================
 * Lógica personalizada – Aplicar precios dinámicos por tienda
 * ------------------------------------------------------------
 * Este hook ejecuta la lógica que ajusta los precios de los
 * productos en el carrito según la tienda seleccionada mediante
 * cookie (`wcmlim_selected_location_termid`). Considera:
 * 
 * - Descuentos directos por SKU configurados manualmente.
 * - Precios base personalizados por ubicación.
 * - Escalados de precio por grupo de cliente.
 * - Precios de promoción (OFERTÓN).
 * ✅ Compatible con lógica de promociones por cantidad y
 * estructura multitienda.
 *
 * Desarrollo para el entorno: WooCommerce multitienda.
 * Autor: Dens - Spark
 * Fecha: 06/06/2025
 * ============================================================
 */
add_action(
    'woocommerce_before_calculate_totals',
    'apply_dynamic_discounts_by_location',
    PHP_INT_MAX - 1,
    1
);
require_once get_stylesheet_directory() . '/includes/woocommerce/checkout/before_calculate_totals.php';

/**
 * ============================================================
 * Utilidad – Obtener grupo de cliente según ubicación
 * ------------------------------------------------------------
 * Extrae el grupo de cliente (`customer_group`) de un término
 * de ubicación (term_id), necesario para aplicar escalados 
 * dinámicos de precio por grupo en el carrito.
 *
 * Desarrollo para el entorno: WooCommerce multitienda.
 * Autor: Dens - Spark
 * Fecha: 06/07/2025
 * ============================================================
 */
function get_customer_group_from_location($term_id)
{
    $meta = get_term_meta($term_id);
    return $meta['customer_group'][0] ?? null;
}

/**
 * ============================================================
 * Visualización – Mostrar precio correcto por unidad (cart)
 * ------------------------------------------------------------
 * Reemplaza el precio por unidad que WooCommerce muestra en
 * el carrito. Calcula el precio correcto según:
 * 
 * - Precios personalizados por tienda.
 * - Descuentos dinámicos por SKU.
 * - Precio OFERTÓN por tienda.
 * - Precios escalonados por grupo.
 *
 * ✅ Visual coherente con la lógica real del carrito.
 *
 * Autor: Dens - Spark
 * Fecha: 06/07/2025
 * ============================================================
 */
add_filter('woocommerce_cart_item_price', 'display_correct_price_in_cart', 10, 3);
require_once get_stylesheet_directory() . '/includes/woocommerce/cart/cart_item_price.php';

/**
 * ============================================================
 * Visualización – Mostrar subtotal correcto (checkout)
 * ------------------------------------------------------------
 * Ajusta el subtotal por ítem en el resumen del checkout,
 * aplicando la misma lógica de cálculo que en el carrito:
 * 
 * - Precios por ubicación (cookie).
 * - Descuentos personalizados.
 * - Escalados por grupo de cliente.
 * - Precios de promoción (OFERTÓN).
 *
 * ✅ Alineado con la lógica interna del carrito.
 *
 * Autor: Dens - Spark
 * Fecha: 06/07/2025
 * ============================================================
 */
add_filter('woocommerce_cart_item_subtotal', 'display_correct_subtotal_checkout', 10, 3);
require_once get_stylesheet_directory() . '/includes/woocommerce/checkout/cart_item_subtotal.php';

/**
 * ============================================================
 * Visualización – Mostrar etiquetas dinámicas en el producto
 * ------------------------------------------------------------
 * Agrega etiquetas visuales a cada producto en el carrito o
 * checkout, mostrando si está en:
 * 
 * - OFERTÓN (precio rebajado por tienda).
 * - PROMOCIÓN (precio por escalado por cantidad).
 * - DISPONIBLE (precio regular sin promo).
 *
 * Además, si está en el carrito, muestra:
 * - Etiqueta informativa con límite y precio.
 *
 * ✅ Mejora visual clara y útil para el usuario final.
 *
 * Autor: Dens - Spark
 * Fecha: 06/07/2025
 * ============================================================
 */
add_filter('woocommerce_cart_item_name', 'display_dynamic_product_label', 10, 3);
require_once get_stylesheet_directory() . '/includes/woocommerce/cart/cart_item_name.php';

function shortcode_etiqueta_promocion_producto()
{
    return '';
    if (!is_product())
        return '';

    global $product;
    $sku = $product->get_sku();
    $descuentos = obtener_configuracion_descuentos();

    if (isset($descuentos[$sku])) {
        $config = $descuentos[$sku];
        $precio_final = wc_price($config['precio_final']);
        $limite = $config['limite'];
        $unidad = $config['unidad'];
        $etiqueta = $config['etiqueta'];

        ob_start();
        ?>
        <div style="margin: 10px 0; padding: 10px; background: #e6f7e6; border-left: 4px solid #28a745;">
            <strong style="color: #28a745;">🟢 <?php echo esc_html($etiqueta); ?></strong><br>
            Precio promocional: <strong><?php echo $precio_final; ?></strong> por <?php echo esc_html($unidad); ?><br>
            <small style="color: #666;">Máximo <?php echo $limite . ' ' . esc_html($unidad); ?> por compra</small>
        </div>
        <?php
        return ob_get_clean();
    }

    return '';
}
add_shortcode('etiqueta_promocion', 'shortcode_etiqueta_promocion_producto');

// Borra método de envío si existe, solo en el carrito
add_action('woocommerce_before_cart', 'borrar_metodo_envio_guardado');

function borrar_metodo_envio_guardado()
{


    if (WC()->session) {
        WC()->session->__unset('chosen_shipping_methods');
    }
}

/**
 * Elimina del HTML final cualquier <script> con la clase "saswp-user-custom-schema-markup-output".
 * Esto evita que se impriman esquemas rotos o vacíos generados por el plugin "Schema & Structured Data for WP".
 * Se usa `ob_start` para interceptar y modificar el contenido antes de que se envíe al navegador.
 */
add_action('wp_loaded', function () {
    ob_start(function ($buffer) {
        return preg_replace('/<script[^>]+class="saswp-user-custom-schema-markup-output"[^>]*>.*?<\/script>/is', '', $buffer);
    });
});

require_once get_stylesheet_directory() . '/includes/wcmultilocations-order.php';

add_filter(
    'user_has_cap',
    function ($allcaps, $caps, $args, $user) {
        // … tu lógica …
        return $allcaps;
    },
    10,    // prioridad
    4      // número de args que acepta tu función
);

/**permiso para  wpseo_manager para categorias woocommerce*/
function agregar_permisos_wpseo_manager()
{
    // Obtener el rol 'wpseo_manager'
    $role = get_role('wpseo_manager');

    if ($role) {
        // Permisos para gestionar categorías de productos (WooCommerce)
        $role->add_cap('manage_product_terms');  // Permiso general para taxonomías de productos
        $role->add_cap('edit_product_terms');    // Editar categorías
        $role->add_cap('delete_product_terms');  // Eliminar categorías
        $role->add_cap('assign_product_terms');  // Asignar categorías a productos      

        // Permiso para editar archivos (robots.txt y .htaccess)
        $role->add_cap('edit_files');

    }
}
add_action('admin_init', 'agregar_permisos_wpseo_manager');


/*Mostrar descripción de giros search -29-julio-aog*/
function shortcode_descripcion_etiqueta_producto()
{
    if (!is_search())
        return '';
    $search_term = get_search_query();
    $term = get_term_by('name', $search_term, 'product_tag');

    if ($term && !is_wp_error($term) && !empty($term->description)) {
        ob_start();
        echo '<div class="descripcion-product-tag">';
        echo wpautop($term->description);
        echo '</div>';
        return ob_get_clean();
    }

    return '';
}
add_shortcode('descripcion_etiqueta_producto', 'shortcode_descripcion_etiqueta_producto');

// ✅ Ordena: primero promociones (si existen), luego resto; si no hay promos, ordena por precio ascendente
add_action('wp_footer', function () { ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const table = document.querySelector('table[class^="custom_price_table_"]');
            if (!table) return;

            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));

            // 🔹 Verifica si hay promociones
            const hasPromos = rows.some(row => row.classList.contains("promocion"));

            rows.sort((a, b) => {
                if (hasPromos) {
                    // 🔥 Si hay promociones, ordénalas primero
                    const isPromoA = a.classList.contains("promocion") ? 1 : 0;
                    const isPromoB = b.classList.contains("promocion") ? 1 : 0;

                    if (isPromoA !== isPromoB) return isPromoB - isPromoA;

                    // Dentro de cada grupo, ordenar por data-regular-price DESC
                    const regularA = parseFloat(a.getAttribute("data-regular-price")) || 0;
                    const regularB = parseFloat(b.getAttribute("data-regular-price")) || 0;
                    return regularB - regularA;

                } else {
                    // ✅ Si NO hay promociones, ordenar por precio de menor a mayor
                    const priceA = parseFloat(
                        a.querySelector(".custom_tier_price").textContent.replace(/[^\d.]/g, "")
                    );
                    const priceB = parseFloat(
                        b.querySelector(".custom_tier_price").textContent.replace(/[^\d.]/g, "")
                    );
                    return priceA - priceB;
                }
            });

            // 🔹 Reinserta las filas ordenadas
            rows.forEach(row => tbody.appendChild(row));

            // 🔹 Muestra la tabla ya ordenada
            table.style.display = "table";
        });
    </script>
<?php });



add_action('template_redirect', function () {
    // Solo front, main query y contextos de productos
    if (
        !is_admin()
        && is_main_query()
        && (is_shop() || is_product_taxonomy() || is_search())
        && isset($_COOKIE['wcmlim_selected_location_termid'])
    ) {
        // Para la mayoría de plugins de cache:
        if (function_exists('nocache_headers')) {
            nocache_headers();
        }
        // Marca a WP-Cache, WP-Rocket, etc. para que no guarden esta página:
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
    }
});

add_action('pre_get_posts', 'cm_filtrar_productos_por_stock_precio', 20);
function cm_filtrar_productos_por_stock_precio(\WP_Query $q)
{
    // 2) Solo en front-end, query principal y en contextos de productos
    if (
        is_admin()
        || !$q->is_main_query()
        || !(is_shop() || is_product_taxonomy() || is_search())
    ) {
        return;
    }

    // 3) Leemos la cookie para saber la sucursal (term_id)
    $term_id = isset($_COOKIE['wcmlim_selected_location_termid'])
        && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined'
        ? intval($_COOKIE['wcmlim_selected_location_termid'])
        : 0;

    // 4) Preparamos el meta_query que ya tenga la consulta
    $meta_query = (array) $q->get('meta_query', []);

    // 5) Añadimos nuestra condición de stock y precio
    if ($term_id) {
        $meta_query[] = [
            'key' => "wcmlim_stock_at_{$term_id}",
            'value' => 0,
            'compare' => '>',
            'type' => 'NUMERIC',
        ];
        $meta_query[] = [
            'key' => "wcmlim_regular_price_at_{$term_id}",
            'value' => 0,
            'compare' => '>',
            'type' => 'NUMERIC',
        ];
    } else {
        $meta_query[] = [
            'key' => '_regular_price',
            'value' => 0,
            'compare' => '>',
            'type' => 'NUMERIC',
        ];
    }

    // 6) Re-aplicamos el meta_query modificado
    $q->set('meta_query', $meta_query);
}


/**
 * Shortcode [cm_recommendations]
 * Muestra productos de la misma categoría del actual,
 * solo con stock y precio válidos en tienda (o base),
 * excluye On Sale badge y solo productos publicados.
 */
add_shortcode('cm_recommendations', 'cm_recommendations_shortcode');
function cm_recommendations_shortcode($atts)
{
    if (!is_product()) {
        return '';
    }

    global $product;

    // 1) Atributos por defecto
    $atts = shortcode_atts(array(
        'posts_per_page' => 4,
        'columns' => 4,
    ), $atts, 'cm_recommendations');

    // 2) ID de tienda desde cookie (evita 'undefined')
    $term_id = (isset($_COOKIE['wcmlim_selected_location_termid'])
        && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined')
        ? intval($_COOKIE['wcmlim_selected_location_termid'])
        : 0;

    // 3) Sacamos los IDs de categoría del producto actual
    $cat_ids = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'ids'));
    if (empty($cat_ids)) {
        return '<p class="msg-general"><span class="cu-info-circle"></span><span class="msg-text">Este producto no tiene categoría.</span></p>';
    }

    // 4) Preparamos meta_query para stock + precio
    $meta_query = array('relation' => 'AND');

    // 4.1) Si hay tienda, exigimos stock_tienda >0 y price_tienda >0
    if ($term_id) {
        $meta_query[] = array(
            'relation' => 'AND',
            array(
                'key' => "wcmlim_stock_at_{$term_id}",
                'value' => 0,
                'compare' => '>',
                'type' => 'NUMERIC',
            ),
            array(
                'key' => "wcmlim_regular_price_at_{$term_id}",
                'value' => 0,
                'compare' => '>',
                'type' => 'NUMERIC',
            ),
        );
    } else {
        // 4.2) Si no hay tienda, mínimo precio base >0
        $meta_query[] = array(
            'key' => '_regular_price',
            'value' => 0,
            'compare' => '>',
            'type' => 'NUMERIC',
        );
    }

    // 4.3) Y siempre stock general = instock
    $meta_query[] = array(
        'key' => '_stock_status',
        'value' => 'instock',
        'compare' => '=',
    );

    // 5) Obtenemos posts con WP_Query
    $loop = new WP_Query(array(
        'post_type' => 'product',
        'posts_per_page' => absint($atts['posts_per_page']),
        'post_status' => 'publish',
        'orderby' => 'rand',
        'post__not_in' => array($product->get_id()),
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $cat_ids,
            ),
        ),
        'meta_query' => $meta_query,
    ));

    if (!$loop->have_posts()) {
        return '<p class="msg-general"><span class="cu-info-circle"></span><span class="msg-text">No hay recomendaciones disponibles tras filtrar stock y precio.</span></p>';
    }

    // 6) Ocultamos el badge “On Sale” solo en este bloque
    remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);

    // 7) Renderizado puro con echo
    ob_start();

    echo '<div id="product-list" class="elementor-element carnemart-loop-productos">';
    echo '<div class="elementor-widget-container">';
    echo '<div class="woocommerce">';
    echo '<ul class="products elementor-grid columns-' . intval($atts['columns']) . '">';

    while ($loop->have_posts()) {
        $loop->the_post();
        // Cada producto con la plantilla nativa
        wc_get_template_part('content', 'product');
    }

    echo '</ul>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

    // Div de loading oculto
    echo '<div id="loading" style="display:none;">';
    echo '<span class="msg-consulta">Consultando inventario …</span>';
    echo '</div>';

    wp_reset_postdata();

    // 8) Volvemos a enganchar el badge “On Sale”
    add_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);

    return ob_get_clean();
}

// /**
//  * 2) Renderiza el botón “Añadir al carrito” con TODOs los data-*
//  */
// function cm_render_add_to_cart_button(WC_Product $product)
// {
//     $product_id = $product->get_id();
//     $sku = $product->get_sku();
//     $cart_url = wc_get_cart_url();
//     $name = $product->get_name();

//     // Misma cookie / term_id de antes
//     $term_id = isset($_COOKIE['wcmlim_selected_location_termid'])
//         && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined'
//         ? intval($_COOKIE['wcmlim_selected_location_termid'])
//         : 0;

//     // Nombre y “key” de la ubicación
//     $term = $term_id ? get_term($term_id) : null;
//     $selected_loc = $term ? $term->name : '';
//     // Puedes guardar en term meta un campo 'location_key', o usar el mismo term_id
//     $location_key = $term_id;

//     // Cantidad en stock en esa tienda
//     $location_qty = $term_id
//         ? get_post_meta($product_id, "wcmlim_stock_at_{$term_id}", true)
//         : 1;

//     // Precios en esa tienda
//     $sale_price = $term_id
//         ? get_post_meta($product_id, "wcmlim_sale_price_at_{$term_id}", true)
//         : '';
//     $regular_price = $term_id
//         ? get_post_meta($product_id, "wcmlim_regular_price_at_{$term_id}", true)
//         : '';

//     // ¿Backorders?
//     $backorder = $product->backorders_allowed() ? 'yes' : '';

//     // Valor inicial de quantity = mismo step que antes
//     $step = get_post_meta($product_id, 'product_step', true);
//     if ($step === '' || !is_numeric($step)) {
//         $step = get_post_meta($product_id, 'min_quantity', true);
//     }
//     $step = (is_numeric($step) && floatval($step) > 0) ? floatval($step) : 1.0;

//     printf(
//         '<a data-cart-url="%1$s" data-isredirect="no" data-quantity="%2$s" class="button product_type_simple add_to_cart_button wcmlim_ajax_add_to_cart" data-product_id="%3$d" data-product_sku="%4$s" aria-label="%5$s" data-selected_location="%6$s" data-location_key="%7$s" data-location_qty="%8$s" data-location_termid="%9$s" data-product_price="%10$.2f" data-location_sale_price="%11$s" data-location_regular_price="%12$s" data-product_backorder="%13$s" rel="nofollow">%14$s</a>',
//         esc_url($cart_url),
//         esc_attr($step),
//         esc_attr($product_id),
//         esc_attr($sku),
//         esc_attr(sprintf(__('Add &ldquo;%s&rdquo; to your cart', 'woocommerce'), $name)),
//         esc_attr($selected_loc),
//         esc_attr($location_key),
//         esc_attr($location_qty),
//         esc_attr($term_id),
//         esc_attr($product->get_price()),
//         esc_attr($sale_price),
//         esc_attr($regular_price),
//         esc_attr($backorder),
//         esc_html__('Añadir al carrito', 'tu-text-domain')
//     );
// }

/**
 * 3) Hook final: sustituimos el render nativo y, en AJAX, imprimimos tu markup completo
 */
//remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
//add_action('woocommerce_after_shop_loop_item', 'cm_loop_render_flex_precio', 10);
// function cm_loop_render_flex_precio()
// {
//     global $product;

//     if (defined('DOING_AJAX') && DOING_AJAX) {
//         echo '<div class="flex-precio">';
//         cm_render_qty_field($product->get_id());
//         cm_render_add_to_cart_button($product);
//         echo '</div>';
//     } else {
//         // Render nativo fuera de AJAX (tu shortcode ya incluye el <div class="flex-precio">)
//         woocommerce_template_loop_add_to_cart();
//     }
// }

// Carga el walker personalizado para categorías de productos
add_action('init', function () {
    require_once get_stylesheet_directory() . '/woocommerce/categorie/custom-cat-list-walker.php';
});

// Filtro args del widget categorías
add_filter('woocommerce_product_categories_widget_args', function ($args) {
    $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

    // Caso especial: si la URL contiene "/giros/"
    if (strpos($request_uri, '/giros/') !== false) {
        $args['parent'] = 0;   // Solo padres
        $args['depth'] = 1;   // No profundiza en hijos
        $args['hide_empty'] = true;
        $args['walker'] = new Custom_WC_Product_Cat_List_Walker();

        // Solo mostrar categorías padres activas (centro_activo = 1)
        $args['include'] = array();
        $terms = get_terms([
            'taxonomy' => 'product_cat',
            'parent' => 0,
            'hide_empty' => true,
        ]);
        foreach ($terms as $term) {
            if (get_term_meta($term->term_id, 'centro_activo', true) == '1') {
                $args['include'][] = $term->term_id;
            }
        }
        if (empty($args['include'])) {
            $args['include'] = array(0); // Para evitar mostrar nada si no hay activas
        }

        return $args; // ✅ retornamos directo, no seguimos con lo demás
    }

    // --- Lógica normal cuando es una categoría de productos ---
    $parent_slug = '';
    if (!empty($request_uri)) {
        $parts = explode('/', trim($request_uri, '/'));
        $pos = array_search('product-category', $parts);
        if ($pos !== false && isset($parts[$pos + 1])) {
            $parent_slug = sanitize_title($parts[$pos + 1]);
        }
    }

    if (!empty($parent_slug)) {
        // Si hay slug válido, obtener term y mostrar solo hijos directos
        $parent_term = get_term_by('slug', $parent_slug, 'product_cat');
        if ($parent_term && !is_wp_error($parent_term)) {
            $args['child_of'] = $parent_term->term_id;
            $args['parent'] = $parent_term->term_id;
            $args['depth'] = 1;
            $args['hide_empty'] = true;
            $args['walker'] = new Custom_WC_Product_Cat_List_Walker();
        }
    } else {
        // Si no hay parent_slug, mostrar solo las categorías padres (top-level)
        $args['parent'] = 0;
        $args['depth'] = 1;
        $args['hide_empty'] = true;
        $args['walker'] = new Custom_WC_Product_Cat_List_Walker();
    }

    return $args;
});

// Oculta el lightbox del mini-cart (cinturón y tirantes)
add_action('wp_head', function () {
    echo '<style id="cmart-hide-minicart">
		.elementor-menu-cart__container.elementor-lightbox{display:none!important;visibility:hidden!important;opacity:0!important}
		.e-lightbox-open{overflow:auto!important}
	</style>';
}, 99);

add_action('wp_footer', function () {
    if (is_admin()) {
        return;
    }
    ?>
    <script>
        (function ($) {
            // URL del carrito
            var cartUrl = (window.wc_add_to_cart_params && wc_add_to_cart_params.cart_url) || '/cart';

            function closeMiniCart() {
                var $lb = $('.elementor-menu-cart__container.elementor-lightbox');
                $lb.attr('aria-hidden', 'true').hide();
                $('body').removeClass('e-lightbox-open');
                $('.elementor-menu-cart__wrapper').removeClass('cart--shown');
            }

            function goToCart(e) {
                if (e) { e.preventDefault(); e.stopImmediatePropagation(); }
                closeMiniCart();
                window.location.href = cartUrl;
            }

            $(function () {

                // 1) Clic en el ícono/botón del carrito del header -> redirigir al carrito
                $(document).on('click', 'a.elementor-menu-cart__toggle_button, .elementor-menu-cart__close-button', goToCart);

                // 2) Al agregar al carrito por AJAX: NO redirigir, solo asegurarnos de cerrar el mini-cart si intenta abrirse
                $(document.body).on('added_to_cart', function () {
                    closeMiniCart();
                });

                // 3) Si al cargar detectamos el lightbox abierto, cerrarlo
                setTimeout(closeMiniCart, 0);

                // 4) Cerrar si algún script cambia aria-hidden a "false"
                var observer = new MutationObserver(function (muts) {
                    for (var i = 0; i < muts.length; i++) {
                        var m = muts[i];
                        if (m.type === 'attributes' && m.attributeName === 'aria-hidden') {
                            if ($(m.target).attr('aria-hidden') === 'false') { closeMiniCart(); }
                        }
                    }
                });
                $('.elementor-menu-cart__container.elementor-lightbox').each(function () {
                    observer.observe(this, { attributes: true });
                });
            });
        })(jQuery);
    </script>
    <?php
}, 99);

// Helper: devuelve el term_id de la tienda seleccionada o 0 si no hay
function cm_get_selected_store_term_id()
{
    if (
        isset($_COOKIE['wcmlim_selected_location_termid'])
        && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined'
        && $_COOKIE['wcmlim_selected_location_termid'] !== ''
    ) {
        return intval($_COOKIE['wcmlim_selected_location_termid']);
    }
    return 0;
}

// UI: deshabilita botón y muestra hint cuando no hay tienda
add_action('wp_footer', function () {
    if (!is_product())
        return;
    ?>
    <script>
        (function () {
            // Lee cookie simple
            function getCookie(name) {
                const m = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'));
                return m ? decodeURIComponent(m[1]) : null;
            }
            var termId = getCookie('wcmlim_selected_location_termid');
            var noStore = (!termId || termId === 'undefined' || termId === '');

            if (noStore) {
                // Botón principal PDP
                var btn = document.querySelector('form.cart button.single_add_to_cart_button');
                if (btn) {
                    btn.setAttribute('disabled', 'disabled');
                    btn.classList.add('cm-disabled-add');
                    btn.setAttribute('title', 'Seleccione una tienda para habilitar el carrito');
                }
                // Evita submit por teclado/enter
                var form = document.querySelector('form.cart');
                if (form) {
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        return false;
                    }, { capture: true });
                }
            }
        })();
    </script>
    <style>
        /* Estilo visual del botón deshabilitado */
        .cm-disabled-add {
            opacity: .6 !important;
            cursor: not-allowed !important;
            filter: grayscale(0.3);
        }
    </style>
    <?php
});

/**
 * Filtra el bloque de stock en la PDP.
 *
 * Comportamiento:
 * - Si el producto tiene stock global:
 *   → se muestra el bloque nativo del plugin.
 *   → si NO hay tienda seleccionada, se inyecta (via JS) un mensaje adicional debajo de `.Wcmlim_prefloc_box`.
 *
 * - Si el producto NO tiene stock global:
 *   → se muestra un banner personalizado con mensaje según haya tienda seleccionada o no.
 */
add_filter('woocommerce_stock_html', function ($html, $availability_text, $product) {
    // Recuperamos el ID de tienda desde cookie (usada por el plugin Multi-Location)
    $term_id = isset($_COOKIE['wcmlim_selected_location_termid'])
        && $_COOKIE['wcmlim_selected_location_termid'] !== 'undefined'
        ? intval($_COOKIE['wcmlim_selected_location_termid'])
        : 0;

    if (!$product) {
        return $html;
    }

    // Caso 1: Producto con stock global
    if ($product->is_in_stock()) {
        // Si NO hay tienda seleccionada, agregamos mensaje extra via JS
        if ($term_id === 0) {
            add_action('wp_footer', function () {
                ?>
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        let target = document.querySelector('.Wcmlim_prefloc_box');

                        if (target) {
                            var p = document.createElement('p');
                            p.className = 'text-stock-message';
                            p.textContent = 'Por favor, seleccione una tienda para consultar disponibilidad.';
                            target.appendChild(p);
                        }
                    });
                </script>
                <?php
            });
        }
        return $html;
    }

    // Caso 2: Producto SIN stock global → Banner personalizado
    ob_start(); ?>
    <div class="cmt-oos" data-cmt-oos>
        <div class="cmt-oos__banner">
            <div class="Wcmlim_box_content select_location-wrapper">
                <div class="Wcmlim_box_header">
                    <h3 class="Wcmlim_box_title">Stock Information</h3>
                </div>
                <hr style="margin: 5px 0px;" class="Wcmlim_line_seperator">
                <div class="Wcmlim_prefloc_box">
                    <p class="text-stock-message">
                        <?php if ($term_id != 0) { ?>
                            Disculpe las molestias, en esta tienda no tenemos disponible inventario
                        <?php } else { ?>
                            Por favor, seleccione una tienda para consultar disponibilidad
                        <?php } ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}, 10, 3);

/**
 * Personalización visual (CSS) y tooltip de ayuda.
 *
 * - Estilos para mensajes de stock (.text-stock-message).
 * - Ajustes de estilo para separador e importe del precio.
 * - Tooltip explicativo sobre el peso de productos frescos/porcionados.
 *   → Desktop: aparece al pasar el mouse.
 *   → Móvil: aparece al hacer click/touch sobre el icono.
 */
add_action('wp_footer', function () { ?>
    <?php if (!is_product())
        return; ?>
    <style>
        /* Ajuste del separador */
        .Wcmlim_line_seperator {
            width: 80% !important;
        }

        /* Mensaje de stock */
        .text-stock-message {
            width: max-content;
            color: #ffffff !important;
            display: block;
            background-color: #ffa135;
            padding: 5px;
            border-radius: 5px;
            font-weight: 700 !important;
            font-size: 12px;
        }

        /* Precio destacado */
        .price.wcmlim_product_price .woocommerce-Price-amount.amount>bdi:first-of-type {
            font-size: 35px !important;
            font-weight: 700 !important;
            font-style: normal !important;
        }
    </style>

    <script>
        jQuery(function ($) {
            var $icon = $('.elementor-element-57ab0b64 .elementor-icon');

            // Elimina tooltips previos para evitar duplicados
            $('.cm-peso-tooltip').remove();

            // Crea tooltip
            var $tooltip = $('<div class="cm-peso-tooltip">El peso de productos frescos, perecederos o porcionados puede legítimamente variar por la naturaleza del producto (corte, humedad, hueso, congelación, etc.) y esto podría impactar en el precio final.</div>');

            // Asegura que el contenedor sea relativo y agrega tooltip
            var $wrapper = $icon.closest('.elementor-widget-icon');
            $wrapper.css('position', 'relative').append($tooltip);

            // PC: hover para mostrar/ocultar
            $icon.on('mouseenter', function () {
                if (window.innerWidth > 768) $tooltip.stop(true, true).fadeIn(150);
            }).on('mouseleave', function () {
                if (window.innerWidth > 768) $tooltip.stop(true, true).fadeOut(100);
            });

            // Móvil: click/touch para alternar
            $icon.on('click touchstart', function (e) {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    e.stopPropagation();
                    $tooltip.fadeToggle(150);
                }
            });

            // Ocultar si se hace clic fuera en móvil
            $(document).on('click touchstart', function (e) {
                if (window.innerWidth <= 768 && !$icon.is(e.target) && $icon.has(e.target).length === 0 && !$tooltip.is(e.target) && $tooltip.has(e.target).length === 0) {
                    $tooltip.fadeOut(100);
                }
            });
        });
    </script>

    <style>
        /* Estilo general tooltip */
        .cm-peso-tooltip {
            display: none;
            position: absolute;
            top: 50%;
            left: 100%;
            transform: translateY(-50%);
            margin-left: 10px;
            background: rgba(2, 27, 109, 0.95);
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            white-space: normal;
            width: 260px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            z-index: 9999;
        }

        /* Flecha lateral del tooltip */
        .cm-peso-tooltip::after {
            content: "";
            position: absolute;
            top: 50%;
            left: -6px;
            transform: translateY(-50%);
            border: 6px solid transparent;
            border-right-color: rgba(2, 27, 109, 0.95);
        }

        /* Estilos responsivos para móviles */
        @media (max-width: 480px) {
            .cm-peso-tooltip {
                top: auto;
                bottom: 100%;
                left: 50%;
                transform: translateX(-50%);
                margin: 0 0 6px 0;
                width: 220px;
                font-size: 0.75rem;
            }

            .cm-peso-tooltip::after {
                top: auto;
                bottom: -6px;
                left: 50%;
                transform: translateX(-50%);
                border: 6px solid transparent;
                border-top-color: rgba(2, 27, 109, 0.95);
            }
        }
    </style>
<?php });

add_action('wp_footer', function () {
    if (!is_product())
        return;

    $no_store = (cm_get_selected_store_term_id() === 0);
    ?>
    <script>
        (function () {
            var NO_STORE = <?php echo $no_store ? 'true' : 'false'; ?>;
            if (!NO_STORE) return;

            let divsQyt = document.getElementsByClassName('quantity');

            // accedemos al input de cada div
            Array.from(divsQyt).forEach(element => {
                let inputQty = element.querySelector('input[type="number"]');
                if (inputQty) {
                    // Deshabilitar
                    inputQty.disabled = true;
                }
            });
        })();
    </script>
    <?php
});

// Badge "AGOTADO" en la imagen principal
// - Siempre si el producto está agotado a nivel global
// - O si la tienda seleccionada no tiene stock (wcmlim_stock_at_{location_id} <= 0)
//   (si no existe el meta, se asume 0)
add_filter('woocommerce_single_product_image_thumbnail_html', function ($html, $post_thumbnail_id) {
    if (!is_product())
        return $html;

    global $product;
    if (!$product || !is_a($product, 'WC_Product'))
        return $html;

    // verificamos primeramente si hay tienda seleccionada
    $location_id = (int) ($_COOKIE['wcmlim_selected_location_termid'] ?? 0);
    if ($location_id === 0) {
        // Sin tienda seleccionada: no mostramos badge
        return $html;
    }

    // ¿Agotado globalmente?
    $oos = !$product->is_in_stock();

    // Si no está agotado globalmente, revisa por tienda (si hay una seleccionada)
    if (!$oos) {
        $location_id = (int) ($_COOKIE['wcmlim_selected_location_termid'] ?? 0);

        if ($location_id > 0) {
            $product_id = $product->get_id();
            $raw = get_post_meta($product_id, "wcmlim_stock_at_{$location_id}", true);

            // Si no hay meta, tratamos como 0 (sin stock)
            $stock = ($raw === '' || $raw === false) ? 0 : (float) $raw;

            if ($stock <= 0) {
                $oos = true;
            }
        } else {
            // Sin tienda seleccionada y con stock global: no mostramos badge
            $oos = false;
        }
    }

    if (!$oos)
        return $html;

    // Inyectar SOLO una vez (primera imagen de la galería)
    static $badge_printed = false;
    if ($badge_printed)
        return $html;

    $pattern = '/^(\s*<div[^>]*class="[^"]*woocommerce-product-gallery__image[^"]*"[^>]*>)/i';
    $replacement = '$1<span class="cm-badge cm-badge--oos">AGOTADO</span>';
    $injected = preg_replace($pattern, $replacement, $html, 1, $count);

    if ($count > 0) {
        $badge_printed = true;
        return $injected;
    }
    return $html;
}, 10, 2);

// (Opcional) CSS básico del badge
add_action('wp_head', function () { ?>
    <style>
        .woocommerce div.product div.images .woocommerce-product-gallery__image {
            position: relative;
        }

        .cm-badge {
            position: absolute;
            z-index: 3;
            top: 12px;
            left: 12px;
            padding: .45rem .6rem;
            background: #c62828;
            color: #fff;
            font-weight: 700;
            border-radius: 6px;
            text-transform: uppercase;
            font-size: .875rem;
            line-height: 1;
        }
    </style>
<?php });

// Estilos del badge (opcional)
add_action('wp_head', function () {
    if (!is_product())
        return;
    ?>
    <style>
        .single-product .woocommerce-product-gallery__image {
            position: relative;
        }

        .cm-badge {
            position: absolute;
            top: .5rem;
            left: .5rem;
            z-index: 50;
            display: inline-block;
            padding: .45rem .75rem;
            border-radius: .6rem;
            font-weight: 700;
            font-size: .85rem;
            letter-spacing: .02em;
            color: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .2);
            pointer-events: none;
        }

        .cm-badge--oos {
            background: #e53935;
        }

        /* rojo */
        @media (max-width: 640px) {
            .cm-badge {
                top: .35rem;
                left: .35rem;
                font-size: .78rem;
                padding: .35rem .6rem;
            }
        }
    </style>
    <?php
});

// 1) JS: mover el campo y 3) limpiar "(opcional)" + asterisco + required siempre
add_action('wp_enqueue_scripts', function () {
    if (!is_checkout())
        return;
    wp_enqueue_script('jquery');

    $js = <<<'JS'
(function($){
  function placeAndFixCompany(){
    var $wrapper = $('#billing_company_field');
    var $giroWrp = $('#giro_empresa').closest('p');

    // Mover debajo del "giro de la empresa" una sola vez
    if ($wrapper.length && $giroWrp.length && !$wrapper.data('moved-below-giro')) {
      $wrapper.insertAfter($giroWrp);
      $wrapper.data('moved-below-giro', true);
    }

    // Forzar etiqueta sin "(opcional)" y con asterisco
    var $label = $wrapper.find('label[for="billing_company"]');
    if ($label.length) {
      // eliminar el span .optional si lo insertan plugins/tema
      $label.find('.optional').remove();
      // por si viene en texto plano
      $label.contents().filter(function(){ return this.nodeType === 3; })
        .each(function(){ this.nodeValue = this.nodeValue.replace(/\s*\(opcional\)\s*/i,''); });

      if ($label.find('.required, abbr.required').length === 0) {
        $label.append(' <abbr class="required" title="obligatorio">*</abbr>');
      }
    }

    // Forzar input requerido (HTML5 y accesibilidad)
    var $input = $('#billing_company');
    if ($input.length) {
      $input.attr({'required': true, 'aria-required': 'true', 'data-required': '1'});
      $wrapper.addClass('validate-required');
    }
  }

  // Primera ejecución y también tras los refrescos de WooCommerce
  $(placeAndFixCompany);
  $(document.body).on('updated_checkout wc_fragments_loaded wc_fragments_refreshed', placeAndFixCompany);
})(jQuery);
JS;

    wp_add_inline_script('jquery', $js);
});

/**
 * 1) Validación en Admin (Usuarios → Editar)
 *    - Nombre/Apellidos: min 4 chars
 *    - Si "Negocio/Business": exigir giro y nombre de empresa (min 3 chars)
 */
add_action('user_profile_update_errors', function ($errors, $update, $user) {
    // Verificamos si estamos en la página de 'user-new.php'
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'user-new.php') !== false) {
        // Si estamos en 'user-new.php', no hacemos nada y retornamos los errores tal cual
        return $errors;
    }

    if (!isset($_POST))
        return;

    $p = wp_unslash($_POST);

    $tipo = isset($p['tipo_uso']) ? sanitize_text_field($p['tipo_uso']) : '';
    $giro = isset($p['giro_empresa']) ? sanitize_text_field($p['giro_empresa']) : '';
    $company = isset($p['billing_company']) ? trim($p['billing_company']) : '';

    // Tipo de uso requerido
    if ($tipo === '') {
        $errors->add('tipo_uso', __('Por favor, selecciona el tipo de uso.', 'woocommerce'));
        return; // paramos aquí para no encadenar más mensajes
    }

    // Si es negocio, exigir giro y empresa
    $is_business = (strcasecmp($tipo, 'Business') === 0) || (strcasecmp($tipo, 'Negocio') === 0);
    if ($is_business) {
        if ($giro === '') {
            $errors->add('giro_empresa', __('Por favor, selecciona el giro de la empresa.', 'woocommerce'));
        }
        if ($company === '') {
            $errors->add('billing_company', __('Por favor, ingresa el nombre de la empresa.', 'woocommerce'));
        } elseif (mb_strlen($company) < 3) {
            $errors->add('billing_company_len', __('El nombre de la empresa debe tener al menos 3 caracteres.', 'woocommerce'));
        }
    }
}, 10, 3);


/**
 * Disable Batcache/object/db cache on product/cart/checkout.
 */
add_action('init', function () {
    if (is_admin())
        return;

    // ¿Página sensible?
    $is_sensitive =
        (function_exists('is_product') && is_product()) ||
        (function_exists('is_cart') && is_cart()) ||
        (function_exists('is_checkout') && is_checkout()) ||
        (function_exists('is_account_page') && is_account_page());

    // No cache si es página sensible
    if ($is_sensitive) {
        if (!defined('DONOTCACHEPAGE'))
            define('DONOTCACHEPAGE', true);
        if (!defined('DONOTCACHEOBJECT'))
            define('DONOTCACHEOBJECT', true);
        if (!defined('DONOTCACHEDB'))
            define('DONOTCACHEDB', true);

        add_action('send_headers', function () {
            nocache_headers();
            header('Cache-Control: private, no-cache, no-store, max-age=0, must-revalidate');
        });
    }
}, 0);

function custom_product_description_styles()
{
    echo "
    <style>
        /* Estilos personalizados para la descripción corta */
        .woocommerce-product-details__short-description {
            font-size: 16px; /* Tamaño de la fuente */
            line-height: 1.6; /* Altura de línea para mayor legibilidad */
            color: #333; /* Color de texto */
            background-color: #f7f7f7ff; /* Fondo más oscuro */
            padding: 20px; /* Espaciado alrededor del texto */
            border-radius: 8px; /* Bordes redondeados */
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Sombra suave */
            margin-top: 20px; /* Separar del contenido de arriba */
            margin-bottom: 20px; /* Separar del contenido de abajo */
        }

        /* Estilo de los elementos de lista dentro de la descripción */
        .woocommerce-product-details__short-description ul {
            padding-left: 20px; /* Aumentar el espacio de la lista */
        }
        .woocommerce-product-details__short-description li {
            margin-bottom: 10px; /* Espaciado entre los elementos de la lista */
            font-weight: 400; /* Peso de la fuente */
            color: #555; /* Color de texto en la lista */
        }

        /* Agregar un ícono o marca verde a los elementos de la lista */
        .woocommerce-product-details__short-description li::before {
            content: '✓'; /* Agregar un checkmark */
            color: green; /* Color verde */
            font-weight: bold;
            margin-right: 10px; /* Espacio entre el ícono y el texto */
        }

        /* Estilo de los títulos dentro de la descripción */
        .woocommerce-product-details__short-description b {
            font-size: 18px; /* Tamaño de la fuente para los títulos */
            font-weight: bold; /* Aumentar el peso */
            color: #0073e6; /* Color azul para los títulos */
        }

        /* Espaciado entre párrafos */
        .woocommerce-product-details__short-description p {
            margin-bottom: 15px; /* Espaciado entre párrafos */
        }

        /* Estilo para el botón de 'Agregar al carrito' */
        .single_add_to_cart_button {
            background-color: #FF5733; /* Color de fondo vibrante */
            color: #fff; /* Texto blanco */
            padding: 15px 30px; /* Aumentar el tamaño */
            border-radius: 5px; /* Bordes redondeados */
            font-size: 18px; /* Tamaño de la fuente */
            transition: background-color 0.3s ease; /* Transición suave */
        }

        .single_add_to_cart_button:hover {
            background-color: #FF2A00; /* Color más oscuro al pasar el ratón */
        }

        /* Mejora en la responsividad */
        @media (max-width: 768px) {
            .woocommerce-product-details__short-description {
                font-size: 14px; /* Reducir el tamaño de la fuente en móviles */
            }
            .single_add_to_cart_button {
                font-size: 16px; /* Tamaño de la fuente del botón */
                padding: 12px 25px; /* Ajustar tamaño del botón */
            }
        }

    </style>
    ";
}
add_action('wp_head', 'custom_product_description_styles');

/**
 * Push "purchase" para Facebook Pixel (vía GTM) en /pago-conekta/?id=...
 */
add_action('wp_footer', function () {
    if (is_admin())
        return;
    /*
     * Data Layer para pixel de compra tagmanager
     */

    // Dispara solo en /pago-conekta/
    $uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field($_SERVER['REQUEST_URI']) : '';
    if (strpos($uri, '/pago-conekta/') === false)
        return;

    // Leer id: base64(order_id) o order_key
    $raw = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    if (!$raw)
        return;

    $order = null;
    $dec = base64_decode($raw, true);
    if ($dec !== false && $dec !== '' && ctype_digit($dec)) {
        $order = wc_get_order((int) $dec);
    } else {
        $maybe_id = wc_get_order_id_by_order_key($raw);
        if ($maybe_id)
            $order = wc_get_order($maybe_id);
    }
    if (!$order)
        return;

    // Solo disparar con pago aceptado
    if (!in_array($order->get_status(), ['processing', 'completed'], true))
        return;

    $order_id = $order->get_id();

    // Evitar dobles envíos (opcional: comenta para permitir re-disparo en recarga)
    if (get_post_meta($order_id, '_fb_purchase_pushed', true))
        return;

    $value = (float) $order->get_total();
    $currency = (string) $order->get_currency();
    $transaction_id = (string) $order->get_order_number();
    $event_id = $transaction_id . '-' . substr($order->get_order_key(), -8); // para dedupe

    // Armar contents como lo pide Facebook
    $contents = [];
    $content_ids = [];


    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        $qty = max(1, (int) $item->get_quantity());
        $sku_or_id = $product && $product->get_sku() ? $product->get_sku() : (string) $item->get_product_id();
        $unit = (float) $item->get_total() / $qty;

        $contents[] = ['id' => $sku_or_id, 'quantity' => $qty, 'item_price' => (float) wc_format_decimal($unit, 2)];
        $content_ids[] = $sku_or_id;

        // GA4-friendly (por si te sirve)
        $cats = wp_get_post_terms($item->get_product_id(), 'product_cat', ['fields' => 'names']);
        $ga4_items[] = [
            'item_id' => $sku_or_id,
            'item_name' => $item->get_name(),
            'price' => (float) wc_format_decimal($unit, 2),
            'quantity' => $qty,
            'item_category' => !empty($cats) ? implode(' / ', $cats) : '',
        ];
    }

    update_post_meta($order_id, '_fb_purchase_pushed', current_time('mysql'));

    // Empujar evento "purchase" con estructura para FB Pixel
    $payload = [
        'event' => 'purchase', // <— Gatilla el trigger Custom Event de GTM
        'ecommerce' => [
            'transaction_id' => $transaction_id,
            'value' => $value,
            'currency' => $currency,
            'content_ids' => $content_ids,
            'contents' => $contents,   // [{id, quantity, item_price}]
            'content_type' => 'product',
            'event_id' => $event_id,   // para deduplicación en FB
            // opcional: mantener items GA4 si los ocupas en otros tags
            'items' => $ga4_items,
        ],
    ];

    echo '<script>window.dataLayer=window.dataLayer||[];dataLayer.push('
        . wp_json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        . ');</script>';
});