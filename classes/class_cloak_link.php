<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

/**
 * Class Link_obfsucation
 */
class Cloak_Link {

	/**
	 * Hide link to improve SEO
	 * @param array $product data of compared product.
	 * @param string $logo String to the partner logo
	 */
	public function compare_create_link( $product, $logo='', $data ) {
		$url      = base64_encode( $product['url'] );
		$currency = get_option( 'general' );
		$currency = $currency['currency'];
		$currency = apply_filters( 'compare_currency_unit', $currency );
		$option = get_option( 'compare-aawp' );
		$text = $option['button_text'];
		if ( empty($text ) ){
			$text = __( 'Buy to ', 'compare' );
		}
		$bg = $option['button-bg'];
		if ( empty( $bg ) ){
			$bg = '#000000';
		}
		$color = $option['button-color'];
		if ( empty( $color ) ){
			$color = '#ffffff';
		}
		?>
		<div class="compare-price-partner compare-others">
			<p class="atc" data-atc = "<?php echo $url; ?>">
				<?php echo $logo . '<p class="product-price">' . $product['price'] . ' ' . $currency ?>
			</p>
			<button style=" background:<?php echo $bg;?>; color: <?php echo $color; ?>; " class="atc btn-compare" data-atc = "<?php echo $url; ?>"><?php echo $text; ?></button>

			</span>
		</div>

		<?php
	}

}

new Cloak_Link();
