<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.


function cap_advertisment() {
	?>
	<div class="cap-setting-ads">
		<a href="<?php echo esc_url( 'https://www.thivinfo.com/aide-au-parametrage/' ); ?>"
		   title="<?php esc_attr_e( 'Need Help to Setup', 'compare-affiliated-products' ); ?>"><?php esc_attr_e( 'Need Help to Setup', 'compare-affiliated-products' ); ?></a>
	</div>
	<?php
}

function cap_support() {
	$support_link = 'https://www.thivinfo.com/soumettre-un-ticket/';
	$support      = sprintf( wp_kses( __( '<a href="%1$s" target="_blank">Leave me a ticket on Thivinfo.com</a>', 'compare-affiliated-products' ), array(
		'a' => array(
			'href'   => array(),
			'target' => array()
		)
	) ), esc_url( $support_link ) );
	?>
	<div class="cap-setting-ads">
	<?php echo $support; ?>
	</div>
	<?php
}

function cap_doc() {
	?>
	<div class="cap-setting-ads">
		<a href="https://www.thivinfo.com/docs/compare-affiliated-products/"><?php _e( 'Documentation Center', 'compare-affiliated-products' ); ?></a>
	</div>
<?php
}
