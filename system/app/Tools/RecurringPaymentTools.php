<?php

/**
 * Recurring payment tools
 *
 * Class Tools_RecurringPaymentTools
 */
class Tools_RecurringPaymentTools
{

    /**
     * Update recurring payment data
     *
     * @param int $cartId cart id
     * @param string $status new status
     * @param string $gatewayName payment gateway name
     * @param mixed $recurringAmount amount to add to final total
     *
     */
    public static function updateRecurringPaymentInfo($cartId, $status, $gatewayName, $recurringAmount = false)
    {
        $recurringPaymentMapper = Store_Mapper_RecurringPaymentsMapper::getInstance();
        $paymentInfo = $recurringPaymentMapper->find($cartId);
        if ($paymentInfo instanceof Store_Model_RecurringPayments) {
                $paymentInfo->setRecurringStatus($status);
            $nextPaymentDate = date('Y-m-d', strtotime($paymentInfo->getNextPaymentDate() . $paymentInfo->getPaymentPeriod()));
            $paymentInfo->setLastPaymentDate(date('Y-m-d'));
            $paymentInfo->setNextPaymentDate($nextPaymentDate);
            $paymentInfo->setGatewayType($gatewayName);
            if ($recurringAmount) {
                $paymentInfo->setTotalAmountPaid($paymentInfo->getTotalAmountPaid() + $recurringAmount);
            }
            $recurringPaymentMapper->save($paymentInfo);
        }
    }

    /**
     * Create new recurring payment based on regular order if payment not currently in use
     *
     * @param int $dependentCartId cart id or real order
     * @param int $recurringTimes recurring times quantity
     * @param string $subscriptionId subscription id
     * @param string $ipnTrackingId ipn tracking id
     * @param string $paymentPeriod Frequency of recurring payment in format (+1 day, +1 month, +1 year etc...)
     * @param string $subscriptionDate subscription date
     * @param float $paymentCycleAmount amount for each recurring payment
     * @param mixed $totalAmountPaid amount that was paid
     * @param string $lastPaymentDate last payment date
     * @param string $recurringStatus recurring payment status
     * @param string $customType Additional information for payment
     * @param string $gatewayName payment gateway name
     *
     */
    public static function createRecurringPaymentInfo(
        $dependentCartId,
        $recurringTimes,
        $subscriptionId,
        $ipnTrackingId,
        $paymentPeriod,
        $paymentCycleAmount,
        $gatewayName,
        $totalAmountPaid = 0,
        $customType = '',
        $recurringStatus = Store_Model_RecurringPayments::NEW_RECURRING_PAYMENT
    ) {

        $recurringPaymentMapper = Store_Mapper_RecurringPaymentsMapper::getInstance();
        $cartSessionMapper = Models_Mapper_CartSessionMapper::getInstance();
        $dependentCart = $cartSessionMapper->find($dependentCartId);
        $recurrentPeriod = str_replace('recurring-payment-', '+1 ', $paymentPeriod);
        $currentDate = date('Y-m-d');
        $nextPaymentDate = date('Y-m-d', strtotime($recurrentPeriod));
        if ($dependentCart instanceof Models_Model_CartSession) {
            $dependentCart->setId(null);
            $recurringCart = $cartSessionMapper->save($dependentCart);
            $recurringCartId = $recurringCart->getId();
            $recurringPaymentMapper->saveRelatedRecurring($recurringCartId, $dependentCartId);
            $paymentInfo = new Store_Model_RecurringPayments();
            $paymentInfo->setCartId($recurringCartId);
            $paymentInfo->setSubscriptionId($subscriptionId);
            $paymentInfo->setIpnTrackingId($ipnTrackingId);
            $paymentInfo->setRecurringStatus($recurringStatus);
            $paymentInfo->setGatewayType($gatewayName);
            $paymentInfo->setPaymentPeriod(strtoupper($recurrentPeriod));
            $paymentInfo->setRecurringTimes($recurringTimes);
            $paymentInfo->setTotalAmountPaid($totalAmountPaid);
            $paymentInfo->setSubscriptionDate($currentDate);
            $paymentInfo->setPaymentCycleAmount($paymentCycleAmount);
            $paymentInfo->setLastPaymentDate($currentDate);
            $paymentInfo->setNextPaymentDate($nextPaymentDate);
            $paymentInfo->setCustomType($customType);
            $recurringPaymentMapper->save($paymentInfo);
        }
    }

    /**
     *
     * Update recurring payments (update, suspend, close)
     * for all types of gateways by cart id
     *
     * @param int $cartId cart id
     * @param string $changeSubscription Change subscription type (update, suspend, close)
     * @param array $params subscription(recurring payment detailed params)
     * @return array
     */
    public static function updateSubscription($cartId, $changeSubscription, $params = array())
    {
        $cart = Models_Mapper_CartSessionMapper::getInstance()->find($cartId);
        $paymentInfo = Store_Mapper_RecurringPaymentsMapper::getInstance()->find($cartId);
        if ($cart instanceof Models_Model_CartSession && !empty($paymentInfo)) {
            $recurringPluginClassName = 'Tools_RecurringPayment' . ucfirst(strtolower($cart->getGateway()));
            if (class_exists($recurringPluginClassName)) {
                $recurringPayment = new $recurringPluginClassName();
                if ($recurringPayment instanceof Interfaces_RecurringPayment) {
                    switch ($changeSubscription) {
                        case Store_Model_RecurringPayments::SUSPENDED_RECURRING_PAYMENT:
                            $subscriptionResponse = $recurringPayment->suspendRecurringPayment();
                            break;
                        case Store_Model_RecurringPayments::CANCELED_RECURRING_PAYMENT:
                            $subscriptionResponse = $recurringPayment->cancelRecurringPayment();
                            break;
                        default:
                            $subscriptionResponse = $recurringPayment->updateRecurringPayment($params);
                    }

                    return $subscriptionResponse;
                } else {
                    return array(
                        'error' => true,
                        'message' => 'Recurring payment should be instance of Interfaces_RecurringPayment Interface'
                    );
                }
            } else {
                return array('error' => true, 'message' => 'Recurring method does\'t exists');
            }
        }
        return array('error' => true, 'message' => 'Wrong params');
    }
}
