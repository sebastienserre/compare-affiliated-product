<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

class register_background_process extends WP_Background_Process {
	protected $action = 'register_data';

	protected function task( $item ) {

		global $wpdb;
		$table       = $wpdb->prefix . 'compare';
		$value       = $item['value'];
		$customer_id = $item['customer_id'];
		$xml         = new XMLReader();

		$awin = new Awin();
		$partner_details = $awin->compare_get_awin_partners( $value );
		foreach ( $partner_details as $partner_detail ) {
			$partner_details = $partner_detail;
		}



		$path   = wp_upload_dir();
		$upload = $path['path'] . '/awin/xml/' . $customer_id . '-' . $value . '.gz';


		$xml->open( 'compress.zlib://' . $upload );
		$xml->read();

		while ( $xml->read() && 'prod' !== $xml->name ) {
			;
		}

		while ( 'prod' === $xml->name ) {

			$element = new SimpleXMLElement( $xml->readOuterXML() );

			$prod = array(
				'price'        => strval( $element->price->buynow ),
				'title'        => $element->text->name ? strval( $element->text->name ) : '',
				'description'  => strval( $element->text->desc ),
				'img'          => strval( $element->uri->mImage ),
				'url'          => strval( $element->uri->awTrack ),
				'partner_name' => $partner_details,
				'productid'    => strval( $xml->getAttribute( 'id' ) ),
				'ean'          => strval( $element->ean ),
				'platform'     => 'Awin',
				'partner_code' => $value,
			);

			$insert = $wpdb->insert( $table, $prod );

			$xml->next( 'prod' );


		}
		return false;

	}
}
