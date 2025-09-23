<?php

if( !function_exists( 'wcmmq_pro_enqueue' ) ){
    /**
     * CSS or Style file add for FrontEnd Section. 
     * 
     * @since 1.0.0
     */
    function wcmmq_pro_enqueue(){

        /**
         * A simple jQuery function that can add listeners on attribute change.
         * http://meetselva.github.io/attrchange/
         * 
         * @since 1.9
         */
        wp_register_script( 'attrchange', WC_MMQ_PRO_BASE_URL . 'assets/js/attrchange.js', array( 'jquery' ), '1.0', true );
        wp_enqueue_script( 'attrchange' );
        
        
        wp_register_script( 'wcmmq-pro-script', WC_MMQ_PRO_BASE_URL . 'assets/js/variation-wcmmq.js', array( 'jquery' ), '1.0.0', true );
        wp_enqueue_script( 'wcmmq-pro-script' );

        wp_register_style( 'wcmmq-pro_front_css', WC_MMQ_PRO_BASE_URL . 'assets/css/wcmmq-front.css', false, WC_MMQ_PRO::getVersion() );
        wp_enqueue_style( 'wcmmq-pro_front_css' );
        
        
        $product_type = false;
        if( is_product() ){
            $product = wc_get_product( get_the_ID() );
            $product_type = $product->get_type();
        }
        $WCMMQ_DATA = array( 
            'product_type' => $product_type,
            );
        $WCMMQ_DATA = apply_filters( 'wcmmq_localize_data', $WCMMQ_DATA );
        wp_localize_script( 'wcmmq-pro-script', 'WCMMQ_DATA ', $WCMMQ_DATA );
    }
}
add_action( 'wp_enqueue_scripts', 'wcmmq_pro_enqueue', 99 );

