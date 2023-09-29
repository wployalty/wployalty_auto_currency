<?php

namespace Wlac\App\Helpers;

use Wlr\App\Helpers\Woocommerce;

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
}