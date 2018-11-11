<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

class compare_shortcode {

	public function __construct() {
		add_shortcode( 'cap', array( $this, 'cap_shortcode' ) );
	}

	public function cap_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'type'     => 'basic',
				'product'  => '',
				'platform' => 'amazon',
			),
			$atts,
			'cap'
		);

		$ean = compare_get_ean( $atts['product'] );

		$datas = template::compare_get_data( $ean, $atts );
	}
}
