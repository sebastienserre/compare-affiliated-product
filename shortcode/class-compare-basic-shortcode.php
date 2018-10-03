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
			'ean'     => '',
			'layout'  => 'horizontal',
			'partner' => 'cdiscount',
		), $atts, 'compare_basic_sc' );

		$awin             = new Awin();
		$datas            = $awin->compare_get_data( $atts['ean'] );
		$main_partner     = ucfirst( $atts['partner'] );
		$general          = get_option( 'general' );
		$currency         = $general['currency'];
		$currency         = apply_filters( 'compare_currency_unit', $currency );
		$partner_logo_url = get_option( 'awin' );
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
					<h4 class="compare_sc_title"><?php _e('Where to find this product?', 'compare'); ?></h4>
					<div class="price-box">
						<?php
						foreach ( $datas as $data ) {
							$partner = apply_filters( 'compare_partner_name', $data['partner_name'] );
							switch ( $data['partner_name'] ) {
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
							if ( 'on' === $general['general-cloack'] ) {
								$link = new Cloak_Link();
								?>

								<?php
								$link->compare_create_link( $p, $logo, $data );
								?>

								<?php
							} else {
								?>

								<div class="compare_basic_sc_partner_price">
									<a href="<?php echo $data['url']; ?>"
									   title="<?php echo $data['title'] . __( ' on ', 'compare' ) . $data['partner_name']; ?>">
										<?php echo $logo; ?>
										<p><?php echo $data['price'] . $currency; ?></p>
									</a>

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
