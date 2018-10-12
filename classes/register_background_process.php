<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

class register_background_process extends WP_Background_Process {
	protected $action = 'register_data';

	protected function task( $item ){


		$partner_details = Awin::compare_get_awin_partners( $value );
		foreach ( $partner_details as $partner_detail){
			$partner_details = $partner_detail;
		}
		$event = 'start partner ' . $value;
		error_log( $event );
		$upload = $path['path'] . '/xml/' . $customer_id . '-' . $value . '.gz';

		$xml = new XMLReader();
		$xml->open( 'compress.zlib://' . $upload );
		$xml->read();

		while ( $xml->read() && 'prod' !== $xml->name ) {
			;
		}

		while ( 'prod' === $xml->name ) {
			$element       = new SimpleXMLElement( $xml->readOuterXML() );
			$code_partners = explode( 'feedId=', $element->uri->awImage );
			$code_partners = explode( '&k=', $code_partners[1] );
			$code_partners = $code_partners[0];

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
				'partner_code' => $code_partners,
			);

			//$wpdb->show_errors();
			$insert = $wpdb->insert( $table, $prod );
			//error_log( $wpdb->print_error() );

			$xml->next( 'prod' );
		}
		$event = 'stop partner ' . $value;
		error_log( $event );

		return false;
	}

}