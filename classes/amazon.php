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
		add_shortcode( 'compare_amz_shortcode', array( $this, 'amz_shortcode' ) );
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
		$country = array(
			'amazon.com',
			'amazon.ca',
			'amazon.br',
			'amazon.com.mx',
			'amazon.co.uk',
			'amazon.de',
			'amazon.fr',
			'amazon.es',
			'amazon.it',
			'amazon.co.jp',
			'amazon.cn',
			'amazon.in'
		);
		if ( ! empty( $this->amz['country'] ) ) {
			$value = $this->amz['country'];
		}
		?>
		<select name="compare-amazon[country]">
			<?php
			foreach ( $country as $amazon ) {
				?>
				<option value="<?php echo $amazon ?>" <?php if ( ! empty( $value ) ) {
					selected( $amazon, $value );
				} ?>><?php echo $amazon ?></option>
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
			$value = 'value="' . $this->amz['secretkey'] . '"';
		}
		?>
		<input type="password" name="compare-amazon[secretkey]" <?php echo $value ?>>
		<?php
	}

	/**
	 * @param $asin
	 *
	 * @return array|mixed|null|object
	 */
	public function compare_get_amz_data( $asin ) {

		$data = get_transient('amz-' . $asin);

		if (! empty( $data ) ){
			return $data;
		}

		$access_key_id = $this->amz['apikey'];
		$secret_key    = $this->amz['secretkey'];
		$country       = $this->amz['country'];
		$endpoint      = 'webservices.' . $country;

		$uri = "/onca/xml";

		$params = array(
			"Service"        => "AWSECommerceService",
			"Operation"      => "ItemLookup",
			"AWSAccessKeyId" => $access_key_id,
			"AssociateTag"   => $asin,
			"ItemId"         => $asin,
			"IdType"         => "ASIN",
			"ResponseGroup"  => "Images,ItemAttributes,Offers"
		);

		// Set current timestamp if not set
		if ( ! isset( $params["Timestamp"] ) ) {
			$params["Timestamp"] = gmdate( 'Y-m-d\TH:i:s\Z' );
		}

// Sort the parameters by key
		ksort( $params );

		$pairs = array();

		foreach ( $params as $key => $value ) {
			array_push( $pairs, rawurlencode( $key ) . "=" . rawurlencode( $value ) );
		}

// Generate the canonical query
		$canonical_query_string = join( "&", $pairs );

// Generate the string to be signed
		$string_to_sign = "GET\n" . $endpoint . "\n" . $uri . "\n" . $canonical_query_string;

// Generate the signature required by the Product Advertising API
		$signature = base64_encode( hash_hmac( "sha256", $string_to_sign, $secret_key, true ) );

// Generate the signed URL
		$request_url = 'https://' . $endpoint . $uri . '?' . $canonical_query_string . '&Signature=' . rawurlencode( $signature );

		$xmlfile = file_get_contents( $request_url );
		$obj     = simplexml_load_string( $xmlfile );
		$json    = json_encode( $obj );
		$data    = json_decode( $json, true );

		set_transient('amz-' . $asin, $data, HOUR_IN_SECONDS * 4 );
		return $data;
	}

	public function amz_shortcode( $atts ) {
		$atts = shortcode_atts( array(
			'product' => '',
		), $atts, 'compare_amz_shortcode' );

		$data = $this->compare_get_amz_data( $atts['product'] );


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
							if (is_array( $data['Items']['Item']['ItemAttributes']['Feature'] ) ){
								foreach ( $data['Items']['Item']['ItemAttributes']['Feature'] as $feature ){
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
						$tag = $this->amz['trackingid'];
						$url = $data['Items']['Item']['Offers']['MoreOffersUrl'];
						$url = add_query_arg('tag', $tag, $url);

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
						$price = explode(' ', $price );
						$price = $price[1] . ' ' . $currency;
						?>
						<div class="compare-price-partner compare-others">
							<div class="product-price">
								<a href="<?php echo $url; ?>">
									<?php echo $price; ?>
								</a>
								<p><?php echo esc_attr( $data['Items']['Item']['Offers']['Offer']['OfferListing']['Availability'] )?></p>
							</div>
							<div class="button-partner">
								<button style=" background:<?php echo $bg; ?>; color: <?php echo $color; ?>; "><a class="btn-compare">
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
}

new Amazon();
