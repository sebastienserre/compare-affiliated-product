<?php

class Compare_price {
	public function __construct() {
		add_shortcode( 'compare_price', array( $this, 'compare_price_sc' ) );
		add_filter( 'compare_products', array( $this, 'compare_add_amz' ), '', 2 );
	}

	public static function compare_add_amz( $products, $atts ) {
		if ( class_exists( 'AAWP_Affiliate' ) ) {
			$data['asin']     = $atts['product'];
			$product_id       = aawp_create_product( $data );
			$amz              = aawp_get_product( $product_id );
			$amz['price_new'] = explode( '.', $amz['price'] );
			$amz['price_cts'] = substr( $amz['price_new'][1], 0, 2 );
			$amz['price']     = $amz['price_new'][0] . ',' . $amz['price_cts'];
			$products['amz']  =
				array(
					'ean'          => $amz['ean'],
					'title'        => $amz['title'],
					'description'  => $amz['title'],
					'img'          => $amz['image_ids'],
					'partner_name' => 'Amazon FR',
					'partner_code' => 'amz',
					'product_id'   => $amz['id'],
					'url'          => $amz['urls']['basic'],
					'price'        => $amz['price'],
					'last_updated' => $amz['date_updated'],
					'platform'     => 'Amz'

				);

		}

		return $products;
	}

	public static function compare_price_sc( $atts ) {
		$atts = shortcode_atts( array(
			'product' => '',
		), $atts, 'compare_price' );

		$ean = compare_get_ean( $atts['product'] );

		$datas = template::compare_get_data( $ean, $atts );


		if ( empty( $datas ) ) {
			return;
		}

		ob_start();
		?>
		<div class="compare_sc compare_price_main">
			<?php
			foreach ( $datas as $p ) {
				$general = get_option( 'compare-general' );
				if ( 'on' === $general['general-cloack'] ) {
					$link = new Cloak_Link();
					?>

					<?php
					$link->compare_create_link( $p );
					?>

					<?php
				} else {

					$logos = template::compare_get_partner_logo();

					if ( isset( $logos[ $p['partner_code'] ] ) ) {
						$logo = $logos[ $p['partner_code'] ];
					}
					if ( "amz" === $p['partner_code'] ){
						$logo = COMPARE_PLUGIN_URL. '/assets/img/amazon.png';
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
			?>
			<div class="clear"></div>
		</div>
		<?php
		return ob_get_clean();
	}

}

new Compare_price();
