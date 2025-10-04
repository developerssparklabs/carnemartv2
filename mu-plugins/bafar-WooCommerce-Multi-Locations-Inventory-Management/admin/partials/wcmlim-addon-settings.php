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
        <li class="wcmlim-admin-menu-tab-li active"><a href="#tab-1"><?php esc_html_e('Addon Plugins', 'wcmlim'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div id="tab-1" class="tab-pane active">
            <div class="wcmlim_flex_container">
                <!-- Addon Plugins @since 1.1.5 -->
                <div class="wcmlim_flex_box">
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
                            border-color: <?php echo $color_separator; ?>;
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
                    
                    <div class="Wcmlim_container wcmlim_product_addon" style="width : 100%;box-sizing: border-box;position: -webkit-sticky; position: sticky;  top: 0;">
                        <div class="Wcmlim_box_wrapper">
                            <div class="Wcmlim_box_content select_location-wrapper">
                                <div class="row" style="    display: block; justify-content: center;">
                                <div class="wcmlim-addon-column" >
                                        <div class="wcmlim-addon-column-container">
                                            <h3 class="wcmlim_price">$29.00</h3>

                                            <img src="https://store.techspawn.com/wp-content/uploads/2023/11/508138dd.jpeg" class="attachment-shop_catalog size-shop_catalog wp-post-image" alt="" loading="lazy">
                                            <div class="wcmlim-addon-column-div">
                                            <a class="para_descrip" href="https://store.techspawn.com/product/smart-location-allocation-for-multi-location/" target="_blank" ><h2>Smart Location Allocation For Multi-Location Adon </h2></a>
                                            </div>
                                            <p>Smart Location Allocation for Multi Location empowers you to assign location <span id="dots">...</span></p>
                                            <a class="button" href="https://store.techspawn.com/product/smart-location-allocation-for-multi-location/" target="_blank"> Buy Now </a>
                                        </div>                                     
                                    </div>
                                 <!---addon product each starts here-->
                                    <div class="wcmlim-addon-column" >
                                        <div class="wcmlim-addon-column-container">
                                            <h3 class="wcmlim_price">Free</h3>
                                        <img src="https://store.techspawn.com/wp-content/uploads/2022/01/SR5.png" class="attachment-shop_catalog size-shop_catalog wp-post-image" alt="" loading="lazy">
                                        <div class="wcmlim-addon-column-div">
                                            <a class="para_descrip" href="https://store.techspawn.com/product/woocommerce-sales-reports-by-location/" target="_blank" ><h2>WooCommerce Sales Reports By Location</h2></a>
                                            </div>
                                            <p>
                                            You can get the sales report by the WooCommerce Multiloation, Shipping Add<span id="dots">...</span></p>
                                            
                                            <a class="button" href="https://store.techspawn.com/product/woocommerce-sales-reports-by-location/" target="_blank"> Buy Now </a>
                                        </div>                                     
                                    </div>
                                    <!---addon product each ends here-->   
                                    <!---addon product each starts here-->
                                    <div class="wcmlim-addon-column" >
                                        <div class="wcmlim-addon-column-container">
                                            <h3 class="wcmlim_price">Free</h3>
                                            <img  src="https://store.techspawn.com/wp-content/uploads/2021/12/image_final_ImprotExport-500x254.png" class="attachment-shop_catalog size-shop_catalog wp-post-image" alt="" loading="lazy" srcset="https://store.techspawn.com/wp-content/uploads/2021/12/image_final_ImprotExport-200x102.png 200w, https://store.techspawn.com/wp-content/uploads/2021/12/image_final_ImprotExport-400x203.png 400w, https://store.techspawn.com/wp-content/uploads/2021/12/image_final_ImprotExport-500x254.png 500w, https://store.techspawn.com/wp-content/uploads/2021/12/image_final_ImprotExport.png 590w" sizes="(max-width: 500px) 100vw, 500px"> 
                                            <div class="wcmlim-addon-column-div">
                                            <a class="para_descrip" href="https://store.techspawn.com/product/import-export-for-multi-location/" target="_blank"><h2>Import Export For Multi Location</h2></a>
                                        </div>
                                           <p>
                                            Importing and exporting products and locations using a CSV File</p>
                                            <a class="button" href="https://store.techspawn.com/product/import-export-for-multi-location/" target="_blank"> Buy Now </a>
                                        </div>                                     
                                    </div>
                                    <!---addon product each ends here-->
                                     <!---addon product each starts here-->
                                    <div class="wcmlim-addon-column" >
                                        <div class="wcmlim-addon-column-container">
                                            <h3 class="wcmlim_price">$25.00 </h3>
                                        <img  src="https://store.techspawn.com/wp-content/uploads/2022/06/Multilocation-Manager-Dashboard.jpg" class="attachment-shop_catalog size-shop_catalog wp-post-image" alt="" loading="lazy" srcset="https://store.techspawn.com/wp-content/uploads/2022/06/Multilocation-Manager-Dashboard.jpg 200w, https://store.techspawn.com/wp-content/uploads/2022/06/Multilocation-Manager-Dashboard.jpg 400w, https://store.techspawn.com/wp-content/uploads/2022/06/Multilocation-Manager-Dashboard.jpg 500w, https://store.techspawn.com/wp-content/uploads/2022/06/Multilocation-Manager-Dashboard.jpg 590w" sizes="(max-width: 500px) 100vw, 500px"> 
                                            <div class="wcmlim-addon-column-div">
                                            <a class="para_descrip" href="https://store.techspawn.com/product/multilocation-dashboard-addon/" target="_blank" ><h2>Multi-Location Manager Dashboard – Addon</h2></a>
                                            </div>
                                            <p>
                                            Multi-Location Manager Dashboard – Addon is used by user managers such as Location<span id="dots">...</span></p>
                                            <a class="button" href="https://store.techspawn.com/product/multilocation-dashboard-addon/" target="_blank"> Buy Now </a>
                                        </div>                                     
                                    </div>
                                    <!---addon product each ends here-->
                                      <!---addon product each starts here-->
                                    <div class="wcmlim-addon-column" >
                                        <div class="wcmlim-addon-column-container">
                                            <h3 class="wcmlim_price" style="    font-size: 18px;">Free For Multilocation Customer</h3>
                                        <img src="https://store.techspawn.com/wp-content/uploads/2022/09/STOCKUPP-POINT-of-SALE_590x317_02.jpg" class="attachment-shop_catalog size-shop_catalog wp-post-image" alt="" loading="lazy">
                                            <div class="wcmlim-addon-column-div">
                                            <a class="para_descrip" href="https://store.techspawn.com/product/stockupp-pos/" target="_blank" ><h2>StockUpp POS For WooCommerce</h2></a>
                                            </div>
                                            <p>Download free copy of POS plugin by using Multilocation purchase code on checkout.<span id="dots">...</span></p>
                                            <a class="button" href="https://store.techspawn.com/product/stockupp-pos/" target="_blank"> Buy Now </a>
                                        </div>                                     
                                    </div>
                                    <!---addon product each ends here-->
                                     <!---addon product each starts here-->
                                    <div class="wcmlim-addon-column" >
                                        <div class="wcmlim-addon-column-container">
                                            <h3 class="wcmlim_price">$25.00</h3>
                                            <img  src="https://store.techspawn.com/wp-content/uploads/2021/12/Split_Order_For_Multi_Location1-500x254.png" class="attachment-shop_catalog size-shop_catalog wp-post-image" alt="" loading="lazy" srcset="https://store.techspawn.com/wp-content/uploads/2021/12/Split_Order_For_Multi_Location1-200x102.png 200w, https://store.techspawn.com/wp-content/uploads/2021/12/Split_Order_For_Multi_Location1-400x203.png 400w, https://store.techspawn.com/wp-content/uploads/2021/12/Split_Order_For_Multi_Location1-500x254.png 500w, https://store.techspawn.com/wp-content/uploads/2021/12/Split_Order_For_Multi_Location1.png 590w" sizes="(max-width: 500px) 100vw, 500px"> 
                                            <div class="wcmlim-addon-column-div"><a class="para_descrip" href="https://store.techspawn.com/product/split-order-for-multi-location/" target="_blank" ><h2>Split Order For Multi Location</h2></a></div>
                                            <p>
                                            Split Order For Multi Location Plugin, splits an order automatically into separate<span id="dots">...</span></p>
                                            <a class="button" href="https://store.techspawn.com/product/split-order-for-multi-location/" target="_blank"> Buy Now </a>
                                        </div>                                     
                                    </div>
                                    <!---addon product each ends here-->
                                     <!---addon product each starts here-->
                                    <div class="wcmlim-addon-column" >
                                        <div class="wcmlim-addon-column-container">
                                            <h3 class="wcmlim_price">$25.00 </h3>
                                            <img  src="https://store.techspawn.com/wp-content/uploads/2022/03/IMG_9868-1-500x254.jpg" class="attachment-shop_catalog size-shop_catalog wp-post-image" alt="" loading="lazy" srcset="https://store.techspawn.com/wp-content/uploads/2022/03/IMG_9868-1-200x102.jpg 200w, https://store.techspawn.com/wp-content/uploads/2022/03/IMG_9868-1-400x203.jpg 400w, https://store.techspawn.com/wp-content/uploads/2022/03/IMG_9868-1-500x254.jpg 500w, https://store.techspawn.com/wp-content/uploads/2022/03/IMG_9868-1.jpg 590w" sizes="(max-width: 500px) 100vw, 500px"> 
                                            <div class="wcmlim-addon-column-div">
                                            <a class="para_descrip" href="https://store.techspawn.com/product/stockupp-splits-package-for-woocommerce/" target="_blank" ><h2>Stockupp Splits Package For WooCommerce</h2></a>
                                            </div>
                                            <p>
                                            StockUpp Splits Package For Woocommerce is plugin which is used to split the shipping<span id="dots1">...</span></p>
                                            <a class="button" href="https://store.techspawn.com/product/stockupp-splits-package-for-woocommerce/" target="_blank"> Buy Now </a>
                                        </div>                                     
                                    </div>
                                    <!---addon product each ends here-->
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
