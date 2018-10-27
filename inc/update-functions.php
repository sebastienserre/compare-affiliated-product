<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

add_action( 'admin_init', 'compare_125_add_primary_key_productid');
function compare_125_add_primary_key_productid(){
	$update125 = get_option( 'compare_update_key_125' );
	if ( false === $update125 || empty( $update125 ) ) {
		global $wpdb;
		$table = $wpdb->prefix . 'compare';
		$truncat = $wpdb->query("TRUNCATE $table" );
		$alter = $wpdb->query( "ALTER TABLE $table ADD PRIMARY KEY (productid)" );
		update_option( 'compare_update_key_125', 1);
	}
}

add_action('plugins_loaded', 'cap_upgrade_options_128' );
function cap_upgrade_options_128(){
	$option = get_option( 'compare-general');
	if ( ! empty( $option['host'] ) ){
		update_option( 'compare-advanced[host]', $option['host']);
	}
	if ( ! empty( $option['prefix'] ) ){
		update_option( 'compare-advanced[prefix]', $option['prefix']);
	}
	if ( ! empty( $option['db'] ) ){
		update_option( 'compare-advanced[db]', $option['db']);
	}
	if ( ! empty( $option['username'] ) ){
		update_option( 'compare-advanced[username]', $option['username']);
	}
	if ( ! empty( $option['pwd'] ) ){
		update_option( 'compare-advanced[pwd]', $option['pwd']);
	}
	if ( ! empty( $option['ext_check'] ) ){
		update_option( 'compare-advanced[ext_check]', $option['ext_check']);
	}
	delete_option('compare-general[host]');
	delete_option('compare-general[prefix]');
	delete_option('compare-general[db]');
	delete_option('compare-general[username]');
	delete_option('compare-general[pwd]');
	delete_option('compare-general[ext_check]');
}