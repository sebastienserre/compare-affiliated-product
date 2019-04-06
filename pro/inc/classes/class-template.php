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

		if ( is_object( $data ) ) {
			$asin = $data->get_product_id();
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
		$this->compare_display_html( $eanlist, $data, $asin );
	}

	/**
	 * Provides HTML to display
	 *
	 * @param $eanlist Array of EAN13
	 * @param $data    array of Product datas.
	 */
	public function compare_display_html( $eanlist, $data, $asin ) {

		switch ( $data->atts['partners'] ) {
			case 'nok' :
				$amz   = new Amazon();
				$datas = $amz->compare_get_amz_data( $asin );

				$price = $datas['Items']['Item']['OfferSummary']['LowestNewPrice']['FormattedPrice'];
				$price = explode( ' ', $price );
				$price = intval( $price[1] );

				$prods['amz'] =
					array(
						'ean'          => '',
						'title'        => $datas['Items']['Item']['ItemAttributes']['Title'],
						'description'  => $datas['Items']['Item']['ItemAttributes']['Feature'],
						'img'          => $datas['Items']['Item']['LargeImage']['URL'],
						'partner_name' => 'Amazon FR',
						'partner_code' => 'amz',
						'product_id'   => $datas['Items']['item']['ASIN'],
						'url'          => $datas['Items']['Item']['DetailPageURL'],
						'price'        => $price,
						'platform'     => 'Amz'

					);
				break;
			case '':
			case 'ok':
			default:
				$prods = $this->compare_get_data( $eanlist );
				$amz   = new Amazon();
				$datas = $amz->compare_get_amz_data( $asin );
				$price = $datas['Items']['Item']['OfferSummary']['LowestNewPrice']['FormattedPrice'];
				$price = explode( ' ', $price );
				$price = $price[1];

				$prods['amz'] =
					array(
						'ean'          => '',
						'title'        => $datas['Items']['Item']['ItemAttributes']['Title'],
						'description'  => $datas['Items']['Item']['ItemAttributes']['Feature'],
						'img'          => $datas['Items']['Item']['LargeImage']['URL'],
						'partner_name' => 'Amazon FR',
						'partner_code' => 'amz',
						'product_id'   => $datas['Items']['Item']['ASIN'],
						'url'          => $datas['Items']['Item']['DetailPageURL'],
						'price'        => $price,
						'platform'     => 'Amz'

					);
				break;
		}


		foreach ( $prods as $key => $p ) {
			$prods[ $key ]['price'] = $p['price'];
			$vc_array_name[ $key ]  = $p['price'];
		}

		array_multisort( $vc_array_name, SORT_ASC, $prods );

		$general  = get_option( 'compare-general' );
		$currency = $general['currency'];
		$currency = apply_filters( 'compare_currency_unit', $currency );

		foreach ( $prods as $key => $p ) {
			$prods[ $key ]['price'] = $p['price'] . $currency;

		}
		$partner_logo_url = get_option( 'awin' );
		$logo             = $partner_logo_url['partner_logo'];
		ob_start();
		?>
		<?php
		if ( ! is_null( $prods ) ) {
			$i = 1;
			?>
            <div class="compare-price">
				<?php
				foreach ( $prods as $p ) {
					$partner = apply_filters( 'compare_partner_name', $p['partner_name'] );

					$premium = get_option( 'compare-premium' );

					$option = get_option( 'compare-style' );
					$text   = $option['button_text'];

					/**
					 * Add an URL tracker
					 */
					switch ( $p['platform'] ) {
						case 'Awin':
							$tracker = apply_filters( 'compare_url_tracker', get_bloginfo( 'url' ) );
							$url     = $p['url'] . '&clickref=' . $tracker;
							break;
						default:
							$url = $p['url'];
					}

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
					if ( isset( $premium['general-cloack'] ) && 'on' === $premium['general-cloack'] ) {
						$link = new Cloak_Link();
						?>

                        <div class="compare-price-partner compare-price-partner-<?php echo $i; ?> compare-others">
							<?php
							$link->compare_create_link( $p, $logo, $data );
							?>
                        </div>

						<?php
					} else {
						if ( "amz" === $p['partner_code'] ) {
							$logo   = COMPARE_PLUGIN_URL . 'assets/img/amazon.png';
							$amz    = get_option( 'compare-amazon' );
							$tag    = $amz['trackingid'];
							$tagpos = strpos( $p['url'], 'tag=' );
							if ( $tagpos > 0 ) {
								$url      = explode( 'tag=', $p['url'] );
								$p['url'] = add_query_arg( 'tag', $tag, $url[0] );
								$url      = $p['url'] . '&keywords=' . $url[1];
							} else {
								$url = add_query_arg( 'tag', $tag, $p['url'] );
							}
						} else {
							$logos = template::compare_get_partner_logo();

							if ( isset( $logos[ $p['partner_code'] ] ) ) {
								$logo = $logos[ $p['partner_code'] ];
							}
						}


						?>
                        <div class="compare-price-partner compare-price-partner-<?php echo $i; ?> compare-others">
                            <div class="img-partner"><img src="<?php echo $logo ?>"></div>
                            <div class="product-price">
                                <a href="<?php echo $url; ?>">
									<?php echo $p['price'] ?>
                                </a>
                            </div>
                            <div class="button-partner">
                                <a class="btn-compare" href="<?php echo $p['url']; ?>"
                                   style=" background:<?php echo $bg; ?>; color: <?php echo $color; ?>; ">
									<?php echo esc_attr( $text ); ?>
                                </a>
                            </div>
                        </div>


						<?php
					}
				}
				?>
            </div>
			<?php
			$i ++;
		}

		$html = ob_get_clean();
		echo $html;
	}

	/**
	 * Provide an array of EAN then get data from partners
	 *
	 * @param $eanlist array
	 *
	 * @return array Array of products
	 */
	public function compare_get_data( $eanlist, $atts = '', $mpn ) {
		$products = array();
		if ( ! is_array( $eanlist ) ) {
			$eanlist = array( $eanlist );
		}

		$transient = get_transient( 'product_' . $eanlist[0] );
		if ( ! empty( $transient ) ) {
			return $transient;
		}

		if ( cap_fs()->is__premium_only() ) {
			$external = get_option( 'compare-advanced' );
			if ( ! empty( $external['ext_check'] ) ) {
				$external = $external['ext_check'];
			}

			/**
			 * Get Subscribed programs
			 */
			$premium   = get_option( 'compare-premium' );
			$platforms = $premium['platform'];
			foreach ( $platforms as $platform ) {
				switch ( $platform ) {
					case 'awin':
						$p = get_option( 'awin' );
						if ( ! empty( $p['partner'] ) ) {
							$programs = explode( ',', $p['partner'] );
						}
						break;
					case 'effiliation':
						$effiliation_programs = get_option( 'compare-effiliation' );
						if ( ! empty( $effiliation_programs['programs'] ) ) {
							foreach ( $effiliation_programs['programs'] as $key => $effiliation_program ) {
								array_push( $programs, $effiliation_program );
							}
						}
						break;
					case 'manomano':

						break;
				}
			}

			if ( 'on' === $external ) {
				$db = compare_external_db::getInstance();
				if ( is_wp_error( $db ) ) {
					global $wpdb;
					$table    = $wpdb->prefix . 'compare';

					$this->compare_get_products( $wpdb, $table, $eanlist, $mpn );

				} else {
					$prefix = get_option( 'compare-advanced' );
					$prefix = $prefix['prefix'];

					$db       = compare_external_db::getInstance();
					$cnx      = $db->getConnection();
					$table    = $prefix . 'compare';

					$this->compare_get_products( $cnx, $table, $eanlist, $mpn );

				}
			} else {
				global $wpdb;
				$table    = $wpdb->prefix . 'compare';

				$products = $this->compare_get_products( $wpdb, $table, $eanlist, $mpn, $products );
			}
		}

		if ( ! empty( $products ) ) {

			$subscribed = compare_get_programs();

			$products = array_reverse( $products[0] );
			$products = array_combine( array_column( $products, 'partner_name' ), $products );


			if ( has_shortcode( get_the_content(), 'compare_price' ) ) {
				$products = apply_filters( 'compare_products', $products, $atts );
			}
			$products = array_reverse( $products );

			foreach ( $products as $key => $value ) {
				if ( 'amz' !== $key ) {
					$in_array = array_key_exists( $key, $subscribed );
					if ( false === $in_array ) {
						unset( $products[ $key ] );
					}
				}
				$products[ $key ]['price'] = floatval( $value['price'] );
				$vc_array_name[ $key ]     = $value['price'];
			}
			array_multisort( $vc_array_name, SORT_ASC, $products );

			foreach ( $products as $key => $p ) {
				$products[ $key ]['price'] = number_format( floatval( $p['price'] ), 2 );

			}

			$transient = set_transient( 'product_' . $eanlist[0], $products, 4 * HOUR_IN_SECONDS );

			return $products;
		}
	}

	/**
	 * Get Logo URL
	 *
	 * @return array array of logo url
	 */
	public static function compare_get_partner_logo() {
		$awin = get_option( 'awin' );
		if ( ! empty( $awin['partner_logo'] ) ) {
			foreach ( $awin['partner_logo'] as $key => $img ) {
				$logos[ $key ] = $img['img'];
			}
		}

		$programs = Effiliation::compare_get_effiliation_program();
		if ( null != $programs ) {
			foreach ( $programs['programs'] as $program ) {
				$logos[ $program['id_programme'] ] = $program['urllo'];
			}
		}

		$options = get_option( 'compare-premium');
		$platform = $options['platform']['manomano'];
		if ( 'manomano' === $platform ){
		    $logos['manomano'] = COMPARE_PLUGIN_URL . 'pro/assets/img/manomano.jpg';
        }

		return $logos;
	}

	/**
	 * @param   $cnx object $wpdb object
	 * @param   $table string table where datas are stored
	 * @param   $eanlist array ean list to retrieve
	 * @param   $mpn  string  product mpn
	 *
	 * @return array Array with product datas
	 * @author  sebastienserre
	 */
	public static function compare_get_products( $cnx, $table, $eanlist, $mpn ) {
	    global $wpdb;

	    if ( ! $products ){
	        $products = array();
        }
	    if ( null !== $eanlist[0] ) {
			foreach ( $eanlist as $list ) {
				$product = $cnx->get_results( $wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE ean = %s ORDER BY `price` ASC', $list ), ARRAY_A );

				if ( ! empty( $product ) ) {
					array_push( $products, $product );
				}
			}
		}
		if ( ! empty( $mpn ) ) {
			$product = $cnx->get_results( $wpdb->prepare( 'SELECT * FROM ' . $table . ' WHERE mpn = %s ORDER BY `price` ASC', $mpn ), ARRAY_A );

			if ( ! empty( $product ) ) {
				array_push( $products, $product );
			}
		}

		return $products;
	}


}

new template();
