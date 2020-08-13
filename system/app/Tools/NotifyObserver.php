<?php
class Tools_NotifyObserver implements Interfaces_Observer
{
    /**
     * Send notification email for customer, when product qty was changed
     * @param $object
     */
    public function notify($object)
    {
        $notifiedProductsMapper = Store_Mapper_NotifiedProductsMapper::getInstance();

        $prodId = $object->getId();
        if(!empty($prodId)) {
            $notifiedCustomerData = $notifiedProductsMapper->findCustomersByProductId($prodId);

            if(!empty($notifiedCustomerData)) {
                foreach ($notifiedCustomerData as $key => $product) {
                    $productInventory = $object->getInventory();

                    if($productInventory > 0) {
                        $currentNotifiedProduct = $notifiedProductsMapper->find($product['id']);

                        if($currentNotifiedProduct instanceof Store_Model_NotifiedProductsModel && $currentNotifiedProduct->getSendNotification() == '0') {
                            $currentNotifiedProduct->setSendNotification('1');

                            $notifiedProductsMapper->save($currentNotifiedProduct);

                            $currentNotifiedProduct->registerObserver(new Tools_Mail_Watchdog(array(
                                'trigger' => Tools_StoreMailWatchdog::TRIGGER_CUSTOMER_NOTIFICATION,
                                'customerProductData' => $product
                            )));

                            $currentNotifiedProduct->notifyObservers();
                        }
                    }

                }
            }
        }
    }

}
