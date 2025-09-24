<?php
if (!defined('ABSPATH'))
    exit;

if (!class_exists('WC_Product_Cat_List_Walker')) {
    include_once WC()->plugin_path() . '/includes/walkers/class-wc-product-cat-list-walker.php';
}

class Custom_WC_Product_Cat_List_Walker extends WC_Product_Cat_List_Walker
{
    public function start_el(&$output, $category, $depth = 0, $args = [], $id = 0)
    {
        $cat_name = esc_html($category->name);
        $term_id = $category->term_id;

        $output .= "<li class='cat-item cat-item-{$term_id} wp-cat-check-item'>";
        $output .= "<label class='wp-cat-check-label'>";
        $output .= "<input type='checkbox' class='cat-filter-checkbox' name='cat_p_filter[]' value='{$term_id}'>";
        $output .= "<span class='cat-filter-text' style='margin-left: 10px;'>{$cat_name}</span>";
        $output .= "</label>";
        $output .= "</li>\n";
    }
}