<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

/**
 * Class Awin
 */
class Awin {

	/**
	 * Awin constructor.
	 */
	public function __construct() {
		add_action( 'compare_fourhour_event', array( $this, 'compare_set_cron' ) );
		add_action( 'compare_twice_event', array( $this, 'compare_set_cron' ) );
		add_action( 'compare_daily_event', array( $this, 'compare_set_cron' ) );
	}

	public function compare_set_cron() {
		$option = get_option( 'general' );
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

	/**
	 * Download and unzip xml from Awin
	 */
	public function compare_schedule_awin() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$awin = get_option( 'awin' );

		define( 'ALLOW_UNFILTERED_UPLOADS', true );

		$urls = $awin['datafeed'];

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
					'name'     => $awin['customer_id'] . '-' . $key . '.gz', // ex: wp-header-logo.png
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
		$awin_options = get_option( 'awin' );
		$path         = wp_upload_dir();
		$xml          = $path['path'] . '/xml/datafeed_' . $awin_options['customer_id'] . '.xml';

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

		$awin     = get_option( 'awin' );
		$partners = $awin['partner'];
		$partners = explode( ',', $partners );

		$customer_id = $awin['customer_id'];
		$path        = wp_upload_dir();
		$secondes    = apply_filters( 'compare_time_limit', 600 );
		set_time_limit( $secondes );
		foreach ( $partners as $key => $value ) {
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
				$element    = new SimpleXMLElement( $xml->readOuterXML() );
				$url_params = explode( '&m=', $element->uri->awTrack );
				$partners   = apply_filters(
					'compare_partners_code',
					array(
						'Cdiscount'            => '6948',
						'Toy\'R us'            => '7108',
						'Oxybul eveil et jeux' => '7103',
						'Rue du Commerce'      => '6901',
						'Darty'                => '7735',
					)
				);
				$partner    = array_search( $url_params[1], $partners, true );

				$prod = array(
					'price'        => strval( $element->price->buynow ),
					'title'        => $element->text->name ? strval( $element->text->name ) : '',
					'description'  => strval( $element->text->desc ),
					'img'          => strval( $element->uri->mImage ),
					'url'          => strval( $element->uri->awTrack ),
					'partner_name' => $partner,
					'productid'    => strval( $xml->getAttribute( 'id' ) ),
					'ean'          => strval( $element->ean ),
					'platform'  =>  'Awin',
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

}

new Awin();
