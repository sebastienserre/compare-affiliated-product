<?php

if ( class_exists( 'WP_CLI' ) ){
	WP_CLI::add_command('cap_import_db', 'cap_upgrade_db' );
}

function cap_upgrade_db() {
	error_log( 'start cron' );

	if ( ! file_exists( 'compare.txt' ) ) {
		cap_create_pid();
		$awin = new Awin();
		$awin->compare_schedule_awin();
		$effiliation = new Effiliation();
		$effiliation->compare_schedule_effiliation();
		cap_delete_pid();

	} else {
		exit;
	}
	error_log( 'stop cron' );
}
