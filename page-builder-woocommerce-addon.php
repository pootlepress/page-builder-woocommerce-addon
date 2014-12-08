<?php
/*
Plugin Name: Woocommerce Extension - Page Builder Addon
Plugin URI: http://pootlepress.com/
Description: An addon for Page Builder that allow user to build product and shop page
Version: 1.0.0
Author: PootlePress
Author URI: http://pootlepress.com/
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( 'page-builder-woocommerce-addon-functions.php' );
require_once( 'classes/class-woocommerce-page-builder-addon.php' );
require_once( 'classes/wx-pb-widgets.php' );
require_once( 'classes/class-pootlepress-updater.php');

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    $GLOBALS['woocommerce_page_builder_addon'] = new WooCommerce_Page_Builder_Addon(__FILE__);
    $GLOBALS['woocommerce_page_builder_addon']->version = '1.0.0';
}

add_action('init', 'pp_wpba_updater');
function pp_wpba_updater()
{
    if (!function_exists('get_plugin_data')) {
        include(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    $data = get_plugin_data(__FILE__);
    $wptuts_plugin_current_version = $data['Version'];
    $wptuts_plugin_remote_path = 'http://www.pootlepress.com/?updater=1';
    $wptuts_plugin_slug = plugin_basename(__FILE__);
    new Pootlepress_Updater ($wptuts_plugin_current_version, $wptuts_plugin_remote_path, $wptuts_plugin_slug);
}
?>
