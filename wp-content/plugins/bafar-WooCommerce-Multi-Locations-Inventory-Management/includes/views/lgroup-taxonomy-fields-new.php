<div class="form-field term-Regional-wrap">
  <label class="" for="wcmlim_email_regmanager"><?php esc_html_e('Email', 'wcmlim'); ?></label>
  <input class="form-control" id="wcmlim_email_regmanager" name="wcmlim_email_regmanager" type="text" />
</div>
<div class="form-field term-Regional-wrap">
  <label class="" for="location"><?php esc_html_e('Location', 'wcmlim'); ?></label>
  <?php
  $location_manage = array(
    'taxonomy' => 'locations',
    'hide_empty' => 0,
  );
  $selected_locations = isset($term->term_id) ? get_term_meta($term->term_id, 'wcmlim_location', false) : array();
  $locations = get_terms($location_manage);
  echo '<select multiple="multiple" name="wcmlim_location[]" id="location" class="form-control">';
  echo '<option value="">' . esc_html__('Please Select', 'wcmlim') . '</option>';
  foreach ($locations as $location) {
    $selected = in_array($location->term_id, (array) (isset($selected_locations[0]) ? $selected_locations[0] : array())) ? 'selected="selected"' : '';
    echo '<option value="' . esc_attr($location->term_id) . '" ' . $selected . '>' . esc_html($location->name) . '</option>';
  }
  echo '</select>';
  ?>
</div>

<?php $esp = get_option("wcmlim_assign_location_shop_manager");
if ($esp == "on") { ?>
  <div class="form-field term-Regional-wrap">
    <label class="" for="wcmlim_shop_regmanager"><?php esc_html_e('Location Regional Manager', 'wcmlim'); ?></label>
    <select multiple="multiple" class="multiselect" name="wcmlim_shop_regmanager[]" id="wcmlim_shop_regmanager">
      <?php
      $args = [
        'role' => 'location_regional_manager'
      ];
      $all_users = get_users($args);
      foreach ((array) $all_users as $key => $user) {
        ?>
        <option value="<?php esc_html_e($user->ID); ?>"><?php esc_html_e($user->display_name); ?></option>
      <?php } ?>
    </select>
  </div>
<?php } ?>