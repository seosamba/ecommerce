<?php
/**
 * CouponTools.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_CouponTools {

	public static function validateCoupons($coupons, $triggerApply = false){
		$validCoupons = array();

		$now = new DateTime();
		foreach ($coupons as $coupon){
			if (sizeof($coupons) > 1 && (bool)$coupon->getAllowCombination() === false) {
				continue;
			}
			$startDate  = new DateTime($coupon->getStartDate());
			$endDate    = new DateTime($coupon->getEndDate());

			if ($startDate > $now || $now > $endDate){
				continue;
			}

			if ($coupon->getScope() === Store_Model_Coupon::DISCOUNT_SCOPE_CLIENT){
				if (Store_Mapper_CouponMapper::getInstance()->checkCouponByClientId($coupon->getId(), Tools_ShoppingCart::getInstance()->getCustomerId())){
					continue;
				}
			}

			array_push($validCoupons, $coupon);
		}

		return $triggerApply === false ? $validCoupons : self::applyCoupons($validCoupons);
	}

	public static function applyCoupons($coupons, $filter = null){
		if (!is_null($filter)){
			$coupons = self::filterCoupons($coupons, $filter);
		}

		if (empty($coupons)){
			return false;
		}

		foreach ($coupons as $coupon) {
			$methodName = 'process'.strtolower($coupon->getType()).'Coupon';
			if (method_exists(__CLASS__, $methodName)){
				self::$methodName($coupon);
			}
		}

		return true;
	}

	public static function filterCoupons($coupons, $filter = null){
		if (empty($coupons) || is_null($filter)){
			return array();
		}

		return array_filter($coupons, function($coupon) use ($filter) { return (strtolower($coupon->getType()) === strtolower($filter)); });
	}

	public static function processDiscountCoupon(Store_Model_Coupon $coupon) {
		if ($coupon->getType() !== Store_Model_Coupon::COUPON_TYPE_DISCOUNT){
			return false;
		}

		$cart = Tools_ShoppingCart::getInstance();
		$orderAmount = floatval($cart->getSubTotal() + $cart->getTotalTax());
		$discount = 0;

		if ($orderAmount >= $coupon->getMinOrderAmount()){
			switch ($coupon->getDiscountUnits()){
				case Store_Model_Coupon::DISCOUNT_PERCENTS:
					$discount = $orderAmount * $coupon->getDiscountAmount() / 100;
					break;
				case Store_Model_Coupon::DISCOUNT_UNITS:
					$discount = $coupon->getDiscountAmount();
					break;
			}

		}

		return floatval($discount);
	}

	public static function processFreeshippingCoupon(Store_Model_Coupon $coupon){
		$minOrderAmount = floatval($coupon->getMinOrderAmount());
		$cartSummary = Tools_ShoppingCart::getInstance()->calculate();
		if (floatval($cartSummary['subTotal']) > $minOrderAmount){
			Tools_ShoppingCart::getInstance()->setShippingData(array(
				'service'   => Shopping::SHIPPING_FREESHIPPING,
				'type'      => 'Coupon: '.$coupon->getCode(),
				'price'     => 0
			))->save();

			return true;
		}

		return false;
	}
}
