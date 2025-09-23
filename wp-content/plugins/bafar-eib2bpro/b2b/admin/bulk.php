<?php

namespace EIB2BPRO\B2b\Admin;

defined('ABSPATH') || exit;

class Bulk
{
	public static function run()
	{

		switch (eib2bpro_get('action')) {
			case 'category':
				self::category();
				break;
		}
	}

	public static function category()
	{
		echo eib2bpro_view('b2b', 'admin', 'bulk.category');
	}

	public static function save_category()
	{
		\EIB2BPRO\Admin::wc_engine();

		$groups        = \EIB2BPRO\B2b\Admin\Groups::get();
		$groups[-1] = (object) array('ID' => 'b2c');
		$groups[-2] = (object) array('ID' => 'guests');

		$categories = \WC()->api->WC_API_Products->get_product_categories()['product_categories'];
		foreach ($categories as $category) {
			foreach ($groups as $group) {
				update_term_meta(intval($category['id']), 'eib2bpro_group_' . $group->ID, intval(eib2bpro_post('new_' . $category['id'] . '_' . $group->ID, 0)));
			}
		}
		Main::clear_cache();

		eib2bpro_success();
	}
}
