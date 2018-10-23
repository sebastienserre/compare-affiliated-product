<?php
/*
	Plugin Name: Compare Affiliated Products
	Plugin URI: https://www.thivinfo.com
	Description: Display Easily products from your affiliate program (Awin)
	Author: Sébastien SERRE
	Author URI: https://thivinfo.com
	Tested up to: 4.9
	Requires PHP: 5.6
	Text Domain: compare
	Domain Path: /languages/
	Version: 1.2.5
	*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.
/**
 * Define Constant
 */
define( 'COMPARE_VERSION', '1.2.5' );
define( 'COMPARE_PLUGIN_NAME', 'Compare Affliated Product' );
define( 'COMPARE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'COMPARE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'COMPARE_PLUGIN_DIR', untrailingslashit( COMPARE_PLUGIN_PATH ) );
define( 'COMPARE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

$upload = wp_upload_dir(  );

define( 'COMPARE_XML_PATH', $upload['basedir'] . '/compare-xml/' );

/**
 * Increase memory to allow large files download / treatment
 */
if ( ! defined( 'WP_MEMORY_LIMIT' ) ) {
	define( 'WP_MEMORY_LIMIT', '512M' );
}

add_action( 'plugins_loaded', 'compare_load_files' );
function compare_load_files() {
	include_once COMPARE_PLUGIN_PATH . '/admin/settings.php';
	include_once COMPARE_PLUGIN_PATH . '/admin/upgrade-notices/upgrade-120-effiliation.php';
	include_once COMPARE_PLUGIN_PATH . '/classes/class-zanox-api.php';
	include_once COMPARE_PLUGIN_PATH . '/classes/class-awin.php';
	include_once COMPARE_PLUGIN_PATH . '/3rd-party/aws_signed_request.php';
	include_once COMPARE_PLUGIN_PATH . '/inc/helpers.php';
	include_once COMPARE_PLUGIN_PATH . '/inc/update-functions.php';
	include_once COMPARE_PLUGIN_PATH . '/shortcode/class-compare-basic-shortcode.php';
	include_once COMPARE_PLUGIN_PATH . '/classes/class_cloak_link.php';
	include_once COMPARE_PLUGIN_PATH . '/classes/class-compare-external-db.php';
	include_once COMPARE_PLUGIN_PATH . '/classes/class-effiliation.php';
	include_once COMPARE_PLUGIN_PATH . '/classes/class-template.php';

}

add_action( 'plugins_loaded', 'compare_load_textdomain' );
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function compare_load_textdomain() {
	load_plugin_textdomain( 'compare', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

add_filter( 'aawp_template_stack', 'compare_plugin_template_path', 50, 2 );
/**
 * Customize templates path to plugin
 *
 * @param array $template_stack
 * @param array $template_names
 *
 * @return array
 */
function compare_plugin_template_path( $template_stack, $template_names ) {

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

add_action( 'wp_enqueue_scripts', 'compare_load_style' );
function compare_load_style() {
	wp_enqueue_style( 'compare_partner', COMPARE_PLUGIN_URL . '/assets/css/compare-partner.css', '', COMPARE_VERSION );
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

/**
 * Triggered on admin_init if plugin updated by FTP
 */
add_action( 'admin_init', 'compare_create_db' );
function compare_create_db(){
	/**
	 * Create Table
	 */
	global $wpdb;
	$charset_collate    = $wpdb->get_charset_collate();
	$compare_table_name = $wpdb->prefix . 'compare';

	$compare_sql = "CREATE TABLE IF NOT EXISTS $compare_table_name(
productid varchar(255) DEFAULT NULL,
platform text DEFAULT NULL,
ean varchar(255) DEFAULT NULL,
title text DEFAULT NULL,
description text DEFAULT NULL,
img text DEFAULT NULL,
partner_name varchar(255) DEFAULT NULL,
partner_code varchar(45) DEFAULT NULL,
url text DEFAULT NULL,
price varchar(10) DEFAULT NULL,
last_updated datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
PRIMARY KEY (productid)
) $charset_collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$dd = dbDelta( $compare_sql );
}

register_activation_hook( __FILE__, 'compare_activation' );

function compare_activation() {

	/**
	 * Create DB
	 */

	compare_create_db();

	/**
	 * Create Cron Tasks
	 */
	compare_create_cron();

	/**
	 * Create a folder to store xml files
	 */

	$upload     = wp_upload_dir();
	$upload_dir = $upload['basedir'];
	$upload_dir = $upload_dir . '/compare-xml';
	if ( ! is_dir( $upload_dir ) ) {
		mkdir( $upload_dir, 0700 );
	}
}

register_uninstall_hook( __FILE__, 'compare_uninstall' );
function compare_uninstall() {
	global $wpdb;
	$options = get_option( 'compare-general' );
	$delete  = $options['delete'];

	if ( 'yes' === $delete ) {
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}compare;" );
	}
}

/**
 * Create Daily Cron Task
 */

function compare_create_cron() {
	if ( ! wp_next_scheduled( 'compare_daily_event' ) ) {
		wp_schedule_event( time(), 'daily', 'compare_daily_event' );
	}
	if ( ! wp_next_scheduled( 'compare_twice_event' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'compare_twice_event' );
	}
	if ( ! wp_next_scheduled( 'compare_four_hour_event' ) ) {
		wp_schedule_event( time(), 'fourhour', 'compare_fourhour_event' );
	}
}


add_filter( 'cron_schedules', 'compare_sechule4_hours' );
function compare_sechule4_hours( $schedules ) {

	// add a 'weekly' schedule to the existing set
	$schedules['fourhour'] = array(
		'interval' => 14400,
		'display'  => __( 'Every 4 hours', 'compare' )
	);

	return $schedules;
}

// Create a helper function for easy SDK access.
function cap_fs() {
	global $cap_fs;

	if ( ! isset( $cap_fs ) ) {
		// Include Freemius SDK.
		require_once dirname( __FILE__ ) . '/freemius/start.php';

		$cap_fs = fs_dynamic_init( array(
			'id'                  => '2422',
			'slug'                => 'compare-affiliated-products',
			'type'                => 'plugin',
			'public_key'          => 'pk_ff3b951b9718b0f9e347ba2925627',
			'is_premium'          => true,
			'is_premium_only'     => true,
			// If your plugin is a serviceware, set this option to false.
			'has_premium_version' => true,
			'has_addons'          => false,
			'has_paid_plans'      => true,
			'is_org_compliant'    => false,
			'trial'               => array(
				'days'               => 30,
				'is_require_payment' => false,
			),
			'has_affiliation'     => 'selected',
			'menu'                => array(
				'slug'    => 'compare-settings',
				'support' => false,
				'parent'  => array(
					'slug' => 'options-general.php',
				),
			),
			// Set the SDK to work in a sandbox mode (for development & testing).
			// IMPORTANT: MAKE SURE TO REMOVE SECRET KEY BEFORE DEPLOYMENT.
			'secret_key'          => 'sk_&S7jJvcB]OCZBp>^Hf.~XVL;0eccs',
		) );
	}

	return $cap_fs;
}

// Init Freemius.
cap_fs();
// Signal that SDK was initiated.
do_action( 'cap_fs_loaded' );

add_action( 'admin_print_styles', 'compare_admin_style', 11 );
function compare_admin_style() {
	wp_enqueue_style( 'compare-admin-style', COMPARE_PLUGIN_URL . 'assets/css/compare-admin.css', '', COMPARE_VERSION );
}

add_action( 'plugins_loaded', 'compare_add_db_column' );
function compare_add_db_column() {
	global $wpdb;

	$compare_table_name = $wpdb->prefix . 'compare';

	$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
WHERE table_name = '$compare_table_name' AND column_name = 'platform'" );

	if ( empty( $row ) ) {
		$wpdb->query( "ALTER TABLE $compare_table_name ADD platform text DEFAULT NULL" );
	}
}

function responsive_tables_enqueue_script() {
	wp_enqueue_script( 'responsive-tables', get_stylesheet_directory_uri() . '/responsive-tables.js', $deps = array(), $ver = false, $in_footer = true );
}

add_action( 'wp_enqueue_scripts', 'responsive_tables_enqueue_script' );