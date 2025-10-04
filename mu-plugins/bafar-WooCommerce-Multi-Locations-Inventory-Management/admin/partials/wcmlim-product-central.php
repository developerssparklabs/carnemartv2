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

$table = new Multiloc_Product_Central();
$search = (isset($_GET['s'])) ? $_GET['s'] : '';
$page_number = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
$table->prepare_items($search,$page_number);
$message = '';
if ('delete' === $table->current_action()) {
  $message = '<div class="updated below-h2" id="message"><p>' . sprintf(esc_html__('Items deleted: %d', 'wcmlim'), count($_REQUEST['id'])) . '</p></div>';
}
?>
<div class="wrap">  
  <div class="pc-woocommerce-layout__header">
    <div class="pc-woocommerce-layout__header-wrapper">
      <h2 ><?php esc_html_e('Product Central', 'wcmlim') ?></h2>
    </div>
  </div>
  <?php esc_attr_e($message, 'wcmlim'); ?>

  <div class="PCtab">
    <ul class="pctabs nav-tab-wrapper woo-nav-tab-wrapper">
      <li class="nav-tab tab-link current" data-tabid = "tab-1"><?php esc_html_e('Products Editor', 'wcmlim') ?></li>
      <li class="nav-tab tab-link" data-tabid = "tab-2"><?php esc_html_e('Settings', 'wcmlim') ?></li>
    </ul>

    <div id="tab-1" class="tab-content current">
      <form id="product-edit-table" method="POST">
        <input type="hidden" name="page" value="<?php esc_attr_e($_REQUEST['page'], 'wcmlim') ?>" />     
        <?php        
        $table->display();       
        ?>
      </form>
    </div>

    <div id="tab-2" class="tab-content">
      <form id="Product_Central_Setting" method="post" action="">
      <table style="width: 100%;">
          <tbody>
            <tr>
              <td style="width: 65%; vertical-align: top;">
              <div class="enable_all">
                <span class="">Enable All</span>&nbsp;
                <label class="switch"><input type="checkbox" id="Enable_ProdCentral_Control" ><span class="slider round"></span></label>
              </div>
              <div class="disable_all">
                <span class="">Disable All</span>&nbsp;
                <label class="switch"><input type="checkbox" id="Disable_ProdCentral_Control" ><span class="slider round"></span></label>
              </div>
                <h3>Columns Settings</h3>
                <?php $get_col = $table->get_columns();
                $get_col_slug = $table->get_sortable_columns(); ?>
                <ul class="SIMS_fields ui-sortable">
                  <?php
                  global $wpdb;
                  $options_table = $wpdb->prefix . 'options';                 
                  $ProductCentralControlOptions = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $options_table . "` WHERE `option_name`= '%s'", 'ProductCentralControlOptions'));
                  $PCFO = maybe_unserialize($ProductCentralControlOptions[0]->option_value);
                  foreach ($get_col as $index => $code) {
                    
                    if (is_array($get_col_slug[$index])) {
                      $col_slug = $get_col_slug[$index][0];
                    } else {
                      $col_slug = $get_col_slug[$index];
                    }
                    if (isset($PCFO)) {
                      if (isset($PCFO[0]) && isset($PCFO[0][$col_slug])) {
                          $checkbox_val = $PCFO[0][$col_slug];
                      } elseif (isset($PCFO[$col_slug])) {
                          $checkbox_val = $PCFO[$col_slug];
                      } else {
                          $checkbox_val = ''; 
                      }
                  } else {
                      $checkbox_val = '';
                  }
                  
                    
                    if ($checkbox_val == 'Show') {
                      $checked_val = 'checked';
                    } else {
                      $checked_val = '';
                    }
                    echo '<li class="SIMS_options_li " id="' . $col_slug . '"><span class="Head_nm">' . $code . '</span><label class="switch"><input type="checkbox" id="' . $col_slug . '" ' . $checked_val . ' chech_status="' . $checkbox_val . '"><span class="slider round"></span></label></li>';
                  }
                  ?>

                </ul>
                <br>
                <input type="button" class="button button-primary button-primary" id="Submit_ProdCentral_Control" value="Save all settings">
              </td>
              <td style="width: 35%; vertical-align: top; padding-left: 7px;">                
              </td>
            </tr>
          </tbody>
        </table>
       
      </form>
    </div>
  </div>
</div>
