<?php
/**
 * Shopping cart storage
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_ShoppingCart {

	const ZONETYPE_ZIP          = 'zip';

	const ZONETYPE_STATE        = 'state';

	const ZONETYPE_COUNTRY      = 'country';

    protected static $_instance = null;

	protected $_content         = array();

	protected $_session         = null;

	protected $_filterId        = 0;

	protected $_cartId          = null;

	protected $_shippingAddressKey  = null;

	protected $_billingAddressKey   = null;

	protected $_shippingData    = null;

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
	    return $this;
    }

	public function getCustomer() {
		$sessionHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('session');
		$currentUser = $sessionHelper->getCurrentUser();

		if ($currentUser->getId()) {
			$customer = Models_Mapper_CustomerMapper::getInstance()->find($currentUser->getId());
			if ($customer === null) {
				$customer = new Models_Model_Customer($currentUser->toArray());
			}
		} else {
			$customer = new Models_Model_Customer();
		}

		return $customer;
	}

	public function save() {
		$this->_save();
		return $this;
	}

    public function getContent() {
        return $this->_content;
    }

	/**
	 * Tries to find storage ID using real product item ID
	 *
	 * @param $id integer Real product id
	 * @return bool|string
	 */
	public function findSidById($id) {
		$item = $this->find($id);
		if(is_array($item) && isset($item['sid'])) {
			return $item['sid'];
		}
		return false;
	}

	/**
	 * Search by storage id through the storage and returns an appropriate product
	 *
	 * @param $sid string
	 * @return array|null
	 */
	public function findBySid($sid) {
		return (array_key_exists($sid, $this->_content)) ? $this->_content[$sid] : null;
	}

	/**
	 * Search for the product in storage by its real id
	 *
	 * @param $id integer
	 * @return mixed
	 */
	public function find($id) {
		$this->_filterId = $id;
		return array_shift(array_filter($this->_content, array($this, '_filterCallback')));
	}

	/**
	 * Add an item to the storage
	 *
	 * @param Models_Model_Product $item
	 * @param $options array of toaster products optionsId => selectionId
	 * @param int $qty Items quantity
	 * @throws Exceptions_SeotoasterPluginException
	 */
    public function add(Models_Model_Product $item, $options = array(), $qty = 1) {
	    if(!$item instanceof Models_Model_Product)  {
		    throw new Exceptions_SeotoasterPluginException('Item should be Models_Model_Product instance');
	    }
	    $itemKey = $this->_generateStorageKey($item, $options);
	    if(!array_key_exists($itemKey, $this->_content)) {
		    $itemTax   = $this->_calculateTax($item);
		    $options   = $this->_parseOptions($item, $options);
		    $itemPrice = $this->_calculateItemPrice($item, $options, $itemTax);
		    $this->_content[$itemKey] = array(
			    'qty'              => $qty,
			    'photo'            => $item->getPhoto(),
			    'name'             => $item->getName(),
			    'sku'              => $item->getSku(),
			    'mpn'              => $item->getMpn(),
			    'description'      => $item->getFullDescription(),
			    'shortDescription' => $item->getShortDescription(),
			    'sid'              => $itemKey,
			    'options'          => $options,
			    'id'               => $item->getId(),
			    'price'            => $itemPrice,
			    'weight'           => $this->_calculateItemWeight($item, $options),
			    'tax'              => $itemTax,
			    'taxPrice'         => $itemPrice + $itemTax,
			    'taxIncluded'      => isset($this->_shoppingConfig['showPriceIncTax']) ? (bool)$this->_shoppingConfig['showPriceIncTax'] : false,
			    'note'             => ''
		    );
	    }
	    else {
		    $this->_content[$itemKey]['qty'] += $qty;
	    }
	    unset($item);
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

	private function _calculateItemPrice(Models_Model_Product $item, $modifiers) {
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
		$summary = array('subTotal' => 0, 'totalTax' => 0, 'shipping' => 0, 'total' => 0);
		if(is_array($this->_content) && !empty($this->_content)) {
			foreach($this->_content as $storageKey => $cartItem) {
				$summary['subTotal'] += $cartItem['price'] * $cartItem['qty'];
				$summary['totalTax'] += $cartItem['tax'] * $cartItem['qty'];
			}
			$summary['shipping'] = 0;
			if (($shipping = $this->getShippingData()) !== null){
				$summary['shipping'] = $shipping['price'];
			}
			$summary['total']    = $summary['subTotal'] + $summary['totalTax'] + $summary['shipping'];

		}
		return $summary;
	}

	/**
	 * Remove item from the storage
	 *
	 * @param string|Models_Model_Product $item
	 * @param boolean $complete Remove completely or just decrease item qty
	 * @return boolean
	 * @throws Exceptions_SeotoasterException
	 */
    public function remove($item, $complete = true) {
	    if(is_string($item)) {
		    $storageKey = $item;
	    } else if($item instanceof Models_Model_Product) {
		    $storageKey = $this->findSidById($item->getId());
	    } else {
		    throw new Exceptions_SeotoasterException('Shopping cart storage key or Models_Model_Product object expected.');
	    }
	    if(array_key_exists($storageKey, $this->_content)) {
			if($complete) {
		        unset($this->_content[$storageKey]);
			} else {
			    $this->_content[$storageKey]['qty']--;
		    }
			$this->_save();
			return true;
		}
	    return false;
    }

	/**
	 * Recount item qty
	 *
	 * @param string|Models_Model_Product $item
	 * @param integer $newQty
	 * @return boolean
	 * @throws Exceptions_SeotoasterException
	 */
	public function updateQty($item, $newQty) {
		if(is_string($item)) {
		    $storageKey = $item;
	    } else if($item instanceof Models_Model_Product) {
		    $storageKey = $this->findSidById($item->getId());
	    } else {
		    throw new Exceptions_SeotoasterException('Shopping cart storage key or Models_Model_Product object expected.');
	    }
		if(array_key_exists($storageKey, $this->_content)) {
			$this->_content[$storageKey]['qty'] += ($newQty - $this->_content[$storageKey]['qty']);
			if($this->_content[$storageKey]['qty'] <= 0) {
				unset($this->_content[$storageKey]);
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

	public function calculateCartPrice() {
		$totalPrice = 0;
		if(is_array($this->_content) && !empty($this->_content)) {
			foreach($this->_content as $cartItem) {
				$totalPrice += $cartItem['price'] * $cartItem['qty'];
			}
		}
		return $totalPrice;
	}

	public function clean() {
		$this->_session->unsetAll();
	}

	private function _load() {
		if (isset($this->_session->cartContent)) {
			$this->setContent(unserialize($this->_session->cartContent));
		}
		if (isset($this->_session->cartId)) {
			$this->setCartId($this->_session->cartId);
		}
		if (isset($this->_session->shippingData)) {
			$this->setShippingData(unserialize($this->_session->shippingData));
		}

		$this->_shippingAddressKey  = $this->_session->shippingAddressKey;
		$this->_billingAddressKey   = $this->_session->billingAddressKey;

		return $this;
	}

    private function _save() {
        $this->_session->cartContent = serialize($this->getContent());
	    $this->_session->cartId      = $this->getCartId();
	    $this->_session->shippingAddressKey = $this->_shippingAddressKey;
	    $this->_session->billingAddressKey  = $this->_billingAddressKey;
	    $this->_session->shippingData  = serialize($this->getShippingData());
    }

	/**
	 * Saves cuurent cart data to database
	 * @return Tools_ShoppingCart
	 */
	public function saveCartSession($customer) {
		if (null === $customer) {
			$customer = $this->getCustomer();
		}

		if ($this->getCartId()) {
			$cartSession = Models_Mapper_CartSessionMapper::getInstance()->find($this->getCartId());
		} else {
			$cartSession = new Models_Model_CartSession();
			$cartSession->setStatus(Models_Model_CartSession::CART_STATUS_NEW);
		}

		$cartSessionContent = array();
		foreach ($this->getContent() as $uniqKey => $item) {
			$data = array(
				'product_id'    => $item['id'],
				'price'         => $item['price'],
				'qty'           => $item['qty'],
				'tax'           => $item['tax'],
				'tax_price'     => $item['taxPrice'],
				'options'       => array()
			);

			foreach ($item['options'] as $option) {
				$data['options'][$option['option_id']] = $option['id'];
			}
			array_push($cartSessionContent, $data);
		}
		$cartSession->setCartContent($cartSessionContent);
		$cartSession->setIpAddress($_SERVER['REMOTE_ADDR']);
		$cartSession->setOptions($this->calculate());

		if ($customer->getId() !== null) {
			$cartSession->setUserId($customer->getId())
					->setShippingAddressId( $this->_shippingAddressKey )
					->setBillingAddressId(  $this->_billingAddressKey );
		}

		if (null !== ($shippingData = $this->getShippingData())) {
			$cartSession->setShippingPrice($shippingData['price'])
				->setShippingType($shippingData['type'])
				->setShippingService($shippingData['service']);
		}

		$result = Models_Mapper_CartSessionMapper::getInstance()->save($cartSession);
		if ($result && $this->getCartId() === null){
			$this->setCartId($cartSession->getId())->save();
		}

        return $this;
	}

	public function restoreCartSession($cartId) {
		$sessionHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('session');
		$currentUser   = $sessionHelper->getCurrentUser();

		$cartSession = Models_Mapper_CartSessionMapper::getInstance()->find(intval($cartId));
		if ($cartSession !== null ) {
			//preventing user to restore foreign cart
			//			if ( $cartSession->getUserId() === null ||
			//              $currentUser->getRoleId() !== Shopping::ROLE_CUSTOMER ||
			//				$currentUser->getId() !== $cartSession->getUserId()){
			//				return false;
			//			}

			$this->setCartId($cartSession->getId())
				->setContent($cartSession->getCartContent())
				->save();
		}
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

	private function _calculateShipping() {

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

	private function _parseOptions(Models_Model_Product $item, $options = array()) {
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

	private function _filterCallback($item) {
		return (isset($item['id']) && $item['id'] == $this->_filterId);
	}

	public function setCartId($cartId) {
		$this->_cartId = $cartId;

		return $this;
	}

	/**
	 * Returns id if cart is saved null if not
	 * @return mixed Cart id or null if cart is not saved
	 */
	public function getCartId() {
		return $this->_cartId;
	}

	public function setAddressKey($type, $addressKey) {
		switch ($type){
			case Models_Model_Customer::ADDRESS_TYPE_BILLING:
				$this->_billingAddressKey = $addressKey;
				break;
			case Models_Model_Customer::ADDRESS_TYPE_SHIPPING:
				$this->_shippingAddressKey = $addressKey;
				break;
		}

		return $this;
	}

	public function getAddressKey($type) {
		switch ($type){
			case Models_Model_Customer::ADDRESS_TYPE_BILLING:
				return $this->_billingAddressKey;
				break;
			case Models_Model_Customer::ADDRESS_TYPE_SHIPPING:
				return $this->_shippingAddressKey;
				break;
		}
	}

	public static function getAddressById($id) {
		$addressTable = new Models_DbTable_CustomerAddress();
		$address = $addressTable->find($id)->current();
		return $address ? $address->toArray() : null ;
	}

	public function setShippingData($shippingData) {
		$this->_shippingData = $shippingData;
		return $this;
	}

	public function getShippingData() {
		return $this->_shippingData;
	}

}
