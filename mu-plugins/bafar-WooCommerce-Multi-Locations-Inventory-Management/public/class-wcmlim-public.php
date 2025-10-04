<?php

/**
 * The public-facing functionality of the plugin.
 *
 *
 * @link       http://www.techspawn.com
 * @since      1.0.0
 * @package    Wcmlim
 * @subpackage Wcmlim/public
 * @author     techspawn Solutions <contact@techspawn.com>
 */
class Wcmlim_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	public $max_value_inpl;
	public $max_in_value;

	public function __construct($plugin_name, $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_filter('init', [$this, 'wcmlim_shop_page_location_cookie'], 20);
	}

	public function wcmlim_add_type_attribute_scripts($tag, $handle, $src)
	{
		// if not your script, do nothing and return original $tag
		if ('wcmlimlocalization' !== $handle && 'wcmlim' !== $handle) {
			return $tag;
		}
		// change the script tag by adding type="module" and return it.
		$tag = '<script type="module" src="' . esc_url($src) . '"></script>';
		return $tag;
	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		include_once plugin_dir_path(__FILE__) . 'controller/assets/style.php';
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		include_once plugin_dir_path(__FILE__) . 'controller/assets/script.php';
	}

	public function enqueue_scripts_clear_cart()
	{
		include_once plugin_dir_path(__FILE__) . 'controller/assets/clear-cart.php';
	}

	// Distance Matrix API to Display the Closest Location for the Product
	public function wcmlim_closest_location()
	{
		include plugin_dir_path(__FILE__) . 'controller/shop/wcmlim-closest-location.php';
	}

	public function wcmlim_closest_instock_location()
	{
		include plugin_dir_path(__FILE__) . 'controller/shop/wcmlim-closest-instock-location.php';
	}

	public function getLocationgroupID($distanceKey)
	{
		return include plugin_dir_path(__FILE__) . 'controller/location/getLocationgroupID.php';
	}

	public function getLocationServiceRadius($distanceKey)
	{
		return include plugin_dir_path(__FILE__) . 'controller/location/getLocationServiceRadius.php';
	}

	public function getSecondNearestLocation($addresses, $dis_unit, $product_id)
	{
		return include plugin_dir_path(__FILE__) . 'controller/location/getSecondNearestLocation.php';
	}

	public function checkLocInstockOrNot($locIndex, $terms, $product_id)
	{
		include plugin_dir_path(__FILE__) . 'controller/location/checkLocInstockOrNot.php';
	}

	public function wcmlim_empty_cart_content()
	{
		include plugin_dir_path(__FILE__) . 'controller/cart/wcmlim-empty-cart-content.php';
	}

	public function action_woocommerce_cart_item_removed($cart_item_key, $instance)
	{
		global $woocommerce;
		$cart_count = $woocommerce->cart->get_cart_total();
		if ($cart_count != 0) {
			unset($_COOKIE['wcmlim_selected_location']);
		}
	}

	public function action_woocommerce_add_tax_each_location($cart)
	{
		include plugin_dir_path(__FILE__) . 'controller/tax/add-tax-each-location.php';
	}
	// woocommerce_order_review
	public function wcmlim_order_review()
	{
		include plugin_dir_path(__FILE__) . 'controller/checkout/wcmlim-order-review.php';
	}

	/**
	 * Dropdown code startingpassedSoldbtn
	 *
	 */
	public function wcmlim_display_location()
	{
		include plugin_dir_path(__FILE__) . 'controller/shop/wcmlim-display-location.php';
	}

	/** shortcode wcmlim_loc_storedropdown && for detail Page */
	public function woo_storelocator_dropdown()
	{
		include plugin_dir_path(__FILE__) . 'controller/shop/woo-storelocator-dropdown.php';
	}

	public function add_order_item_meta($item_id, $cart_item, $cart_item_key)
	{
		include plugin_dir_path(__FILE__) . 'controller/order/add-order-item-meta.php';
	}

	public function wcmlim_remove_item_meta_from_mail($formatted_meta, $item)
	{
		return include plugin_dir_path(__FILE__) . 'controller/mail/wcmlim-remove-item-meta-from-mail.php';
	}

	public function hidden_order_itemmeta($args)
	{
		return include plugin_dir_path(__FILE__) . 'controller/order/hidden-order-itemmeta.php';
	}

	// Adds the Selected Stock Location to the Product Cart
	public function wcmlim_add_location_item_data($cart_item_data, $product_id, $variation_id, $quantity)
	{
		return include plugin_dir_path(__FILE__) . 'controller/cart/wcmlim-add-location-item-data.php';
	}


	//Detail Address List view
	public function wcmlim_get_loactionaddress($termid)
	{
		$streetNumber = get_term_meta($termid, 'wcmlim_street_number', true);
		$route = get_term_meta($termid, 'wcmlim_route', true);
		$locality = get_term_meta($termid, 'wcmlim_locality', true);
		$state = get_term_meta($termid, 'wcmlim_administrative_area_level_1', true);
		$postal_code = get_term_meta($termid, 'wcmlim_postal_code', true);
		$country = get_term_meta($termid, 'wcmlim_country', true);
		return $streetNumber . " " . $route . " " . $locality . " " . $state . " " . $postal_code . " " . $country;
	}
	/**
	 * WC custom notice message for variation
	 */
	public function wcmlim_get_wc_script_data($params, $handle)
	{
		$no_matching_var = get_option('wcmlim_var_message2');
		$make_a_selection = get_option('wcmlim_var_message3');
		$var_unavailable = get_option('wcmlim_var_message4');
		if ($handle === 'wc-add-to-cart-variation') {
			$params['i18n_no_matching_variations_text'] = __($no_matching_var, 'wcmlim');
			$params['i18n_make_a_selection_text'] = __($make_a_selection, 'wcmlim');
			$params['i18n_unavailable_text'] = __($var_unavailable, 'wcmlim');
		}
		return $params;
	}

	/**
	 * Set the max attribute value for the quantity input field for Add to cart forms.
	 * This applies to Simple product Add To Cart forms, and ALL (simple and variable) products on the Cart page quantity field.
	 *
	 */
	public function wcmlim_max_qty_input_args($args, $product)
	{

		$stock = $product->get_stock_quantity();

		$max = isset($this->max_in_value) ? $this->max_in_value : $stock;

		$product_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
		if ($product->backorders_allowed()) {
			echo '<p id="backorder_status" style="display:none">backorders_allowed</p>';
		}
		if ($product->managing_stock() && !$product->backorders_allowed()) {
			// Limit our max by the available stock
			if (isset($this->max_value_inpl)) {
				$args['max_value'] = $this->max_value_inpl;

			} else {
				$args['max_value'] = isset($max['location_qty']) ? $max['location_qty'] : $stock;

			}

		}
		return $args;
	}

	//Displays the Selected Location below the Product name in Cart
	public function wcmlim_cart_item_name($name, $cart_item, $cart_item_key)
	{
		$name = include plugin_dir_path(__FILE__) . 'controller/cart/wcmlim-cart-item-name.php';
		return $name;
	}

	public function wcmlim_shop_page_location_cookie()
	{
		global $wpdb;
		$url = $_SERVER['REQUEST_URI'];
		$path = parse_url($url, PHP_URL_PATH);
		$segments = explode('/', rtrim($path, '/'));
		$loc_name = end($segments);

		$refresh_id = (isset($_COOKIE['wcmlim_selected_location'])) ? $_COOKIE['wcmlim_selected_location'] : '';
		$cookieindays = get_option('wcmlim_set_location_cookie_time');
		//restrict user for restricted location
		$current_user = wp_get_current_user();
		$current_user_id = get_current_user_id();
		$current_ui = isset($current_user_id) ? $current_user_id : "";
		$restricUsers = get_option('wcmlim_enable_userspecific_location');
		if ($restricUsers == "on" && is_user_logged_in()) {
			$user_selected_location = get_user_meta($current_ui, 'wcmlim_user_specific_location', true);
			setcookie("wcmlim_selected_location", $user_selected_location, time() + (86400 * $cookieindays), "/");
			if ($refresh_id != $user_selected_location)
				header("Refresh:0");
			$user_selected_location = "";
		}
		$restrictGuest = get_option('wcmlim_enable_restrict_guestuser_location');
		$selectedGuestLoc = get_option('wcmlim_restrict_guest_user_location');

		if ($restrictGuest == "on" && !is_user_logged_in()) {
			$locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
			foreach ($locations as $key => $term) {
				if ($term->term_id == $selectedGuestLoc) {
					if ($key != $refresh_id) {
						setcookie("wcmlim_selected_location", $key, time() + (86400 * $cookieindays), "/");
						header("Refresh:0");
					}
				}
			}
		}

		$taxonomies = get_taxonomies();
		foreach ($taxonomies as $tax_type_key => $taxonomy) {
			if ($term_object = get_term_by('slug', $loc_name, $taxonomy)) {
				break;
			}
		}
		if (isset($term_object->term_id)) {
			$l_term_id = $term_object->term_id;
			$locations = array();
			$wpdb->show_errors();
			$locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
			foreach ($locations as $key => $term) {
				if ($term->term_id == $l_term_id) {
					if ($key != $refresh_id) {
						if ($loc_name == 'shop') {
							header("Refresh:0");
						}
					}
					setcookie("wcmlim_selected_location", $key, time() + (86400 * $cookieindays), "/");
					$autodetect_by_maxmind = get_option('wcmlim_enable_autodetect_location_by_maxmind');
					if ($autodetect_by_maxmind != 'on') {
						setcookie("wcmlim_nearby_location", $key, time() + (86400 * $cookieindays), "/");
					}
				}
			}
		}
	}

	public function wcmlim_display_selected_location_dropdown()
	{
		$product_id = $_POST['product_id'];
		$location_cookie = $_POST['locationCookie'];
		$location_stock = get_post_meta($product_id, "wcmlim_stock_at_{$location_cookie}", true);
		echo $location_stock;
		die();

	}
	public function wcmlim_display_location_dropdown()
	{
		include plugin_dir_path(__FILE__) . 'controller/shop/wcmlim-display-location-dropdown.php';
	}

	// Comentamos toda la logica de la funcion, debido a que ya se implementa mejor el filtro en el tema carnemart
	public function wcmlim_add_custom_price($cart_object)
	{
		// error_log('add_custom_price');
		// // Avoiding hook repetition (when using price calculations for example)
		// if (did_action('woocommerce_before_calculate_totals') >= 2)
		// 	return;

		// foreach ($cart_object->get_cart() as $key => $item_values) {
		// 	##  Get cart item data
		// 	if (isset($item_values['select_location'])) {
		// 		$location_termId = $item_values['select_location']['location_termId'];
		// 		$location_regular_price = '';
		// 		$location_sale_price = '';
		// 		$prod_id = '';

		// 		//check if product is variable
		// 		$product_type = $item_values['data']->get_type();
		// 		if ($product_type == "variation") {
		// 			$prod_id = $item_values["variation_id"];
		// 			$manageStock = get_post_meta($item_values["variation_id"], '_manage_stock', true);
		// 		} else {
		// 			$prod_id = $item_values["product_id"];
		// 			$manageStock = get_post_meta($item_values["product_id"], '_manage_stock', true);
		// 		}
		// 		$location_regular_price = get_post_meta($prod_id, '_regular_price', true);
		// 		$location_sale_price = get_post_meta($prod_id, '_sale_price', true);
		// 		$location_regular_price = get_post_meta($prod_id, "wcmlim_regular_price_at_{$location_termId}", true);
		// 		$location_sale_price = get_post_meta($prod_id, "wcmlim_sale_price_at_{$location_termId}", true);
		// 		$price1 = ($location_sale_price != '') ? $location_sale_price : $location_regular_price;
		// 		$price1 = (empty($price1)) ? $item_values['data']->get_price() : $price1;
		// 		$item_values['select_location']['location_cart_price'] = $price1;
		// 		if ($manageStock == "no") {
		// 			$price = $item_values['data']->get_price();
		// 			return wc_price($price);
		// 		} else {
		// 			$original_price = isset($item_values['select_location']['location_org_price']) ? $item_values['select_location']['location_org_price'] : ""; // Product original price
		// 			if (!empty($original_price)) {
		// 				## Set the new item price in cart
		// 				$item_values['data']->set_price(($original_price));
		// 			} else {
		// 				$price = isset($item_values['select_location']['location_cart_price']) ? $item_values['select_location']['location_cart_price'] : "";
		// 				$newprice = html_entity_decode($price);
		// 				## Set the new item price in cart
		// 				$item_values['data']->set_price(($newprice));
		// 			}
		// 		}
		// 	}
		// }
	}

	public function wcmlim_get_all_locations()
	{
		$locations = include plugin_dir_path(__FILE__) . 'controller/location/wcmlim-get-all-locations.php';
		return $locations;
	}

	public function wcmlim_set_preferred_location()
	{
		if (isset($_POST)) {
			$prefLoc = $_POST['ploc'];
		}
		wp_die();
	}

	public function woo_switch_content()
	{
		include plugin_dir_path(__FILE__) . 'controller/shop/wcmlim-switch-content.php';
	}

	/***
	 * On load and change region update
	 */
	public function wcmlim_getdropdown_location()
	{
		include plugin_dir_path(__FILE__) . 'controller/location/wcmlim-getdropdown-location.php';
	}
	/**
	 * Location Group
	 */

	public function wcmlim_get_all_store()
	{
		$isStoreLocEx = get_option("wcmlim_exclude_locations_group_frontend");
		if (!empty($isStoreLocEx)) {
			$terms = get_terms(array('taxonomy' => 'location_group', 'hide_empty' => false, 'parent' => 0, 'exclude' => $isStoreLocEx));
		} else {
			$terms = get_terms(array('taxonomy' => 'location_group', 'hide_empty' => false, 'parent' => 0));
		}

		$result = [];
		$i = 0;
		foreach ($terms as $k => $term) {
			$term_meta = get_option("taxonomy_$term->term_id");

			$result[$i]['store_name'] = $term->name;
			$result[$i]['store_id'] = $term->term_id;
			$i++;
		}
		return $result;
		wp_die();
	}

	private static function get_selected_location($count_on_default = false)
	{

		if (!$count_on_default) {
			// only alternative locations will be counted
			$selected_location = (isset($_COOKIE['wcmlim_selected_location']) && $_COOKIE['wcmlim_selected_location'] != 'default') ? $_COOKIE['wcmlim_selected_location'] : -1;
		} else {
			// any location selected, even default will return true
			$selected_location = (isset($_COOKIE['wcmlim_selected_location'])) ? true : -1;
		}
		return $selected_location;
	}

	// process select location form submission here
	public function handle_switch_form_submit()
	{

		$selected_location = isset($_POST['wcmlim_change_lc_to']) ? $_POST['wcmlim_change_lc_to'] : "";

		if ($selected_location && $selected_location == 'default') {
			$this->set_location_cookie($selected_location); // set
		} else if (isset($selected_location) && $selected_location != '') {
			$this->set_location_cookie($selected_location); // set
		}
	}

	// set and unset location cookies
	public function set_location_cookie($selected_location = null)
	{
		include plugin_dir_path(__FILE__) . 'controller/location/wcmlim-set-location-cookie.php';
	}

	public function wcmlim_show_stock_shop()
	{
		global $product;
		$setLocation = isset($_COOKIE['wcmlim_selected_location']) ? $_COOKIE['wcmlim_selected_location'] : "";
		if ($product->get_type() == 'simple') {
			$manage_stock = get_post_meta($product->get_id(), '_manage_stock', true);
			if ($manage_stock == 'no') {
				return;
			}

			$exclExists = get_option("wcmlim_exclude_locations_from_frontend");
			if (!empty($exclExists)) {
				$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $exclExists));
			} else {
				$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
			}

			foreach ($terms as $k => $term) {
				$locationQty = get_post_meta($product->get_id(), "wcmlim_stock_at_{$term->term_id}", true);
				if ($setLocation == $k && !empty($locationQty)) {
					print_r($term->name . ': ' . $locationQty . '<br>');
				}
			}
		}
	}


	public function wcmlim_select_location_validation($passed)
	{
		return include plugin_dir_path(__FILE__) . 'controller/location/wcmlim-select-location-validation.php';

	}

	public function wcmlim_replacing_add_to_cart_button($button, $product)
	{
		return include plugin_dir_path(__FILE__) . 'controller/cart/wcmlim-add-to-cart-button.php';

	}

	public function wcmlim_ajax_validation_manage_stock()
	{
		include plugin_dir_path(__FILE__) . 'controller/cart/wcmlim-ajax-validation.php';
	}

	public function wcmlim_ajax_add_to_cart()
	{
		$default_location = get_option('wcmlim_enable_default_location');
		if ($default_location == 'on') {
			return include plugin_dir_path(__FILE__) . 'controller/cart/wcmlim-default-location-ajax-add-to-cart.php';
		} else {
			//se usa este 4 de febrero 2025 s8k sergio keikos
			return include plugin_dir_path(__FILE__) . 'controller/cart/wcmlim-ajax-add-to-cart.php';
		}
		echo $return;
	}

	public function wcmlim_woocommerce_price_class($string)
	{
		// Add new class
		$string = 'price wcmlim_product_price';
		return $string;
	}

	public function wcmlim_cart_item_price($price, $cart_item, $cart_item_key)
	{
		// Check if 'select_location' key exists in $cart_item
		if (isset($cart_item['select_location'])) {
			// Get product type 
			$location_termId = $cart_item['select_location']['location_termId'];
			$location_regular_price = '';
			$location_sale_price = '';
			$prod_id = '';
			$product_type = $cart_item['data']->get_type();
			if ($product_type == "variation") {
				$prod_id = $cart_item["variation_id"];
				$manageStock = get_post_meta($cart_item["variation_id"], '_manage_stock', true);
			} else {
				$prod_id = $cart_item["product_id"];
				$manageStock = get_post_meta($cart_item["product_id"], '_manage_stock', true);
			}
			$location_regular_price = get_post_meta($prod_id, '_regular_price', true);
			$location_sale_price = get_post_meta($prod_id, '_sale_price', true);
			$location_regular_price = get_post_meta($prod_id, "wcmlim_regular_price_at_{$location_termId}", true);
			$location_sale_price = get_post_meta($prod_id, "wcmlim_sale_price_at_{$location_termId}", true);

			if ($manageStock == "no") {
				return (trim(strip_tags($price)));
			}

			$original_price = floatval($cart_item['data']->get_price()); // Product original price

			$price1 = ($location_sale_price != '') ? $location_sale_price : $location_regular_price;

			// CALCULATION FOR EACH ITEM:
			if (!$price1) {
				$new_price = $original_price;
			} else {
				$new_price = $price1;
			}
			$cart_item['select_location']['location_cart_price'] = $new_price;
			$cart_item['select_location']['location_org_price'] = $new_price;

			return $new_price;
		}

		return $price; // Return original price if 'select_location' key is not set
	}

	public function wcmlim_location_stock_allowed_add_to_cart($passed, $product_id, $quantity)
	{
		return include plugin_dir_path(__FILE__) . 'controller/cart/wcmlim-stock-allowed.php';

	}

	//wcmlim_proceed_to_checkout
	public function wcmlim_proceed_to_checkout()
	{
		include plugin_dir_path(__FILE__) . 'controller/checkout/wcmlim-proceed-to-checkout.php';
	}

	public function wc_qty_update_cart_validation($passed, $cart_item_key, $values, $quantity)
	{
		// Fetch location with quantity
		$cart_item = WC()->cart->get_cart_item($cart_item_key);

		// Check if backorders are allowed for this product
		$product_id = $values['data']->get_id();
		$stz = 'wcmlim_allow_backorder_at_' . $cart_item['select_location']['location_termId'];
		$is_backorder_enabled = get_post_meta($product_id, $stz, true);

		// If backorders are allowed, set product for this location to backorder
		if ($is_backorder_enabled == 'yes') {
			$location_id = $cart_item['select_location']['location_termId'];
			$product = wc_get_product($product_id);
			$product->set_stock_quantity(0);
			$product->set_backorders('yes');
			$product->save();
			update_post_meta($product_id, '_backorders', 'yes');
			return $passed;
		} else {
			if (isset($cart_item['select_location']['location_termId']) && $quantity > $cart_item['select_location']['location_qty']) {
				// Calculate available quantity
				$available_qty = $cart_item['select_location']['location_qty'];

				// Display error message
				$error_message = sprintf(
					__('Sorry, we do not have enough "%s" in stock to fulfill your order (%d available) for location %s. We apologize for any inconvenience caused.', 'wcmlim'),
					$values['data']->get_name(), // Item name
					$available_qty,
					$cart_item['select_location']['location_name']
				);
				wc_clear_notices();
				wc_add_notice($error_message, 'error');
				$passed = false; // Set $passed to false to prevent updating the cart
			}

			return $passed;
		}
	}


	/**
	 * Add rewrite rule and tag to WP
	 */
	public function wcmlim_url_init()
	{
		// rewrite rule tells wordpress to expect the given url pattern
		add_rewrite_rule('^mlfilter/(.*)/?', 'index.php?locations=$matches[1]', 'top');
		// rewrite tag adds the matches found in the pattern to the global $wp_query
		add_rewrite_tag('%mlfilter%', '(.*)');
	}

	/**
	 * Modify the query based on our rewrite tag
	 */
	public function wcmlim_url_redirect()
	{

		// get the value of our rewrite tag
		$longerer = get_query_var('mlfilter');

		// look for the existence of our rewrite tag
		if (get_query_var('mlfilter')) {
			// get the post ID from the longerer string

			$location_Name = $longerer;

			// attempt to find the permalink associated with this post ID
			$permalink = get_permalink($location_Name);
			// if valid, send to permalink
			if ($location_Name && $permalink) {
				wp_redirect($permalink);
			}
			// otherwise, send to homepage
			else {
				wp_redirect(home_url());
			}
			exit;
		}
		$isClearCart = get_option('wcmlim_clear_cart');
		$location = null;
		$manage_stock = "";
		$two_diff_loc = get_option('wcmlim_two_diff_loc_addtocart');
		// 		if ( WC()->cart ) {
		foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
			$item_location_id = !empty($cart_item['select_location']['location_termId']) ? $cart_item['select_location']['location_termId'] : "";
			$item_names = $cart_item['data']->get_name();
			if ($isClearCart == 'on') {
				if (empty($location)) {
					$location = $item_location_id;
					continue;
				}
				if ($manage_stock == "yes") {
					if ($location != $item_location_id) {

						wc_add_notice($two_diff_loc, 'error');
						WC()->cart->remove_cart_item($cart_item_key);
						remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
						break;
					}
				}
			}
		}
		// 		}
	}

	public function wcmlim_sortshop_product()
	{
		do_action("woocommerce_product_query");
		wp_die();
	}

	public function wcmlim_calculate_distance_search()
	{
		include plugin_dir_path(__FILE__) . 'controller/location/wcmlim-calculate-distance.php';
	}

	//backorder for each location
	public function wcmlim_backorder4el()
	{
		include plugin_dir_path(__FILE__) . 'controller/location/backorder-for-each-location.php';
	}

	//toggle for each location
	public function wcmlim_toggle_for_each_location()
	{
		echo 'hello';
	}

	//list advanced view location details results
	public function wcmlim_prepare_advanced_view_information()
	{
		include plugin_dir_path(__FILE__) . 'controller/shop/wcmlim-advance-list-view.php';
	}

	public function wcmlim_calculate_shipping($location_id)
	{
		return include plugin_dir_path(__FILE__) . 'controller/shipping/wcmlim-calculate-shipping.php';
	}

	public function getAddress($address)
	{
		return include plugin_dir_path(__FILE__) . 'controller/location/wcmlim-getaddress.php';
	}


	// wcmlim_shipstation_custom_field_2
	public function wcmlim_shipstation_custom_field_2()
	{
		return '_location';
	}

	public function wcmlim_maybe_reduce_stock_levels($order_id)
	{

		// var_dump("you are here man");die;
		$order = wc_get_order($order_id);

		if (!$order) {
			return;
		}

		$stock_reduced = $order->get_data_store()->get_stock_reduced($order_id);
		$trigger_reduce = apply_filters('woocommerce_payment_complete_reduce_order_stock', !$stock_reduced, $order_id);

		// Only continue if we're reducing stock.
		if (!$trigger_reduce) {
			return;
		}

		$this->wc_reduce_stock_levels($order);
		// file_put_contents('wc_reduce_stock_levels.txt', $order_id);
		// Ensure stock is marked as "reduced" in case payment complete or other stock actions are called.
		// $order->get_data_store()->set_stock_reduced($order_id, true);
	}

	public function wc_reduce_stock_levels($order_id)
	{

		if (is_a($order_id, 'WC_Order')) {
			$order = $order_id;
			$order_id = $order->get_id();
		} else {
			$order = wc_get_order($order_id);
		}

		// We need an order, and a store with stock management to continue.
		if (!$order || 'yes' !== get_option('woocommerce_manage_stock') || !apply_filters('woocommerce_can_reduce_order_stock', true, $order)) {
			// return;
		}

		$changes = array();
		$item_mail = array();
		// Loop over all items.

		// Given URL
		$url = $_SERVER['REQUEST_URI'];

		// Search substring 
		$key = 'wp-json/wc-pos';

		// *WooCommerce Point Of Sale by Actuality Extensions compatibility
		$wc_pos_compatiblity1 = get_option('wcmlim_wc_pos_compatiblity');
		if (($wc_pos_compatiblity1 == "on") && (in_array('woocommerce-point-of-sale/woocommerce-point-of-sale.php', apply_filters('active_plugins', get_option('active_plugins')))) && (strpos($url, $key) != false)) {

			$blogURL = get_bloginfo('url');
			$referer = $_SERVER['HTTP_REFERER'];
			$referer = str_replace($blogURL, "", $referer);
			$refe = explode("/", $referer);
			foreach ($refe as $r => $s) {
				if ($s == "point-of-sale") {
					$outletSlug = $refe[$r + 1];
					$registerSlug = $refe[$r + 2];
				}
			}

			$rargs = array(
				'post_type' => 'pos_register',
				'name' => $registerSlug,
				'post_status' => 'publish',
				'fields' => 'ids',
			);

			$registerPOST = get_posts($rargs);
			$regPostID = $registerPOST[0];
			$assignedOutletID = get_post_meta($regPostID, 'outlet', true);

			global $wpdb;
			$termMetaTable = $wpdb->prefix . 'termmeta';
			$getTermID = $wpdb->get_results("SELECT term_id FROM $termMetaTable WHERE meta_key = 'wcmlim_wcpos_compatiblity' AND meta_value = $assignedOutletID;");

			$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));


			$exclExists = get_option("wcmlim_exclude_locations_from_frontend");
			if (!empty($exclExists)) {
				$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $exclExists));
			} else {
				$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
			}

			foreach ($order->get_items() as $item) {

				if (!$item->is_type('line_item')) {
					continue;
				}

				// Only reduce stock once for each item.
				$product = $item->get_product();
				$product_id = $product->get_id();
				$item_stock_reduced = $item->get_quantity();

				// *WooCommerce Point Of Sale by Actuality Extensions compatibility

				foreach ($getTermID as $key => $value) {

					$foundTermID = $value->term_id;

					if (!$product || !$product->managing_stock()) {
						continue;
					}

					$qty = apply_filters('woocommerce_order_item_quantity', $item->get_quantity(), $order, $item);
					$item_name = $product->get_formatted_name();

					$order = wc_get_order($order_id);

					$item->add_meta_data('Location', $value->name);
					$item->add_meta_data('_selectedLocationKey', $key);
					$item->add_meta_data('_selectedLocTermId', $value->term_id);
					$item->add_meta_data('_wcmlim_reduced_stock', $qty, true);
					$item->save();

					$this->wcpos_reduce_product_stock($product_id, $qty, $foundTermID, $order);
					$order->get_data_store()->set_stock_reduced($order_id, true);

					$product->update_meta_data('wcmlim_sync_updated', true);
					$product->save();
					update_post_meta($order_id, '_order_stock_reduced', 'yes');

				}

			}

		} else {
			foreach ($order->get_items() as $item) {

				if (!$item->is_type('line_item')) {
					continue;
				}

				// Only reduce stock once for each item.
				$product = $item->get_product();
				$item_stock_reduced = $item->get_meta('_reduced_stock', true);


				$item_selectedLocation_key = $item->get_meta('_selectedLocationKey', true);
				$itemSelLocTermId = $item->get_meta('_selectedLocTermId', true);
				$itemSelLocName = $item->get_meta('Location', true);
				$dataLocation = $item->get_meta('Location', true);
				if ($itemSelLocTermId == '') {
					return;
				}

				$selLocQty = get_post_meta($product->get_id(), "wcmlim_stock_at_{$itemSelLocTermId}", true);

				if ($item_stock_reduced || !$product || !$product->managing_stock()) {
					continue;
				}

				$qty = apply_filters('woocommerce_order_item_quantity', $item->get_quantity(), $order, $item);


				// *WooCommerce Point Of Sale by Actuality Extensions compatibility
				$wc_pos_compatiblity1 = get_option('wcmlim_wc_pos_compatiblity');

				if (($wc_pos_compatiblity1 == "on") && (in_array('woocommerce-point-of-sale/woocommerce-point-of-sale.php', apply_filters('active_plugins', get_option('active_plugins'))))) {
					//now we have to get the outlet id and update the stock if wc pos is active

					$outlet_id = get_term_meta($itemSelLocTermId, 'wcmlim_wcpos_compatiblity', true);
					$new_outlet_stock = wc_pos_update_product_outlet_stock($product, array($outlet_id => $qty), 'decrease');

				}



				$item_name = $product->get_formatted_name();
				$new_stock = $this->wc_update_product_stock($product, $qty, 'decrease', false, $item_selectedLocation_key);

				if (is_wp_error($new_stock)) {
					/* translators: %s item name. */
					$order->add_order_note(sprintf(_e('Unable to reduce stock for item %s.', 'woocommerce'), $item_name));
					continue;
				}

				$item->add_meta_data('_reduced_stock', $qty, true);
				$item->save();

				$changes[] = array(
					'product' => $product,
					'from' => intval($new_stock) + intval($qty),
					'to' => $new_stock,
				);

				$locChanges[] = array(
					'product' => $product,
					'location' => $itemSelLocName,
					'from' => $selLocQty,
					'to' => intval($selLocQty) - intval($qty),
				);
				//send Mail
				if (!in_array($itemSelLocTermId, $item_mail)) {
					$item_mail[] = $itemSelLocTermId;
				}
			}
			$dataLocate = array();
			$wcmlim_assign_location_shop_manager = get_option('wcmlim_assign_location_shop_manager');
			if ($wcmlim_assign_location_shop_manager != 'on') {
				$item_mail = array();
			}
			foreach ($item_mail as $wcmlim_email_val) {

				$wcmlim_emailadd = get_term_meta($wcmlim_email_val, 'wcmlim_email', true);
				$shop_manager = get_term_meta($wcmlim_email_val, 'wcmlim_shop_manager', true);
				$term_object = get_term($wcmlim_email_val);
				$dataLo = $term_object->term_id;
				$dataLocate[] = $dataLo;
				if ($shop_manager) {
					$author_id = $shop_manager[0];
					$author_obj = get_user_by('id', $author_id);
					$author_email = $author_obj->user_email;
					if ($wcmlim_emailadd) {
						$wcmlimemail = $author_email . ", " . $wcmlim_emailadd;
					} else {
						$wcmlimemail = $author_email;
					}
				} else {
					$wcmlimemail = $wcmlim_emailadd;
				}
				/*Regional code */
				$regM = get_term_meta($wcmlim_email_val, "wcmlim_locator", true);
				$regM2 = get_term_meta($regM, "wcmlim_shop_regmanager", true);
				$wcmlim_regemail = get_term_meta($regM, 'wcmlim_email_regmanager', true);
				if ($regM2) {
					$authorid = $regM2[0];
					$authorobj = get_user_by('id', $authorid);
					$authoremail = $authorobj->user_email;
					if ($wcmlim_regemail) {
						$regwcmlimemail = $authoremail . ", " . $wcmlim_regemail;
					} else {
						$regwcmlimemail = $authoremail;
					}
				} else {
					$regwcmlimemail = $wcmlim_regemail;
				}

				if ($regwcmlimemail) {
					$wcmlim_email = $regwcmlimemail . ", " . $wcmlimemail;
				} else {
					$wcmlim_email = $wcmlimemail;
				}

				if (isset($wcmlim_email) && !empty($wcmlim_email)) {
					include(plugin_dir_path(dirname(__FILE__)) . 'public/partials/email-template.php');
				}
			}
			//update multiloc
			if (!empty($dataLocate)) {
				if (WC_HPOS_IS_ACTIVE) {
					$order->update_meta_data('_multilocation', $dataLocate);
				} else {
					update_post_meta($order_id, "_multilocation", $dataLocate);
				}
			}

			$wordCount = explode(" ", $dataLocation);
			if (count($wordCount) > 1) {
				$_location = str_replace(' ', '-', strtolower($dataLocation));
			} else {
				$_location = $dataLocation;
			}

			if (preg_match('/"/', $_location)) {
				$_location = str_replace('"', '', $_location);
			}

			if (!empty($_location)) {
				if (WC_HPOS_IS_ACTIVE) {
					$order->update_meta_data('_location', $_location);
				} else {
					update_post_meta($order_id, "_location", $_location);
				}
			}
			$this->wc_trigger_stock_change_notifications($order, $changes, $locChanges);

			do_action('woocommerce_reduce_order_stock', $order);
		}
		$order->save();
	}

	public function wcpos_reduce_product_stock($product_id, $qty, $location_id, $order)
	{

		$product_current_qty_at = get_post_meta($product_id, "wcmlim_stock_at_{$location_id}", true);
		$postmeta_backorders_product = get_post_meta($product_id, '_backorders', true);

		if ((($product_current_qty_at <= 0) || ($product_current_qty_at >= 0) || ($product_current_qty_at = '')) && (($postmeta_backorders_product == "yes") || ($postmeta_backorders_product == "notify"))) {

			$product_updated_qty = intval($product_current_qty_at) - intval($qty);
			$term_name = get_term($location_id)->name;

			$product = wc_get_product($product_id);

			$loc_order_notes[] = "{ Stock levels reduced: {$product->get_formatted_name()}  from Location: {$term_name} {$product_current_qty_at} &rarr; {$product_updated_qty} }";
			$order->add_order_note(implode(', ', $loc_order_notes));

			update_post_meta($product_id, "wcmlim_stock_at_{$location_id}", $product_updated_qty);

			return 1;
		} else {
			if ($qty > $product_current_qty_at) {
				$product_updated_qty = 0;

			} else {
				$product_updated_qty = intval($product_current_qty_at) - intval($qty);
			}
			$term_name = get_term($location_id)->name;

			$product = wc_get_product($product_id);

			$loc_order_notes[] = "{ Stock levels reduced: {$product->get_formatted_name()}  from Location: {$term_name} {$product_current_qty_at} &rarr; {$product_updated_qty} }";
			$order->add_order_note(implode(', ', $loc_order_notes));

			update_post_meta($product_id, "wcmlim_stock_at_{$location_id}", $product_updated_qty);

			return 1;
		}
	}

	public function wc_update_product_stock($product, $stock_quantity = null, $operation = 'set', $updating = false, $item_selectedLocation_key = null)
	{

		if (!is_a($product, 'WC_Product')) {
			$product = wc_get_product($product);
		}

		if (!$product) {
			return false;
		}

		if (!is_null($stock_quantity) && $product->managing_stock()) {
			// Some products (variations) can have their stock managed by their parent. Get the correct object to be updated here.
			$product_id_with_stock = $product->get_stock_managed_by_id();
			$product_with_stock = $product_id_with_stock !== $product->get_id() ? wc_get_product($product_id_with_stock) : $product;

			$data_store = WC_Data_Store::load('product');

			$product_id = $product_with_stock->get_id();

			$exclExists = get_option("wcmlim_exclude_locations_from_frontend");
			if (!empty($exclExists)) {
				$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0, 'exclude' => $exclExists));
			} else {
				$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
			}

			// Fire actions to let 3rd parties know the stock is about to be changed.
			if ($product_with_stock->is_type('variation')) {
				foreach ($terms as $k => $term) {
					if ($k == $item_selectedLocation_key) {
						$variationParentTerms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => $term->term_id));
						if (!empty($variationParentTerms)) {
							foreach ($variationParentTerms as $vParentTerm) {
								$stockInpVariation[$vParentTerm->term_id] = get_post_meta($product_id, "wcmlim_stock_at_{$vParentTerm->term_id}", true);
							}
							$varParentValue = max($stockInpVariation);
							$varParentKey = array_search($varParentValue, $stockInpVariation);
							if ($varParentKey) {
								$maxStockAtVarSub = get_post_meta($product_id, "wcmlim_stock_at_{$varParentKey}", true);
								if ($operation == "decrease") {
									$varSubStock = ((int) $maxStockAtVarSub - (int) $stock_quantity);
								} else {
									$varSubStock = ((int) $maxStockAtVarSub + (int) $stock_quantity);
								}
								if (class_exists('SitePress')) {
									global $sitepress;
									$trid = $sitepress->get_element_trid($product_id, 'post_product');
									$translations = $sitepress->get_element_translations($trid, 'product');
									foreach ($translations as $lang => $translation) {
										if ($translation->element_id != $product_id) {
											update_post_meta($translation->element_id, "wcmlim_stock_at_{$varParentKey}", $varSubStock);
										}
									}
									if (!$updating) {
										$product_with_stock->save();
									}
								}

								update_post_meta($product_id, "wcmlim_stock_at_{$varParentKey}", $varSubStock);
								//OpenPos - Outlet stock updated
								$wcmlim_pos_compatiblity = get_option('wcmlim_pos_compatiblity');
								if ($wcmlim_pos_compatiblity == "on" && in_array('woocommerce-openpos/woocommerce-openpos.php', apply_filters('active_plugins', get_option('active_plugins')))) {
									$wcmlim_pos_id = get_term_meta($term->term_id, 'wcmlim_pos_compatiblity', true);
									update_post_meta($product_id, "_op_qty_warehouse_{$wcmlim_pos_id}", $varSubStock);
								}
							}
						}
						$stock_in_location_variation = get_post_meta($product_id, "wcmlim_stock_at_{$term->term_id}", true);
						if ($operation == "decrease") {
							$stock = ((int) $stock_in_location_variation - (int) $stock_quantity);
						} else {
							$stock = ((int) $stock_in_location_variation + (int) $stock_quantity);
						}
						if (class_exists('SitePress')) {
							global $sitepress;
							$trid = $sitepress->get_element_trid($product_id, 'post_product');
							$translations = $sitepress->get_element_translations($trid, 'product');
							foreach ($translations as $lang => $translation) {
								if ($translation->element_id != $product_id) {
									update_post_meta($translation->element_id, "wcmlim_stock_at_{$term->term_id}", $stock);

								}
							}
							if (!$updating) {
								$product_with_stock->save();
							}
						}
						update_post_meta($product_id, "wcmlim_stock_at_{$term->term_id}", $stock);
						//OpenPos - Outlet stock updated
						$wcmlim_pos_compatiblity = get_option('wcmlim_pos_compatiblity');
						if ($wcmlim_pos_compatiblity == "on" && in_array('woocommerce-openpos/woocommerce-openpos.php', apply_filters('active_plugins', get_option('active_plugins')))) {
							$wcmlim_pos_id = get_term_meta($term->term_id, 'wcmlim_pos_compatiblity', true);
							update_post_meta($product_id, "_op_qty_warehouse_{$wcmlim_pos_id}", $stock);
						}
					}

				}
				$locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
				$stock = '';
				foreach ($locations as $key => $term) {
					$stock = intval(get_post_meta($product_id, "wcmlim_stock_at_{$term->term_id}", true));
					if ($stock == '' || $stock == 0) {
						$removetermName[] = $term->slug;
					} else {
						$termName[] = $term->slug;
					}
				}
				wp_set_object_terms($product_id, $termName, 'locations');
				wp_remove_object_terms($product_id, $removetermName, 'locations');
				do_action('woocommerce_variation_before_set_stock', $product_with_stock);
			} else {

				foreach ($terms as $k => $term) {
					if ($k == $item_selectedLocation_key) {
						$parentTerms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => $term->term_id));
						if (!empty($parentTerms)) {
							foreach ($parentTerms as $parentTerm) {
								$stockInParentLocation[$parentTerm->term_id] = get_post_meta($product_id, "wcmlim_stock_at_{$parentTerm->term_id}", true);
							}
							$parentValue = max($stockInParentLocation);
							$parentKey = array_search($parentValue, $stockInParentLocation);
							if ($parentKey) {
								$maxStockAtSub = get_post_meta($product_id, "wcmlim_stock_at_{$parentKey}", true);
								if ($operation == "decrease") {
									$subStock = ((int) $maxStockAtSub - (int) $stock_quantity);
								} else {
									$subStock = ((int) $maxStockAtSub + (int) $stock_quantity);
								}
								if (class_exists('SitePress')) {
									global $sitepress;
									$trid = $sitepress->get_element_trid($product_id, 'post_product');
									$translations = $sitepress->get_element_translations($trid, 'product');
									foreach ($translations as $lang => $translation) {
										if ($translation->element_id != $product_id) {
											update_post_meta($translation->element_id, "wcmlim_stock_at_{$parentKey}", $subStock);
										}
									}
									if (!$updating) {
										$product_with_stock->save();
									}
								}
								update_post_meta($product_id, "wcmlim_stock_at_{$parentKey}", $subStock);
							}
						}
						$stock_in_location = get_post_meta($product_id, "wcmlim_stock_at_{$term->term_id}", true);
						if ($operation == "decrease") {
							$stock = ((int) $stock_in_location - (int) $stock_quantity);
						} else {
							$stock = ((int) $stock_in_location + (int) $stock_quantity);
						}
						if (class_exists('SitePress')) {
							global $sitepress;
							$trid = $sitepress->get_element_trid($product_id, 'post_product');
							$translations = $sitepress->get_element_translations($trid, 'product');
							foreach ($translations as $lang => $translation) {
								if ($translation->element_id != $product_id) {
									update_post_meta($translation->element_id, "wcmlim_stock_at_{$term->term_id}", $stock);
								}
							}
							if ($stock == '' || $stock == 0) {
								$removetermName[] = $term->slug;
							}
							wp_remove_object_terms($product_id, $removetermName, 'locations');
							if (!$updating) {
								$product_with_stock->save();
							}
						}

						update_post_meta($product_id, "wcmlim_stock_at_{$term->term_id}", $stock);
						//OpenPos - Outlet stock updated
						$wcmlim_pos_compatiblity = get_option('wcmlim_pos_compatiblity');

						if ($wcmlim_pos_compatiblity == "on" && in_array('woocommerce-openpos/woocommerce-openpos.php', apply_filters('active_plugins', get_option('active_plugins')))) {
							$wcmlim_pos_id = get_term_meta($term->term_id, 'wcmlim_pos_compatiblity', true);
							update_post_meta($product_id, "_op_qty_warehouse_{$wcmlim_pos_id}", $stock);
						}
					}
				}

				$locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
				$stock = '';
				foreach ($locations as $key => $term) {
					$stock = intval(get_post_meta($product_id, "wcmlim_stock_at_{$term->term_id}", true));
					if ($stock == '' || $stock == 0) {
						$removetermName[] = $term->slug;
					} else {
						$termName[] = $term->slug;
					}
				}
				wp_set_object_terms($product_id, $termName, 'locations');
				wp_remove_object_terms($product_id, $removetermName, 'locations');
				do_action('woocommerce_product_before_set_stock', $product_with_stock);
			}

			// Update the database.
			$new_stock = $data_store->update_product_stock($product_id_with_stock, $stock_quantity, $operation);

			// Update the product object.
			$data_store->read_stock_quantity($product_with_stock, $new_stock);

			// If this is not being called during an update routine, save the product so stock status etc is in sync, and caches are cleared.
			if (!$updating) {
				$product_with_stock->save();
			}

			// Fire actions to let 3rd parties know the stock changed.
			if ($product_with_stock->is_type('variation')) {
				do_action('woocommerce_variation_set_stock', $product_with_stock);
			} else {
				do_action('woocommerce_product_set_stock', $product_with_stock);
			}

			return $product_with_stock->get_stock_quantity();
		}
		return $product->get_stock_quantity();
	}

	public function wc_trigger_stock_change_notifications($order, $changes, $locChanges)
	{
		if (empty($changes)) {
			return;
		}

		$order_notes = array();
		$no_stock_amount = absint(get_option('woocommerce_notify_no_stock_amount', 0));

		foreach ($changes as $change) {
			$order_notes[] = $change['product']->get_formatted_name() . ' ' . $change['from'] . '&rarr;' . $change['to'];
			$low_stock_amount = absint($this->wc_get_low_stock_amount(wc_get_product($change['product']->get_id())));
			if ($change['to'] <= $no_stock_amount) {
				do_action('woocommerce_no_stock', wc_get_product($change['product']->get_id()));
			} elseif ($change['to'] <= $low_stock_amount) {
				do_action('woocommerce_low_stock', wc_get_product($change['product']->get_id()));
			}

			if ($change['to'] < 0) {
				do_action(
					'woocommerce_product_on_backorder',
					array(
						'product' => wc_get_product($change['product']->get_id()),
						'order_id' => $order->get_id(),
						'quantity' => abs($change['from'] - $change['to']),
					)
				);
			}
		}

		$order->add_order_note(implode(', ', $order_notes));

		if (empty($locChanges)) {
			return;
		}

		foreach ($locChanges as $locChange) {
			$loc_order_notes[] = "{ Stock levels reduced: {$locChange['product']->get_formatted_name()}  from Location: {$locChange['location']} {$locChange['from']} &rarr; {$locChange['to']} }";

			$low_stock_amount = absint($this->wc_get_low_stock_amount(wc_get_product($locChange['product']->get_id())));
			if ($locChange['to'] <= $no_stock_amount) {
				do_action('woocommerce_no_stock', wc_get_product($locChange['product']->get_id()));
			} elseif ($locChange['to'] <= $low_stock_amount) {
				do_action('woocommerce_low_stock', wc_get_product($locChange['product']->get_id()));
			}

			if ($locChange['to'] < 0) {
				do_action(
					'woocommerce_product_on_backorder',
					array(
						'product' => wc_get_product($locChange['product']->get_id()),
						'order_id' => $order->get_id(),
						'quantity' => intval($locChange['from']) - intval($locChange['to']),
					)
				);
			}
		}

		$order->add_order_note(implode(', ', $loc_order_notes));
	}

	public function wc_get_low_stock_amount(WC_Product $product)
	{
		if ($product->is_type('variation')) {
			$product = wc_get_product($product->get_parent_id());
		}
		$low_stock_amount = $product->get_low_stock_amount();
		if ('' === $low_stock_amount) {
			$low_stock_amount = get_option('woocommerce_notify_low_stock_amount', 2);
		}

		return $low_stock_amount;
	}

	public function wcmlim_maybe_increase_stock_levels($order_id)
	{
		//Check order as wc_order or it a ID
		if (is_a($order_id, 'WC_Order')) {
			$order = $order_id;
			$order_id = $order->get_id();
		} else {
			$order = wc_get_order($order_id);
		}

		if (!$order) {
			return;
		}

		$stock_reduced = $order->get_data_store()->get_stock_reduced($order_id);
		$trigger_increase = (bool) $stock_reduced;

		// Only continue if we're increasing stock.
		if (!$trigger_increase) {
			return;
		}

		$this->wc_increase_stock_levels($order);

		// Ensure stock is not marked as "reduced" anymore.
		$order->get_data_store()->set_stock_reduced($order_id, false);
	}

	public function wc_increase_stock_levels($order_id)
	{
		if (is_a($order_id, 'WC_Order')) {
			$order = $order_id;
			$order_id = $order->get_id();
		} else {
			$order = wc_get_order($order_id);
		}

		// We need an order, and a store with stock management to continue.
		if (!$order || 'yes' !== get_option('woocommerce_manage_stock') || !apply_filters('woocommerce_can_restore_order_stock', true, $order)) {

			$wc_pos_compatiblity1 = get_option('wcmlim_wc_pos_compatiblity');

			if (($wc_pos_compatiblity1 != "on") && (!in_array('woocommerce-point-of-sale/woocommerce-point-of-sale.php', apply_filters('active_plugins', get_option('active_plugins'))))) {
				return;
			}
		}

		$changes = array();
		$StoreTermID = array();
		// Loop over all items.
		foreach ($order->get_items() as $item) {
			if (!$item->is_type('line_item')) {
				continue;
			}

			// Only increase stock once for each item.
			$product = $item->get_product();
			$wc_pos_compatiblity1 = get_option('wcmlim_wc_pos_compatiblity');

			if (($wc_pos_compatiblity1 == "on") && (in_array('woocommerce-point-of-sale/woocommerce-point-of-sale.php', apply_filters('active_plugins', get_option('active_plugins'))))) {
				$item_stock_reduced = $item->get_meta('_wcmlim_reduced_stock', true);

			} else {
				$item_stock_reduced = $item->get_meta('_reduced_stock', true);
			}



			$item_selectedLocation_key = $item->get_meta('_selectedLocationKey', true);
			$itemSelLocTermId = $item->get_meta('_selectedLocTermId', true);
			$itemSelLocName = $item->get_meta('Location', true);
			$selLocQty = get_post_meta($product->get_id(), "wcmlim_stock_at_{$itemSelLocTermId}", true);

			if (!$item_stock_reduced || !$product || !$product->managing_stock()) {
				continue;
			}

			$item_name = $product->get_formatted_name();
			$new_stock = $this->wc_update_product_stock($product, $item_stock_reduced, 'increase', false, $item_selectedLocation_key);

			if (is_wp_error($new_stock)) {
				/* translators: %s item name. */
				$order->add_order_note(sprintf(_e('Unable to restore stock for item %s.', 'woocommerce'), $item_name));
				continue;
			}
			if (($wc_pos_compatiblity1 != "on") && (!in_array('woocommerce-point-of-sale/woocommerce-point-of-sale.php', apply_filters('active_plugins', get_option('active_plugins'))))) {
				$item->delete_meta_data('_reduced_stock');
			}
			$item->save();

			$val_stock1 = intval($new_stock) - intval($item_stock_reduced);
			$changes[] = "{ Stock levels increased : {$item_name} {$val_stock1} &rarr; {$new_stock} }";

			$val_stock2 = (intval($selLocQty) + intval($item_stock_reduced));
			$locChanges[] = "{ Stock levels increased from location : {$itemSelLocName} {$selLocQty} &rarr; {$val_stock2} }";

			if (!in_array($itemSelLocTermId, $StoreTermID)) {
				$StoreTermID[] = $itemSelLocTermId;
			}
		}

		$dataLocate = array();
		foreach ($StoreTermID as $wcmlim_tid) {
			$term_object = get_term($wcmlim_tid);
			$dataLo = $term_object->term_id;
			$dataLocate[] = $dataLo;
		}
		if (!empty($dataLocate)) {
			if (WC_HPOS_IS_ACTIVE) {
				$order->update_meta_data('_multilocation', $dataLocate);
			} else {
				update_post_meta($order_id, "_multilocation", $dataLocate);
			}
		}

		if ($changes) {
			$order->add_order_note(implode(', ', $changes));
		}

		if ($locChanges) {
			$order->add_order_note(implode(', ', $locChanges));
		}

		do_action('woocommerce_restore_order_stock', $order);
	}


	public function wcmlim_stock_availability_for_each_location($stock_text, $product)
	{
		return include plugin_dir_path(__FILE__) . 'controller/location/wcmlim-stock-availability-for-each-location.php';
	}

	public function wcmlim_change_product_price($price_html, $product)
	{
		return include plugin_dir_path(__FILE__) . 'controller/shop/wcmlim-change-product-price.php';
	}

	public function wcmlim_change_cookie_change_location()
	{
		include plugin_dir_path(__FILE__) . 'controller/location/wcmlim-change-cookie-change-location.php';
	}


	public function wcmlim_change_product_query($q)
	{
		return 0;
		//tuvmos un problema de memoria y deshabilitamos este query :: spark 
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => '-1'
		);
		$term_ids = '';
		$loop = new WP_Query($args);
		$vp_ids = array();
		$all_ids = array();
		while ($loop->have_posts()):
			$loop->the_post();
			global $product;
			if ($product->is_type('simple')) {
				$product_id = $product->get_id();
			}
			if ($product->is_type('variable')) {
				$variations1 = $product->get_children();
				foreach ($variations1 as $id) {
					$vp_ids[] = $id; //variable product ids
				}
			}
			if (!empty($product_id)) {
				$all_ids[] = $product_id;
			}
		endwhile;
		wp_reset_query();

		//now get selected location id
		$selected_loc_id = $_COOKIE['wcmlim_selected_location'] ? $_COOKIE['wcmlim_selected_location'] : null;
		$locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
		foreach ($locations as $key => $term) {
			if ($key == $selected_loc_id) {
				$term_ids = $term->term_id;
				break;
			}
		}
		if (is_array($vp_ids)) {
			$all_ids = array_merge($all_ids, $vp_ids);
		}
		$product_with_outstock = array();
		//now get stock at locations for each products
		foreach ($all_ids as $pid) {
			$_product = wc_get_product($pid);
			if ($term_ids) {
				$stock_at_loc = get_post_meta($pid, "wcmlim_stock_at_{$term_ids}", true);
				$stock_at_loc = ($stock_at_loc <= 0 || $stock_at_loc == null || $stock_at_loc == "undefined") ? 0 : 1;
			} else {
				$stock_at_loc = intval($_product->get_stock_quantity());
				$stock_at_loc = ($stock_at_loc <= 0 || $stock_at_loc == null || $stock_at_loc == "undefined") ? 0 : 1;
			}
			if ($_product->is_type('simple')) {
				if ($stock_at_loc == 0) {
					$product_with_outstock[] = $pid;
				}
			} else {
				$variation_backorder = $_product->backorders_allowed();
				if ($stock_at_loc <= 0 || $stock_at_loc == null || $stock_at_loc == false) {
					if ($variation_backorder == '1') {
						$withstock[] = $_product->get_parent_id();
					}
					$product_with_outstock[] = $_product->get_parent_id();
				}
				if ($stock_at_loc > 0) {
					$withstock[] = $_product->get_parent_id();
				}
			}
		}
		if (!empty($withstock)) {
			$new_array = array_diff($product_with_outstock, $withstock);
			$q->set('post__not_in', $new_array);
			return;
		} else {
			$q->set('post__not_in', $product_with_outstock);
			return;
		}
	}

	public function wcmlim_widget_product_query($q)
	{
		$globalLocFilter = get_option("wcmlim_sort_shop_asper_glocation");
		if ($globalLocFilter == "on") {
			return true;
		}
		$location = isset($_COOKIE['wcmlim_widget_chosenlc']) ? $_COOKIE['wcmlim_widget_chosenlc'] : "";
		$locations = !empty($location) ? explode(',', $location) : array();

		if (!empty($locations)) {
			$tax_query[] = array(
				'taxonomy' => 'locations',
				'field' => 'id',
				'terms' => $locations,
				'operator' => 'IN',
			);
			$q->set('tax_query', $tax_query);
		}
	}



	public function woocommerce_template_loop_stock()
	{
		include plugin_dir_path(__FILE__) . 'controller/shop/woocommerce-template-loop.php';
	}

	function woo_locations_info($atts)
	{

		$scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http';
		$isEditor = $uri = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		if (strpos($isEditor, 'wp-json/wp/')) {
			return;
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/shortcodes/locationInfoShortcode.php';
	}

	function woo_store_finder_list_view()
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/shortcodes/locationFinderMapListShortcode.php';
	}

	function wcmlim_shortcode_atts_products($out, $pairs, $atts, $shortcode)
	{
		if (isset($atts['location_id'])) {
			$out['location_id'] = $atts['location_id'];
		}
		return $out;
	}

	function wcmlim_woocommerce_shortcode_products_query($query_args, $attributes)
	{

		if (isset($attributes['location_id'])) {
			$location_id = $attributes['location_id'];
			// write your own $query_args here
			$query_args['meta_query'] = array(
				array(
					'key' => "wcmlim_stock_at_$location_id",
					'compare' => 'EXISTS'
				)
			);
		}
		return $query_args;
	}

	function woo_store_finder()
	{
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/shortcodes/locationFinderMapShortcode.php';
	}

	function wcmlim_ajax_cart_count()
	{
		global $woocommerce;
		$new_manage_stock = [];

		foreach ($woocommerce->cart->get_cart() as $cart_item) {
			$product_ids = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
			$manage_stock = get_post_meta($product_ids, '_manage_stock', true);
			$new_manage_stock[] = $manage_stock == 'yes' ? 'yes' : 'no';
		}

		$items_count = in_array('yes', $new_manage_stock) ? count($woocommerce->cart->get_cart()) : 0;
		echo $items_count;
		die();
	}
	function wcmlim_filter_map_product_wise()
	{
		include plugin_dir_path(__FILE__) . 'controller/location/wcmlim-filter-map-product-wise.php';
	}

	public function restore_order_stock($order_id)
	{
		include plugin_dir_path(__FILE__) . 'controller/order/wcmlim-restore-order-stock.php';
	}

	/** since V3.1.1 */
	//each line item tax class code starts here
	public function overwrite_tax_calculation_to_use_product_tax_class_and_location_zip_code($item_tax_rates, $item, $cart)
	{
		$location_term_id = $_COOKIE['wcmlim_selected_location_termid'];
		$request = new WP_REST_Request('GET', "/wp/v2/locations/" . $location_term_id);
		$response = rest_do_request($request);
		if (!$response->is_error()) {
			$server = rest_get_server();
			$data = $server->response_to_data($response, false);
			$store_meta = $data["meta"];

			$current_location_standard_tax_rate = $this->get_tax_rate_for_location($store_meta, '');
			$current_location_reduced_tax_rate = $this->get_tax_rate_for_location($store_meta, 'reduced');

			switch ($item->tax_class) {
				case 'reduced':
					$rate = $current_location_reduced_tax_rate;
					break;
				default:
					$rate = $current_location_standard_tax_rate;
					break;
			}
			return $rate;
		} else {
			error_log('REST API call to get location_taxonomy for a given id failed: ' . $response->get_error_message());
		}
	}



	public function get_tax_rate_for_location($store_meta, $tax_class)
	{
		return include plugin_dir_path(__FILE__) . 'controller/tax/wcmlim-get-tax-rate-for-location.php';

	}

	public function checking_order_is_in_location_radius($rates, $package)
	{
		return include plugin_dir_path(__FILE__) . 'controller/location/wcmlim-checking-order-is-in-location-radius.php';

	}

	public function wcmlim_restrict_user_instock_quantity_text($availability, $product)
	{
		$availability = include plugin_dir_path(__FILE__) . 'controller/shop/wcmlim-restrict-user-instock-quantity-text.php';
		return $availability;
	}

	public function check_stock_for_location_group($cart_updated)
	{
		global $woocommerce;
		$items = $woocommerce->cart->get_cart();

		foreach ($items as $item => $values) {
			$_product = wc_get_product($values['data']->get_id());
		}
		//check stock for selected location
		$selected_loc_id = $_COOKIE['wcmlim_selected_location'] ? $_COOKIE['wcmlim_selected_location'] : null;
		$locations = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
		foreach ($locations as $key => $term) {
			if ($key == $selected_loc_id) {
				$term_ids = $term->term_id;
				foreach ($items as $item => $values) {
					$_product = wc_get_product($values['data']->get_id());
					$stock_at_loc = get_post_meta($_product->get_id(), "wcmlim_stock_at_{$term_ids}", true);
				}
				break;
			}
		}
		//check stock for selected location is not greater than stock entered in quantity field
		foreach ($items as $item => $values) {
			$_product = wc_get_product($values['data']->get_id());
			$stock_at_loc = get_post_meta($_product->get_id(), "wcmlim_stock_at_{$term_ids}", true);
			if ($stock_at_loc < $values['quantity']) {
				wc_add_notice(__('Stock at ' . $term->name . ' is less than quantity entered', 'woocommerce'), 'error');
				remove_action('woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20);
				$cart_updated = false;
				break;
			}
		}

	}


}





function distance_between_coordinates($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'miles')
{
	$longitude1 = (float) $longitude1;
	$longitude2 = (float) $longitude2;
	$latitude1 = (float) $latitude1;
	$latitude2 = (float) $latitude2;

	$theta = $longitude1 - $longitude2;
	$distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
	$distance = acos($distance);
	$distance = rad2deg($distance);
	$dis_unit = get_option("wcmlim_show_location_distance", true);
	$distance = $distance * 60 * 1.1515;
	$distance = round($distance, 2);
	switch ($unit) {
		case 'miles':
			$distance = $distance;
			break;
		case 'kilometers':
			$distance = $distance * 1.609344;
			$distance = $distance;
	}

	return $distance;
}

function wcmlim_get_lat_lng($address, $termid)
{
	global $latlngarr;
	$api_key = get_option('wcmlim_google_api_key');
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=false&key=' . $api_key,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
	));
	$geocode = curl_exec($curl);
	$output = json_decode($geocode);
	curl_close($curl);
	if (isset($output->results[0]->geometry->location->lat)) {
		$latitude = $output->results[0]->geometry->location->lat;
		$longitude = $output->results[0]->geometry->location->lng;
	} else {
		$latitude = 0;
		$longitude = 0;
	}
	update_term_meta($termid, 'wcmlim_lat', $latitude);
	update_term_meta($termid, 'wcmlim_lng', $longitude);


	$latlngarr = array(
		'latitude' => $latitude,
		'longitude' => $longitude
	);
	//update term meta lat lng

	return json_encode($latlngarr);
	wp_die();
}
