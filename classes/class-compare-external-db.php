<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

class compare_external_db {
	public $db;
	public $connect;
	public $sql;

	public function __construct() {

	}

	public function compare_external_cnx() {

		$option   = get_option( 'compare-general' );
		$external = $option['ext_check'];
		if ( 'on' === $external ) {
			$this->compare_check_sql();
			if ( 'ok' === $this->connect ) {
				$host     = $option['host'];
				$db       = $option['db'];
				$username = $option['username'];
				$password = $option['pwd'];

				$this->sql = new wpdb ( $username, $password, $db, $host );

			}

		}

		return $this->sql;
	}

	public function compare_check_sql() {
		$option        = get_option( 'compare-general' );
		$this->connect = 'ok';
		if ( isset( $option['ext_check'] ) ) {
			$external = $option['ext_check'];
		}

		if ( isset( $external ) && 'on' === $external ) {
			$host     = $option['host'];
			$db       = $option['db'];
			$username = $option['username'];
			$password = $option['pwd'];

			$sql   = new wpdb ( $username, $password, $db, $host );

			if ( ! empty ( $sql->error ) ) {
				$this->connect = 'nok';
			}

		}

		return $this->connect;

	}

	public function compare_check_html() {
		$this->compare_check_sql();
		if ( 'nok' === $this->connect ) {
			$connexion = '<div class="compare-sql-nok">' . __( 'Failed to connect to MySQL, please check your login credentials', 'compare' ) . '</div>';
		} else {
			$connexion = '<div class="compare-sql-ok">' . __( 'Succesfully connected to the database', 'compare' ) . '</div>';
		}

		return $connexion;
	}

}

new compare_external_db();

