<?php

namespace EIB2BPRO\Core;

defined('ABSPATH') || exit;

class Activate
{
	public static function activate()
	{

		/* Check WooCommerce */

		if (!class_exists('WooCommerce', false)) {
			die(esc_html__('WooCommerce must be enabled before activate the B2B Pro', 'eib2bpro'));
		}

		if (!function_exists('eib2bpro_option')) {
			require_once EIB2BPRO_DIR . 'helpers/functions.php';
		}

		update_option('eib2bpro_current_version', EIB2BPRO_VERSION, false);

		eib2bpro_option('tracker', 1, 'set');

		eib2bpro_option('theme', 'one', 'set');
		eib2bpro_option('theme_menu_icon_size', 31, 'set');
		eib2bpro_option('theme_menu_padding', 14, 'set');
		eib2bpro_option('font', 'Noto Sans', 'set');

		$widgets = array(
			"61adfb8c15a1e" => array(
				"type" => "overview",
				"col" => 1,
				"row" => 1,
				"id" => "61adfb8c15a1e",
				"w" => 20,
				"h" => 3
			),

			"61adfb95cf988" => array(
				"type" => "onlineusers",
				"col" => 1,
				"row" => 4,
				"id" => "61adfb95cf988",
				"w" => 4,
				"h" => 8
			),

			"61adfb9e1736c" => array(
				"type" => "hourly",
				"col" => 5,
				"row" => 4,
				"id" => "61adfb9e1736c",
				"w" => 16,
				"h" => 8
			),

			"61adfba8ebc21" => array(
				"type" => "lastactivity",
				"col" => 1,
				"row" => 12,
				"id" => "61adfba8ebc21",
				"w" => 14,
				"h" => 12
			),

			"61adfbb061c45" => array(
				"type" => "productviews",
				"col" => 15,
				"row" => 12,
				"id" => "61adfbb061c45",
				"w" => 6,
				"h" => 12
			),

			"61adfbbd7bc30" => array(
				"id" => "61adfbbd7bc30",
				"type" => "funnel",
				"w" => 20,
				"h" => 8
			)
		);

		eib2bpro_option('dashboard-widgets', $widgets, 'set');

		eib2bpro_option('top-widgets', [
			'me' => ['active' => 1, 'position' => 0],
			'divider_1' => ['active' => 1, 'position' => 1],
			'notifications' => ['active' => 1, 'position' => 2],
			'orders' => ['active' => 1, 'position' => 3],
			'divider_2' => ['active' => 1, 'position' => 4],
			'online_visitors' => ['active' => 1, 'position' => 5],
			'today_revenue' => ['active' => 1, 'position' => 6]
		], 'set');

		self::db();

		\EIB2BPRO\B2b\Admin\Toolbox::activate();
		\EIB2BPRO\B2b\Admin\Main::clear_cache();


		if (!wp_next_scheduled('eib2bpro_hourly_cron')) {
			wp_schedule_event(time(), 'hourly', 'eib2bpro_hourly_cron');
		}

		// save default todo
		$data = [
			'content' => esc_html__('Type a private to-do for yourself and press enter', 'eib2bpro'),
			'checked' => 0,
			'status' => 1
		];
		\EIB2BPRO\Core\Todo::save(0, $data);

		// save default note
		$data = [
			'resource_type' => 'private',
			'content' =>  esc_html__('Write here your private note...', 'eib2bpro')
		];
		\EIB2BPRO\Core\Note::save(0, $data);
	}

	public static function deactivate()
	{
		wp_clear_scheduled_hook('eib2bpro_hourly_cron');
	}


	public static function db()
	{
		global $wpdb;

		// Create the database tables
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$table_name = $wpdb->prefix . 'eib2bpro_events';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
		event_id int(11) NOT NULL AUTO_INCREMENT,
		user_id int(11) NOT NULL DEFAULT '0',
		event_type varchar(32) NOT NULL DEFAULT '',
		resource_id int(11) DEFAULT NULL,
		extra text DEFAULT NULL,
		event_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY  (event_id),
		KEY event_type  (event_type)
	) $charset_collate";

		dbDelta($sql);


		$table_name = $wpdb->prefix . 'eib2bpro_requests';

		$sql = "CREATE TABLE $table_name (
		request_id int(11) NOT NULL AUTO_INCREMENT,
		session_id varchar(32) DEFAULT NULL,
		year tinyint(2) DEFAULT NULL,
		month tinyint(2) DEFAULT NULL,
		week tinyint(2) DEFAULT NULL,
		day tinyint(2) DEFAULT NULL,
		date timestamp NULL DEFAULT NULL,
		time int(11) NOT NULL,
		visitor varchar(33) NOT NULL,
		type tinyint(4) NOT NULL DEFAULT '0',
		id int(11) DEFAULT NULL,
		extra text,
		ref varchar(254) NOT NULL,
		ip varchar(32) NULL,
		PRIMARY KEY  (request_id),
		KEY type (type),
		KEY month (month),
		KEY week (week)
	) $charset_collate";

		dbDelta($sql);

		$table_name = $wpdb->prefix . 'eib2bpro_note';

		$sql = "CREATE TABLE $table_name (
		id int(11) NOT NULL AUTO_INCREMENT,
		resource_type varchar(10) DEFAULT NULL,
		resource_id int(11) DEFAULT NULL,
		extra_id int(11) DEFAULT NULL,
		created_by int(11) DEFAULT '0',
		created_at timestamp,
		status tinyint(4) DEFAULT '1',
		content text,
		users text,
		collapsed tinyint(1) DEFAULT NULL,
		position int(11) DEFAULT '0',
		color varchar(10) DEFAULT NULL,
		updated_at timestamp NULL DEFAULT NULL,
		PRIMARY KEY  (id)
	) $charset_collate";

		dbDelta($sql);

		$table_name = $wpdb->prefix . 'eib2bpro_todo';

		$sql = "CREATE TABLE $table_name (
		id int(11) NOT NULL AUTO_INCREMENT,
		list_id int(11) DEFAULT NULL,
		section_id int(11) DEFAULT '0',
		checked tinyint(1) DEFAULT '0',
		status tinyint(4) DEFAULT '1',
		content text,
		resource_type varchar(10) DEFAULT NULL,
		resource_id int(11) DEFAULT NULL,
		details text,
		created_by int(11) DEFAULT '0',
		created_at timestamp NULL DEFAULT NULL,
		updated_at timestamp NULL DEFAULT NULL,
		deleted_at timestamp NULL DEFAULT NULL,
		parent_id int(11) DEFAULT '0',
		due timestamp NULL DEFAULT NULL,
		priority tinyint(4) DEFAULT '0',
		position int(11) DEFAULT NULL,
		users text,
		PRIMARY KEY  (id)
	) $charset_collate";

		dbDelta($sql);

		$table_name = $wpdb->prefix . 'eib2bpro_meta';

		$sql = "CREATE TABLE $table_name (
			meta_id int(11) NOT NULL AUTO_INCREMENT,
			resource_type varchar(32) DEFAULT NULL,
			resource_id int(11) DEFAULT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value text NULL DEFAULT NULL,
			PRIMARY KEY  (meta_id),
			KEY resource_id (resource_id),
			KEY meta_key (meta_key)
		) $charset_collate";

		dbDelta($sql);

		add_option('eib2bpro_db_version', '1', false);
	}
}
