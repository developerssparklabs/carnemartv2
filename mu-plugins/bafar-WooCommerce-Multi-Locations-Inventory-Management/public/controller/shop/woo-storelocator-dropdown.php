<?php
global $post;
$Inline_title = get_option("wcmlim_txt_inline_location", true);									
$is_preferred = get_option('wcmlim_preferred_location');
$geolocation = get_option('wcmlim_geo_location');
$useLc = get_option('wcmlim_enable_autodetect_location');
$uspecLoc = get_option('wcmlim_enable_userspecific_location');
$show_in_popup = get_option("wcmlim_show_in_popup");
$product = wc_get_product($post->ID);
$storelocator_list = $this->wcmlim_get_all_store();
$locations_list = $this->wcmlim_get_all_locations();										
$selected_location = $this->get_selected_location();
$terms = get_terms(array('taxonomy' => 'location_group', 'hide_empty' => false, 'parent' => 0));
    if (sizeof($locations_list) > 0) { 											
            
        if ($product instanceof WC_Product && $product->is_type('variable') && !$product->is_downloadable() && !$product->is_virtual()) { ?>									
        <select class="sel_location Wcmlim_sel_loc" name="sel_location">
        <option value="-1"><?php echo esc_html('- Select Location -', 'wcmlim'); ?></option>
        </select>
        <div class="wcmlim-lcswitch" style="display:none;" >
                    <?php } else { ?>
                    <div class="wcmlim-lcswitch">
                    <?php } ?>
                    <div class="wcmlim_sel_location wcmlim_storeloc">
                        
                        <select name="wcmlim_change_sl_to" class="wcmlim_changesl" id="wcmlim-change-sl-select">
                        <?php if(count($terms)==0){?>	
                            <option value="-1"><?php _e('No group found', 'wcmlim') ?></option>
                        <?php }else { ?>
                                <option value="-1"><?php _e('Select City or Area', 'wcmlim') ?></option>
                        <?php }
                            foreach ($storelocator_list as $key => $loc) {
                            ?>
                                <option class="<?php echo 'wclimstore_'.$loc['store_id']; ?>" value="<?php echo $loc['store_id']; ?>"><?php echo ucfirst($loc['store_name']); ?></option>
                            <?php
                            }
                            ?>
                        </select>		
                                            
                        <select class="wcmlim_lcselect" name="wcmlim_change_lc_to" id="wcmlim-change-lcselect">
                            <option value="-1" <?php if (!$selected_location) echo "selected='selected'"; ?>><?php _e('Seleccionar', 'wcmlim') ?></option>
                
                        </select>
                        
                                                                            
                    </div>	
                    </div>	
                    <script>
                        jQuery(document).ready(function () {
                            // Obtener valores de las cookies en PHP
                            var regiExists = "<?php echo isset($_COOKIE['wcmlim_selected_location_regid']) ? $_COOKIE['wcmlim_selected_location_regid'] : ""; ?>";
                            var termiExists = "<?php echo isset($_COOKIE['wcmlim_selected_location_termid']) ? $_COOKIE['wcmlim_selected_location_termid'] : ""; ?>";

                            // Aplicar selección en los elementos según las cookies
                            if (regiExists) {
                                jQuery('.wcmlim_changesl option[value="' + regiExists + '"]').prop("selected", true);
                            }

                            if (termiExists) {
                                jQuery('.wcmlim_lcselect option[data-lc-term="' + termiExists + '"]').prop("selected", true);
                            }
                        });
                        </script>


                
        <!-- default design End-->
    <?php }		