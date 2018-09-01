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
		'general' =>__('general', 'compare'),
		'awin' => 'Awin',
		'help' => __('help', 'compare')
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
	add_settings_field( 'compare-awin-id', __( 'Awin Customer Code', 'compare' ), 'compare_awin_id', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-feed', '', 'compare_awin_feed', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-feed-reset', __('Reload data','compare'), 'compare_reset_awin_df_settings', 'compare-awin', 'compare-awin' );

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
	$partners = $feed['partner'];
	$partners = explode( ',', $partners );
	$general = get_option( 'general' );
	$lang = $general['languages'];

	foreach ( $partners as $partner ) {
		$url = 'https://productdata.awin.com/datafeed/download/apikey/' . $feed['apikey'] . '/language/' . $lang . '/fid/' . $partner . '/bid/63565,63241,63245,65403,63579,63559,64567,63315,63353,55949,66429,63369,63745,66569,63393,63435,63479,60351,67471,60541,63511,63515/columns/aw_deep_link,product_name,aw_product_id,merchant_product_id,merchant_image_url,description,merchant_category,search_price,merchant_name,merchant_id,category_name,category_id,aw_image_url,currency,store_price,delivery_cost,merchant_deep_link,language,last_updated,upc,ean,product_GTIN/format/xml/dtd/1.5/compression/gzip/';
		?>
		<div>
		<textarea name="awin[datafeed][<?php echo $partner ?>]" rows="4"
		          cols="150" hidden><?php echo esc_attr( $url ); ?></textarea>
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
	$awin = get_option('awin');
	$partners = explode( ',', $awin['partner'] );

	foreach ( $partners as $partner ){
			$value = 'value="' . $awin['partner_logo'][ $partner ].'"';
		?>
		<div>
		<?php
		echo $partner;
		?>
		<input type="text" name="awin[partner_logo][<?php echo $partner; ?>]" <?php echo $value; ?>>
		</div>
		<?php
	}
}


function compare_general_delete() {
	$general = get_option( 'general' );
	?>
	<input type="checkbox" value="yes" name="general[delete]" <?php checked( $general['delete'], 'yes' ); ?>>
	<?php
}

function compare_currency_unit() {
	$currency = array(
		'AFN' => __( 'Afghan Afghani', 'compare' ),
		'AFA' => __( 'Afghan Afghani (1927–2002)', 'compare' ),
		'ALL' => __( 'Albanian Lek', 'compare' ),
		'ALK' => __( 'Albanian Lek (1946–1965)', 'compare' ),
		'DZD' => __( 'Algerian Dinar', 'compare' ),
		'ADP' => __( 'Andorran Peseta', 'compare' ),
		'AOA' => __( 'Angolan Kwanza', 'compare' ),
		'AOK' => __( 'Angolan Kwanza (1977–1991)', 'compare' ),
		'AON' => __( 'Angolan New Kwanza (1990–2000)', 'compare' ),
		'AOR' => __( 'Angolan Readjusted Kwanza (1995–1999)', 'compare' ),
		'ARA' => __( 'Argentine Austral', 'compare' ),
		'ARS' => __( 'Argentine Peso', 'compare' ),
		'ARM' => __( 'Argentine Peso (1881–1970)', 'compare' ),
		'ARP' => __( 'Argentine Peso (1983–1985)', 'compare' ),
		'ARL' => __( 'Argentine Peso Ley (1970–1983)', 'compare' ),
		'AMD' => __( 'Armenian Dram', 'compare' ),
		'AWG' => __( 'Aruban Florin', 'compare' ),
		'AUD' => __( 'Australian Dollar', 'compare' ),
		'ATS' => __( 'Austrian Schilling', 'compare' ),
		'AZN' => __( 'Azerbaijani Manat', 'compare' ),
		'AZM' => __( 'Azerbaijani Manat (1993–2006)', 'compare' ),
		'BSD' => __( 'Bahamian Dollar', 'compare' ),
		'BHD' => __( 'Bahraini Dinar', 'compare' ),
		'BDT' => __( 'Bangladeshi Taka', 'compare' ),
		'BBD' => __( 'Barbadian Dollar', 'compare' ),
		'BYN' => __( 'Belarusian Ruble', 'compare' ),
		'BYB' => __( 'Belarusian Ruble (1994–1999)', 'compare' ),
		'BYR' => __( 'Belarusian Ruble (2000–2016)', 'compare' ),
		'BEF' => __( 'Belgian Franc', 'compare' ),
		'BEC' => __( 'Belgian Franc (convertible)', 'compare' ),
		'BEL' => __( 'Belgian Franc (financial)', 'compare' ),
		'BZD' => __( 'Belize Dollar', 'compare' ),
		'BMD' => __( 'Bermudan Dollar', 'compare' ),
		'BTN' => __( 'Bhutanese Ngultrum', 'compare' ),
		'BOB' => __( 'Bolivian Boliviano', 'compare' ),
		'BOL' => __( 'Bolivian Boliviano (1863–1963)', 'compare' ),
		'BOV' => __( 'Bolivian Mvdol', 'compare' ),
		'BOP' => __( 'Bolivian Peso', 'compare' ),
		'BAM' => __( 'Bosnia-Herzegovina Convertible Mark', 'compare' ),
		'BAD' => __( 'Bosnia-Herzegovina Dinar (1992–1994)', 'compare' ),
		'BAN' => __( 'Bosnia-Herzegovina New Dinar (1994–1997)', 'compare' ),
		'BWP' => __( 'Botswanan Pula', 'compare' ),
		'BRC' => __( 'Brazilian Cruzado (1986–1989)', 'compare' ),
		'BRZ' => __( 'Brazilian Cruzeiro (1942–1967)', 'compare' ),
		'BRE' => __( 'Brazilian Cruzeiro (1990–1993)', 'compare' ),
		'BRR' => __( 'Brazilian Cruzeiro (1993–1994)', 'compare' ),
		'BRN' => __( 'Brazilian New Cruzado (1989–1990)', 'compare' ),
		'BRB' => __( 'Brazilian New Cruzeiro (1967–1986)', 'compare' ),
		'BRL' => __( 'Brazilian Real', 'compare' ),
		'GBP' => __( 'British Pound', 'compare' ),
		'BND' => __( 'Brunei Dollar', 'compare' ),
		'BGL' => __( 'Bulgarian Hard Lev', 'compare' ),
		'BGN' => __( 'Bulgarian Lev', 'compare' ),
		'BGO' => __( 'Bulgarian Lev (1879–1952)', 'compare' ),
		'BGM' => __( 'Bulgarian Socialist Lev', 'compare' ),
		'BUK' => __( 'Burmese Kyat', 'compare' ),
		'BIF' => __( 'Burundian Franc', 'compare' ),
		'XPF' => __( 'CFP Franc', 'compare' ),
		'KHR' => __( 'Cambodian Riel', 'compare' ),
		'CAD' => __( 'Canadian Dollar', 'compare' ),
		'CVE' => __( 'Cape Verdean Escudo', 'compare' ),
		'KYD' => __( 'Cayman Islands Dollar', 'compare' ),
		'XAF' => __( 'Central African CFA Franc', 'compare' ),
		'CLE' => __( 'Chilean Escudo', 'compare' ),
		'CLP' => __( 'Chilean Peso', 'compare' ),
		'CLF' => __( 'Chilean Unit of Account (UF)', 'compare' ),
		'CNX' => __( 'Chinese People’s Bank Dollar', 'compare' ),
		'CNY' => __( 'Chinese Yuan', 'compare' ),
		'COP' => __( 'Colombian Peso', 'compare' ),
		'COU' => __( 'Colombian Real Value Unit', 'compare' ),
		'KMF' => __( 'Comorian Franc', 'compare' ),
		'CDF' => __( 'Congolese Franc', 'compare' ),
		'CRC' => __( 'Costa Rican Colón', 'compare' ),
		'HRD' => __( 'Croatian Dinar', 'compare' ),
		'HRK' => __( 'Croatian Kuna', 'compare' ),
		'CUC' => __( 'Cuban Convertible Peso', 'compare' ),
		'CUP' => __( 'Cuban Peso', 'compare' ),
		'CYP' => __( 'Cypriot Pound', 'compare' ),
		'CZK' => __( 'Czech Koruna', 'compare' ),
		'CSK' => __( 'Czechoslovak Hard Koruna', 'compare' ),
		'DKK' => __( 'Danish Krone', 'compare' ),
		'DJF' => __( 'Djiboutian Franc', 'compare' ),
		'DOP' => __( 'Dominican Peso', 'compare' ),
		'NLG' => __( 'Dutch Guilder', 'compare' ),
		'XCD' => __( 'East Caribbean Dollar', 'compare' ),
		'DDM' => __( 'East German Mark', 'compare' ),
		'ECS' => __( 'Ecuadorian Sucre', 'compare' ),
		'ECV' => __( 'Ecuadorian Unit of Constant Value', 'compare' ),
		'EGP' => __( 'Egyptian Pound', 'compare' ),
		'GQE' => __( 'Equatorial Guinean Ekwele', 'compare' ),
		'ERN' => __( 'Eritrean Nakfa', 'compare' ),
		'EEK' => __( 'Estonian Kroon', 'compare' ),
		'ETB' => __( 'Ethiopian Birr', 'compare' ),
		'EUR' => __( 'Euro', 'compare' ),
		'XEU' => __( 'European Currency Unit', 'compare' ),
		'FKP' => __( 'Falkland Islands Pound', 'compare' ),
		'FJD' => __( 'Fijian Dollar', 'compare' ),
		'FIM' => __( 'Finnish Markka', 'compare' ),
		'FRF' => __( 'French Franc', 'compare' ),
		'XFO' => __( 'French Gold Franc', 'compare' ),
		'XFU' => __( 'French UIC-Franc', 'compare' ),
		'GMD' => __( 'Gambian Dalasi', 'compare' ),
		'GEK' => __( 'Georgian Kupon Larit', 'compare' ),
		'GEL' => __( 'Georgian Lari', 'compare' ),
		'DEM' => __( 'German Mark', 'compare' ),
		'GHS' => __( 'Ghanaian Cedi', 'compare' ),
		'GHC' => __( 'Ghanaian Cedi (1979–2007)', 'compare' ),
		'GIP' => __( 'Gibraltar Pound', 'compare' ),
		'GRD' => __( 'Greek Drachma', 'compare' ),
		'GTQ' => __( 'Guatemalan Quetzal', 'compare' ),
		'GWP' => __( 'Guinea-Bissau Peso', 'compare' ),
		'GNF' => __( 'Guinean Franc', 'compare' ),
		'GNS' => __( 'Guinean Syli', 'compare' ),
		'GYD' => __( 'Guyanaese Dollar', 'compare' ),
		'HTG' => __( 'Haitian Gourde', 'compare' ),
		'HNL' => __( 'Honduran Lempira', 'compare' ),
		'HKD' => __( 'Hong Kong Dollar', 'compare' ),
		'HUF' => __( 'Hungarian Forint', 'compare' ),
		'ISK' => __( 'Icelandic Króna', 'compare' ),
		'ISJ' => __( 'Icelandic Króna (1918–1981)', 'compare' ),
		'INR' => __( 'Indian Rupee', 'compare' ),
		'IDR' => __( 'Indonesian Rupiah', 'compare' ),
		'IRR' => __( 'Iranian Rial', 'compare' ),
		'IQD' => __( 'Iraqi Dinar', 'compare' ),
		'IEP' => __( 'Irish Pound', 'compare' ),
		'ILS' => __( 'Israeli New Shekel', 'compare' ),
		'ILP' => __( 'Israeli Pound', 'compare' ),
		'ILR' => __( 'Israeli Shekel (1980–1985)', 'compare' ),
		'ITL' => __( 'Italian Lira', 'compare' ),
		'JMD' => __( 'Jamaican Dollar', 'compare' ),
		'JPY' => __( 'Japanese Yen', 'compare' ),
		'JOD' => __( 'Jordanian Dinar', 'compare' ),
		'KZT' => __( 'Kazakhstani Tenge', 'compare' ),
		'KES' => __( 'Kenyan Shilling', 'compare' ),
		'KWD' => __( 'Kuwaiti Dinar', 'compare' ),
		'KGS' => __( 'Kyrgystani Som', 'compare' ),
		'LAK' => __( 'Laotian Kip', 'compare' ),
		'LVL' => __( 'Latvian Lats', 'compare' ),
		'LVR' => __( 'Latvian Ruble', 'compare' ),
		'LBP' => __( 'Lebanese Pound', 'compare' ),
		'LSL' => __( 'Lesotho Loti', 'compare' ),
		'LRD' => __( 'Liberian Dollar', 'compare' ),
		'LYD' => __( 'Libyan Dinar', 'compare' ),
		'LTL' => __( 'Lithuanian Litas', 'compare' ),
		'LTT' => __( 'Lithuanian Talonas', 'compare' ),
		'LUL' => __( 'Luxembourg Financial Franc', 'compare' ),
		'LUC' => __( 'Luxembourgian Convertible Franc', 'compare' ),
		'LUF' => __( 'Luxembourgian Franc', 'compare' ),
		'MOP' => __( 'Macanese Pataca', 'compare' ),
		'MKD' => __( 'Macedonian Denar', 'compare' ),
		'MKN' => __( 'Macedonian Denar (1992–1993)', 'compare' ),
		'MGA' => __( 'Malagasy Ariary', 'compare' ),
		'MGF' => __( 'Malagasy Franc', 'compare' ),
		'MWK' => __( 'Malawian Kwacha', 'compare' ),
		'MYR' => __( 'Malaysian Ringgit', 'compare' ),
		'MVR' => __( 'Maldivian Rufiyaa', 'compare' ),
		'MVP' => __( 'Maldivian Rupee (1947–1981)', 'compare' ),
		'MLF' => __( 'Malian Franc', 'compare' ),
		'MTL' => __( 'Maltese Lira', 'compare' ),
		'MTP' => __( 'Maltese Pound', 'compare' ),
		'MRO' => __( 'Mauritanian Ouguiya', 'compare' ),
		'MUR' => __( 'Mauritian Rupee', 'compare' ),
		'MXV' => __( 'Mexican Investment Unit', 'compare' ),
		'MXN' => __( 'Mexican Peso', 'compare' ),
		'MXP' => __( 'Mexican Silver Peso (1861–1992)', 'compare' ),
		'MDC' => __( 'Moldovan Cupon', 'compare' ),
		'MDL' => __( 'Moldovan Leu', 'compare' ),
		'MCF' => __( 'Monegasque Franc', 'compare' ),
		'MNT' => __( 'Mongolian Tugrik', 'compare' ),
		'MAD' => __( 'Moroccan Dirham', 'compare' ),
		'MAF' => __( 'Moroccan Franc', 'compare' ),
		'MZE' => __( 'Mozambican Escudo', 'compare' ),
		'MZN' => __( 'Mozambican Metical', 'compare' ),
		'MZM' => __( 'Mozambican Metical (1980–2006)', 'compare' ),
		'MMK' => __( 'Myanmar Kyat', 'compare' ),
		'NAD' => __( 'Namibian Dollar', 'compare' ),
		'NPR' => __( 'Nepalese Rupee', 'compare' ),
		'ANG' => __( 'Netherlands Antillean Guilder', 'compare' ),
		'TWD' => __( 'New Taiwan Dollar', 'compare' ),
		'NZD' => __( 'New Zealand Dollar', 'compare' ),
		'NIO' => __( 'Nicaraguan Córdoba', 'compare' ),
		'NIC' => __( 'Nicaraguan Córdoba (1988–1991)', 'compare' ),
		'NGN' => __( 'Nigerian Naira', 'compare' ),
		'KPW' => __( 'North Korean Won', 'compare' ),
		'NOK' => __( 'Norwegian Krone', 'compare' ),
		'OMR' => __( 'Omani Rial', 'compare' ),
		'PKR' => __( 'Pakistani Rupee', 'compare' ),
		'PAB' => __( 'Panamanian Balboa', 'compare' ),
		'PGK' => __( 'Papua New Guinean Kina', 'compare' ),
		'PYG' => __( 'Paraguayan Guarani', 'compare' ),
		'PEI' => __( 'Peruvian Inti', 'compare' ),
		'PEN' => __( 'Peruvian Sol', 'compare' ),
		'PES' => __( 'Peruvian Sol (1863–1965)', 'compare' ),
		'PHP' => __( 'Philippine Peso', 'compare' ),
		'PLN' => __( 'Polish Zloty', 'compare' ),
		'PLZ' => __( 'Polish Zloty (1950–1995)', 'compare' ),
		'PTE' => __( 'Portuguese Escudo', 'compare' ),
		'GWE' => __( 'Portuguese Guinea Escudo', 'compare' ),
		'QAR' => __( 'Qatari Rial', 'compare' ),
		'XRE' => __( 'RINET Funds', 'compare' ),
		'RHD' => __( 'Rhodesian Dollar', 'compare' ),
		'RON' => __( 'Romanian Leu', 'compare' ),
		'ROL' => __( 'Romanian Leu (1952–2006)', 'compare' ),
		'RUB' => __( 'Russian Ruble', 'compare' ),
		'RUR' => __( 'Russian Ruble (1991–1998)', 'compare' ),
		'RWF' => __( 'Rwandan Franc', 'compare' ),
		'SVC' => __( 'Salvadoran Colón', 'compare' ),
		'WST' => __( 'Samoan Tala', 'compare' ),
		'SAR' => __( 'Saudi Riyal', 'compare' ),
		'RSD' => __( 'Serbian Dinar', 'compare' ),
		'CSD' => __( 'Serbian Dinar (2002–2006)', 'compare' ),
		'SCR' => __( 'Seychellois Rupee', 'compare' ),
		'SLL' => __( 'Sierra Leonean Leone', 'compare' ),
		'SGD' => __( 'Singapore Dollar', 'compare' ),
		'SKK' => __( 'Slovak Koruna', 'compare' ),
		'SIT' => __( 'Slovenian Tolar', 'compare' ),
		'SBD' => __( 'Solomon Islands Dollar', 'compare' ),
		'SOS' => __( 'Somali Shilling', 'compare' ),
		'ZAR' => __( 'South African Rand', 'compare' ),
		'ZAL' => __( 'South African Rand (financial)', 'compare' ),
		'KRH' => __( 'South Korean Hwan (1953–1962)', 'compare' ),
		'KRW' => __( 'South Korean Won', 'compare' ),
		'KRO' => __( 'South Korean Won (1945–1953)', 'compare' ),
		'SSP' => __( 'South Sudanese Pound', 'compare' ),
		'SUR' => __( 'Soviet Rouble', 'compare' ),
		'ESP' => __( 'Spanish Peseta', 'compare' ),
		'ESA' => __( 'Spanish Peseta (A account)', 'compare' ),
		'ESB' => __( 'Spanish Peseta (convertible account)', 'compare' ),
		'LKR' => __( 'Sri Lankan Rupee', 'compare' ),
		'SHP' => __( 'St. Helena Pound', 'compare' ),
		'SDD' => __( 'Sudanese Dinar (1992–2007)', 'compare' ),
		'SDG' => __( 'Sudanese Pound', 'compare' ),
		'SDP' => __( 'Sudanese Pound (1957–1998)', 'compare' ),
		'SRD' => __( 'Surinamese Dollar', 'compare' ),
		'SRG' => __( 'Surinamese Guilder', 'compare' ),
		'SZL' => __( 'Swazi Lilangeni', 'compare' ),
		'SEK' => __( 'Swedish Krona', 'compare' ),
		'CHF' => __( 'Swiss Franc', 'compare' ),
		'SYP' => __( 'Syrian Pound', 'compare' ),
		'STD' => __( 'São Tomé & Príncipe Dobra', 'compare' ),
		'TJR' => __( 'Tajikistani Ruble', 'compare' ),
		'TJS' => __( 'Tajikistani Somoni', 'compare' ),
		'TZS' => __( 'Tanzanian Shilling', 'compare' ),
		'THB' => __( 'Thai Baht', 'compare' ),
		'TPE' => __( 'Timorese Escudo', 'compare' ),
		'TOP' => __( 'Tongan Paʻanga', 'compare' ),
		'TTD' => __( 'Trinidad & Tobago Dollar', 'compare' ),
		'TND' => __( 'Tunisian Dinar', 'compare' ),
		'TRY' => __( 'Turkish Lira', 'compare' ),
		'TRL' => __( 'Turkish Lira (1922–2005)', 'compare' ),
		'TMT' => __( 'Turkmenistani Manat', 'compare' ),
		'TMM' => __( 'Turkmenistani Manat (1993–2009)', 'compare' ),
		'USD' => __( 'US Dollar', 'compare' ),
		'USN' => __( 'US Dollar (Next day)', 'compare' ),
		'USS' => __( 'US Dollar (Same day)', 'compare' ),
		'UGX' => __( 'Ugandan Shilling', 'compare' ),
		'UGS' => __( 'Ugandan Shilling (1966–1987)', 'compare' ),
		'UAH' => __( 'Ukrainian Hryvnia', 'compare' ),
		'UAK' => __( 'Ukrainian Karbovanets', 'compare' ),
		'AED' => __( 'United Arab Emirates Dirham', 'compare' ),
		'UYU' => __( 'Uruguayan Peso', 'compare' ),
		'UYP' => __( 'Uruguayan Peso (1975–1993)', 'compare' ),
		'UYI' => __( 'Uruguayan Peso (Indexed Units)', 'compare' ),
		'UZS' => __( 'Uzbekistani Som', 'compare' ),
		'VUV' => __( 'Vanuatu Vatu', 'compare' ),
		'VEF' => __( 'Venezuelan Bolívar', 'compare' ),
		'VEB' => __( 'Venezuelan Bolívar (1871–2008)', 'compare' ),
		'VND' => __( 'Vietnamese Dong', 'compare' ),
		'VNN' => __( 'Vietnamese Dong (1978–1985)', 'compare' ),
		'CHE' => __( 'WIR Euro', 'compare' ),
		'CHW' => __( 'WIR Franc', 'compare' ),
		'XOF' => __( 'West African CFA Franc', 'compare' ),
		'YDD' => __( 'Yemeni Dinar', 'compare' ),
		'YER' => __( 'Yemeni Rial', 'compare' ),
		'YUN' => __( 'Yugoslavian Convertible Dinar (1990–1992)', 'compare' ),
		'YUD' => __( 'Yugoslavian Hard Dinar (1966–1990)', 'compare' ),
		'YUM' => __( 'Yugoslavian New Dinar (1994–2002)', 'compare' ),
		'YUR' => __( 'Yugoslavian Reformed Dinar (1992–1993)', 'compare' ),
		'ZRN' => __( 'Zairean New Zaire (1993–1998)', 'compare' ),
		'ZRZ' => __( 'Zairean Zaire (1971–1993)', 'compare' ),
		'ZMW' => __( 'Zambian Kwacha', 'compare' ),
		'ZMK' => __( 'Zambian Kwacha (1968–2012)', 'compare' ),
		'ZWD' => __( 'Zimbabwean Dollar (1980–2008)', 'compare' ),
		'ZWR' => __( 'Zimbabwean Dollar (2008)', 'compare' ),
		'ZWL' => __( 'Zimbabwean Dollar (2009)', 'compare' ),
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

function compare_reset_awin_df_settings(){

	?>
	<a href="<?php echo add_query_arg(array('page' => 'compare-settings', 'reset' => 'ok'), admin_url('/options-general.php') );?>"><?php _e('Delete & reload feed in database'); ?></a>

<?php
}

add_action( 'admin_init', 'compare_reset_awin_datafeed' );
function compare_reset_awin_datafeed() {
	if ( isset( $_GET['reset'] ) && $_GET['reset'] == 'ok' ) {
		$awin = new Awin();
		$awin->compare_schedule_awin();
		$awin->compare_register_prod();

	}
}

function compare_general_languages() {
	$lang = array (
		'ab' => __('Abkhazian', 'compare' ),
		'ace' => __('Achinese', 'compare' ),
		'ach' => __('Acoli', 'compare' ),
		'ada' => __('Adangme', 'compare' ),
		'ady' => __('Adyghe', 'compare' ),
		'aa' => __('Afar', 'compare' ),
		'afh' => __('Afrihili', 'compare' ),
		'af' => __('Afrikaans', 'compare' ),
		'agq' => __('Aghem', 'compare' ),
		'ain' => __('Ainu', 'compare' ),
		'ak' => __('Akan', 'compare' ),
		'akk' => __('Akkadian', 'compare' ),
		'bss' => __('Akoose', 'compare' ),
		'akz' => __('Alabama', 'compare' ),
		'sq' => __('Albanian', 'compare' ),
		'ale' => __('Aleut', 'compare' ),
		'arq' => __('Algerian Arabic', 'compare' ),
		'en_US' => __('American English', 'compare' ),
		'ase' => __('American Sign Language', 'compare' ),
		'am' => __('Amharic', 'compare' ),
		'egy' => __('Ancient Egyptian', 'compare' ),
		'grc' => __('Ancient Greek', 'compare' ),
		'anp' => __('Angika', 'compare' ),
		'njo' => __('Ao Naga', 'compare' ),
		'ar' => __('Arabic', 'compare' ),
		'an' => __('Aragonese', 'compare' ),
		'arc' => __('Aramaic', 'compare' ),
		'aro' => __('Araona', 'compare' ),
		'arp' => __('Arapaho', 'compare' ),
		'arw' => __('Arawak', 'compare' ),
		'hy' => __('Armenian', 'compare' ),
		'rup' => __('Aromanian', 'compare' ),
		'frp' => __('Arpitan', 'compare' ),
		'as' => __('Assamese', 'compare' ),
		'ast' => __('Asturian', 'compare' ),
		'asa' => __('Asu', 'compare' ),
		'cch' => __('Atsam', 'compare' ),
		'en_AU' => __('Australian English', 'compare' ),
		'de_AT' => __('Austrian German', 'compare' ),
		'av' => __('Avaric', 'compare' ),
		'ae' => __('Avestan', 'compare' ),
		'awa' => __('Awadhi', 'compare' ),
		'ay' => __('Aymara', 'compare' ),
		'az' => __('Azerbaijani', 'compare' ),
		'bfq' => __('Badaga', 'compare' ),
		'ksf' => __('Bafia', 'compare' ),
		'bfd' => __('Bafut', 'compare' ),
		'bqi' => __('Bakhtiari', 'compare' ),
		'ban' => __('Balinese', 'compare' ),
		'bal' => __('Baluchi', 'compare' ),
		'bm' => __('Bambara', 'compare' ),
		'bax' => __('Bamun', 'compare' ),
		'bjn' => __('Banjar', 'compare' ),
		'bas' => __('Basaa', 'compare' ),
		'ba' => __('Bashkir', 'compare' ),
		'eu' => __('Basque', 'compare' ),
		'bbc' => __('Batak Toba', 'compare' ),
		'bar' => __('Bavarian', 'compare' ),
		'bej' => __('Beja', 'compare' ),
		'be' => __('Belarusian', 'compare' ),
		'bem' => __('Bemba', 'compare' ),
		'bez' => __('Bena', 'compare' ),
		'bn' => __('Bengali', 'compare' ),
		'bew' => __('Betawi', 'compare' ),
		'bho' => __('Bhojpuri', 'compare' ),
		'bik' => __('Bikol', 'compare' ),
		'bin' => __('Bini', 'compare' ),
		'bpy' => __('Bishnupriya', 'compare' ),
		'bi' => __('Bislama', 'compare' ),
		'byn' => __('Blin', 'compare' ),
		'zbl' => __('Blissymbols', 'compare' ),
		'brx' => __('Bodo', 'compare' ),
		'bs' => __('Bosnian', 'compare' ),
		'brh' => __('Brahui', 'compare' ),
		'bra' => __('Braj', 'compare' ),
		'pt_BR' => __('Brazilian Portuguese', 'compare' ),
		'br' => __('Breton', 'compare' ),
		'en_GB' => __('British English', 'compare' ),
		'bug' => __('Buginese', 'compare' ),
		'bg' => __('Bulgarian', 'compare' ),
		'bum' => __('Bulu', 'compare' ),
		'bua' => __('Buriat', 'compare' ),
		'my' => __('Burmese', 'compare' ),
		'cad' => __('Caddo', 'compare' ),
		'frc' => __('Cajun French', 'compare' ),
		'en_CA' => __('Canadian English', 'compare' ),
		'fr_CA' => __('Canadian French', 'compare' ),
		'yue' => __('Cantonese', 'compare' ),
		'cps' => __('Capiznon', 'compare' ),
		'car' => __('Carib', 'compare' ),
		'ca' => __('Catalan', 'compare' ),
		'cay' => __('Cayuga', 'compare' ),
		'ceb' => __('Cebuano', 'compare' ),
		'tzm' => __('Central Atlas Tamazight', 'compare' ),
		'dtp' => __('Central Dusun', 'compare' ),
		'ckb' => __('Central Kurdish', 'compare' ),
		'esu' => __('Central Yupik', 'compare' ),
		'shu' => __('Chadian Arabic', 'compare' ),
		'chg' => __('Chagatai', 'compare' ),
		'ch' => __('Chamorro', 'compare' ),
		'ce' => __('Chechen', 'compare' ),
		'chr' => __('Cherokee', 'compare' ),
		'chy' => __('Cheyenne', 'compare' ),
		'chb' => __('Chibcha', 'compare' ),
		'cgg' => __('Chiga', 'compare' ),
		'qug' => __('Chimborazo Highland Quichua', 'compare' ),
		'zh' => __('Chinese', 'compare' ),
		'chn' => __('Chinook Jargon', 'compare' ),
		'chp' => __('Chipewyan', 'compare' ),
		'cho' => __('Choctaw', 'compare' ),
		'cu' => __('Church Slavic', 'compare' ),
		'chk' => __('Chuukese', 'compare' ),
		'cv' => __('Chuvash', 'compare' ),
		'nwc' => __('Classical Newari', 'compare' ),
		'syc' => __('Classical Syriac', 'compare' ),
		'ksh' => __('Colognian', 'compare' ),
		'swb' => __('Comorian', 'compare' ),
		'swc' => __('Congo Swahili', 'compare' ),
		'cop' => __('Coptic', 'compare' ),
		'kw' => __('Cornish', 'compare' ),
		'co' => __('Corsican', 'compare' ),
		'cr' => __('Cree', 'compare' ),
		'mus' => __('Creek', 'compare' ),
		'crh' => __('Crimean Turkish', 'compare' ),
		'hr' => __('Croatian', 'compare' ),
		'cs' => __('Czech', 'compare' ),
		'dak' => __('Dakota', 'compare' ),
		'da' => __('Danish', 'compare' ),
		'dar' => __('Dargwa', 'compare' ),
		'dzg' => __('Dazaga', 'compare' ),
		'del' => __('Delaware', 'compare' ),
		'din' => __('Dinka', 'compare' ),
		'dv' => __('Divehi', 'compare' ),
		'doi' => __('Dogri', 'compare' ),
		'dgr' => __('Dogrib', 'compare' ),
		'dua' => __('Duala', 'compare' ),
		'nl' => __('Dutch', 'compare' ),
		'dyu' => __('Dyula', 'compare' ),
		'dz' => __('Dzongkha', 'compare' ),
		'frs' => __('Eastern Frisian', 'compare' ),
		'efi' => __('Efik', 'compare' ),
		'arz' => __('Egyptian Arabic', 'compare' ),
		'eka' => __('Ekajuk', 'compare' ),
		'elx' => __('Elamite', 'compare' ),
		'ebu' => __('Embu', 'compare' ),
		'egl' => __('Emilian', 'compare' ),
		'en' => __('English', 'compare' ),
		'myv' => __('Erzya', 'compare' ),
		'eo' => __('Esperanto', 'compare' ),
		'et' => __('Estonian', 'compare' ),
		'pt_PT' => __('European Portuguese', 'compare' ),
		'es_ES' => __('European Spanish', 'compare' ),
		'ee' => __('Ewe', 'compare' ),
		'ewo' => __('Ewondo', 'compare' ),
		'ext' => __('Extremaduran', 'compare' ),
		'fan' => __('Fang', 'compare' ),
		'fat' => __('Fanti', 'compare' ),
		'fo' => __('Faroese', 'compare' ),
		'hif' => __('Fiji Hindi', 'compare' ),
		'fj' => __('Fijian', 'compare' ),
		'fil' => __('Filipino', 'compare' ),
		'fi' => __('Finnish', 'compare' ),
		'nl_BE' => __('Flemish', 'compare' ),
		'fon' => __('Fon', 'compare' ),
		'gur' => __('Frafra', 'compare' ),
		'fr' => __('French', 'compare' ),
		'fur' => __('Friulian', 'compare' ),
		'ff' => __('Fulah', 'compare' ),
		'gaa' => __('Ga', 'compare' ),
		'gag' => __('Gagauz', 'compare' ),
		'gl' => __('Galician', 'compare' ),
		'gan' => __('Gan Chinese', 'compare' ),
		'lg' => __('Ganda', 'compare' ),
		'gay' => __('Gayo', 'compare' ),
		'gba' => __('Gbaya', 'compare' ),
		'gez' => __('Geez', 'compare' ),
		'ka' => __('Georgian', 'compare' ),
		'de' => __('German', 'compare' ),
		'aln' => __('Gheg Albanian', 'compare' ),
		'bbj' => __('Ghomala', 'compare' ),
		'glk' => __('Gilaki', 'compare' ),
		'gil' => __('Gilbertese', 'compare' ),
		'gom' => __('Goan Konkani', 'compare' ),
		'gon' => __('Gondi', 'compare' ),
		'gor' => __('Gorontalo', 'compare' ),
		'got' => __('Gothic', 'compare' ),
		'grb' => __('Grebo', 'compare' ),
		'el' => __('Greek', 'compare' ),
		'gn' => __('Guarani', 'compare' ),
		'gu' => __('Gujarati', 'compare' ),
		'guz' => __('Gusii', 'compare' ),
		'gwi' => __('Gwichʼin', 'compare' ),
		'hai' => __('Haida', 'compare' ),
		'ht' => __('Haitian', 'compare' ),
		'hak' => __('Hakka Chinese', 'compare' ),
		'ha' => __('Hausa', 'compare' ),
		'haw' => __('Hawaiian', 'compare' ),
		'he' => __('Hebrew', 'compare' ),
		'hz' => __('Herero', 'compare' ),
		'hil' => __('Hiligaynon', 'compare' ),
		'hi' => __('Hindi', 'compare' ),
		'ho' => __('Hiri Motu', 'compare' ),
		'hit' => __('Hittite', 'compare' ),
		'hmn' => __('Hmong', 'compare' ),
		'hu' => __('Hungarian', 'compare' ),
		'hup' => __('Hupa', 'compare' ),
		'iba' => __('Iban', 'compare' ),
		'ibb' => __('Ibibio', 'compare' ),
		'is' => __('Icelandic', 'compare' ),
		'io' => __('Ido', 'compare' ),
		'ig' => __('Igbo', 'compare' ),
		'ilo' => __('Iloko', 'compare' ),
		'smn' => __('Inari Sami', 'compare' ),
		'id' => __('Indonesian', 'compare' ),
		'izh' => __('Ingrian', 'compare' ),
		'inh' => __('Ingush', 'compare' ),
		'ia' => __('Interlingua', 'compare' ),
		'ie' => __('Interlingue', 'compare' ),
		'iu' => __('Inuktitut', 'compare' ),
		'ik' => __('Inupiaq', 'compare' ),
		'ga' => __('Irish', 'compare' ),
		'it' => __('Italian', 'compare' ),
		'jam' => __('Jamaican Creole English', 'compare' ),
		'ja' => __('Japanese', 'compare' ),
		'jv' => __('Javanese', 'compare' ),
		'kaj' => __('Jju', 'compare' ),
		'dyo' => __('Jola-Fonyi', 'compare' ),
		'jrb' => __('Judeo-Arabic', 'compare' ),
		'jpr' => __('Judeo-Persian', 'compare' ),
		'jut' => __('Jutish', 'compare' ),
		'kbd' => __('Kabardian', 'compare' ),
		'kea' => __('Kabuverdianu', 'compare' ),
		'kab' => __('Kabyle', 'compare' ),
		'kac' => __('Kachin', 'compare' ),
		'kgp' => __('Kaingang', 'compare' ),
		'kkj' => __('Kako', 'compare' ),
		'kl' => __('Kalaallisut', 'compare' ),
		'kln' => __('Kalenjin', 'compare' ),
		'xal' => __('Kalmyk', 'compare' ),
		'kam' => __('Kamba', 'compare' ),
		'kbl' => __('Kanembu', 'compare' ),
		'kn' => __('Kannada', 'compare' ),
		'kr' => __('Kanuri', 'compare' ),
		'kaa' => __('Kara-Kalpak', 'compare' ),
		'krc' => __('Karachay-Balkar', 'compare' ),
		'krl' => __('Karelian', 'compare' ),
		'ks' => __('Kashmiri', 'compare' ),
		'csb' => __('Kashubian', 'compare' ),
		'kaw' => __('Kawi', 'compare' ),
		'kk' => __('Kazakh', 'compare' ),
		'ken' => __('Kenyang', 'compare' ),
		'kha' => __('Khasi', 'compare' ),
		'km' => __('Khmer', 'compare' ),
		'kho' => __('Khotanese', 'compare' ),
		'khw' => __('Khowar', 'compare' ),
		'ki' => __('Kikuyu', 'compare' ),
		'kmb' => __('Kimbundu', 'compare' ),
		'krj' => __('Kinaray-a', 'compare' ),
		'rw' => __('Kinyarwanda', 'compare' ),
		'kiu' => __('Kirmanjki', 'compare' ),
		'tlh' => __('Klingon', 'compare' ),
		'bkm' => __('Kom', 'compare' ),
		'kv' => __('Komi', 'compare' ),
		'koi' => __('Komi-Permyak', 'compare' ),
		'kg' => __('Kongo', 'compare' ),
		'kok' => __('Konkani', 'compare' ),
		'ko' => __('Korean', 'compare' ),
		'kfo' => __('Koro', 'compare' ),
		'kos' => __('Kosraean', 'compare' ),
		'avk' => __('Kotava', 'compare' ),
		'khq' => __('Koyra Chiini', 'compare' ),
		'ses' => __('Koyraboro Senni', 'compare' ),
		'kpe' => __('Kpelle', 'compare' ),
		'kri' => __('Krio', 'compare' ),
		'kj' => __('Kuanyama', 'compare' ),
		'kum' => __('Kumyk', 'compare' ),
		'ku' => __('Kurdish', 'compare' ),
		'kru' => __('Kurukh', 'compare' ),
		'kut' => __('Kutenai', 'compare' ),
		'nmg' => __('Kwasio', 'compare' ),
		'ky' => __('Kyrgyz', 'compare' ),
		'quc' => __('Kʼicheʼ', 'compare' ),
		'lad' => __('Ladino', 'compare' ),
		'lah' => __('Lahnda', 'compare' ),
		'lkt' => __('Lakota', 'compare' ),
		'lam' => __('Lamba', 'compare' ),
		'lag' => __('Langi', 'compare' ),
		'lo' => __('Lao', 'compare' ),
		'ltg' => __('Latgalian', 'compare' ),
		'la' => __('Latin', 'compare' ),
		'es_419' => __('Latin American Spanish', 'compare' ),
		'lv' => __('Latvian', 'compare' ),
		'lzz' => __('Laz', 'compare' ),
		'lez' => __('Lezghian', 'compare' ),
		'lij' => __('Ligurian', 'compare' ),
		'li' => __('Limburgish', 'compare' ),
		'ln' => __('Lingala', 'compare' ),
		'lfn' => __('Lingua Franca Nova', 'compare' ),
		'lzh' => __('Literary Chinese', 'compare' ),
		'lt' => __('Lithuanian', 'compare' ),
		'liv' => __('Livonian', 'compare' ),
		'jbo' => __('Lojban', 'compare' ),
		'lmo' => __('Lombard', 'compare' ),
		'nds' => __('Low German', 'compare' ),
		'sli' => __('Lower Silesian', 'compare' ),
		'dsb' => __('Lower Sorbian', 'compare' ),
		'loz' => __('Lozi', 'compare' ),
		'lu' => __('Luba-Katanga', 'compare' ),
		'lua' => __('Luba-Lulua', 'compare' ),
		'lui' => __('Luiseno', 'compare' ),
		'smj' => __('Lule Sami', 'compare' ),
		'lun' => __('Lunda', 'compare' ),
		'luo' => __('Luo', 'compare' ),
		'lb' => __('Luxembourgish', 'compare' ),
		'luy' => __('Luyia', 'compare' ),
		'mde' => __('Maba', 'compare' ),
		'mk' => __('Macedonian', 'compare' ),
		'jmc' => __('Machame', 'compare' ),
		'mad' => __('Madurese', 'compare' ),
		'maf' => __('Mafa', 'compare' ),
		'mag' => __('Magahi', 'compare' ),
		'vmf' => __('Main-Franconian', 'compare' ),
		'mai' => __('Maithili', 'compare' ),
		'mak' => __('Makasar', 'compare' ),
		'mgh' => __('Makhuwa-Meetto', 'compare' ),
		'kde' => __('Makonde', 'compare' ),
		'mg' => __('Malagasy', 'compare' ),
		'ms' => __('Malay', 'compare' ),
		'ml' => __('Malayalam', 'compare' ),
		'mt' => __('Maltese', 'compare' ),
		'mnc' => __('Manchu', 'compare' ),
		'mdr' => __('Mandar', 'compare' ),
		'man' => __('Mandingo', 'compare' ),
		'mni' => __('Manipuri', 'compare' ),
		'gv' => __('Manx', 'compare' ),
		'mi' => __('Maori', 'compare' ),
		'arn' => __('Mapuche', 'compare' ),
		'mr' => __('Marathi', 'compare' ),
		'chm' => __('Mari', 'compare' ),
		'mh' => __('Marshallese', 'compare' ),
		'mwr' => __('Marwari', 'compare' ),
		'mas' => __('Masai', 'compare' ),
		'mzn' => __('Mazanderani', 'compare' ),
		'byv' => __('Medumba', 'compare' ),
		'men' => __('Mende', 'compare' ),
		'mwv' => __('Mentawai', 'compare' ),
		'mer' => __('Meru', 'compare' ),
		'mgo' => __('Metaʼ', 'compare' ),
		'es_MX' => __('Mexican Spanish', 'compare' ),
		'mic' => __('Micmac', 'compare' ),
		'dum' => __('Middle Dutch', 'compare' ),
		'enm' => __('Middle English', 'compare' ),
		'frm' => __('Middle French', 'compare' ),
		'gmh' => __('Middle High German', 'compare' ),
		'mga' => __('Middle Irish', 'compare' ),
		'nan' => __('Min Nan Chinese', 'compare' ),
		'min' => __('Minangkabau', 'compare' ),
		'xmf' => __('Mingrelian', 'compare' ),
		'mwl' => __('Mirandese', 'compare' ),
		'lus' => __('Mizo', 'compare' ),
		'ar_001' => __('Modern Standard Arabic', 'compare' ),
		'moh' => __('Mohawk', 'compare' ),
		'mdf' => __('Moksha', 'compare' ),
		'ro_MD' => __('Moldavian', 'compare' ),
		'lol' => __('Mongo', 'compare' ),
		'mn' => __('Mongolian', 'compare' ),
		'mfe' => __('Morisyen', 'compare' ),
		'ary' => __('Moroccan Arabic', 'compare' ),
		'mos' => __('Mossi', 'compare' ),
		'mul' => __('Multiple Languages', 'compare' ),
		'mua' => __('Mundang', 'compare' ),
		'ttt' => __('Muslim Tat', 'compare' ),
		'mye' => __('Myene', 'compare' ),
		'naq' => __('Nama', 'compare' ),
		'na' => __('Nauru', 'compare' ),
		'nv' => __('Navajo', 'compare' ),
		'ng' => __('Ndonga', 'compare' ),
		'nap' => __('Neapolitan', 'compare' ),
		'ne' => __('Nepali', 'compare' ),
		'new' => __('Newari', 'compare' ),
		'sba' => __('Ngambay', 'compare' ),
		'nnh' => __('Ngiemboon', 'compare' ),
		'jgo' => __('Ngomba', 'compare' ),
		'yrl' => __('Nheengatu', 'compare' ),
		'nia' => __('Nias', 'compare' ),
		'niu' => __('Niuean', 'compare' ),
		'zxx' => __('No linguistic content', 'compare' ),
		'nog' => __('Nogai', 'compare' ),
		'nd' => __('North Ndebele', 'compare' ),
		'frr' => __('Northern Frisian', 'compare' ),
		'se' => __('Northern Sami', 'compare' ),
		'nso' => __('Northern Sotho', 'compare' ),
		'no' => __('Norwegian', 'compare' ),
		'nb' => __('Norwegian Bokmål', 'compare' ),
		'nn' => __('Norwegian Nynorsk', 'compare' ),
		'nov' => __('Novial', 'compare' ),
		'nus' => __('Nuer', 'compare' ),
		'nym' => __('Nyamwezi', 'compare' ),
		'ny' => __('Nyanja', 'compare' ),
		'nyn' => __('Nyankole', 'compare' ),
		'tog' => __('Nyasa Tonga', 'compare' ),
		'nyo' => __('Nyoro', 'compare' ),
		'nzi' => __('Nzima', 'compare' ),
		'nqo' => __('NʼKo', 'compare' ),
		'oc' => __('Occitan', 'compare' ),
		'oj' => __('Ojibwa', 'compare' ),
		'ang' => __('Old English', 'compare' ),
		'fro' => __('Old French', 'compare' ),
		'goh' => __('Old High German', 'compare' ),
		'sga' => __('Old Irish', 'compare' ),
		'non' => __('Old Norse', 'compare' ),
		'peo' => __('Old Persian', 'compare' ),
		'pro' => __('Old Provençal', 'compare' ),
		'or' => __('Oriya', 'compare' ),
		'om' => __('Oromo', 'compare' ),
		'osa' => __('Osage', 'compare' ),
		'os' => __('Ossetic', 'compare' ),
		'ota' => __('Ottoman Turkish', 'compare' ),
		'pal' => __('Pahlavi', 'compare' ),
		'pfl' => __('Palatine German', 'compare' ),
		'pau' => __('Palauan', 'compare' ),
		'pi' => __('Pali', 'compare' ),
		'pam' => __('Pampanga', 'compare' ),
		'pag' => __('Pangasinan', 'compare' ),
		'pap' => __('Papiamento', 'compare' ),
		'ps' => __('Pashto', 'compare' ),
		'pdc' => __('Pennsylvania German', 'compare' ),
		'fa' => __('Persian', 'compare' ),
		'phn' => __('Phoenician', 'compare' ),
		'pcd' => __('Picard', 'compare' ),
		'pms' => __('Piedmontese', 'compare' ),
		'pdt' => __('Plautdietsch', 'compare' ),
		'pon' => __('Pohnpeian', 'compare' ),
		'pl' => __('Polish', 'compare' ),
		'pnt' => __('Pontic', 'compare' ),
		'pt' => __('Portuguese', 'compare' ),
		'prg' => __('Prussian', 'compare' ),
		'pa' => __('Punjabi', 'compare' ),
		'qu' => __('Quechua', 'compare' ),
		'raj' => __('Rajasthani', 'compare' ),
		'rap' => __('Rapanui', 'compare' ),
		'rar' => __('Rarotongan', 'compare' ),
		'rif' => __('Riffian', 'compare' ),
		'rgn' => __('Romagnol', 'compare' ),
		'ro' => __('Romanian', 'compare' ),
		'rm' => __('Romansh', 'compare' ),
		'rom' => __('Romany', 'compare' ),
		'rof' => __('Rombo', 'compare' ),
		'root' => __('Root', 'compare' ),
		'rtm' => __('Rotuman', 'compare' ),
		'rug' => __('Roviana', 'compare' ),
		'rn' => __('Rundi', 'compare' ),
		'ru' => __('Russian', 'compare' ),
		'rue' => __('Rusyn', 'compare' ),
		'rwk' => __('Rwa', 'compare' ),
		'ssy' => __('Saho', 'compare' ),
		'sah' => __('Sakha', 'compare' ),
		'sam' => __('Samaritan Aramaic', 'compare' ),
		'saq' => __('Samburu', 'compare' ),
		'sm' => __('Samoan', 'compare' ),
		'sgs' => __('Samogitian', 'compare' ),
		'sad' => __('Sandawe', 'compare' ),
		'sg' => __('Sango', 'compare' ),
		'sbp' => __('Sangu', 'compare' ),
		'sa' => __('Sanskrit', 'compare' ),
		'sat' => __('Santali', 'compare' ),
		'sc' => __('Sardinian', 'compare' ),
		'sas' => __('Sasak', 'compare' ),
		'sdc' => __('Sassarese Sardinian', 'compare' ),
		'stq' => __('Saterland Frisian', 'compare' ),
		'saz' => __('Saurashtra', 'compare' ),
		'sco' => __('Scots', 'compare' ),
		'gd' => __('Scottish Gaelic', 'compare' ),
		'sly' => __('Selayar', 'compare' ),
		'sel' => __('Selkup', 'compare' ),
		'seh' => __('Sena', 'compare' ),
		'see' => __('Seneca', 'compare' ),
		'sr' => __('Serbian', 'compare' ),
		'sh' => __('Serbo-Croatian', 'compare' ),
		'srr' => __('Serer', 'compare' ),
		'sei' => __('Seri', 'compare' ),
		'ksb' => __('Shambala', 'compare' ),
		'shn' => __('Shan', 'compare' ),
		'sn' => __('Shona', 'compare' ),
		'ii' => __('Sichuan Yi', 'compare' ),
		'scn' => __('Sicilian', 'compare' ),
		'sid' => __('Sidamo', 'compare' ),
		'bla' => __('Siksika', 'compare' ),
		'szl' => __('Silesian', 'compare' ),
		'zh_Hans' => __('Simplified Chinese', 'compare' ),
		'sd' => __('Sindhi', 'compare' ),
		'si' => __('Sinhala', 'compare' ),
		'sms' => __('Skolt Sami', 'compare' ),
		'den' => __('Slave', 'compare' ),
		'sk' => __('Slovak', 'compare' ),
		'sl' => __('Slovenian', 'compare' ),
		'xog' => __('Soga', 'compare' ),
		'sog' => __('Sogdien', 'compare' ),
		'so' => __('Somali', 'compare' ),
		'snk' => __('Soninke', 'compare' ),
		'azb' => __('South Azerbaijani', 'compare' ),
		'nr' => __('South Ndebele', 'compare' ),
		'alt' => __('Southern Altai', 'compare' ),
		'sma' => __('Southern Sami', 'compare' ),
		'st' => __('Southern Sotho', 'compare' ),
		'es' => __('Spanish', 'compare' ),
		'srn' => __('Sranan Tongo', 'compare' ),
		'zgh' => __('Standard Moroccan Tamazight', 'compare' ),
		'suk' => __('Sukuma', 'compare' ),
		'sux' => __('Sumerian', 'compare' ),
		'su' => __('Sundanese', 'compare' ),
		'sus' => __('Susu', 'compare' ),
		'sw' => __('Swahili', 'compare' ),
		'ss' => __('Swati', 'compare' ),
		'sv' => __('Swedish', 'compare' ),
		'fr_CH' => __('Swiss French', 'compare' ),
		'gsw' => __('Swiss German', 'compare' ),
		'de_CH' => __('Swiss High German', 'compare' ),
		'syr' => __('Syriac', 'compare' ),
		'shi' => __('Tachelhit', 'compare' ),
		'tl' => __('Tagalog', 'compare' ),
		'ty' => __('Tahitian', 'compare' ),
		'dav' => __('Taita', 'compare' ),
		'tg' => __('Tajik', 'compare' ),
		'tly' => __('Talysh', 'compare' ),
		'tmh' => __('Tamashek', 'compare' ),
		'ta' => __('Tamil', 'compare' ),
		'trv' => __('Taroko', 'compare' ),
		'twq' => __('Tasawaq', 'compare' ),
		'tt' => __('Tatar', 'compare' ),
		'te' => __('Telugu', 'compare' ),
		'ter' => __('Tereno', 'compare' ),
		'teo' => __('Teso', 'compare' ),
		'tet' => __('Tetum', 'compare' ),
		'th' => __('Thai', 'compare' ),
		'bo' => __('Tibetan', 'compare' ),
		'tig' => __('Tigre', 'compare' ),
		'ti' => __('Tigrinya', 'compare' ),
		'tem' => __('Timne', 'compare' ),
		'tiv' => __('Tiv', 'compare' ),
		'tli' => __('Tlingit', 'compare' ),
		'tpi' => __('Tok Pisin', 'compare' ),
		'tkl' => __('Tokelau', 'compare' ),
		'to' => __('Tongan', 'compare' ),
		'fit' => __('Tornedalen Finnish', 'compare' ),
		'zh_Hant' => __('Traditional Chinese', 'compare' ),
		'tkr' => __('Tsakhur', 'compare' ),
		'tsd' => __('Tsakonian', 'compare' ),
		'tsi' => __('Tsimshian', 'compare' ),
		'ts' => __('Tsonga', 'compare' ),
		'tn' => __('Tswana', 'compare' ),
		'tcy' => __('Tulu', 'compare' ),
		'tum' => __('Tumbuka', 'compare' ),
		'aeb' => __('Tunisian Arabic', 'compare' ),
		'tr' => __('Turkish', 'compare' ),
		'tk' => __('Turkmen', 'compare' ),
		'tru' => __('Turoyo', 'compare' ),
		'tvl' => __('Tuvalu', 'compare' ),
		'tyv' => __('Tuvinian', 'compare' ),
		'tw' => __('Twi', 'compare' ),
		'kcg' => __('Tyap', 'compare' ),
		'udm' => __('Udmurt', 'compare' ),
		'uga' => __('Ugaritic', 'compare' ),
		'uk' => __('Ukrainian', 'compare' ),
		'umb' => __('Umbundu', 'compare' ),
		'und' => __('Unknown Language', 'compare' ),
		'hsb' => __('Upper Sorbian', 'compare' ),
		'ur' => __('Urdu', 'compare' ),
		'ug' => __('Uyghur', 'compare' ),
		'uz' => __('Uzbek', 'compare' ),
		'vai' => __('Vai', 'compare' ),
		've' => __('Venda', 'compare' ),
		'vec' => __('Venetian', 'compare' ),
		'vep' => __('Veps', 'compare' ),
		'vi' => __('Vietnamese', 'compare' ),
		'vo' => __('Volapük', 'compare' ),
		'vro' => __('Võro', 'compare' ),
		'vot' => __('Votic', 'compare' ),
		'vun' => __('Vunjo', 'compare' ),
		'wa' => __('Walloon', 'compare' ),
		'wae' => __('Walser', 'compare' ),
		'war' => __('Waray', 'compare' ),
		'wbp' => __('Warlpiri', 'compare' ),
		'was' => __('Washo', 'compare' ),
		'guc' => __('Wayuu', 'compare' ),
		'cy' => __('Welsh', 'compare' ),
		'vls' => __('West Flemish', 'compare' ),
		'fy' => __('Western Frisian', 'compare' ),
		'mrj' => __('Western Mari', 'compare' ),
		'wal' => __('Wolaytta', 'compare' ),
		'wo' => __('Wolof', 'compare' ),
		'wuu' => __('Wu Chinese', 'compare' ),
		'xh' => __('Xhosa', 'compare' ),
		'hsn' => __('Xiang Chinese', 'compare' ),
		'yav' => __('Yangben', 'compare' ),
		'yao' => __('Yao', 'compare' ),
		'yap' => __('Yapese', 'compare' ),
		'ybb' => __('Yemba', 'compare' ),
		'yi' => __('Yiddish', 'compare' ),
		'yo' => __('Yoruba', 'compare' ),
		'zap' => __('Zapotec', 'compare' ),
		'dje' => __('Zarma', 'compare' ),
		'zza' => __('Zaza', 'compare' ),
		'zea' => __('Zeelandic', 'compare' ),
		'zen' => __('Zenaga', 'compare' ),
		'za' => __('Zhuang', 'compare' ),
		'gbz' => __('Zoroastrian Dari', 'compare' ),
		'zu' => __('Zulu', 'compare' ),
		'zun' => __('Zuni', 'compare' ),
	);

$general  = get_option( 'general' );
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
	$support = sprintf( wp_kses( __('If you meet a bug, you can leave me a ticket on <a href="%s" target="_blank">Thivinfo.com</a>', 'compare'),array(  'a' => array( 'href' => array(), 'target' => array() ) ) ), esc_url( $support_link ) );
	?>
	<h3><?php _e('Welcome on the support center', 'compare'); ?></h3>
	<p><?php echo $support; ?></p>
<?php
}