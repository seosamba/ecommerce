<?php
/**
 * ShoppingCart
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_ShoppingCart {

    protected static $_instance = null;

    private $_content           = array();

    private $_session           = null;

    private function __construct() {
        $this->_websiteHelper   = Zend_Controller_Action_HelperBroker::getExistingHelper('website');
        $this->_shoppingConfig  = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
        if ($this->_session === null){
            $this->_session = new Zend_Session_Namespace($this->_websiteHelper->getUrl().'cart');
        }
        
        $this->_load();
    }

    private function __clone() { }

    private function __wakeup() { }

    /**
     * Returns instance of Shopping Cart
     * @return Tools_ShoppingCart
     */
    public static function getInstance() {
        if (is_null(self::$_instance)){
            self::$_instance = new Tools_ShoppingCart();
        }

        return self::$_instance;
    }

    private function _load() {
        if (isset($this->_session->cartContent)) {
            $this->_content = unserialize($this->_session->cartContent);
        }

        return $this;
    }

    private function _save() {
        $this->_session->cartContent = serialize($this->_content);
    }

    public function setContent($content) {
        $this->_content = $content;
    }

    public function getContent() {
        return $this->_content;
    }

    public function add(Models_Model_Product $item) {
	    if(!$item instanceof Models_Model_Product)  {
		    throw new Exceptions_SeotoasterPluginException('Item should be Models_Model_Product instance');
	    }
	    $itemKey = $this->_generateStorageKey($item);
	    if(!array_key_exists($itemKey, $this->_content)) {
		    $itemTax = $this->_calculateTax($item);
		    $this->_content[$itemKey] = array(
			    'qty'      => 1,
			    'options'  => $item->getDefaultOptions(),
			    'item'     => $item,
			    'tax'      => $itemTax,
			    'taxPrice' => $item->getPrice() + $itemTax
		    );
	    }
	    else {
		    $this->_content[$itemKey]['qty']++;
	    }

	    $this->_save();
    }

	private function _calculateTax(Models_Model_Product $item) {
		if(($taxClass = $item->getTaxClass()) != 0) {
			$zoneId  = false;
			$zones   = Models_Mapper_Zone::getInstance()->fetchAll();
			if(is_array($zones) && !empty($zones)) {
				foreach($zones as $zone) {
					$zips = $zone->getZip();
					if(is_array($zips) && !empty($zips)) {
						if(in_array($this->_shoppingConfig['zip'], $zips)) {
							$zoneId = $zone->getId();
							break;
						}
					}
					$states = $zone->getStates();
					if(is_array($states) && !empty($states)) {
						foreach($states as $state) {
							if($state['id'] == $this->_shoppingConfig['state']) {
								$zoneId = $zone->getId();
								break;
							}
						}
					}
					$countries = $zone->getCountries(true);
					if(is_array($countries) && !empty($countries)) {
						if(in_array($this->_shoppingConfig['country'], $countries)) {
							$zoneId = $zone->getId();
							break;
						}
					}
				}
				if($zoneId) {
					$tax = Models_Mapper_Tax::getInstance()->findByZoneId($zoneId);
					$rateMethodName = 'getRate' . $taxClass;
					return ($item->getPrice() /100) * $tax->$rateMethodName();
				}
			}
		}
		return 0;
	}

	public function getStorageKey($item) {
		return $this->_generateStorageKey($item);
	}

	public function calculate() {
		$summary = array(
			'subTotal' => 0,
			'totalTax' => 0,
			'shipping' => 0,
			'total'    => 0
		);
		if(is_array($this->_content) && !empty($this->_content)) {
			foreach($this->_content as $storageKey => $cartItem) {
				$summary['subTotal'] += $cartItem['item']->getPrice() * $cartItem['qty'];
				$summary['totalTax'] += $cartItem['tax'] * $cartItem['qty'];
			}
			$summary['total'] = $summary['subTotal'] + $summary['totalTax'];
		}
		return $summary;
	}

	private function _generateStorageKey($item) {
		return substr(md5($item->getName() . $item->getSku() . $item->getDefaultOptions()), 0, 10);
	}

    public function remove($item, $complete = true) {
		$storageKey = $this->_generateStorageKey($item);
	    if(array_key_exists($storageKey, $this->_content)) {
			if($complete) {
		        unset($this->_content[$storageKey]);
			}
		    else {
			    $cartItem = $this->_content[$storageKey];
			    $cartItem['qty']--;
			    $this->_content[$storageKey] = $cartItem;
		    }
			$this->_save();
			return true;
		}
	    return false;
    }

	public function updateQty($item, $newQty) {
		$storageKey = $this->_generateStorageKey($item);
		if(array_key_exists($storageKey, $this->_content)) {
			$cartItem        = $this->_content[$storageKey];
			$oldQty          = $cartItem['qty'];
			$cartItem['qty'] = $oldQty + ($newQty - $oldQty);
			if($cartItem['qty'] <= 0) {
				unset($this->_content[$storageKey]);
			}
			else {
				$this->_content[$storageKey] = $cartItem;
			}
			$this->_save();
			return true;
		}
		return false;
	}

	public function clean() {
		$this->_session->cartContent = null;
	}

}
