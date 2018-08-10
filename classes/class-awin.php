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
		add_action( 'compare_daily_event', array( $this, 'compare_schedule_awin' ) );
		add_action( 'thfo_compare_after_price', array( $this, 'compare_display_price' ) );
	}

	/**
	 * @param string $dir wp upload dir
	 *
	 * @return array $dir new wp upload dir
	 */
	public function compare_upload_dir( $dir ) {
		$mkdir = wp_mkdir_p( $dir . '/xml' );
		if ( ! $mkdir ) {
			wp_mkdir_p( $dir . '/xml' );
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
		$awin = get_option( 'awin' );

		define( 'ALLOW_UNFILTERED_UPLOADS', true );

		$urls = $awin['datafeed'];

		add_filter( 'upload_dir', array( $this, 'compare_upload_dir' ) );

		$path = wp_upload_dir();
		if ( file_exists( $path['path'] ) && is_dir( $path['path'] ) ) {
			array_map( 'unlink', glob( $path['path'] . '/*' ) );
		}

		set_time_limit( 600 );
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
	 * Get All EAN Code attached to the ASIN
	 *
	 * @param array $data array of data about displayed product.
	 */
	public function compare_display_price( $data ) {
		//$style = $data->ge
		$asin   = $data->get_product_id();
		$params = array(
			'Operation'     => 'ItemLookup',
			'ItemId'        => $asin,
			'ResponseGroup' => 'ItemAttributes',
		);

		$gtin = $data->get_product_id(); // return the asin

		$apikey        = $data->api_key;
		$secret        = $data->api_secret_key;
		$associate_tag = $data->api_associate_tag;

		$asin2ean = aws_signed_request( 'fr', $params, $apikey, $secret, $associate_tag );

		$asin2ean = wp_remote_get( $asin2ean );
		$asin2ean = $asin2ean['body'];

		$amazon  = simplexml_load_string( $asin2ean );
		$json    = wp_json_encode( $amazon );
		$array   = json_decode( $json, true );
		$eanlist = $array['Items']['Item']['ItemAttributes']['EANList']['EANListElement'];
		if ( ! is_array( $eanlist ) ) {
			$eanlist = array( $eanlist );
		}
		$this->compare_display_html( $eanlist );
		//$this->compare_get_data( $eanlist );
	}

	public function compare_get_data( $eanlist ) {
		if ( !is_array( $eanlist ) ){
			$eanlist = array( $eanlist );
		}
		global $wpdb;
		$table    = $wpdb->prefix . 'compare';
		$products = array();

		if ( null !== $eanlist[0] ) {
			foreach ( $eanlist as $list ) {
				$product = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE ean = %s ORDER BY `price` ASC', $list ), ARRAY_A );

				if ( ! empty( $product ) ) {
					array_push( $products, $product );
				}
			}
		}

		$products = array_reverse( $products[0] );
		$products = array_combine( array_column( $products, 'partner_name' ), $products );
		$products = array_reverse( $products );


		return $products;
		//$this->compare_display_html( $products );
	}

	/**
	 * Register in database product from xml files
	 */
	public function compare_register_prod() {
		global $wpdb;
		$table = $wpdb->prefix . 'compare';

		$truncat = $wpdb->query( 'TRUNCATE TABLE ' . $table );

		$awin     = get_option( 'awin' );
		$partners = $awin['partner'];
		$partners = explode( ',', $partners );

		$customer_id = $awin['customer_id'];
		$path        = wp_upload_dir();
		set_time_limit( 600 );
		foreach ( $partners as $key => $value ) {
			$upload = $path['path'] . '/xml/' . $customer_id . '-' . $value . '.gz';

			$xml = new XMLReader();
			$xml->open( 'compress.zlib://' . $upload );
			$xml->read();

			while ( $xml->read() && $xml->name != 'prod' ) {
				;
			}

			while ( $xml->name === 'prod' ) {
				$element    = new SimpleXMLElement( $xml->readOuterXML() );
				$url_params = explode( '&m=', $element->uri->awTrack );
				$partners   = array(
					'Cdiscount'            => '6948',
					'Toy\'R us'            => '7108',
					'Oxybul eveil et jeux' => '7103',
					'Rue du Commerce'      => '6901',
					'Darty'                => '7735',
				);
				$partner    = array_search( $url_params[1], $partners );

				$prod = array(
					'price'        => strval( $element->price->buynow ),
					'title'         => $element->text->name ? strval( $element->text->name ) : '',
					'description'  => strval( $element->text->desc ),
					'img'          => strval( $element->uri->mImage ),
					'url'          => strval( $element->uri->awTrack ),
					'partner_name' => $partner,
					'productid'    => strval( $xml->getAttribute( 'id' ) ),
					'ean'          => strval( $element->ean ),
				);

				$wpdb->show_errors();
				$insert = $wpdb->insert( $table, $prod );


				$xml->next( 'prod' );
			}
		}
	}

	public function compare_display_html( $eanlist ) {
		$prods = $this->compare_get_data( $eanlist );
		$currency         = get_option( 'general' );
		$currency   =   $currency['currency'];
		$currency         = apply_filters( 'compare_currency_unit', $currency );
		$partner_logo_url = get_option( 'awin' );
		$partner_logo_url = $partner_logo_url['partner_logo'];
		ob_start();
		?>
		<div class="compare-partners">
			<?php
			foreach ( $prods as $p ) {
				$partner = apply_filters( 'compare_partner_name', $p['partner_name'] );
				//var_dump( $p );
				switch ( $p['partner_name'] ) {
					case 'Cdiscount':
						$logo = '<img class="compare_partner_logo" src="' . $partner_logo_url['15557'] . '" >';
						break;
					case 'Darty':
						$logo = '<img class="compare_partner_logo" src="' . $partner_logo_url['25905'] . '" >';
						break;
					case 'Rue du Commerce':
						$logo = '<img class="compare_partner_logo" src="' . $partner_logo_url['26507'] . '" >';
						break;
					default:
						$logo = $partner;
				}
				?>
				<p class="compare-price">
					<a class="compare-link"
					   href="<?php echo $p['url'] ?>"><?php echo $logo . ' ' . $p['price'] . ' ' . $currency ?></a>
				</p>
				<?php
			}
			?>
		</div>
		<?php
		$html = ob_get_clean();
		echo $html;
	}


}

new Awin();
