<?php
if(is_wc_endpoint_url()) {
    return $formatted_meta;
}
if(is_checkout() || is_cart() )
{
return $formatted_meta;
}
foreach($formatted_meta as $key => $meta){
    if(in_array($meta->key, array('_selectedLocationKey'))) {
    
        unset($formatted_meta[$key]);
    }
    if(in_array($meta->key, array('_selectedLocTermId'))) {
    
        unset($formatted_meta[$key]);
    }
}
return $formatted_meta;