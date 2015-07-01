<?php

/**
 * RecurringPaymentsMapper.php
 *
 * @method Store_Mapper_RecurringPaymentsMapper   getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Store_Mapper_RecurringPaymentsMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_RecurringPayments';

    protected $_dbTable = 'Store_DbTable_RecurringPayments';

    /**
     * Saving recurring payments info
     *
     * @param Store_Model_RecurringPayments $model
     * @return mixed
     * @throws Exceptions_SeotoasterException
     */
    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            $model = new $this->_model($model);
        }

        $data = array(
            'cart_id' => $model->getCartId(),
            'subscription_id' => $model->getSubscriptionId(),
            'ipn_tracking_id' => $model->getIpnTrackingId(),
            'gateway_type' => $model->getGatewayType(),
            'payment_period' => $model->getPaymentPeriod(),
            'recurring_times' => $model->getRecurringTimes(),
            'subscription_date' => $model->getSubscriptionDate(),
            'payment_cycle_amount' => $model->getPaymentCycleAmount(),
            'total_amount_paid' => $model->getTotalAmountPaid(),
            'last_payment_date' => $model->getLastPaymentDate(),
            'recurring_status' => $model->getRecurringStatus(),
            'custom_type' => $model->getCustomType(),
            'accept_changing_next_billing_date' => $model->getAcceptChangingNextBillingDate(),
            'accept_changing_shipping_address' => $model->getAcceptChangingShippingAddress(),
            'free_transaction_cycle' => $model->getFreeTransactionCycle(),
            'next_payment_date' => $model->getNextPaymentDate(),
            'transactions_quantity' => $model->getTransactionsQuantity()
        );

        $recurringPaymentExists = $this->getByCartId($data['cart_id']);
        if ($recurringPaymentExists) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('cart_id = ?', $data['cart_id']);
            unset($data['cart_id']);
            $this->getDbTable()->update($data, $where);
        } else {
            $id = $this->getDbTable()->insert($data);
            if ($id) {
                $model->setId($id);
            } else {
                throw new Exceptions_SeotoasterException('Can\'t save coupon');
            }
        }

        return $model;
    }

    /**
     * Save and attach regular payment to recurring
     *
     * @param int $recurringCartId recurring cart (parent cart id)
     * @param int $originalCartId regular payment cart id
     */
    public function saveRelatedRecurring($recurringCartId, $originalCartId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('recurring_cart_id = ?', $recurringCartId);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('cart_id = ?', $originalCartId);
        $select = $this->getDbTable()->getAdapter()->select()
            ->from('shopping_cart_session_has_recurring')->where($where);
        $relatedRecurringExist = $this->getDbTable()->getAdapter()->fetchAll($select);
        if (empty($relatedRecurringExist)) {
            $data = array('recurring_cart_id' => $recurringCartId, 'cart_id' => $originalCartId);
            $this->getDbTable()->getAdapter()->insert('shopping_cart_session_has_recurring', $data);
        }

    }

    /**
     * Get recurring payments by cart id
     *
     * @param int $cartId cart id
     * @return array|null
     */
    public function getByCartId($cartId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('cart_id = ?', $cartId);

        return $this->fetchAll($where);
    }

    /**
     * Get active recurring payments by date
     *
     * @param string $date
     * @param array $statuses
     * @return array|null
     */
    public function getRecurentsByDate($date, $statuses = array())
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('next_payment_date = ?', $date);
        $where .= ' AND '. $this->getDbTable()->getAdapter()->quoteInto('recurring_status IN (?)', $statuses);
        return $this->fetchAll($where);
    }

    /**
     * Get whole information about order by user id
     *
     * @param int $userId user id
     * @return array
     */
    public function getRecurringOrdersDataByUserId($userId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('sct.user_id = ?', $userId);
        $select = $this->getDbTable()->getAdapter()->select()->from(array('scp' => 'shopping_recurring_payment'))
            ->join(array('sct' => 'shopping_cart_session'), 'scp.cart_id=sct.id')
            ->join(array('scshr' => 'shopping_cart_session_has_recurring'), 'sct.id=scshr.recurring_cart_id', array('dependentOrders' => new Zend_Db_Expr('GROUP_CONCAT(scshr.cart_id)')))
            ->where($where)
            ->order('scp.recurring_status')
            ->group('scshr.recurring_cart_id');

        return $this->getDbTable()->getAdapter()->fetchAll($select);
    }

    /**
     * Get existing recurring types from general shopping config
     *
     * @param string $status recurring payment status
     * @return array
     */
    public function getRecurringTypes($status = Api_Store_Recurringtypes::RECURRING_PAYMENT_TYPE_STATUS_ENABLED)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('name IN (?)',
            Api_Store_Recurringtypes::$recurringAcceptType);
        $where .= ' AND '. $this->getDbTable()->getAdapter()->quoteInto('value = ?', $status);
        $select = $this->getDbTable()->getAdapter()->select()->from(array('sc' => 'shopping_config'), array('name', 'value'))->where($where);
        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }

    /**
     * Update recurring payment status
     *
     * @param int $cartId recurring cart (parent cart id)
     * @param string $status
     */
    public function updateRecurringStatus($cartId, $status)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('cart_id = ?', $cartId);
        $this->getDbTable()->update(array('recurring_status' => $status), $where);
    }

    /**
     * Update next recurring payment date
     *
     * @param int $cartId recurring cart (parent cart id)
     * @param string $date
     */
    public function updateRecurringDate($cartId, $date)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('cart_id = ?', $cartId);
        $this->getDbTable()->update(array('next_payment_date' => $date), $where);
    }

    /**
     * Return order info if it's created from parent recurring order
     *
     * @param int $cartId
     *
     * @return array
     */
    public function checkRecurringOrder($cartId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('cart_id = ?', $cartId);
        $select = $this->getDbTable()->getAdapter()->select()->from('shopping_cart_session_has_recurring')
            ->where($where);

        return $this->getDbTable()->getAdapter()->fetchAll($select);
    }

}
