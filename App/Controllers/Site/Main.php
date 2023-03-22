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
        if ($this->isEnableRealMagCurrency()) {
            return apply_filters('woocs_convert_price', $productPrice, false);
        }
        /*if ($this->isEnabledVilaThemeCurrency()) {
            return $productPrice;
        }*/
        return $productPrice;
    }

    /**
     * @return bool
     */
    function isEnableRealMagCurrency()
    {
        // Ref: https://wordpress.org/plugins/woocommerce-currency-switcher/
        return $this->isPluginIsActive('woocommerce-currency-switcher/index.php');
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

    function getProductPrice($productPrice, $item, $is_redeem, $orderCurrency)
    {
        if ($this->isEnableRealMagCurrency()) {
            global $WOOCS;
            if ($WOOCS->default_currency != $orderCurrency) {
                $productPrice = $this->convertToDefaultCurrency($productPrice, $orderCurrency);
            }
            return $productPrice;
        }
        if ($this->isEnabledVilaThemeCurrency()) {
            if (class_exists('\WOOMULTI_CURRENCY_F_Data') && !empty($orderCurrency)) {
                $setting = \WOOMULTI_CURRENCY_F_Data::get_ins();
                $default_currency = $setting->get_default_currency();
                if ($orderCurrency != $default_currency) {
                    $productPrice = $this->convertToDefaultCurrency($productPrice, $orderCurrency);
                }
            }
            return $productPrice;
        }
        return $productPrice;
    }

    function convertToDefaultCurrency($amount, $current_currency_code)
    {
        if ($this->isEnableRealMagCurrency()) {
            global $WOOCS;
            if ($WOOCS->default_currency != $current_currency_code) {
                $currencies = $WOOCS->get_currencies();
                $rate = isset($currencies[$current_currency_code]['rate']) && !empty($currencies[$current_currency_code]['rate']) ? $currencies[$current_currency_code]['rate'] : 0;
                $decimal = isset($currencies[$current_currency_code]['decimals']) && !empty($currencies[$current_currency_code]['decimals']) ? $currencies[$current_currency_code]['decimals'] : 2;
                if ($rate > 0) {
                    $amount = $WOOCS->back_convert($amount, $rate, $decimal);
                }
            }
            return $amount;
        }
        if ($this->isEnabledVilaThemeCurrency() && class_exists('\WOOMULTI_CURRENCY_F_Data')) {
            $setting = \WOOMULTI_CURRENCY_F_Data::get_ins();
            $default_currency = $setting->get_default_currency();
            if ($default_currency != $current_currency_code) {
                $amount = wmc_revert_price($amount, $current_currency_code);
            }
            return $amount;
        }
        return $amount;
    }

    function isEnabledVilaThemeCurrency()
    {
        //Ref: https://wordpress.org/plugins/woo-multi-currency/
        return $this->isPluginIsActive('woo-multi-currency/woo-multi-currency.php');
    }

    function getCurrentCurrencyCode($code)
    {
        if ($this->isEnableRealMagCurrency()) {
            global $WOOCS;
            return isset($WOOCS->current_currency) ? $WOOCS->current_currency : $code;
        }
        if ($this->isEnabledVilaThemeCurrency() && class_exists('\WOOMULTI_CURRENCY_F_Data')) {
            $setting = \WOOMULTI_CURRENCY_F_Data::get_ins();
            return $setting->get_current_currency();
        }
        return $code;
    }

    function getCartSubtotal($sub_total, $cart_data)
    {
        if ($this->isEnabledVilaThemeCurrency() && class_exists('\WOOMULTI_CURRENCY_F_Data')) {
            $setting = \WOOMULTI_CURRENCY_F_Data::get_ins();
            $current_currency = $setting->get_current_currency();
            return $this->convertToDefaultCurrency($sub_total, $current_currency);
        }
        return $sub_total;
    }

    function getOrderSubtotal($sub_total, $order_data)
    {
        $woocommerce_helper = Woocommerce::getInstance();
        $order = $woocommerce_helper->getOrder($order_data);
        $order_currency = $woocommerce_helper->isMethodExists($order, 'get_currency') ? $order->get_currency() : '';
        if ($this->isEnabledVilaThemeCurrency() && !empty($order_currency) && class_exists('\WOOMULTI_CURRENCY_F_Data')) {
            $setting = \WOOMULTI_CURRENCY_F_Data::get_ins();
            $current_currency = $setting->get_current_currency();
            return $this->convertToDefaultCurrency($sub_total, $current_currency);
        }
        return $sub_total;
    }

    function handleConditionOrderTotal($total, $order)
    {
        $woocommerce_helper = Woocommerce::getInstance();
        $order_currency = $woocommerce_helper->isMethodExists($order, 'get_currency') ? $order->get_currency() : '';
        if ($this->isEnabledVilaThemeCurrency() && !empty($order_currency) && class_exists('\WOOMULTI_CURRENCY_F_Data')) {
            $setting = \WOOMULTI_CURRENCY_F_Data::get_ins();
            $default_currency = $setting->get_default_currency();
            if ($order_currency != $default_currency) {
                $total = $this->convertToDefaultCurrency($total, $order_currency);
            }
            return $total;
        }
        return $total;
    }

    function handleWoocommerceHelperOrderTotal($total, $order)
    {
        $woocommerce_helper = Woocommerce::getInstance();
        $order_currency = $woocommerce_helper->isMethodExists($order, 'get_currency') ? $order->get_currency() : '';
        if ($this->isEnabledVilaThemeCurrency() && !empty($order_currency) && class_exists('\WOOMULTI_CURRENCY_F_Data')) {
            $setting = \WOOMULTI_CURRENCY_F_Data::get_ins();
            $default_currency = $setting->get_default_currency();
            if ($order_currency != $default_currency) {
                $total = $this->convertToDefaultCurrency($total, $order_currency);
            }
            return $total;
        }
        return $total;
    }
}