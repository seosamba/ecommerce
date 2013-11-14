<?php

/**
 * User landing
 *
 *
 */
class Widgets_User_User extends Widgets_Abstract {


    const GATEWAY_QUOTE = 'Quote';

	/**
     * @var Models_Mapper_ProductMapper Product Mapper
     */
    protected $_productMapper;

    /**
     * @var array Contains payment config
     * @static
     */
	protected static $_shoppingConfig = null;

    /**
	 * @var Models_Model_Product Product instance
	 */
	protected $_product = null;

    /**
     * @var null|string Type of widget
     */
    private $_type = null;

    /**
     * @var null|Zend_Currency Zend_Currency holder
     */
    private $_currency = null;

    /**
     * @var Models_Model_Customer instance
     */
    protected  $_customer = null;

	protected function _init(){
		parent::_init();
        $this->_cacheable = false;
        if (empty($this->_options)) {
            throw new Exceptions_SeotoasterWidgetException('No options provided');
        }

	}

	protected function _load(){
		if (empty($this->_options)){
			throw new Exceptions_SeotoasterWidgetException('No options provided');
		}

        $sessionHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('session');

        if ($sessionHelper->getCurrentUser()->getRoleId() == Tools_Security_Acl::ROLE_GUEST) {
            return '';
        }

        $this->_view = new Zend_View(array(
            'scriptPath' => dirname(__FILE__) . '/views'
        ));

        $this->_view->websiteUrl = Zend_Controller_Action_HelperBroker::getExistingHelper('website')->getUrl();

        $customerId = $sessionHelper->getCurrentUser()->getId();
        $this->_customer = Models_Mapper_CustomerMapper::getInstance()->find($customerId);
        if(!$this->_customer instanceof Models_Model_Customer){
            return '';
        }
        $this->_type = array_shift($this->_options);
        $methodName = '_render'.ucfirst(strtolower($this->_type));
		if (method_exists($this, $methodName)){
			return $this->$methodName();
		}
		return '<b>Method '. $this->_type .' doesn\'t exist</b>';
	}

    private function _renderName() {
        return $this->_customer->getFullName();
    }

    private function _renderRegistration(){
        return $this->_customer->getRegDate();
    }

    private function _renderLastlogin(){
        return $this->_customer->getLastLogin();
    }

    private function _renderEmail(){
        return $this->_customer->getEmail();
    }

    private function _renderTabs(){
        if(!empty($this->_options)){
            $tabsNames = explode(',',$this->_options[0]);
            $this->_view->tabsNames = $tabsNames;
            return $this->_view->render('tabs.phtml');
        }else{
            return '';
        }
    }

    private function _renderAccount(){
        $form = new Forms_User();
        $this->_view->userForm = $form;
        $this->_view->currentEmail = $this->_customer->getEmail();
        return $this->_view->render('edit-account.phtml');
    }

    private function _renderGrid(){
        $addresses = $this->_customer->getAddresses();
        $enabledInvoicePlugin = Application_Model_Mappers_PluginMapper::getInstance()->findByName('invoicetopdf');
        if ($enabledInvoicePlugin != null) {
            if ($enabledInvoicePlugin->getStatus() == 'enabled') {
                $this->_view->invoicePlugin = 1;
            }
        }
        $this->_view->customer = $this->_customer;
        $orders = Models_Mapper_CartSessionMapper::getInstance()->fetchAll(array('user_id = ?' => $this->_customer->getId()));
        $this->_view->stats = array(
            'all'     => sizeof($orders),
            'completed' => sizeof(array_filter($orders, function ($order) {
                return $order->getStatus() === Models_Model_CartSession::CART_STATUS_COMPLETED;
            })),
            'shipped'   => sizeof(array_filter($orders, function ($order) {
                return $order->getStatus() === Models_Model_CartSession::CART_STATUS_SHIPPED;
            })),
            'delivered' => sizeof(array_filter($orders, function ($order) {
                return $order->getStatus() === Models_Model_CartSession::CART_STATUS_DELIVERED;
            })),
            'quote_sent' => sizeof(array_filter($orders, function ($order) {
                return ($order->getStatus() === Models_Model_CartSession::CART_STATUS_PROCESSING && $order->getGateway() === 'Quote');
            }))

        );
        $this->_view->orders = $orders;
        return $this->_view->render('grid.phtml');

    }
}
