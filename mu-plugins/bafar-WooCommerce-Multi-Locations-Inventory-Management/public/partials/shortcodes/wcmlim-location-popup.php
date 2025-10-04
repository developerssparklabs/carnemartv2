<?php

/**
 * This file responsible for location popup shortcode
 *
 * @link       http://www.techspawn.com
 * @since      1.2.11
 *
 * @package    Wcmlim
 * @subpackage Wcmlim/public
 */

//20 de enero, este modulo se ajusto para poder incluir a los 
//plugin s8k
class Wcmlim_Location_Popup
{
    public function __construct()
    {

        $locationpopup = get_option('wcmlim_location_popup');
        if ($locationpopup == "on") {
            add_shortcode('wcmlim_locations_popup', [$this, 'wcmlim_locations_popup_cb']);
        }
        add_action('wp_enqueue_scripts', [$this, 'my_plugin_assets']);

        // *ajax call for force visitors to choose locations
        add_action("wp_ajax_get_locations", [$this, 'wcmlim_get_locations']);
        add_action("wp_ajax_nopriv_get_locations", [$this, 'wcmlim_get_locations']);

        // *popup position as per setting if set
        add_action("wp_head", [$this, 'wcmlim_set_inline_css']);
    }

    public function wcmlim_set_inline_css()
    {
        $popuppos = get_option('wcmlim_popup_icon_position');
        if (isset($popuppos) && $popuppos !== "default") {
            if ($popuppos == "bottom") {
                echo "<style> .set-def-store-popup-btn{margin-right: -39px;margin-bottom: -40px;}</style>";
            }
            if ($popuppos == "top") {
                echo "<style> .set-def-store-popup-btn{ padding-top: 35px !important;margin-right: -43px;}</style>";
            }
            if ($popuppos == "left") {
                echo "<style> .set-def-store-popup-btn{ padding-right: 5px !important; margin-left:10px;padding-bottom: 2px !important }</style>";
            }
            if ($popuppos == "right") {
                echo "<style> .set-def-store-popup-btn{ margin-right: -90px;margin-bottom: -15px;}</style>";
            }
        }

    }

    public function my_plugin_assets()
    {
        $show_in_popup = get_option("wcmlim_show_in_popup");
        $force_to_select = get_option("wcmlim_force_to_select_location");

        wp_enqueue_style('wcmlim-magnificPopup', WCMLIM_URL_PATH . 'public/css/magnific-popup-min.css', array(), rand(), 'all');
        wp_enqueue_style('wcmlim-popupcss', WCMLIM_URL_PATH . 'public/css/wcmlim-popup-min.css', array(), rand(), 'all');
        wp_enqueue_script('wcmlim-magnificPopup', WCMLIM_URL_PATH . 'public/js/jquery.magnific-popup.js', array('jquery'), rand(), true);
        wp_enqueue_script('wcmlim-popupjs', WCMLIM_URL_PATH . 'public/js/wcmlim-popup-min.js', array('jquery'), rand(), true);
        wp_localize_script('wcmlim-popupjs', 'multi_inventory_popup', array('ajaxurl' => admin_url('admin-ajax.php'), 'show_in_popup' => $show_in_popup, 'force_to_select' => $force_to_select, ));
    }

    public function wcmlim_locations_popup_cb()
    {
        $request_scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';

        // $isEditor = $uri = $request_scheme . '://' . $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        // if(strpos($isEditor, 'wp-json/wp/')){
        //     return;
        // }
        $wcmlim_this_style_absolution_postition = '';
        // $wcmlim_this_post = get_post();
        //     $wcmlim_this_content = $wcmlim_this_post->post_content;
        //     if($wcmlim_this_content != ''){
        //         $wcmlim_this_has_shortcode = has_shortcode( $wcmlim_this_content, 'wcmlim_locations_switch' );
        //         $wcmlim_this_style_absolution_postition = ($wcmlim_this_has_shortcode) ? 'style="position:absolute;"' : '';
        //     }
        // if (isset($_COOKIE['wcmlim_selected_location']) && $_COOKIE['wcmlim_selected_location'] != -1) {
        //     $locations_list = $this->get_locations_lists();
        //     $select_location = isset($locations_list[$_COOKIE['wcmlim_selected_location']]) ? $locations_list[$_COOKIE['wcmlim_selected_location']] : null;
        //     $select_location['location_name'] = (isset($select_location['location_name'])) ? $select_location['location_name'] : '';
        //     echo '<a id="set-def-store-popup-btn" class="set-def-store-popup-btn" href="#set-def-store">' . $select_location['location_name'] . '</a>';

        // } else {

        //$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));


        // echo '<a id="set-def-store-popup-btn" class="set-def-store-popup-btn" href="#set-def-store" style="display:none;"></a>';
        // }

        if (isset($_COOKIE['wcmlim_selected_location']) && $_COOKIE['wcmlim_selected_location'] != -1) {
            echo '<div id="set-def-store" class="zoom-anim-dialog set-def-store-popup-div mfp-hide">';
        } else {
            echo '<div id="set-def-store" class="zoom-anim-dialog set-def-store-popup-div mfp-hide hide-close">';
        }
        echo '<div>' . do_shortcode("[wcmlim_locations_switch]") . '</div>';
        echo '<button class="mfp-close" title="Close (Esc)" type="button">×</button>';
        echo '</div>';
        ?>
        <?php
        if (!isset($_COOKIE['wcmlim_selected_location']) || $_COOKIE['wcmlim_selected_location'] == -1 || $_COOKIE['wcmlim_selected_location'] == '') {
            ?>
            <script type="text/javascript">
                (function ($) {
                    // console.log('se ejecuto esto.')
                    $('.set-def-store-popup-btn').click();
                })(jQuery);
            </script>
        <?php } ?>
    <?php
    }

    public function get_locations_lists()
    {
        $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
        $result = [];
        $i = 0;
        foreach ($terms as $k => $term) {
            $centro_activo = get_term_meta($term->term_id, 'centro_activo', true);
            if ($centro_activo != "1") {
                //continue;
            }
            $term_meta = get_option("taxonomy_$term->term_id");
            $term_meta = array_map(function ($term) {
                if (!is_array($term)) {
                    return $term;
                }
            }, $term_meta);
            $result[$i]['location_address'] = implode(" ", array_filter($term_meta));
            $result[$i]['location_name'] = $term->name;
            $i++;

        }
        return $result;
    }

    public function wcmlim_get_locations()
    {

        $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));

        $result = [];
        foreach ($terms as $k => $term) {
            $centro_activo = get_term_meta($term->term_id, 'centro_activo', true);
            if ($centro_activo == "1") {
                $term_meta = get_option("taxonomy_$term->term_id");
                $term_meta = array_map(function ($term) {
                    if (!is_array($term)) {
                        return $term;
                    }
                }, $term_meta);
                $result[$k] = $term->name;
            }
        }
        echo json_encode($result);
        wp_die();
    }
}
new Wcmlim_Location_Popup();
