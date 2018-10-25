<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.


/**
 * load script for admin settings
 */
function load_admin_scripts() {
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'color-picker-script', plugins_url( 'color-picker.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}

if ( class_exists( 'AAWP_Affiliate' ) ) {
	add_filter( 'compare_setting_tabs', 'compare_add_aawp_tab' );
	add_action( 'admin_enqueue_scripts', 'load_admin_scripts');
}

/**
 * Add a tab AAWP only if AAWp activated
 *
 * @param $tabs array Array With tabs already defined in settings
 *
 * @return mixed
 */
function compare_add_aawp_tab( $tabs ) {
	$tabs['aawp'] = __( 'AAWP', 'compare' );

	return $tabs;
}

/**
 * Create the settings content
 */
add_filter( 'compare_setting_tabs', 'compare_pro_settings_page' );
function compare_pro_settings_page( $tabs ) {
	$tabs['advanced'] = __( 'advanced', 'compare' );
	$tabs['help']     = __( 'help', 'compare' );
	$tabs['premium']     = __( 'premium', 'compare' );
	$options          = get_option( 'compare-general' );
	$platforms        = $options['platform'];
	foreach ( $platforms as $platform ) {
		if ( ! empty( $platform ) ) {
			$tabs[ $platform ] = $platform;
		}
	}
	return $tabs;

}


add_action( 'admin_init', 'compare_pro_register_settings' );
function compare_pro_register_settings() {

	/**
	 * General
	 */
	add_settings_section( 'compare-external', __( 'External DB Settings', 'compare' ), 'compare_external', 'compare-advanced' );

	add_settings_field( 'compare-external-check', __( 'Using an external DB?', 'compare' ), 'cae_ext_check', 'compare-advanced', 'compare-external' );
	add_settings_field( 'compare-external-host', __( 'Host', 'compare' ), 'cae_host', 'compare-advanced', 'compare-external' );
	add_settings_field( 'compare-external-db', __( 'Database', 'compare' ), 'cae_db', 'compare-advanced', 'compare-external' );
	add_settings_field( 'compare-external-user', __( 'Username', 'compare' ), 'cae_user', 'compare-advanced', 'compare-external' );
	add_settings_field( 'compare-external-pwd', __( 'Password', 'compare' ), 'cae_pwd', 'compare-advanced', 'compare-external' );
	add_settings_field( 'compare-external-prefix', __( 'Prefix', 'compare' ), 'cae_prefix', 'compare-advanced', 'compare-external' );

	/**
	 * Premium
	 */

	add_settings_section( 'compare-premium', '', '', 'compare-premium' );
	add_settings_field( 'compare-general-cloak-link', __( 'Cloak Link', 'compare' ), 'compare_general_cloak_link', 'compare-premium', 'compare-premium' );
	add_settings_field( 'compare-general-platforms', __( 'Platforms', 'compare' ), 'compare_general_platforms', 'compare-premium', 'compare-premium' );
	add_settings_field( 'compare-general-tracker', __( 'tracking Word', 'compare' ), 'compare_general_trackers', 'compare-premium', 'compare-premium' );

	/**
	 * Help
	 */
	add_settings_section( 'compare-help', '', 'compare_help', 'compare-help' );
	register_setting( 'compare-help', 'help' );

	/**
	 * Awin
	 *
	 * @since 1.0.0
	 */
	add_settings_section( 'compare-awin', '', '', 'compare-awin' );

	register_setting( 'awin', 'awin' );

	add_settings_field( 'compare-awin-api', __( 'API Key', 'compare' ), 'compare_awin_key', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-partner', __( 'Awin partner Code', 'compare' ), 'compare_awin_partner', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-partner_logo', __( 'Awin partner logo', 'compare' ), 'compare_awin_partner_logo', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-partner_url', __( 'Awin Trademark code', 'compare' ), 'compare_awin_partner_url', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-id', __( 'Awin Customer Code', 'compare' ), 'compare_awin_id', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-feed', '', 'compare_awin_feed', 'compare-awin', 'compare-awin' );

	/**
	 * Aawp
	 */
	add_settings_section( 'compare-aawp', '', '', 'compare-aawp' );
	register_setting( 'compare-aawp', 'compare-aawp' );
	add_settings_field( 'compare-aawp-button-text', __( 'Button Text', 'compare' ), 'compare_aawp_button_text', 'compare-aawp', 'compare-aawp' );
	add_settings_field( 'compare-aawp-button-bg', __( 'Button Background Color', 'compare' ), 'compare_awwp_button_bg', 'compare-aawp', 'compare-aawp' );
	add_settings_field( 'compare-aawp-button-color', __( 'Button Text Color', 'compare' ), 'compare_awwp_button_color', 'compare-aawp', 'compare-aawp' );

	/**
	 * Effiliation
	 *
	 * @since 1.2.0
	 */
	add_settings_section( 'compare-effiliation', '', '', 'compare-effiliation' );
	register_setting( 'compare-effiliation', 'compare-effiliation' );
	add_settings_field( 'compare-effiliation-apikey', __( 'API Key', 'compare' ), 'compare_effiliation_api', 'compare-effiliation', 'compare-effiliation' );
	add_settings_field( 'compare-effiliation-programs', __( 'My Programs', 'compare' ), 'compare_effiliation_program', 'compare-effiliation', 'compare-effiliation' );

}

/**
 * @since 1.2.6
 * Customize the tracking word
 */
function compare_general_trackers() {
	$options = get_option( 'compare-general' );
	if ( ! empty( $options ) ) {
		$value = 'value="' . $options['tracker'] . '"';
	}
	?>
	<input type="text" name="compare-general[tracker]" <?php echo $value; ?>>
	<?php
}


function compare_effiliation_program() {
	echo effiliation::compare_effiliation_list_html();

}

function compare_effiliation_api() {
	$options = get_option( 'compare-effiliation' );
	if ( ! empty( $options ) ) {
		$value = 'value="' . $options['apikey'] . '"';
	}
	?>
	<input type="text" name="compare-effiliation[apikey]" <?php echo $value; ?>>
	<p><?php printf( __( '%s API Key. Get in your profile', 'compare' ), 'Effiliation' ); ?></p>
	<?php
}

function compare_general_platforms() {
	$platforms = array( 'awin', 'effiliation' );
	$options   = get_option( 'compare-general' );


	foreach ( $platforms as $platform ) {
		if ( isset( $options['platform'] ) && ! empty( $options['platform'] ) ) {
			$check = $options['platform'][ $platform ];
		}
		?>
		<input type="checkbox"
		       name="compare-general[platform][<?php echo $platform; ?>]" <?php checked( $check, $platform ) ?>
		       value="<?php echo $platform; ?>">
		<?php echo $platform; ?>

		<?php
	}
	?>
	<p><?php _e( 'Check the platform to work with', 'compare' ); ?></p>
	<?php
}

function compare_awwp_button_bg() {
	$option = get_option( 'compare-aawp' );
	$color  = $option['button-bg'];
	if ( ! empty( $color ) ) {
		$value = 'value="' . $color . '"';
	}

	?>
	<input name="compare-aawp[button-bg]" type='text' class='color-field' <?php echo $value; ?>>
	<?php
}

function compare_awwp_button_color() {
	$option = get_option( 'compare-aawp' );
	$color  = $option['button-color'];
	if ( ! empty( $color ) ) {
		$value = 'value="' . $color . '"';
	}

	?>
	<input name="compare-aawp[button-color]" type='text' class='color-field' <?php echo $value; ?>>
	<?php
}

function compare_aawp_button_text() {
	$option = get_option( 'compare-aawp' );
	$text   = $option['button_text'];
	if ( ! empty( $text ) ) {
		$value = 'value="' . $text . '"';
	} else {
		$value = 'value="' . __( 'Buy to ', 'compare' ) . '"';
	}
	?>
	<input name="compare-aawp[button_text]" type="text" <?php echo $value; ?>
	<?php
}

function compare_general_cloak_link() {
	$check = get_option( 'compare-general' );
	if ( isset( $check['general-cloack'] ) && ! empty( $check['general-cloack'] ) ) {
		$check = $check['general-cloack'];
	}
	?>
	<input name="compare-general[general-cloack]" type="checkbox" <?php checked( $check, 'on' ) ?>>
	<?php
}

function cae_ext_check() {
	$check = get_option( 'compare-general' );
	if ( isset( $check['ext_check'] ) && ! empty( $check['ext_check'] ) ) {
		$check = $check['ext_check'];
	}
	?>
	<input name="compare-general[ext_check]" type="checkbox" <?php checked( $check, 'on' ) ?>>

	<?php
	$external_db = compare_external_db::getInstance();
	$cnx         = $external_db->compare_check_html();
	echo $cnx;
	?>

	<?php
}

function compare_external() {
	$url  = 'https://www.thivinfo.com/docs/compare-affiliated-products/hooks/connection-to-an-external-db/';
	$link = sprintf( wp_kses( __( 'For more informations, Please <a href="%1$s">read the documentation</a>', 'compare' ),
		array( 'a' => array( 'href' => array() ) ) ), esc_url( $url ) );
	?>
	<p><?php _e( 'Optional - It could be a good idea if you\'d like to connect several websites to a common database', 'compare' ); ?></p>
	<p><?php echo $link; ?></p>
	<?php
}

function cae_host() {
	$external = get_option( 'compare-general' );
	if ( ! empty( $external ) ) {
		$value = 'value=' . esc_attr( $external['host'] );
	}
	?>

	<input name="compare-general[host]" type="text" <?php echo $value; ?>>
	<?php
}

function cae_prefix() {
	$external = get_option( 'compare-general' );
	if ( ! empty( $external ) ) {
		$value = 'value=' . esc_attr( $external['prefix'] );
	}
	?>

	<input name="compare-general[prefix]" type="text" <?php echo $value; ?>>
	<?php
}

function cae_db() {
	$external = get_option( 'compare-general' );
	if ( ! empty( $external ) ) {
		$value = 'value=' . esc_attr( $external['db'] );
	}
	?>

	<input name="compare-general[db]" type="text" <?php echo $value; ?>>
	<?php
}

function cae_user() {
	$external = get_option( 'compare-general' );
	if ( ! empty( $external ) ) {
		$value = 'value=' . esc_attr( $external['username'] );
	}
	?>

	<input name="compare-general[username]" type="text" <?php echo $value; ?>>
	<?php
}

function cae_pwd() {
	$external = get_option( 'compare-general' );
	if ( ! empty( $external ) ) {
		$value = 'value=' . esc_attr( $external['pwd'] );
	}
	?>

	<input name="compare-general[pwd]" type="text" <?php echo $value; ?>>
	<?php
}

function compare_awin_feed() {
	$feed = get_option( 'awin' );
	if ( ! empty( $feed ) ) {
		$value = $feed['datafeed'];
	}
	$partners  = $feed['partner'];
	$partners  = explode( ',', $partners );
	$general   = get_option( 'compare-general' );
	$lang      = $general['languages'];
	$trademark = $feed['trademark_code'];

	foreach ( $partners as $partner ) {
		if ( empty( $trademark ) ) {
			$url = 'https://productdata.awin.com/datafeed/download/apikey/' . $feed['apikey'] . '/language/' . $lang . '/fid/' . $partner . '/columns/aw_deep_link,product_name,aw_product_id,merchant_product_id,merchant_image_url,description,merchant_category,search_price,merchant_name,merchant_id,category_name,category_id,aw_image_url,currency,store_price,delivery_cost,merchant_deep_link,language,last_updated,upc,ean,product_GTIN/format/xml/dtd/1.5/compression/gzip/';
		} else {
			$url = 'https://productdata.awin.com/datafeed/download/apikey/' . $feed['apikey'] . '/language/' . $lang . '/fid/' . $partner . '/bid/' . $trademark . '/columns/aw_deep_link,product_name,aw_product_id,merchant_product_id,merchant_image_url,description,merchant_category,search_price,merchant_name,merchant_id,category_name,category_id,aw_image_url,currency,store_price,delivery_cost,merchant_deep_link,language,last_updated,upc,ean,product_GTIN/format/xml/dtd/1.5/compression/gzip/';
		}
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
	<p><?php printf( __( '%s Customer ID. Needed to let "Convert a link" feature working', 'compare' ), 'Awin' ) ?></p>
	<?php
}


function compare_awin_key() {
	$feed = get_option( 'awin' );
	if ( ! empty( $feed ) ) {
		$value = esc_attr( $feed['apikey'] );
	}
	?>
	<input type="text" name="awin[apikey]" value="<?php echo esc_attr( $value ) ?>">
	<p><?php printf( __( '%s API Key. Get in your profile', 'compare' ), 'Awin' ); ?></p>
	<?php
}

function compare_awin_partner() {
	$feed = get_option( 'awin' );
	if ( ! empty( $feed ) ) {
		$value = esc_attr( $feed['partner'] );
	}
	?>
	<input type="text" name="awin[partner]" value="<?php echo esc_attr( $value ) ?>">
	<p><?php printf( __( 'Choose the programs you\'d like to display on your site. You can get code by creating a feed in %s website', 'compare' ), 'Awin' ); ?></p>
	<?php
}

function compare_awin_partner_logo() {
	$awin_data = new Awin();
	$partners  = $awin_data->compare_get_awin_partners();
	$awin      = get_option( 'awin' );
	foreach ( $partners as $key => $partner ) {
		if ( ! empty( $awin['partner_logo'][ $key ]['img'] ) ) {
			$value = 'value="' . $awin['partner_logo'][ $key ]['img'] . '"';
		}

		?>
		<div class="compare-partners-logo">
			<?php

			echo $key;
			?>
			<select name="awin[partner_logo][<?php echo $key; ?>]['name']">
				<option><?php _e( 'Choose your partner', 'compare' ); ?></option>
				<?php foreach ( $partners as $k => $p ) {
					?>
					<option value="<?php echo $k; ?>" <?php selected( $k, $key ); ?>><?php echo $p; ?></option>

				<?php } ?>
			</select>
			<input type="text" name="awin[partner_logo][<?php echo $key; ?>][img]" <?php echo $value; ?>>
			<img width="40px" src="<?php echo $awin['partner_logo'][ $key ]['img']; ?>">
		</div>

		<?php
	}
	?>
	<p><?php _e( 'Upload first image on media library then paste the link here.', 'compare' ); ?></p>
	<?php
}

function compare_awin_partner_url() {
	$awin = get_option( 'awin' );

	$value = 'value="' . $awin['trademark_code'] . '"';
	?>
	<div class="compare-partners-datafeed">
		<input type="text" name="awin[trademark_code]" <?php echo $value; ?>>
	</div>
	<p><?php printf( __( 'Choose the mark you\'d like to display on your site. You can get code by creating a feed in %s website. Left empty to get all mark from partner feed.', 'compare' ), 'Awin' ); ?></p>
	<?php
}

function compare_help() {
	$support_link = 'https://www.thivinfo.com/soumettre-un-ticket/';
	$support      = sprintf( wp_kses( __( 'If you encounter a bug, you can leave me a ticket on <a href="%1$s" target="_blank">Thivinfo.com</a>', 'compare' ), array(
		'a' => array(
			'href'   => array(),
			'target' => array()
		)
	) ), esc_url( $support_link ) );
	?>
	<h3><?php _e( 'Welcome on the support center', 'compare' ); ?></h3>
	<p><?php echo $support; ?></p>
	<p>
		<a href="https://www.thivinfo.com/docs/compare-affiliated-products/"><?php _e( 'Documentation Center', 'compare' ); ?></a>
	</p>
	<?php
}

function compare_get_programs() {
	$awin_data            = new Awin();
	$awin_partners        = $awin_data->compare_get_awin_partners();
	$effiliation_partners = Effiliation::compare_get_effiliation_program();
	$options              = get_option( 'compare-general' );

	$awin_programs = array();

	$p = get_option( 'awin' );

	if ( ! empty( $p['partner'] ) ) {
		$programs = $p['partner'];
	}
	array_push( $awin_programs, $programs );

	$list = array();
	foreach ( $awin_programs as $key => $value ) {
		if ( false != strpos( $value, ',' ) ) {
			$awins = explode( ',', $value );
		}

		array_push( $list, $awins );
	}
	$test = array_intersect_ukey( $awins, $awin_partners );
	$list = array_flip( $list[0] );
	foreach ( $list as $key => $value ) {
		$subscribed[ $awin_partners[ $key ] ] = $value;
	}

	/**
	 * Get Effiliation list of programs
	 */
	$effiliation_programs = get_option( 'compare-effiliation' );

	if ( ! empty( $effiliation_programs['programs'] ) ) {
		foreach ( $effiliation_programs['programs'] as $programs ) {
			$subscribed[ $programs ] = $programs;
		}
	}


	return $subscribed;
}