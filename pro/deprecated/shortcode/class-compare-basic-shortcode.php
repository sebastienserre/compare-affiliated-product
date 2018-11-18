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

		ob_start();
		?>
		<div class="compare_basic_sc">
			<h3><?php _e('Deprecated Shortcode', 'compare') ?></h3>
			<p><?php _e(' Please check to use the [cap] shortcode', 'compare'); ?></p>
			<a href="https://www.thivinfo.com/en/docs/compare-affiliated-products/"><?php _e('Please read the documentation', 'compare' ); ?></a>
		</div>

		<?php
		return ob_get_clean();
	}
}

new Compare_Basic_Widget();
