<?php

class Widgets_Notifyme_Notifyme extends Widgets_Abstract {

    const DEFAULT_LIMIT = 20;

    protected $_cacheable      = false;

    protected $_redirector = null;

    protected $_websiteHelper = null;

    protected $_request = null;

    protected $_sessionHelper;

    protected $_productMapper = null;

    protected  $_limit = null;

    protected $_productTemplate = null;

    protected $_cleanListOnly = false;

    protected function _load() {
        $this->_request = Zend_Controller_Front::getInstance()->getRequest();
        $this->_productMapper = Models_Mapper_ProductMapper::getInstance();

        $currentController = $this->_request->getParam('controller');
        if (!preg_match('~backend_~', $currentController)) {
            $layout = Zend_Layout::getMvcInstance();
            //$layout->getView()->inlineScript()->appendFile($this->_websiteHelper->getUrl() . 'plugins/shopping/web/js/storewishlist.min.js');
        }

        $methodName = Tools_Plugins_Abstract::OPTION_MAKER_PREFIX.ucfirst(strtolower($this->_options[0]));
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }
    }

    protected function _init() {
        parent::_init();
        if (empty($this->_options)) {
            throw new Exceptions_SeotoasterWidgetException('No options provided');
        }
        $this->_view = new Zend_View();
        $this->_view->websiteUrl = Zend_Controller_Action_HelperBroker::getExistingHelper('website')->getUrl();
        $this->_view->setScriptPath(realpath(__DIR__.DIRECTORY_SEPARATOR.'views'));
        $this->_websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
        $this->_redirector = new Zend_Controller_Action_Helper_Redirector();
        $this->_sessionHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('session');
    }

    protected function _makeOptionAddToNotificationList() {
        $translator = Zend_Registry::get('Zend_Translate');

        $toFavoritesBtnName = $translator->translate('Add to favorites');
        $readyNotified = false;
        $isLogged = false;
        $goToProgile = 0;
        $htmlClassName = '';

        if(in_array('btnname', $this->_options)) {
            $btnOptionKey = array_search('btnname', $this->_options);
            $btnName = $this->_options[$btnOptionKey+1];
            if(!empty($btnName)) {
                $toFavoritesBtnName = preg_replace('~[^A-Za-z\s]+~','',$btnName);
            }
        }

        if(in_array('htmlclass', $this->_options)) {
            $classOptionKey = array_search('htmlclass', $this->_options);
            $htmlClassName = $this->_options[$classOptionKey+1];
            if(!empty($htmlClassName)) {
                $htmlClassName = preg_replace('~[^a-z1-9-_\s]+~','', $htmlClassName);
            }
        }

        $clientPage = Application_Model_Mappers_PageMapper::getInstance()->fetchByOption(Shopping::OPTION_STORE_CLIENT_LOGIN, true);
        $page = $this->_websiteHelper->getDefaultPage();
        if ($clientPage != null) {
            $page = $clientPage->getUrl();
        }

        $user = $this->_sessionHelper->getCurrentUser();
        $userId = $user->getId();

        if ($userId) {
            if(!empty($this->_options[1]) && is_numeric($this->_options[1])) {
                $productId = intval($this->_options[1]);
                if(!empty($productId)) {
                    $this->_view->productId = $productId;

                    $notifiedProductsMapper = Store_Mapper_NotifiedProductsMapper::getInstance();
                    $notifiedProduct = $notifiedProductsMapper->findByUserIdProductId($userId, $productId);

                    if($notifiedProduct instanceof Store_Model_NotifiedProductsModel) {
                        $readyNotified = true;
                    }
                }
            }

            if(in_array('profile', $this->_options)) {
                $goToProgile = 1;
            }
            $isLogged = true;
        }

        $this->_view->isLogged = $isLogged;
        $this->_view->clientPage = $page;
        $this->_view->readyNotified = $readyNotified;
        $this->_view->toFavoritesBtnName = $toFavoritesBtnName;
        $this->_view->goToProgile = $goToProgile;
        $this->_view->htmlClass = $htmlClassName;

        return $this->_view->render('to-client-notify-page.phtml');
    }

}
