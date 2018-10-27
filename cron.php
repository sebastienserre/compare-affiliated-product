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


/**
 * Create a file with the date to avoid launching cron twice
 */
function cap_create_pid() {

	$date = date( 'd F Y @ H\hi:s' );
	$file = fopen( 'compare.txt', 'w+' );
	fwrite( $file, $date );
	fclose( $file );
}

function cap_delete_pid() {
	if ( file_exists( 'compare.txt' ) ) {
		unlink( 'compare.txt' );
	}
}

if ( ! file_exists( 'compare.txt' ) ) {
	error_log('file don\'t exist');
	cap_create_pid();
	error_log('file created');
	$awin = new Awin();
	$awin->compare_schedule_awin();
	$effiliation = new Effiliation();
	$effiliation->compare_schedule_effiliation();
	cap_delete_pid();
	error_log('file deleted');
} else {
	exit;
}
