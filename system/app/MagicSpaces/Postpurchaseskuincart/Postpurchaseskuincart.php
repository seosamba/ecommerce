<?php
/*
 * MAGICSPACE: postpurchasecode
 * {postpurchaseskuincart[:skuname1,skuname2,...]} ... {/postpurchaseskuincart}
 * - Render if sku exists in the cart
 */

class MagicSpaces_Postpurchaseskuincart_Postpurchaseskuincart extends Tools_MagicSpaces_Abstract
{

    protected function _run()
    {
        if (empty($this->_params[0])) {
            return $this->_spaceContent;
        }

        $registry = Zend_Registry::getInstance();
        if ($registry->isRegistered('postPurchaseCart')) {
            $skuInCarts = explode(',', filter_var($this->_params[0], FILTER_SANITIZE_STRING));

            $cartSession = $registry->get('postPurchaseCart');
            if ($cartSession instanceof Models_Model_CartSession) {
                $cartContent = $cartSession->getCartContent();
                if (!empty($cartContent)) {
                    $matched = false;
                    foreach ($cartContent as $content) {
                        if (in_array($content['sku'], $skuInCarts)) {
                            $matched = true;
                            break;
                        }
                    }

                    if ($matched === true) {
                        return $this->_spaceContent;
                    }
                }
            }

        }

        return '';
    }

}
