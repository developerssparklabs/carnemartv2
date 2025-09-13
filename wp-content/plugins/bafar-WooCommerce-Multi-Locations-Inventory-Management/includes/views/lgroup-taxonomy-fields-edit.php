<table class="form-table" role="presentation">
    <tbody>
        <!-- Multiple Selection for Location -->
        <tr class="form-field term-c-wrap">
            <th>
                <label class="" for="location"><?php esc_html_e('Locations', 'wcmlim'); ?></label>
            </th>
            <td>
                <?php
                $wcmlim_location = get_term_meta($term->term_id, 'wcmlim_location', false);
                $locations = get_terms(array(
                    'taxonomy' => 'locations',
                    'hide_empty' => false,
                ));

                echo '<select multiple="multiple" name="wcmlim_location[]" id="chosen-select" class="multiselect">';
                echo '<option value="">' . esc_html__('Please Select', 'wcmlim') . '</option>';

                foreach ($locations as $location) {
                    $selected = in_array($location->term_id, (array) (isset($wcmlim_location[0]) ? $wcmlim_location[0] : [])) ? 'selected="selected"' : '';
                    ?>
                    <option value="<?php echo esc_attr($location->term_id); ?>" <?php echo $selected; ?>>
                        <?php echo esc_html($location->name); ?>
                    </option>
                    <?php
                }

                echo '</select>';

                ?>

            </td>
        </tr>

        <!-- The rest of your fields remain unchanged -->
        <?php $wcmlim_email_regmanager = get_term_meta($term->term_id, 'wcmlim_email_regmanager', true); ?>
        <tr class="form-field term-c-wrap">
            <th>
                <label class="" for="wcmlim_email_regmanager"><?php esc_html_e('Email', 'wcmlim'); ?></label>
            </th>
            <td>
                <input class="form-control" id="wcmlim_email_regmanager" name="wcmlim_email_regmanager" type="text"
                    value="<?php echo esc_attr($wcmlim_email_regmanager) ?>" />
            </td>
        </tr>

        <?php $espe = get_option("wcmlim_assign_location_shop_manager");
        if ($espe == "on") { ?>
            <tr class="form-field term-c-wrap">
                <th>
                    <label class=""
                        for="wcmlim_shop_regmanager"><?php esc_html_e('Location Regional Manager', 'wcmlim'); ?></label>
                </th>
                <td>
                    <select multiple="multiple" class="multiselect" name="wcmlim_shop_regmanager[]"
                        id="wcmlim_shop_regmanager">
                        <?php
                        $args = ['role' => 'location_regional_manager'];
                        $all_users = get_users($args);
                        foreach ((array) $all_users as $key => $user) {
                            ?>
                            <option value="<?php esc_html_e($user->ID); ?>" <?php if (!empty($groupshopManager)) {
                                  if (in_array($user->ID, $groupshopManager)) {
                                      echo "selected='selected'";
                                  }
                              }
                              ?>><?php esc_html_e($user->display_name); ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>