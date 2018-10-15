<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

class register_background_process extends WP_Background_Process {
	protected $action = 'register_data';

	protected function task( $item ) {

		global $wpdb;
		$table = $wpdb->prefix . 'compare';

		$insert = $wpdb->insert( $table, $item );

		return false;
	}

}
