<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

class compare_shortcode {

	public function __construct() {
		add_shortcode( 'cap', array( $this, 'cap_shortcode' ) );
	}

	public function cap_shortcode( $atts ) {
		$atts   = shortcode_atts(
			array(
				'type'     => 'basic',
				'product'  => '',
				'partners' => 'nok',
				'options'   => '',
			),
			$atts,
			'cap'
		);
		$amazon = new Amazon();
		$datas  = $amazon->compare_get_amz_data( $atts['product'] );

		$currency = get_option( 'compare-general' );
		$currency = $currency['currency'];
		$currency = apply_filters( 'compare_currency_unit', $currency );

		$price = $datas['Items']['Item']['OfferSummary']['LowestNewPrice']['FormattedPrice'];
		$price = explode( ' ', $price );
		$price = intval( $price[1] );

		if ( cap_fs()->is__premium_only() && $atts['partners'] == 'ok' ) {
			$ean      = compare_get_ean( $atts['product'] );
			$template = new template();
			$products = $template->compare_get_data( $ean, $atts );
		}


		$products['amz'] =
			array(
				'ean'          => '',
				'title'        => $datas['Items']['Item']['ItemAttributes']['Title'],
				'description'  => $datas['Items']['Item']['ItemAttributes']['Feature'],
				'img'          => $datas['Items']['Item']['LargeImage']['URL'],
				'partner_name' => 'Amazon FR',
				'partner_code' => 'amz',
				'product_id'   => $datas['Items']['item']['ASIN'],
				'url'          => $datas['Items']['Item']['Offers']['MoreOffersUrl'],
				'price'        => $price,
				'platform'     => 'Amz',
			);

		/**
		 * @description Add reviews.
		 * @since 2.0.6
		 * @author Sébastien SERRE
		 *
		 */
		if ( 'reviews' === $atts['options'] ||'reviews' === $atts['type'] && 'true' === $datas["Items"]["Item"]["CustomerReviews"]["HasReviews"]) {
			$products['amz']['reviews'] = $datas["Items"]["Item"]["CustomerReviews"]["IFrameURL"];
		}


		foreach ( $products as $key => $p ) {
			$products[ $key ]['price'] = floatval( $p['price'] );
			$vc_array_name[ $key ]     = $p['price'];
		}

		array_multisort( $vc_array_name, SORT_ASC, $products );

		foreach ( $products as $key => $p ) {
			$products[ $key ]['price'] = number_format( floatval( $p['price'] ), 2 ) . $currency;

		}

		switch ( $atts['type'] ) {
			case 'basic':
				return $this->cap_shortcode_basic( $products );
			case 'table':
				return $this->cap_shortcode_table( $products );
			case 'reviews':
				return $this->cap_shortcode_reviews( $products );
		}
	}

	/**
	 * @param $products array Array with Amazon products details.
	 *
	 * @return false|string
	 * @since 2.0.6
	 * @author Sébastien Serre
	 */
	public function cap_shortcode_reviews( $products ){
		ob_start();
		?>
		<div class="cap_amz_review">
			<iframe class="cap-amz-review" src="<?php echo $products['amz']['reviews']; ?>"width="100%" height="auto"></iframe>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * @param $products array Array with Amazon products details.
	 *
	 * @return false|string
	 * @throws Freemius_Exception
	 */
	public function cap_shortcode_basic( $products ) {
		$option = get_option( 'compare-style' );
		$text   = $option['button_text'];
		if ( empty( $text ) ) {
			$text = __( 'Buy to ', 'compare-affiliated-products' );
		}
		$bg = $option['button-bg'];
		if ( empty( $bg ) ) {
			$bg = '#000000';
		}
		$color = $option['button-color'];
		if ( empty( $color ) ) {
			$color = '#ffffff';
		}

		ob_start();
		?>
		<div class="compare_basic_amz">
			<h3><?php echo esc_attr( $products['amz']['title'] ); ?></h3>
			<div class="main-row">
				<div class="compare_basic_sc_left">
					<img src="<?php echo esc_url( $products['amz']['img'] ); ?>"/>
				</div>
				<div class="compare_basic_right">
					<div class="compare_sc_description">
						<ul>
							<?php
							if ( is_array( $products['amz']['description'] ) ) {
								foreach ( $products['amz']['description'] as $feature ) {
									echo '<li>' . $feature . '</li>';
								}
							} else {
								echo '<li>' . $products['amz']['description'] . '</li>';
							}
							?>
						</ul>
					</div>
					<?php
					/**
					* @description Add reviews.
					* @since 2.0.6
					* @author Sébastien SERRE
					*
					*/
					if ( ! empty( $products['amz']['reviews'] ) ) {
						?>

					<button id="login" style=" background:<?php echo $bg; ?>; color: <?php echo $color; ?>; ">Voir les Avis</button>
					<div id="popup">
						<div id="popup-bg"></div>
						<div id="popup-fg">

							<iframe class="cap-amz-review" src="<?php echo $products['amz']['reviews']; ?>"width="100%" height="auto"></iframe>
							<div class="actions">
								<button id="close" style="background:<?php echo $bg; ?>; color: <?php echo $color; ?>; "><?php _e( 'Close', 'compare-affiliated-products'); ?></button>
							</div>
						</div>
					</div>
						<?php } ?>
					<div class="price-box cap-sc">
						<?php
						foreach ( $products as $p ) {
							if ( "amz" === $p['partner_code'] ) {
								$logo     = COMPARE_PLUGIN_URL . '/assets/img/amazon.png';
								$amz      = get_option( 'compare-amazon' );
								$tag      = $amz['trackingid'];
								$p['url'] = add_query_arg( 'tag', $tag, $p['url'] );
							} else {
								$logos = template::compare_get_partner_logo();
								if ( isset( $logos[ $p['partner_code'] ] ) ) {
									$logo = $logos[ $p['partner_code'] ];
								}
							}
							$premium = get_option( 'compare-premium' );
							if ( 'on' === $premium['general-cloack'] && cap_fs()->is__premium_only() ) {
								$cloaked = new Cloak_Link();
								$cloaked->compare_create_link( $p, $logo, $data );

							} else {
								echo $this->cap_template_price( $p, $text, $color, $bg, $logo );
							}
						}
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * @param $products array Array with Amazon products details.
	 *
	 * @return false|string
	 * @throws Freemius_Exception
	 */
	public function cap_shortcode_table( $products ) {
		$option = get_option( 'compare-style' );
		$text   = $option['button_text'];
		if ( empty( $text ) ) {
			$text = __( 'Buy to ', 'compare-affiliated-products' );
		}
		$bg = $option['button-bg'];
		if ( empty( $bg ) ) {
			$bg = '#000000';
		}
		$color = $option['button-color'];
		if ( empty( $color ) ) {
			$color = '#ffffff';
		}

		ob_start();
		?>
		<?php
		/**
		 * @description Add reviews.
		 * @since 2.0.6
		 * @author Sébastien SERRE
		 *
		 */
		if ( ! empty( $products['amz']['reviews'] ) ) {
			?>

			<button id="login" style=" background:<?php echo $bg; ?>; color: <?php echo $color; ?>; ">Voir les Avis</button>
			<div id="popup">
				<div id="popup-bg"></div>
				<div id="popup-fg">

					<iframe class="cap-amz-review" src="<?php echo $products['amz']['reviews']; ?>"width="100%" height="auto"></iframe>
					<div class="actions">
						<button id="close" style="background:<?php echo $bg; ?>; color: <?php echo $color; ?>; "><?php _e( 'Close', 'compare-affiliated-products'); ?></button>
					</div>
				</div>
			</div>
		<?php } ?>
		<div class="cap-sc">
			<?php
			foreach ( $products as $p ) {

				if ( cap_fs()->is__premium_only() ) {
					$logos = template::compare_get_partner_logo();

					if ( isset( $logos[ $p['partner_code'] ] ) ) {
						$logo = $logos[ $p['partner_code'] ];
					}
				}
				if ( "amz" === $p['partner_code'] ) {
					$logo     = COMPARE_PLUGIN_URL . '/assets/img/amazon.png';
					$amz      = get_option( 'compare-amazon' );
					$tag      = $amz['trackingid'];
					$p['url'] = add_query_arg( 'tag', $tag, $p['url'] );
				}
				$premium = get_option( 'compare-premium' );
				if ( 'on' === $premium['general-cloack'] && cap_fs()->is__premium_only() ) {
					$cloaked = new Cloak_Link();
					$cloaked->compare_create_link( $p, $logo, $data );

				} else {
					echo $this->cap_template_price( $p, $text, $color, $bg, $logo );
				}
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	public
	function cap_template_price(
		$p, $text, $color, $bg, $logo
	) {
		?>
		<div class="compare_price-partner">
			<div class="compare_partner_logo">
				<img src="<?php echo $logo; ?>">
			</div>
			<div class="compare_partner_name">
				<p><?php echo $p['partner_name']; ?></p>
			</div>
			<div class="compare_price">
				<?php echo $p['price'] . ' ' . $currency; ?>
			</div>
			<button style=" background:<?php echo $bg; ?>; color: <?php echo $color; ?>; "
			        class="compare_buy"><a
						class="btn-compare">
					<a href="<?php echo $p['url']; ?>"><?php echo $text; ?></a>
				</a>
			</button>
		</div>
		<?php
	}
}


new compare_shortcode();
