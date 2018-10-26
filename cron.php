<?php
/**
 * Include wp-load only if triggered by cli
 */


https://wordpressfr.slack.com/archives/C0538Q46U/p1540562802031400
// Fake WordPress, build server array
$_SERVER = array(
        'HTTP_HOST'       => $domain,
        'SERVER_NAME'     => $domain,
        'REQUEST_URI'     => basename( __FILE__ ),
        'REQUEST_METHOD'  => 'GET',
        'SCRIPT_NAME'     => basename( __FILE__ ),
        'SCRIPT_FILENAME' => basename( __FILE__ ),
        'PHP_SELF'        => basename( __FILE__ )
);


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
 * Create a file with the pid number to avoid launching cron twice
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
	cap_create_pid();
	$awin = new Awin();
	$awin->compare_schedule_awin();
	cap_delete_pid();
} else {
	exit;
}
