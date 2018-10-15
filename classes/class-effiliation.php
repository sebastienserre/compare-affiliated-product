<?php

/**
 * Include wp-load only if triggered by cli
 */
if( 'cli' === php_sapi_name() ) {

	function find_wordpress_base_path() {
		$dir = dirname( __FILE__ );
		do {
			//it is possible to check for other files here
			if ( file_exists( $dir . "/wp-config.php" ) ) {
				return $dir;
			}
		} while ( $dir = realpath( "$dir/.." ) );

		return null;
	}

	define( 'BASE_PATH', find_wordpress_base_path() . "/" );
	define( 'WP_USE_THEMES', false );
	global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
	require BASE_PATH . 'wp-load.php';
} elseif ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

/**
 * Class effiliation
 *
 * @since 1.2.0
 * https://publisher.effiliation.com/
 */
class Effiliation {

	/**
	 * @var string Apikey to retrieve in Effiliation.com settings page.
	 */
	protected static $apikey;

	public function __construct() {
		add_action( 'compare_fourhour_event', array( $this, 'compare_effiliation_set_cron' ) );
		add_action( 'compare_twice_event', array( $this, 'compare_effiliation_set_cron' ) );
		add_action( 'compare_daily_event', array( $this, 'compare_effiliation_set_cron' ) );
	}

	public function compare_reset_effiliation_feed() {
			$this->compare_schedule_effiliation();
	}

	public function compare_effiliation_set_cron() {
		$option = get_option( 'compare-general' );
		if ( ! isset( $option['platform']['effiliation'] ) ){
			return;
		}
		$cron   = $option['cron'];
		switch ( $cron ) {
			case 'four':
				$this->compare_schedule_effiliation();
				break;
			case 'twice':
				$this->compare_schedule_effiliation();
				break;
			case 'daily':
				$this->compare_schedule_effiliation();
				break;
			case 'none':
				break;
		}

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
					$check = $options['programs'][ $prog['siteannonceur'] ];
				}
				$img = update_option("compare-general[partner_logo][$prog[id_programme]]['img']", $prog['urllo'] );
				$name = update_option("compare-general[partner_logo][$prog[id_programme]]['name']", $prog['siteannonceur'] );
				?>
				<p>
					<input type="checkbox" value="<?php echo $prog['siteannonceur']; ?>"
					       name="compare-effiliation[programs][<?php echo $prog['siteannonceur'] ?>]" <?php checked( $check, $prog['siteannonceur'] ) ?>>
					<label for="compare-effiliation[programs][<?php $prog['siteannonceur'] ?>]">
						<img src="<?php echo $prog['urllo']; ?>" alt="<?php echo $prog['siteannonceur']; ?>"
						     title="<?php echo $prog['siteannonceur']; ?>">
					</label>
				</p>
				<?php
			}
		}
		?>
		<p><?php _e( 'Check the program to display', 'compare' ); ?></p>
<?php
	}

	public static function compare_effiliation_get_feed() {
		$apikey   = self::compare_get_apikey();
		$url      = 'https://apiv2.effiliation.com/apiv2/productfeeds.json?key=' . $apikey . '&filter=mines';
		$response = wp_remote_get( $url, array( 'sslverify' => false, ) );
		if ( 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$body         = wp_remote_retrieve_body( $response );
			$decoded_body = json_decode( $body, true );
		}

		foreach ( $decoded_body['feeds'] as $feeds ) {
			$urls[ $feeds['site_affilieur'] ] = $feeds['code'];
		}

		return $urls;
	}

	/**
	 * @param string $dir wp upload dir
	 *
	 * @return array $dir new wp upload dir
	 */
	public function compare_upload_effiliation_dir( $dir ) {
		$mkdir = wp_mkdir_p( $dir['path'] . '/effiliation/xml' );
		if ( ! $mkdir ) {
			wp_mkdir_p( $dir['path'] . '/effiliation/xml' );
		}
		$dir =
			array(
				'path'   => $dir['path'] . '/effiliation/xml',
				'url'    => $dir['url'] . '/effiliation/xml',
				'subdir' => $dir['path'] . '/effiliation/xml',
			) + $dir;

		return $dir;
	}

	/**
	 * Download and unzip xml from EffiLes règles de chasse varient selon les territoires, les associations ou sociétés de chasses, les zones privées ou publiques, les règlements locaux… Voilà pourquoi il est difficile de s’en faire une idée précise sur internet. Mais il y a toujours un « tronc commun » préfectoral : Les tirs en direction des habitations, des routes, sont formellement interdits… On peut donc partir en battue au ras de la propriété privée, et tirer, mais toujours dos à celle-ci.
Que dit la loi générale : « au titre de la police de chasse, il n’y a pas de distance déterminée autour des habitations » (sous réserve bien sûr d’être dos à l’habitation)… Trois choses peuvent modifier cela :
1)      Un arrêté municipal pour déterminer un périmètre de tir (200m). Les raisons doivent être motivées par des circonstances locales et précises de sécurité particulière. Cela signifie qu’il ne suffit pas de d’écrire « pour raisons de sécurité » ; encore moins « parce que cela gêne le voisinage »... La chasse en France est un droit règlementé. Pour info, il n’y a pas sur Thiverval-Grignon d’arrêté de ce type, parce qu’il n’y a pas de « circonstances locales particulières » qui le permettent.
2)      Un zonage soumis à l’action « ACCA ». Dans ces zonages, on ne peut pas tirer à moins de 150m des habitations. Pour info, nous ne sommes pas en zonage ACCA.
3)      Un règlement local de l’association ou société de chasse locale… Ici nous sommes concernés car la société de chasse de la Commune a règlementé l’interdiction de tir à moins de 100m des habitations dans ses statuts.
Mais attention ! Ces règles sont applicables sur les domaines de chasses publiques. Il existe aussi les chasses privées, sur domaines privés. Et nous sommes concernés par l’AgroParisTech qui dispose sur la Commune de sa propre société de chasse « ministérielle ». Et je pense que vous avez eu affaire à elle car ils n’ont pas, eux, de règlement local fixant une limite de distance tirs, et ils appliquent la Loi stricto-senso.
Quelles sont les zones ? Si vous êtes dos à la RD 119, Folleville devant vous… Les terres à droite du parc (jusqu’au futur golf) sont du domaine de la société de chasse communale. A gauche du parc (jusqu’au rond-point et entre les deux ronds-points), ce sont des terres appartenant à l’Agro et donc de leur propre société de chasse.

Voilà ce je pouvais vous dire en matière de règlementation et de cas de figure sur notre Commune. Je peux vous dire aussi que votre mail m’a alerté sur le sujet et qu’avec le programme d’habitat sur Folleville je vais demander un règlement de chasse au Ministère de l’agriculture, fixant une limite de tirs pour ses chasseurs. De là vous dire que j’arriverai à quelque chose, il y a un pas que je ne franchirai pas aujourd’hui… mais je suis pugnace ! et j’ai ce matin même demandé un RV au responsable local de la société (garde-chasse Agro). En tout état de cause, avec certitude n’attendons rien d’officiel pour cette année alors que la période de chasse est commencée ; je connais les lenteurs de l’Administration et il y a longtemps que je ne « rêve » plus sur ce sujet.liation
	 */
	public function compare_schedule_effiliation() {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		add_filter( 'upload_dir', array( $this, 'compare_upload_effiliation_dir' ) );
		define( 'ALLOW_UNFILTERED_UPLOADS', true );

		$urls = self::compare_effiliation_get_feed();
		$path = wp_upload_dir();
		$path = $path['path'];
		if ( file_exists( $path ) && is_dir( $path ) ) {
			array_map( 'unlink', glob( $path . '/*' ) );
		} else {
			wp_mkdir_p( $path );
		}

		$secondes = apply_filters( 'compare_time_limit', 600 );
		set_time_limit( $secondes );
		error_log( 'Start Download Effiliation Feed' );
		foreach ( $urls as $key => $url ) {
			$temp_file = download_url( $url, 300 );
			if ( ! is_wp_error( $temp_file ) ) {
				// Array based on $_FILE as seen in PHP file uploads
				$file = array(
					'name'     => $key . '.gz', // ex: wp-header-logo.png
					'type'     => 'application/gzip',
					'tmp_name' => $temp_file,
					'error'    => 0,
					'size'     => filesize( $temp_file ),
				);

				$overrides = array(
					'test_form' => false,
					'test_size' => true,
				);

				// Move the temporary file into the uploads directory
				$results = wp_handle_sideload( $file, $overrides );

			} else {
				error_log( $temp_file->errors['http_request_failed'][0] );
			}
		}
		error_log( 'Stop Download Effiliation Feed' );
		remove_filter( 'upload_dir', array( $this, 'compare_upload_dir' ) );
		$this->compare_effiliation_register();
	}

	public function compare_effiliation_register() {
		global $wpdb;
		$programs = self::compare_get_effiliation_program();
		error_log( 'start Effiliation Import' );
		$table = $wpdb->prefix . 'compare';

		$truncat  = $wpdb->query( 'DELETE FROM ' . $table . ' WHERE `platform` LIKE "effiliation"' );
		$path     = wp_upload_dir();
		$secondes = apply_filters( 'compare_time_limit', 600 );
		set_time_limit( $secondes );
		foreach ( $programs['programs'] as $program ) {
			$event = 'start partner ' . $program['siteannonceur'];
			error_log( $event );
			$upload  = $path['path'] . '/' . $program['siteannonceur'] . '.gz';
			$new_xml = file_get_contents( 'compress.zlib://' . $upload );
			//$xml = new SimpleXMLElement();
			libxml_use_internal_errors( false );
			$element = simplexml_load_string( $new_xml, 'SimpleXMLElement' );

			libxml_clear_errors();

			foreach ( $element->product as $prod ) {
				$prod = array(
					'price'        => strval( $prod->price ),
					'title'        => strval( $prod->name ),
					'description'  => strval( $prod->description ),
					'img'          => strval( $prod->url_image ),
					'url'          => strval( $prod->url_product ),
					'partner_name' => $program['siteannonceur'],
					'partner_code' => $program['id_programme'],
					'productid'    => strval( $prod->sku ),
					'ean'          => strval( $prod->ean ),
					'platform'     => 'effiliation',
				);

				$wpdb->show_errors( true );
				$wpdb->show_errors(true);
				$insert =$wpdb->replace( $table, $prod );
				$transient = get_transient( 'product_' . strval( $prod['ean'] ) );
				if (! empty( $transient ) ){
					delete_transient( $transient );
				}
			}
		}
		error_log( 'stop Effiliation Import' );
	}

}

new Effiliation();