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
            'custom_type' => $model->getCustomType()
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

}
