<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wcmlim
 * @subpackage Wcmlim/includes
 * @author     techspawn1 <contact@techspawn.com>
 */
class Wcmlim_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		$locationDistance = get_option('wcmlim_show_location_distance');
       if($locationDistance=="")
       {
       update_option('wcmlim_show_location_distance','miles',true);
       }
		global $wp_roles;

		if (!isset($wp_roles)) {
			$wp_roles = new WP_Roles(); // @codingStandardsIgnoreLine
		}
		// Location Shop Regional manager role.
		add_role(
			'location_regional_manager',
			'Location Regional Manager',
			array(
				'level_9'                => true,
				'level_8'                => true,
				'level_7'                => true,
				'level_6'                => true,
				'level_5'                => true,
				'level_4'                => true,
				'level_3'                => true,
				'level_2'                => true,
				'level_1'                => true,
				'level_0'                => true,
				'read'                   => true,
				'read_private_pages'     => true,
				'read_private_posts'     => true,
				'edit_posts'             => true,
				'edit_pages'             => true,
				'edit_published_posts'   => true,
				'edit_published_pages'   => true,
				'edit_private_pages'     => true,
				'edit_private_posts'     => true,
				'edit_others_posts'      => true,
				'edit_others_pages'      => true,
				'publish_posts'          => true,
				'publish_pages'          => true,
				'delete_posts'           => true,
				'delete_pages'           => true,
				'delete_private_pages'   => true,
				'delete_private_posts'   => true,
				'delete_published_pages' => true,
				'delete_published_posts' => true,
				'delete_others_posts'    => true,
				'delete_others_pages'    => true,
				'manage_categories'      => true,
				'manage_links'           => true,
				'moderate_comments'      => true,
				'upload_files'           => true,
				'export'                 => true,
				'import'                 => true,
				'list_users'             => true,
				'edit_theme_options'     => true,
			)
		);
		// Location Shop manager role.
		add_role(
			'location_shop_manager',
			'Location Shop manager',
			array(
				'level_9'                => true,
				'level_8'                => true,
				'level_7'                => true,
				'level_6'                => true,
				'level_5'                => true,
				'level_4'                => true,
				'level_3'                => true,
				'level_2'                => true,
				'level_1'                => true,
				'level_0'                => true,
				'read'                   => true,
				'read_private_pages'     => true,
				'read_private_posts'     => true,
				'edit_posts'             => true,
				'edit_pages'             => true,
				'edit_published_posts'   => true,
				'edit_published_pages'   => true,
				'edit_private_pages'     => true,
				'edit_private_posts'     => true,
				'edit_others_posts'      => true,
				'edit_others_pages'      => true,
				'publish_posts'          => true,
				'publish_pages'          => true,
				'delete_posts'           => true,
				'delete_pages'           => true,
				'delete_private_pages'   => true,
				'delete_private_posts'   => true,
				'delete_published_pages' => true,
				'delete_published_posts' => true,
				'delete_others_posts'    => true,
				'delete_others_pages'    => true,
				'manage_categories'      => true,
				'manage_links'           => true,
				'moderate_comments'      => true,
				'upload_files'           => true,
				'export'                 => true,
				'import'                 => true,
				'list_users'             => true,
				'edit_theme_options'     => true,
			)
		);

		$capabilities = self::get_core_capabilities();

		foreach ($capabilities as $cap_group) {
			foreach ($cap_group as $cap) {
				$wp_roles->add_cap('location_shop_manager', $cap);
				$wp_roles->add_cap('location_regional_manager', $cap);			
			}
		}

		// Added Options on plugin activation


global $wpdb;
$options_table = $wpdb->prefix . 'options';                 
$ProductCentralControlOptions = $wpdb->get_results($wpdb->prepare("SELECT * FROM `" . $options_table . "` WHERE `option_name`= '%s'", 'ProductCentralControlOptions'));
$PCFO = maybe_unserialize($ProductCentralControlOptions[0]->option_value);
 if(!empty($PCFO)){

	$Values[] = array("id"=>"Show","thumbnail"=>"Show","name"=>"Show" ,"sku"=>"Show" ,"regular_price"=>"Show","sale_price"=>"Show" 
,"stock_quantity"=>"Show" ,"stock_status"=>"Show", "stock_at_location"=>"Show", "categories"=>"" , "tags"=>"" , "short_description"=>"" ,
"description"=>"", "type"=>"", "status"=>"", "parent"=>"", "catalog_visibility"=>"", "tax_status"=>"", "backorders"=>""
,"weight"=>"", "length"=>"", "width"=>"", "height"=>"" , "manage_stock"=>""  );

} else {
$Values[] = array("id"=>"Show","thumbnail"=>"Show","name"=>"Show" ,"sku"=>"Show" ,"regular_price"=>"Show","sale_price"=>"Show" 
,"stock_quantity"=>"Show" ,"stock_status"=>"Show", "stock_at_location"=>"Show", "categories"=>"" , "tags"=>"" , "short_description"=>"" ,
"description"=>"", "type"=>"", "status"=>"", "parent"=>"", "catalog_visibility"=>"", "tax_status"=>"", "backorders"=>""
,"weight"=>"", "length"=>"", "width"=>"", "height"=>"" , "manage_stock"=>""  );
update_option( 'ProductCentralControlOptions', $Values );
} 
	}

	private static function get_core_capabilities()
	{
		$capabilities = array();

		$capabilities['core'] = array(
			'manage_woocommerce',
			// 'view_woocommerce_reports',
		);

		$capability_types = array('product', 'shop_order', 'shop_coupon');

		foreach ($capability_types as $capability_type) {

			$capabilities[$capability_type] = array(
				// Post type.
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",

				// Terms.
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms",
			);
		}

		return $capabilities;
	}
}
