<?php
class Tools_ProductLocationWatchdog implements Interfaces_Observer
{

    /**
     * Add current product inventory to location inventory if store have only one location with cash register
     * @param $object
     */
    public function notify($object)
    {
        $prodId = $object->getId();
        $prodInventory = $object->getInventory();

        if(!empty($prodId)) {
            $enabledSeosambaPosPlugin = Application_Model_Mappers_PluginMapper::getInstance()->findByName('seosambapos');
            if ($enabledSeosambaPosPlugin->getStatus() == Application_Model_Models_Plugin::ENABLED) {
                $defaultLocationId = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('defaultLocationId');
                $productLocationsMapper = Models_Mapper_ProductLocationsMapper::getInstance();

                if(!empty($defaultLocationId) && !is_null($prodInventory)) {
                    $pickupLocations = Tools_Misc::getLocationsData();

                    if(!empty($pickupLocations) && count($pickupLocations) === 1)  {
                        $defaultLocationExists = false;
                        foreach($pickupLocations as $location) {
                            if($location['id'] == $defaultLocationId) {
                                $defaultLocationExists = true;
                            }
                        }

                        if($defaultLocationExists) {
                            $productLocationsAll = $productLocationsMapper->findLocationsByProductId($prodId);
                            $existedProductLocation = $productLocationsMapper->findLocationByProductIdAndLocationId($prodId, $defaultLocationId);

                            if($prodInventory < 0) {
                                $prodInventory = 0;
                            }

                            if(empty($existedProductLocation) && empty($productLocationsAll)) {
                                $productLocationsModel = new Models_Model_ProductLocationsModel();
                                $productLocationsModel->setProductId($prodId);
                                $productLocationsModel->setLocationId($defaultLocationId);
                                $productLocationsModel->setInventory($prodInventory);
                                $productLocationsModel->setIsDefaultLocation(1);
                                $productLocationsModel->setIsQuickProduct(0);

                                $productLocationsMapper->save($productLocationsModel);
                            } else {
                                if(!empty($productLocationsAll) && count($productLocationsAll) === 1 && !empty($existedProductLocation)) {
                                    $productLocationsModel = new Models_Model_ProductLocationsModel();
                                    $existedProductLocation['inventory'] = $prodInventory;
                                    $productLocationsModel->setOptions($existedProductLocation);

                                    $productLocationsMapper->save($productLocationsModel);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

}
