<?php
$wc_base_country = WC()->countries->get_base_country();
$wc_states = WC()->countries->get_states( $wc_base_country );

$tax_rate_object = WC_Tax::get_rates_from_location($tax_class,
    [
        $wc_base_country,
        array_search( strtolower( $store_meta['wcmlim_administrative_area_level_1'][0] ), array_map( 'strtolower',$wc_states ) ),
        $store_meta["wcmlim_postal_code"][0],
        strtoupper($store_meta['wcmlim_locality'][0])
    ], null);

return $tax_rate_object;