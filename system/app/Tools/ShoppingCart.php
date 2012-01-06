<?php
/**
 * ShoppingCart
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_ShoppingCart {

	const ZONETYPE_ZIP          = 'zip';

	const ZONETYPE_STATE        = 'state';

	const ZONETYPE_COUNTRY      = 'country';

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

    public function setContent($content) {
        $this->_content = $content;
    }

    public function getContent() {
        return $this->_content;
    }

    public function add(Models_Model_Product $item, $options) {
	    if(!$item instanceof Models_Model_Product)  {
		    throw new Exceptions_SeotoasterPluginException('Item should be Models_Model_Product instance');
	    }
	    $itemKey = $this->_generateStorageKey($item, $options);
	    if(!array_key_exists($itemKey, $this->_content)) {
		    $itemTax   = $this->_calculateTax($item);
		    $modifiers = $this->_getModifiers($item, $options);
		    $itemPrice = $this->_calculateItemPrice($item, $modifiers, $itemTax);
		    $this->_content[$itemKey] = array(
			    'qty'       => 1,
			    'options'   => $options,
			    'modifiers' => $modifiers,
			    'item'      => $item,
			    'price'     => $itemPrice,
			    'weight'    => $this->_calculateItemWeight($item, $modifiers),
			    'tax'       => $itemTax,
			    'taxPrice'  => $itemPrice + $itemTax
		    );
	    }
	    else {
		    $this->_content[$itemKey]['qty']++;
	    }

	    $this->_save();
    }

	private function _calculateItemWeight(Models_Model_Product $item, $modifiers) {
		$weight = $item->getWeight();
		if(!empty($modifiers)) {
			foreach($modifiers as $modifier) {
				$weight = (($modifier['weightSign'] == '+') ? $weight + $modifier['weightValue'] : $weight - $modifier['weightValue']);
			}
		}
		return $weight;
	}

	private function _calculateItemPrice(Models_Model_Product $item, $modifiers, $tax = 0) {
		$price = $item->getPrice();
		if(!empty($modifiers)) {
			foreach($modifiers as $modifier) {
				$addPrice = (($modifier['priceType'] == 'unit') ? $modifier['priceValue'] : ($price / 100) * $modifier['priceValue']);
				$price    = (($modifier['priceSign'] == '+') ? $price + $addPrice : $price - $addPrice);
            }
		}
		return $price;
	}

	public function getStorageKey($item, $options = array()) {
		return $this->_generateStorageKey($item, $options);
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
				$summary['subTotal'] += $cartItem['price'] * $cartItem['qty'];
				$summary['totalTax'] += $cartItem['tax'] * $cartItem['qty'];
			}
			$summary['total'] = $summary['subTotal'] + $summary['totalTax'];
		}
		return $summary;
	}

    public function remove($storageKey, $complete = true) {
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

	public function updateQty($storageKey, $newQty) {
		if(array_key_exists($storageKey, $this->_content)) {
			$cartItem        = $this->_content[$storageKey];
			$cartItem['qty'] = $cartItem['qty'] + ($newQty - $cartItem['qty']);
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

	public function calculateCartWeight() {
		$totalWeight = 0;
		if(is_array($this->_content) && !empty($this->_content)) {
			foreach($this->_content as $cartItem) {
				$totalWeight += $cartItem['weight'] * $cartItem['qty'];
			}
		}
		return $totalWeight;
	}

	public function clean() {
		$this->_session->cartContent = null;
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

	private function _calculateTax(Models_Model_Product $item) {
		if(($taxClass = $item->getTaxClass()) != 0) {
			$zoneId = $this->_getZoneId();
			if($zoneId) {
				$tax = Models_Mapper_Tax::getInstance()->findByZoneId($zoneId);
				if($tax !== null) {
					$rateMethodName = 'getRate' . $taxClass;
					return ($item->getPrice() /100) * $tax->$rateMethodName();
				}
			}
		}
		return 0;
	}

	/**
	 * Tries to find zone id using all zone types (zip, state, country)
	 *
	 * @return int
	 */
	private function _getZoneId() {
		$zones = Models_Mapper_Zone::getInstance()->fetchAll();
		if(is_array($zones) && !empty($zones)) {
			foreach($zones as $zone) {
				$zoneIdByZip     = $this->_getZoneIdByType($zone, self::ZONETYPE_ZIP);
				if($zoneIdByZip) {
					return $zoneIdByZip;
				}
				$zoneIdByState   = $this->_getZoneIdByType($zone, self::ZONETYPE_STATE);
				if($zoneIdByState) {
					return $zoneIdByState;
				}
				$zoneIdByCountry = $this->_getZoneIdByType($zone, self::ZONETYPE_COUNTRY);
				if($zoneIdByCountry) {
					return $zoneIdByCountry;
				}
			}
		}
		return 0;
	}

	/**
	 * Gives zone id by type such as: zip, state, country
	 *
	 * @param Models_Model_Zone $zone
	 * @param string $type
	 * @return int
	 */
	private function _getZoneIdByType(Models_Model_Zone $zone, $type = self::ZONETYPE_ZIP) {
		$zoneParts = array();
		switch($type) {
			case self::ZONETYPE_ZIP:
				$zoneParts = $zone->getZip();
			break;
			case self::ZONETYPE_STATE:
				$zoneParts = $zone->getStates();
			break;
			case self::ZONETYPE_COUNTRY:
				$zoneParts = $zone->getCountries(true);
			break;
		}
		if(is_array($zoneParts) && !empty($zoneParts)) {
			if($type == self::ZONETYPE_STATE) {
				foreach($zoneParts as $zonePart) {
					if($zonePart['id'] == $this->_shoppingConfig['state']) {
						return $zone->getId();
					}
				}
			}
			if(in_array($this->_shoppingConfig[$type], $zoneParts)) {
				return $zone->getId();
			}
		}
		return 0;
	}

	private function _generateStorageKey($item, $options = array()) {
		return substr(md5($item->getName() . $item->getSku() . http_build_query($options)), 0, 10);
	}

	private function _getModifiers(Models_Model_Product $item, $options = array()) {
		$modifiers = array();
		if(!empty($options)) {
			$defaultOptions = $item->getDefaultOptions();
			foreach($defaultOptions as $defaultOption) {
				foreach($options as $optionId => $selectionId) {
					if($defaultOption['id'] != $optionId) {
						continue;
					}
					$defaultSelections = $defaultOption['selection'];
					if(empty($defaultSelections)) {
						return array();
					}
					foreach($defaultSelections as $defaultSelection) {
						if($defaultSelection['id'] != $selectionId) {
							continue;
						}
						$modifiers[$defaultOption['title']] = $defaultSelection;
					}
				}
			}
		}
		return $modifiers;
	}
}
