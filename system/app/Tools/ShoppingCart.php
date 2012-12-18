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

	protected $_customerId      = null;

	protected $_shippingAddressKey  = null;

	protected $_billingAddressKey   = null;

	protected $_shippingData    = null;
    
    protected $_notes           = null;

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
		} elseif ($this->getCustomerId()) {
			$customer = Models_Mapper_CustomerMapper::getInstance()->find($this->getCustomerId());
		}

		if (!isset($customer) || $customer === null) {
			$customer = new Models_Model_Customer($currentUser->toArray());
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
     *
     * @param Models_Model_Product $item
     * @param type $options
     * @return itemPrice 
     */
    public function calculateProductPrice(Models_Model_Product $item, $options = array()){
        $options   = $this->_parseOptions($item, $options);
		$itemPrice = $this->_calculateItemPrice($item, $options);
        return $itemPrice;
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
		    $itemTax   = Tools_Tax_Tax::calculateProductTax($item);
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
			    'taxClass'         => $item->getTaxClass(),
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
		$price = is_null($item->getCurrentPrice()) ? $item->getPrice() : $item->getCurrentPrice();
		if(!empty($modifiers)) {
			foreach($modifiers as $modifier) {
				$addPrice = (($modifier['priceType'] == 'unit') ? $modifier['priceValue'] : ($item->getPrice() / 100) * $modifier['priceValue']);
				$price    = (($modifier['priceSign'] == '+') ? $price + $addPrice : $price - $addPrice);
            }
		}
		return $price;
	}

	public function getStorageKey($item, $options = array()) {
		return $this->_generateStorageKey($item, $options);
	}

	public function calculate() {
		$summary = array('subTotal' => 0, 'totalTax' => 0, 'shipping' => 0, 'total' => 0, 'showPriceIncTax' => (bool)$this->_shoppingConfig['showPriceIncTax']);
		if(is_array($this->_content) && !empty($this->_content)) {
			if (null !== ($addrId = Tools_ShoppingCart::getInstance()->getAddressKey(Models_Model_Customer::ADDRESS_TYPE_SHIPPING))){
				$destinationAddress = Tools_ShoppingCart::getInstance()->getAddressById($addrId);
			} else {
				$destinationAddress = null;
			}

			foreach($this->_content as $storageKey => &$cartItem) {
				$product = new Models_Model_Product(array(
					'price'     => $cartItem['price'],
					'taxClass'  => $cartItem['taxClass']
				));

				$cartItem['tax'] = Tools_Tax_Tax::calculateProductTax($product, isset($destinationAddress) ? $destinationAddress : null);
				$cartItem['taxPrice'] = $cartItem['price'] + $cartItem['tax'];
				$summary['subTotal'] += $cartItem['price'] * $cartItem['qty'];
				$summary['totalTax'] += $cartItem['tax'] * $cartItem['qty'];
			}
			$summary['shipping'] = 0;
			if (($shipping = $this->getShippingData()) !== null){
				$summary['shipping'] = floatval($shipping['price']);
			}
			$summary['total'] = $summary['subTotal'] + $summary['totalTax'] + $summary['shipping'];

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
		if (isset($this->_session->customerId)) {
			$this->setCustomerId($this->_session->customerId);
		}
		if (isset($this->_session->shippingData)) {
			$this->setShippingData(unserialize($this->_session->shippingData));
		}
        if (isset($this->_session->notes)) {
			$this->setNotes($this->_session->notes);
		}

		$this->_shippingAddressKey  = $this->_session->shippingAddressKey;
		$this->_billingAddressKey   = $this->_session->billingAddressKey;

		return $this;
	}

    private function _save() {
        $this->_session->cartContent = serialize($this->getContent());
	    $this->_session->cartId      = $this->getCartId();
	    $this->_session->customerId  = $this->getCustomerId();
	    $this->_session->shippingAddressKey = $this->_shippingAddressKey;
	    $this->_session->billingAddressKey  = $this->_billingAddressKey;
	    $this->_session->shippingData  = serialize($this->getShippingData());
        $this->_session->notes      = $this->getNotes();
    }

	/**
	 * Saves cuurent cart data to database
	 *
	 * @param $customer
	 * @return Tools_ShoppingCart
	 */
	public function saveCartSession($customer = null) {
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
				$data['options'][$option['option_id']] = isset($option['id']) ? $option['id'] : $option['title'];
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

		if ($customer->getReferer()){
			$cartSession->setReferer($customer->getReferer());
		}

		if (null !== ($shippingData = $this->getShippingData())) {
			$cartSession->setShippingPrice($shippingData['price'])
				->setShippingType($shippingData['type'])
				->setShippingService($shippingData['service']);
		}
        
        if($this->getNotes()){
            $cartSession->setNotes($this->getNotes());
        }

		$result = Models_Mapper_CartSessionMapper::getInstance()->save($cartSession);
		if ($result && $this->getCartId() === null){
			$cartSession->notifyObservers();
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

	private function _generateStorageKey($item, $options = array()) {
		return substr(md5($item->getName() . $item->getSku() . http_build_query($options)), 0, 10);
	}

	private function _parseOptions(Models_Model_Product $item, $options = array()) {
		$modifiers = array();
		if(!empty($options)) {
			$defaultOptions = $item->getDefaultOptions();
			foreach($defaultOptions as $defaultOption) {
				switch ($defaultOption['type']){
					case Models_Model_Option::TYPE_DROPDOWN:
					case Models_Model_Option::TYPE_RADIO:
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
						break;
					case Models_Model_Option::TYPE_DATE:
					case Models_Model_Option::TYPE_TEXT:
						$modifiers[$defaultOption['title']] = array(
							'option_id' => $defaultOption['id'],
							'title'     => $options[$defaultOption['id']],
							'priceSign' => null,
							'priceType' => null,
							'priceValue' => null,
							'weightSign' => null,
							'weightValue' => null
						);
						break;
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

	public function setCustomerId($customerId) {
		$this->_customerId = $customerId;
		return $this;
	}

	public function getCustomerId() {
		return $this->_customerId;
	}
    
    public function setNotes($notes) {
		$this->_notes = $notes;
		return $this;
	}

	public function getNotes() {
		return $this->_notes;
	}

}
