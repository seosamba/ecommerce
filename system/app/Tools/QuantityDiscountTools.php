<?php
/**
 * Class Tools_QuantityDiscountTools
 */
class Tools_QuantityDiscountTools extends Tools_DiscountRulesTools
{

    public static function prepareDiscountRule($cartItem)
    {

        if (!empty($cartItem['id'])) {
            $discountsConfigData = Store_Mapper_DiscountMapper::getInstance()->getDiscountDataConfig(
                $cartItem['qty'],
                $cartItem['id']
            );
            if (!empty($discountsConfigData)) {
                $discountConfig = array_shift($discountsConfigData);
                return array(
                    'name' => 'quantitydiscount',
                    'discount' => $discountConfig['amount'],
                    'type' => $discountConfig['discount_price_type'],
                    'sign' => $discountConfig['discount_price_sign']
                );
            }
        }
        return array('name' => 'quantitydiscount', 'discount' => 0, 'type' => '', 'sign' => '');

    }

}
