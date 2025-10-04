<?php

/**
 * All Common Functions of the plugin for admin side.
 *
 * @link       http://www.techspawn.com
 * @since      1.2.10
 *
 * @package    Wcmlim
 * @subpackage Wcmlim/admin/partials
 */

class Wcmlim_Common_Functions
{
    public function __construct(){

    }

    public function wcmlim_order_page_top_bar_button($class_name='alignleft'){ ?>
        <div class="<?= $class_name ?> actions custom">
            <button type="button" id="updateAllorder" name="custom_" style="height:32px;" class="button" value=""><?php
                echo __( 'Update Order for Managers', 'woocommerce' ); ?></button>
        </div>
    <?php }

    public function wcmlim_order_page_top_location_filter($domain, $filter_id, $current){
        echo '<select name="' . $filter_id . '">
        <option value="">' . __('All locations', $domain) . '</option>';

        $options = $this -> wcmlim_get_filter_shop_order_meta($domain);
        $taxonomies = array('locations');
        foreach ($options as $key => $label) {
          foreach ($taxonomies as $taxonomy_slug) {
            // Retrieve taxonomy data
            $taxonomy_name = 'locations';
            // Retrieve taxonomy terms
            $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
            foreach ($terms as $term) {
              $wordCount = explode(" ", $term->name);
              if (count($wordCount) > 1) {
                $_termname = str_replace(' ', '-', strtolower($term->name));
              } else {
                $_termname = $term->name;
              }
              if (in_array($term->name, $label)) {
                printf(
                  '<option value="%s" %s>%s</option>',
                  $_termname,
                  $_termname === $current ? 'selected="selected"' : '',
                  $term->name
                );
              }
            }
          }
        }
        echo '</select>';
    }


        // Custom function where metakeys / labels pairs are defined
        public function wcmlim_get_filter_shop_order_meta($domain = 'woocommerce')
        {
          $order_filter = array();
          // Add below the metakey / label pairs to filter orders
          $taxonomies = array('locations');
          foreach ($taxonomies as $taxonomy_slug) {
            // Retrieve taxonomy data
            $taxonomy_name = 'locations';
            // Retrieve taxonomy terms
            $terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
    
            foreach ($terms as $term) {
              $wordCount = explode(" ", $term->name);
              if (count($wordCount) > 1) {
                $_termname = str_replace(' ', '-', strtolower($term->name));
              } else {
                $_termname = $term->name;
              }
              $order_filter[] = array(
                $_termname => __($term->name, $domain),
              );
            }
          }
          return $order_filter;
        }
}