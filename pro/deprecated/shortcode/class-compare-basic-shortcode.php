<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.


/**
 * Class Compare_Basic_Widget
 */
class Compare_Basic_Widget {
	public function __construct() {
		add_shortcode( 'compare_basic_sc', array( $this, 'compare_basic_sc' ) );
	}

	public function compare_basic_sc( $atts ) {
		$atts = shortcode_atts( array(
			'product' => '',
			'layout'  => 'horizontal',
			'partner' => 'cdiscount',
		), $atts, 'compare_basic_sc' );

		$ean              = compare_get_ean( $atts['product'] );
		$template         = new template();
		$datas            = $template->compare_get_data( $ean );
		$main_partner     = ucfirst( $atts['partner'] );
		$general          = get_option( 'compare-general' );
		$currency         = $general['currency'];
		$currency         = apply_filters( 'compare_currency_unit', $currency );
		$option           = get_option( 'compare-general' );
		$partner_logo_url = $partner_logo_url['partner_logo'];

		ob_start();
		?>
		<div class="compare_basic_sc">
			<h3><?php echo esc_attr( $datas[ $main_partner ]['title'] ); ?></h3>
			<div class="main-row">
				<div class="compare_basic_sc_left">
					<img src="<?php echo esc_url( $datas[ $main_partner ]['img'] ); ?>"/>
				</div>
				<div class="compare_basic_right">
					<div class="compare_sc_description">
						<p><?php echo esc_attr( $datas[ $main_partner ]['description'] ); ?></p>
					</div>
					<h4 class="compare_sc_title"><?php _e( 'Where finding this product?', 'compare' ); ?></h4>
					<div class="price-box">
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

								?>
								<div class="compare-price-partner compare-price-partner-<?php echo $i; ?> compare-others">
									<div class="img-partner"><img src="<?php echo $logo ?>"></div>
									<div class="product-price">
										<a href="<?php echo $p['url']; ?>">
											<?php echo $p['price'] . ' ' . $currency ?>
										</a>
									</div>
									<div class="button-partner">
										<button style=" background:<?php echo $bg; ?>; color: <?php echo $color; ?>; ">
											<a class="btn-compare">
												<a href="<?php echo $p['url']; ?>"><?php echo $text; ?></a>
											</a>
										</button>
									</div>
								</div>

								<?php
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
}

new Compare_Basic_Widget();
