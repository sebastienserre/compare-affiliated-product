<?php
/**
 * Include wp-load only if triggered by cli
 */
if( 'cli' === php_sapi_name() ) {

	function find_wordpress_base_path() {
		$dir = dirname( __FILE__ );
		do {
			//it is possible to check for other files here
			if ( file_exists( $dir . "/wp-config.php" ) ) {
				return $dir;
			}
		} while ( $dir = realpath( "$dir/.." ) );

		return null;
	}

	define( 'BASE_PATH', find_wordpress_base_path() . "/" );
	define( 'WP_USE_THEMES', false );
	global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
	require BASE_PATH . 'wp-load.php';
} elseif ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.


/**
 * Class Awin
 */
class Awin {

	protected $awin;
	protected $_option;
	protected $queue_register;

	/**
	 * Awin constructor.
	 */
	public function __construct() {
		add_action( 'compare_fourhour_event', array( $this, 'compare_set_cron' ) );
		add_action( 'compare_twice_event', array( $this, 'compare_set_cron' ) );
		add_action( 'compare_daily_event', array( $this, 'compare_set_cron' ) );
		$this->queue_register = new register_background_process();
		$this->awin           = get_option( 'awin' );
		$this->_option        = get_option( 'compare-general' );

		if( 'cli' === php_sapi_name() ) {
			$this->compare_schedule_awin();
		}

	}

	public function compare_set_option() {
		$this->_option = get_option( 'compare-general' );
	}

	public function compare_set_cron() {
		$cron = $this->_option['cron'];
		if ( ! isset( $this->_option['platform']['awin'] ) ) {
			return;
		}
		switch ( $cron ) {
			case 'four':
				$this->compare_schedule_awin();
				break;
			case 'twice':
				$this->compare_schedule_awin();
				break;
			case 'daily':
				$this->compare_schedule_awin();
				break;
			case 'none':
				__return_false();
				break;
		}

	}

	/**
	 * @param string $dir wp upload dir
	 *
	 * @return array $dir new wp upload dir
	 */
	public function compare_upload_dir( $dir ) {
		$mkdir = wp_mkdir_p( $dir['path'] . '/awin/xml' );
		if ( ! $mkdir ) {
			wp_mkdir_p( $dir['path'] . '/awin/xml' );
		}
		$dir =
			array(
				'path'   => $dir['path'] . '/awin/xml',
				'url'    => $dir['url'] . '/awin/xml',
				'subdir' => $dir['path'] . '/awin/xml',
			) + $dir;

		return $dir;
	}

	public function compare_get_awin_partners( $partner_code = '' ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$url       = 'https://productdata.awin.com/datafeed/list/apikey/' . $this->awin['apikey'];
		$temp_file = download_url( $url, 300 );
		$csv       = file_get_contents( $url );
		$array     = array_map( "str_getcsv", explode( "\n", $csv ) );

		if ( empty( $partner_code ) ) {
			$partners = explode( ',', $this->awin['partner'] );
			$results  = array();
			foreach ( $partners as $partner ) {
				foreach ( $array as $a ) {
					if ( isset( $a[3] ) && $a[3] === 'active' ) {
						$search = array_search( $partner, $a );
						if ( false !== $search ) {
							$results[ $partner ] = $a[1];
						}
					}
				}

			}
		} else {
			foreach ( $array as $a ) {
				if ( isset( $a[3] ) && $a[3] === 'active' ) {
					$search = array_search( $partner_code, $a );
					if ( false !== $search ) {
						$results[ $partner_code ] = $a[1];
					}
				}
			}
		}

		return $results;

	}

	/**
	 * Download and unzip xml from Awin
	 */
	public function compare_schedule_awin() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		define( 'ALLOW_UNFILTERED_UPLOADS', true );

		$urls = $this->awin['datafeed'];

		add_filter( 'upload_dir', array( $this, 'compare_upload_dir' ) );

		$path = wp_upload_dir();
		if ( file_exists( $path['path'] ) && is_dir( $path['path'] ) ) {
			array_map( 'unlink', glob( $path['path'] . '/*' ) );
		}

		$secondes = apply_filters( 'compare_time_limit', 600 );
		set_time_limit( $secondes );
		error_log( 'Start Download Feed' );

		foreach ( $urls as $key => $url ) {
			$temp_file = download_url( $url, 300 );
			if ( ! is_wp_error( $temp_file ) ) {
				// Array based on $_FILE as seen in PHP file uploads
				$file = array(
					//'name'     => basename($url), // ex: wp-header-logo.png
					'name'     => $this->awin['customer_id'] . '-' . $key . '.gz', // ex: wp-header-logo.png
					'type'     => 'application/gzip',
					'tmp_name' => $temp_file,
					'error'    => 0,
					'size'     => filesize( $temp_file ),
				);

				$overrides = array(
					'test_form' => false,
					'test_size' => true,
				);

				// Move the temporary file into the uploads directory
				$results = wp_handle_sideload( $file, $overrides );


			}
		}
		error_log( 'Stop Download Feed' );
		remove_filter( 'upload_dir', array( $this, 'compare_upload_dir' ) );
		$this->compare_register_prod();
	}


	public function compare_awin_data( $product_id ) {
		$path = wp_upload_dir();
		$xml  = $path['path'] . '/xml/datafeed_' . $this->awin['customer_id'] . '.xml';

		if ( file_exists( $xml ) ) {
			$xml = simplexml_load_file( $xml );
		}
	}

	/**
	 * Register in database product from xml files
	 */
	public function compare_register_prod() {
		error_log( 'start Import' );

		//$truncat = $wpdb->query( 'DELETE FROM ' . $table . ' WHERE `platform` LIKE "Awin"' );

		$partners = $this->awin['partner'];
		$partners = explode( ',', $partners );

		$secondes = apply_filters( 'compare_time_limit', 600 );
		set_time_limit( $secondes );
		$awin        = get_option( 'awin' );
		$customer_id = $awin['customer_id'];


		foreach ( $partners as $key => $value ) {
			$event = 'start partner ' . $value;
			error_log( $event );
			error_log( memory_get_usage() );

			$this->queue_register->push_to_queue(
				array(
					'key'         => $key,
					'value'       => $value,
					'customer_id' => $customer_id,
				)
			);

			$this->queue_register->save();
			$this->queue_register->dispatch();

			$path            = null;
			$upload          = null;
			$partner_details = null;

			error_log( memory_get_usage() );
			$event = 'stop partner ' . $value;
			error_log( $event );

		}

		$event = 'import complete';
		error_log( $event );

	}


	public function compare_reset_awin_datafeed() {
		$this->compare_schedule_awin();
	}


}

new Awin();
