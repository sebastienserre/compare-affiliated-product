<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

function compare_reset_upload_dir( $dir ){
	$dir =
		array(
			'path'   => $dir['path'],
			'url'    => $dir['url'],
			'subdir' => $dir['path'],
		) + $dir;

	return $dir;
}


