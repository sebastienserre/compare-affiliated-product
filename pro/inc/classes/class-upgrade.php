<?php

namespace CAP\Pro\upgrade;

use function add_action;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

/**
 * Class Upgrade
 * @package CAP\Pro\upgrade
 */
class Upgrade {
	public function __construct() {
		add_action( 'admin_init', array( $this, 'add_mpn_columns' ) );
	}
	public static function add_mpn_columns() {

		global $wpdb;
		$table = $wpdb->prefix . 'compare';
		$sql = "ALTER TABLE $table ADD mpn varchar(255)";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta( $sql );
	}

}

new Upgrade();