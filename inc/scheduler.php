<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.


/**
 * add Awin cron tasks
 */
$awin = new Awin();
add_action( 'compare_fourhour_event', array( $awin, 'compare_set_cron' ) );
add_action( 'compare_twice_event', array( $awin, 'compare_set_cron' ) );
add_action( 'compare_daily_event', array( $awin, 'compare_set_cron' ) );

/**
 * Add Effiliation Cron tasks
 */
$effiliation = new Effiliation();
add_action( 'compare_fourhour_event', array( $effiliation, 'compare_effiliation_set_cron' ) );
add_action( 'compare_twice_event', array( $effiliation, 'compare_effiliation_set_cron' ) );
add_action( 'compare_daily_event', array( $effiliation, 'compare_effiliation_set_cron' ) );
