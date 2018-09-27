<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

/**
 * Class effiliation
 *
 * @since 1.2.0
 * https://publisher.effiliation.com/
 */
class effiliation {

	/**
	 * @var string Apikey to retrieve in Effiliation.com settings page.
	 */
	protected static $apikey;

	public function __construct() {
	}

	/**
	 * @return mixed apikey for effiliation account.
	 */
	private static function compare_get_apikey() {
		$option       = get_option( 'compare-effiliation' );
		self::$apikey = $option['apikey'];

		return self::$apikey;
	}

	/**
	 * @return array array with Effiliation program datas.
	 */
	public static function compare_get_effiliation_program() {
		$apikey   = self::compare_get_apikey();
		$url      = 'https://apiv2.effiliation.com/apiv2/programs.json?key=' . $apikey . '&filter=mines';
		$response = wp_remote_get( $url, array( 'sslverify' => false, ) );
		if ( 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$body         = wp_remote_retrieve_body( $response );
			$decoded_body = json_decode( $body, true );
		}

		return $decoded_body;
	}

	public static function compare_effiliation_list_html() {
		$programs = self::compare_get_effiliation_program();
		$options  = get_option( 'compare-effiliation' );

		foreach ( $programs as $program ) {
			foreach ( $program as $prog ) {
				if ( isset( $options['programs'] ) && ! empty( $options['programs'] ) ) {
					$check = $options['programs'];
				}
				?>
				<p>
					<input type="checkbox" value="<?php echo $prog['id_session']; ?>"
					       name="compare-effiliation[programs]" <?php checked( $check, $prog['id_session'] ) ?>>
					<label for="compare-effiliation[programs]">
						<img src="<?php echo $prog['urllo']; ?>" alt="<?php echo $prog['siteannonceur']; ?>"
						     title="<?php echo $prog['siteannonceur']; ?>">
					</label>
				</p>
				<?php
			}
		}
	}
}

new effiliation();