<?php
/**
 * Payment Gateway plugin
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_PaymentGateway extends Tools_Plugins_Abstract {


	/**
	 * Updates a status for cart by given cart id
	 * @param $cartId   Id of a cart to update
	 * @param $status   New status
	 * @return Tools_PaymentGateway
	 */
	public function updateCartStatus($cartId, $status) {
		$gateway = get_called_class();

		$cart = Models_Mapper_CartSessionMapper::getInstance()->find($cartId);
		$cart->registerObserver(
			new Tools_InventoryObserver($cart->getStatus())
		);
		if ($cart !== null) {
			$cart->setStatus($status);
			$cart->setGateway($gateway);

			Models_Mapper_CartSessionMapper::getInstance()->save($cart);
		}

		return $this;
	}

    /**
     * Update recurring payment data
     *
     * @param int $cartId cart id
     * @param string $status new status
     * @param mixed $recurringAmount amount to add to final total
     * @return Tools_PaymentGateway
     */
    public function updateRecurringPaymentInfo($cartId, $status, $recurringAmount = false)
    {
        $gateway = get_called_class();

        $recurringPaymentMapper = Store_Mapper_RecurringPaymentsMapper::getInstance();
        $paymentInfo = $recurringPaymentMapper->find($cartId);
        if ($paymentInfo instanceof Store_Model_RecurringPayments) {
            $paymentInfo->setRecurringStatus($status);
            $paymentInfo->setGatewayType($gateway);
            if ($recurringAmount) {
                $paymentInfo->setTotalAmountPaid($paymentInfo->getTotalAmountPaid() + $recurringAmount);
            }
            $recurringPaymentMapper->save($paymentInfo);
        }

        return $this;

    }

    /**
     * Create new recurring payment
     *
     * @param int $cartId cart id
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
     * @return Tools_PaymentGateway
     */
    public function createRecurringPaymentInfo(
        $cartId,
        $recurringTimes,
        $subscriptionId,
        $ipnTrackingId,
        $paymentPeriod,
        $subscriptionDate,
        $paymentCycleAmount,
        $totalAmountPaid = 0,
        $lastPaymentDate = '0000-00-00 00:00:00',
        $customType = '',
        $recurringStatus = Store_Model_RecurringPayments::NEW_RECURRING_PAYMENT
    ) {
        $gateway = get_called_class();

        $recurringPaymentMapper = Store_Mapper_RecurringPaymentsMapper::getInstance();
        $paymentInfo = $recurringPaymentMapper->find($cartId);
        if (empty($paymentInfo)) {
            $paymentInfo = new Store_Model_RecurringPayments();
            $paymentInfo->setCartId($cartId);
            $paymentInfo->setSubscriptionId($subscriptionId);
            $paymentInfo->setIpnTrackingId($ipnTrackingId);
            $paymentInfo->setRecurringStatus($recurringStatus);
            $paymentInfo->setGatewayType($gateway);
            $paymentInfo->setPaymentPeriod($paymentPeriod);
            $paymentInfo->setRecurringTimes($recurringTimes);
            $paymentInfo->setTotalAmountPaid($totalAmountPaid);
            $paymentInfo->setSubscriptionDate($subscriptionDate);
            $paymentInfo->setPaymentCycleAmount($paymentCycleAmount);
            $paymentInfo->setLastPaymentDate($lastPaymentDate);
            $paymentInfo->setCustomType($customType);
            $recurringPaymentMapper->save($paymentInfo);
        }

        return $this;

    }

}
