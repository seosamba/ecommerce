<?php
/**
 * GroupPriceTools.php
 *
 */
class Tools_GroupPriceTools {


	public static function calculateGroupPrice(Models_Model_Product $product, $productId){
        $cache = Zend_Controller_Action_HelperBroker::getStaticHelper('Cache');
        $cacheTags  = array();
        if (null === ($allCustomersGroups = $cache->load('customers_groups', 'store_'))){
            $dbTable = new Models_DbTable_CustomerInfo();
            $select = $dbTable->select()->from('shopping_customer_info', array('user_id', 'group_id'));
            $allCustomersGroups =  $dbTable->getAdapter()->fetchAssoc($select);
            $cache->save('customers_groups',  $allCustomersGroups, 'store_', array());
        }
        array_push($cacheTags, 'product_price');
        if (null === ($allProductsWithGroups = $cache->load('products_groups_price', 'store_'))){
            $allProductsWithGroups = Store_Mapper_GroupPriceMapper::getInstance()->fetchAssocAll();
            $cache->save('products_groups_price',  $allProductsWithGroups, 'store_', is_array($cacheTags) ? $cacheTags : array());
        }
        if (null === ($allProductsGroups = $cache->load('products_groups', 'store_'))){
            $allProductsGroups = Store_Mapper_GroupMapper::getInstance()->fetchAssocAll();
            $cache->save('products_groups',  $allProductsGroups, 'store_', is_array($cacheTags) ? $cacheTags : array());
        }
        $sessionHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('session');
        $currentUser = $sessionHelper->getCurrentUser()->getId();
        if(isset($currentUser)){
            if(array_key_exists($currentUser, $allCustomersGroups)){
                $groupId = $allCustomersGroups[$currentUser]['group_id'];
                if(isset($allProductsGroups[$groupId])){
                    if($productId != null){
                        $groupProductKey = $groupId.'_'.$productId;
                        $priceNow = $product->getPrice();
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
                        return $resultPrice;
                    }
                    return $product->getPrice();

                }
            }
        }
        return $product->getPrice();

	}

	public static function reduceFilterPrice ($price, $tax, $tags = array())
    {
        if (!empty($price)) {//$urlFilter['price']
            if (!empty($tags)) {//$filters['tags']
                $sessionHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('session');
                $currentUser = $sessionHelper->getCurrentUser()->getId();

                $dbTable = new Models_DbTable_CustomerInfo();
                $select = $dbTable->select()->from('shopping_customer_info', array('user_id', 'group_id'));
                $allCustomersGroups =  $dbTable->getAdapter()->fetchAssoc($select);

                if(!empty($allCustomersGroups)) {
                    if(array_key_exists($currentUser, $allCustomersGroups)){
                        $groupId = $allCustomersGroups[$currentUser]['group_id'];
                        $allProductsGroups = Store_Mapper_GroupMapper::getInstance()->fetchAssocAll();
                        if(isset($allProductsGroups[$groupId])){

                            $additionalPrice = array();
                            foreach ($price as $key => $range) {
                                $priceNow = $range;
                                $priceValue = $allProductsGroups[$groupId]['priceValue'];
                                $priceSign  = $allProductsGroups[$groupId]['priceSign'];
                                $priceType  = $allProductsGroups[$groupId]['priceType'];
                                $nonTaxable = $allProductsGroups[$groupId]['nonTaxable'];

                                if($priceType == 'percent'){
                                    if($priceSign == 'minus') {
                                        $remainder = 1 - ($priceValue / 100);
                                    }
                                    if($priceSign == 'plus') {
                                        $remainder = 1 + ($priceValue / 100);
                                    }

                                    if(!empty($tax) && empty($nonTaxable)) {
                                        if($tax < 10) {
                                            $tax = '0'. $tax;
                                        }

                                        $priceNow = $priceNow / "1.$tax";
                                    }

                                    $resultPrice = $priceNow / $remainder;
                                }
                                if($priceType == 'unit'){
                                    if(!empty($tax) && empty($nonTaxable)) {
                                        if($tax < 10) {
                                            $tax = '0'. $tax;
                                        }

                                        $priceNow = $priceNow / "1.$tax";
                                    }

                                    if($priceSign == 'minus') {
                                        $resultPrice = $priceNow + $priceValue;
                                    }
                                    if($priceSign == 'plus') {
                                        $resultPrice = $priceNow - $priceValue;
                                    }
                                }

                                $price[$key] = $resultPrice;

                                if($key == 'from') {
                                    $additionalPrice['min'] = $range;
                                }
                                if($key == 'to') {
                                    $additionalPrice['max'] = $range;
                                }
                            }
                        }
                    } else {
                        if(!empty($tax)) {
                            if($tax < 10) {
                                $tax = '0'. $tax;
                            }

                            $additionalPrice['min'] = $price['from'];
                            $additionalPrice['max'] = $price['to'];

                            $price['from'] = $price['from'] / "1.$tax";
                            $price['to'] = $price['to'] / "1.$tax";
                        }
                    }
                }
            }

            $priceFilter = array(
                'min'   => $price['from'],
                'max'   => $price['to'],
                'additionalPrice' => $additionalPrice
            );

            return $priceFilter;
        }
    }


}
