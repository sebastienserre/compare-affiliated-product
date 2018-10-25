<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

class compare_external_db {
	private $_connection;
	private static $_instance; //The single instance
	private $_host = '';
	private $_username = '';
	private $_password = '';
	private $_database = '';
	private $connect = '';

	/*
Get an instance of the Database
@return Instance
*/
	public static function getInstance() {
		if ( ! self::$_instance ) { // If no instance then make one
			self::$_instance = new self();
		}

		return self::$_instance;
	}


	public function __construct() {
		$option   = get_option( 'compare-general' );
		if ( isset( $option['ext_check'] ) && 'on' === $option['ext_check'] ) {
			$this->compare_create_connexion();
		}
	}

	public function compare_create_connexion() {
		$this->compare_set_credentials();
		$this->_connection = new wpdb( $this->_username,
			$this->_password, $this->_database, $this->_host );
	}

	public function compare_set_credentials() {

		$option   = get_option( 'compare-general' );
		if (isset( $option['ext_check']) ) {
			$external = $option['ext_check'];
			if ( 'on' === $external ) {
				$this->_host     = $option['host'];
				$this->_database = $option['db'];
				$this->_username = $option['username'];
				$this->_password = $option['pwd'];
			}
		}

	}

	public function compare_check_sql() {
		$option        = get_option( 'compare-general' );
		$this->connect = 'ok';
		if ( isset( $option['ext_check'] ) ) {
			$external = $option['ext_check'];
		}
		//$cnx = $this->getConnection();
		if ( isset( $external ) && 'on' === $external ) {
			if ( false == $this->_connection->has_connected ) {
				$this->connect = 'nok';

			}

			return $this->connect;
		}
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

	// Magic method clone is empty to prevent duplication of connection
	private function __clone() {
	}

	// Get mysqli connection
	public function getConnection() {
		return $this->_connection;
	}

}

new compare_external_db();

