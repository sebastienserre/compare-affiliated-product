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
	public function compare_create_link( $product, $logo ) {
		$url      = base64_encode( $product['url'] );
		$currency = get_option( 'general' );
		$currency = $currency['currency'];
		$currency = apply_filters( 'compare_currency_unit', $currency );
		?>
		<p class="compare-price">
			<span class="atc"
			      data-atc = "<?php echo $url; ?>"><?php echo $logo . ' ' . $product['price'] . ' ' . $currency ?></span>
		</p>
		<?php
	}

}

new Cloak_Link();
