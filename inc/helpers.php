<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

add_action( 'compare_daily_event', 'compare_delete_old_data' );
function compare_delete_old_data(){
	global $wpdb;
	$table = $wpdb->prefix . 'compare';
	$wpdb->query( "DELETE FROM $table WHERE `last_updated` < CURRENT_DATE" );

}