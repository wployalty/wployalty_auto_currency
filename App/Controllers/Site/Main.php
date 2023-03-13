<?php
/**
 * @author      Wployalty (Alagesan)
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @link        https://www.wployalty.net
 * */

namespace Wlac\App\Controllers\Site;

use Wlac\App\Controllers\Base;
use Wlr\App\Helpers\Woocommerce;

defined('ABSPATH') or die;

class Main extends Base
{
    public static $active_plugin_list = array();

    function getDefaultProductPrice($productPrice, $product, $item, $is_redeem, $orderCurrency)
    {
        $woocommerce_helper = Woocommerce::getInstance();
        $woocommerce_helper->_log('getDefaultProductPrice Staring');
        $woocommerce_helper->_log('Input price:' . $productPrice);
        $status = false;//later use for other currency
        /*if ($this->isEnableRealMagCurrency()) {
            $productPrice = apply_filters('woocs_convert_price', $productPrice, false);
            $status = true;
        }*/
        if (!$status && $this->isEnabledVilaThemeCurrency()) {
            return $productPrice;
        }
        /*if (!$status && $this->isEnabledAeliaoCurrency()) {
            $data = $woocommerce_helper->isMethodExists($product, 'get_data') ? $product->get_data() : array();
            if (isset($data['price']) && $data['price'] == $productPrice && isset($GLOBALS['woocommerce-aelia-currencyswitcher']) && !empty($GLOBALS['woocommerce-aelia-currencyswitcher']) && is_object($GLOBALS['woocommerce-aelia-currencyswitcher'])) {
                //convert price
                $code = $GLOBALS['woocommerce-aelia-currencyswitcher']->get_selected_currency();
                $current_currency_code = $GLOBALS['woocommerce-aelia-currencyswitcher']->base_currency();
                $productPrice = $GLOBALS['woocommerce-aelia-currencyswitcher']->convert($productPrice, $current_currency_code, $code, $price_decimals = null, $include_markup = true);
            }
        }*/
        $woocommerce_helper->_log('OutPut price:' . $productPrice);
        return $productPrice;
    }

    function isEnabledVilaThemeCurrency()
    {
        //Ref: https://wordpress.org/plugins/woo-multi-currency/
        return $this->isPluginIsActive('woo-multi-currency/woo-multi-currency.php');
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
        $woocommerce_helper = Woocommerce::getInstance();
        $woocommerce_helper->_log('getProductPrice Staring');
        $woocommerce_helper->_log('Input price:' . $productPrice);
        $status = false;//later use for other currency
        /*if ($this->isEnableRealMagCurrency()) {
            $status = true;
        }*/
        if (!$status && $this->isEnabledVilaThemeCurrency()) {
            return $productPrice;
        }
        /*if (!$status && $this->isEnabledWPMLCurrency()) {
            $status = true;
        }*/
        $woocommerce_helper->_log('OutPut price:' . $productPrice);
        return $productPrice;
    }

    function getCurrentCurrencyCode($code)
    {
        $woocommerce_helper = Woocommerce::getInstance();
        $woocommerce_helper->_log('getCurrentCurrencyCode Staring');
        $woocommerce_helper->_log('Input code:' . $code);
        $status = false;
        /*if ($this->isEnableRealMagCurrency()) {
            global $WOOCS;
            $code = $WOOCS->current_currency;
            $status = true;
        }*/
        if ($this->isEnabledVilaThemeCurrency() && class_exists('\WOOMULTI_CURRENCY_F_Data')) {
            $setting = \WOOMULTI_CURRENCY_F_Data::get_ins();
            return $setting->get_current_currency();
        }
        /*if (!$status && $this->isEnabledWPMLCurrency()) {
            global $woocommerce_wpml;
            $multi_currency = $woocommerce_wpml->get_multi_currency();
            $code = $multi_currency->get_client_currency();
            $status = true;
        }
        if (!$status && $this->isEnabledAeliaoCurrency() && isset($GLOBALS['woocommerce-aelia-currencyswitcher']) && !empty($GLOBALS['woocommerce-aelia-currencyswitcher']) && is_object($GLOBALS['woocommerce-aelia-currencyswitcher'])) {
            $code = $GLOBALS['woocommerce-aelia-currencyswitcher']->get_selected_currency();
            $status = true;
        }*/
        $woocommerce_helper->_log('OutPut code:' . $code);
        return $code;
    }

    function convertToDefaultCurrency($amount, $current_currency_code)
    {
        $woocommerce_helper = Woocommerce::getInstance();
        $woocommerce_helper->_log('convertToDefaultCurrency Staring');
        $woocommerce_helper->_log('Input amount:' . $amount);

        $status = false;
        /* if ($this->isEnableRealMagCurrency()) {
             global $WOOCS;
             if ($WOOCS->default_currency != $current_currency_code) {
                 $currencies = $WOOCS->get_currencies();
                 $rate = isset($currencies[$current_currency_code]['rate']) && !empty($currencies[$current_currency_code]['rate']) ? $currencies[$current_currency_code]['rate'] : 0;
                 $decimal = isset($currencies[$current_currency_code]['decimals']) && !empty($currencies[$current_currency_code]['decimals']) ? $currencies[$current_currency_code]['decimals'] : 2;
                 if ($rate > 0) {
                     $amount = $WOOCS->back_convert($amount, $rate, $decimal);
                 }
             }
             $status = true;
         }*/
        if (!$status && $this->isEnabledVilaThemeCurrency() && class_exists('\WOOMULTI_CURRENCY_F_Data')) {
            $setting = \WOOMULTI_CURRENCY_F_Data::get_ins();
            $default_currency = $setting->get_default_currency();
            if ($default_currency != $current_currency_code) {
                $amount = wmc_revert_price($amount, $current_currency_code);
            }
            $status = true;
        }

        /*if (!$status && $this->isEnabledWPMLCurrency()) {
            global $woocommerce_wpml;
            $default_currency = wcml_get_woocommerce_currency_option();//$woocommerce_wpml->multi_currency->get_default_currency();
            if ($default_currency != $current_currency_code) {
                $amount = $woocommerce_wpml->multi_currency->prices->unconvert_price_amount($amount, $current_currency_code);
                //wcml_convert_price($amount, $current_currency_code);
            }
            $status = true;
        }
        if (!$status && $this->isEnabledAeliaoCurrency() && isset($GLOBALS['woocommerce-aelia-currencyswitcher']) && !empty($GLOBALS['woocommerce-aelia-currencyswitcher']) && is_object($GLOBALS['woocommerce-aelia-currencyswitcher'])) {
            $default_currency = $GLOBALS['woocommerce-aelia-currencyswitcher']->base_currency();
            if ($default_currency != $current_currency_code) {
                $amount = $GLOBALS['woocommerce-aelia-currencyswitcher']->convert($amount, $current_currency_code, $default_currency, $price_decimals = null, $include_markup = true);
            }
        }*/
        $woocommerce_helper->_log('Output amount:' . $amount);
        return $amount;
    }
}