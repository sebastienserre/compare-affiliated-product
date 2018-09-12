<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

add_action( 'admin_menu', 'compare_settings' );
function compare_settings() {
	add_options_page( __( 'Compare Settings', 'compare' ), __( 'Compare Settings', 'compare' ), 'manage_options', 'compare-settings', 'compare_settings_page' );
}

function compare_settings_page() {
	$tabs = array(
		'general' => __( 'general', 'compare' ),
		'awin'    => 'Awin',
		'help'    => __( 'help', 'compare' )
	);
	if ( isset( $_GET['tab'] ) ) {

		$active_tab = $_GET['tab'];

	} else {
		$active_tab = 'general';
	}
	?>
	<div class="wrap">
		<h2><?php _e( 'Settings', 'compare' ); ?></h2>
		<!--<div class="description">This is description of the page.</div>-->
		<?php settings_errors(); ?>

		<h2 class="nav-tab-wrapper">
			<?php
			foreach ( $tabs as $tab => $value ) {
				?>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=compare-settings&tab=' . $tab ) ); ?>"
				   class="nav-tab <?php echo $active_tab === $tab ? 'nav-tab-active' : ''; ?>"><?php echo $value ?></a>
			<?php } ?>
		</h2>

		<form method="post" action="options.php">
			<?php

			switch ( $active_tab ) {
				case 'general':
					settings_fields( 'general' );
					do_settings_sections( 'compare-general' );
					break;
				case 'awin':
					settings_fields( 'awin' );
					do_settings_sections( 'compare-awin' );
					break;
				case 'zanox':
					settings_fields( 'zanox' );
					do_settings_sections( 'compare-zanox' );
					break;
				case 'help':
					settings_fields( 'compare-help' );
					do_settings_sections( 'compare-help' );
					break;
			}
			submit_button( 'Save Changes', 'primary', 'save_compare_settings' );
			?>
		</form>


	</div>
	<?php
}

add_action( 'admin_init', 'compare_register_settings' );
function compare_register_settings() {
	add_settings_section( 'compare-zanox', '', '', 'compare-zanox' );
	add_settings_section( 'compare-awin', '', '', 'compare-awin' );
	add_settings_section( 'compare-general', '', '', 'compare-general' );
	add_settings_section( 'compare-help', '', 'compare_help', 'compare-help' );

	register_setting( 'zanox', 'zanox' );
	register_setting( 'awin', 'awin' );
	register_setting( 'general', 'general' );
	register_setting( 'compare-help', 'help' );

	add_settings_field( 'compare-zanox-connect', __( 'ConnectID', 'compare' ), 'compare_zanox_connectID', 'compare-zanox', 'compare-zanox' );
	add_settings_field( 'compare-zanox-secret', __( 'SecretID', 'compare' ), 'compare_zanox_secretID', 'compare-zanox', 'compare-zanox' );

	add_settings_field( 'compare-awin-api', __( 'API Key', 'compare' ), 'compare_awin_key', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-partner', __( 'Awin partner Code', 'compare' ), 'compare_awin_partner', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-partner_logo', __( 'Awin partner logo', 'compare' ), 'compare_awin_partner_logo', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-partner_url', __( 'Awin Trademark code', 'compare' ), 'compare_awin_partner_url', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-id', __( 'Awin Customer Code', 'compare' ), 'compare_awin_id', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-feed', '', 'compare_awin_feed', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-feed-reset', __( 'Reload data', 'compare' ), 'compare_reset_awin_df_settings', 'compare-awin', 'compare-awin' );

	add_settings_field( 'compare-general-currency', __( 'Currency Unit', 'compare' ), 'compare_currency_unit', 'compare-general', 'compare-general' );
	add_settings_field( 'compare-general-language', __( 'Languages', 'compare' ), 'compare_general_languages', 'compare-general', 'compare-general' );
	add_settings_field( 'compare-general-delete', __( 'Delete All Datas when delete this plugin', 'compare' ), 'compare_general_delete', 'compare-general', 'compare-general' );
}

function compare_zanox_connectID() {
	$connect = get_option( 'zanox' );
	if ( ! empty( $connect ) ) {
		$value = 'value="' . esc_attr( $connect['connectID'] ) . '"';
	}
	?>
	<input type="text" name="zanox[connectID]" <?php echo $value; ?>>
	<?php
}

function compare_zanox_secretID() {
	$secret = get_option( 'zanox' );
	if ( ! empty( $secret ) ) {
		$value = 'value="' . esc_attr( $secret['secretID'] ) . '"';
	}
	?>
	<input type="text" name="zanox[secretID]" <?php echo $value; ?>>
	<?php
}

function compare_awin_feed() {
	$feed = get_option( 'awin' );
	if ( ! empty( $feed ) ) {
		$value = $feed['datafeed'];
	}
	$partners  = $feed['partner'];
	$partners  = explode( ',', $partners );
	$general   = get_option( 'general' );
	$lang      = $general['languages'];
	$trademark = $feed['trademark_code'];

	foreach ( $partners as $partner ) {
		$url = 'https://productdata.awin.com/datafeed/download/apikey/' . $feed['apikey'] . '/language/' . $lang . '/fid/' . $partner . '/bid/' . $trademark . '/columns/aw_deep_link,product_name,aw_product_id,merchant_product_id,merchant_image_url,description,merchant_category,search_price,merchant_name,merchant_id,category_name,category_id,aw_image_url,currency,store_price,delivery_cost,merchant_deep_link,language,last_updated,upc,ean,product_GTIN/format/xml/dtd/1.5/compression/gzip/';
		?>
		<div hidden>
			<?php
			echo $partner;
			?>
			<textarea name="awin[datafeed][<?php echo $partner ?>]" rows="4"
			          cols="150"><?php echo esc_attr( $url ); ?></textarea>
		</div>
		<?php
	}

}

function compare_awin_id() {
	$feed = get_option( 'awin' );
	if ( ! empty( $feed ) ) {
		$value = esc_attr( $feed['customer_id'] );
	}
	?>
	<input type="text" name="awin[customer_id]" value="<?php echo esc_attr( $value ) ?>">
	<?php
}


function compare_awin_key() {
	$feed = get_option( 'awin' );
	if ( ! empty( $feed ) ) {
		$value = esc_attr( $feed['apikey'] );
	}
	?>
	<input type="text" name="awin[apikey]" value="<?php echo esc_attr( $value ) ?>">
	<?php
}

function compare_awin_partner() {
	$feed = get_option( 'awin' );
	if ( ! empty( $feed ) ) {
		$value = esc_attr( $feed['partner'] );
	}
	?>
	<input type="text" name="awin[partner]" value="<?php echo esc_attr( $value ) ?>">
	<?php
}

function compare_awin_partner_logo() {
	$awin     = get_option( 'awin' );
	$partners = explode( ',', $awin['partner'] );

	foreach ( $partners as $partner ) {
		$value = 'value="' . $awin['partner_logo'][ $partner ] . '"';
		?>
		<div class="compare-partners-datafeed">
			<?php
			echo $partner;
			?>
			<input type="text" name="awin[partner_logo][<?php echo $partner; ?>]" <?php echo $value; ?>><img
					width="40px" src="<?php echo $awin['partner_logo'][ $partner ]; ?>">
		</div>
		<?php
	}
}

function compare_awin_partner_url() {
	$awin     = get_option( 'awin' );

		$value = 'value="' . $awin['trademark_code'] . '"';
		?>
		<div class="compare-partners-datafeed">
			<input type="text" name="awin[trademark_code]" <?php echo $value; ?>>
		</div>
		<?php
}


function compare_general_delete() {
	$general = get_option( 'general' );
	if ( !isset( $general['delete'] ) || empty( $general['delete'] ) ) {
		$general['delete'] = 'no';
	}
	?>
	<input type="checkbox" value="yes" name="general[delete]" <?php checked( $general['delete'], 'yes' ); ?>>
	<?php
}

function compare_currency_unit() {
	$currency = array(
		'AFN' => 'Afghan Afghani',
		'AFA' => 'Afghan Afghani (1927–2002)',
		'ALL' => 'Albanian Lek',
		'ALK' => 'Albanian Lek (1946–1965)',
		'DZD' => 'Algerian Dinar',
		'ADP' => 'Andorran Peseta',
		'AOA' => 'Angolan Kwanza',
		'AOK' => 'Angolan Kwanza (1977–1991)',
		'AON' => 'Angolan New Kwanza (1990–2000)',
		'AOR' => 'Angolan Readjusted Kwanza (1995–1999)',
		'ARA' => 'Argentine Austral',
		'ARS' => 'Argentine Peso',
		'ARM' => 'Argentine Peso (1881–1970)',
		'ARP' => 'Argentine Peso (1983–1985)',
		'ARL' => 'Argentine Peso Ley (1970–1983)',
		'AMD' => 'Armenian Dram',
		'AWG' => 'Aruban Florin',
		'AUD' => 'Australian Dollar',
		'ATS' => 'Austrian Schilling',
		'AZN' => 'Azerbaijani Manat',
		'AZM' => 'Azerbaijani Manat (1993–2006)',
		'BSD' => 'Bahamian Dollar',
		'BHD' => 'Bahraini Dinar',
		'BDT' => 'Bangladeshi Taka',
		'BBD' => 'Barbadian Dollar',
		'BYN' => 'Belarusian Ruble',
		'BYB' => 'Belarusian Ruble (1994–1999)',
		'BYR' => 'Belarusian Ruble (2000–2016)',
		'BEF' => 'Belgian Franc',
		'BEC' => 'Belgian Franc (convertible)',
		'BEL' => 'Belgian Franc (financial)',
		'BZD' => 'Belize Dollar',
		'BMD' => 'Bermudan Dollar',
		'BTN' => 'Bhutanese Ngultrum',
		'BOB' => 'Bolivian Boliviano',
		'BOL' => 'Bolivian Boliviano (1863–1963)',
		'BOV' => 'Bolivian Mvdol',
		'BOP' => 'Bolivian Peso',
		'BAM' => 'Bosnia-Herzegovina Convertible Mark',
		'BAD' => 'Bosnia-Herzegovina Dinar (1992–1994)',
		'BAN' => 'Bosnia-Herzegovina New Dinar (1994–1997)',
		'BWP' => 'Botswanan Pula',
		'BRC' => 'Brazilian Cruzado (1986–1989)',
		'BRZ' => 'Brazilian Cruzeiro (1942–1967)',
		'BRE' => 'Brazilian Cruzeiro (1990–1993)',
		'BRR' => 'Brazilian Cruzeiro (1993–1994)',
		'BRN' => 'Brazilian New Cruzado (1989–1990)',
		'BRB' => 'Brazilian New Cruzeiro (1967–1986)',
		'BRL' => 'Brazilian Real',
		'GBP' => 'British Pound',
		'BND' => 'Brunei Dollar',
		'BGL' => 'Bulgarian Hard Lev',
		'BGN' => 'Bulgarian Lev',
		'BGO' => 'Bulgarian Lev (1879–1952)',
		'BGM' => 'Bulgarian Socialist Lev',
		'BUK' => 'Burmese Kyat',
		'BIF' => 'Burundian Franc',
		'XPF' => 'CFP Franc',
		'KHR' => 'Cambodian Riel',
		'CAD' => 'Canadian Dollar',
		'CVE' => 'Cape Verdean Escudo',
		'KYD' => 'Cayman Islands Dollar',
		'XAF' => 'Central African CFA Franc',
		'CLE' => 'Chilean Escudo',
		'CLP' => 'Chilean Peso',
		'CLF' => 'Chilean Unit of Account (UF)',
		'CNX' => 'Chinese People’s Bank Dollar',
		'CNY' => 'Chinese Yuan',
		'COP' => 'Colombian Peso',
		'COU' => 'Colombian Real Value Unit',
		'KMF' => 'Comorian Franc',
		'CDF' => 'Congolese Franc',
		'CRC' => 'Costa Rican Colón',
		'HRD' => 'Croatian Dinar',
		'HRK' => 'Croatian Kuna',
		'CUC' => 'Cuban Convertible Peso',
		'CUP' => 'Cuban Peso',
		'CYP' => 'Cypriot Pound',
		'CZK' => 'Czech Koruna',
		'CSK' => 'Czechoslovak Hard Koruna',
		'DKK' => 'Danish Krone',
		'DJF' => 'Djiboutian Franc',
		'DOP' => 'Dominican Peso',
		'NLG' => 'Dutch Guilder',
		'XCD' => 'East Caribbean Dollar',
		'DDM' => 'East German Mark',
		'ECS' => 'Ecuadorian Sucre',
		'ECV' => 'Ecuadorian Unit of Constant Value',
		'EGP' => 'Egyptian Pound',
		'GQE' => 'Equatorial Guinean Ekwele',
		'ERN' => 'Eritrean Nakfa',
		'EEK' => 'Estonian Kroon',
		'ETB' => 'Ethiopian Birr',
		'EUR' => 'Euro',
		'XEU' => 'European Currency Unit',
		'FKP' => 'Falkland Islands Pound',
		'FJD' => 'Fijian Dollar',
		'FIM' => 'Finnish Markka',
		'FRF' => 'French Franc',
		'XFO' => 'French Gold Franc',
		'XFU' => 'French UIC-Franc',
		'GMD' => 'Gambian Dalasi',
		'GEK' => 'Georgian Kupon Larit',
		'GEL' => 'Georgian Lari',
		'DEM' => 'German Mark',
		'GHS' => 'Ghanaian Cedi',
		'GHC' => 'Ghanaian Cedi (1979–2007)',
		'GIP' => 'Gibraltar Pound',
		'GRD' => 'Greek Drachma',
		'GTQ' => 'Guatemalan Quetzal',
		'GWP' => 'Guinea-Bissau Peso',
		'GNF' => 'Guinean Franc',
		'GNS' => 'Guinean Syli',
		'GYD' => 'Guyanaese Dollar',
		'HTG' => 'Haitian Gourde',
		'HNL' => 'Honduran Lempira',
		'HKD' => 'Hong Kong Dollar',
		'HUF' => 'Hungarian Forint',
		'ISK' => 'Icelandic Króna',
		'ISJ' => 'Icelandic Króna (1918–1981)',
		'INR' => 'Indian Rupee',
		'IDR' => 'Indonesian Rupiah',
		'IRR' => 'Iranian Rial',
		'IQD' => 'Iraqi Dinar',
		'IEP' => 'Irish Pound',
		'ILS' => 'Israeli New Shekel',
		'ILP' => 'Israeli Pound',
		'ILR' => 'Israeli Shekel (1980–1985)',
		'ITL' => 'Italian Lira',
		'JMD' => 'Jamaican Dollar',
		'JPY' => 'Japanese Yen',
		'JOD' => 'Jordanian Dinar',
		'KZT' => 'Kazakhstani Tenge',
		'KES' => 'Kenyan Shilling',
		'KWD' => 'Kuwaiti Dinar',
		'KGS' => 'Kyrgystani Som',
		'LAK' => 'Laotian Kip',
		'LVL' => 'Latvian Lats',
		'LVR' => 'Latvian Ruble',
		'LBP' => 'Lebanese Pound',
		'LSL' => 'Lesotho Loti',
		'LRD' => 'Liberian Dollar',
		'LYD' => 'Libyan Dinar',
		'LTL' => 'Lithuanian Litas',
		'LTT' => 'Lithuanian Talonas',
		'LUL' => 'Luxembourg Financial Franc',
		'LUC' => 'Luxembourgian Convertible Franc',
		'LUF' => 'Luxembourgian Franc',
		'MOP' => 'Macanese Pataca',
		'MKD' => 'Macedonian Denar',
		'MKN' => 'Macedonian Denar (1992–1993)',
		'MGA' => 'Malagasy Ariary',
		'MGF' => 'Malagasy Franc',
		'MWK' => 'Malawian Kwacha',
		'MYR' => 'Malaysian Ringgit',
		'MVR' => 'Maldivian Rufiyaa',
		'MVP' => 'Maldivian Rupee (1947–1981)',
		'MLF' => 'Malian Franc',
		'MTL' => 'Maltese Lira',
		'MTP' => 'Maltese Pound',
		'MRO' => 'Mauritanian Ouguiya',
		'MUR' => 'Mauritian Rupee',
		'MXV' => 'Mexican Investment Unit',
		'MXN' => 'Mexican Peso',
		'MXP' => 'Mexican Silver Peso (1861–1992)',
		'MDC' => 'Moldovan Cupon',
		'MDL' => 'Moldovan Leu',
		'MCF' => 'Monegasque Franc',
		'MNT' => 'Mongolian Tugrik',
		'MAD' => 'Moroccan Dirham',
		'MAF' => 'Moroccan Franc',
		'MZE' => 'Mozambican Escudo',
		'MZN' => 'Mozambican Metical',
		'MZM' => 'Mozambican Metical (1980–2006)',
		'MMK' => 'Myanmar Kyat',
		'NAD' => 'Namibian Dollar',
		'NPR' => 'Nepalese Rupee',
		'ANG' => 'Netherlands Antillean Guilder',
		'TWD' => 'New Taiwan Dollar',
		'NZD' => 'New Zealand Dollar',
		'NIO' => 'Nicaraguan Córdoba',
		'NIC' => 'Nicaraguan Córdoba (1988–1991)',
		'NGN' => 'Nigerian Naira',
		'KPW' => 'North Korean Won',
		'NOK' => 'Norwegian Krone',
		'OMR' => 'Omani Rial',
		'PKR' => 'Pakistani Rupee',
		'PAB' => 'Panamanian Balboa',
		'PGK' => 'Papua New Guinean Kina',
		'PYG' => 'Paraguayan Guarani',
		'PEI' => 'Peruvian Inti',
		'PEN' => 'Peruvian Sol',
		'PES' => 'Peruvian Sol (1863–1965)',
		'PHP' => 'Philippine Peso',
		'PLN' => 'Polish Zloty',
		'PLZ' => 'Polish Zloty (1950–1995)',
		'PTE' => 'Portuguese Escudo',
		'GWE' => 'Portuguese Guinea Escudo',
		'QAR' => 'Qatari Rial',
		'XRE' => 'RINET Funds',
		'RHD' => 'Rhodesian Dollar',
		'RON' => 'Romanian Leu',
		'ROL' => 'Romanian Leu (1952–2006)',
		'RUB' => 'Russian Ruble',
		'RUR' => 'Russian Ruble (1991–1998)',
		'RWF' => 'Rwandan Franc',
		'SVC' => 'Salvadoran Colón',
		'WST' => 'Samoan Tala',
		'SAR' => 'Saudi Riyal',
		'RSD' => 'Serbian Dinar',
		'CSD' => 'Serbian Dinar (2002–2006)',
		'SCR' => 'Seychellois Rupee',
		'SLL' => 'Sierra Leonean Leone',
		'SGD' => 'Singapore Dollar',
		'SKK' => 'Slovak Koruna',
		'SIT' => 'Slovenian Tolar',
		'SBD' => 'Solomon Islands Dollar',
		'SOS' => 'Somali Shilling',
		'ZAR' => 'South African Rand',
		'ZAL' => 'South African Rand (financial)',
		'KRH' => 'South Korean Hwan (1953–1962)',
		'KRW' => 'South Korean Won',
		'KRO' => 'South Korean Won (1945–1953)',
		'SSP' => 'South Sudanese Pound',
		'SUR' => 'Soviet Rouble',
		'ESP' => 'Spanish Peseta',
		'ESA' => 'Spanish Peseta (A account)',
		'ESB' => 'Spanish Peseta (convertible account)',
		'LKR' => 'Sri Lankan Rupee',
		'SHP' => 'St. Helena Pound',
		'SDD' => 'Sudanese Dinar (1992–2007)',
		'SDG' => 'Sudanese Pound',
		'SDP' => 'Sudanese Pound (1957–1998)',
		'SRD' => 'Surinamese Dollar',
		'SRG' => 'Surinamese Guilder',
		'SZL' => 'Swazi Lilangeni',
		'SEK' => 'Swedish Krona',
		'CHF' => 'Swiss Franc',
		'SYP' => 'Syrian Pound',
		'STD' => 'São Tomé & Príncipe Dobra',
		'TJR' => 'Tajikistani Ruble',
		'TJS' => 'Tajikistani Somoni',
		'TZS' => 'Tanzanian Shilling',
		'THB' => 'Thai Baht',
		'TPE' => 'Timorese Escudo',
		'TOP' => 'Tongan Paʻanga',
		'TTD' => 'Trinidad & Tobago Dollar',
		'TND' => 'Tunisian Dinar',
		'TRY' => 'Turkish Lira',
		'TRL' => 'Turkish Lira (1922–2005)',
		'TMT' => 'Turkmenistani Manat',
		'TMM' => 'Turkmenistani Manat (1993–2009)',
		'USD' => 'US Dollar',
		'USN' => 'US Dollar (Next day)',
		'USS' => 'US Dollar (Same day)',
		'UGX' => 'Ugandan Shilling',
		'UGS' => 'Ugandan Shilling (1966–1987)',
		'UAH' => 'Ukrainian Hryvnia',
		'UAK' => 'Ukrainian Karbovanets',
		'AED' => 'United Arab Emirates Dirham',
		'UYU' => 'Uruguayan Peso',
		'UYP' => 'Uruguayan Peso (1975–1993)',
		'UYI' => 'Uruguayan Peso (Indexed Units)',
		'UZS' => 'Uzbekistani Som',
		'VUV' => 'Vanuatu Vatu',
		'VEF' => 'Venezuelan Bolívar',
		'VEB' => 'Venezuelan Bolívar (1871–2008)',
		'VND' => 'Vietnamese Dong',
		'VNN' => 'Vietnamese Dong (1978–1985)',
		'CHE' => 'WIR Euro',
		'CHW' => 'WIR Franc',
		'XOF' => 'West African CFA Franc',
		'YDD' => 'Yemeni Dinar',
		'YER' => 'Yemeni Rial',
		'YUN' => 'Yugoslavian Convertible Dinar (1990–1992)',
		'YUD' => 'Yugoslavian Hard Dinar (1966–1990)',
		'YUM' => 'Yugoslavian New Dinar (1994–2002)',
		'YUR' => 'Yugoslavian Reformed Dinar (1992–1993)',
		'ZRN' => 'Zairean New Zaire (1993–1998)',
		'ZRZ' => 'Zairean Zaire (1971–1993)',
		'ZMW' => 'Zambian Kwacha',
		'ZMK' => 'Zambian Kwacha (1968–2012)',
		'ZWD' => 'Zimbabwean Dollar (1980–2008)',
		'ZWR' => 'Zimbabwean Dollar (2008)',
		'ZWL' => 'Zimbabwean Dollar (2009)',
	);
	$general  = get_option( 'general' );
	?>
	<select name="general[currency]">
		<option><?php _e( 'Choose your currency', 'compare' ); ?></option>
		<?php
		foreach ( $currency as $key => $curr ) {
			?>
			<option value="<?php echo $key; ?>" <?php selected( $general['currency'], $key ); ?> ><?php echo $curr; ?></option>
			<?php
		}

		?>
	</select>
	<?php
}

function compare_reset_awin_df_settings() {

	?>
	<a href="<?php echo add_query_arg( array(
		'page'  => 'compare-settings',
		'reset' => 'ok'
	), admin_url( '/options-general.php' ) ); ?>"><?php _e( 'Delete & reload feed in database' ); ?></a>

	<?php
}

add_action( 'admin_init', 'compare_reset_awin_datafeed' );
function compare_reset_awin_datafeed() {
	if ( isset( $_GET['reset'] ) && $_GET['reset'] == 'ok' ) {
		$awin = new Awin();
		$awin->compare_schedule_awin();

	}
}

function compare_general_languages() {
	$lang = array(
		'ab'      => 'Abkhazian',
		'ace'     => 'Achinese',
		'ach'     => 'Acoli',
		'ada'     => 'Adangme',
		'ady'     => 'Adyghe',
		'aa'      => 'Afar',
		'afh'     => 'Afrihili',
		'af'      => 'Afrikaans',
		'agq'     => 'Aghem',
		'ain'     => 'Ainu',
		'ak'      => 'Akan',
		'akk'     => 'Akkadian',
		'bss'     => 'Akoose',
		'akz'     => 'Alabama',
		'sq'      => 'Albanian',
		'ale'     => 'Aleut',
		'arq'     => 'Algerian Arabic',
		'en_US'   => 'American English',
		'ase'     => 'American Sign Language',
		'am'      => 'Amharic',
		'egy'     => 'Ancient Egyptian',
		'grc'     => 'Ancient Greek',
		'anp'     => 'Angika',
		'njo'     => 'Ao Naga',
		'ar'      => 'Arabic',
		'an'      => 'Aragonese',
		'arc'     => 'Aramaic',
		'aro'     => 'Araona',
		'arp'     => 'Arapaho',
		'arw'     => 'Arawak',
		'hy'      => 'Armenian',
		'rup'     => 'Aromanian',
		'frp'     => 'Arpitan',
		'as'      => 'Assamese',
		'ast'     => 'Asturian',
		'asa'     => 'Asu',
		'cch'     => 'Atsam',
		'en_AU'   => 'Australian English',
		'de_AT'   => 'Austrian German',
		'av'      => 'Avaric',
		'ae'      => 'Avestan',
		'awa'     => 'Awadhi',
		'ay'      => 'Aymara',
		'az'      => 'Azerbaijani',
		'bfq'     => 'Badaga',
		'ksf'     => 'Bafia',
		'bfd'     => 'Bafut',
		'bqi'     => 'Bakhtiari',
		'ban'     => 'Balinese',
		'bal'     => 'Baluchi',
		'bm'      => 'Bambara',
		'bax'     => 'Bamun',
		'bjn'     => 'Banjar',
		'bas'     => 'Basaa',
		'ba'      => 'Bashkir',
		'eu'      => 'Basque',
		'bbc'     => 'Batak Toba',
		'bar'     => 'Bavarian',
		'bej'     => 'Beja',
		'be'      => 'Belarusian',
		'bem'     => 'Bemba',
		'bez'     => 'Bena',
		'bn'      => 'Bengali',
		'bew'     => 'Betawi',
		'bho'     => 'Bhojpuri',
		'bik'     => 'Bikol',
		'bin'     => 'Bini',
		'bpy'     => 'Bishnupriya',
		'bi'      => 'Bislama',
		'byn'     => 'Blin',
		'zbl'     => 'Blissymbols',
		'brx'     => 'Bodo',
		'bs'      => 'Bosnian',
		'brh'     => 'Brahui',
		'bra'     => 'Braj',
		'pt_BR'   => 'Brazilian Portuguese',
		'br'      => 'Breton',
		'en_GB'   => 'British English',
		'bug'     => 'Buginese',
		'bg'      => 'Bulgarian',
		'bum'     => 'Bulu',
		'bua'     => 'Buriat',
		'my'      => 'Burmese',
		'cad'     => 'Caddo',
		'frc'     => 'Cajun French',
		'en_CA'   => 'Canadian English',
		'fr_CA'   => 'Canadian French',
		'yue'     => 'Cantonese',
		'cps'     => 'Capiznon',
		'car'     => 'Carib',
		'ca'      => 'Catalan',
		'cay'     => 'Cayuga',
		'ceb'     => 'Cebuano',
		'tzm'     => 'Central Atlas Tamazight',
		'dtp'     => 'Central Dusun',
		'ckb'     => 'Central Kurdish',
		'esu'     => 'Central Yupik',
		'shu'     => 'Chadian Arabic',
		'chg'     => 'Chagatai',
		'ch'      => 'Chamorro',
		'ce'      => 'Chechen',
		'chr'     => 'Cherokee',
		'chy'     => 'Cheyenne',
		'chb'     => 'Chibcha',
		'cgg'     => 'Chiga',
		'qug'     => 'Chimborazo Highland Quichua',
		'zh'      => 'Chinese',
		'chn'     => 'Chinook Jargon',
		'chp'     => 'Chipewyan',
		'cho'     => 'Choctaw',
		'cu'      => 'Church Slavic',
		'chk'     => 'Chuukese',
		'cv'      => 'Chuvash',
		'nwc'     => 'Classical Newari',
		'syc'     => 'Classical Syriac',
		'ksh'     => 'Colognian',
		'swb'     => 'Comorian',
		'swc'     => 'Congo Swahili',
		'cop'     => 'Coptic',
		'kw'      => 'Cornish',
		'co'      => 'Corsican',
		'cr'      => 'Cree',
		'mus'     => 'Creek',
		'crh'     => 'Crimean Turkish',
		'hr'      => 'Croatian',
		'cs'      => 'Czech',
		'dak'     => 'Dakota',
		'da'      => 'Danish',
		'dar'     => 'Dargwa',
		'dzg'     => 'Dazaga',
		'del'     => 'Delaware',
		'din'     => 'Dinka',
		'dv'      => 'Divehi',
		'doi'     => 'Dogri',
		'dgr'     => 'Dogrib',
		'dua'     => 'Duala',
		'nl'      => 'Dutch',
		'dyu'     => 'Dyula',
		'dz'      => 'Dzongkha',
		'frs'     => 'Eastern Frisian',
		'efi'     => 'Efik',
		'arz'     => 'Egyptian Arabic',
		'eka'     => 'Ekajuk',
		'elx'     => 'Elamite',
		'ebu'     => 'Embu',
		'egl'     => 'Emilian',
		'en'      => 'English',
		'myv'     => 'Erzya',
		'eo'      => 'Esperanto',
		'et'      => 'Estonian',
		'pt_PT'   => 'European Portuguese',
		'es_ES'   => 'European Spanish',
		'ee'      => 'Ewe',
		'ewo'     => 'Ewondo',
		'ext'     => 'Extremaduran',
		'fan'     => 'Fang',
		'fat'     => 'Fanti',
		'fo'      => 'Faroese',
		'hif'     => 'Fiji Hindi',
		'fj'      => 'Fijian',
		'fil'     => 'Filipino',
		'fi'      => 'Finnish',
		'nl_BE'   => 'Flemish',
		'fon'     => 'Fon',
		'gur'     => 'Frafra',
		'fr'      => 'French',
		'fur'     => 'Friulian',
		'ff'      => 'Fulah',
		'gaa'     => 'Ga',
		'gag'     => 'Gagauz',
		'gl'      => 'Galician',
		'gan'     => 'Gan Chinese',
		'lg'      => 'Ganda',
		'gay'     => 'Gayo',
		'gba'     => 'Gbaya',
		'gez'     => 'Geez',
		'ka'      => 'Georgian',
		'de'      => 'German',
		'aln'     => 'Gheg Albanian',
		'bbj'     => 'Ghomala',
		'glk'     => 'Gilaki',
		'gil'     => 'Gilbertese',
		'gom'     => 'Goan Konkani',
		'gon'     => 'Gondi',
		'gor'     => 'Gorontalo',
		'got'     => 'Gothic',
		'grb'     => 'Grebo',
		'el'      => 'Greek',
		'gn'      => 'Guarani',
		'gu'      => 'Gujarati',
		'guz'     => 'Gusii',
		'gwi'     => 'Gwichʼin',
		'hai'     => 'Haida',
		'ht'      => 'Haitian',
		'hak'     => 'Hakka Chinese',
		'ha'      => 'Hausa',
		'haw'     => 'Hawaiian',
		'he'      => 'Hebrew',
		'hz'      => 'Herero',
		'hil'     => 'Hiligaynon',
		'hi'      => 'Hindi',
		'ho'      => 'Hiri Motu',
		'hit'     => 'Hittite',
		'hmn'     => 'Hmong',
		'hu'      => 'Hungarian',
		'hup'     => 'Hupa',
		'iba'     => 'Iban',
		'ibb'     => 'Ibibio',
		'is'      => 'Icelandic',
		'io'      => 'Ido',
		'ig'      => 'Igbo',
		'ilo'     => 'Iloko',
		'smn'     => 'Inari Sami',
		'id'      => 'Indonesian',
		'izh'     => 'Ingrian',
		'inh'     => 'Ingush',
		'ia'      => 'Interlingua',
		'ie'      => 'Interlingue',
		'iu'      => 'Inuktitut',
		'ik'      => 'Inupiaq',
		'ga'      => 'Irish',
		'it'      => 'Italian',
		'jam'     => 'Jamaican Creole English',
		'ja'      => 'Japanese',
		'jv'      => 'Javanese',
		'kaj'     => 'Jju',
		'dyo'     => 'Jola-Fonyi',
		'jrb'     => 'Judeo-Arabic',
		'jpr'     => 'Judeo-Persian',
		'jut'     => 'Jutish',
		'kbd'     => 'Kabardian',
		'kea'     => 'Kabuverdianu',
		'kab'     => 'Kabyle',
		'kac'     => 'Kachin',
		'kgp'     => 'Kaingang',
		'kkj'     => 'Kako',
		'kl'      => 'Kalaallisut',
		'kln'     => 'Kalenjin',
		'xal'     => 'Kalmyk',
		'kam'     => 'Kamba',
		'kbl'     => 'Kanembu',
		'kn'      => 'Kannada',
		'kr'      => 'Kanuri',
		'kaa'     => 'Kara-Kalpak',
		'krc'     => 'Karachay-Balkar',
		'krl'     => 'Karelian',
		'ks'      => 'Kashmiri',
		'csb'     => 'Kashubian',
		'kaw'     => 'Kawi',
		'kk'      => 'Kazakh',
		'ken'     => 'Kenyang',
		'kha'     => 'Khasi',
		'km'      => 'Khmer',
		'kho'     => 'Khotanese',
		'khw'     => 'Khowar',
		'ki'      => 'Kikuyu',
		'kmb'     => 'Kimbundu',
		'krj'     => 'Kinaray-a',
		'rw'      => 'Kinyarwanda',
		'kiu'     => 'Kirmanjki',
		'tlh'     => 'Klingon',
		'bkm'     => 'Kom',
		'kv'      => 'Komi',
		'koi'     => 'Komi-Permyak',
		'kg'      => 'Kongo',
		'kok'     => 'Konkani',
		'ko'      => 'Korean',
		'kfo'     => 'Koro',
		'kos'     => 'Kosraean',
		'avk'     => 'Kotava',
		'khq'     => 'Koyra Chiini',
		'ses'     => 'Koyraboro Senni',
		'kpe'     => 'Kpelle',
		'kri'     => 'Krio',
		'kj'      => 'Kuanyama',
		'kum'     => 'Kumyk',
		'ku'      => 'Kurdish',
		'kru'     => 'Kurukh',
		'kut'     => 'Kutenai',
		'nmg'     => 'Kwasio',
		'ky'      => 'Kyrgyz',
		'quc'     => 'Kʼicheʼ',
		'lad'     => 'Ladino',
		'lah'     => 'Lahnda',
		'lkt'     => 'Lakota',
		'lam'     => 'Lamba',
		'lag'     => 'Langi',
		'lo'      => 'Lao',
		'ltg'     => 'Latgalian',
		'la'      => 'Latin',
		'es_419'  => 'Latin American Spanish',
		'lv'      => 'Latvian',
		'lzz'     => 'Laz',
		'lez'     => 'Lezghian',
		'lij'     => 'Ligurian',
		'li'      => 'Limburgish',
		'ln'      => 'Lingala',
		'lfn'     => 'Lingua Franca Nova',
		'lzh'     => 'Literary Chinese',
		'lt'      => 'Lithuanian',
		'liv'     => 'Livonian',
		'jbo'     => 'Lojban',
		'lmo'     => 'Lombard',
		'nds'     => 'Low German',
		'sli'     => 'Lower Silesian',
		'dsb'     => 'Lower Sorbian',
		'loz'     => 'Lozi',
		'lu'      => 'Luba-Katanga',
		'lua'     => 'Luba-Lulua',
		'lui'     => 'Luiseno',
		'smj'     => 'Lule Sami',
		'lun'     => 'Lunda',
		'luo'     => 'Luo',
		'lb'      => 'Luxembourgish',
		'luy'     => 'Luyia',
		'mde'     => 'Maba',
		'mk'      => 'Macedonian',
		'jmc'     => 'Machame',
		'mad'     => 'Madurese',
		'maf'     => 'Mafa',
		'mag'     => 'Magahi',
		'vmf'     => 'Main-Franconian',
		'mai'     => 'Maithili',
		'mak'     => 'Makasar',
		'mgh'     => 'Makhuwa-Meetto',
		'kde'     => 'Makonde',
		'mg'      => 'Malagasy',
		'ms'      => 'Malay',
		'ml'      => 'Malayalam',
		'mt'      => 'Maltese',
		'mnc'     => 'Manchu',
		'mdr'     => 'Mandar',
		'man'     => 'Mandingo',
		'mni'     => 'Manipuri',
		'gv'      => 'Manx',
		'mi'      => 'Maori',
		'arn'     => 'Mapuche',
		'mr'      => 'Marathi',
		'chm'     => 'Mari',
		'mh'      => 'Marshallese',
		'mwr'     => 'Marwari',
		'mas'     => 'Masai',
		'mzn'     => 'Mazanderani',
		'byv'     => 'Medumba',
		'men'     => 'Mende',
		'mwv'     => 'Mentawai',
		'mer'     => 'Meru',
		'mgo'     => 'Metaʼ',
		'es_MX'   => 'Mexican Spanish',
		'mic'     => 'Micmac',
		'dum'     => 'Middle Dutch',
		'enm'     => 'Middle English',
		'frm'     => 'Middle French',
		'gmh'     => 'Middle High German',
		'mga'     => 'Middle Irish',
		'nan'     => 'Min Nan Chinese',
		'min'     => 'Minangkabau',
		'xmf'     => 'Mingrelian',
		'mwl'     => 'Mirandese',
		'lus'     => 'Mizo',
		'ar_001'  => 'Modern Standard Arabic',
		'moh'     => 'Mohawk',
		'mdf'     => 'Moksha',
		'ro_MD'   => 'Moldavian',
		'lol'     => 'Mongo',
		'mn'      => 'Mongolian',
		'mfe'     => 'Morisyen',
		'ary'     => 'Moroccan Arabic',
		'mos'     => 'Mossi',
		'mul'     => 'Multiple Languages',
		'mua'     => 'Mundang',
		'ttt'     => 'Muslim Tat',
		'mye'     => 'Myene',
		'naq'     => 'Nama',
		'na'      => 'Nauru',
		'nv'      => 'Navajo',
		'ng'      => 'Ndonga',
		'nap'     => 'Neapolitan',
		'ne'      => 'Nepali',
		'new'     => 'Newari',
		'sba'     => 'Ngambay',
		'nnh'     => 'Ngiemboon',
		'jgo'     => 'Ngomba',
		'yrl'     => 'Nheengatu',
		'nia'     => 'Nias',
		'niu'     => 'Niuean',
		'zxx'     => 'No linguistic content',
		'nog'     => 'Nogai',
		'nd'      => 'North Ndebele',
		'frr'     => 'Northern Frisian',
		'se'      => 'Northern Sami',
		'nso'     => 'Northern Sotho',
		'no'      => 'Norwegian',
		'nb'      => 'Norwegian Bokmål',
		'nn'      => 'Norwegian Nynorsk',
		'nov'     => 'Novial',
		'nus'     => 'Nuer',
		'nym'     => 'Nyamwezi',
		'ny'      => 'Nyanja',
		'nyn'     => 'Nyankole',
		'tog'     => 'Nyasa Tonga',
		'nyo'     => 'Nyoro',
		'nzi'     => 'Nzima',
		'nqo'     => 'NʼKo',
		'oc'      => 'Occitan',
		'oj'      => 'Ojibwa',
		'ang'     => 'Old English',
		'fro'     => 'Old French',
		'goh'     => 'Old High German',
		'sga'     => 'Old Irish',
		'non'     => 'Old Norse',
		'peo'     => 'Old Persian',
		'pro'     => 'Old Provençal',
		'or'      => 'Oriya',
		'om'      => 'Oromo',
		'osa'     => 'Osage',
		'os'      => 'Ossetic',
		'ota'     => 'Ottoman Turkish',
		'pal'     => 'Pahlavi',
		'pfl'     => 'Palatine German',
		'pau'     => 'Palauan',
		'pi'      => 'Pali',
		'pam'     => 'Pampanga',
		'pag'     => 'Pangasinan',
		'pap'     => 'Papiamento',
		'ps'      => 'Pashto',
		'pdc'     => 'Pennsylvania German',
		'fa'      => 'Persian',
		'phn'     => 'Phoenician',
		'pcd'     => 'Picard',
		'pms'     => 'Piedmontese',
		'pdt'     => 'Plautdietsch',
		'pon'     => 'Pohnpeian',
		'pl'      => 'Polish',
		'pnt'     => 'Pontic',
		'pt'      => 'Portuguese',
		'prg'     => 'Prussian',
		'pa'      => 'Punjabi',
		'qu'      => 'Quechua',
		'raj'     => 'Rajasthani',
		'rap'     => 'Rapanui',
		'rar'     => 'Rarotongan',
		'rif'     => 'Riffian',
		'rgn'     => 'Romagnol',
		'ro'      => 'Romanian',
		'rm'      => 'Romansh',
		'rom'     => 'Romany',
		'rof'     => 'Rombo',
		'root'    => 'Root',
		'rtm'     => 'Rotuman',
		'rug'     => 'Roviana',
		'rn'      => 'Rundi',
		'ru'      => 'Russian',
		'rue'     => 'Rusyn',
		'rwk'     => 'Rwa',
		'ssy'     => 'Saho',
		'sah'     => 'Sakha',
		'sam'     => 'Samaritan Aramaic',
		'saq'     => 'Samburu',
		'sm'      => 'Samoan',
		'sgs'     => 'Samogitian',
		'sad'     => 'Sandawe',
		'sg'      => 'Sango',
		'sbp'     => 'Sangu',
		'sa'      => 'Sanskrit',
		'sat'     => 'Santali',
		'sc'      => 'Sardinian',
		'sas'     => 'Sasak',
		'sdc'     => 'Sassarese Sardinian',
		'stq'     => 'Saterland Frisian',
		'saz'     => 'Saurashtra',
		'sco'     => 'Scots',
		'gd'      => 'Scottish Gaelic',
		'sly'     => 'Selayar',
		'sel'     => 'Selkup',
		'seh'     => 'Sena',
		'see'     => 'Seneca',
		'sr'      => 'Serbian',
		'sh'      => 'Serbo-Croatian',
		'srr'     => 'Serer',
		'sei'     => 'Seri',
		'ksb'     => 'Shambala',
		'shn'     => 'Shan',
		'sn'      => 'Shona',
		'ii'      => 'Sichuan Yi',
		'scn'     => 'Sicilian',
		'sid'     => 'Sidamo',
		'bla'     => 'Siksika',
		'szl'     => 'Silesian',
		'zh_Hans' => 'Simplified Chinese',
		'sd'      => 'Sindhi',
		'si'      => 'Sinhala',
		'sms'     => 'Skolt Sami',
		'den'     => 'Slave',
		'sk'      => 'Slovak',
		'sl'      => 'Slovenian',
		'xog'     => 'Soga',
		'sog'     => 'Sogdien',
		'so'      => 'Somali',
		'snk'     => 'Soninke',
		'azb'     => 'South Azerbaijani',
		'nr'      => 'South Ndebele',
		'alt'     => 'Southern Altai',
		'sma'     => 'Southern Sami',
		'st'      => 'Southern Sotho',
		'es'      => 'Spanish',
		'srn'     => 'Sranan Tongo',
		'zgh'     => 'Standard Moroccan Tamazight',
		'suk'     => 'Sukuma',
		'sux'     => 'Sumerian',
		'su'      => 'Sundanese',
		'sus'     => 'Susu',
		'sw'      => 'Swahili',
		'ss'      => 'Swati',
		'sv'      => 'Swedish',
		'fr_CH'   => 'Swiss French',
		'gsw'     => 'Swiss German',
		'de_CH'   => 'Swiss High German',
		'syr'     => 'Syriac',
		'shi'     => 'Tachelhit',
		'tl'      => 'Tagalog',
		'ty'      => 'Tahitian',
		'dav'     => 'Taita',
		'tg'      => 'Tajik',
		'tly'     => 'Talysh',
		'tmh'     => 'Tamashek',
		'ta'      => 'Tamil',
		'trv'     => 'Taroko',
		'twq'     => 'Tasawaq',
		'tt'      => 'Tatar',
		'te'      => 'Telugu',
		'ter'     => 'Tereno',
		'teo'     => 'Teso',
		'tet'     => 'Tetum',
		'th'      => 'Thai',
		'bo'      => 'Tibetan',
		'tig'     => 'Tigre',
		'ti'      => 'Tigrinya',
		'tem'     => 'Timne',
		'tiv'     => 'Tiv',
		'tli'     => 'Tlingit',
		'tpi'     => 'Tok Pisin',
		'tkl'     => 'Tokelau',
		'to'      => 'Tongan',
		'fit'     => 'Tornedalen Finnish',
		'zh_Hant' => 'Traditional Chinese',
		'tkr'     => 'Tsakhur',
		'tsd'     => 'Tsakonian',
		'tsi'     => 'Tsimshian',
		'ts'      => 'Tsonga',
		'tn'      => 'Tswana',
		'tcy'     => 'Tulu',
		'tum'     => 'Tumbuka',
		'aeb'     => 'Tunisian Arabic',
		'tr'      => 'Turkish',
		'tk'      => 'Turkmen',
		'tru'     => 'Turoyo',
		'tvl'     => 'Tuvalu',
		'tyv'     => 'Tuvinian',
		'tw'      => 'Twi',
		'kcg'     => 'Tyap',
		'udm'     => 'Udmurt',
		'uga'     => 'Ugaritic',
		'uk'      => 'Ukrainian',
		'umb'     => 'Umbundu',
		'und'     => 'Unknown Language',
		'hsb'     => 'Upper Sorbian',
		'ur'      => 'Urdu',
		'ug'      => 'Uyghur',
		'uz'      => 'Uzbek',
		'vai'     => 'Vai',
		've'      => 'Venda',
		'vec'     => 'Venetian',
		'vep'     => 'Veps',
		'vi'      => 'Vietnamese',
		'vo'      => 'Volapük',
		'vro'     => 'Võro',
		'vot'     => 'Votic',
		'vun'     => 'Vunjo',
		'wa'      => 'Walloon',
		'wae'     => 'Walser',
		'war'     => 'Waray',
		'wbp'     => 'Warlpiri',
		'was'     => 'Washo',
		'guc'     => 'Wayuu',
		'cy'      => 'Welsh',
		'vls'     => 'West Flemish',
		'fy'      => 'Western Frisian',
		'mrj'     => 'Western Mari',
		'wal'     => 'Wolaytta',
		'wo'      => 'Wolof',
		'wuu'     => 'Wu Chinese',
		'xh'      => 'Xhosa',
		'hsn'     => 'Xiang Chinese',
		'yav'     => 'Yangben',
		'yao'     => 'Yao',
		'yap'     => 'Yapese',
		'ybb'     => 'Yemba',
		'yi'      => 'Yiddish',
		'yo'      => 'Yoruba',
		'zap'     => 'Zapotec',
		'dje'     => 'Zarma',
		'zza'     => 'Zaza',
		'zea'     => 'Zeelandic',
		'zen'     => 'Zenaga',
		'za'      => 'Zhuang',
		'gbz'     => 'Zoroastrian Dari',
		'zu'      => 'Zulu',
		'zun'     => 'Zuni',
	);

	$general = get_option( 'general' );
	?>
	<select name="general[languages]">
		<option><?php _e( 'Choose your language', 'compare' ); ?></option>
		<?php
		foreach ( $lang as $key => $lang ) {
			?>
			<option value="<?php echo $key; ?>" <?php selected( $general['languages'], $key ); ?> ><?php echo $lang; ?></option>
			<?php
		}

		?>
	</select>
	<?php
}

function compare_help() {
	$support_link = 'https://www.thivinfo.com/soumettre-un-ticket/';
	$support      = sprintf( wp_kses( __( 'If you meet a bug, you can leave me a ticket on <a href="%s" target="_blank">Thivinfo.com</a>', 'compare' ), array(
		'a' => array(
			'href'   => array(),
			'target' => array()
		)
	) ), esc_url( $support_link ) );
	?>
	<h3><?php _e( 'Welcome on the support center', 'compare' ); ?></h3>
	<p><?php echo $support; ?></p>
	<p><a href="<?php echo COMPARE_PLUGIN_URL . '/how-to/awin.html'; ?>"><?php _e('How to configure Awin', 'compare'); ?></a> </p>
	<p><a href="<?php echo COMPARE_PLUGIN_URL . '/how-to/shortcodes.html'; ?>"><?php _e('How to use the shortcodes', 'compare'); ?></a> </p>
	<p><a href="<?php echo COMPARE_PLUGIN_URL . '/how-to/hooks.html'; ?>"><?php _e('Hooks - Action & Filter', 'compare'); ?></a> </p>
	<p><a href="<?php echo COMPARE_PLUGIN_URL . '/how-to/aawp.html'; ?>"><?php _e('AAWP', 'compare'); ?></a> </p>
	<?php
}