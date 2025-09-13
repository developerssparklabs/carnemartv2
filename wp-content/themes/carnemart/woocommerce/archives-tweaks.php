<?php
defined('ABSPATH') || exit;

// Vacía la query principal en tienda/categorías
function cm_woo_archive_empty_main_query(WP_Query $q)
{
    if (is_admin() || !$q->is_main_query())
        return;
    if (!(is_shop() || is_product_taxonomy()))
        return;

    // 0 resultados y sin COUNT(*)
    $q->set('post__in', [0]);
    $q->set('no_found_rows', true);
}
add_action('pre_get_posts', 'cm_woo_archive_empty_main_query');

// Quita UI nativa (contador/orden/paginación)
function cm_woo_archive_remove_default_ui()
{
    if (!(is_shop() || is_product_taxonomy()))
        return;

    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
    remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 10);
}
add_action('template_redirect', 'cm_woo_archive_remove_default_ui');