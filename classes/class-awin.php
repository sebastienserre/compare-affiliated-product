<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

/**
 * Class Awin
 */
class Awin {

	protected $awin;
	protected $_option;
	protected $queue;
	protected $queue_register;

	/**
	 * Awin constructor.
	 */
	public function __construct() {
		add_action( 'compare_fourhour_event', array( $this, 'compare_set_cron' ) );
		add_action( 'compare_twice_event', array( $this, 'compare_set_cron' ) );
		add_action( 'compare_daily_event', array( $this, 'compare_set_cron' ) );
		$this->awin = get_option( 'awin' );
		//$this->queue = new DlBackground_Process();
		//$this->queue_register = new register_background_process();
	}

	public function compare_set_option(){
		$this->_option = get_option( 'compare-general' );
	}

	public function compare_set_cron() {
		$cron   = $this->_option['cron'];
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
		$mkdir = wp_mkdir_p( $dir['path'] . '/xml' );
		if ( ! $mkdir ) {
			wp_mkdir_p( $dir['path'] . '/xml' );
		}
		$dir =
			array(
				'path'   => $dir['path'] . '/xml',
				'url'    => $dir['url'] . '/xml',
				'subdir' => $dir['path'] . '/xml',
			) + $dir;

		return $dir;
	}

	public static function compare_get_awin_partners( $partner_code = '' ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$url          = 'https://productdata.awin.com/datafeed/list/apikey/' . $this->awin['apikey'];
		$temp_file    = download_url( $url, 300 );
		$csv          = file_get_contents( $url );
		$array        = array_map( "str_getcsv", explode( "\n", $csv ) );
		$array_1      = array_shift( $array );

		if ( empty( $partner_code ) ) {
			$partners = explode( ',', $this->awin['partner'] );
			$results  = array();
			foreach ( $partners as $partner ) {
				foreach ( $array as $a ) {
					if ( $a[3] === 'active' ) {
						$search = array_search( $partner, $a );
						if ( false !== $search ) {
							$results[ $partner ] = $a[1];
						}
					}
				}

			}
		} else {
			foreach ( $array as $a ) {
				if ( $a[3] === 'active' ) {
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
	public static function compare_schedule_awin() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$this->compare_get_awin_partners();
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
			$this->queue->push_to_queue( [ 'key' => $key, 'url' => $url ] );
		}
		$this->queue->dispatch();
		error_log( 'Stop Download Feed' );
		remove_filter( 'upload_dir', array( $this, 'compare_upload_dir' ) );

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
		global $wpdb;
		$table = $wpdb->prefix . 'compare';

		$truncat = $wpdb->query( 'DELETE FROM ' . $table . ' WHERE `platform` LIKE "Awin"' );

		$partners = $this->awin['partner'];
		$partners = explode( ',', $partners );

		$customer_id = $this->awin['customer_id'];
		$path        = wp_upload_dir();
		$secondes    = apply_filters( 'compare_time_limit', 600 );
		set_time_limit( $secondes );
		foreach ( $partners as $key => $value ) {
			$this->queue_register->push_to_queue( [ 'key' => $key, 'url' => $url ] );
		}
		$this->queue_register->dispatch();

		$event = 'import complete';
		error_log( $event );
	}


	public function compare_reset_awin_datafeed() {
			$this->compare_schedule_awin();
	}


}

new Awin();
