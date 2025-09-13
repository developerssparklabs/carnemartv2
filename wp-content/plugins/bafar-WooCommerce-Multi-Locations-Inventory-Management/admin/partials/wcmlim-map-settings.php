<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.techspawn.com
 * @since      1.0.0
 *
 * @package    Wcmlim
 * @subpackage Wcmlim/admin/partials
 */
?>
<div class="wrap">
    <?php
    if (get_option('wcmlim_license') == '' || get_option('wcmlim_license') == 'invalid') {
    ?>
        <script>
            window.location.href = "?page=multi-location-inventory-management";
        </script>
    <?php
    }
    ?>
    <style type="text/stylesheet">
        code {
            font-size: 12px;
        }
    </style>
    <h1><?php esc_html_e('WooCommerce Multi Locations Inventory Management', 'wcmlim'); ?></h1>
    <?php settings_errors(); ?>
    <ul class="nav nav-tabs">
        <li class="wcmlim-admin-menu-tab-li active"><a href="#tab-1"><?php esc_html_e('Location Finder On Map', 'wcmlim'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div id="tab-1" class="tab-pane active">
            <div class="wcmlim_flex_container">
                <div class="wcmlim_flex_box">
                    <form action="options.php" method="post" class="wcmlim-setting-form">
                        <?php
                        settings_fields('wcmlim_map_settings');
                        do_settings_sections('wcmlim_map_settings');
                        submit_button();
                        ?>
                    </form>
                    <div class="shortcode-instruction-block">
                        <h1>Shortcode Instruction</h1>
                        <hr />
                        <p><?php echo '<h3>Location Finder Map</h3>' . __('To display Location finder on map - use', 'wcmlim') . '<code>[wcmlim_location_finder]</code>  or insert as PHP code into your theme files: <code> &#60;?php echo do_shortcode([wcmlim_location_finder]);?&#62;</code><hr /><h3>Location Finder List</h3>' . __('To display Location finder List View - use', 'wcmlim') . '<code>[wcmlim_location_finder_list_view]</code> or insert as PHP code into your theme files: <code> &#60;?php echo do_shortcode([wcmlim_location_finder_list_view]); ?&#62;</code><hr /><h3>Location Info</h3>' . __('To display Location info - use', 'wcmlim') . '<code>[wcmlim_location_info id=1]</code> or insert as PHP code into your theme files: <code> &#60;?php echo do_shortcode([wcmlim_location_info id=1]); ?&#62;</code> Make sure you are passing location id. Eg. <strong>id = 1</strong>'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>