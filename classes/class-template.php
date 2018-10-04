<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

class template {

	function __construct() {
		add_action( 'thfo_compare_after_price', array( $this, 'compare_display_price' ) );
		add_action( 'admin_init', array( $this, 'compare_get_partner_logo' ) );
	}

	/**
	 * Get All EAN Code attached to the ASIN
	 *
	 * @param array $data array of data about displayed product.
	 */
	public function compare_display_price( $data ) {
		$asin   = $data->get_product_id();
		$params = array(
			'Operation'     => 'ItemLookup',
			'ItemId'        => $asin,
			'ResponseGroup' => 'ItemAttributes',
		);

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
		$this->compare_display_html( $eanlist, $data );
	}

	public function compare_display_html( $eanlist, $data ) {
		$prods            = $this->compare_get_data( $eanlist );
		$partner_logo_url = get_option( 'awin' );
		$partner_logo_url = $partner_logo_url['partner_logo'];
		ob_start();
		?>
		<?php
		if ( ! is_null( $prods ) ) {
			foreach ( $prods as $p ) {
				$partner = apply_filters( 'compare_partner_name', $p['partner_name'] );
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
				$general  = get_option( 'general' );
				$currency = $general['currency'];
				$currency = apply_filters( 'compare_currency_unit', $currency );
				$option   = get_option( 'compare-aawp' );
				$text     = $option['button_text'];
				if ( empty( $text ) ) {
					$text = __( 'Buy to ', 'compare' );
				}
				$bg = $option['button-bg'];
				if ( empty( $bg ) ) {
					$bg = '#000000';
				}
				$color = $option['button-color'];
				if ( empty( $color ) ) {
					$color = '#ffffff';
				}
				if ( 'on' === $general['general-cloack'] ) {
					$link = new Cloak_Link();
					?>

					<?php
					$link->compare_create_link( $p, $logo, $data );
					?>

					<?php
				} else {
					?>
					<p class=" compare-price">
						<a href="<?php echo $p['url']; ?>"><?php echo $logo . ' ' . $p['price'] . ' ' . $currency; ?></a>
					</p>

					<?php
				}
			}
		}
		$html = ob_get_clean();
		echo $html;
	}

	public function compare_get_data( $eanlist ) {
		if ( ! is_array( $eanlist ) ) {
			$eanlist = array( $eanlist );
		}

		$transient = get_transient( 'product_' . $eanlist[0] );
		if ( ! empty( $transient ) ) {
			return $transient;
		}
		$external = get_option( 'general' );
		$external = $external['ext_check'];
		if ( 'on' === $external ) {
			$db = new compare_external_db();
			if ( is_wp_error( $db ) ) {
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
			} else {
				$prefix = get_option( 'general' );
				$prefix = $prefix['prefix'];

				$db       = new compare_external_db();
				$cnx      = $db->compare_external_cnx();
				$table    = $prefix . 'compare';
				$products = array();
				if ( null !== $eanlist[0] ) {
					foreach ( $eanlist as $list ) {
						$product = $cnx->get_results( $cnx->prepare( 'SELECT * FROM ' . $table . ' WHERE ean = %s ORDER BY `price` ASC', $list ), ARRAY_A );

						if ( ! empty( $product ) ) {
							array_push( $products, $product );
						}
					}
				}

			}
		} else {
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
		}

		if ( !empty( $products ) ) {
			$products  = array_reverse( $products[0] );
			$products  = array_combine( array_column( $products, 'partner_name' ), $products );
			$products  = array_reverse( $products );
			$transient = set_transient( 'product_' . $eanlist[0], $products, 4 * HOUR_IN_SECONDS );

			return $products;
		}
	}

	public static function compare_get_partner_logo() {
		$awin = get_option( 'awin' );
		foreach ( $awin['partner_logo'] as $key => $img ){
			$logos[$key] = $img['img'];
		}

		$effi = get_option('compare-effiliation');
		$programs = Effiliation::compare_get_effiliation_program();
		foreach ( $programs['programs'] as $program ){
			$logos[$program['id_programme']] = $program['urllo'];
		}
		return $logos;
	}

	protected function clear_others_pending_posts_transient( $ean ) {
		global $wpdb;

		$others_pending_posts_transients = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options}
                WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_dff_others_pending_posts_' ) . '%'
			)
		);

		foreach ( $others_pending_posts_transients as $transient ) {
			$transient_name = str_replace( '_transient_', '', $transient );
			delete_transient( $transient_name );
		}
	}

}

new template();