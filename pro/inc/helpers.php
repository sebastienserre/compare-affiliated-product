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
