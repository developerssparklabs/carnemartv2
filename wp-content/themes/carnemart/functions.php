<?php
require get_template_directory() . '/functions/custom_blocks.php';
add_theme_support('woocommerce');

function admin_styles()
{
    wp_enqueue_style('loginCSS', get_template_directory_uri() . '/login/css/loginStyles.css', false);
    wp_enqueue_script('jquery');
    wp_enqueue_script('loginJS', get_template_directory_uri() . '/login/js/loginJs.js', array('jquery'), '1.3.8', true);
}
add_action('login_enqueue_scripts', 'admin_styles', 10);

if (!isset($content_width)) {
    $content_width = 900;
}

if (function_exists('add_theme_support')) {
    add_theme_support('menus');
    add_theme_support('post-thumbnails');
    add_image_size('large', 700, '', true);
    add_image_size('medium', 250, '', true);
    add_image_size('small', 120, '', true);

    add_image_size('icon-86', 86, 86, true);  // chips / iconitos (recorte cuadrado)
    add_image_size('logo-200', 200, 0, false); // logo header
    add_image_size('logo-400', 400, 0, false); // 2x del logo

    add_image_size('banner-mobile', 392, 0, false); // ~ancho visible en móvil (ajústalo)
    add_image_size('banner-tablet', 768, 0, false);
    add_image_size('banner-desktop', 1350, 0, false);

    // Productos (si necesitas forzar un tope práctico)
    add_image_size('product-card', 360, 360, true);  // grid de productos (ajusta al ancho real)


    add_theme_support('automatic-feed-links');
    add_theme_support('custom-logo');
    add_action('init', function () {
        load_theme_textdomain('html5blank', get_template_directory() . '/languages');
    });
}


// 1) Si algún template pide 'full', cámbialo por 'woocommerce_thumbnail' en archivos de producto
add_filter('post_thumbnail_size', function ($size) {
    if (is_shop() || is_product_taxonomy() || is_post_type_archive('product')) {
        return 'woocommerce_thumbnail';
    }
    return $size;
}, 999);

// 2) Define el tamaño de miniatura que WooCommerce debe generar/usar en el catálogo
add_filter('woocommerce_get_image_size_thumbnail', function () {
    return [
        'width' => 366, // 183*2 para pantallas 2x
        'height' => 366,
        'crop' => 1,
    ];
}, 999);

// 3) Asegura que WooCommerce pida ese tamaño en el loop
add_filter('single_product_archive_thumbnail_size', function () {
    return 'woocommerce_thumbnail';
}, 999);

// 4) Ajusta atributos HTML (sizes/lazy/decoding) en las cards del catálogo
add_filter('wp_get_attachment_image_attributes', function ($attr, $attachment, $size) {
    if (
        (is_shop() || is_product_taxonomy() || is_post_type_archive('product')) &&
        !empty($attr['class']) && strpos($attr['class'], 'wp-post-image') !== false
    ) {
        // Ajusta a tu grid real; 183px fue lo que marcó Lighthouse como target
        $attr['sizes'] = '(max-width:480px) 45vw, (max-width:768px) 30vw, 183px';
        $attr['loading'] = $attr['loading'] ?? 'lazy';
        $attr['decoding'] = $attr['decoding'] ?? 'async';
    }
    return $attr;
}, 999, 3);





add_theme_support('title-tag');


function html5blank_header_scripts()
{
    if ($GLOBALS['pagenow'] != 'wp-login.php' && !is_admin()) {
        wp_register_script('allscripts', get_template_directory_uri() . '/js/allscripts.js', array('jquery'), '2.1.0', true);
        wp_enqueue_script('allscripts');
        wp_register_script('html5blankscripts', get_template_directory_uri() . '/js/scripts.js', array('jquery'), '2.1.4', true);
        wp_enqueue_script('html5blankscripts');
    }
}

function scripts_footer()
{
    wp_enqueue_script('mi-script-footer', get_template_directory_uri() . '/js/funciones-woocommerce.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'scripts_footer');

function html5blank_conditional_scripts()
{
    if (is_page('pagenamehere')) {
        wp_register_script('scriptname', get_template_directory_uri() . '/js/scriptname.js', array('jquery'), '2.1.0');
        wp_enqueue_script('scriptname');
    }
}

function html5blank_styles()
{

    wp_register_style('html5blank', get_template_directory_uri() . '/style.css', array(), '2.1.0', 'all');
    wp_enqueue_style('html5blank');
    wp_register_style('allcss', get_template_directory_uri() . '/css/allcss.css', array(), '2.1.0', 'all');
    wp_enqueue_style('allcss');
    wp_register_style('siteStyle', get_template_directory_uri() . '/site_style.css', array(), '2.2.0', 'all');
    wp_enqueue_style('siteStyle');
}


// Fuentes

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'site-fonts',
        get_stylesheet_directory_uri() . '/fonts.css',
        [],
        '1.3'
    );
}, 5);

add_action('wp_head', function () {
    $base = get_stylesheet_directory_uri() . '/fonts/';
    $preloads = [

        'Poppins-Regular.woff2',
        'Poppins-Black.woff2',
        'Poppins-Bold.woff2',
        'Poppins-Medium.woff2',
        'Oswald-Regular.woff2',
        'bootstrap-icons.woff',
        'bootstrap-icons.woff2'

    ];

    foreach ($preloads as $rel) {
        $href = esc_url($base . $rel);
        echo '<link rel="preload" href="' . $href . '" as="font" type="font/woff2" crossorigin>' . "\n";
    }
}, 1);


function register_my_menus()
{
    register_nav_menus(array(
        'menu-principal' => __('Menú principal'),
        'menu-extra' => __('Menú extra'),
        'menu-footer' => __('Menú footer'),
        'menu-footer-2' => __('Menú footer columna 2'),
        'menu-footer-3' => __('Menú footer columna 3'),
        'menu-footer-4' => __('Menú footer columna 4'),
    ));
}
add_action('init', 'register_my_menus');

function my_wp_nav_menu_args($args = '')
{
    $args['container'] = false;
    return $args;
}

function my_css_attributes_filter($var)
{
    return is_array($var) ? array() : '';
}

function remove_category_rel_from_category_list($thelist)
{
    return str_replace('rel="category tag"', 'rel="tag"', $thelist);
}


function add_slug_to_body_class($classes)
{
    global $post;
    if (is_home()) {
        $key = array_search('blog', $classes);
        if ($key > -1) {
            unset($classes[$key]);
        }
    } elseif (is_page()) {
        $classes[] = sanitize_html_class($post->post_name);
    } elseif (is_singular()) {
        $classes[] = sanitize_html_class($post->post_name);
    }
    return $classes;
}

if (function_exists('register_sidebar')) {
    register_sidebar(array(
        'name' => __('Widget área 1', 'html5blank'),
        'description' => __('Bloque footer 1', 'html5blank'),
        'id' => 'widget-area-1',
        'before_widget' => '<div id="%1$s" class="%2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h6>',
        'after_title' => '</h6>'
    ));
}

if (function_exists('acf_add_options_page')) {
    acf_add_options_page(array(
        'page_title' => 'Opciones generales',
        'menu_title' => '',
        'menu_slug' => 'theme-general-settings',
        'capability' => 'edit_posts',
        'redirect' => false
    ));

    acf_add_options_sub_page(array(
        'page_title' => 'Publicidad',
        'menu_title' => 'Segmento de publicidad',
        'parent_slug' => 'theme-general-settings',
    ));

    acf_add_options_sub_page(array(
        'page_title' => 'Marcas',
        'menu_title' => 'Marcas de CarneMart',
        'parent_slug' => 'theme-general-settings',
    ));

    acf_add_options_sub_page(array(
        'page_title' => 'Header',
        'menu_title' => 'Header',
        'parent_slug' => 'theme-general-settings',
    ));

    acf_add_options_sub_page(array(
        'page_title' => 'Footer',
        'menu_title' => 'Footer',
        'parent_slug' => 'theme-general-settings',
    ));

    acf_add_options_sub_page(array(
        'page_title' => 'Redes Sociales',
        'menu_title' => 'Redes sociales',
        'parent_slug' => 'theme-general-settings',
    ));
}

function admin_bar()
{
    if (is_user_logged_in()) {
        add_filter('show_admin_bar', '__return_true', 1000);
    }
}
add_action('init', 'admin_bar');

function my_remove_recent_comments_style()
{
    global $wp_widget_factory;
    remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
}

function html5wp_pagination()
{
    global $wp_query;
    $big = 999999999;
    echo paginate_links(array(
        'base' => str_replace($big, '%#%', get_pagenum_link($big)),
        'format' => '?paged=%#%',
        'current' => max(1, get_query_var('paged')),
        'total' => $wp_query->max_num_pages
    ));
}

function excerpt($limit)
{
    $excerpt = explode(' ', get_the_excerpt(), $limit);
    if (count($excerpt) >= $limit) {
        array_pop($excerpt);
        $excerpt = implode(" ", $excerpt) . '...';
    } else {
        $excerpt = implode(" ", $excerpt);
    }
    $excerpt = preg_replace('`[[^]]*]`', '', $excerpt);
    return $excerpt;
}

function html5wp_index($length)
{
    return 2;
}

function html5wp_custom_post($length)
{
    return 3;
}

function html5wp_excerpt($length_callback = '', $more_callback = '')
{
    global $post;
    if (function_exists($length_callback)) {
        add_filter('excerpt_length', $length_callback);
    }
    if (function_exists($more_callback)) {
        add_filter('excerpt_more', $more_callback);
    }
    $output = get_the_excerpt();
    $output = apply_filters('wptexturize', $output);
    $output = apply_filters('convert_chars', $output);
    $output = '<p>' . $output . '</p>';
    echo $output;
}

function html5_blank_view_article($more)
{
    global $post;
    return ' ... ';
}

function html5_style_remove($tag)
{
    return preg_replace('~\s+type=["\'][^"\']++["\']~', '', $tag);
}

function remove_thumbnail_dimensions($html)
{
    $html = preg_replace('/(width|height)=\"\d*\"\s/', "", $html);
    return $html;
}

function html5blankgravatar($avatar_defaults)
{
    $myavatar = get_template_directory_uri() . '/img/gravatar.jpg';
    $avatar_defaults[$myavatar] = "Custom Gravatar";
    return $avatar_defaults;
}

function enable_threaded_comments()
{
    if (!is_admin()) {
        if (is_singular() and comments_open() and (get_option('thread_comments') == 1)) {
            wp_enqueue_script('comment-reply');
        }
    }
}

function html5blankcomments($comment, $args, $depth)
{
    $GLOBALS['comment'] = $comment;
    extract($args, EXTR_SKIP);
    if ('div' == $args['style']) {
        $tag = 'div';
        $add_below = 'comment';
    } else {
        $tag = 'li';
        $add_below = 'div-comment';
    }
    ?>
    <?php echo $tag ?>     <?php comment_class(empty($args['has_children']) ? '' : 'parent') ?>
    id="comment-<?php comment_ID() ?>">
    <?php if ('div' != $args['style']): ?>
        <div id="div-comment-<?php comment_ID() ?>" class="comment-body">
        <?php endif; ?>
        <div class="comment-author vcard">
            <?php if ($args['avatar_size'] != 0)
                echo get_avatar($comment, $args['180']); ?>
            <?php printf(__('<cite class="fn">%s</cite> <span class="says">says:</span>'), get_comment_author_link()) ?>
        </div>
        <?php if ($comment->comment_approved == '0'): ?>
            <em class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.') ?></em>
            <br />
        <?php endif; ?>
        <div class="comment-meta commentmetadata"><a
                href="<?php echo htmlspecialchars(get_comment_link($comment->comment_ID)) ?>">
                <?php printf(__('%1$s at %2$s'), get_comment_date(), get_comment_time()) ?></a><?php edit_comment_link(__('(Edit)'), '  ', ''); ?>
        </div>
        <?php comment_text() ?>
        <div class="reply">
            <?php comment_reply_link(array_merge($args, array('add_below' => $add_below, 'depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
        </div>
        <?php if ('div' != $args['style']): ?>
        </div>
    <?php endif; ?>
<?php }

add_action('init', 'html5blank_header_scripts');
add_action('wp_print_scripts', 'html5blank_conditional_scripts');
add_action('get_header', 'enable_threaded_comments');
add_action('wp_enqueue_scripts', 'html5blank_styles');
add_action('widgets_init', 'my_remove_recent_comments_style');
add_action('init', 'html5wp_pagination');

remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
remove_action('wp_head', 'rel_canonical');
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

add_filter('avatar_defaults', 'html5blankgravatar');
add_filter('body_class', 'add_slug_to_body_class');
add_filter('widget_text', 'do_shortcode');
add_filter('widget_text', 'shortcode_unautop');
add_filter('wp_nav_menu_args', 'my_wp_nav_menu_args');
add_filter('style_loader_tag', 'html5_style_remove');
add_filter('post_thumbnail_html', 'remove_thumbnail_dimensions', 10);
add_filter('image_send_to_editor', 'remove_thumbnail_dimensions', 10);

remove_filter('the_excerpt', 'wpautop');

function modify_jquery_version()
{
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_register_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js', false, '2.0.s');
        wp_enqueue_script('jquery');
    }
}
add_action('init', 'modify_jquery_version');

require_once('wp_bootstrap_navwalker.php');




require_once get_template_directory() . '/includes/funcionalidad-sidebar-carrito.php'; //AOG 2 sep
require_once get_template_directory() . '/includes/funcionalidad-taxonomias-categorias.php';
require_once get_template_directory() . '/includes/funcionalidades-tema.php';
require_once get_template_directory() . '/includes/funcionalidad-tiendas.php';
require_once get_template_directory() . '/includes/shortcode-blog.php';
require_once get_template_directory() . '/includes/taxonomias-imagenes.php';
require_once get_template_directory() . '/includes/colores-personalizados.php';
require_once get_template_directory() . '/includes/funcionalidad-producto-single.php';
require_once get_template_directory() . '/includes/hook-banderas.php';
require_once get_template_directory() . '/includes/acf-estilos-administrador.php';
require_once get_template_directory() . '/includes/funcionalidad-boton-inicio-sesion.php';
require_once get_template_directory() . '/includes/shortcode-slick-productos.php';
require_once get_template_directory() . '/includes/filtros-categoria-producto.php';
require_once get_template_directory() . '/includes/facturacion.php';
require_once get_template_directory() . '/includes/megamenu-productos.php';
require_once get_template_directory() . '/includes/shortcode-barra-promociones.php'; //AOG 2 sep
require_once get_template_directory() . '/includes/shortcode-slider-giros-negocio.php';  //AOG 2 sep
require_once get_template_directory() . '/includes/shortcode-slider-banners-cinturon.php';  //AOG 2 sep
require_once get_template_directory() . '/includes/shortcode-slider-marcas.php';  //AOG 2 sep
require_once get_template_directory() . '/includes/menu-giros-negocio.php';  //AOG 2 sep
require_once get_template_directory() . '/includes/shortcode-faqs.php';  //AOG 2 sep



add_action('wp_head', 'mi_analytics', 20);

function mi_analytics()
{
    ?>
    <!-- Google tag (gtag.js) -->
    <script async src=""></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-');
    </script>
    <?php
}


/**
 * Redirecciona URLs antiguas con estructura tipo PrestaShop (.html) 
 * al producto correspondiente en WooCommerce si se encuentra un match por slug.
 *
 * Ejemplo:
 * /whisky-single-malt/whisky-glenmorangie-lasanta-12-anos-750ml.html
 * → redirige a /producto/whisky-glenmorangie-lasanta-12-anos-750ml/
 */
add_action('init', function () {
    // Solo ejecuta en frontend
    if (is_admin())
        return;

    $request_uri = $_SERVER['REQUEST_URI'];

    // Detecta URLs antiguas que terminan en .html
    if (preg_match('/\/([^\/]+)\.html$/', $request_uri, $matches)) {
        $slug = sanitize_title($matches[1]);

        // Buscar producto por slug exacto (post_name)
        $product = get_page_by_path($slug, OBJECT, 'product');

        if ($product) {
            $new_url = get_permalink($product->ID);

            // Redirecciona con 301 permanente
            wp_redirect($new_url, 301);
            exit;
        }
    }
});

/*****
 **
 ** PROMO 2X1
 ***/


//  Función para obtener SKUs con descuento desde uploads
function obtener_skus_2x1()
{
    $archivo = WP_CONTENT_DIR . '/themes/lanaval/configuracion2x1.php';

    if (file_exists($archivo)) {
        return include $archivo;
    }
    return []; // Si no existe, devuelve array vacío
}

// No se usara esto de aqui, comentado por Dens | 2025-09-26
//  Aplicar descuento en carrito
// add_action('woocommerce_before_calculate_totals', 'aplicar_2x1_por_sku', 20);
// function aplicar_2x1_por_sku($cart)
// {
//     error_log('aplicar_2x1_por_sku');
//     if (is_admin() && !defined('DOING_AJAX'))
//         return;

//     $skus_promocion = obtener_skus_2x1();

//     foreach ($cart->get_cart() as $cart_item) {
//         $product = $cart_item['data'];
//         if (in_array($product->get_sku(), $skus_promocion)) {
//             $cantidad = $cart_item['quantity'];
//             if ($cantidad >= 2) {
//                 $precio_original = $product->get_regular_price();
//                 $pares = floor($cantidad / 2);
//                 $descuento_total = ($pares * $precio_original) / $cantidad;
//                 $product->set_price($precio_original - $descuento_total);
//             }
//         }
//     }
// }

//  Badge en listado
add_action('woocommerce_before_shop_loop_item_title', 'badge_2x1_en_listado', 9);
function badge_2x1_en_listado()
{
    global $product;
    $skus_promocion = obtener_skus_2x1();

    if (in_array($product->get_sku(), $skus_promocion)) {
        echo '<span class="promo-2x1-badge" style="position:absolute;top:10px;left:10px;background:#d00;color:#fff;padding:4px 8px;border-radius:3px;font-size:13px;font-weight:bold;z-index:5;">2x1</span>';
    }
}


// 🔹 Precio elegante + tag 2x1 alineado correctamente en Single Product
add_action('wp_footer', 'forzar_precio_2x1_single_js_elegante');
function forzar_precio_2x1_single_js_elegante()
{
    if (!is_product())
        return;

    global $product;
    if (!$product)
        return;

    $skus_promocion = obtener_skus_2x1();
    if (in_array($product->get_sku(), $skus_promocion)) {
        $precio_original = wc_price($product->get_regular_price());
        $precio_2x1 = wc_price($product->get_regular_price() / 2);
        ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                let priceContainer = document.querySelector(".product-price-sale");
                if (priceContainer) {
                    // ✅ Contenedor flex: precio original, precio 2x1 y tag juntos
                    priceContainer.innerHTML = `
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
       
       <ins style="font-size:32px;font-weight:bold;"><?php echo $precio_original; ?></ins>
                <div style="display:flex;align-items:center;gap:8px;">
               
                    <span class="product-discount-tag">
                        <span class="mini-texto">Ahorre</span>
                        <span class="mini-descuento">2x1</span>
                    </span>
                </div>
            </div>
            
        `;
                }
            });
        </script>
        <?php
    }
}


// Permitir SVG solo a administradores
add_filter('upload_mimes', function ($mimes) {
    if (current_user_can('manage_options')) {
        $mimes['svg'] = 'image/svg+xml';
        $mimes['svgz'] = 'image/svg+xml';
    }
    return $mimes;
});

// Corregir chequeo estricto de WP (tipo/ext)
add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (in_array($ext, ['svg', 'svgz'])) {
        $data['ext'] = 'svg';
        $data['type'] = 'image/svg+xml';
        $data['proper_filename'] = $filename;
    }
    return $data;
}, 10, 4);

require_once get_stylesheet_directory() . '/functions-extend.php';
