<?php
/**
 * GroupPriceObserver
 *
 */
class Tools_GroupPriceObserver implements Interfaces_Observer {

	private static $_configParams = null;

    protected $_cacheTags      = array();

	public function __construct() {
		if (self::$_configParams === null) {
			self::$_configParams = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
		}
		$this->_cache = Zend_Controller_Action_HelperBroker::getStaticHelper('Cache');
	}


    /**
     * @param Models_Model_Product $object
     * @return Models_Model_Product
     */
	public function notify($object) {
        if (null === ($allCustomersGroups = $this->_cache->load('customers_groups', 'store_'))){
            $dbTable = new Models_DbTable_CustomerInfo();
            $select = $dbTable->select()->from('shopping_customer_info', array('user_id', 'group_id'));
            $allCustomersGroups =  $dbTable->getAdapter()->fetchAssoc($select);
            $this->_cache->save('customers_groups',  $allCustomersGroups, 'store_', array());
        }
        array_push($this->_cacheTags, 'product_price');
        if (null === ($allProductsWithGroups = $this->_cache->load('products_groups_price', 'store_'))){
            $allProductsWithGroups = Store_Mapper_GroupPriceMapper::getInstance()->fetchAssocAll();
            $this->_cache->save('products_groups_price',  $allProductsWithGroups, 'store_', is_array($this->_cacheTags) ? $this->_cacheTags : array());
        }
        if (null === ($allProductsGroups = $this->_cache->load('products_groups', 'store_'))){
            $allProductsGroups = Store_Mapper_GroupMapper::getInstance()->fetchAssocAll();
            $this->_cache->save('products_groups',  $allProductsGroups, 'store_', is_array($this->_cacheTags) ? $this->_cacheTags : array());
        }
        $sessionHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('session');
        $currentUser = $sessionHelper->getCurrentUser()->getId();
        if(isset($currentUser) && is_array($allCustomersGroups)){
            if(array_key_exists($currentUser, $allCustomersGroups)){
                $groupId = $allCustomersGroups[$currentUser]['group_id'];
                if(isset($allProductsGroups[$groupId])){
                    $productId = $object->getId();
                    if($productId != null){
                        $groupProductKey = $groupId.'_'.$productId;
                        $currentPrice = $object->getCurrentPrice();
                        if (empty($currentPrice)) {
                            $priceNow = $object->getPrice();
                        } else {
                            $priceNow = $currentPrice;
                        }
                        $priceValue = $allProductsGroups[$groupId]['priceValue'];
                        $priceSign  = $allProductsGroups[$groupId]['priceSign'];
                        $priceType  = $allProductsGroups[$groupId]['priceType'];
                        if(array_key_exists($groupProductKey, $allProductsWithGroups)){
                            $priceValue = $allProductsWithGroups[$groupProductKey]['priceValue'];
                            $priceSign  = $allProductsWithGroups[$groupProductKey]['priceSign'];
                            $priceType  = $allProductsWithGroups[$groupProductKey]['priceType'];
                        }
                        if($priceType == 'percent'){
                            $priceModificationValue = $priceNow*$priceValue/100;
                        }
                        if($priceType == 'unit'){
                            $priceModificationValue = $priceValue;
                        }
                        if($priceSign == 'minus'){
                            $resultPrice = $priceNow - $priceModificationValue;
                        }
                        if($priceSign == 'plus'){
                            $resultPrice = $priceNow + $priceModificationValue;
                        }

                        //Adding discount info
                        $object = $this->_addDiscounts($object, $priceValue, $priceType, $priceSign);

                        $object->setOriginalPrice($object->getPrice());
                        $object->setGroupPriceEnabled(1);
                        $object->setCurrentPrice($resultPrice);
                        return $object;
                    }
                }
            }
        }
        $this->_addDiscounts($object);

	}

    /**
     * @param $object Models_Model_Product
     * @param bool $priceValue price
     * @param bool $priceType price type
     * @param bool $priceSign price sign
     * @return Models_Model_Product
     */
    private function _addDiscounts($object, $priceValue = false, $priceType = false, $priceSign = false)
    {
        $productDiscounts = $object->getProductDiscounts();
        if ($priceValue) {
            array_push(
                $productDiscounts,
                array('name' => 'groupprice', 'discount' => $priceValue, 'type' => $priceType, 'sign' => $priceSign)
            );
        } else {
            array_push($productDiscounts, array('name' => 'groupprice', 'discount' => 0, 'type' => '', 'sign' => ''));
        }
        return $object->setProductDiscounts($productDiscounts);
    }

}
