<?php
/**
 * @author      Wployalty (Alagesan)
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @link        https://www.wployalty.net
 * */

namespace Wlac\App;

use Wlac\App\Controllers\Site\Main;

defined('ABSPATH') or die;

class Router
{
    private static $site, $admin;

    function init()
    {
        self::$site = empty(self::$site) ? new Main() : self::$site;
        //self::$admin = empty(self::$admin) ? new Main() : self::$admin;
        add_filter('wlr_core_multicurrency_allowed', function ($status) {
            return false;
        });
        if (is_admin()) {
            add_action('admin_menu', array(self::$site, 'addMenu'));
            add_action('admin_footer', array(self::$site, 'menuHide'));
            add_action('admin_enqueue_scripts', array(self::$site, 'adminScripts'), 100);
            add_action('wp_ajax_wlac_save_settings', array(self::$site, 'saveSettings'));
        }
        add_filter('wlr_default_product_price', array(self::$site, 'getDefaultProductPrice'), 10, 5);
        add_filter('wlr_product_price', array(self::$site, 'getProductPrice'), 10, 4);
        /*add_filter('wlr_convert_to_default_currency', array(self::$site, 'convertToDefaultCurrency'), 10, 2);*/
        /*Woocommerce Helper Functions*/
        add_filter('wlr_current_currency', array(self::$site, 'getCurrentCurrencyCode'));
        //Purchase Last order amount
        add_filter('wlr_get_order_total', array(self::$site, 'handleWoocommerceHelperOrderTotal'), 10, 2);
        /* Conditions*/
        /*Subtotal Conditions*/
        add_filter('wlr_get_cart_subtotal', array(self::$site, 'getCartSubtotal'), 10, 2);
        add_filter('wlr_get_order_subtotal', array(self::$site, 'getOrderSubtotal'), 10, 2);
        /*Life time sale value*/
        //add_filter( 'wlr_life_time_sale_value_order_total', array( self::$site, 'handleConditionOrderTotal' ), 10, 2 );
        //add_filter( 'wlr_purchase_spent_order_total', array( self::$site, 'handleConditionOrderTotal' ), 10, 2 );
        /*Custom price change*/
        add_filter('wlr_custom_default_currency', array(self::$site, 'getDefaultCurrency'), 10);
        add_filter('wlr_custom_price_convert', array(self::$site, 'convertDefaultToCurrentAmount'), 10, 4);
        add_filter('wlr_custom_display_currency', array(self::$site, 'getDisplayCurrency'), 10);
        add_filter('wlr_page_user_reward_list', array(self::$site, 'handleActionShortCodes'), 10);
        add_filter('wlr_page_reward_list', array(self::$site, 'handleRewardShortCodes'), 10, 2);
    }
}