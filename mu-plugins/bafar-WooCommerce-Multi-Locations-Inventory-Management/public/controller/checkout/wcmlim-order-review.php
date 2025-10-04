<?php
   
    $zipcode = sanitize_text_field($_POST['zipCode']);
    $city = sanitize_text_field($_POST['city']);
    $state = sanitize_text_field($_POST['state']);
    $country = sanitize_text_field($_POST['country']);
    if (is_user_logged_in()) {
                // Get the user's ID
        $user_id = get_current_user_id();
        
        // Update the billing address for the logged-in user
        update_user_meta($user_id,'billing_state',$state);
        update_user_meta($user_id,'billing_country',$country);
        update_user_meta($user_id,'billing_postcode',$zipcode);
        update_user_meta($user_id,'billing_city',$city);


        update_user_meta($user_id,'shipping_state',$state);
        update_user_meta($user_id,'shipping_country',$country);
        update_user_meta($user_id,'shipping_postcode',$zipcode);
        update_user_meta($user_id,'shipping_city',$city);
 
    
            
    } 
    else { 
        
        // Optionally,you can also update the WooCommerce customer's billing state
        WC()->customer->set_billing_state($state);
        WC()->customer->set_billing_country($country);
        WC()->customer->set_billing_city($city);
        WC()->customer->set_billing_postcode($zipcode);

        WC()->customer->set_shipping_state($state);
        WC()->customer->set_shipping_country($country);
        WC()->customer->set_shipping_city($city);
        WC()->customer->set_shipping_postcode($zipcode);

        WC()->customer->save();
    }
   
    die();
    

?>
