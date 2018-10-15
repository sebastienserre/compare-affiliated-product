<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

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
		add_action( 'compare_fourhour_event', array( $this, 'compare_effiliation_set_cron' ) );
		add_action( 'compare_twice_event', array( $this, 'compare_effiliation_set_cron' ) );
		add_action( 'compare_daily_event', array( $this, 'compare_effiliation_set_cron' ) );

		//add_action( 'admin_init', array($this, 'compare_effiliation_register'));
	}

	public function compare_reset_effiliation_feed() {
			$this->compare_schedule_effiliation();
	}

	public function compare_effiliation_set_cron() {
		$option = get_option( 'compare-general' );
		$cron   = $option['cron'];
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
				$img = update_option("compare-general[partner_logo][$prog[id_programme]]['img']", $prog['urllo'] );
				$name = update_option("compare-general[partner_logo][$prog[id_programme]]['name']", $prog['siteannonceur'] );
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
		<p><?php _e( 'Check the program to display', 'compare' ); ?></p>
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
		$mkdir = wp_mkdir_p( $dir['path'] . '/effiliation/xml' );
		if ( ! $mkdir ) {
			wp_mkdir_p( $dir['path'] . '/effiliation/xml' );
		}
		$dir =
			array(
				'path'   => $dir['path'] . '/effiliation/xml',
				'url'    => $dir['url'] . '/effiliation/xml',
				'subdir' => $dir['path'] . '/effiliation/xml',
			) + $dir;

		return $dir;
	}

	/**
	 * Download and unzip xml from Effiliation
	 */
	public function compare_schedule_effiliation() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		add_filter( 'upload_dir', array( $this, 'compare_upload_effiliation_dir' ) );
		define( 'ALLOW_UNFILTERED_UPLOADS', true );

		$urls = self::compare_effiliation_get_feed();
		$path = wp_upload_dir();
		$path = $path['path'];
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
				// Array based on $_FILE as seen in PHP file uploads
				$file = array(
					'name'     => $key . '.gz', // ex: wp-header-logo.png
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

			} else {
				error_log( $temp_file->errors['http_request_failed'][0] );
			}
		}
		error_log( 'Stop Download Effiliation Feed' );
		remove_filter( 'upload_dir', array( $this, 'compare_upload_dir' ) );
		$this->compare_effiliation_register();
	}

	public function compare_effiliation_register() {
		global $wpdb;
		$programs = self::compare_get_effiliation_program();
		error_log( 'start Effiliation Import' );
		$table = $wpdb->prefix . 'compare';

		$truncat  = $wpdb->query( 'DELETE FROM ' . $table . ' WHERE `platform` LIKE "effiliation"' );
		$path     = wp_upload_dir();
		$secondes = apply_filters( 'compare_time_limit', 600 );
		set_time_limit( $secondes );
		foreach ( $programs['programs'] as $program ) {
			$event = 'start partner ' . $program['siteannonceur'];
			error_log( $event );
			$upload  = $path['path'] . '/' . $program['siteannonceur'] . '.gz';
			$new_xml = file_get_contents( 'compress.zlib://' . $upload );
			//$xml = new SimpleXMLElement();
			libxml_use_internal_errors( false );
			$element = simplexml_load_string( $new_xml, 'SimpleXMLElement' );

			libxml_clear_errors();

			foreach ( $element->product as $prod ) {
				$prod = array(
					'price'        => strval( $prod->price ),
					'title'        => strval( $prod->name ),
					'description'  => strval( $prod->description ),
					'img'          => strval( $prod->url_image ),
					'url'          => strval( $prod->url_product ),
					'partner_name' => $program['siteannonceur'],
					'partner_code' => $program['id_programme'],
					'productid'    => strval( $prod->sku ),
					'ean'          => strval( $prod->ean ),
					'platform'     => 'effiliation',
				);

				$wpdb->show_errors( true );
				$wpdb->show_errors(true);
				$insert =$wpdb->replace( $table, $prod );
				$transient = get_transient( 'product_' . strval( $prod['ean'] ) );
				if (! empty( $transient ) ){
					delete_transient( $transient );
				}
			}
		}
		error_log( 'stop Effiliation Import' );
	}

}

new Effiliation();