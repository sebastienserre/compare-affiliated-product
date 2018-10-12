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



		return false;
	}

	protected function complete(){
		parent::complete();
		$awin = new Awin();
		$awin->compare_register_prod();

	}

}