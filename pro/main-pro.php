<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

add_filter( 'aawp_template_stack', 'compare_plugin_template_path__premium_only', 50, 2 );
/**
 * Customize templates path to plugin
 *
 * @param array $template_stack
 * @param array $template_names
 *
 * @return array
 */
function compare_plugin_template_path__premium_only( $template_stack, $template_names ) {

	if ( file_exists( get_stylesheet_directory() . '/aawp' ) ) {
		return $template_stack;
	}

	$template_stack = array(
		plugin_dir_path( __FILE__ ) . 'aawp/',
		plugin_dir_path( __FILE__ ) . 'aawp/products',
		plugin_dir_path( __FILE__ ) . 'aawp/parts',
	);


	return $template_stack;
}

add_action( 'wp_enqueue_scripts', 'compare_load_scripts' );
/**
 * Enqueue Scripts
 */
function compare_load_scripts() {
	/**
	 * Convert-a-link is a system from Awin
	 */
	$customer_id = get_option( 'awin' );
	wp_enqueue_script( 'convert-a-link', 'https://www.dwin2.com/pub.' . $customer_id['customer_id'] . '.min.js', array(), '1.0.0', true );
	wp_enqueue_script( 'create-link', COMPARE_PLUGIN_URL . '/assets/js/linkJS.js', array(), '1.0.0', true );
}
