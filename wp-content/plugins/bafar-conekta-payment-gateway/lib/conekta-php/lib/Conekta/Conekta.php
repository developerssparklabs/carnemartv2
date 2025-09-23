<?php

namespace Conekta;

abstract class Conekta
{
    public static $apiKey;
    public static $apiBase = 'https://api.conekta.io';
    public static $apiVersion = '2.0.0';
    public static $locale = 'es';
    public static $plugin = '';
    public static $pluginVersion = '';
    const VERSION = '4.2.0';

    public static function setApiKey($apiKey)
    {
        /*hardcode by Sparklabs + Naveed */

        $location_id = !empty($_COOKIE['wcmlim_selected_location_termid']) ? $_COOKIE['wcmlim_selected_location_termid'] : '';
    
        if(WP_ENVIRONMENT_TYPE=="dev"){
            $location_apikey = get_term_meta($location_id, 'sandbox_location_api_key', true);
        }else{
            $location_apikey = get_term_meta($location_id, 'location_api_key', true);
        }

       
        if ( !empty($location_apikey) ) {
            $apiKey = $location_apikey;
        }
        self::$apiKey = $apiKey;

    }
    
    public static function setApiVersion($version)
    {
        self::$apiVersion = $version;
    }
    
    public static function setLocale($locale)
    {
        self::$locale = $locale;
    }
    
    public static function setPlugin($plugin = '')
    {
        self::$plugin = $plugin;
    }
    
    public static function setPluginVersion($pluginVersion = '')
    {
        self::$pluginVersion = $pluginVersion;
    }
    
    public static function getPlugin()
    {
        return self::$plugin;
    }
    
    public static function getPluginVersion()
    {
        return self::$pluginVersion;
    }
}
