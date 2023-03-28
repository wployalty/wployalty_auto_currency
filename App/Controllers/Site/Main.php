<?php
/**
 * @author      Wployalty (Alagesan)
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @link        https://www.wployalty.net
 * */

namespace Wlac\App\Controllers\Site;

defined('ABSPATH') or die;

use Wlac\App\Controllers\Base;
use Wlr\App\Helpers\Template;
use Wlr\App\Helpers\Woocommerce;

class Main extends Base
{
    public static $active_plugin_list = array();

    function addMenu()
    {
        if (Woocommerce::hasAdminPrivilege()) {
            add_menu_page(__('WPLoyalty: Auto Currency Change', 'wp-loyalty-auto-currency'), __('WPLoyalty: Auto Currency Change', 'wp-loyalty-auto-currency'), 'manage_woocommerce', WLAC_PLUGIN_SLUG, array($this, 'manageLoyaltyPages'), 'dashicons-megaphone', 57);
        }
    }

    function manageLoyaltyPages()
    {
        if (!Woocommerce::hasAdminPrivilege()) {
            wp_die(__("Don't have access permission", 'wp-loyalty-auto-currency'));
        }
        //it will automatically add new table column,via auto generate alter query
        if (!isset($_GET['page']) || ($_GET['page'] != WLAC_PLUGIN_SLUG)) {
            wp_die(__('Page query params missing...', 'wp-loyalty-auto-currency'));
        }
        $template = new Template();
        $path = WLAC_PLUGIN_PATH . 'App/Views/main.php';
        $main_page_params = array();
        $template->setData($path, $main_page_params)->display();
    }

    function getDefaultProductPrice($productPrice, $product, $item, $is_redeem, $orderCurrency)
    {
        /*if ($this->isEnableRealMagCurrency()) {
            return $productPrice;
        }
        if ($this->isEnabledVilaThemeCurrency()) {
            return $productPrice;
        }*/
        if ($this->isEnabledWPMLCurrency()) {
            global $woocommerce_wpml;
            $multi_currency = $woocommerce_wpml->get_multi_currency();
            $current_code = $multi_currency->get_client_currency();
            return $this->convertToDefaultCurrency($productPrice, $current_code);
        }
        if ($this->isEnabledAeliaoCurrency()) {
            $current_code = isset($GLOBALS['woocommerce-aelia-currencyswitcher']) ? $GLOBALS['woocommerce-aelia-currencyswitcher']->get_selected_currency() : '';
            if (!empty($current_code)) return $this->convertToDefaultCurrency($productPrice, $current_code);
        }
        return $productPrice;
    }

    function isEnabledWPMLCurrency()
    {
        //ref: https://wordpress.org/plugins/woocommerce-multilingual/
        return $this->isPluginIsActive('woocommerce-multilingual/wpml-woocommerce.php');
    }

    protected function isPluginIsActive($plugin = '')
    {
        if (empty($plugin) || !is_string($plugin)) {
            return false;
        }
        $active_plugins = $this->getActivePlugins();
        if (in_array($plugin, $active_plugins, false) || array_key_exists($plugin, $active_plugins)) {
            return true;
        }
        return false;
    }

    function getActivePlugins()
    {
        if (empty(self::$active_plugin_list)) {
            $active_plugins = apply_filters('active_plugins', get_option('active_plugins', array()));
            if (is_multisite()) {
                $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
            }
            self::$active_plugin_list = $active_plugins;
        }
        return self::$active_plugin_list;
    }

    function convertToDefaultCurrency($amount, $current_currency_code)
    {
        $default_currency = $this->getDefaultCurrency();
        if (!empty($default_currency) && $default_currency == $current_currency_code) {
            return $amount;
        }
        if ($this->isEnableRealMagCurrency()) {
            global $WOOCS;
            $currencies = $WOOCS->get_currencies();
            $rate = isset($currencies[$current_currency_code]['rate']) && !empty($currencies[$current_currency_code]['rate']) ? $currencies[$current_currency_code]['rate'] : 0;
            $decimal = isset($currencies[$current_currency_code]['decimals']) && !empty($currencies[$current_currency_code]['decimals']) ? $currencies[$current_currency_code]['decimals'] : 2;
            if ($rate > 0) {
                $amount = $WOOCS->back_convert($amount, $rate, $decimal);
            }
            return (float)$amount;
        }
        if ($this->isEnabledVilaThemeCurrency() && class_exists('\WOOMULTI_CURRENCY_F_Data')) {
            return (float)wmc_revert_price($amount, $current_currency_code);
        }
        if ($this->isEnabledWPMLCurrency()) {
            global $woocommerce_wpml;
            return (float)$woocommerce_wpml->multi_currency->prices->unconvert_price_amount($amount, $current_currency_code);
        }
        if ($this->isEnabledAeliaoCurrency()) {
            return (float)$GLOBALS['woocommerce-aelia-currencyswitcher']->convert($amount, $current_currency_code, $default_currency, $price_decimals = null, $include_markup = true);
        }
        return $amount;
    }

    protected function getDefaultCurrency($code = '')
    {
        if ($this->isEnableRealMagCurrency()) {
            global $WOOCS;
            return $WOOCS->default_currency;
        }
        if ($this->isEnabledVilaThemeCurrency() && class_exists('\WOOMULTI_CURRENCY_F_Data')) {
            $setting = \WOOMULTI_CURRENCY_F_Data::get_ins();
            return $setting->get_default_currency();
        }
        if ($this->isEnabledWPMLCurrency()) {
            return wcml_get_woocommerce_currency_option();
        }
        if ($this->isEnabledAeliaoCurrency()) {
            return $GLOBALS['woocommerce-aelia-currencyswitcher']->base_currency();
        }
        return $code;
    }

    /**
     * @return bool
     */
    function isEnableRealMagCurrency()
    {
        // Ref: https://wordpress.org/plugins/woocommerce-currency-switcher/
        return $this->isPluginIsActive('woocommerce-currency-switcher/index.php');
    }

    function isEnabledVilaThemeCurrency()
    {
        //Ref: https://wordpress.org/plugins/woo-multi-currency/
        return $this->isPluginIsActive('woo-multi-currency/woo-multi-currency.php');
    }

    function isEnabledAeliaoCurrency()
    {
        return $this->isPluginIsActive('woocommerce-aelia-currencyswitcher/woocommerce-aelia-currencyswitcher.php');
    }

    function getProductPrice($productPrice, $item, $is_redeem, $orderCurrency)
    {
        if (empty($orderCurrency)) {
            $orderCurrency = $this->getCurrentCurrencyCode($orderCurrency);
        }
        $default_currency = $this->getDefaultCurrency();
        if ($orderCurrency == $default_currency) {
            return $productPrice;
        }
        if (!empty($orderCurrency)) {
            return $this->convertToDefaultCurrency($productPrice, $orderCurrency);
        }
        return $productPrice;
    }

    function getCurrentCurrencyCode($code = '')
    {
        if ($this->isEnableRealMagCurrency()) {
            global $WOOCS;
            return isset($WOOCS->current_currency) ? $WOOCS->current_currency : $code;
        }
        if ($this->isEnabledVilaThemeCurrency() && class_exists('\WOOMULTI_CURRENCY_F_Data')) {
            $setting = \WOOMULTI_CURRENCY_F_Data::get_ins();
            return $setting->get_current_currency();
        }
        if ($this->isEnabledWPMLCurrency()) {
            global $woocommerce_wpml;
            $multi_currency = $woocommerce_wpml->get_multi_currency();
            return $multi_currency->get_client_currency();
        }
        if ($this->isEnabledAeliaoCurrency()) {
            return $GLOBALS['woocommerce-aelia-currencyswitcher']->get_selected_currency();
        }
        return $code;
    }

    function getCartSubtotal($sub_total, $cart_data)
    {
        $current_currency = $this->getCurrentCurrencyCode();
        return $this->convertToDefaultCurrency($sub_total, $current_currency);
    }

    function getOrderSubtotal($sub_total, $order_data)
    {
        $woocommerce_helper = Woocommerce::getInstance();
        $order = $woocommerce_helper->getOrder($order_data);
        $order_currency = $woocommerce_helper->isMethodExists($order, 'get_currency') ? $order->get_currency() : '';
        if (!empty($order_currency)) {
            return $this->convertToDefaultCurrency($sub_total, $order_currency);
        }
        return $sub_total;
    }

    function handleConditionOrderTotal($total, $order)
    {
        return $this->convertOrderTotal($total, $order);
    }

    function convertOrderTotal($total, $order)
    {
        $woocommerce_helper = Woocommerce::getInstance();
        $order_currency = $woocommerce_helper->isMethodExists($order, 'get_currency') ? $order->get_currency() : '';
        $default_currency = $this->getDefaultCurrency();
        if ($order_currency != $default_currency) {
            $total = $this->convertToDefaultCurrency($total, $order_currency);
        }
        return $total;
    }

    function handleWoocommerceHelperOrderTotal($total, $order)
    {
        return $this->convertOrderTotal($total, $order);
    }
}