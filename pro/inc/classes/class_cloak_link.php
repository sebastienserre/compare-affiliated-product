<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

/**
 * Class Link_obfsucation
 */
class Cloak_Link {

	public function __construct() {
		//add_action( 'thfo_compare_after_price', array( $this, 'compare_amz_link' ), 20, 1 );
	}

	/**
	 * Hide link to improve SEO
	 *
	 * @param array  $product data of compared product.
	 * @param string $logo    String to the partner logo
	 */
	public function compare_create_link( $product, $logo, $data ) {
		$logos = template::compare_get_partner_logo();
		if ( isset( $logos[$product['partner_code']] ) ){
			$logo = $logos[$product['partner_code']];
		}
		if ( "amz" === $product['partner_code'] ){
			$logo = COMPARE_PLUGIN_URL. '/assets/img/amazon.png';
		}

		/**
		 * Add an URL tracker
		 */
		switch ( $product['platform'] ){
			case 'Awin':
				$tracker = apply_filters( 'compare_url_tracker', get_bloginfo( 'url' ) );
				$url     = base64_encode( $product['url'] . '&clickref=' . $tracker );
				break;
			default:
				$url     = base64_encode( $product['url'] );
		}

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
		?>

			<div class="atc" data-atc="<?php echo $url; ?>">
				<div class="img-partner"><img src="<?php echo $logo ?>"></div>
				<?php if ( has_shortcode( get_the_content(), 'compare_price') ) {
					?>
					<div class="compare_partner_name">
						<p><?php echo $product['partner_name']; ?></p>
					</div>
			<?php
				}?>
				<div class="product-price">
					<?php echo $product['price']; ?>
				</div>
				<div class="button-partner">
					<button style=" background:<?php echo $bg; ?>; color: <?php echo $color; ?>; " class="btn-compare"
					        data-atc="<?php echo $url; ?>"><?php echo $text; ?>
					</button>
				</div>
			</div>

		<?php
	}

	public static function compare_amz_link( $data ) {
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
		?>
		<div class="compare-price-partner compare-price-amz">
			<div class="atc" data-atc="<?php echo base64_encode( $data->get_product_url() ); ?>">
				<div class="img-partner">
					<img class="logo-amazon" src="<?php echo COMPARE_PLUGIN_URL ?>/assets/img/amazon.png">
				</div>
				<div class="product-price">
					<?php echo $data->get_product_pricing() ?>
				</div>
				<div class="button-partner">
					<button class="btn-compare" style="background:<?php echo $bg; ?>; color: <?php echo $color; ?>; ">
						<?php echo $text; ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}

	public function compare_amz_cloak_table( $data ) {
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
		$product = aawp_get_product_from_api( $data['asin'] );
		if ( null === $product['pricing']['new'] ){
			$price = $product['pricing']['list']['normalized'];
		} else {
			$price = $product['pricing']['new']['normalized'];
		}
		$currency = $general['currency'];
		$currency = apply_filters( 'compare_currency_unit', $currency );
		?>
		<div class="compare-price-partner compare-price-amz">
			<div class="atc" data-atc="<?php echo base64_encode( $product['urls']['basic'] ); ?>">
				<div class="img-partner">
					<img class="logo-amazon" src="<?php echo COMPARE_PLUGIN_URL ?>/assets/img/amazon.png">
				</div>
				<div class="product-price">
					<?php echo $price . $currency; ?>
				</div>
				<div class="button-partner">
					<button class="btn-compare" style="background:<?php echo $bg; ?>; color: <?php echo $color; ?>; ">
						<?php echo $text; ?>
					</button>
				</div>
			</div>
		</div>
<?php
	}
}

new Cloak_Link();
