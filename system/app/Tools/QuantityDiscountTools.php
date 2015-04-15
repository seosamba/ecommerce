<?php
/**
 * Class Tools_QuantityDiscountTools
 */
class Tools_QuantityDiscountTools extends Tools_DiscountRulesTools
{

    /**
     * flag for local discount
     */
    const LOCAL_DISCOUNT_ENABLED = 'enabled';

    /**
     * Process discount rule
     *
     * @param array $cartItem cart item
     * @return array
     */
    public static function prepareDiscountRule($cartItem)
    {

        if (!empty($cartItem['id'])) {
            $discountsConfigData = Store_Mapper_DiscountMapper::getInstance()->getDiscountDataConfig(
                $cartItem['id'],
                $cartItem['qty'],
                false,
                true
            );
            if (!empty($discountsConfigData)) {
                $discountsConfigData = array_reverse($discountsConfigData, true);
                $discountConfig = array();
                foreach ($discountsConfigData as $discountConfigItem) {
                    if ($discountConfigItem['status'] === self::LOCAL_DISCOUNT_ENABLED && $discountConfigItem['quantity'] <= $cartItem['qty']) {
                        $discountConfig = $discountConfigItem;
                        break;
                    }
                }
                if (!empty($discountConfig)) {
                    return array(
                        'name' => 'q-ty discount',
                        'discount' => $discountConfig['amount'],
                        'type' => $discountConfig['price_type'],
                        'sign' => $discountConfig['price_sign']
                    );
                }
            }
        }
        return array('name' => 'q-ty discount', 'discount' => 0, 'type' => '', 'sign' => '');

    }

}
