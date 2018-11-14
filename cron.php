<?php
error_log('start cron');
// Detect wp-config location
// Inspiration : http://boiteaweb.fr/wordpress-bootstraps-ou-comment-bien-charger-wordpress-6717.html
$wp_location = 'wp-load.php';
while ( ! is_file( $wp_location ) ) {
    if ( is_dir( '..' ) ) {
        chdir( '..' );
    } else {
        die( '-9' ); // Config file not exist, stop script
    }
}

include( $wp_location );

// Constant are defined ? WP & Plugin is loaded ?
if ( ! defined( 'DB_NAME' ) ) {
    die( '-8' );
}

if ( ! file_exists( 'compare.txt' ) ) {
	$awin = new Awin();
	$awin->compare_schedule_awin();
	$effiliation = new Effiliation();
	$effiliation->compare_schedule_effiliation();

} else {
	exit;
}
