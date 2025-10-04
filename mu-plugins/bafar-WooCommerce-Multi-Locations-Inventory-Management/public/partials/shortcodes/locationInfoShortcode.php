<?php

$api_key = get_option('wcmlim_google_api_key');
$loc_texanomy_id = $atts['id'];
if (isset($loc_texanomy_id) && !empty($loc_texanomy_id)) {
	$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false));
	foreach ($terms as $in => $term) {
		if ($term->term_id == $loc_texanomy_id) {
			$term_meta = get_option("taxonomy_$term->term_id");
			$termid = $term->term_taxonomy_id;
			$loc_phone = get_term_meta($term->term_id, 'wcmlim_phone', true);
			$loc_email = get_term_meta($term->term_id, 'wcmlim_email', true);
			$start_time = get_term_meta($term->term_id, 'wcmlim_start_time', true);
			$end_time = get_term_meta($term->term_id, 'wcmlim_end_time', true);
			$default_location_time = get_option('wcmlim_default_location_time');
			if (empty($default_location_time)) {
				$default_location_time = update_option('wcmlim_default_location_time', "10.00 AM - 6.00 PM");
			}
			$default_location_time = get_option('wcmlim_default_location_time');
			$term_meta = get_option("taxonomy_$term->term_id");
			$location_address = $term_meta['wcmlim_street_number'] . ' ' . $term_meta['wcmlim_route'] . ' ' . $term_meta['wcmlim_locality'] . ' ' . $term_meta['wcmlim_administrative_area_level_1'] . ' ' . $term_meta['wcmlim_postal_code'] . ' ' . $term_meta['wcmlim_country'];
?>
			<div class="wcmlim_fe_location-mini-store">
				<h3 class="location_name">
					<?php echo $term->name; ?>
				</h3>
				<hr />
				<div class="wcmlim_fe_left" style="width: 50%;vertical-align: top;">
					<div class="wcmlim_fe_location_address">
						<label> <span class="fa fa-map-marker"></span>
							<?php echo $location_address; ?>
						</label>
					</div>
					<div class="wcmlim_fe_location_contact">
						<?php
						if (!empty($loc_phone)) {
						?>
							<label> <span class="fa fa-mobile"></span>
								<?php echo $loc_phone; ?>
							</label>
						<?php
						}
						if (!empty($loc_email)) {
						?>
							<label> <span class="fa fa-envelope-o"></span>
								<?php echo $loc_email; ?>
							</label>
						<?php
						}
						?> <br />
					</div>
				</div>
				<div class="wcmlim_fe_right" style="width: 25%;">
					<div class="wcmlim_fe_location_availability">

						<?php if (empty($start_time) && empty($end_time)) {
							echo '<label> <span class="fa fa-clock"></span> Time ' . $default_location_time . '</label>';
						} else {
						?>
							<label> <span class="fa fa-clock"></span> Time
								<?php echo $start_time . '-' . $end_time; ?>
							</label>
						<?php
						}
						?>
						<br />
					</div>
				</div>
				<div class="wcmlim_fe_right" style="width: 20%;float: right;">
					<div id="wcmlim_fe_location_map" style="margin-top: -1.2rem; width:100%;height:115px;">
					</div>
					<a class="get_direction btn btn-primary" target='_blank' href='https://www.google.com/maps/dir//<?php echo $location_address; ?>'> Get Direction
					</a>
					<script>
						jQuery(document).ready(($) => {
							var geocoder = new google.maps.Geocoder();
							geocoder.geocode({
								'address': "<?php echo $location_address; ?>"
							}, function(results, status) {
								if (status == google.maps.GeocoderStatus.OK) {
									var location_infowindow = new google.maps.InfoWindow();
									location_lat = results[0].geometry.location.lat();
									location_lng = results[0].geometry.location.lng();
									map = new google.maps.Map(document.getElementById('wcmlim_fe_location_map'), {
										zoom: 5,
										center: new google.maps.LatLng(location_lat, location_lng),
										mapTypeId: google.maps.MapTypeId.ROADMAP
									});
									marker = new google.maps.Marker({
										position: new google.maps.LatLng(location_lat, location_lng),
										map: map,
										label: {
											fontFamily: "'Font Awesome 5 Free'",
											fontWeight: '900', //careful! some icons in FA5 only exist for specific font weights
											color: '#FFFFFF', //color of the text inside marker
										},
									});
									google.maps.event.addListener(marker, 'click', (function(marker) {
										return function() {
											location_infowindow.setContent("<?php echo $location_address; ?>");
											location_infowindow.open(map, marker);
										}
									})(marker));
								}
							});
						});
					</script>
				</div>
			</div>
<?php
		}
	}
}
?>
