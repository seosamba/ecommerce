<?php
/*
 * MAGICSPACE: postpurchaseoptionincart
 * {postpurchaseoptionincart[:skuname1,skuname2,...]} ... {/postpurchaseoptionincart}
 * - Render if product option exists in the cart
 */

class MagicSpaces_Postpurchaseoptionincart_Postpurchaseoptionincart extends Tools_MagicSpaces_Abstract
{
    protected $_parseBefore    = false;

    protected function _run()
    {
        if (empty($this->_params[0])) {
            return $this->_spaceContent;
        }

        $registry = Zend_Registry::getInstance();
        if ($registry->isRegistered('postPurchaseCart')) {
            $optionNames = explode(',', filter_var($this->_params[0], FILTER_SANITIZE_STRING));

            $cartSession = $registry->get('postPurchaseCart');
            if ($cartSession instanceof Models_Model_CartSession) {
                $cartContent = $cartSession->getCartContent();
                if (!empty($cartContent)) {
                    $matched = false;
                    foreach ($cartContent as $content) {
                        if (!empty($content['options'])) {
                            foreach ($optionNames as $optionName) {
                                if (array_key_exists($optionName, $content['options'])) {
                                    if (!empty($content['options'][$optionName]['title'])) {
                                        $matched = true;
                                        break;
                                    }
                                }
                            }
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
