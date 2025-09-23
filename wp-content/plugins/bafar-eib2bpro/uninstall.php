<?php

/**
 * EIB2BPRO Uninstall
 *
 * Fired during EIB2BPRO uninstall
 *
 * @since      1.0.0
 * @author     EN.ER.GY <support@en.er.gy>
 * */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

if (0 === intval(get_option('eib2bpro_keep_data', 1))) {
    global $wpdb;

    // Remove options
    $options = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'eib2bpro_%' LIMIT %d",
            1000000
        )
    );
    foreach ($options as $option) {
        delete_option($option->option_name);
    }

    // Remove tables
    $tableArray = array(
        $wpdb->prefix . "eib2bpro_events",
        $wpdb->prefix . "eib2bpro_requests",
        $wpdb->prefix . "eib2bpro_note",
        $wpdb->prefix . "eib2bpro_todo",
        $wpdb->prefix . "eib2bpro_meta"
    );

    foreach ($tableArray as $tablename) {
        $wpdb->query("DROP TABLE IF EXISTS $tablename");
    }

    // Remove posts
    $types = [
        ['slug' => 'eib2bpro_groups', 'title' => esc_html__('B2B Pro Groups', 'eib2bpro')],
        ['slug' => 'eib2bpro_fields', 'title' => esc_html__('B2B Pro Custom Fields', 'eib2bpro')],
        ['slug' => 'eib2bpro_regtype', 'title' => esc_html__('B2B Pro Reg. Types', 'eib2bpro')],
        ['slug' => 'eib2bpro_offers', 'title' => esc_html__('B2B Pro Offers', 'eib2bpro')],
        ['slug' => 'eib2bpro_quote', 'title' => esc_html__('B2B Pro Quotes', 'eib2bpro')],
        ['slug' => 'eib2bpro_quote_field', 'title' => esc_html__('B2B Pro Quote Fields', 'eib2bpro')],
        ['slug' => 'eib2bpro_rules', 'title' => esc_html__('B2B Pro Rules', 'eib2bpro')],
        ['slug' => 'eib2bpro_quick', 'title' => esc_html__('B2B Pro Quick Orders', 'eib2bpro')]
    ];
    foreach ($types as $type) {
        $posts = get_posts(['post_type' => $type['slug'], 'numberposts' => -1, 'post_status' => 'any']);
        foreach ($posts as $post) {
            wp_delete_post($post->ID, true);
        }
    }

    // Remove product metadata
    $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE 'eib2bpro_%' LIMIT %d", 1000000));
    $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '_eib2bpro_%' LIMIT %d", 1000000));

    // Remove user metadata
    $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'eib2bpro_%' LIMIT %d", 1000000));
    $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->usermeta WHERE meta_key LIKE '_eib2bpro_%' LIMIT %d", 1000000));

    // Cache flush
    wp_cache_flush();
}
