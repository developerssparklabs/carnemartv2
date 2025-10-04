<?php
$default_map_color = get_option('wcmlim_default_map_color');
if (isset($default_map_color) && !empty($default_map_color)) {
	$default_map_color = $default_map_color;
} else {
	$default_map_color = '#187dc7';
}
?>
<style>
	.marker-btn-1 {
		background: <?php echo $default_map_color;
					?> !important;
	}

	.marker-btn-2 {
		background: <?php echo $default_map_color;
					?> !important;
	}

	.locator-store-block h4 {
		background: <?php echo $default_map_color;
					?> !important;
	}

	.wcmlim-map-sidebar-list button {
		background: <?php echo $default_map_color;
					?> !important;
	}

	.wcmlim-top-map-widgets {
		background: <?php echo $default_map_color;
					?> !important;
	}


	.wcmlim-map-sidebar-list h4 {
		color: <?php echo $default_map_color;
				?> !important;
	}
</style>
<?php
$api_key = get_option('wcmlim_google_api_key');
$default_list_align = get_option('wcmlim_default_list_align');
$default_origin_center = get_option('wcmlim_default_origin_center');
$filter_visibility_shortcode = get_option('wcmlim_filter_visibility_shortcode');
$default_origin_center_modify = str_replace(' ', '+', $default_origin_center);
$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
$result = [];
$store_on_map_arr = [];
$mapid = 1;
$curl = curl_init();
curl_setopt_array($curl, array(
	CURLOPT_URL => 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($default_origin_center_modify) . '&sensor=false&key=' . $api_key,
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
	$originlatitude = $output->results[0]->geometry->location->lat;
	$originlongitude = $output->results[0]->geometry->location->lng;
} else {
	$originlatitude = 0;
	$originlongitude = 0;
}
$origin_store_on_map_str = array(
	"<div class='locator-store-block'><h4>" . $default_origin_center . "</h4></div>",
	floatval($originlatitude),
	floatval($originlongitude),
	intval($mapid),
	'origin'
);
array_push($store_on_map_arr, $origin_store_on_map_str);
$mapid = 2;
foreach ($terms as $k => $term) {
	$slug = $term->slug;
	$term_meta = get_option("taxonomy_$term->term_id");
	$term_meta = array_map(function ($term) {
		if (!is_array($term)) {
			return $term;
		}
	}, $term_meta);
	$_street_number = isset($term_meta['wcmlim_street_number']) ? $term_meta['wcmlim_street_number'] : "";
	$_route = isset($term_meta['wcmlim_route']) ? $term_meta['wcmlim_route'] : "";
	$_locality = isset($term_meta['wcmlim_locality']) ? $term_meta['wcmlim_locality'] : "";
	$_admin_level1 = isset($term_meta['wcmlim_administrative_area_level_1']) ? $term_meta['wcmlim_administrative_area_level_1'] : "";
	$_country = isset($term_meta['wcmlim_country']) ? $term_meta['wcmlim_country'] : "";
	$_postal_code = isset($term_meta['wcmlim_postal_code']) ? $term_meta['wcmlim_postal_code'] : "";

	$get_address =  $_street_number . ' ' . $_route . ' ' . $_locality . ' ' . $_admin_level1 . ',' . $_country . ' - ' . $_postal_code;
	$address = $_street_number . ' ' . $_route . ' ' . $_locality . ' ' . $_admin_level1 . ' ' . $_postal_code . ' ' . $_country;
	$address = str_replace(' ', '+', $address);
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
	$wcmlim_email = get_term_meta($term->term_id, 'wcmlim_email', true);
	$titletext = "<div class='locator-store-block' id='" . $term->term_id . "'><h4>" . $term->name . "</h4>";
	$site_url = get_site_url();
	$get_address_dir = str_replace(' ', '+', $get_address);

	if (isset($wcmlim_email) && !empty($wcmlim_email)) {
		$titletext = $titletext . "<p><span class='far fa-envelope'></span>" . $wcmlim_email . '</p>';
	}
	$titletext =  $titletext . "<p><span class='far fa-map'></span>" . $get_address . "</p>";
	$titletext =  $titletext . "<a class='marker-btn-1 btn btn-primary' target='_blank' href='https://www.google.com/maps/dir//$get_address_dir'> Direction </a>";
	$titletext =  $titletext . "<a class='marker-btn-2 btn btn-primary' target='_blank' href='$site_url?locations=$slug'> Shop Now </a></div>";
	$store_on_map_str = array(
		$titletext,
		floatval($latitude),
		floatval($longitude),
		intval($mapid),
		intval($term->term_id)
	);
	array_push($store_on_map_arr, $store_on_map_str);
	$mapid++;
}
update_option("store_on_map_arr", json_encode($store_on_map_arr));
?>
<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
<script src="https://unpkg.com/@google/markerclustererplus@4.0.1/dist/markerclustererplus.min.js"></script>
<script>
	/* if my "reload" var isn't set locally.. getItem will be false */
if (!localStorage.getItem("reload")) {
    /* set reload to true and then reload the page */
    localStorage.setItem("reload", "true");
    location.reload();
}
/* after reloading remove "reload" from localStorage */
else {
    localStorage.removeItem("reload");
    // localStorage.clear(); // or clear it, instead
}
	</script>
	<div class="wcmlim_shortcode_block">

<div class="wcmlim-map-widgets map-view-locations" style="display:none;">
	<div class="wcmlim-top-map-widgets">
		<?php
		if (!empty($filter_visibility_shortcode) || $filter_visibility_shortcode == 'on') {
		?>
			<div class="search-filter-toggle_parameter">

				<button class="map-search-by-btn" id="btn-filter-toggle_parameter2"><?php echo _e('Search By Product Category', 'wcmlim'); ?> </button>
				<button class="map-search-by-btn" id="btn-filter-toggle_parameter1"><?php echo _e('Search By Product', 'wcmlim'); ?> </button>
			</div>
			<div class="search-filter-toggle_parameter_cat">
				<div class="search-filter-product-category-parameter">
					<label><?php echo _e('Search By Category', 'wcmlim'); ?></label>
					<select multiple="multiple" class="multiselect" name="wcmlim_map_prodct_category_filter" id="wcmlim_map_prodct_category_filter">
						<?php
						$cat_args = array(
							'orderby'    => 'name',
							'order'      => 'asc',
							'hide_empty' => false,
						);
						$product_categories = get_terms('product_cat', $cat_args);
						if (!empty($product_categories)) {
							foreach ($product_categories as $key => $category) {
						?>
								<option value="<?php echo $category->slug; ?>">
									<?php echo $category->name; ?>
								</option>
						<?php
							}
						}
						?>
					</select>
					<div class="search-filter-submit-button">
						<button class="btn btn-primary" data-type="category" class="search-parametered-btn" id="search-parametered-btn-cat"><?php echo _e('Search', 'wcmlim'); ?></button>
					</div>
				</div>
			</div>
			<div class="search-filter-toggle_parameter_prod">
				<div class="search-filter-product-parameter">
					<label><?php echo _e('Search By Product', 'wcmlim'); ?></label>
					<select multiple="multiple" class="multiselect" name="wcmlim_map_prodct_filter" id="wcmlim_map_prodct_filter">
						<?php
						$prod_args = array(
							'post_type'      => 'product',
							'posts_per_page' => -1,
						);

						$loop = new WP_Query($prod_args);
						while ($loop->have_posts()) : $loop->the_post();
							global $product;
						?>
							<option value="<?php echo $product->get_id(); ?>">
								<?php echo get_the_title(); ?>
							</option>
						<?php
						endwhile;
						wp_reset_query();
						?>
					</select>

					<div class="search-filter-submit-button">
						<button class="btn btn-primary" data-type="product" class="search-parametered-btn" id="search-parametered-btn-pro"><?php echo _e('Search', 'wcmlim'); ?></button>
					</div>
				</div>
			</div>
		<?php
		}
		?>
		<div class="search-bar">
			<label><?php echo _e('Search Location', 'wcmlim'); ?></label>
			<input type="text" id="elementIdGlobalMap" class="form-control" placeholder="Enter Location">
			<button class="geolocation-marker" id="my_current_location" title="&nbsp; Use Current Location">
				<i class="fa fa-map-marker" aria-hidden="true"></i>
			</button>
		</div>
		<div class="distance-bar">
			<label><?php echo _e('Distance Range', 'wcmlim'); ?></label>
		</div>
		<div class="range-bar">
			<input type="range" id="rangeInput" style="margin-top: 2rem;">
			<span style="font-weight: 800;" id="rangedisplay"></span>
		</div>
	</div>
	<div class="block-1" <?php if ($default_list_align == 'left') {
								echo "style='float: right';";
							}
							?>>
		<div id="map" style="width: 100%; height: 452px;"></div>
	</div>
	<div class="wcmlim-map-sidebar-widgets block-2">
		<div class="filter-form">
			<div class="form-control">
				<?php
				foreach ($terms as $k => $term) {
					$term_meta = get_option("taxonomy_$term->term_id");
					$term_meta = array_map(function ($term) {
						if (!is_array($term)) {
							return $term;
						}
					}, $term_meta);
					$slug = $term->slug;
					$__street_number = isset($term_meta['wcmlim_street_number']) ? $term_meta['wcmlim_street_number'] : "";
					$__route = isset($term_meta['wcmlim_route']) ? $term_meta['wcmlim_route'] : "";
					$__locality = isset($term_meta['wcmlim_locality']) ? $term_meta['wcmlim_locality'] : "";
					$__admin_level1 = isset($term_meta['wcmlim_administrative_area_level_1']) ? $term_meta['wcmlim_administrative_area_level_1'] : "";
					$__country = isset($term_meta['wcmlim_country']) ? $term_meta['wcmlim_country'] : "";
					$__postal_code = isset($term_meta['wcmlim_postal_code']) ? $term_meta['wcmlim_postal_code'] : "";

					$get_address = $__street_number . ' ' . $__route . ' ' . $__locality . ' ' . $__admin_level1 . ',' . $__country . ' - ' . $__postal_code;
					$get_address_dir = str_replace(' ', '+', $get_address);
					$wcmlim_email = get_term_meta($term->term_id, 'wcmlim_email', true);

				?>
					<div class="wcmlim-map-sidebar-list" id="<?php echo $term->term_id; ?>">
						<h4>
							<?php echo $term->name; ?>
						</h4>
						<p class="location-address">
							<span class='far fa-building'></span>
							<span>
								<?php echo $get_address; ?>
							</span>
							<br />
							<?php
							if (isset($wcmlim_email) && !empty($wcmlim_email)) {
							?>
								<span class='far fa-envelope-open'></span>
								<span>
									<?php echo $wcmlim_email; ?>
								</span>
							<?php
							}
							$site_url = get_site_url();
							?>
						</p>
						<button class="btn btn-primary" onclick="window.open('https://www.google.com/maps/dir//<?php echo $get_address_dir; ?>', '_blank');">
							<?php echo _e('Direction', 'wcmlim'); ?> </button>
						<button class="btn btn-primary" onclick="window.open('<?php echo $site_url . '?locations=' . $slug; ?>', '_blank');">
							<?php echo _e('Shop Now', 'wcmlim'); ?> </button>
					</div>
				<?php
				}
				?>
			</div>
		</div>
	</div>
			</div>
</div>
<?php 
$isEditor = wp_get_referer();
if(strpos($isEditor, 'action=edit') || strpos($isEditor, 'post-new.php')){
	return ob_get_clean();
}
else 
{
return;
}
?>