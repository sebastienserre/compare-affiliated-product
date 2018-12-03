<?php
/*
	Plugin Name: Compare Affiliated Products
	Plugin URI: https://www.thivinfo.com
	Description: Display Easily products from your affiliated programs (Amazon, Awin, Effiliation...)
	Author: Sébastien SERRE
	Author URI: https://thivinfo.com
	Tested up to: 5.0
	Requires PHP: 5.6
	Text Domain: compare-affiliated-products
	Domain Path: /pro/languages/
	@fs_premium_only /pro/, /languages/, /cron.php, /compare.txt
	Version: 2.0.9
	*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly.
/**
 * Define Constant
 */
define( 'COMPARE_VERSION', '2.0.9' );
define( 'COMPARE_PLUGIN_NAME', 'Compare Affliated Product' );
define( 'COMPARE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'COMPARE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'COMPARE_PLUGIN_DIR', untrailingslashit( COMPARE_PLUGIN_PATH ) );
define( 'COMPARE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
$upload = wp_upload_dir();
define( 'COMPARE_XML_PATH', $upload['basedir'] . '/compare-xml/' );

// Create a helper function for easy SDK access.
/**
 * Do not Edit in any cases
 *
 * @return Freemius
 * @throws Freemius_Exception
 */
function cap_fs() {
	global $cap_fs;

	if ( ! isset( $cap_fs ) ) {
		// Include Freemius SDK.
		require_once dirname(__FILE__) . '/freemius/start.php';

		$cap_fs = fs_dynamic_init( array(
			'id'                  => '2422',
			'slug'                => 'compare-affiliated-products',
			'type'                => 'plugin',
			'public_key'          => 'pk_ff3b951b9718b0f9e347ba2925627',
			'is_premium'          => false,
			'has_addons'          => false,
			'has_paid_plans'      => true,
			'trial'               => array(
				'days'               => 30,
				'is_require_payment' => false,
			),
			'has_affiliation'     => 'selected',
			'menu'                => array(
				'slug'           => 'compare-settings',
				'support'        => false,
				'parent'         => array(
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

/**
 * Increase memory to allow large files download / treatment
 */
if ( ! defined( 'WP_MEMORY_LIMIT' ) ) {
	define( 'WP_MEMORY_LIMIT', '512M' );
}

add_action( 'plugins_loaded', 'compare_load_files' );
function compare_load_files() {

	include_once COMPARE_PLUGIN_PATH . '/admin/ads.php';
	include_once COMPARE_PLUGIN_PATH . '/admin/upgrade-notices/upgrade-120-effiliation.php';
	include_once COMPARE_PLUGIN_PATH . '/3rd-party/aws_signed_request.php';
	include_once COMPARE_PLUGIN_PATH . '/inc/update-functions.php';
	include_once COMPARE_PLUGIN_PATH . '/admin/settings.php';
	include_once COMPARE_PLUGIN_PATH . '/admin/css.php';
	include_once COMPARE_PLUGIN_PATH . '/classes/amazon.php';
	include_once COMPARE_PLUGIN_PATH . '/classes/class-compare-shortcode.php';
	include_once COMPARE_PLUGIN_PATH . '/inc/functions.php';

	if ( cap_fs()->is__premium_only() ){
		include_once COMPARE_PLUGIN_PATH . '/pro/inc/classes/class-template.php';
		include_once COMPARE_PLUGIN_PATH . '/pro/main-pro.php';
		include_once COMPARE_PLUGIN_PATH . '/pro/admin/settings.php';
		include_once COMPARE_PLUGIN_PATH . '/pro/inc/helpers.php';
		include_once COMPARE_PLUGIN_PATH . '/pro/inc/classes/class-zanox-api.php';
		include_once COMPARE_PLUGIN_PATH . '/pro/inc/classes/class-awin.php';
		include_once COMPARE_PLUGIN_PATH . '/pro/deprecated/shortcode/class-compare-basic-shortcode.php';
		include_once COMPARE_PLUGIN_PATH . '/pro/deprecated/shortcode/class-compare-price.php';
		include_once COMPARE_PLUGIN_PATH . '/pro/inc/classes/class_cloak_link.php';
		include_once COMPARE_PLUGIN_PATH . '/pro/inc/classes/class-compare-external-db.php';
		include_once COMPARE_PLUGIN_PATH . '/pro/inc/classes/class-effiliation.php';
		include_once COMPARE_PLUGIN_PATH . '/pro/inc/scheduler.php';
		include_once COMPARE_PLUGIN_PATH . '/pro/inc/cron.php';
	}
}

add_action( 'init', 'compare_load_textdomain__premium_only' );
/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
function compare_load_textdomain__premium_only() {
	$lang = load_plugin_textdomain( 'compare-affiliated-products-pro', false, dirname( plugin_basename( __FILE__ ) ) . '/pro/languages' );
}

add_action( 'wp_enqueue_scripts', 'compare_load_style' );
function compare_load_style() {
	wp_enqueue_style( 'compare_partner', COMPARE_PLUGIN_URL . '/assets/css/compare-partner.css', '', COMPARE_VERSION );
}

/**
 * Triggered on admin_init if plugin updated by FTP
 */
add_action( 'admin_init', 'compare_create_db' );
function compare_create_db() {
	/**
	 * Create Table
	 */
	global $wpdb;
	$charset_collate    = $wpdb->get_charset_collate();
	$compare_table_name = $wpdb->prefix . 'compare';

	$compare_sql = "CREATE TABLE IF NOT EXISTS $compare_table_name(
productid varchar(255) DEFAULT NULL,
platform text DEFAULT NULL,
ean varchar(13) DEFAULT NULL,
title text DEFAULT NULL,
description text DEFAULT NULL,
img text DEFAULT NULL,
partner_name varchar(255) DEFAULT NULL,
partner_code varchar(45) DEFAULT NULL,
url text DEFAULT NULL,
price varchar(10) DEFAULT NULL,
last_updated datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
PRIMARY KEY (productid),
KEY ean (ean)
) $charset_collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	$dd = dbDelta( $compare_sql );
	//$wpdb->query( "ALTER TABLE $compare_table_name ADD IF NOT EXISTS INDEX( `ean`);");
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

/**
 * Delete table on plugin deletion
 */
function compare_uninstall() {
	global $wpdb;
	$options = get_option( 'compare-premium' );
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
/**
 * @param $schedules array array with existing WP Schedule
 *
 * @return $schedules array
 */
function compare_sechule4_hours( $schedules ) {

	// add a 'weekly' schedule to the existing set
	$schedules['fourhour'] = array(
		'interval' => 14400,
		'display'  => __( 'Every 4 hours', 'compare-affiliated-products' )
	);

	return $schedules;
}

add_action( 'admin_print_styles', 'compare_admin_style', 11 );
/**
 * Load Style for Admin
 */
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

/**
 * Load Script to make beautiful responsive tables
 */

add_action( 'wp_enqueue_scripts', 'responsive_tables_enqueue_script' );
function responsive_tables_enqueue_script() {
	wp_enqueue_script( 'responsive-tables', get_stylesheet_directory_uri() . '/responsive-tables.js', $deps = array(), $ver = false, $in_footer = true );
}

add_action( 'wp_enqueue_scripts', 'cap_load_popup' );
function cap_load_popup() {
	wp_enqueue_script( 'popup', COMPARE_PLUGIN_URL . '/assets/js/popup.js', '', '1.0.0', true );
}

/**
 * @description Add a supported param in AAWP Shortcoded to allow alter it
 * @param $supported array list of supported Shortcode Params
 * @param $type array type of params
 *
 * @return mixed
 */

add_filter( 'aawp_func_supported_attributes', 'cap_supported', 20,2);
function cap_supported( $supported, $type ) {
	array_push( $supported, 'partners' );
	return $supported;
}

add_action( 'admin_enqueue_scripts', 'cap_load_admin_style' );
function cap_load_admin_style() {
	wp_enqueue_style( 'cap_admin_style', COMPARE_PLUGIN_URL . 'admin/assets/css/admin-style.css', '', '1.0' );
}
