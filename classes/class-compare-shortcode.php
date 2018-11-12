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
				'platform' => 'amazon',
			),
			$atts,
			'cap'
		);
		$amazon = new Amazon();
		$datas  = $amazon->compare_get_amz_data( $atts['product'] );

		$currency = get_option( 'compare-general' );
		$currency = $currency['currency'];
		$currency = apply_filters( 'compare_currency_unit', $currency );

		$price = $datas['Items']['Item']['Offers']['Offer']['OfferListing']['Price']['FormattedPrice'];
		$price = explode( ' ', $price );
		$price = intval( $price[1] );

		if ( cap_fs()->is__premium_only() ) {
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
				'platform'     => 'Amz'

			);


		foreach ( $products as $key => $p ) {
			//$products[$key]['price'] = number_format( floatval( $p['price'] ), 2);
			$products[ $key ]['price'] = floatval( $p['price'] );
			$vc_array_name[ $key ]     = $p['price'];

		}

		array_multisort( $vc_array_name, SORT_ASC, $products );

		foreach ( $products as $key => $p ) {
			$products[$key]['price'] = number_format( floatval( $p['price'] ), 2) . $currency;

		}

		switch ( $atts['type'] ) {
			case 'basic':
				return $this->cap_shortcode_basic( $datas );
			case 'table':
				return $this->cap_shortcode_table( $products );
		}
	}


	public function cap_shortcode_basic( $data ) {
		ob_start();
		?>
		<div class="compare_basic_amz">
			<h3><?php echo esc_attr( $data['Items']['Item']['ItemAttributes']['Title'] ); ?></h3>
			<div class="main-row">
				<div class="compare_basic_sc_left">
					<img src="<?php echo esc_url( $data['Items']['Item']['LargeImage']['URL'] ); ?>"/>
				</div>
				<div class="compare_basic_right">
					<div class="compare_sc_description">
						<ul>
							<?php
							if ( is_array( $data['Items']['Item']['ItemAttributes']['Feature'] ) ) {
								foreach ( $data['Items']['Item']['ItemAttributes']['Feature'] as $feature ) {
									echo '<li>' . $feature . '</li>';
								}
							} else {
								echo '<li>' . $data['Items']['Item']['ItemAttributes']['Feature'] . '</li>';
							}
							?>
						</ul>
					</div>
					<div class="price-box">
						<?php
						$url = $p['url'];

						$currency = get_option( 'compare-general' );
						$currency = $currency['currency'];
						$currency = apply_filters( 'compare_currency_unit', $currency );
						$option   = get_option( 'compare-style' );
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
						$price = $data['Items']['Item']['Offers']['Offer']['OfferListing']['Price']['FormattedPrice'];
						$price = explode( ' ', $price );
						$price = $price[1] . ' ' . $currency;
						?>
						<div class="compare-price-partner compare-others">
							<div class="product-price">
								<a href="<?php echo $data['Items']['Item']['Offers']['MoreOffersUrl']; ?>">
									<?php echo $price; ?>
								</a>
								<p><?php echo esc_attr( $data['Items']['Item']['Offers']['Offer']['OfferListing']['Availability'] ) ?></p>
							</div>
							<div class="button-partner">
								<button style=" background:<?php echo $bg; ?>; color: <?php echo $color; ?>; "><a
											class="btn-compare">
										<a href="<?php echo $data['Items']['Item']['Offers']['MoreOffersUrl']; ?>"><?php echo $text; ?></a>
									</a>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public function cap_shortcode_table( $products ) {
		$option = get_option( 'compare-style' );
		$text   = $option['button_text'];
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
		ob_start();
		?>
		<div class="cap-sc">
		<?php
		foreach ( $products as $p ) {

			$logos = template::compare_get_partner_logo();

			if ( isset( $logos[ $p['partner_code'] ] ) ) {
				$logo = $logos[ $p['partner_code'] ];
			}
			if ( "amz" === $p['partner_code'] ) {
				$logo = COMPARE_PLUGIN_URL . '/assets/img/amazon.png';
			}

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
		?>
		</div>
			<?php
		return ob_get_clean();
	}
}


new compare_shortcode();
