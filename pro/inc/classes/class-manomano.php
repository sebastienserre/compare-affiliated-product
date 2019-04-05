<?php

namespace CAP\Manomano;

use function array_map;
use const COMPARE_XML_PATH;
use function delete_transient;
use function download_url;
use function error_log;
use function file_exists;
use function fopen;
use function get_transient;
use function glob;
use function is_dir;
use function is_wp_error;
use function rename;
use function unlink;
use function wp_mkdir_p;
use function wp_remote_get;
use function wp_remote_retrieve_body;
use function wp_remote_retrieve_response_code;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

/**
 * Class Manomano
 * @package CAP\Manomano
 * @description Load ManoMano Json feed in DB
 */
class Manomano {
	public function __construct() {
	//	add_action( 'admin_init', array( $this, 'register_in_db' ) );
	}

	/**
	 * @return array data from Json
	 */
	public function load_json( $json ) {
		global $wpdb;
		$table    = $wpdb->prefix . 'compare';
		$value    = get_option( 'compare-manomano' );
		$url      = $value['url'];
		$response = wp_remote_get( $url, array( 'sslverify' => false, ) );
		if ( 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$body  = wp_remote_retrieve_body( $response );
			$datas = json_decode( $body, true );
			foreach ( $datas as $data ) {
				$prod = array(
					'price'        => $data['price'],
					'title'        => $data['productname'],
					'description'  => $data['brandname'],
					'img'          => $data['image_URL'],
					'url'          => $data['product_URL'],
					'partner_name' => 'ManoMano',
					'productid'    => $data['id'],
					'ean'          => $data['EAN'],
					'platform'     => 'manomano',
					'mpn'          => $data['MPN'],
				);


				$replace   = $wpdb->replace( $table, $prod );
				$transient = get_transient( 'product_' . $datas->EAN );
				if ( ! empty( $transient ) ) {
					delete_transient( $transient );
				}
				$transient = null;
			}
		}
		error_log( 'start Manomano Registration' );
		if ( $handle ) {
			while ( ( $line = fgets( $handle ) ) !== false ) {
				$datas = json_decode( $line );


			}

			fclose( $handle );
		}
		error_log( 'stop Manomano Registration' );

	}

	public function register_in_db() {
		$data = $this->load_json( $json );
	}

}

new Manomano();
