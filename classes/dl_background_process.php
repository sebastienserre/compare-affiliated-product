<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.
/**
 * Class dlBackground_process
 */
class DlBackground_Process extends WP_Background_Process {

	protected $action = 'download_url';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $item ) {

		$key       = $item['key'];
		$url       = $item['url'];
		$temp_file = download_url( $url, 300 );
		if ( ! is_wp_error( $temp_file ) ) {
			// Array based on $_FILE as seen in PHP file uploads
			$file = array(
				//'name'     => basename($url), // ex: wp-header-logo.png
				'name'     => $this->awin['customer_id'] . '-' . $key . '.gz', // ex: wp-header-logo.png
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


		}

		return false;
	}

	protected function complete(){
		parent::complete();
		$awin = new Awin();
		$awin->compare_register_prod();

	}

}