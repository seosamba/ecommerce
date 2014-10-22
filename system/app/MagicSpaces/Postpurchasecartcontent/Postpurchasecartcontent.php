<?php
class MagicSpaces_Postpurchasecartcontent_Postpurchasecartcontent extends Tools_MagicSpaces_Abstract
{

    protected function _run()
    {
        $registry = Zend_Registry::getInstance();
        if ($registry->isRegistered('postPurchaseCart')) {
            $content = '';
            $tmpPageContent = $this->_content;
            $this->_content = $this->_findPageTemplateContent();
            $spaceContent = $this->_parse();
            $this->_content = $tmpPageContent;
            if (!$spaceContent) {
                $spaceContent = $this->_parse();
            }
            $cartSession = $registry->get('postPurchaseCart');
            if ($cartSession instanceof Models_Model_CartSession) {
                $cartContent = $cartSession->getCartContent();
                if (!empty($cartContent)) {
                    foreach ($cartContent as $sid => $cartItem) {
                        $content .= preg_replace_callback(
                            '~{\$postpurchase:(cartitem:(.+))}~uU',
                            function ($matches) use ($sid) {
                                $options = array_merge(array($sid), explode(':', $matches[1]));
                                return Tools_Factory_WidgetFactory::createWidget('Postpurchase', $options)->render();
                            },
                            $spaceContent
                        );
                    }
                }
                return $content;
            }
            return '';
        }
    }

    protected function _findPageTemplateContent()
    {
        $page = Application_Model_Mappers_PageMapper::getInstance()->find($this->_toasterData['id']);
        $template = Application_Model_Mappers_TemplateMapper::getInstance()->find($page->getTemplateId());
        unset($page);
        if (!$template instanceof Application_Model_Models_Template) {
            return false;
        }
        return $template->getContent();
    }

}
