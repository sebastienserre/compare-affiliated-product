<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

class compare_external_db {

	public function __construct() {
		add_action( 'admin_init', array( $this, 'compare_external_cnx' ) );
	}

	public function compare_external_cnx() {

		$external = get_option( 'general' );
		$external = $external['ext_check'];
		if ( 'on' === $external ) {
			$host     = $external['host'];
			$db       = $external['db'];
			$username = $external['username'];
			$password = $external['pwd'];
			$sql      = new wpdb( $username, $password, $db, $host );

			return $sql;
		}
	}

}

new compare_external_db();

