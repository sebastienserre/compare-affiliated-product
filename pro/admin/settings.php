<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.


/**
 * Create the settings content
 */
add_filter( 'compare_setting_tabs', 'compare_pro_settings_page' );
function compare_pro_settings_page( $tabs ) {
	$tabs['advanced'] = __( 'advanced', 'compare-affiliated-products' );
	$tabs['premium']  = __( 'premium', 'compare-affiliated-products' );
	$platforms        = array( 'awin', 'effiliation', 'manomano' );
	foreach ( $platforms as $platform ) {
		$tabs[ $platform ] = $platform;
	}

	return $tabs;

}


add_action( 'admin_init', 'compare_pro_register_settings', 20 );
function compare_pro_register_settings() {

	/**
	 * General
	 */
	register_setting( 'compare-general', 'compare-general' );

	add_settings_section( 'compare-general', '', '', 'compare-general' );

	add_settings_field( 'compare-general-currency', __( 'Currency Unit', 'compare-affiliated-products' ), 'compare_currency_unit', 'compare-general', 'compare-general' );
	add_settings_field( 'compare-general-language', __( 'Language', 'compare-affiliated-products' ), 'compare_general_languages', 'compare-general', 'compare-general' );


	/**
	 * Advanced
	 */
	register_setting( 'compare-advanced', 'compare-advanced' );

	add_settings_section( 'compare-external', __( 'External DB Settings', 'compare-affiliated-products' ), 'compare_external', 'compare-advanced' );


	add_settings_field( 'compare-external-check', __( 'Using an external DB?', 'compare-affiliated-products' ), 'cae_ext_check', 'compare-advanced', 'compare-external' );
	add_settings_field( 'compare-external-host', __( 'Host', 'compare-affiliated-products' ), 'cae_host', 'compare-advanced', 'compare-external' );
	add_settings_field( 'compare-external-db', __( 'Database', 'compare-affiliated-products' ), 'cae_db', 'compare-advanced', 'compare-external' );
	add_settings_field( 'compare-external-user', __( 'Username', 'compare-affiliated-products' ), 'cae_user', 'compare-advanced', 'compare-external' );
	add_settings_field( 'compare-external-pwd', __( 'Password', 'compare-affiliated-products' ), 'cae_pwd', 'compare-advanced', 'compare-external' );
	add_settings_field( 'compare-external-prefix', __( 'Prefix', 'compare-affiliated-products' ), 'cae_prefix', 'compare-advanced', 'compare-external' );

	/**
	 * Premium
	 */

	register_setting( 'compare-premium', 'compare-premium' );
	add_settings_section( 'compare-premium', '', '', 'compare-premium' );

	add_settings_field( 'compare-general-cloak-link', __( 'Cloak Link', 'compare-affiliated-products' ), 'compare_general_cloak_link', 'compare-premium', 'compare-premium' );
	add_settings_field( 'compare-general-tracker', __( 'tracking Word', 'compare-affiliated-products' ), 'compare_general_trackers', 'compare-premium', 'compare-premium' );
	add_settings_field( 'compare-general-delete', __( 'Delete All Data when deleting this plugin', 'compare-affiliated-products' ), 'compare_general_delete', 'compare-premium', 'compare-premium' );
	add_settings_field( 'compare-general-cron', __( 'Configure Cron Job', 'compare-affiliated-products' ), 'compare_general_cron', 'compare-premium', 'compare-premium' );
	add_settings_field( 'compare-general-platforms', __( 'Platforms to display', 'compare-affiliated-products' ), 'compare_general_platforms', 'compare-premium', 'compare-premium' );
	add_settings_field( 'compare-launch_cron', '', 'compare_launch_cron', 'compare-premium', 'compare-premium' );

	/**
	 * Awin
	 *
	 * @since 1.0.0
	 */
	add_settings_section( 'compare-awin', '', '', 'compare-awin' );

	register_setting( 'awin', 'awin' );

	add_settings_field( 'compare-awin-api', __( 'API Key', 'compare-affiliated-products' ), 'compare_awin_key', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-partner', __( 'Awin partner Code', 'compare-affiliated-products' ), 'compare_awin_partner', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-partner_logo', __( 'Awin partner logo', 'compare-affiliated-products' ), 'compare_awin_partner_logo', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-partner_url', __( 'Awin Trademark code', 'compare-affiliated-products' ), 'compare_awin_partner_url', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-id', __( 'Awin Customer Code', 'compare-affiliated-products' ), 'compare_awin_id', 'compare-awin', 'compare-awin' );
	add_settings_field( 'compare-awin-feed', '', 'compare_awin_feed', 'compare-awin', 'compare-awin' );


	/**
	 * Effiliation
	 *
	 * @since 1.2.0
	 */
	add_settings_section( 'compare-effiliation', '', '', 'compare-effiliation' );

	register_setting( 'compare-effiliation', 'compare-effiliation' );

	add_settings_field( 'compare-effiliation-apikey', __( 'API Key', 'compare-affiliated-products' ), 'compare_effiliation_api', 'compare-effiliation', 'compare-effiliation' );
	add_settings_field( 'compare-effiliation-programs', __( 'My Programs', 'compare-affiliated-products' ), 'compare_effiliation_program', 'compare-effiliation', 'compare-effiliation' );

	/**
	 * Manomano
	 * @since 2.2.0
	 *
	 */
	add_settings_section( 'compare-manomano', '', '', 'compare-manomano' );

	register_setting( 'compare-manomano', 'compare-manomano' );
	add_settings_field( 'compare-manomano-json', __( 'JSON URL', 'compare-affiliated-products' ), 'compare_manomano_url', 'compare-manomano', 'compare-manomano' );

}

function compare_manomano_url() {
	$value = get_option( 'compare-manomano' );
	$value = 'value="'. $value['url'] .'"';

	?>
    <input type="text" name="compare-manomano[url]" <?php echo $value; ?>>
    <p><?php printf( __( 'URL of the Json product feed given by %s. Ask it to them', 'compare-affiliated-products' ), 'Manomano' ); ?></p>
	<?php
}

/**
 * @since 2.1.0
 *        Launch Cron
 */

function compare_launch_cron() {
	$nonce = wp_create_nonce( 'cap-launch-cron' );
	$url   = $_SERVER['REQUEST_URI'];
	$url   = add_query_arg(
		array(
			'launch_cron' => 'ok',
			'_wpnonce'    => $nonce,

		),
		$url );
	?>
    <a href="<?php echo $url ?>"><?php _e( 'Launch Cron Job', 'compare-affiliated-products' ); ?></a>
	<?php
}

/**
 * @since 1.2.6
 * Customize the tracking word
 */
function compare_general_trackers() {
	$options = get_option( 'compare-premium' );
	if ( ! empty( $options ) ) {
		$value = 'value="' . $options['tracker'] . '"';
	}
	?>
    <input type="text" name="compare-premium[tracker]" <?php echo $value; ?>>
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
    <p><?php printf( __( '%s API Key. Get in your profile', 'compare-affiliated-products' ), 'Effiliation' ); ?></p>
	<?php
}

function compare_general_platforms() {
	$platforms = array( 'awin', 'effiliation', 'manomano' );
	$options   = get_option( 'compare-premium' );


	foreach ( $platforms as $platform ) {
		if ( ! empty( $options['platform'][ $platform ] ) ) {
			$check = $options['platform'][ $platform ];
		}
		?>
        <input type="checkbox"
               name="compare-premium[platform][<?php echo $platform; ?>]" <?php checked( $check, $platform ) ?>
               value="<?php echo $platform; ?>">
		<?php echo $platform; ?>

		<?php
	}
	?>
    <p><?php _e( 'Check the platform to display', 'compare-affiliated-products' ); ?></p>
	<?php
}

function compare_general_cloak_link() {
	$check = get_option( 'compare-premium' );
	if ( isset( $check['general-cloack'] ) && ! empty( $check['general-cloack'] ) ) {
		$check = $check['general-cloack'];
	}
	?>
    <input name="compare-premium[general-cloack]" type="checkbox" <?php checked( $check, 'on' ) ?>>
	<?php
}

function cae_ext_check() {
	$check = get_option( 'compare-advanced' );
	if ( isset( $check['ext_check'] ) && ! empty( $check['ext_check'] ) ) {
		$check = $check['ext_check'];
	}
	?>
    <input name="compare-advanced[ext_check]" type="checkbox" <?php checked( $check, 'on' ) ?>>

	<?php
	//$external_db = compare_external_db::getInstance();
	$connection  = new compare_external_db();
	$external_db = $connection->compare_create_connexion();
	$cnx         = $connection->compare_check_html();
	echo $cnx;
	?>

	<?php
}

function compare_external() {
	$url  = 'https://www.thivinfo.com/docs/compare-affiliated-products/hooks/connection-to-an-external-db/';
	$link = sprintf( wp_kses( __( 'For more informations, Please <a href="%1$s">read the documentation</a>', 'compare-affiliated-products' ),
		array( 'a' => array( 'href' => array() ) ) ), esc_url( $url ) );
	?>
    <p><?php _e( 'Optional - It could be a good idea if you\'d like to connect several websites to a common database', 'compare-affiliated-products' ); ?></p>
    <p><?php echo $link; ?></p>
	<?php
}

function cae_host() {
	$external = get_option( 'compare-advanced' );
	if ( ! empty( $external ) ) {
		$value = 'value=' . esc_attr( $external['host'] );
	}
	?>

    <input name="compare-advanced[host]" type="text" <?php echo $value; ?>>
	<?php
}

function cae_prefix() {
	$external = get_option( 'compare-advanced' );
	if ( ! empty( $external ) ) {
		$value = 'value=' . esc_attr( $external['prefix'] );
	}
	?>

    <input name="compare-advanced[prefix]" type="text" <?php echo $value; ?>>
	<?php
}

function cae_db() {
	$external = get_option( 'compare-advanced' );
	if ( ! empty( $external ) ) {
		$value = 'value=' . esc_attr( $external['db'] );
	}
	?>

    <input name="compare-advanced[db]" type="text" <?php echo $value; ?>>
	<?php
}

function cae_user() {
	$external = get_option( 'compare-advanced' );
	if ( ! empty( $external ) ) {
		$value = 'value=' . esc_attr( $external['username'] );
	}
	?>

    <input name="compare-advanced[username]" type="text" <?php echo $value; ?>>
	<?php
}

function cae_pwd() {
	$external = get_option( 'compare-advanced' );
	if ( ! empty( $external ) ) {
		$value = 'value=' . esc_attr( $external['pwd'] );
	}
	?>

    <input name="compare-advanced[pwd]" type="text" <?php echo $value; ?>>
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
			$url = 'https://productdata.awin.com/datafeed/download/apikey/' . $feed['apikey'] . '/language/' . $lang . '/fid/' . $partner . '/columns/aw_deep_link,product_name,aw_product_id,merchant_product_id,merchant_image_url,description,merchant_category,search_price,merchant_name,merchant_id,category_name,category_id,aw_image_url,currency,store_price,delivery_cost,merchant_deep_link,language,last_updated,upc,ean,product_GTIN,mpn/format/xml/dtd/1.5/compression/gzip/';
		} else {
			$url = 'https://productdata.awin.com/datafeed/download/apikey/' . $feed['apikey'] . '/language/' . $lang . '/fid/' . $partner . '/bid/' . $trademark . '/columns/aw_deep_link,product_name,aw_product_id,merchant_product_id,merchant_image_url,description,merchant_category,search_price,merchant_name,merchant_id,category_name,category_id,aw_image_url,currency,store_price,delivery_cost,merchant_deep_link,language,last_updated,upc,ean,product_GTIN,mpn/format/xml/dtd/1.5/compression/gzip/';
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
    <p><?php printf( __( '%s Customer ID. Needed to let "Convert a link" feature working', 'compare-affiliated-products' ), 'Awin' ) ?></p>
	<?php
}


function compare_awin_key() {
	$feed = get_option( 'awin' );
	if ( ! empty( $feed ) ) {
		$value = esc_attr( $feed['apikey'] );
	}
	?>
    <input type="text" name="awin[apikey]" value="<?php echo esc_attr( $value ) ?>">
    <p><?php printf( __( '%s API Key. Get in your profile', 'compare-affiliated-products' ), 'Awin' ); ?></p>
	<?php
}

function compare_awin_partner() {
	$feed = get_option( 'awin' );
	if ( ! empty( $feed ) ) {
		$value = esc_attr( $feed['partner'] );
	}
	?>
    <input type="text" name="awin[partner]" value="<?php echo esc_attr( $value ) ?>">
    <p><?php printf( __( 'Choose the programs you\'d like to display on your site. You can get code by creating a feed in %s website', 'compare-affiliated-products' ), 'Awin' ); ?></p>
	<?php
}

function compare_awin_partner_logo() {
	$awin_data = new Awin();
	$partners  = $awin_data->compare_get_awin_partners();
	$awin      = get_option( 'awin' );
	foreach ( $partners as $key => $partner ) {
		if ( ! empty( $awin['partner_logo'][ $key ]['img'] ) ) {
			$value = 'value="' . $awin['partner_logo'][ $key ]['img'] . '"';
			$img = '<img width="40px" src="' . $awin['partner_logo'][ $key ]['img'] . '>">';
		} else {
		    $value = '';
		    $img    =   '';
        }

		?>
        <div class="compare-partners-logo">
			<?php

			echo $key;
			?>
            <select name="awin[partner_logo][<?php echo $key; ?>]['name']">
                <option><?php _e( 'Choose your partner', 'compare-affiliated-products' ); ?></option>
				<?php foreach ( $partners as $k => $p ) {
					?>
                    <option value="<?php echo $k; ?>" <?php selected( $k, $key ); ?>><?php echo $p; ?></option>

				<?php } ?>
            </select>
            <input type="text" name="awin[partner_logo][<?php echo $key; ?>][img]" <?php echo $value; ?>>
            <?php echo $img; ?>
        </div>

		<?php
	}
	?>
    <p><?php _e( 'Upload first image on media library then paste the link here.', 'compare-affiliated-products' ); ?></p>
	<?php
}

function compare_awin_partner_url() {
	$awin = get_option( 'awin' );

	$value = 'value="' . $awin['trademark_code'] . '"';
	?>
    <div class="compare-partners-datafeed">
        <input type="text" name="awin[trademark_code]" <?php echo $value; ?>>
    </div>
    <p><?php printf( __( 'Choose the mark you\'d like to display on your site. You can get code by creating a feed in %s website. Left empty to get all mark from partner feed.', 'compare-affiliated-products' ), 'Awin' ); ?></p>
	<?php
}

/**
 * Deprecated since 1.2.4
 */
add_action( 'admin_init', 'compare_reset_feed' );
function compare_reset_feed() {
	if ( isset( $_GET['reset'] ) && $_GET['reset'] === 'ok' ) {
		if ( isset ( $_GET['tab'] ) ) {
			switch ( $_GET['tab'] ) {
				case 'awin' :
					$awin = new Awin();
					$awin->compare_reset_awin_datafeed();
					break;
				case 'effiliation':
					$effiliation = new Effiliation();
					$effiliation->compare_reset_effiliation_feed();

			}
		}
	}
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

function compare_general_cron() {
	$option = get_option( 'compare-premium' );
	$cron   = $option['cron'];
	?>
    <select name="compare-premium[cron]">
        <option value="none" <?php selected( $cron, 'none' ); ?>><?php _e( 'None', 'compare-affiliated-products' ); ?></option>
        <option value="four" <?php selected( $cron, 'four' ); ?>><?php _e( 'Every 4 hours', 'compare-affiliated-products' ); ?></option>
        <option value="twice" <?php selected( $cron, 'twice' ); ?>><?php _e( 'Twice Daily', 'compare-affiliated-products' ); ?></option>
        <option value="daily" <?php selected( $cron, 'daily' ); ?>><?php _e( 'Daily', 'compare-affiliated-products' ); ?></option>
    </select>
    <p><?php _e( 'Cron Task will regenerate database programmatically. If you\'re using an external DB, no need to use Cron Jobs', 'compare-affiliated-products' ); ?></p>
	<?php
}

function compare_general_delete() {
	$general = get_option( 'compare-premium' );
	if ( ! isset( $general['delete'] ) || empty( $general['delete'] ) ) {
		$general['delete'] = 'no';
	}
	?>
    <input type="checkbox" value="yes" name="compare-premium[delete]" <?php checked( $general['delete'], 'yes' ); ?>>
	<?php
}
