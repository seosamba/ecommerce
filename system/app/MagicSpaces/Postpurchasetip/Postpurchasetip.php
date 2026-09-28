<?php
/*
 * MAGICSPACE: postpurchasetip
 * {postpurchasetip} ... {/postpurchasetip} - Postpurchasetip magic space is used to specify place where to display
 * information about purchase tip if it added by POS app
 */

class MagicSpaces_Postpurchasetip_Postpurchasetip extends Tools_MagicSpaces_Abstract
{

    protected function _run()
    {
        $registry = Zend_Registry::getInstance();
        $enableSeosambaPosPlugin = false;

        $enabledSeosambaPosPlugin = Application_Model_Mappers_PluginMapper::getInstance()->findByName('seosambapos');
        if ($enabledSeosambaPosPlugin instanceof Application_Model_Models_Plugin) {
            if ($enabledSeosambaPosPlugin->getStatus() == Application_Model_Models_Plugin::ENABLED) {
                $enableSeosambaPosPlugin = true;
            }
        }

        if ($enableSeosambaPosPlugin && $registry->isRegistered('postPurchaseCart')) {
            $cartSession = $registry->get('postPurchaseCart');
            if ($cartSession instanceof Models_Model_CartSession) {
                $defaultProductSettingsMapper = Seosambapos_Models_Mappers_SeosambaposDefaultproductSettingMapper::getInstance();
                $defaultTipProductId = $defaultProductSettingsMapper->getConfigParam('defaultTipProductId');

                $cartContent = $cartSession->getCartContent();

                if (!empty($cartContent)) {
                    $tipVal = 0;
                    if(!empty($defaultTipProductId)) {
                        if(!empty($cartContent)) {
                            foreach ($cartContent as $cartItem) {
                                if($cartItem['product_id'] == $defaultTipProductId) {
                                    $tipVal = round($cartItem['price'], 2);
                                }
                            }
                        }
                    }

                    if($tipVal > 0) {
                        return $this->_spaceContent;
                    }
                }
            }
        }

        return '';
    }

}
