<?php

/**
 * User landing
 *
 */
class Widgets_User_User extends Widgets_User_Base {

    const GATEWAY_QUOTE = 'Quote';

    const GRID_TYPE_DIGITAL = 'digital';

    const GRID_TYPE_RECURRING = 'recurring';

    const GRID_OPTION_WITHOUT_PERIOD_CYCLE = 'without_period_cycle';

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

    /**
     * render user orders grid
     *
     * @return string
     */
    protected function _renderGrid() {
        $customerObject = $this->_getCustomer();
        $userId = $customerObject->getId();
        $addresses = Models_Mapper_CustomerMapper::getInstance()->getUserAddressByUserId($userId);
        $enabledInvoicePlugin = Application_Model_Mappers_PluginMapper::getInstance()->findByName('invoicetopdf');
        if ($enabledInvoicePlugin != null) {
            if ($enabledInvoicePlugin->getStatus() == 'enabled') {
                $this->_view->invoicePlugin = 1;
            }
        }
        $this->_view->customer = $customerObject;
        if (isset($this->_options['0']) && $this->_options['0'] === self::GRID_TYPE_RECURRING) {
            return $this->_recurringOrdersGrid($userId, $addresses);

        }

        if (!empty($this->_options['0']) && $this->_options['0'] === self::GRID_TYPE_DIGITAL) {
            return $this->_digitalProductsGrid($userId);
        }

        $enabledInvoicePlugin = Application_Model_Mappers_PluginMapper::getInstance()->findByName('quote');
        if (!$enabledInvoicePlugin instanceof Application_Model_Models_Plugin) {
            return $this->_translator->translate('Please enable quote plugin to use user grid feature');
        }

        $orders = Models_Mapper_CartSessionMapper::getInstance()->fetchOrdersWithQuotes($userId);
        $this->_view->stats = array(
            'all'     => sizeof($orders),
            'completed' => sizeof(array_filter($orders, function ($order) {
                return $order['status'] === Models_Model_CartSession::CART_STATUS_COMPLETED;
            })),
            'shipped'   => sizeof(array_filter($orders, function ($order) {
                return $$order['status'] === Models_Model_CartSession::CART_STATUS_SHIPPED;
            })),
            'delivered' => sizeof(array_filter($orders, function ($order) {
                return $order['status'] === Models_Model_CartSession::CART_STATUS_DELIVERED;
            })),
            'partial' => sizeof(array_filter($orders, function ($order) {
                return $order['status'] === Models_Model_CartSession::CART_STATUS_PARTIAL;
            })),
            'quote_sent' => sizeof(array_filter($orders, function ($order) {
                return ($order['status'] === Models_Model_CartSession::CART_STATUS_PROCESSING && $order['status'] === 'Quote');
            })),
            'refunded' => sizeof(array_filter($orders, function ($order) {
                return $order['status'] === Models_Model_CartSession::CART_STATUS_REFUNDED;
        })),

        );
        $serviceLabelMapper = Models_Mapper_ShoppingShippingServiceLabelMapper::getInstance();
        $shippingServiceLabels = $serviceLabelMapper->fetchAllAssoc();
        if(!empty($orders) && !empty($shippingServiceLabels)){
            foreach ($orders as $index => $order) {
                if (!empty($shippingServiceLabels[$order['shipping_service']])) {
                    $orders[$index]['shipping_service'] = $shippingServiceLabels[$order['shipping_service']];
                }
            }
        }
        $this->_view->orders = $orders;
        return $this->_view->render('grid.phtml');

    }

    /**
     * Recurring user orders grid
     *
     * @param int $userId user id
     * @param array $addresses all current user addresses
     * @return string
     */
    protected function _recurringOrdersGrid($userId, $addresses)
    {
        $orders = Store_Mapper_RecurringPaymentsMapper::getInstance()->getRecurringOrdersDataByUserId($userId);
        $this->_view->stats = array(
            'all' => sizeof($orders),
            'new' => sizeof(array_filter($orders, function ($order) {
                return $order['recurring_status'] === Store_Model_RecurringPayments::NEW_RECURRING_PAYMENT;
            })),
            'active' => sizeof(array_filter($orders, function ($order) {
                return $order['recurring_status'] === Store_Model_RecurringPayments::ACTIVE_RECURRING_PAYMENT;
            })),
            'pending' => sizeof(array_filter($orders, function ($order) {
                return $order['recurring_status'] === Store_Model_RecurringPayments::PENDING_RECURRING_PAYMENT;
            })),
            'expired' => sizeof(array_filter($orders, function ($order) {
                return $order['recurring_status'] === Store_Model_RecurringPayments::EXPIRED_RECURRING_PAYMENT;
            })),
            'suspended' => sizeof(array_filter($orders, function ($order) {
                return $order['recurring_status'] === Store_Model_RecurringPayments::SUSPENDED_RECURRING_PAYMENT;
            })),
            'canceled' => sizeof(array_filter($orders, function ($order) {
                return $order['recurring_status'] === Store_Model_RecurringPayments::CANCELED_RECURRING_PAYMENT;
            }))
        );
        $this->_view->activeRecurringPaymentTypes = Store_Mapper_RecurringPaymentsMapper::getInstance()->getRecurringTypes();
        $this->_view->orders = $orders;
        $this->_view->addresses = $addresses;
        $this->_view->shippingEnabledStatuses = array(
            Models_Model_CartSession::CART_STATUS_COMPLETED,
            Models_Model_CartSession::CART_STATUS_SHIPPED,
            Models_Model_CartSession::CART_STATUS_DELIVERED
        );
        if (isset($this->_options[1]) && $this->_options[1] === self::GRID_OPTION_WITHOUT_PERIOD_CYCLE) {
            $this->_view->withoutPeriodCycle = $this->_options[1];
        }

        return $this->_view->render('recurring_user_grid.phtml');
    }

    /**
     * Render digital products grid
     *
     * @param int $userId user id
     * @return string
     */
    protected function _digitalProductsGrid($userId)
    {
        $digitalProducts = Store_Mapper_DigitalProductMapper::getInstance()->findDigitalProductsByUserId($userId);
        $this->_view->digitalProducts = $digitalProducts;
        return $this->_view->render('digital-products-grid.phtml');
    }

}
