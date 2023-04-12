<?php
/**
 * @author      Wployalty (Alagesan)
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @link        https://www.wployalty.net
 * */

namespace Wlac\App\Helpers;

use Wlr\App\Helpers\Woocommerce;

class RealMag implements Currency {
	public static $instance = null;

	function getDefaultProductPrice( $product_price, $product, $item, $is_redeem, $order_currency ) {
		return $product_price;
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
		global $WOOCS;
		return isset( $WOOCS->current_currency ) ? $WOOCS->current_currency : $code;
	}

	function getDefaultCurrency( $code = '' ) {
		global $WOOCS;
		return $WOOCS->default_currency;
	}

	function convertToDefaultCurrency( $amount, $current_currency_code ) {
		$default_currency = $this->getDefaultCurrency();
		if ( ! empty( $default_currency ) && $default_currency == $current_currency_code ) {
			return $amount;
		}
		global $WOOCS;
		$currencies = $WOOCS->get_currencies();
		$rate       = isset( $currencies[ $current_currency_code ]['rate'] ) && ! empty( $currencies[ $current_currency_code ]['rate'] ) ? $currencies[ $current_currency_code ]['rate'] : 0;
		$decimal    = isset( $currencies[ $current_currency_code ]['decimals'] ) && ! empty( $currencies[ $current_currency_code ]['decimals'] ) ? $currencies[ $current_currency_code ]['decimals'] : 2;
		if ( $rate > 0 ) {
			$amount = $WOOCS->back_convert( $amount, $rate, $decimal );
		}
		return (float) $amount;
	}

	function convertOrderTotal( $total, $order ) {
		$woocommerce_helper = Woocommerce::getInstance();
		$order_data         = $woocommerce_helper->isMethodExists( $order, 'get_data' ) ? $order->get_data() : '';
		$order_currency     = ! empty( $order_data ) && is_array( $order_data ) && isset( $order_data['currency'] ) && ! empty( $order_data['currency'] ) ? $order_data['currency'] : '';
		$default_currency   = $this->getDefaultCurrency();
		if ( $order_currency != $default_currency ) {
			$total = $this->convertToDefaultCurrency( $total, $order_currency );
		}
		return $total;
	}

	public static function getInstance( array $config = array() ) {
		if ( ! self::$instance ) {
			self::$instance = new self( $config );
		}
		return self::$instance;
	}

	function getCartSubtotal( $sub_total, $cart_data ) {
		$current_currency = $this->getCurrentCurrencyCode();
		return $this->convertToDefaultCurrency( $sub_total, $current_currency );
	}

	function getOrderSubtotal( $sub_total, $order_data ) {
		$woocommerce_helper = Woocommerce::getInstance();
		$order              = $woocommerce_helper->getOrder( $order_data );
		$order_data         = $woocommerce_helper->isMethodExists( $order, 'get_data' ) ? $order->get_data() : '';
		$order_currency     = ! empty( $order_data ) && is_array( $order_data ) && isset( $order_data['currency'] ) && ! empty( $order_data['currency'] ) ? $order_data['currency'] : '';
		if ( ! empty( $order_currency ) ) {
			return $this->convertToDefaultCurrency( $sub_total, $order_currency );
		}
		return $sub_total;
	}
}