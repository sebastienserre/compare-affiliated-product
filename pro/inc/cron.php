<?php

if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::add_command( 'cap_import_db', 'cap_upgrade_db' );
}

function cap_select_cron() {

	$premium = get_option( 'compare-premium' );
	$cron    = $premium['cron'];
	switch ( $cron ) {
		case 'four':
			add_action( 'compare_fourhour_event', 'cap_upgrade_db' );
			break;
		case 'twice':
			add_action( 'compare_twice_event', 'cap_upgrade_db' );
			break;
		case 'daily':
			add_action( 'compare_daily_event', 'cap_upgrade_db' );
			break;
		case 'none':
			__return_false();
			break;
	}
}

add_action( 'admin_init', 'cap_launch_cron_setting' );
function cap_launch_cron_setting() {
	$nonce = $_REQUEST['_wpnonce'];
	if ( ! empty( $_GET['launch_cron'] ) && 'ok' === $_GET['launch_cron'] && wp_verify_nonce( $nonce, 'cap-launch-cron' ) ) {
		cap_upgrade_db();
	}
}

function cap_upgrade_db() {
	error_log( 'start cron' );

	$option = get_option( 'compare-premium' );
	if ( ! file_exists( 'compare.txt' ) ) {
		cap_create_pid();

		foreach ( $option['platform'] as $platform ) {
			if ( 'awin' === $platform ) {
				$awin = new Awin();
				$awin->compare_schedule_awin();
			}

			if ( 'effiliation' === $platform ) {
				$effiliation = new Effiliation();
				$effiliation->compare_schedule_effiliation();
			}
		}
		cap_delete_pid();

	} else {
		exit;
	}
	error_log( 'stop cron' );
}
