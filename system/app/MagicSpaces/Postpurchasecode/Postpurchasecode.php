<?php
class MagicSpaces_Postpurchasecode_Postpurchasecode extends Tools_MagicSpaces_Abstract
{

    protected function _run()
    {
        $registry = Zend_Registry::getInstance();
        if ($registry->isRegistered('ConfirmationCartId')) {
            return $this->_spaceContent;
        }
        return '';

    }

}