<?php
/**
 * Plugin Name:       CBX Pushover Notification for Dokan
 * Plugin URI:        https://codeboxr.com/product/pushover-notification-for-dokan
 * Description:       Pushover Notification for Dokan, a multi vendor platform for woocommerce inside wordpress
 * Version:           1.0.8
 * Author:            Codeboxr
 * Author URI:        https://codeboxr.com
 * Text Domain:       codeboxrpushoverfordokan
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


require_once( plugin_dir_path( __FILE__ ) . 'public/class-codeboxrpushoverfordokan.php' );
require_once( plugin_dir_path( __FILE__ ) . 'public/class-dokan-pushover-setting.php' ); //singleton

define('CODEBOXR_PUSHOVER_DIR', plugin_dir_path( __FILE__ ) );
//activation and deactivatio hooks
register_activation_hook( __FILE__, array( 'Codeboxrpushoverfordokan', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Codeboxrpushoverfordokan', 'deactivate' ) );


add_action( 'plugins_loaded', array( 'Codeboxrpushoverfordokan', 'get_instance' ) );


//admin actions
if ( is_admin() ) {
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-codeboxrpushoverfordokan-admin.php' );
	add_action( 'plugins_loaded', array( 'CodeboxrpushoverfordokanAdmin', 'get_instance' ) );
}

