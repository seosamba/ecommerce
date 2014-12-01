<?php
/**
 * Tools for calculation discounts for items in cart
 *
 * Class Tools_DiscountTools
 */
class Tools_DiscountTools
{

    /**
     * System cart item discounts
     *
     * @var array
     */
    public static $_systemDiscounts = array(
        'Tools_GroupPriceTools',
        'Tools_QuantityDiscountTools'
    );

    /**
     * Applying all discounts to cart item
     *
     * @param $originalPrice
     * @param array $discounts
     * @param bool $excludeDiscount
     * @return string
     */
    public static function calculateDiscountPrice($originalPrice, $discounts, $excludeDiscount = false)
    {
        foreach ($discounts as $discountData) {
            if (($excludeDiscount && $discountData['name'] === $excludeDiscount) || empty($discountData['type'])) {
                continue;
            }
            $originalPrice = self::applyDiscountData($originalPrice, $discountData);
        }
        return $originalPrice;
    }

    /**
     * precalculating discount for single item
     *
     * @param $price
     * @param ex: array('name', 'type', 'sign', 'discount') $discountData
     * @param bool if true subtraction will be analyzed like addition and in reverse $reverse
     * @return mixed
     */
    public static function applyDiscountData($price, $discountData, $reverse = false)
    {
        $resultPrice = $price;
        $priceModificationValue = 0;
        if ($discountData['type'] === 'percent') {
            $priceModificationValue = $price * $discountData['discount'] / 100;
        }
        if ($discountData['type'] === 'unit') {
            $priceModificationValue = $discountData['discount'];
        }
        if ($reverse) {
            if ($discountData['sign'] === 'minus') {
                $resultPrice = $price + $priceModificationValue;
            }
            if ($discountData['sign'] === 'plus') {
                $resultPrice = $price - $priceModificationValue;
            }
        } else {
            if ($discountData['sign'] === 'minus') {
                $resultPrice = $price - $priceModificationValue;
            }
            if ($discountData['sign'] === 'plus') {
                $resultPrice = $price + $priceModificationValue;
            }
        }
        return $resultPrice;
    }

    /**
     * Adding new discount rule or updating existing rule if exists
     *
     * @param array Existing discounts $discounts
     * @param array ex: array('name', 'type', 'sign', 'discount') new discount $newDiscount
     * @return mixed
     */
    public static function addAdditionalDiscountRule($discounts, $newDiscount)
    {
        if (!empty($discounts)) {
            $inDiscount = false;
            foreach ($discounts as $key => $discount) {
                if ($discount['name'] === $newDiscount['name']) {
                    $discounts[$key]['name'] = $newDiscount['name'];
                    $discounts[$key]['discount'] = $newDiscount['discount'];
                    $discounts[$key]['type'] = $newDiscount['type'];
                    $discounts[$key]['sign'] = $newDiscount['sign'];
                    $inDiscount = true;
                    break;
                }
            }
            if (!$inDiscount) {
                array_push($discounts, $newDiscount);
            }
        } else {
            array_push($discounts, $newDiscount);
        }
        return $discounts;
    }

    /**
     * return list of active discounts
     * (Return just system discounts)
     *
     * @return array
     */

    public static function getActiveDiscounts()
    {
        return self::$_systemDiscounts;
    }

    /**
     * Analyze current active discounts and apply it to cart item
     *
     * @param array Single cart item $cartItem
     * @return array
     */
    public static function applyDiscountRules($cartItem)
    {
        $activeDiscountsNames = self::getActiveDiscounts();
        foreach ($activeDiscountsNames as $activeDiscountName) {
            if (class_exists($activeDiscountName) && is_subclass_of($activeDiscountName, 'Tools_DiscountRulesTools')) {
                $discountData = $activeDiscountName::prepareDiscountRule($cartItem);
                if (!empty($discountData)) {
                    $cartItem['productDiscounts'] = self::addAdditionalDiscountRule(
                        $cartItem['productDiscounts'],
                        $discountData
                    );
                }
            }
        }
        $originalDiscountedPrice = self::calculateDiscountPrice(
            $cartItem['originalPrice'],
            $cartItem['productDiscounts']
        );

        $cartItem['price'] = $originalDiscountedPrice;
        return $cartItem;
    }
}
