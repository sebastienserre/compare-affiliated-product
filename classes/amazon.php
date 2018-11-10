<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

class Amazon {

	public $amz;

	public function __construct() {
		add_filter( 'compare_setting_tabs', array( $this, 'compare_amazon_settings_tabs' ) );
		add_action( 'admin_init', array( $this, 'compare_amazon_settings' ) );
		$this->amz = get_option( 'compare-amazon' );
	}

	/**
	 * @param $tabs array array with existings tabs
	 *
	 * @return mixed array with all tabs
	 */
	public function compare_amazon_settings_tabs( $tabs ) {
		$tabs['amazon'] = 'Amazon';

		return $tabs;
	}

	/**
	 * Add settings for platform amz
	 */
	public function compare_amazon_settings() {
		add_settings_section( 'compare-amazon', '', '', 'compare-amazon' );
		register_setting( 'compare-amazon', 'compare-amazon' );
		add_settings_field( 'compare-amazon-apikey', __( 'Amazon API Key', 'compare' ), array(
			$this,
			'compare_amz_apikey'
		), 'compare-amazon', 'compare-amazon' );
		add_settings_field( 'compare-amazon-secretkey', __( 'Amazon Secret Key', 'compare' ), array(
			$this,
			'compare_amz_secretkey'
		), 'compare-amazon', 'compare-amazon' );
		add_settings_field( 'compare-amazon-trackingid', __( 'Amazon Tracking ID', 'compare' ), array(
			$this,
			'compare_amz_trackingid'
		), 'compare-amazon', 'compare-amazon' );
		add_settings_field( 'compare-amazon-country', __( 'Amazon Country', 'compare' ), array(
			$this,
			'compare_amz_country'
		), 'compare-amazon', 'compare-amazon' );

	}

	public function compare_amz_country() {
		$country = array( 'Amazon.com', 'Amazon.ca', 'Amazon.br', 'Amazon.com.mx', 'Amazon.co.uk', 'Amazon.de', 'Amazon.fr', 'Amazon.es', 'Amazon.it', 'Amazon.co.jp', 'Amazon.cn', 'Amazon.in' );
		?>
		<select name="compare-amazon[country]">
			<?php
			foreach ( $country as $amazon){
				?>
				<option value="<?php echo $amazon ?>" <?php selected( $amazon, $this->amz['country']) ?>><?php echo $amazon ?></option>
				<?php
			}
			?>
		</select>
		<?php
	}

	public function compare_amz_trackingid() {
		if ( ! empty( $this->amz['trackingid'] ) ) {
			$value = 'value="' . $this->amz['trackingid'] . '"';
		}
		?>
		<input type="text" name="compare-amazon[trackingid]" <?php echo $value ?>>
		<?php
	}

	public function compare_amz_apikey() {
		if ( ! empty( $this->amz['apikey'] ) ) {
			$value = 'value="' . $this->amz['apikey'] . '"';
		}
		?>
		<input type="text" name="compare-amazon[apikey]" <?php echo $value ?>>
		<?php
	}

	public function compare_amz_secretkey() {
		if ( ! empty( $this->amz['secretkey'] ) ) {
			$value = 'value="tropfastoche"';
		}
		?>
		<input type="password" name="compare-amazon[secretkey]" <?php echo $value ?>>
		<?php
	}
}

new Amazon();
