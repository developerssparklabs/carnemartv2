<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://www.techspawn.com
 * @since      1.0.0
 *
 * @package    Wcmlim
 * @subpackage Wcmlim/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Wcmlim
 * @subpackage Wcmlim/includes
 * @author     techspawn1 <contact@techspawn.com>
 */
class Wcmlim_Deactivator
{

  /**
   * Short Description. (use period)
   *
   * Long Description.
   *
   * @since    1.0.0
   */
  public static function deactivate()
  {
    remove_role('location_regional_manager');
    remove_role('location_shop_manager');

  }
}
