<?php
foreach ($terms as $p => $term) {
    if ($p == $locIndex) {
        $quanAtLocation = get_post_meta($product_id, "wcmlim_stock_at_{$term->term_id}", true);
    }
}

if (empty($quanAtLocation) || $quanAtLocation == 0) {
    $locStockStatus = "outofstock";
} else {
    $locStockStatus = "instock";
}
return $locStockStatus;