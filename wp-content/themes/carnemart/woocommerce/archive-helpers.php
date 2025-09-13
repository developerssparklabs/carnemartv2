<?php
defined('ABSPATH') || exit;

/**
 * Devuelve un resumen ligero del archivo Woo actual.
 * - Contextos: tienda, product_cat (padre/hijo), product_tag.
 * - El total se calcula con un COUNT barato (límite=1 + paginación=verdadero).
 * - Para categorías, se incluyen los hijos en el total.
 *
 * @return array{
 *   context: string,
 *   total: int,
 *   term_id?: int,
 *   term_name?: string,
 *   term_slug?: string,
 *   is_parent?: bool,
 *   subcategories?: array<int, array{term_id:int,name:string,slug:string,count:int,link:string}>
 * }
 */
function cm_get_archive_summary(): array
{
    $summary = ['context' => 'unknown', 'total' => 0];

    // Base ligera pero consistente con el loop
    $base = [
        'status' => ['publish'],
        'limit' => 1,        // solo para obtener ->total
        'paginate' => true,
        'return' => 'ids',
        'catalog_visibility' => 'visible', // respeta visibilidad de catálogo
    ];

    // Ocultar agotados si la tienda lo tiene activo
    if ('yes' === get_option('woocommerce_hide_out_of_stock_items')) {
        $base['stock_status'] = 'instock';
    }

    // Heredar filtros EXACTOS del loop (precio, atributos, outofstock, exclude-from-catalog, etc.)
    $meta_query = WC()->query->get_meta_query();
    $tax_query = WC()->query->get_tax_query();

    // Shop
    if (is_shop()) {
        $args = array_merge($base, compact('meta_query', 'tax_query'));
        $r = wc_get_products($args);
        $summary['context'] = 'shop';
        $summary['total'] = (int) ($r->total ?? 0);
        return $summary;
    }

    // Categoría (incluye hijos)
    if (is_product_category()) {
        $term = get_queried_object();
        if ($term && !is_wp_error($term)) {

            $parent_tax = $tax_query;
            $parent_tax[] = [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => [(int) $term->term_id],
                'include_children' => true,
            ];

            $args = array_merge($base, compact('meta_query'));
            $args['tax_query'] = $parent_tax;

            $r = wc_get_products($args);

            $summary['context'] = 'product_cat';
            $summary['total'] = (int) ($r->total ?? 0);
            $summary['term_id'] = (int) $term->term_id;
            $summary['term_name'] = (string) $term->name;
            $summary['term_slug'] = (string) $term->slug;
            $summary['is_parent'] = ((int) $term->parent === 0);

            // Recalcular counts de los hijos con los mismos filtros del loop
            $children = get_terms([
                'taxonomy' => 'product_cat',
                'hide_empty' => true,
                'parent' => (int) $term->term_id,
            ]);

            $summary['subcategories'] = [];
            if (!is_wp_error($children) && !empty($children)) {
                foreach ($children as $child) {
                    $child_tax = $tax_query;
                    $child_tax[] = [
                        'taxonomy' => 'product_cat',
                        'field' => 'term_id',
                        'terms' => [(int) $child->term_id],
                        'include_children' => true, // o false si solo quieres el término directo
                    ];
                    $child_args = array_merge($base, compact('meta_query'));
                    $child_args['tax_query'] = $child_tax;

                    $cr = wc_get_products($child_args);
                    $count = (int) ($cr->total ?? 0);

                    $summary['subcategories'][] = [
                        'term_id' => (int) $child->term_id,
                        'name' => (string) $child->name,
                        'slug' => (string) $child->slug,
                        'count' => $count, // ya filtrado (visibilidad/stock/filtros)
                        'link' => (string) get_term_link($child),
                    ];
                }
            }
        }
        return $summary;
    }

    // Etiquetas
    if (is_product_tag()) {
        $term = get_queried_object();
        if ($term && !is_wp_error($term)) {
            $tq = $tax_query;
            $tq[] = [
                'taxonomy' => 'product_tag',
                'field' => 'term_id',
                'terms' => [(int) $term->term_id],
            ];
            $args = array_merge($base, compact('meta_query'));
            $args['tax_query'] = $tq;

            $r = wc_get_products($args);

            $summary['context'] = 'product_tag';
            $summary['total'] = (int) ($r->total ?? 0);
            $summary['term_id'] = (int) $term->term_id;
            $summary['term_name'] = (string) $term->name;
            $summary['term_slug'] = (string) $term->slug;
        }
        return $summary;
    }

    return $summary;
}

/**
 * Ayudante para repetir el recuento (puede utilizarlo en una plantilla).
 */
function cm_echo_archive_total(): void
{
    $s = cm_get_archive_summary();
    $total = (int) ($s['total'] ?? 0);
    $suffix = !empty($s['term_name']) ? ' en ' . esc_html($s['term_name']) : '';

    printf(
        '<p class="woocommerce-result-count" role="status">%s</p>',
        sprintf(
            _n('%1$d producto%2$s', '%1$d productos%2$s', $total, 'cm'),
            $total,
            $suffix
        )
    );
}