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
	private $option;

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
		$this->option   = get_option( 'compare-advanced' );
		if ( isset( $this->option['ext_check'] ) && 'on' === $this->option['ext_check'] ) {
			$this->compare_create_connexion();
		}
	}

	public function compare_create_connexion() {
		$this->compare_set_credentials();
		$this->_connection = new wpdb( $this->_username,
			$this->_password, $this->_database, $this->_host );
	}

	public function compare_set_credentials() {

		if (isset( $this->option['ext_check']) ) {
			$external = $this->option['ext_check'];
			if ( 'on' === $external ) {
				$this->_host     = $this->option['host'];
				$this->_database = $this->option['db'];
				$this->_username = $this->option['username'];
				$this->_password = $this->option['pwd'];
			}
		}

	}

	public function compare_check_sql() {
		$this->connect = 'ok';
		if ( isset( $this->option['ext_check'] ) ) {
			$external = $this->option['ext_check'];
		}
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
			$connexion = '<div class="compare-sql-nok">' . __( 'Failed to connect to MySQL, please check your login credentials', 'compare-affiliated-products' ) . '</div>';
		} else {
			$connexion = '<div class="compare-sql-ok">' . __( 'Succesfully connected to the database', 'compare-affiliated-products' ) . '</div>';
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

