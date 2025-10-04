<?php

class WC_API_WCMLIM extends WC_API_Resource {
	protected $base = '/locations';

	public function register_routes( $routes ) {
		$routes[ $this->base ] = array(
			array( array( $this, 'get_location' ), WC_API_Server::READABLE )
		);

		return $routes;
	}
	
	public function get_location() {
		$terms = get_terms(array('taxonomy' => 'locations', 'hide_empty' => false, 'parent' => 0));
		if (empty($terms)) {
			return new WP_Error( 'empty_locations', 'there is no Locations', array('status' => 404) );
		}    
		return $terms;
	}
}
?>