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
	$options = get_option( 'compare-premium' );
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
	switch ( $data ) {
		/*case ( is_object( $data ) ):
			$asin = $data->get_product_id();
			break;*/
		case ( 10 === strlen( $data ) ):
			$asin = $data;
			break;
		default:
			$asin = $data['asin'];
			break;
	}

	$transient = get_transient( 'eanlist-' . $asin );
	if ( ! empty( $transient ) ) {
		return $transient;
	}


	$data = new Amazon();

	$params = array(
		'Operation'     => 'ItemLookup',
		'ItemId'        => $asin,
		'ResponseGroup' => 'ItemAttributes',
	);

	$apikey        = $data->amz['apikey'];
	$secret        = $data->amz['secretkey'];
	$associate_tag = $data->amz['trackingid'];

	$asin2ean = aws_signed_request( 'fr', $params, $apikey, $secret, $associate_tag );

	$asin2ean = wp_remote_get( $asin2ean );
	$asin2ean = $asin2ean['body'];

	$amazon  = simplexml_load_string( $asin2ean );
	$json    = wp_json_encode( $amazon );
	$array   = json_decode( $json, true );
	$eanlist = $array['Items']['Item']['ItemAttributes']['EANList']['EANListElement'];
	if ( ! is_array( $eanlist ) ) {
		$eanlist[0] = $array['Items']['Item']['ItemAttributes']['EANList']['EANListElement'];
	}
	$transient = set_transient( 'eanlist-' . $asin, $eanlist, 4 * HOUR_IN_SECONDS );
	return $eanlist;
}

/**
 * Create a file with the date to avoid launching cron twice
 */
function cap_create_pid() {

	$date = date( 'd F Y @ H\hi:s' );
	$file = fopen( COMPARE_PLUGIN_PATH . 'compare.txt', 'w+' );
	fwrite( $file, $date );
	fclose( $file );
}

function cap_delete_pid() {
	if ( file_exists( 'compare.txt' ) ) {
		unlink( COMPARE_PLUGIN_PATH . 'compare.txt' );
	}
}

function create_index () {
	global $wpdb;

	$sql = "CREATE INDEX cap_ean ON {$wpdb->prefix}compare (ean)";

	$wpdb->query ( $sql );
}

function cap_format_ean( $ean ){
	/**
	 * SELECT * FROM k13s_compare WHERE LENGTH(ean) > 13
	 * ALTER TABLE `k13s_compare` ADD INDEX( `ean`);
	 */
	if ( strlen( $ean ) > 13 ) {
		$ean = str_replace( ' ', '', $ean );
	}
	if ( strlen( $ean ) > 13 ) {
		$ean = '';
	}
	return $ean;
}
