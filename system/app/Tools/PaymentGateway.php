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
     * @return Tools_PaymentGateway
     * @throws Exceptions_SeotoasterPluginException
     */
	public function updateCartStatus($cartId, $status) {
		$gateway = get_called_class();

		$cart = Models_Mapper_CartSessionMapper::getInstance()->find($cartId);
		if ($cart instanceof Models_Model_CartSession) {
            $cart->registerObserver(
                new Tools_InventoryObserver($cart->getStatus())
            );

            $cart->registerObserver(
                new Tools_SupplierObserver($cart->getStatus())
            );

			$cart->setStatus($status);
			$cart->setGateway($gateway);

			if ($status === Models_Model_CartSession::CART_STATUS_COMPLETED) {
                $cart->setPurchasedOn(date(Tools_System_Tools::DATE_MYSQL));
            }

			Models_Mapper_CartSessionMapper::getInstance()->save($cart);
		}

		return $this;
	}

}
