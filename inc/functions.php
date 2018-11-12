<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

add_action( 'admin_init', 'cap_delete_transients' );
function cap_delete_transients() {
	if ( ! empty( $_GET['transient-delete'] ) && 'ok' === $_GET['transient-delete'] ) {
		global $wpdb;
		$table = $wpdb->prefix . 'options';
		$wpdb->query( "DELETE FROM $table WHERE `option_name` LIKE '_transient_amz-%'" );
		$wpdb->query( "DELETE FROM $table WHERE `option_name` LIKE '_transient_timeout_amz-%'" );

		$wpdb->query( "DELETE FROM $table WHERE `option_name` LIKE '_transient_product_%'" );
		$wpdb->query( "DELETE FROM $table WHERE `option_name` LIKE '_transient_timeout_product_%'" );
	}
}
