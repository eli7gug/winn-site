<?php
/**
 *Plugin Name: Thank You Page Customizer for WooCommerce
 *Plugin URI: https://villatheme.com/extensions/woo-thank-you-page-customizer
 *Description: The easiest way to customize a beautiful thank you page for your WooCommerce store
 *Version: 1.0.5.8
 *Author: VillaTheme
 *Author URI: https://villatheme.com
 *Text Domain: woo-thank-you-page-customizer
 *Domain Path: /languages
 *Copyright 2018 VillaTheme.com. All rights reserved.
 * Tested up to: 5.5
 * WC requires at least: 3.0.0
 * WC tested up to: 4.3
 **/
if (!defined('ABSPATH')) {
    exit;
}

define('VI_WOO_THANK_YOU_PAGE_VERSION', '1.0.5.8');
/**
 * Detect plugin. For use on Front End only.
 */
include_once(ABSPATH . 'wp-admin/includes/plugin.php');
if (is_plugin_active('woocommerce-thank-you-page-customizer/woocommerce-thank-you-page-customizer.php')) {
    return;
}
if (is_plugin_active('woocommerce/woocommerce.php')) {
    $init_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woo-thank-you-page-customizer" . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "define.php";
    require_once $init_file;
}

/**
 * Class WOO_THANK_YOU_PAGE_CUSTOMIZER
 */
class WOO_THANK_YOU_PAGE_CUSTOMIZER
{
    public function __construct()
    {
        add_action('admin_notices', array($this, 'global_note'));
    }

    /**
     * Notify if WooCommerce is not activated
     */
    function global_note()
    {
        if (!is_plugin_active('woocommerce/woocommerce.php')) {
            ?>
            <div id="message" class="error">
                <p><?php _e('Please install and activate WooCommerce to use Thank You Page Customizer for WooCommerce.', 'woo-thank-you-page-customizer'); ?></p>
            </div>
            <?php
            if (is_plugin_active('woo-thank-you-page-customizer/woo-thank-you-page-customizer.php')) {
                deactivate_plugins('woo-thank-you-page-customizer/woo-thank-you-page-customizer.php');
                unset($_GET['activate']);
            }
        }
    }
}

new WOO_THANK_YOU_PAGE_CUSTOMIZER();