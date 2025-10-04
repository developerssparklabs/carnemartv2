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
    <h1><?php esc_html_e('WooCommerce Multi Locations Inventory Management', 'wcmlim'); ?></h1>
    <?php settings_errors(); ?>
    <ul class="nav nav-tabs">
        <li class="wcmlim-admin-menu-tab-li active"><a href="#tab-1"><?php esc_html_e('Display Settings', 'wcmlim'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div id="tab-1" class="tab-pane active">
            <div class="wcmlim_flex_container">
                <div class="wcmlim_flex_box">
                    <form action="options.php" method="post">
                        <div style="height: 450px;overflow-y: scroll;">
                        <?php
                        settings_fields('wcmlim_display_settings');
                        do_settings_sections('wcmlim_display_settings');
                        ?>
                        </div>
                        <?php
                        submit_button();
                        ?>
                    </form>
                </div>
                <!-- Design Preview @since 1.1.5 -->
                <div class="wcmlim_flex_box">
                    <h2 style="margin-left:20px"><?php _e('Design Preview', 'wcmlim'); ?> </h2>
                    <?php /**Get Option value */
                    $stockbox_color = get_option("wcmlim_preview_stock_bgcolor", true);
                    $txt_stock_inf = get_option("wcmlim_txt_stock_info", true);
                    $txtcolor_stock_inf = get_option("wcmlim_txtcolor_stock_info", true);
                    $display_stock_inf = get_option("wcmlim_display_stock_info", true);
                    $txt_preferred = get_option("wcmlim_txt_preferred_location", true);
                    $txtcolor_preferred = get_option("wcmlim_txtcolor_preferred_loc", true);
                    $display_preferred = get_option("wcmlim_display_preferred_loc", true);
                    $txt_nearest = get_option("wcmlim_txt_nearest_stock_loc", true);
                    $txtcolor_nearest = get_option("wcmlim_txtcolor_nearest_stock", true);
                    $display_nearest = get_option("wcmlim_display_nearest_stock", true);
                    $color_separator = get_option("wcmlim_separator_linecolor", true);
                    $display_separator = get_option("wcmlim_display_separator_line", true);
                    $oncheck_btntxt = get_option("wcmlim_oncheck_button_text", true);
                    $oncheck_btnbgcolor = get_option("wcmlim_oncheck_button_color", true);
                    $oncheck_btntxtcolor = get_option("wcmlim_oncheck_button_text_color", true);
                    $soldout_btntxt = get_option("wcmlim_soldout_button_text", true);
                    $soldout_btnbgcolor = get_option("wcmlim_soldout_button_color", true);
                    $soldout_btntxtcolor = get_option("wcmlim_soldout_button_text_color", true);
                    $instock_btntxt = get_option("wcmlim_instock_button_text", true);
                    $instock_btnbgcolor = get_option("wcmlim_instock_button_color", true);
                    $instock_btntxtcolor = get_option("wcmlim_instock_button_text_color", true);
                    $border_option = get_option("wcmlim_preview_stock_borderoption", true);
                    $border_color = get_option("wcmlim_preview_stock_bordercolor", true);
                    $border_width = get_option("wcmlim_preview_stock_border", true);
                    $border_radius = get_option("wcmlim_preview_stock_borderradius", true);

                    $refborder_radius = get_option("wcmlim_refbox_borderradius", true);
                    $input_radius = get_option("wcmlim_input_borderradius", true);
                    $oncheck_radius = get_option("wcmlim_oncheck_borderradius", true);
                    $instock_radius = get_option("wcmlim_instock_borderradius", true);
                    $soldout_radius = get_option("wcmlim_soldout_borderradius", true);

                    ?>
                    <style>
                        <?php if ($border_option != "none") { ?>.Wcmlim_container {
                            border-color: <?php echo $border_color; ?>;
                            border-width: <?php echo $border_width; ?>;
                            border-style: <?php echo $border_option; ?>;
                        }

                        <?php } ?>.postcode-checker-div input[type="text"] {
                            border-radius: <?php echo $input_radius; ?>;
                        }

                        .Wcmlim_container {
                            border-radius: <?php echo $border_radius; ?>;
                        }

                        .loc_dd.Wcmlim_prefloc_sel {
                            border-radius: <?php echo $refborder_radius; ?>;
                        }

                        .wcmlim_flex_container {
                            display: flex;
                            flex-wrap: wrap;
                        }

                        .wcmlim_flex_box {
                            flex: 50%;
                        }

                        .Wcmlim_container {
                            background-color: <?php echo $stockbox_color; ?>;
                        }

                        .Wcmlim_box_title {
                            color: <?php echo $txtcolor_stock_inf; ?>;
                        }

                        .loc_dd {
                            color: <?php echo $txtcolor_preferred; ?>;
                        }

                        .postcode-checker-title {
                            color: <?php echo $txtcolor_nearest; ?>;
                        }

                        .Wcmlim_line_seperator {
                            background-color: <?php echo $color_separator; ?>;
                        }

                        #submit_postcode_product {
                            border-radius: <?php echo $oncheck_radius; ?>;
                            color: <?php echo $oncheck_btntxtcolor; ?>;
                            background-color: <?php echo $oncheck_btnbgcolor; ?>;
                        }

                        .Wcmlim_have_stock {
                            border-radius: <?php echo $instock_radius; ?>;
                            color: <?php echo $instock_btntxtcolor; ?>;
                            background-color: <?php echo $instock_btnbgcolor; ?>;
                        }

                        .Wcmlim_over_stock {
                            border-radius: <?php echo $soldout_radius; ?>;
                            color: <?php echo $soldout_btntxtcolor; ?>;
                            background-color: <?php echo $soldout_btnbgcolor; ?>;
                        }

                        <?php if ($display_stock_inf == "on") { ?>.Wcmlim_box_header {
                            display: none;
                        }

                        <?php } ?><?php if ($display_preferred == "on") { ?>.Wcmlim_sloc_label {
                            display: none;
                        }

                        <?php } ?><?php if ($display_nearest == "on") { ?>.postcode-checker-title {
                            display: none;
                        }

                        <?php } ?><?php if ($display_separator == "on") { ?>.Wcmlim_line_seperator {
                            border: none;
                        }

                        <?php } ?>
                    </style>

                    <div class="Wcmlim_container wcmlim_product" style="width: 70%;box-sizing: border-box;position: -webkit-sticky; position: sticky;  top: 0;margin-left:20px;">
                        <div class="Wcmlim_box_wrapper">
                            <div class="Wcmlim_box_content select_location-wrapper">
                                <div class="Wcmlim_prefloc_box">
                                    <div class="Wcmlim_box_header">
                                        <h4 class="Wcmlim_box_title"><?php echo $txt_stock_inf; ?></h4>
                                    </div>
                                    <div class="loc_dd Wcmlim_prefloc_sel">
                                        <label class="Wcmlim_sloc_label" for="select_location">
                                            <?php echo $txt_preferred; ?>
                                        </label>
                                        <i style="font-size: 18px;" class="fa fa-map-marker-alt" aria-hidden="true"></i>
                                        <select style="background:none" class="Wcmlim_sel_loc select_location" name="select_location" id="select_location" required="">
                                            <option value="-1"> - Select - </option>
                                            <option value="0" data-lc-regular-price="<span class=&quot;woocommerce-Price-amount amount&quot;><bdi><span class=&quot;woocommerce-Price-currencySymbol&quot;>£</span>245.00</bdi></span>" data-lc-sale-price="<span class=&quot;woocommerce-Price-amount amount&quot;><bdi><span class=&quot;woocommerce-Price-currencySymbol&quot;>£</span>230.00</bdi></span>">
                                                <?php _e('location 1 - In Stock', 'wcmlim'); ?></option>
                                            <option selected="" value="1" data-lc-regular-price="<span class=&quot;woocommerce-Price-amount amount&quot;><bdi><span class=&quot;woocommerce-Price-currencySymbol&quot;>£</span>255.00</bdi></span>" data-lc-sale-price="<span class=&quot;woocommerce-Price-amount amount&quot;><bdi><span class=&quot;woocommerce-Price-currencySymbol&quot;>£</span>220.00</bdi></span>">
                                                <?php _e('location 2 - Out Of Stock', 'wcmlim'); ?></option>
                                        </select>                                       
                                    </div>
                                    <p id="losm"><b>0</b> <?php _e('in stock', 'wcmlim'); ?> </p>
                                    <p id="globMsg"><b>3</b> <?php _e('in stock', 'wcmlim'); ?> </p>
                                </div>
                                <div class="postcode-checker">
                                    <p class="postcode-checker-title">
                                        <strong class="postcode-checker-strong">
                                            <?php echo $txt_nearest; ?>
                                        </strong>
                                        <a class="postcode-checker-change postcode-checker-change-hide" href="#" data-wpzc-form-open="">
                                            <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-pencil-square" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456l-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"></path>
                                                <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"></path>
                                            </svg>
                                        </a>
                                    </p>
                                    <div class="postcode-checker-div postcode-checker-div-show">
                                        <input type="text" placeholder="Enter Location" required="" class="class_post_code" name="class_post_code" value="" id="class_post_code">
                                        <button class="button submit_postcode" id="submit_postcode_product" style="line-height: 1.4;border: 0;">
                                            <i class="fa fa-map-marker-alt" aria-hidden="true"></i>
                                            <?php echo $oncheck_btntxt; ?>
                                        </button>
                                    </div>

                                    <div class="Wcmlim_loc_label" id="not_stock_availabe" style="margin: 10px 0;">
                                        <div class="Wcmlim_locadd">
                                            <div class="selected_location_detail">
                                                <div id="seloc" class="selected_location_name"><i class="fa fa-dot-circle" aria-hidden="true"></i><?php _e('loaction 2', 'wcmlim'); ?></div>
                                            </div>
                                            <div class="postcode-location-distance"><i class="fa fa-map-marker-alt" aria-hidden="true"></i> <?php _e('11.0 km away', 'wcmlim'); ?></div>
                                        </div>
                                        <div class="Wcmlim_locstock">
                                            <div id="locsoldImg" class="Wcmlim_over_stock"><i class="fa fa-times"></i> <?php echo $soldout_btntxt; ?></div>
                                        </div>
                                    </div>
                                    <div class="Wcmlim_loc_label" id="stock_availabe" style="margin: 10px 0;">
                                        <div class="Wcmlim_locadd">
                                            <div class="selected_location_detail">
                                                <div id="seloc" class="selected_location_name"><i class="fa fa-dot-circle" aria-hidden="true"></i><?php _e('loaction 1', 'wcmlim'); ?></div>
                                            </div>
                                            <div class="postcode-location-distance"><i class="fa fa-map-marker-alt" aria-hidden="true"></i> <?php _e('181.0 km away', 'wcmlim'); ?></div>
                                        </div>
                                        <div class="Wcmlim_locstock">
                                            <div id="locstockImg" class="Wcmlim_have_stock"><i class="fa fa-check"></i> <?php echo $instock_btntxt; ?></div>
                                        </div>
                                    </div>
                                    <div class="Wcmlim_loc_label" id="no_store" style="margin: 10px 0;">
                                        <div class="Wcmlim_locstock Wcmlim_mssgerro">
                                            <div id="locstockImg" class="Wcmlim_noStore"></i><?php _e('No Store Found', 'wcmlim'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
