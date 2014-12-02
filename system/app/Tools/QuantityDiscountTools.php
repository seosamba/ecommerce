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
                $cartItem['id'],
                $cartItem['qty']
            );
            if (!empty($discountsConfigData)) {
                $discountConfig = array_shift($discountsConfigData);
                return array(
                    'name' => 'quantitydiscount',
                    'discount' => $discountConfig['amount'],
                    'type' => $discountConfig['price_type'],
                    'sign' => $discountConfig['price_sign']
                );
            }
        }
        return array('name' => 'quantitydiscount', 'discount' => 0, 'type' => '', 'sign' => '');

    }

}
