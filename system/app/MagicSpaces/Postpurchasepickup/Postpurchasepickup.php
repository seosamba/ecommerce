<?php
/*
 * MAGICSPACE: postpurchasepickup
 * {postpurchasepickup} ... {/postpurchasepickup} - Postpurchasepickup magic space is used to specify place where to display
 * information about purchase pickup
 */

class MagicSpaces_Postpurchasepickup_Postpurchasepickup extends Tools_MagicSpaces_Abstract
{

    protected function _run()
    {
        $registry = Zend_Registry::getInstance();
        if ($registry->isRegistered('postPurchasePickup')) {
            return $this->_spaceContent;
        }
        return '';
    }

}
