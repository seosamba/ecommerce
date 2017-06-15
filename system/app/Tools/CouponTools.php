<?php
/**
 * CouponTools.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_CouponTools {

	const STATUS_FAIL_COMBINATION   = 'fail_combination';

	const STATUS_FAIL_EXISTS        = 'fail_exists';

	const STATUS_FAIL_NOT_ACTIVE    = 'fail_not_active';

	const STATUS_FAIL_EXPIRED       = 'fail_expired';

	const STATUS_FAIL_PRODUCTS_MISSING = 'fail_products_missing';

	const STATUS_FAIL_NOT_REUSABLE  = 'fail_not_reusable';

	const STATUS_APPLIED            = true;


	public static function applyCoupons($coupons){
		$status = array();

		foreach ($coupons as $coupon){
			if (is_string($coupon)){
				$status[$coupon] = self::applyCoupon($coupon);
			} elseif ($coupon instanceof Store_Model_Coupon){
				$status[$coupon->getCode()] = self::applyCoupon($coupon);
			}
		}

		return $status;
	}

	public static function applyCoupon($coupon){
		if (empty($coupon)){
			return false;
		}

		if (!$coupon instanceof Store_Model_Coupon){
			if (is_string($coupon)){
				$coupon = Store_Mapper_CouponMapper::getInstance()->findByCode($coupon);
				return self::applyCoupon($coupon);
			} elseif (is_array($coupon)){
				return self::applyCoupons($coupon);
			}
		}


		//initial values

		$now = new DateTime();

		//getting cart instance
		$cart = Tools_ShoppingCart::getInstance();

		//getting currently used coupons
		$currentCoupons = $cart->getCoupons();
		if (is_null($currentCoupons)){
			$currentCoupons = array();
		}

		//fetching coupon codes array
		$currentCodes = array();
		if (!empty($currentCoupons)){
			$currentCodes = array_map(function($coupon) { return $coupon->getCode(); }, $currentCoupons);
		}

		//checking if we already have coupon that doesn't allow combination
		if (count($currentCoupons) === 1){
			$currentCoupon = reset($currentCoupons);
			if ((bool)$currentCoupon->getAllowCombination() === false){
				return self::STATUS_FAIL_COMBINATION;
			}
			unset ($currentCoupon);
		}

		// validating coupon
		if (!empty($currentCodes)){
			if (in_array($coupon->getCode(), $currentCodes)){
				return self::STATUS_FAIL_EXISTS;
			} elseif ( (bool)$coupon->getAllowCombination() === false){
				return self::STATUS_FAIL_COMBINATION;
			}
		}

		$startDate  = new DateTime($coupon->getStartDate());
		$endDate    = new DateTime($coupon->getEndDate());

		if ($startDate > $now) {
			return self::STATUS_FAIL_NOT_ACTIVE;
		} elseif ($now > $endDate){
			return self::STATUS_FAIL_EXPIRED;
		}

		if (null !== ($products = $coupon->getProducts())){
			$isValid = false;
			foreach($products as $productId){
				if ($cart->find($productId)){
					$isValid = true;
				}
			}
			if (false === $isValid){
				return self::STATUS_FAIL_PRODUCTS_MISSING;
			}
		}

		if ($coupon->getScope() === Store_Model_Coupon::DISCOUNT_SCOPE_CLIENT){
			if (Store_Mapper_CouponMapper::getInstance()->checkCouponByClientId($coupon->getId(), $cart->getCustomerId())){
				return self::STATUS_FAIL_NOT_REUSABLE;
			}
		}

		// saving coupon to cart session and recalculating cart
		array_push($currentCoupons, $coupon);
		$cart->setCoupons($currentCoupons);
		$cart->calculate(true);
		$cart->save();

        $customer = $cart->getCustomer();
        $cart->save()->saveCartSession($customer);

		return self::STATUS_APPLIED;
	}

	public static function processCoupons($coupons, $filter = null){
		$status = true;

		if (!is_null($filter)){
			$coupons = self::filterCoupons($coupons, $filter);
		}

		if (empty($coupons)){
			return false;
		}

		foreach ($coupons as $coupon) {
			$methodName = 'process'.strtolower($coupon->getType()).'Coupon';
			if (method_exists(__CLASS__, $methodName)){
				$status = $status && self::$methodName($coupon);
			}
		}

		return $status;
	}

	public static function filterCoupons($coupons, $filter = null){
		if (empty($coupons) || is_null($filter)){
			return array();
		}

		return array_filter($coupons, function($coupon) use ($filter) { return (strtolower($coupon->getType()) === strtolower($filter)); });
	}

    /**
     * Calculate discount using coupon code
     *
     * @param Store_Model_Coupon $coupon
     * @param array $orderSummary array('subTotalTax' => '', 'subTotal' = '')
     * @return bool|float
     */
	public static function processDiscountCoupon(Store_Model_Coupon $coupon, $orderSummary = array()) {
		if ($coupon->getType() !== Store_Model_Coupon::COUPON_TYPE_DISCOUNT){
			return false;
		}

        if (empty($orderSummary)) {
            $cart = Tools_ShoppingCart::getInstance();
            $orderAmount = floatval($cart->getSubTotal() + $cart->getTotalTax());
        } else {
            $orderAmount = $orderSummary['subTotal'] + $orderSummary['subTotalTax'];
        }
		$discount = 0;

		if ($orderAmount >= $coupon->getMinOrderAmount()){
			switch ($coupon->getDiscountUnit()){
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
