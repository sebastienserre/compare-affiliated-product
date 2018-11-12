<?php
if ( ! defined( 'ABSPATH' ) ) {
		exit;
} // Exit if accessed directly.

/**
 * @param $dir array array with paths & url
 *
 * @return array the new dir & path
 */
function compare_reset_upload_dir( $dir ){
	$dir =
		array(
			'path'   => $dir['path'],
			'url'    => $dir['url'],
			'subdir' => $dir['path'],
		) + $dir;

	return $dir;
}

add_filter( 'compare_url_tracker', 'compare_customize_tracker_word' );
/**
 * @param $word string default string is your website URL.
 *
 * @return string you input in settings
 */
function compare_customize_tracker_word( $word ){
	$options = get_option( 'compare-general' );
	if ( !empty( $options['tracker'] ) ){
		$word = $options['tracker'];
	}
	return $word;
}

/**
 * Get All EAN Code attached to the ASIN
 *
 * @param array $data array of data about displayed product.
 */
function compare_get_ean( $data ) {
	switch ( $data ){
		case ( is_object( $data ) ):
			$asin = $data->get_product_id();
			break;
		case ( 10 === strlen( $data ) ):
			$asin = $data;
			break;
		default:
			$asin = $data['asin'];
			break;
	}

	$data = new AAWP_Template_Handler();

	$params = array(
		'Operation'     => 'ItemLookup',
		'ItemId'        => $asin,
		'ResponseGroup' => 'ItemAttributes',
	);

	$apikey        = $data->api_key;
	$secret        = $data->api_secret_key;
	$associate_tag = $data->api_associate_tag;

	$asin2ean = aws_signed_request( 'fr', $params, $apikey, $secret, $associate_tag );

	$asin2ean = wp_remote_get( $asin2ean );
	$asin2ean = $asin2ean['body'];

	$amazon  = simplexml_load_string( $asin2ean );
	$json    = wp_json_encode( $amazon );
	$array   = json_decode( $json, true );
	$eanlist = $array['Items']['Item']['ItemAttributes']['EANList']['EANListElement'];
	if ( ! is_array( $eanlist ) ) {
		$eanlist = array( $eanlist );
	}
	return $eanlist;
}
