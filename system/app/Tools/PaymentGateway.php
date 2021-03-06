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
     * @return Tools_PaymentGateway
     * @throws Exceptions_SeotoasterPluginException
     */
	public function updateCartStatus($cartId, $status, $skipSupplierNotification = false) {
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

			$cart->setStatus($status);
			$cart->setGateway($gateway);

			if ($status === Models_Model_CartSession::CART_STATUS_COMPLETED) {
                $cart->setPurchasedOn(date(Tools_System_Tools::DATE_MYSQL));
                if (Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('throttleTransactions') === 'true') {
                    Tools_Misc::addThrottleTransaction();
                }
            }

            if ($status === Models_Model_CartSession::CART_STATUS_PARTIAL) {
                $cart->setPurchasedOn(date(Tools_System_Tools::DATE_MYSQL));
                $cart->setPartialPurchasedOn(date(Tools_System_Tools::DATE_MYSQL));
            }


			Models_Mapper_CartSessionMapper::getInstance()->save($cart);
		}

		return $this;
	}

}
