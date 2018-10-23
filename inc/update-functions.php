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
		$alter = $wpdb->query( "ALTER TABLE $table ADD PRIMARY KEY (productid)" );
		update_option( 'compare_update_key_125', 1);
	}
}