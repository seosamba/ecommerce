<?php
/**
 * Payment Gateway plugin
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_PaymentGateway extends Tools_Plugins_Abstract {


    /**
     * Updates a status for cart by given cart id
     *
     * @param int $cartId cart id
     * @param string $status new status (completed, shipped, etc..)
     * @param bool $skipSupplierNotification skip suppliers notification flag
     * @param string $message some text message
     * @return Tools_PaymentGateway
     * @throws Exceptions_SeotoasterPluginException
     */
	public function updateCartStatus($cartId, $status, $skipSupplierNotification = false, $message = '') {
		$gateway = get_called_class();

		$cart = Models_Mapper_CartSessionMapper::getInstance()->find($cartId);
		if ($cart instanceof Models_Model_CartSession) {
            $cart->registerObserver(
                new Tools_InventoryObserver($cart->getStatus())
            );

            if ($skipSupplierNotification === false) {
                $cart->registerObserver(
                    new Tools_SupplierObserver($cart->getStatus())
                );
            }

            if ($status === Models_Model_CartSession::CART_STATUS_COMPLETED) {
                $currentStatus = $cart->getStatus();
                if (!empty($cart->getIsPartial())) {
                    $cart->setSecondPaymentGateway($gateway);
                    $cart->setSecondPartialPaidAmount(round($cart->getTotal() - $cart->getFirstPartialPaidAmount(), 2));
                    if ($gateway === Models_Model_CartSession::MANUALLY_PAYED_GATEWAY_QUOTE || $gateway === Models_Model_CartSession::MANUALLY_PAYED_GATEWAY_MANUALL) {
                        $cart->setIsSecondPaymentManuallyPaid('1');
                        $isFirstPaymentManuallyPaid = $cart->getIsFirstPaymentManuallyPaid();
                        $isSecondPaymentManuallyPaid = $cart->getIsSecondPaymentManuallyPaid();
                        if (!empty($isFirstPaymentManuallyPaid) && !empty($isSecondPaymentManuallyPaid)) {
                            $cart->setIsFullOrderManuallyPaid('1');
                        }
                    } else {
                        $cart->setIsSecondPaymentManuallyPaid('0');
                    }
                }

            }

			$cart->setStatus($status);
			$cart->setGateway($gateway);

			if ($status === Models_Model_CartSession::CART_STATUS_COMPLETED) {
                $cart->setPurchasedOn(date(Tools_System_Tools::DATE_MYSQL));
                if (Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('throttleTransactions') === 'true') {
                    Tools_Misc::addThrottleTransaction();
                }
            }

            if ($status === Models_Model_CartSession::CART_STATUS_NOT_VERIFIED) {
                $cart->setPurchasedOn(null);
            }

            if ($status === Models_Model_CartSession::CART_STATUS_PARTIAL) {
                $cart->setPurchasedOn(date(Tools_System_Tools::DATE_MYSQL));
                $cart->setPartialPurchasedOn(date(Tools_System_Tools::DATE_MYSQL));
                $cart->setFirstPaymentGateway($gateway);
                if ($gateway === Models_Model_CartSession::MANUALLY_PAYED_GATEWAY_QUOTE || $gateway === Models_Model_CartSession::MANUALLY_PAYED_GATEWAY_MANUALL) {
                    $cart->setIsFirstPaymentManuallyPaid('1');
                } else {
                    $cart->setIsFirstPaymentManuallyPaid('0');
                }

                $partialPercentage = $cart->getPartialPercentage();
                $partialPaymentType = $cart->getPartialType();

                if ($partialPaymentType === Models_Model_CartSession::CART_PARTIAL_PAYMENT_TYPE_AMOUNT) {
                    $amountToPayPartial = round($partialPercentage, 2);
                } else {
                    $amountToPayPartial = round(($cart->getTotal() * $cart->getPartialPercentage()) / 100, 2);
                }

                $cart->setFirstPartialPaidAmount($amountToPayPartial);
            }

            if ($status === Models_Model_CartSession::CART_STATUS_ERROR) {
                $cart->setPurchaseErrorMessage($message);
            }


			Models_Mapper_CartSessionMapper::getInstance()->save($cart);
		}

		return $this;
	}

}
