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
        }
        add_filter('wlr_default_product_price', array(self::$site, 'getDefaultProductPrice'), 10, 5);
        add_filter('wlr_product_price', array(self::$site, 'getProductPrice'), 10, 4);
        add_filter('wlr_current_currency', array(self::$site, 'getCurrentCurrencyCode'));
        add_filter('wlr_convert_to_default_currency', array(self::$site, 'convertToDefaultCurrency'), 10, 2);
        /* Conditions*/
        add_filter('wlr_get_cart_subtotal', array(self::$site, 'getCartSubtotal'), 10, 2);
        add_filter('wlr_get_order_subtotal', array(self::$site, 'getOrderSubtotal'), 10, 2);
    }
}