<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $product;

if ( $product->is_on_sale() ) {
    $regular_price = floatval( $product->get_regular_price() ); // Precio regular
    $sale_price = floatval( $product->get_sale_price() );       // Precio en oferta

    if ( $regular_price && $sale_price ) {
        $percentage = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
        echo '<span class="onsale">-' . esc_html( $percentage ) . '%</span>';
    } else {
        // Si no hay datos, muestra el badge predeterminado
        echo '<span class="onsale">' . esc_html__( 'Oferta', 'woocommerce' ) . '</span>';
    }
}
?>
