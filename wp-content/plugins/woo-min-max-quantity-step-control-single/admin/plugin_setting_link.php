<?php

/**
 * Setting page link from plugin page, so that: user can get easily from Plugin Install page.
 * 
 * @param type $links Getting wordpress default array as name $links
 * @return type Array
 * @since 1.0.0
 */
function wcmmq_add_action_links($links) {

    if( ! defined( 'WC_MMQ_PRO_VERSION' ) ){
        $my_links[] = '<a class="wcmmq-wp-plugin-list-link" href="https://codeastrology.com/min-max-quantity/pricing/?utm_source=MinMax+Dashboard&utm_medium=Free+Version&utm_content=Get+Pro" title="' . esc_attr__( 'Many awesome features is waiting for you', 'woo-min-max-quantity-step-control-single' ) . '" target="_blank">'.esc_html__( 'Get Premium','woo-min-max-quantity-step-control-single' ).'</a>';
    }
    $my_links[] = '<a href="' . admin_url('admin.php?page=wcmmq-min-max-control') . '" title="Setting">Settings</a>';
    $my_links[] = '<a href="https://codeastrology.com/my-support/?utm_source=Product+Table+Dashboard&utm_medium=Free+Version" title="' . esc_attr__( 'CodeAstrology Support', 'woo-min-max-quantity-step-control-single' ) . '" target="_blank">'.esc_html__( 'Get Support','woo-min-max-quantity-step-control-single' ).'</a>';


    
    return array_merge($my_links, $links);
}
add_filter('plugin_action_links_woo-min-max-quantity-step-control-single/wcmmq.php', 'wcmmq_add_action_links');