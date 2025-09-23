<?php
/**
 *  Metodo de envío UBER
 * 
 */


// if ( ! class_exists( 'WC_Uber_Direct_Shipping_Method' ) ) {
if (0) {
    class WC_Uber_Direct_Shipping_Method extends WC_Shipping_Method
    {

        public function __construct()
        {
            $this->id = 'uber_direct_shipping';
            $this->method_title = __('Uber Direct Shipping', 'woocommerce');
            $this->method_description = __('Custom Shipping Method for Uber Direct', 'woocommerce');

            $this->enabled = "yes";
            $this->title = "Uber Direct Shipping";
            $this->init();
        }

        function init()
        {
            $this->init_form_fields();
            $this->init_settings();
            $this->title = $this->get_option('title');
            add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        }

        function init_form_fields()
        {
            $this->form_fields = array(
                'title' => array(
                    'title' => __('Title', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Title to be displayed on site', 'woocommerce'),
                    'default' => __('Uber Direct Shipping', 'woocommerce')
                )
            );
        }

        public function calculate_shipping($package = array())
        {
            $location = isset($_COOKIE['user_location']) ? json_decode(stripslashes($_COOKIE['user_location']), true) : null;


            if ($location && $this->is_in_cdmx($location['latitude'], $location['longitude'])) {
                $cost = 100.00;
            } else {
                $cost = 1000.00;
            }

            $rate = array(
                'id' => $this->id,
                'label' => $this->title,
                'cost' => $cost,
                'calc_tax' => 'per_item'
            );

            $this->add_rate($rate);
        }

        private function is_in_cdmx($latitude, $longitude)
        {
            // Coordenadas aproximadas del centro de CDMX
            $cdmx_center_lat = 19.432608;
            $cdmx_center_lon = -99.133209;
            $radius = 0.1; // 0.1 grados de radio (aproximadamente 11 km)
            $lat_diff = abs($latitude - $cdmx_center_lat);
            $lon_diff = abs($longitude - $cdmx_center_lon);

            return $lat_diff <= $radius && $lon_diff <= $radius;
        }
    }
}

function add_uber_direct_shipping_method2($methods)
{
    $methods['uber_direct_shipping'] = 'WC_Uber_Direct_Shipping_Method';
    return $methods;
}

add_filter('woocommerce_shipping_methods', 'add_uber_direct_shipping_method2');