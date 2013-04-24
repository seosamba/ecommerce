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
	 * @param $object Models_Model_Product
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
        $sessionHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('session');
        $currentUser = $sessionHelper->getCurrentUser()->getId();
        if(isset($currentUser)){
            if(array_key_exists($currentUser, $allCustomersGroups)){
                $groupId = $allCustomersGroups[$currentUser]['group_id'];
                $productId = $object->getId();
                $groupProductKey = $groupId.'_'.$productId;
                if(array_key_exists($groupProductKey, $allProductsWithGroups)){
                    $priceNow = $object->getPrice();
                    $priceValue = $allProductsWithGroups[$groupProductKey]['priceValue'];
                    $priceSign  = $allProductsWithGroups[$groupProductKey]['priceSign'];
                    $priceType  = $allProductsWithGroups[$groupProductKey]['priceType'];
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
                    $object->setCurrentPrice($resultPrice);
                }
            }
        }
	}

}
