<?php
/**
 * MAGICSPACE: customeronly
 * {freeshipment[:not]} ... {/freeshipment} - displays content if user eligible for free shipment
 *
 * Class MagicSpaces_Freeshipment_Freeshipment
 */
class MagicSpaces_Freeshipment_Freeshipment extends Tools_MagicSpaces_Abstract {
	/**
	 * @return string
	 */
	protected function _run()
	{
        $shippingConfigMapper = Models_Mapper_ShippingConfigMapper::getInstance();
        $freeShippingConfig = $shippingConfigMapper->find(Shopping::SHIPPING_FREESHIPPING);
        $eligible = true;
        if (in_array('not', $this->_params)) {
            $eligible = false;
        }

        if (!empty($freeShippingConfig['config']) && !empty($freeShippingConfig['enabled'])) {

            $cart = Tools_ShoppingCart::getInstance();
            if (empty($cart)) {
                if ($eligible === true) {
                    return '';
                } else {
                    return $this->_spaceContent;
                }
            }

            $cartAmount = $cart->calculateCartPrice();

            if (empty($cart->getShippingAddressKey())){
                if ($eligible === true) {
                    if ($cartAmount > $freeShippingConfig['config']['cartamount']) {
                        return $this->_spaceContent;
                    }
                    return '';
                } else {
                    if ($cartAmount > $freeShippingConfig['config']['cartamount']) {
                        return '';
                    }

                    return $this->_spaceContent;
                }
            }

            $shippingAddress = $cart->getAddressById($cart->getShippingAddressKey());
            $cartAmount = $cart->calculateCartPrice();
            if (empty($cartAmount)) {
                if ($eligible === true) {
                    return '';
                } else {
                    return $this->_spaceContent;
                }
            }

            $shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
            $cartContent = $cart->getContent();
            $quantityOfCartProducts = count($cartContent);
            $freeShippingProductsQuantity = 0;
            if (is_array($cartContent) && !empty($cartContent)) {
                foreach ($cartContent as $cartItem) {
                    if ($cartItem['freeShipping'] == 1) {
                        $freeShippingProductsQuantity += 1;
                    }
                }
            }

            if ($freeShippingProductsQuantity == $quantityOfCartProducts) {
                if ($eligible === true) {
                    return '';
                } else {
                    return $this->_spaceContent;
                }
            }

            $deliveryType = $shoppingConfig['country'] == $shippingAddress['country'] ? Forms_Shipping_FreeShipping::DESTINATION_NATIONAL : Forms_Shipping_FreeShipping::DESTINATION_INTERNATIONAL;

            if ($freeShippingConfig['config']['destination'] === Forms_Shipping_FreeShipping::DESTINATION_BOTH
                || $freeShippingConfig['config']['destination'] === $deliveryType
            ) {
                if ($cartAmount > $freeShippingConfig['config']['cartamount']) {
                    if ($eligible === true) {
                        return $this->_spaceContent;
                    } else {
                        return '';
                    }
                }

            } elseif ($freeShippingConfig['config']['destination'] > 0) {
                $zoneId = Tools_Tax_Tax::getZone($shippingAddress, false);
                if ($zoneId == $freeShippingConfig['config']['destination']) {
                    if ($cartAmount > $freeShippingConfig['config']['cartamount']) {
                        if ($eligible === true) {
                            return $this->_spaceContent;
                        } else {
                            return '';
                        }
                    }
                }
            }

            if ($eligible === true) {
                return '';
            } else {
                return $this->_spaceContent;
            }

        }

	}

}
