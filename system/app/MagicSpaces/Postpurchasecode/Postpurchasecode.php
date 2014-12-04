<?php
/*
 * MAGICSPACE: postpurchasecode
 * {postpurchasecode} ... {/postpurchasecode} - Postpurchasecode magic space is used to specify place where to display
 * information about purchase
 */

class MagicSpaces_Postpurchasecode_Postpurchasecode extends Tools_MagicSpaces_Abstract
{

    protected function _run()
    {
        $registry = Zend_Registry::getInstance();
        if ($registry->isRegistered('postPurchaseCart')) {
            return $this->_spaceContent;
        }
        return '';
    }

}
