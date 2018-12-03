<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

add_action( 'admin_init', 'cap_create_css_file' );
function cap_create_css_file() {
	$option = get_option( 'compare-style' );
	if ( ! empty( $option['css'] ) ) {
		$path = COMPARE_XML_PATH . '/css/';
		if (! file_exists( $path ) ){
			wp_mkdir_p( $path );
		}
		$file = fopen( COMPARE_XML_PATH . '/css/cap.css', 'w+' );
		fwrite( $file, $option['css'] );
		fclose( $file );
	}
}

add_action( 'wp_enqueue_scripts', 'cap_enqueue_custom_css', 9999, 1 );
function cap_enqueue_custom_css() {
	if ( file_exists( COMPARE_XML_PATH . '/css/cap.css' ) ) {
		$css = apply_filters( 'cap_enqueue_css', content_url( '/uploads/compare-xml/css/cap.css' ) );
		wp_enqueue_style( 'cap_style', $css, '', COMPARE_VERSION );
	}
}

add_action( 'admin_init', 'cap_delete_custom_css_file' );
function cap_delete_custom_css_file() {
	$option = get_option( 'compare-style' );
	if ( empty( $option['css'] ) ) {
		unlink( COMPARE_XML_PATH . '/css/cap.css' );
		unlink( COMPARE_XML_PATH . '/css' );
	}
}
