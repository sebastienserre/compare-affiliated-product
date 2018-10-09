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
		if ( is_object( $data) ){
			$asin   = $data->get_product_id();
		} else {
			$asin = $data['asin'];
			$data = new AAWP_Template_Handler();
		}

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

	/**
	 * Provides HTML to display
	 * @param $eanlist Array of EAN13
	 * @param $data array of Product datas.
	 */
	public function compare_display_html( $eanlist, $data ) {
		$prods            = $this->compare_get_data( $eanlist );
		$partner_logo_url = get_option( 'awin' );
		$partner_logo_url = $partner_logo_url['partner_logo'];
		ob_start();
		?>
		<?php
		if ( ! is_null( $prods ) ) {
			$i = 1;
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
				$general  = get_option( 'compare-general' );
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
					<div class="compare-price-partner compare-price-partner-<?php echo $i; ?> compare-others">
					<?php
					$link->compare_create_link( $p, $logo, $data );
					?>
					</div>

					<?php
				} else {
					$logos = template::compare_get_partner_logo();

						if ( isset( $logos[ $p['partner_code'] ] ) ) {
							$logo = $logos[ $p['partner_code'] ];
						}
						$url = $p['url'];

					$currency = get_option( 'compare-general' );
					$currency = $currency['currency'];
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

					?>
					<div class="compare-price-partner compare-price-partner-<?php echo $i; ?> compare-others">
						<div class="img-partner"><img src="<?php echo $logo ?>"></div>
						<div class="product-price">
							<a href="<?php echo $p['url']; ?>">
							<?php echo $p['price'] . ' ' . $currency ?>
							</a>
						</div>
						<div class="button-partner">
							<button style=" background:<?php echo $bg; ?>; color: <?php echo $color; ?>; "><a class="btn-compare">
								<a href="<?php echo $p['url']; ?>"><?php echo $text; ?></a>
							</a>
							</button>
						</div>
					</div>


					<?php
				}
				$i++;
			}
		}
		$html = ob_get_clean();
		echo $html;
	}

	/**
	 * Provide an array of EAN then get data from partners
	 * @param $eanlist array
	 *
	 * @return array Array of products
	 */
	public function compare_get_data( $eanlist ) {
		if ( ! is_array( $eanlist ) ) {
			$eanlist = array( $eanlist );
		}

		$transient = get_transient( 'product_' . $eanlist[0] );
		if ( ! empty( $transient ) ) {
			return $transient;
		}
		$external = get_option( 'compare-general' );
		$external = $external['ext_check'];

		/**
		 * Get Subscribed programs
		 */
		$p = get_option( 'awin' );
		if ( ! empty( $p['partner'] ) ) {
			$programs = explode( ',', $p['partner'] );
		}

		$effiliation_programs = get_option( 'compare-effiliation');
		if (!empty( $effiliation_programs['programs'] ) ){
			foreach ( $effiliation_programs['programs'] as $key => $effiliation_program ) {
				array_push( $programs, $effiliation_program );
			}
		}
		if ( 'on' === $external ) {
			$db       = compare_external_db::getInstance();
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
				$prefix = get_option( 'compare-general' );
				$prefix = $prefix['prefix'];

				$db       = compare_external_db::getInstance();
				$cnx      = $db->getConnection();
				$table    = $prefix . 'compare';
				$products = array();
				if ( null !== $eanlist[0] ) {
					foreach ( $eanlist as $list ) {
						$product = $cnx->get_results( $cnx->prepare( 'SELECT * FROM ' . $table . ' WHERE ean LIKE %s ORDER BY `price` ASC', $list ), ARRAY_A );

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
					foreach ($programs as $program ) {
						$product = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE ean = %s ORDER BY `price` ASC', $list ), ARRAY_A );


						if ( ! empty( $product ) ) {
							array_push( $products, $product );
						}
					}
				}
			}
		}

		if ( !empty( $products ) ) {
			$subscribed = compare_get_programs();
			$products  = array_reverse( $products[0] );
			$products  = array_combine( array_column( $products, 'partner_name' ), $products );
			$products  = array_reverse( $products );

			foreach ( $products as $key => $value){
				$in_array = array_key_exists( $key, $subscribed );
				if ( false ===  $in_array  ){
					unset( $products[ $key ] );
				}
			}

			$transient = set_transient( 'product_' . $eanlist[0], $products, 4 * HOUR_IN_SECONDS );
			return $products;
		}
	}

	/**
	 * Get Logo URL
	 * @return array array of logo url
	 */
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


}

new template();