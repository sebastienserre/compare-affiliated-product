<?php
if ( class_exists( 'WP_CLI' ) ){
	WP_CLI::add_command('cap_import_awin', 'Awin' );
}

/**
 * Class Awin
 */
class Awin {

	protected $awin;
	protected $_option;
	protected $queue_register;
	protected $_premium;

	/**
	 * Awin constructor.
	 */
	public function __construct() {

		$this->awin           = get_option( 'awin' );
		$this->_option        = get_option( 'compare-general' );
		$this->premium  = get_option( 'compare-premium' );

		if ( isset( $_GET['compare-test'] ) && $_GET[ 'compare-test'] === 'ok' ){
			$this->compare_schedule_awin();
		}

	}

	public function compare_set_option() {
		$this->_option = get_option( 'compare-general' );
	}

	public function compare_set_premium(){
		$this->_premium = get_option( 'compare-premium' );
	}

	public function compare_set_cron() {
		_deprecated_function( __FUNCTION__, '2.0.11');

		if (file_exists( COMPARE_PLUGIN_PATH . '/compare.txt')){
			return;
		} else {
			cap_create_pid();
		}
		$cron = $this->_premium['cron'];
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

	public function compare_get_awin_partners( $partner_code = '' ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		$url       = 'https://productdata.awin.com/datafeed/list/apikey/' . $this->awin['apikey'];
		$temp_file = download_url( $url, 300 );
		//$csv       = file_get_contents( $url );
		$csv       = wp_remote_get( $url );
		if ( $csv ) {
			//$array = array_map( "str_getcsv", explode( '\n', $csv ) );
			$array = array_map( "str_getcsv", $csv );
		}
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
		$option = get_option( 'compare-premium' );
		if ( ! isset( $option['platform']['awin'] ) ) {
			return;
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		define( 'ALLOW_UNFILTERED_UPLOADS', true );

		$urls = $this->awin['datafeed'];

		$path = COMPARE_XML_PATH . 'awin/' ;
		if ( file_exists( $path ) && is_dir( $path ) ) {
			array_map( 'unlink', glob( $path . '/*' ) );
		} else {
			wp_mkdir_p( $path );
		}

		$secondes = apply_filters( 'compare_time_limit', 600 );

		error_log( 'Start Download Feed' );

		foreach ( $urls as $key => $url ) {
			set_time_limit( $secondes );
			$temp_file = download_url( $url, 300 );
			if ( ! is_wp_error( $temp_file ) ) {
				// Array based on $_FILE as seen in PHP file uploads
				$name = $this->awin['customer_id'] . '-' . $key . '.gz';
				$results = rename( $temp_file, $path . $name );
				if ( file_exists( $temp_file ) ){
					unlink( $temp_file );
				}
			}
		}
		error_log( 'Stop Download Feed' );
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

		$partners = $this->awin['partner'];
		$partners = explode( ',', $partners );

		$secondes = apply_filters( 'compare_time_limit', 6000 );

		$awin        = get_option( 'awin' );
		$customer_id = $awin['customer_id'];


		foreach ( $partners as $key => $value ) {

			$event = 'start partner ' . $value;
			error_log( $event );
			error_log( memory_get_usage() );


			global $wpdb;
			$table       = $wpdb->prefix . 'compare';
			$xml         = new XMLReader();

			$partner_details = $this->compare_get_awin_partners( $value );
			foreach ( $partner_details as $partner_detail ) {
				$partner_details = $partner_detail;
			}

			$path = COMPARE_XML_PATH . 'awin/' ;
			$upload = $path . $customer_id . '-' . $value . '.gz';


			$xml->open( 'compress.zlib://' . $upload );
			$xml->read();

			while ( $xml->read() && 'prod' !== $xml->name ) {
				;
			}

			while ( 'prod' === $xml->name ) {
				set_time_limit( $secondes );
				$element = new SimpleXMLElement( $xml->readOuterXML() );

				$ean = cap_format_ean( strval( $element->ean ) );

				if ( ! empty( $ean ) ) {
					$prod = array(
						'price'        => strval( $element->price->buynow ),
						'title'        => $element->text->name ? strval( $element->text->name ) : '',
						'description'  => strval( $element->text->desc ),
						'img'          => strval( $element->uri->mImage ),
						'url'          => strval( $element->uri->awTrack ),
						'partner_name' => $partner_details,
						'productid'    => strval( $xml->getAttribute( 'id' ) ),
						'ean'          => $ean,
						'platform'     => 'Awin',
						'partner_code' => $value,
						'mpn'   =>  strval( $element->mpn )
					);

					$wpdb->replace( $table, $prod );

					$transient = get_transient( 'product_' . $ean );

					if ( ! empty( $transient ) ) {
						delete_transient( $transient );
					}
				}
				$xml->next( 'prod' );


			}

			$path            = null;
			$upload          = null;
			$partner_details = null;

			error_log( memory_get_usage() );
			$event = 'stop partner ' . $value;
			error_log( $event );

		}

		$event = 'import complete';
		cap_delete_pid();

		error_log( $event );

	}


	public function compare_reset_awin_datafeed() {
		$this->compare_schedule_awin();
	}


}
