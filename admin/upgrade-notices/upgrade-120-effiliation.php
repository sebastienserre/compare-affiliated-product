<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

add_action( 'upgrader_process_complete', 'compare_upgrade_120_completed', 10, 2 );
function compare_upgrade_120_completed( $upgrader_object, $options ) {
	$plugin  = COMPARE_PLUGIN_BASENAME;
	$version = update_option( 'compare_version', COMPARE_VERSION );
	if ( $options['action'] == 'update' && $options['type'] == 'plugin' && isset( $options['plugins'] ) && ( '1.2.0' === $version ) ) {
		foreach ( $options['plugins'] as $plugin ) {
			if ( $plugin === $plugin ) {
				set_transient( 'compare_120_updated', 1 );
			}
		}
	}
}

add_action( 'admin_notices', 'compare_120_notice' );
function compare_120_notice() {
	if ( get_transient( 1 === 'compare_120_updated' ) ) {
		$url = admin_url();
		$url = add_query_arg( 'compare_upgrade_db', 'true', $url );
		?>
		<div class="notice notice-warning">
			<p><?php printf( _x( 'Thanks for updating %s', 'Plugin\'s name', 'compare' ), COMPARE_PLUGIN_NAME ); ?></p>
			<p><?php _e( 'Please follow the link to upgrade the database', 'compare' ); ?></p>
			<p><a href="<?php echo esc_url( $url ) ?>"><?php _e( 'Click to Update', 'compare' ); ?></a></p>
		</div>
		<?php
	}
}

add_action( 'admin_init', 'compare_120_db_upgrade' );
function compare_120_db_upgrade() {
	if ( isset( $_GET['compare_upgrade_db'] ) && $_GET['compare_upgrade_db'] === 'true' ) {
		global $wpdb;
		$table = $wpdb->prefix . 'compare';
		$wpdb->query( 'ALTER TABLE ' . $table . ' MODIFY title text' );
		$wpdb->query( 'ALTER TABLE ' . $table . ' MODIFY partner_name varchar(255)' );
		$wpdb->query( 'ALTER TABLE ' . $table . ' MODIFY productid varchar(255)' );
		$wpdb->query( 'ALTER TABLE ' . $table . ' MODIFY url text' );
		$wpdb->query( 'ALTER TABLE ' . $table . ' ADD COLUMN partner_code varchar(45) AFTER partner_name');

		delete_transient( 'compare_120_updated' );
	}
}