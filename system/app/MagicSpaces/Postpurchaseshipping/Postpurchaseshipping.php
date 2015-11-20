<?php
/*
 * MAGICSPACE: postpurchaseshipping
 * {postpurchaseshipping} ... {/postpurchaseshipping} - Postpurchaseshipping magic space is used to specify place where to display
 * information about purchase shipping
 */

class MagicSpaces_Postpurchaseshipping_Postpurchaseshipping extends Tools_MagicSpaces_Abstract
{

    protected function _run()
    {
        $registry = Zend_Registry::getInstance();
        if (!$registry->isRegistered('postPurchasePickup')) {
            return $this->_spaceContent;
        }
        return '';
    }

}
