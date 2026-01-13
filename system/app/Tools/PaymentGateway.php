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

            if($status == Models_Model_CartSession::CART_STATUS_COMPLETED
                || $status == Models_Model_CartSession::CART_STATUS_SHIPPED
                || $status == Models_Model_CartSession::CART_STATUS_DELIVERED
                || $status == Models_Model_CartSession::CART_STATUS_PARTIAL
            ) {
                self::processProductLocationInventory($cart);
                self::productLocationInventoryNotification($cartId);
            }
		}

		return $this;
	}

    public static function processProductLocationInventory($cartSessionModel)
    {
        $productLocationsMapper = Models_Mapper_ProductLocationsMapper::getInstance();
        $cartLocationInventoryMapper = Models_Mapper_CartLocationInventoryMapper::getInstance();

        $cartId = $cartSessionModel->getId();
        $cartContent = $cartSessionModel->getCartContent();

        foreach ($cartContent as $cartItem) {
            $priorityLocationsToCalculate = array();
            $productId = $cartItem['product_id'];
            $inventoriesAddedToCart = $cartItem['qty'];

            $productLocationsAll = $productLocationsMapper->findLocationsByProductId($productId);

            if(!empty($productLocationsAll)) {
                $defaultLocation = '';
                $locationsInventoryLimit = array();

                foreach ($productLocationsAll as $key => $loc) {
                    $locationsInventoryLimit[$loc['location_id']] = $loc['inventory'];
                    if(!empty($loc['is_default_location'])) {
                        $defaultLocation = $loc['location_id'];
                    }
                }

                arsort($locationsInventoryLimit);

                if(empty($defaultLocation)) {
                    $defaultLocation = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('defaultLocationId');
                }

                //default location
                if(!in_array($defaultLocation, $priorityLocationsToCalculate)) {
                    $priorityLocationsToCalculate[] = $defaultLocation;
                }

                foreach ($locationsInventoryLimit as $id => $inventory) {
                    if(!in_array($id, $priorityLocationsToCalculate)) {
                        $priorityLocationsToCalculate[] = $id;
                    }
                }

                $processingComplete = false;
                foreach ($priorityLocationsToCalculate as $locationId) {
                    $remainderOfValue = $locationsInventoryLimit[$locationId] - $inventoriesAddedToCart;

                    $cartLocationInventory = new Models_Model_CartLocationInventoryModel();
                    $cartLocationInventory->setCartId($cartId);
                    $cartLocationInventory->setProductId($productId);
                    $cartLocationInventory->setLocationId($locationId);
                    $cartLocationInventory->setProductStatus('new');

                    if($remainderOfValue >= 0) {
                        //save $inventoriesAddedToCart
                        $cartLocationInventory->setLocationInventory($inventoriesAddedToCart);

                        if(!empty($inventoriesAddedToCart)) {
                            $cartLocationInventoryMapper->save($cartLocationInventory);

                            $existedProductLocation = $productLocationsMapper->findLocationByProductIdAndLocationId($productId, $locationId);
                            $existedProductLocation['inventory'] -= $inventoriesAddedToCart;

                            $productLocationsModel = new Models_Model_ProductLocationsModel();
                            $productLocationsModel->setOptions($existedProductLocation);
                            $productLocationsMapper->save($productLocationsModel);
                        }

                        $processingComplete = true;

                        break;
                    } elseif ($remainderOfValue < 0) {
                        //save $locationsInventoryLimit[$locationId]
                        $cartLocationInventory->setLocationInventory($locationsInventoryLimit[$locationId]);

                        if(!empty($locationsInventoryLimit[$locationId])) {
                            $cartLocationInventoryMapper->save($cartLocationInventory);

                            $existedProductLocation = $productLocationsMapper->findLocationByProductIdAndLocationId($productId, $locationId);
                            $existedProductLocation['inventory'] -= $locationsInventoryLimit[$locationId];

                            $productLocationsModel = new Models_Model_ProductLocationsModel();
                            $productLocationsModel->setOptions($existedProductLocation);
                            $productLocationsMapper->save($productLocationsModel);
                        }

                        $inventoriesAddedToCart = abs($remainderOfValue);
                    }
                }

                if(!empty($inventoriesAddedToCart) && !$processingComplete) {
                    $cartLocationInventory = new Models_Model_CartLocationInventoryModel();
                    $cartLocationInventory->setCartId($cartId);
                    $cartLocationInventory->setProductId($productId);
                    $cartLocationInventory->setLocationId('');
                    $cartLocationInventory->setLocationInventory($inventoriesAddedToCart);
                    $cartLocationInventory->setProductStatus('new');

                    $cartLocationInventoryMapper->save($cartLocationInventory);
                }
            }
        }

    }

    public static function productLocationInventoryNotification($cartId) {
        $cartLocationInventoryMapper = Models_Mapper_CartLocationInventoryMapper::getInstance();
        $cartLocationInventoryData = $cartLocationInventoryMapper->getLocationInventoryInfo($cartId);

        $preparedLocationinventoryData = array();
        if(!empty($cartLocationInventoryData)) {
            foreach ($cartLocationInventoryData as $locationInventoryData) {
                if(!empty($locationInventoryData['location_id']) && !empty($locationInventoryData['send_email_notification'])) {
                    $preparedLocationinventoryData[$locationInventoryData['location_id']]['id'] = $locationInventoryData['id'];
                    $preparedLocationinventoryData[$locationInventoryData['location_id']]['orderId'] = $cartId;
                    $preparedLocationinventoryData[$locationInventoryData['location_id']]['locationEmail'] = $locationInventoryData['email'];
                    $preparedLocationinventoryData[$locationInventoryData['location_id']]['locationName'] = $locationInventoryData['location_name'];
                    $preparedLocationinventoryData[$locationInventoryData['location_id']]['products'][] = $locationInventoryData['product_name'] . ' - (' . $locationInventoryData['location_inventory'] . ')';
                }
            }
        }

        if(!empty($preparedLocationinventoryData)) {
            foreach ($preparedLocationinventoryData as $locationInventoryData) {
                $cartLocationInventory = $cartLocationInventoryMapper->find($locationInventoryData['id']);

                if($cartLocationInventory instanceof Models_Model_CartLocationInventoryModel) {
                    $cartLocationInventory->registerObserver(new Tools_Mail_Watchdog(array(
                        'trigger' => Tools_StoreMailWatchdog::TRIGGER_LOCATION_INVENTORY_NOTIFICATION,
                        'notificationData' => $locationInventoryData
                    )));

                    $cartLocationInventory->notifyObservers();
                }
            }
        }
    }

}
