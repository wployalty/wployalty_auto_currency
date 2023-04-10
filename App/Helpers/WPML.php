<?php
/**
 * @author      Wployalty (Alagesan)
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @link        https://www.wployalty.net
 * */

namespace Wlac\App\Helpers;

use Wlr\App\Helpers\Woocommerce;

class WPML implements Currency {

	function getDefaultProductPrice( $product_price, $product, $item, $is_redeem, $order_currency ) {
		global $woocommerce_wpml;
		$multi_currency = $woocommerce_wpml->get_multi_currency();
		$current_code   = $multi_currency->get_client_currency();
		return $this->convertToDefaultCurrency( $product_price, $current_code );
	}

	function convertToDefaultCurrency( $amount, $current_currency_code ) {
		$default_currency = $this->getDefaultCurrency();
		if ( ! empty( $default_currency ) && $default_currency == $current_currency_code ) {
			return $amount;
		}
		global $woocommerce_wpml;
		return (float) $woocommerce_wpml->multi_currency->prices->unconvert_price_amount( $amount, $current_currency_code );
	}

	function getDefaultCurrency( $code = '' ) {
		return wcml_get_woocommerce_currency_option();
	}

	function getProductPrice( $product_price, $item, $is_redeem, $order_currency ) {
		if ( empty( $order_currency ) ) {
			$order_currency = $this->getCurrentCurrencyCode( $order_currency );
		}
		$default_currency = $this->getDefaultCurrency();
		if ( $order_currency == $default_currency ) {
			return $product_price;
		}
		if ( ! empty( $order_currency ) ) {
			return $this->convertToDefaultCurrency( $product_price, $order_currency );
		}
		return $product_price;
	}

	function getCurrentCurrencyCode( $code = '' ) {
		global $woocommerce_wpml;
		$multi_currency = $woocommerce_wpml->get_multi_currency();
		return $multi_currency->get_client_currency();
	}

	function convertOrderTotal( $total, $order ) {
		$woocommerce_helper = Woocommerce::getInstance();
		$order              = $woocommerce_helper->getOrder( $total );
		$order_currency     = $woocommerce_helper->isMethodExists( $order, 'get_currency' ) ? $order->get_currency() : '';
		if ( ! empty( $order_currency ) ) {
			return $this->convertToDefaultCurrency( $total, $order_currency );
		}
		return $total;
	}

	function getCartSubtotal( $sub_total, $cart_data ) {
		$current_currency = $this->getCurrentCurrencyCode();
		return $this->convertToDefaultCurrency( $sub_total, $current_currency );
	}

	function getOrderSubtotal( $sub_total, $order_data ) {
		$woocommerce_helper = Woocommerce::getInstance();
		$order              = $woocommerce_helper->getOrder( $order_data );
		$order_currency     = $woocommerce_helper->isMethodExists( $order, 'get_currency' ) ? $order->get_currency() : '';
		if ( ! empty( $order_currency ) ) {
			return $this->convertToDefaultCurrency( $sub_total, $order_currency );
		}
		return $sub_total;
	}
}