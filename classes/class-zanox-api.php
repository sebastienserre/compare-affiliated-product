<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

/**
 * Class Zanox_Api
 */
class Zanox_Api {
	public function __construct() {
		//add_action( 'init', array( $this, 'compare_get_data' ) );
		//add_action('thfo_compare_after_price', array( $this, 'compare_display_price' ) );

	}

	public function compare_get_data( $data ) {

		$credentials = get_option( 'awin' );

		$response = wp_remote_get( 'https://api.zanox.com/json/2011-03-01/products?connectid=' . $credentials['connectID'] . '&q=' . $data );
		if ( 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			$decoded_body = json_decode( $body, true );
		}

		return $decoded_body;
	}
}
