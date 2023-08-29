<?php
/*
 * MAGICSPACE: postpurchasestatus
 * {postpurchasestatus:partial,completed} ... {/postpurchasestatus}
 * - Render if product option exists in the cart
 */

class MagicSpaces_Postpurchasestatus_Postpurchasestatus extends Tools_MagicSpaces_Abstract
{
    protected $_parseBefore    = false;

    protected function _run()
    {
        if (empty($this->_params[0])) {
            return $this->_spaceContent;
        }

        $registry = Zend_Registry::getInstance();
        if ($registry->isRegistered('postPurchaseCart')) {
            $cartStatuses = explode(',', filter_var($this->_params[0], FILTER_SANITIZE_STRING));

            $cartSession = $registry->get('postPurchaseCart');
            if ($cartSession instanceof Models_Model_CartSession) {
                $cartStatus = $cartSession->getStatus();

                if (in_array($cartStatus, $cartStatuses)) {
                    return $this->_spaceContent;
                }
            }

        }

        return '';
    }

}
