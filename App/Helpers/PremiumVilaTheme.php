<?php
/**
 * @author      Wployalty (Mohan)
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @link        https://www.wployalty.net
 * */

namespace Wlac\App\Helpers;

use Wlr\App\Helpers\Woocommerce;

defined('ABSPATH') or die;

class PremiumVilaTheme implements Currency
{
    public static $instance = null;

    function getDefaultProductPrice($product_price, $product, $item, $is_redeem, $order_currency)
    {
        return $product_price;
    }

    function getProductPrice($product_price, $item, $is_redeem, $order_currency)
    {
        if (empty($order_currency)) {
            $order_currency = $this->getCurrentCurrencyCode($order_currency);
        }
        $default_currency = $this->getDefaultCurrency();
        if ($order_currency == $default_currency) {
            return $product_price;
        }
        if (!empty($order_currency)) {
            return $this->convertToDefaultCurrency($product_price, $order_currency);
        }
        return $product_price;
    }

    function getCurrentCurrencyCode($code = '')
    {
        if (class_exists('\WOOMULTI_CURRENCY_Data')) {
            $setting = \WOOMULTI_CURRENCY_Data::get_ins();
            return $setting->get_current_currency();
        }
        return $code;
    }

    function getDefaultCurrency($code = '')
    {
        if (class_exists('\WOOMULTI_CURRENCY_Data')) {
            $setting = \WOOMULTI_CURRENCY_Data::get_ins();
            return $setting->get_default_currency();
        }
        return $code;
    }

    function convertToDefaultCurrency($amount, $current_currency_code)
    {
        $default_currency = $this->getDefaultCurrency();
        if (!empty($default_currency) && $default_currency == $current_currency_code) {
            return $amount;
        }
        if (class_exists('\WOOMULTI_CURRENCY_Data')) {
            return (float)wmc_revert_price($amount, $current_currency_code);
        }
        return $amount;
    }

    function convertOrderTotal($total, $order)
    {
        $woocommerce_helper = Woocommerce::getInstance();
        $order = $woocommerce_helper->getOrder($order);
        $order_currency = $woocommerce_helper->isMethodExists($order, 'get_currency') ? $order->get_currency() : '';
        if (!empty($order_currency)) {
            return $this->convertToDefaultCurrency($total, $order_currency);
        }
        return $total;
    }

    public static function getInstance(array $config = array())
    {
        if (!self::$instance) {
            self::$instance = new self($config);
        }
        return self::$instance;
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

    function convertToCurrentCurrency($original_amount, $default_currency)
    {
        $current_currency_code = $this->getCurrentCurrencyCode();
        if (class_exists('\WOOMULTI_CURRENCY_Data')) {
            return (float)wmc_get_price($original_amount, $current_currency_code);
        }
        return $original_amount;
    }

    function getPriceFormat($amount, $code = '')
    {
        if (empty($code)) {
            return false;
        }
        if (!class_exists('\WOOMULTI_CURRENCY_Data')) {
            return $amount;
        }
        $setting = \WOOMULTI_CURRENCY_Data::get_ins();
        $selected_currencies = $setting->get_list_currencies();
        $currency = isset($selected_currencies[$code]) && is_array($selected_currencies[$code]) ? $selected_currencies[$code] : array();
        if (empty($currency)) {
            return $amount;
        }
        $currency_symbol = $this->getCurrencySymbol($currency, $code);
        $num_decimal = is_array($currency) && !empty($currency['decimals']) ? $currency['decimals'] : wc_get_price_decimals();
        $decimal_sep = wc_get_price_decimal_separator();
        $thousand_sep = wc_get_price_thousand_separator();

        $amount = number_format($amount, $num_decimal, $decimal_sep, $thousand_sep);
        $price_format = $this->getFormat($currency, $code);
        $formatted_price = sprintf($price_format, '<span class="woocommerce-Price-currencySymbol">' . $currency_symbol . '</span>', $amount);
        return '<span class="woocommerce-Price-amount amount"><bdi>' . $formatted_price . '</bdi></span>';
    }

    protected function getCurrencySymbol($current_currency, $code)
    {
        $woocommerce_helper = new Woocommerce();
        if (!is_array($current_currency)) {
            return $woocommerce_helper->getCurrencySymbols($code);
        }
        if (isset($current_currency['custom']) && !empty($current_currency['custom'])) {
            return $current_currency['custom'];
        }
        return $woocommerce_helper->getCurrencySymbols($code);
    }

    protected function getFormat($currency, $code = '')
    {
        $format = get_woocommerce_price_format();
        if (empty($code)) {
            return $format;
        }
        if (is_array($currency) && !empty($currency['pos'])) {
            switch ($currency['pos']) {
                case 'left':
                    $format = '%1$s%2$s';
                    break;
                case 'right':
                    $format = '%2$s%1$s';
                    break;
                case 'left_space':
                    $format = '%1$s&nbsp;%2$s';
                    break;
                case 'right_space':
                    $format = '%2$s&nbsp;%1$s';
                    break;
            }
        }
        return $format;
    }
}