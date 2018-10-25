<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

class Amazon {

	public function __construct() {
		add_filter('compare_setting_tabs', array( $this, 'compare_amazon_settings_tabs' ) );
	}

	public function compare_amazon_settings_tabs( $tabs ){
		$tabs['amazon'] = 'Amazon';
		return $tabs;
	}
}
new Amazon();