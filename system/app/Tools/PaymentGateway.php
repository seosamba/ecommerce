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

}
