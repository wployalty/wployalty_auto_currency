<?php
/**
 * Plugin Name: WPLoyalty - Auto Currency earning
 * Plugin URI: https://www.wployalty.net
 * Description: Auto currency
 * Version: 1.0.0
 * Author: Wployalty
 * Slug: wp-loyalty-auto-currency
 * Text Domain: wp-loyalty-auto-currency
 * Domain Path: /i18n/languages/
 * Requires at least: 4.9.0
 * WC requires at least: 6.5
 * WC tested up to: 7.5
 * Contributors: Wployalty, Alagesan
 * Author URI: https://wployalty.net/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * WPLoyalty: 1.2.0
 * WPLoyalty Page Link: wp-loyalty-auto-currency
 */
defined('ABSPATH') or die;
if (!function_exists('isWployaltyActiveOrNot')) {
    function isWployaltyActiveOrNot()
    {
        $active_plugins = apply_filters('active_plugins', get_option('active_plugins', array()));
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
        return in_array('wp-loyalty-rules/wp-loyalty-rules.php', $active_plugins, false) || in_array('wp-loyalty-rules-lite/wp-loyalty-rules-lite.php', $active_plugins, false) || in_array('wployalty/wp-loyalty-rules-lite.php', $active_plugins, false);
    }
}
if (!isWployaltyActiveOrNot()) {
    return;
}
//Define the plugin version
defined('WLAC_PLUGIN_NAME') or define('WLAC_PLUGIN_NAME', 'WPLoyalty - Auto currency');
defined('WLAC_PLUGIN_VERSION') or define('WLAC_PLUGIN_VERSION', '1.0.0');
defined('WLAC_PLUGIN_SLUG') or define('WLAC_PLUGIN_SLUG', 'wp-loyalty-auto-currency');
defined('WLAC_PLUGIN_PATH') or define('WLAC_PLUGIN_PATH', __DIR__ . '/');
defined('WLAC_PLUGIN_URL') or define('WLAC_PLUGIN_URL', plugin_dir_url(__FILE__));

if (!class_exists('\Wlac\App\Router')) {
    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        return;
    }
    require __DIR__ . '/vendor/autoload.php';
}
if (class_exists('\Wlac\App\Router')) {
    $myUpdateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/wployalty/wployalty_auto_currency',
        __FILE__,
        'wp-loyalty-auto-currency'
    );
    $myUpdateChecker->getVcsApi()->enableReleaseAssets();
    $router = new \Wlac\App\Router();
    $router->init();
}