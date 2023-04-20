<?php
/**
 * @author      Wployalty (Alagesan)
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @link        https://www.wployalty.net
 * */

namespace Wlac\App\Helpers;

use Wlr\App\Helpers\Woocommerce;

class Aelia implements Currency
{
    public static $instance = null;

    function getDefaultProductPrice($product_price, $product, $item, $is_redeem, $order_currency)
    {
        $current_code = isset($GLOBALS['woocommerce-aelia-currencyswitcher']) ? $GLOBALS['woocommerce-aelia-currencyswitcher']->get_selected_currency() : '';
        if (!empty($current_code)) {
            $product_price = $this->convertToDefaultCurrency($product_price, $current_code);
        }
        return $product_price;
    }

    function convertToDefaultCurrency($amount, $current_currency_code)
    {
        $default_currency = $this->getDefaultCurrency();
        if (!empty($default_currency) && $default_currency == $current_currency_code) {
            return $amount;
        }
        return (float)$GLOBALS['woocommerce-aelia-currencyswitcher']->convert($amount, $current_currency_code, $default_currency, $price_decimals = null, $include_markup = true);
    }

    function getDefaultCurrency($code = '')
    {
        return $GLOBALS['woocommerce-aelia-currencyswitcher']->base_currency();
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
        return $GLOBALS['woocommerce-aelia-currencyswitcher']->get_selected_currency();
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
        $price_decimals = null;
        return (float)$GLOBALS['woocommerce-aelia-currencyswitcher']->convert($original_amount, $default_currency, $current_currency_code, $price_decimals, $include_markup = true);
    }
}