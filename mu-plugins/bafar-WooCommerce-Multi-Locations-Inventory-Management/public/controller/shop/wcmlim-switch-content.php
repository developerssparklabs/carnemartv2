<?php
$Inline_title = get_option("wcmlim_txt_inline_location", true);
$is_preferred = get_option("wcmlim_preferred_location");
$geolocation = get_option("wcmlim_geo_location");
$useLc = get_option("wcmlim_enable_autodetect_location");
$autodetect_by_maxmind = get_option("wcmlim_enable_autodetect_location_by_maxmind", );
$uspecLoc = get_option("wcmlim_enable_userspecific_location");
$restrictGuest = get_option("wcmlim_enable_restrict_guestuser_location");
$show_in_popup = get_option("wcmlim_show_in_popup");
$isLocationsGroup = get_option("wcmlim_enable_location_group");
$isEditor = wp_get_referer();
if (strpos($isEditor, "action=edit") || strpos($isEditor, "post-new.php")) {
    return;
}
if ($isLocationsGroup == "on" && empty($this->wcmlim_get_all_store())) {
    echo "<p style='color: red;padding:10%;'>No location group found.</p>";
} else {
    if ($restrictGuest == "on" && !is_user_logged_in()) {

        $selectedGuestLoc = get_option("wcmlim_restrict_guest_user_location");
        $locations_list = $this->wcmlim_get_all_locations();
        $selected_location = $this->get_selected_location();
        ?>
        <div class="main-cont">
            <div class="wcmlim-lc-switch">
                <form id="lc-switch-form" class="inline_wcmlim_lc" method="post">
                    <div class="wcmlim_form_box">
                        <div class="wcmlim_sel_location wcmlim_storeloc"
                            style="display: flex !important;flex-direction: column !important;">
                            <div class="wcmlim_change_lc_to new"><?php if ($Inline_title) {
                                echo $Inline_title;
                            } else {
                                _e("Location: ", "wcmlim");
                            } ?></div>
                            <select name="wcmlim_change_lc_to" class="wcmlim-lc-select wcmlim-select-style-general"
                                id="wcmlim-change-lc-select">
                                <option value="-1" <?php if (!$selected_location) {
                                    echo "selected='selected'";
                                } ?>><?php _e("Seleccionar", "wcmlim"); ?></option>
                                <?php foreach ($locations_list as $key => $loc) {
                                    if ($selectedGuestLoc == $loc["location_termid"]) { ?>
                                        <option class="<?php echo "wclimloc_" . $loc["location_slug"]; ?>" value="<?php echo $key; ?>"
                                            data-lc-address="<?php echo base64_encode($loc["location_address"], ); ?>"
                                            data-lc-term="<?php echo $loc["location_termid"]; ?>" <?php if (preg_match('/^\d+$/', $selected_location)) {
                                                   if ($selected_location == $key) {
                                                       echo "selected='selected'";
                                                   }
                                               } ?>>
                                            <?php echo ucfirst($loc["location_name"]); ?>
                                        </option>
                                        <?php
                                    }
                                } ?>
                            </select>
                            <div class="er_location"></div>
                            <?php if ($isLocationsGroup == null || $isLocationsGroup == false) { ?>
                                <div class="rlist_location"></div>
                                <?php
                            } ?>
                        </div>
                        <?php if ($geolocation == "on" || (is_array($show_in_popup) && in_array("location_finder_in_popup", $show_in_popup, ))): ?>
                            <div class="postcode-checker">
                                <div class="postcode_wcmliminput">
                                    <span class="postcode-checker-div postcode-checker-div-show">
                                        <?php
                                        $globpin = isset($_COOKIE["wcmlim_nearby_location"], ) ? $_COOKIE["wcmlim_nearby_location"] : "";
                                        $loc_dis_un = get_option("wcmlim_location_distance", );
                                        ?>
                                        <input type="text" placeholder="<?php _e("Enter Pincode/Zipcode", "wcmlim", ); ?>" required
                                            class="class_post_code_global" name="post_code_global" value="<?php if ($globpin != 0) {
                                                esc_html_e($globpin);
                                            } ?>" id="elementIdGlobalCode">
                                        <input type="button" class="button" id="submit_postcode_global"
                                            value="<?php _e("Buscar", "wcmlim", ); ?>">
                                        <input type='hidden' name="global_postal_check" id='global-postal-check' value='true'>
                                        <input type='hidden' name="global_postal_location" id='global-postal-location'
                                            value='<?php esc_html_e($globpin, ); ?>'>
                                        <input type='hidden' name="product_location_distance" id='product-location-distance'
                                            value='<?php esc_html_e($loc_dis_un, ); ?>'>
                                    </span>
                                </div>
                                <?php if ($useLc == "on") { ?>
                                    <div class="wclimlocsearch">
                                        <a id="currentLoc" class="currentLoc">
                                            <i class="fas fa-crosshairs currentLoc"></i>
                                            &nbsp; Usar ubicación actual 106
                                        </a>
                                    </div>
                                    <?php
                                } ?>
                                <div class="search_rep">
                                    <div class="postcode-checker-response"></div>
                                    <a class="postcode-checker-change postcode-checker-change-show" href="#" data-wpzc-form-open=""
                                        style="display: none;">
                                        <i class="fa fa-edit" aria-hidden="true"></i>
                                    </a>
                                </div>
                                <div class="postcode-location-distance"></div>
                            </div>
                            <?php
                        endif; ?>
                    </div>
                    <?php wp_nonce_field("wcmlim_change_lc", "wcmlim_change_lc_nonce", ); ?>
                    <input type="hidden" name="action" value="wcmlim_location_change">
                </form>

            </div>
        </div>
        <?php
    } elseif ($is_preferred == "on" || (!empty($show_in_popup) && (empty($uspecLoc) && get_current_user_id()))) {
        $locations_list = $this->wcmlim_get_all_locations();
        $selected_location = $this->get_selected_location();
        $storelocator_list = [];
        if ($isLocationsGroup == "on") {
            $storelocator_list = $this->wcmlim_get_all_store();
        }
        if (sizeof($locations_list) >= 0 && sizeof($storelocator_list) >= 0) { ?>
            <style>
                /* .espacio {
                                                                                                                                                        display: block;
                                                                                                                                                        height: 20px;
                                                                                                                                                    }

                                                                                                                                                    .zipcodeinfo__box-bloque-2,
                                                                                                                                                    .zipcodeinfo__box-bloque-3,
                                                                                                                                                    .zipcodeinfo__box-form {
                                                                                                                                                        display: none !important;
                                                                                                                                                    }

                                                                                                                                                    .zipcodeinfo__box {
                                                                                                                                                        display: flex;
                                                                                                                                                        text-align: center;
                                                                                                                                                        margin-bottom: 4px;

                                                                                                                                                        h3 {
                                                                                                                                                            font-weight: bold !important;
                                                                                                                                                            font-family: "Oswald", Sans-serif !important;
                                                                                                                                                            color: var(--e-global-color-accent) !important;
                                                                                                                                                            font-size: 22px;
                                                                                                                                                            padding: 0;
                                                                                                                                                            margin: 0 0 10px 0;
                                                                                                                                                        }

                                                                                                                                                        p {
                                                                                                                                                            font-family: "Poppins" !important;
                                                                                                                                                            font-size: 14px;
                                                                                                                                                            line-height: 1.3;
                                                                                                                                                            color: var(--e-global-color-primary) !important;
                                                                                                                                                        }
                                                                                                                                                    }

                                                                                                                                                    .zipcodeinfo__box-form {
                                                                                                                                                        display: flex;
                                                                                                                                                        margin: 0 auto;
                                                                                                                                                        width: 80%;
                                                                                                                                                        padding-top: 13px;

                                                                                                                                                        div {
                                                                                                                                                            display: flex;
                                                                                                                                                            align-items: center;
                                                                                                                                                            gap: 15px;
                                                                                                                                                            width: 100%;
                                                                                                                                                        }
                                                                                                                                                    }

                                                                                                                                                    .zipcodeinfo__box-input {
                                                                                                                                                        height: 50px;
                                                                                                                                                        margin: 10px 0;
                                                                                                                                                        width: 100%;
                                                                                                                                                        text-align: center;
                                                                                                                                                        font-family: "Poppins" !important;
                                                                                                                                                        font-size: 15px;
                                                                                                                                                        border: solid 1px var(--e-global-color-primary) !important;
                                                                                                                                                        color: var(--e-global-color-primary) !important;
                                                                                                                                                    }

                                                                                                                                                    .zipcodeinfo__box-input::placeholder {
                                                                                                                                                        color: var(--e-global-color-primary) !important;
                                                                                                                                                    }

                                                                                                                                                    */
                .zipcodeinfo__box-btn {
                    border: solid 1px var(--e-global-color-accent) !important;
                    background-color: var(--e-global-color-accent) !important;
                    color: #ffffff;
                    font-family: "Poppins" !important;
                    padding-left: 20px !important;
                    padding-right: 20px !important;
                    transition: all ease .1s;
                    /* 
                                                                                                                    &:hover {
                                                                                                                        border: solid 1px var(--e-global-color-accent) !important;
                                                                                                                        background-color: rgba(255, 255, 255, 0) !important;
                                                                                                                        color: var(--e-global-color-accent) !important;
                                                                                                                    } */

                    /* &.full-width {
                                                                                                                        width: 80% !important;
                                                                                                                    }

                                                                                                                    &.negative {
                                                                                                                        border: solid 1px var(--e-global-color-accent) !important;
                                                                                                                        background-color: #ffffff !important;
                                                                                                                        color: var(--e-global-color-accent) !important;
                                                                                                                    } */

                }

                .zipcodeinfo__box-btn-line {
                    font-family: "Poppins" !important;
                    font-size: 14px;
                    color: var(--e-global-color-primary) !important;
                    padding: 15px;
                }

                @media screen and (max-width:680px) {
                    .zipcodeinfo__box-form {
                        width: 100%;
                    }

                    .zipcodeinfo__box-btn {
                        &.full-width {
                            width: 100% !important;
                            font-size: 14px;
                        }
                    }
                }

                /* 
                                                                                                                                                    #mensajesPopup {
                                                                                                                                                        position: relative;
                                                                                                                                                        top: 10px;
                                                                                                                                                    } */

                /* Aplicamos estilos para las tiendas */
                /* .er_location p {
                                                                                                                                                        opacity: 0;
                                                                                                                                                        transform: translateY(20px);
                                                                                                                                                        transition: all 0.5s ease;
                                                                                                                                                    }

                                                                                                                                                    .er_location p.show {
                                                                                                                                                        opacity: 1;
                                                                                                                                                        transform: translateY(0);
                                                                                                                                                    } */
            </style>
            <div class="">
                <div class="zipcodeinfo__box zipcodeinfo__box-bloque-1" id="zipcodeinfoCajaIngresarCP">
                    <!-- <div class="zipcodeinfo__box-contendido">
                        <h3>Estimado Cliente</h3>
                        <p>Por favor seleccione la tienda de su interés o proporcione su dirección para localizar la mas cercana.
                        </p>
                    </div> -->
                    <!-- <a href="#" class="btnCompartirUbicacion">Compartir mi ubicación</a> -->
                    <?php
                    ?>
                    <div id="contentPostalCode" class="zipcodeinfo__box-form postcode-checker-change postcode-checker-change-show"
                        style="display: none">
                        <div>
                            <input type="text" id="elementIdGlobalCode" name="fname" value="" placeholder="Ingresa tu Código Postal"
                                class="zipcodeinfo__box-input">
                            <button type="submit" id="submit_postcode_global" class="zipcodeinfo__box-btn"
                                style="color: #fff !important;background: green !important;">Buscar</button>
                        </div>
                    </div>
                </div>
                <style>
                    .wcmlim-select-style-general {
                        background-color: rgba(255, 255, 255, 0) !important;
                        border: solid 1px var(--color-tercero);
                        border-radius: 8px !important;
                        padding: 10px 20px !important;
                        height: 45px;
                        text-align: left;
                        width: 100%;
                        letter-spacing: 1px;
                        margin-bottom: 10px !important;
                        font-weight: 400;
                        -webkit-appearance: none !important;
                        -webkit-border-radius: 0px;
                        color: var(--color-obscuro);
                        -webkit-appearance: none;
                        /* WebKit */
                        -moz-appearance: none;
                        /* Mozilla */
                        appearance: none;
                        font-family: var(--fuente-regular);
                        font-size: 16px !important;
                    }

                    #wcmlim-change-lc-select {
                        background-color: rgba(255, 255, 255, 0) !important;
                        border: solid 1px var(--color-tercero);
                        border-radius: 8px !important;
                        padding: 10px 20px !important;
                        height: 45px;
                        text-align: left;
                        width: 100%;
                        letter-spacing: 1px;
                        margin-bottom: 10px !important;
                        font-weight: 400;
                        -webkit-appearance: none !important;
                        -webkit-border-radius: 0px;
                        color: var(--color-obscuro);
                        -webkit-appearance: none;
                        /* WebKit */
                        -moz-appearance: none;
                        /* Mozilla */
                        appearance: none;
                        font-family: var(--fuente-regular);
                        font-size: 16px !important;
                    }

                    .wcmlim_change_sl_to,
                    #wcmlim_store_label_popup {
                        font-weight: 700;
                        color: #021b6d;
                    }
                </style>
                <div class="wcmlim-lc-switch">
                    <form id="lc-switch-form" class="inline_wcmlim_lc" method="post">
                        <div class="wcmlim_form_box">
                            <div class="wcmlim_sel_location wcmlim_storeloc"
                                style="display: flex !important;flex-direction: column !important;">
                                <?php
                                $current_user = wp_get_current_user();
                                $current_user_id = get_current_user_id();
                                $current_ui = isset($current_user_id) ? $current_user_id : "";
                                $restricUsers = get_option("wcmlim_enable_userspecific_location", );
                                if ($restricUsers == "on") {
                                    $user_selected_location = get_user_meta($current_ui, "wcmlim_user_specific_location", true, );
                                } else {
                                    $user_selected_location = "";
                                }
                                $wcmlim_selected_location_termid = isset($_COOKIE["wcmlim_selected_location_termid"], ) ? $_COOKIE["wcmlim_selected_location_termid"] : null;
                                $terms = get_terms(["taxonomy" => "location_group", "hide_empty" => false, "parent" => 0,]);
                                $roles = $current_user->roles;
                                if ($isLocationsGroup == "on" && sizeof($terms) > 0) { ?>
                                    <p class="wcmlim_change_sl_to"><?php if ($Inline_title) {
                                        echo "Estado";
                                    } else {
                                        _e("Ciudad: ", "wcmlim");
                                    } ?></p>
                                    <?php if ($restricUsers == "on" && $roles[0] == "customer") { ?>
                                        <select name="wcmlim_change_sl_to" class="wcmlim-select-style-general" id="wcmlim-change-sl-select">
                                            <option value="-1"><?php _e("Selecciona", "wcmlim", ); ?></option>
                                            <?php
                                            foreach ($locations_list as $key => $loc) {
                                                if (preg_match('/^\d+$/', $user_selected_location, )) {
                                                    if ($user_selected_location == $key) {
                                                        $rectricted_location_store_id = $loc["location_storeid"];
                                                    }
                                                }
                                            }
                                            $restricted_store = get_term($rectricted_location_store_id, );
                                            ?>

                                            <option class="<?php echo "wclimstore_" . $restricted_store->term_id; ?>"
                                                value="<?php echo $restricted_store->term_id; ?>">
                                                <?php echo ucfirst($restricted_store->name, ); ?>
                                            </option>
                                        </select>
                                        <?php
                                    } else { ?>
                                        <select name="wcmlim_change_sl_to" class="wcmlim-select-style-general" id="wcmlim-change-sl-select">
                                            <option value="-1"><?php _e("Selecciona", "wcmlim", ); ?></option>
                                            <?php foreach ($storelocator_list as $key => $loc) { ?>
                                                <option class="<?php echo "wclimstore_" . $loc["store_id"]; ?>"
                                                    value="<?php echo $loc["store_id"]; ?>"><?php echo ucfirst($loc["store_name"]); ?></option>
                                                <?php
                                            } ?>
                                        </select>

                                        <?php
                                    } ?>
                                    <p class="wcmlim_change_lc_to" id="wcmlim_store_label_popup"><?php _e("Sucursal: ", "wcmlim", ); ?>
                                    </p>
                                    <?php if ($restricUsers == "on" && $roles[0] == "customer") { ?>
                                        <select name="wcmlim_change_lc_to" class="wcmlim-lc-select wcmlim-select-style-general"
                                            id="wcmlim-change-lc-select">
                                            <option value="-1" <?php if (!$selected_location) {
                                                echo "selected='selected'";
                                            } ?>><?php _e("Seleccionar1", "wcmlim"); ?></option>
                                            <?php foreach ($locations_list as $key => $loc) {
                                                if (preg_match('/^\d+$/', $user_selected_location, )) {
                                                    if ($user_selected_location == $key) { ?>
                                                        <option class="<?php echo "wclimloc_" . $loc["location_slug"]; ?>" value="<?php echo $key; ?>"
                                                            data-lc-address="<?php echo base64_encode($loc["location_address"], ); ?>"
                                                            data-lc-term="<?php echo $loc["location_termid"]; ?>" <?php if (preg_match('/^\d+$/', $user_selected_location)) {
                                                                   if ($user_selected_location == $key) {
                                                                       echo "selected='selected'";
                                                                   }
                                                               } ?>>
                                                            <?php echo ucfirst(strtolower($loc["location_name"])); ?>
                                                        </option>
                                                        <?php
                                                    }
                                                }
                                            } ?>
                                        </select>
                                        <?php
                                    } else { ?>
                                        <select name="wcmlim_change_lc_to" class="wcmlim-lc-select <?php
                                        $lcselect = get_option("wcmlim_enable_location_group", );
                                        if ($lcselect == "on") {
                                            echo "wcmlim-lc-select-2";
                                        }
                                        ?>" id="wcmlim-change-lc-select">
                                            <option value="-1" <?php if (!$selected_location) {
                                                echo "selected='selected'";
                                            } ?>><?php _e("Selecciona", "wcmlim", ); ?></option>
                                        </select>
                                        <?php
                                    }
                                } elseif (!empty($current_user_id) || (empty($current_user_id) && $restricUsers == "")) { ?>
                                    <div class="wcmlim_change_lc_to"><?php if ($Inline_title) {
                                        echo $Inline_title;
                                    } else {
                                        _e("Location: ", "wcmlim");
                                    } ?></div>
                                    <?php if (isset($roles[0]) && $roles[0] == "customer") { ?>
                                        <select name="wcmlim_change_lc_to" class="wcmlim-lc-select wcmlim-select-style-general"
                                            id="wcmlim-change-lc-select">
                                            <option value="-1" <?php if (!$selected_location) {
                                                echo "selected='selected'";
                                            } ?>>
                                                <?php _e("Seleccionar Sucursal", "wcmlim"); ?>
                                            </option>
                                            <?php
                                            $estados = []; // Array para agrupar las localidades por estado
                                            // Primero agrupamos las localidades por estado
                                            foreach ($locations_list as $key => $loc) {

                                                $estado = $loc["ciudad"];

                                                if (!isset($estados[$estado])) {
                                                    $estados[$estado] = [];
                                                }

                                                $nombretienda = $loc["location_name"];
                                                $parts = explode(" - ", $nombretienda);
                                                $parts[0] = str_replace("CMT", "", $parts[0]);
                                                $updatedName = $parts[0];

                                                $estados[$estado][] = ["key" => $key, "slug" => $loc["location_slug"], "address" => base64_encode($loc["location_address"]), "termid" => $loc["location_termid"], "nombretienda" => $updatedName, "ciudad" => $loc["ciudad"],];
                                            }

                                            // Generación del select con agrupación por estado
                                            foreach ($estados as $estado => $locations) {
                                                echo '<optgroup label="' . htmlspecialchars($estado) . '">';
                                                foreach ($locations as $loc) { ?>
                                                    <option class="<?php echo "wclimloc_" . $loc["slug"]; ?>" value="<?php echo $loc["key"]; ?>"
                                                        data-lc-address="<?php echo $loc["address"]; ?>"
                                                        data-lc-term="<?php echo $loc["termid"]; ?>" <?php if (preg_match('/^\d+$/', $user_selected_location) && $user_selected_location == $loc["key"]) {
                                                               echo "selected='selected'";
                                                           } elseif (isset($_COOKIE["wcmlim_selected_location"]) && $_COOKIE["wcmlim_selected_location"] == $loc["key"]) {
                                                               echo "selected='selected'";
                                                           } ?>>
                                                        <?php echo $loc["nombretienda"]; ?>
                                                    </option>
                                                    <?php
                                                }
                                                echo "</optgroup>";
                                            }
                                            ?>
                                        </select>
                                        <?php
                                    } elseif (isset($roles[0]) && $roles[0] != "") { ?>
                                        <select name="wcmlim_change_lc_to" class="wcmlim-lc-select wcmlim-select-style-general"
                                            id="wcmlim-change-lc-select">
                                            <option value="-1" <?php if (!$selected_location) {
                                                echo "selected='selected'";
                                            } ?>><?php _e("Seleccionar tienda1", "wcmlim", ); ?></option>
                                            <?php foreach ($locations_list as $key => $loc) { ?>
                                                <option class="<?php echo "wclimloc_" . $loc["location_slug"]; ?>" value="<?php echo $key; ?>"
                                                    data-lc-address="<?php echo base64_encode($loc["location_address"], ); ?>"
                                                    data-lc-term="<?php echo $loc["location_termid"]; ?>" <?php if (preg_match('/^\d+$/', $selected_location)) {
                                                           if ($selected_location == $key) {
                                                               echo "selected='selected'";
                                                           }
                                                       } ?>>
                                                    <?php echo ucfirst($loc["location_name"]); ?>

                                                </option>
                                                <?php
                                            } ?>
                                        </select>

                                        <?php
                                    } elseif ($restricUsers == "") { ?>
                                        <select name="wcmlim_change_lc_to" class="wcmlim-lc-select wcmlim-select-style-general"
                                            id="wcmlim-change-lc-select">
                                            <option value="-1" <?php if (!$selected_location) {
                                                echo "selected='selected'";
                                            } ?>>
                                                <?php _e("Seleccionar tu Sucursal.", "wcmlim"); ?>
                                            </option>
                                            <?php
                                            $estados = []; // Array para agrupar las localidades por estado
                                            // Primero agrupamos las localidades por estado
                                            foreach ($locations_list as $key => $loc) {

                                                $estado = $loc["ciudad"];

                                                if (!isset($estados[$estado])) {
                                                    $estados[$estado] = [];
                                                }

                                                $nombretienda = $loc["location_name"];
                                                $parts = explode(" - ", $nombretienda);
                                                $parts[0] = str_replace("CMT", "", $parts[0]);
                                                $updatedName = $parts[0];

                                                $estados[$estado][] = ["key" => $key, "slug" => $loc["location_slug"], "address" => base64_encode($loc["location_address"]), "termid" => $loc["location_termid"], "nombretienda" => $updatedName, "ciudad" => $loc["ciudad"],];
                                            }

                                            // Generación del select con agrupación por estado
                                            foreach ($estados as $estado => $locations) {
                                                echo '<optgroup label="' . htmlspecialchars($estado) . '">';
                                                foreach ($locations as $loc) { ?>
                                                    <option class="<?php echo "wclimloc_" . $loc["slug"]; ?>" value="<?php echo $loc["key"]; ?>"
                                                        data-lc-address="<?php echo $loc["address"]; ?>"
                                                        data-lc-term="<?php echo $loc["termid"]; ?>" <?php if (preg_match('/^\d+$/', $user_selected_location) && $user_selected_location == $loc["key"]) {
                                                               echo "selected='selected'";
                                                           } elseif (isset($_COOKIE["wcmlim_selected_location"]) && $_COOKIE["wcmlim_selected_location"] == $loc["key"]) {
                                                               echo "selected='selected'";
                                                           } ?>>
                                                        <?php echo $loc["nombretienda"]; ?>
                                                    </option>
                                                    <?php
                                                }
                                                echo "</optgroup>";
                                            }
                                            ?>
                                        </select>
                                        <?php
                                    }
                                } else { ?>
                                    <div class="wcmlim_change_lc_to"><?php if ($Inline_title) {
                                        echo $Inline_title;
                                    } else {
                                        _e("Location: ", "wcmlim");
                                    } ?></div>
                                    <select name="wcmlim_change_lc_to" class="wcmlim-lc-select wcmlim-select-style-general"
                                        id="wcmlim-change-lc-select">
                                        <option value="-1" <?php if (!$selected_location) {
                                            echo "selected='selected'";
                                        } ?>><?php _e("Seleccionar tienda11", "wcmlim", ); ?></option>
                                        <?php foreach ($locations_list as $key => $loc) { ?>
                                            <option class="<?php echo "wclimloc_" . $loc["location_slug"]; ?>" value="<?php echo $key; ?>"
                                                data-lc-address="<?php echo base64_encode($loc["location_address"], ); ?>"
                                                data-lc-term="<?php echo $loc["location_termid"]; ?>" <?php if (preg_match('/^\d+$/', $selected_location)) {
                                                       if ($selected_location == $key) {
                                                           echo "selected='selected'";
                                                       }
                                                   } ?>>
                                                <?php echo ucfirst($loc["location_name"]); ?>
                                            </option>
                                            <?php
                                        } ?>
                                    </select>
                                    <?php
                                }
                                ?>
                                <div class="er_location"></div>
                                <!-- Radio Listing Mode -->
                                <?php if ($isLocationsGroup == null || $isLocationsGroup == false) { ?>
                                    <div class="rlist_location"></div>
                                    <?php
                                } ?>
                            </div>
                            <?php if ($geolocation == "on" || (is_array($show_in_popup) && in_array("location_finder_in_popup", $show_in_popup, ))): ?>

                                <div class="search_rep">
                                    <div class="postcode-checker-response"></div>
                                    <a class="postcode-checker-change postcode-checker-change-show" href="#"
                                        data-wpzc-form-open="" ">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <i class="
                        fa fa-edit" aria-hidden="true"></i>
                                    </a>
                                </div>
                                <!-- <div class="postcode-checker-keikoxxxx">
                                    <div class="postcode_wcmliminput">
                                        <div class="postcode-checker-div postcode-checker-div-hide">

                                            <?php
                                            $globpin = isset($_COOKIE["wcmlim_nearby_location"], ) ? $_COOKIE["wcmlim_nearby_location"] : "";
                                            $loc_dis_un = get_option("wcmlim_location_distance", );
                                            ?>
                                            <input placeholder="Dirección" type="text" width="100" required
                                                class="class_post_code_global" name="post_code_global" id="elementIdGlobalCode"
                                                style="border: 2px solid green;">
                                            <style>
                                                #elementIdGlobalCode {
                                                    border: 1px solid green;
                                                    /* Borde verde */
                                                    width: 100%;
                                                    /* Ancho completo */
                                                    max-width: 300px;
                                                    /* Máximo ancho */
                                                    height: 40px;
                                                    /* Altura */
                                                    font-size: 18px;
                                                    /* Tamaño de la fuente */
                                                    padding: 10px;
                                                    /* Espaciado interno */
                                                    box-sizing: border-box;
                                                    /* Incluye padding y borde en el ancho total */
                                                }

                                                .postcode-checker {
                                                    width: 100%;
                                                }

                                                @media (max-width: 600px) {
                                                    #elementIdGlobalCode {
                                                        width: 95%;
                                                        /* Ajusta el ancho en pantallas pequeñas */
                                                    }
                                                }
                                            </style>
                                            <input type="button" class="button" id="submit_postcode_global"
                                                value="<?php _e("Buscar", "wcmlim", ); ?>">
                                            <input type='hidden' name="global_postal_check" id='global-postal-check' value='true'>
                                            <input type='hidden' name="global_postal_location" id='global-postal-location'
                                                value='<?php esc_html_e($globpin, ); ?>'>
                                            <input type='hidden' name="product_location_distance" id='product-location-distance'
                                                value='<?php esc_html_e($loc_dis_un, ); ?>'>
                                        </div>
                                    </div>
                                    <?php if ($useLc == "on") { ?>
                                        <div class="wclimlocsearch">
                                            <a id="currentLoc" class="currentLoc">
                                                <i id="currentLoc" class="fas fa-crosshairs currentLoc">
                                                </i>
                                                &nbsp; Use Current Location 685</a>
                                        </div>
                                        <?php
                                    } ?>

                                    <div class="postcode-location-distance"></div>
                                </div> -->
                                <?php
                            endif; ?>
                        </div>
                        <?php wp_nonce_field("wcmlim_change_lc", "wcmlim_change_lc_nonce", ); ?>
                        <input type="hidden" name="action" value="wcmlim_location_change">
                    </form>
                </div>
                <div class="mt-3">
                </div>
                <div id="mensajesPopup">
                </div>
            </div>
            <?php
        }
    }
}
