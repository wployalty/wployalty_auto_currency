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
 * WC tested up to: 7.3
 * Contributors: Wployalty, Alagesan
 * Author URI: https://wployalty.net/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('ABSPATH') or die;
if (!function_exists('isWployaltyActiveOrNot')) {
    function isWployaltyActiveOrNot()
    {
        $active_plugins = apply_filters('active_plugins', get_option('active_plugins', array()));
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
        return in_array('wp-loyalty-rules/wp-loyalty-rules.php', $active_plugins, false) || in_array('wp-loyalty-rules-lite/wp-loyalty-rules-lite.php', $active_plugins, false);
    }
}
if(!isWployaltyActiveOrNot()){
    return;
}
