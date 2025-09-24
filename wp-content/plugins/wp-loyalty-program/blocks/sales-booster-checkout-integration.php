<?php
if (!class_exists('Sales_Booster_Checkout_Integration')) {
    class Sales_Booster_Checkout_Integration {
		
        public function __construct() {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
            add_action('woocommerce_before_checkout_form', [$this, 'inject_checkout_data']);
        }
        
        public function enqueue_scripts() {
            if (!is_checkout()) {
                return;
            }
            
            $script_asset_path = WC_LP_DIR . 'build/classic-checkout.asset.php';
            $script_asset = file_exists($script_asset_path)
                ? require $script_asset_path
                : [
                    'dependencies' => ['jquery'],
                    'version' => ''
                ];
            
            wp_enqueue_script(
                'lp-classic-checkout',
                WC_LP_URL . 'build/classic-checkout.js',
                $script_asset['dependencies'],
                $script_asset['version'],
                true
            );
            
            wp_localize_script('lp-classic-checkout', 'lpCheckoutData', $this->get_checkout_data());
        }
        
        public function get_checkout_data() {
            if (!is_user_logged_in() || current_user_can('administrator')) {
                return [];
            }
            
            $user_id = get_current_user_id();
            $crm = get_user_meta($user_id, 'customer_crm', true);
            $username = 'SY_LOYALTY';
            $password = 'aPibafar01*';
            
            $response = wp_remote_post(
                'https://gwd.lineamccoy.com.mx/neptune/api/LOYALTY/INT037',
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Basic ' . base64_encode($username . ':' . $password)
                    ],
                    'body' => '[
                        {
                            "KEY": "IT_CSP_PETICION",
                            "VALUE": "[{\"id_organizacion_ventas\": \"3200\",\"id_cuenta\": \"' . $crm . '\"}]"
                        }
                    ]',
                ]
            );
            
            $data = json_decode($response['body']);
            $data = reset($data->result->IT_CSP_RESPUESTA);
            $points = reset($data->PUNTOSCLIENTER);
            
            return [
                'points' => $points->DISPONIBLES,
                'username' => ucwords(strtolower(wp_get_current_user()->data->display_name)),
                'ajax_url' => admin_url('admin-ajax.php'),
                'apply_coupon_nonce' => wp_create_nonce('apply_coupon'),
            ];
        }
        
        public function inject_checkout_data() {
            echo '<script>
                var lpCheckoutData = ' . json_encode($this->get_checkout_data()) . ';
            </script>';
        }
    }
    
    new Sales_Booster_Checkout_Integration();
}
