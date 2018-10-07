<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

/**
 * Class Awin
 */
class Awin {

	protected $awin;

	/**
	 * Awin constructor.
	 */
	public function __construct() {
		add_action( 'compare_fourhour_event', array( $this, 'compare_set_cron' ) );
		add_action( 'compare_twice_event', array( $this, 'compare_set_cron' ) );
		add_action( 'compare_daily_event', array( $this, 'compare_set_cron' ) );
		$this->awin = get_option( 'awin' );
	}

	public function compare_set_cron() {
		$option = get_option( 'compare-general' );
		$cron   = $option['cron'];
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

	public function compare_get_awin_partners( $partner_code = '' ) {
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
	public function compare_schedule_awin() {
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
			$partner_details = $this->compare_get_awin_partners( $value );
			foreach ( $partner_details as $partner_detail){
				$partner_details = $partner_detail;
			}
			$event = 'start partner ' . $value;
			error_log( $event );
			$upload = $path['path'] . '/xml/' . $customer_id . '-' . $value . '.gz';

			$xml = new XMLReader();
			$xml->open( 'compress.zlib://' . $upload );
			$xml->read();

			while ( $xml->read() && 'prod' !== $xml->name ) {
				;
			}

			while ( 'prod' === $xml->name ) {
				$element       = new SimpleXMLElement( $xml->readOuterXML() );
				$code_partners = explode( 'feedId=', $element->uri->awImage );
				$code_partners = explode( '&k=', $code_partners[1] );
				$code_partners = $code_partners[0];

				$prod = array(
					'price'        => strval( $element->price->buynow ),
					'title'        => $element->text->name ? strval( $element->text->name ) : '',
					'description'  => strval( $element->text->desc ),
					'img'          => strval( $element->uri->mImage ),
					'url'          => strval( $element->uri->awTrack ),
					'partner_name' => $partner_details,
					'productid'    => strval( $xml->getAttribute( 'id' ) ),
					'ean'          => strval( $element->ean ),
					'platform'     => 'Awin',
					'partner_code' => $code_partners,
				);

				//$wpdb->show_errors();
				$insert = $wpdb->insert( $table, $prod );
				//error_log( $wpdb->print_error() );

				$xml->next( 'prod' );
			}
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
