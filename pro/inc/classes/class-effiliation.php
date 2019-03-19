<?php

if ( class_exists( 'WP_CLI' ) ){
	WP_CLI::add_command('cap_import_effiliation', 'Effiliation' );
}
/**
 * Class effiliation
 *
 * @since 1.2.0
 * https://publisher.effiliation.com/
 */
class Effiliation {

	/**
	 * @var string Apikey to retrieve in Effiliation.com settings page.
	 */
	protected static $apikey;

	public function __construct() {

	}

	public function compare_reset_effiliation_feed() {
		$this->compare_schedule_effiliation();
	}

	public function compare_effiliation_set_cron() {
		_deprecated_function( __FUNCTION__, '2.0.11' );
		if (file_exists( COMPARE_PLUGIN_PATH . '/compare.txt')){
			return;
		} else {
			cap_create_pid();
		}

		$option = get_option( 'compare-premium' );
		if ( ! isset( $option['platform']['effiliation'] ) ) {
			return;
		}
		$cron = $option['cron'];
		switch ( $cron ) {
			case 'four':
				$this->compare_schedule_effiliation();
				break;
			case 'twice':
				$this->compare_schedule_effiliation();
				break;
			case 'daily':
				$this->compare_schedule_effiliation();
				break;
			case 'none':
				break;
		}

	}

	/**
	 * @return mixed apikey for effiliation account.
	 */
	private static function compare_get_apikey() {
		$option       = get_option( 'compare-effiliation' );
		self::$apikey = $option['apikey'];

		return self::$apikey;
	}

	/**
	 * @return array array with Effiliation program datas.
	 */
	public static function compare_get_effiliation_program() {
		$apikey   = self::compare_get_apikey();
		$url      = 'https://apiv2.effiliation.com/apiv2/programs.json?key=' . $apikey . '&filter=mines';
		$response = wp_remote_get( $url, array( 'sslverify' => false, ) );
		if ( 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$body         = wp_remote_retrieve_body( $response );
			$decoded_body = json_decode( $body, true );
		}

		return $decoded_body;
	}

	public static function compare_effiliation_list_html() {
		$programs = self::compare_get_effiliation_program();
		$options  = get_option( 'compare-effiliation' );


		foreach ( $programs as $program ) {
			foreach ( $program as $prog ) {
				if ( isset( $options['programs'] ) && ! empty( $options['programs'] ) ) {
					$check = $options['programs'][ $prog['siteannonceur'] ];
				}
				$img  = update_option( "compare-general[partner_logo][$prog[id_programme]]['img']", $prog['urllo'] );
				$name = update_option( "compare-general[partner_logo][$prog[id_programme]]['name']", $prog['siteannonceur'] );
				?>
				<p>
					<input type="checkbox" value="<?php echo $prog['siteannonceur']; ?>"
					       name="compare-effiliation[programs][<?php echo $prog['siteannonceur'] ?>]" <?php checked( $check, $prog['siteannonceur'] ) ?>>
					<label for="compare-effiliation[programs][<?php $prog['siteannonceur'] ?>]">
						<img src="<?php echo $prog['urllo']; ?>" alt="<?php echo $prog['siteannonceur']; ?>"
						     title="<?php echo $prog['siteannonceur']; ?>">
					</label>
				</p>
				<?php
			}
		}
		?>
		<p><?php _e( 'Check the program to display', 'compare-affiliated-products-pro' ); ?></p>
		<?php
	}

	public static function compare_effiliation_get_feed() {
		$apikey   = self::compare_get_apikey();
		$url      = 'https://apiv2.effiliation.com/apiv2/productfeeds.json?key=' . $apikey . '&filter=mines';
		$response = wp_remote_get( $url, array( 'sslverify' => false, ) );
		if ( 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$body         = wp_remote_retrieve_body( $response );
			$decoded_body = json_decode( $body, true );
		}

		foreach ( $decoded_body['feeds'] as $feeds ) {
			$urls[ $feeds['site_affilieur'] ] = $feeds['code'];
		}

		return $urls;
	}

	/**
	 * @param string $dir wp upload dir
	 *
	 * @return array $dir new wp upload dir
	 */
	public function compare_upload_effiliation_dir( $dir ) {
		$mkdir = wp_mkdir_p( $dir['path'] . '/xml/effiliation' );
		if ( ! $mkdir ) {
			wp_mkdir_p( $dir['path'] . '/xml/effiliation' );
		}
		$dir =
			array(
				'path'   => $dir['path'] . '/xml/effiliation',
				'url'    => $dir['url'] . '/xml/effiliation',
				'subdir' => $dir['path'] . '/xml/effiliation',
			);

		return $dir;
	}

	/**
	 * Download and unzip xml from Effiliaiton
	 **/
	public function compare_schedule_effiliation() {
		$option = get_option( 'compare-premium' );

		if ( ! isset( $option['platform']['effiliation'] ) ) {
			return;
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		add_filter( 'upload_dir', array( $this, 'compare_upload_effiliation_dir' ) );
		define( 'ALLOW_UNFILTERED_UPLOADS', true );

		$urls = self::compare_effiliation_get_feed();
		$path = COMPARE_XML_PATH . 'effiliation/';
		if ( file_exists( $path ) && is_dir( $path ) ) {
			array_map( 'unlink', glob( $path . '/*' ) );
		} else {
			wp_mkdir_p( $path );
		}

		$secondes = apply_filters( 'compare_time_limit', 600 );
		set_time_limit( $secondes );
		error_log( 'Start Download Effiliation Feed' );
		foreach ( $urls as $key => $url ) {
			$temp_file = download_url( $url, 300 );
			if ( ! is_wp_error( $temp_file ) ) {

				$name    = $key . '.gz';
				$results = rename( $temp_file, $path . $name );
				if ( file_exists( $temp_file ) ){
					unlink( $temp_file );
				}

			} else {
				error_log( $temp_file->errors['http_request_failed'][0] );
			}
		}
		error_log( 'Stop Download Effiliation Feed' );
		$this->compare_effiliation_register();
	}

	public function compare_effiliation_register() {
		global $wpdb;
		$programs = self::compare_get_effiliation_program();
		error_log( 'start Effiliation Import' );
		$table = $wpdb->prefix . 'compare';

		$path = COMPARE_XML_PATH . 'effiliation/';
		$secondes = apply_filters( 'compare_time_limit', 600 );
		set_time_limit( $secondes );
		foreach ( $programs['programs'] as $program ) {
			$event = 'start partner ' . $program['siteannonceur'];
			error_log( $event );
			$upload  = $path . $program['siteannonceur'] . '.gz';
			$new_xml = file_get_contents( 'compress.zlib://' . $upload );
			//$xml = new SimpleXMLElement();
			libxml_use_internal_errors( false );
			$element = simplexml_load_string( $new_xml, 'SimpleXMLElement' );

			libxml_clear_errors();

			foreach ( $element->product as $prod ) {
				$ean = cap_format_ean( strval( $prod->ean ) );
				if ( ! empty( $ean ) ) {
					$prod = array(
						'price'        => strval( $prod->price ),
						'title'        => strval( $prod->name ),
						'description'  => strval( $prod->description ),
						'img'          => strval( $prod->url_image ),
						'url'          => strval( $prod->url_product ),
						'partner_name' => $program['siteannonceur'],
						'partner_code' => $program['id_programme'],
						'productid'    => strval( $prod->sku ),
						'ean'          => $ean,
						'platform'     => 'effiliation',
					);

					$wpdb->replace( $table, $prod );
					$transient = get_transient( 'product_' . $ean );
					if ( ! empty( $transient ) ) {
						delete_transient( $transient );
					}
					$transient = null;
				}
			}
		}
		cap_delete_pid();
		error_log( 'stop Effiliation Import' );
	}

}
