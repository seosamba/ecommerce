<?php

/**
 * User landing
 *
 */
class Widgets_User_User extends Widgets_User_Base {

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
     * @var Models_Model_Customer instance
     */
    protected $_customer = null;

    protected function _init() {
        parent::_init();
        $this->_cacheable = false;
        if (empty($this->_options)) {
            throw new Exceptions_SeotoasterWidgetException('No options provided');
        }
        $this->_view->addScriptPath(dirname(__FILE__) . '/views');


    }

    private function _getCustomer(){
        $customerId = $this->_sessionHelper->getCurrentUser()->getId();
        $customerMapper = Models_Mapper_CustomerMapper::getInstance();
        $customer = $customerMapper->find($customerId);
        if(is_null($customer)){
            return $customerMapper->find($this->_user->getId());
        }
        return $customer;
    }
    protected function _renderName() {
        return $this->_getCustomer()->getFullName();
    }

    protected function _renderRegistration() {
        return $this->_getCustomer()->getRegDate();
    }

    protected function _renderLastlogin() {
        return $this->_getCustomer()->getLastLogin();
    }

    protected function _renderEmail() {
        return $this->_getCustomer()->getEmail();
    }

    protected function _renderTabs() {
        if (!empty($this->_options)) {
            $tabsNames = explode(',', $this->_options[0]);
            $this->_view->tabsNames = $tabsNames;
            return $this->_view->render('tabs.phtml');
        } else {
            return '';
        }
    }

    protected function _renderAccount() {
        $form = new Forms_User();
        $this->_view->userForm = $form;
        $this->_view->currentEmail = $this->_getCustomer()->getEmail();
        return $this->_view->render('edit-account.phtml');
    }

    protected function _renderGrid() {
        $customerObject = $this->_getCustomer();
        $addresses = $customerObject->getAddresses();
        $enabledInvoicePlugin = Application_Model_Mappers_PluginMapper::getInstance()->findByName('invoicetopdf');
        if ($enabledInvoicePlugin != null) {
            if ($enabledInvoicePlugin->getStatus() == 'enabled') {
                $this->_view->invoicePlugin = 1;
            }
        }
        $this->_view->customer = $customerObject;
        $orders = Models_Mapper_CartSessionMapper::getInstance()->fetchAll(
            array('user_id = ?' => $customerObject->getId())
        );
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
