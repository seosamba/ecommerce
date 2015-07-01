<?php

/**
 * RecurringPayments
 */
class Store_Model_RecurringPayments extends Application_Model_Models_Abstract
{

    const NEW_RECURRING_PAYMENT = 'new';

    const ACTIVE_RECURRING_PAYMENT = 'active';

    const PENDING_RECURRING_PAYMENT = 'pending';

    const EXPIRED_RECURRING_PAYMENT = 'expired';

    const SUSPENDED_RECURRING_PAYMENT = 'suspended';

    const CANCELED_RECURRING_PAYMENT = 'canceled';

    protected $_cartId;

    protected $_subscriptionId;

    protected $_ipnTrackingId;

    protected $_gatewayType;

    protected $_paymentPeriod;

    protected $_recurringTimes;

    protected $_subscriptionDate;

    protected $_paymentCycleAmount;

    protected $_totalAmountPaid;

    protected $_lastPaymentDate;

    protected $_recurringStatus;

    protected $_customType;

    protected $_acceptChangingNextBillingDate;

    protected $_acceptChangingShippingAddress;

    protected $_nextPaymentDate;

    protected $_freeTransactionCycle;

    protected $_transactionsQuantity;

    /**
     * @return mixed
     */
    public function getCartId()
    {
        return $this->_cartId;
    }

    /**
     * @param mixed $cartId
     * @return mixed
     */
    public function setCartId($cartId)
    {
        $this->_cartId = $cartId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubscriptionId()
    {
        return $this->_subscriptionId;
    }

    /**
     * @param mixed $subscriptionId
     * @return mixed
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->_subscriptionId = $subscriptionId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIpnTrackingId()
    {
        return $this->_ipnTrackingId;
    }

    /**
     * @param mixed $ipnTrackingId
     * @return mixed
     */
    public function setIpnTrackingId($ipnTrackingId)
    {
        $this->_ipnTrackingId = $ipnTrackingId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGatewayType()
    {
        return $this->_gatewayType;
    }

    /**
     * @param mixed $gatewayType
     * @return mixed
     */
    public function setGatewayType($gatewayType)
    {
        $this->_gatewayType = $gatewayType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaymentPeriod()
    {
        return $this->_paymentPeriod;
    }

    /**
     * @param mixed $paymentPeriod
     * @return mixed
     */
    public function setPaymentPeriod($paymentPeriod)
    {
        $this->_paymentPeriod = $paymentPeriod;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecurringTimes()
    {
        return $this->_recurringTimes;
    }

    /**
     * @param mixed $recurringTimes
     * @return mixed
     */
    public function setRecurringTimes($recurringTimes)
    {
        $this->_recurringTimes = $recurringTimes;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubscriptionDate()
    {
        return $this->_subscriptionDate;
    }

    /**
     * @param mixed $subscriptionDate
     * @return mixed
     */
    public function setSubscriptionDate($subscriptionDate)
    {
        $this->_subscriptionDate = $subscriptionDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaymentCycleAmount()
    {
        return $this->_paymentCycleAmount;
    }

    /**
     * @param mixed $paymentCycleAmount
     * @return mixed
     */
    public function setPaymentCycleAmount($paymentCycleAmount)
    {
        $this->_paymentCycleAmount = $paymentCycleAmount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalAmountPaid()
    {
        return $this->_totalAmountPaid;
    }

    /**
     * @param mixed $totalAmountPaid
     * @return mixed
     */
    public function setTotalAmountPaid($totalAmountPaid)
    {
        $this->_totalAmountPaid = $totalAmountPaid;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastPaymentDate()
    {
        return $this->_lastPaymentDate;
    }

    /**
     * @param mixed $lastPaymentDate
     * @return mixed
     */
    public function setLastPaymentDate($lastPaymentDate)
    {
        $this->_lastPaymentDate = $lastPaymentDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecurringStatus()
    {
        return $this->_recurringStatus;
    }

    /**
     * @param mixed $recurringStatus
     * @return mixed
     */
    public function setRecurringStatus($recurringStatus)
    {
        $this->_recurringStatus = $recurringStatus;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomType()
    {
        return $this->_customType;
    }

    /**
     * @param mixed $customType
     * @return mixed
     */
    public function setCustomType($customType)
    {
        $this->_customType = $customType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAcceptChangingNextBillingDate()
    {
        return $this->_acceptChangingNextBillingDate;
    }

    /**
     * @param mixed $acceptChangingNextBillingDate
     * @return mixed
     */
    public function setAcceptChangingNextBillingDate($acceptChangingNextBillingDate)
    {
        $this->_acceptChangingNextBillingDate = $acceptChangingNextBillingDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAcceptChangingShippingAddress()
    {
        return $this->_acceptChangingShippingAddress;
    }

    /**
     * @param mixed $acceptChangingShippingAddress
     * @return mixed
     */
    public function setAcceptChangingShippingAddress($acceptChangingShippingAddress)
    {
        $this->_acceptChangingShippingAddress = $acceptChangingShippingAddress;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNextPaymentDate()
    {
        return $this->_nextPaymentDate;
    }

    /**
     * @param mixed $nextPaymentDate
     * @return mixed
     */
    public function setNextPaymentDate($nextPaymentDate)
    {
        $this->_nextPaymentDate = $nextPaymentDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFreeTransactionCycle()
    {
        return $this->_freeTransactionCycle;
    }

    /**
     * @param mixed $freeTransactionCycle
     * @return mixed
     */
    public function setFreeTransactionCycle($freeTransactionCycle)
    {
        $this->_freeTransactionCycle = $freeTransactionCycle;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTransactionsQuantity()
    {
        return $this->_transactionsQuantity;
    }

    /**
     * @param mixed $transactionsQuantity
     * @return mixed
     */
    public function setTransactionsQuantity($transactionsQuantity)
    {
        $this->_transactionsQuantity = $transactionsQuantity;

        return $this;
    }


}