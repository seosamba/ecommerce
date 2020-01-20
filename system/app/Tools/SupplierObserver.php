<?php

/**
 * SupplierObserver.php
 */
class Tools_SupplierObserver implements Interfaces_Observer
{

    /**
     * Previous cart status
     *
     * @var string
     */
    private $_oldStatus;

    /**
     * @var Models_Model_CartSession
     */
    private $_object;

    /**
     * @param string $oldStatus Previous status to track inventory changes
     * @param null|array $options
     */
    public function __construct($oldStatus, $options = null)
    {
        $this->_oldStatus = $oldStatus;
        if (!is_null($options)) {
            $this->_options = $options;
        }
    }

    /**
     * @param $object Models_Model_CartSession
     * @return bool
     */
    public function notify($object)
    {
        $this->_object = $object;
        $currentStatus = $object->getStatus();
        switch ($this->_oldStatus) {
            case Models_Model_CartSession::CART_STATUS_NEW:
            case Models_Model_CartSession::CART_STATUS_PENDING:
            case Models_Model_CartSession::CART_STATUS_PROCESSING:
                if ($currentStatus === Models_Model_CartSession::CART_STATUS_COMPLETED) {
                    return $this->_sendEmailToSupplier($currentStatus);
                }
                break;
            case Models_Model_CartSession::CART_STATUS_COMPLETED:
                if ($currentStatus === Models_Model_CartSession::CART_STATUS_SHIPPED) {
                    return $this->_sendEmailToSupplier($currentStatus);
                }
                break;
            default:
                break;
        }

        return true;
    }

    private function _sendEmailToSupplier($currentStatus)
    {
        $companyProductsMapper = Store_Mapper_CompanyProductsMapper::getInstance();
        $productIds = array();
        $cartContent = $this->_object->getCartContent();
        foreach ($cartContent as $content) {
            $productIds[$content['product_id']] = $content['product_id'];
        }

        $where = $companyProductsMapper->getDbTable()->getAdapter()->quoteInto('sp.id IN (?)', $productIds);
        $select = $companyProductsMapper->getDbTable()->getAdapter()->select()->from(array('sp' => 'shopping_product'),
            array('sp.id', 'p.url', 'sp.name'))
            ->joinLeft(array('p' => 'page'), 'p.id=sp.page_id', array())->where($where);
        $productPagesUrls = $companyProductsMapper->getDbTable()->getAdapter()->fetchAssoc($select);

        $supplierProductsData = $companyProductsMapper->getGroupedBySupplierData($productIds);
        if (!empty($supplierProductsData)) {
            foreach ($supplierProductsData as $supplierProduct) {
                $supplierId = $supplierProduct['supplier_id'];
                $userModel = Application_Model_Mappers_UserMapper::getInstance()->find($supplierId);
                if (!$userModel instanceof Application_Model_Models_User) {
                    continue;
                }
                $userRoleId = $userModel->getRoleId();
                if ($userRoleId !== Shopping::ROLE_SUPPLIER) {
                    continue;
                }

                $triggerName = Tools_StoreMailWatchdog::TRIGGER_SUPPLIER_COMPLETED;
                if ($currentStatus === Models_Model_CartSession::CART_STATUS_SHIPPED) {
                    $triggerName = Tools_StoreMailWatchdog::TRIGGER_SUPPLIER_SHIPPED;
                }

                $session = Zend_Controller_Action_HelperBroker::getStaticHelper('session');

                $session->storeCartSessionKey = $this->_object->getId();
                $session->storeCartSessionConversionKey = $this->_object->getId();

                $userModel->registerObserver(new Tools_Mail_Watchdog(array(
                    'trigger' => $triggerName,
                    'productIds' => explode(',', $supplierProduct['productsIds']),
                    'productPagesUrls' => $productPagesUrls
                )));
                $userModel->notifyObservers();
            }
        }

        return true;
    }


}
