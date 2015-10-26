<?php
/**
 * Shopping cart storage
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_ShoppingCart {

	/**
	 * @deprecated
	 */
	const ZONETYPE_ZIP = 'zip';

	/**
	 * @deprecated
	 */
	const ZONETYPE_STATE = 'state';

	/**
	 * @deprecated
	 */
	const ZONETYPE_COUNTRY = 'country';

	protected static $_instance = null;

	protected $_content = array();

	protected $_session = null;

	protected $_cartId = null;

	protected $_customerId = null;

	protected $_shippingAddressKey = null;

	protected $_billingAddressKey = null;

	protected $_shippingData = null;

	protected $_notes = null;

	protected $_coupons = null;

	protected $_subTotal = 0;

    protected $_shippingTax = 0;

    protected $_discountTax = 0;

    protected $_subTotalTax = 0;

	protected $_totalTax = 0;

    protected $_discountTaxRate = 0;

	protected $_total = 0;

	protected $_discount = 0;

    protected $_recurringPaymentType = '';

	private function __construct() {
		$this->_websiteHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('website');
		$this->_shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();

		if ($this->_session === null) {
			$websiteConfig = Zend_Registry::get('website');
			$this->_session = new Zend_Session_Namespace('cart_' . $websiteConfig['url']);
			unset($websiteConfig);
		}
		$this->_load();
	}

	private function __clone() {
	}

	private function __wakeup() {
	}

	/**
	 * Returns instance of Shopping Cart
	 * @return Tools_ShoppingCart
	 */
	public static function getInstance() {
		if (is_null(self::$_instance)) {
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
		if (is_array($item) && isset($item['sid'])) {
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
		$filteredContent = array_filter($this->_content, function ($item) use ($id) {
			if (!isset($item['product_id']) || $item['product_id'] != $id) {
				return (isset($item['id']) && $item['id'] == $id);
			}
			return true;
		});

		return reset($filteredContent);
	}

    /**
     * Returns quantity of products in cart
     *
     * @return int
     *
     */
    public function findProductQuantityInCart(){
        $quantity = 0;
        foreach($this->_content as $cartItem){
            $quantity = $quantity + $cartItem['qty'];
        }
        return $quantity;
    }

	/**
	 *
	 * @param Models_Model_Product $item
	 * @param array                $options
	 * @return float Item price with applied tax
	 */
	public function calculateProductPrice(Models_Model_Product $item, $options = array()) {
		$itemTax = Tools_Tax_Tax::calculateProductTax($item);
		$options = $this->_parseOptions($item, $options);
		$showWithTax = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('showPriceIncTax');

		if ((bool)$showWithTax) {
			$itemPrice = $this->_calculateItemPriceWithTax($item, $options);
			return $itemPrice + $itemTax;
		} else {
			$itemPrice = $this->_calculateItemPrice($item, $options);
			return $itemPrice;
		}
	}

	/**
	 * Add an item to the storage
	 *
	 * @param Models_Model_Product $item
	 * @param                      $options array of toaster products optionsId => selectionId
	 * @param int                  $qty     Items quantity
     * @param bool                 $recalculate Recalculate cart
	 * @throws Exceptions_SeotoasterPluginException
	 * @return string Item storage key
	 */
	public function add(Models_Model_Product $item, $options = array(), $qty = 1, $recalculate = true) {
		if (!$item instanceof Models_Model_Product) {
			throw new Exceptions_SeotoasterPluginException('Item should be Models_Model_Product instance');
		}
		$itemKey = $this->_generateStorageKey($item, $options);
		if (!array_key_exists($itemKey, $this->_content)) {
			$options = $this->_parseOptions($item, $options);
			$itemPrice = $this->_calculateItemPrice($item, $options);
			$item->setCurrentPrice($itemPrice);
			$itemTax = Tools_Tax_Tax::calculateProductTax($item);
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
				'note'             => '',
                'freeShipping'     => $item->getFreeShipping(),
                'freebies'         => $item->getFreebies(),
                'groupPriceEnabled' => $item->getGroupPriceEnabled(),
                'originalPrice'     => $item->getPrice()
			);
		} else {
			$this->_content[$itemKey]['qty'] += $qty;
		}
		unset($item);
        if($recalculate){
		    $this->calculate(true);
        }
		$this->_save();

		return $itemKey;
	}

	private function _calculateItemWeight(Models_Model_Product $item, $modifiers) {
		$weight = $item->getWeight();
		if (!empty($modifiers)) {
			foreach ($modifiers as $modifier) {
				$weight = (($modifier['weightSign'] == '+') ? $weight + $modifier['weightValue'] : $weight - $modifier['weightValue']);
			}
		}
		return $weight;
	}

	private function _calculateItemPrice(Models_Model_Product $item, $modifiers) {
		$originalPrice = is_null($item->getCurrentPrice()) ? $item->getPrice() : $item->getCurrentPrice();
		$price = $originalPrice;
		if (!empty($modifiers)) {
			foreach ($modifiers as $modifier) {
				if (!is_array($modifier) || empty($modifier)) {
					continue;
				}
				$addPrice = (($modifier['priceType'] == 'unit') ? $modifier['priceValue'] : ($originalPrice / 100) * $modifier['priceValue']);
				$price = (($modifier['priceSign'] == '+') ? $price + $addPrice : $price - $addPrice);
			}
		}
		return $price;
	}

	private function _calculateItemPriceWithTax(Models_Model_Product $item, $modifiers) {
		$originalPrice = is_null($item->getCurrentPrice()) ? $item->getPrice() : $item->getCurrentPrice();
		$price = $originalPrice;
		$taxRate = Tools_Tax_Tax::calculateProductTax($item, null, true);
		if (!empty($modifiers)) {
			foreach ($modifiers as $modifier) {
				if ($taxRate) {
					$addPrice = (($modifier['priceType'] == 'unit') ? $modifier['priceValue'] + round(($taxRate * $modifier['priceValue']) / 100, 2) : ($originalPrice / 100) * $modifier['priceValue']);
				} else {
					$addPrice = (($modifier['priceType'] == 'unit') ? $modifier['priceValue'] : ($originalPrice / 100) * $modifier['priceValue']);
				}
				$price = (($modifier['priceSign'] == '+') ? $price + $addPrice : $price - $addPrice);
			}
		}
		return $price;
	}

	public function getStorageKey($item, $options = array()) {
		return $this->_generateStorageKey($item, $options);
	}

	public function calculate($recalculate = false) {

        $summary = array(
            'subTotal'        => 0,
            'discount'        => 0,
            'totalTax'        => 0,
            'total'           => 0,
            'shipping'        => 0,
            'shippingTax'     => 0,
            'discountTax'     => 0,
            'subTotalTax'     => 0,
            'discountTaxRate' => 0,
            'showPriceIncTax' => (bool)$this->_shoppingConfig['showPriceIncTax']
        );

		$shippingPrice = 0;
		if (($shipping = $this->getShippingData()) !== null) {
			$shippingPrice = floatval($shipping['price']);
		}

		if ($recalculate === true) {

            if (null !== ($addrId = Tools_ShoppingCart::getInstance()->getAddressKey(Models_Model_Customer::ADDRESS_TYPE_SHIPPING))) {
				$destinationAddress = Tools_ShoppingCart::getInstance()->getAddressById($addrId);
			}
            $freebiesProductsInCart = array();
            $notFreebiesProductsInCart = array();
            $notFreebiesProductsInCartStorageKeys = array();
			if (is_array($this->_content) && !empty($this->_content)) {
				foreach ($this->_content as $storageKey => &$cartItem) {
					if (isset($cartItem['sid'])) {
						if($cartItem['freebies'] == 1){
                            $cartItem['price'] = 0;
                            $freebiesProductsInCart[$cartItem['id']] = $storageKey;
                        }else{
                            $notFreebiesProductsInCart[$cartItem['id']] = $cartItem['id'];
                            $notFreebiesProductsInCartStorageKeys[$cartItem['id']] = $storageKey;
                        }
                        $product = new Models_Model_Product(array(
							'price'    => $cartItem['price'],
							'taxClass' => $cartItem['taxClass']
						));

					} else {
						$product = Models_Mapper_ProductMapper::getInstance()->find($cartItem['product_id']);
						$product->setPrice($cartItem['price']);
					}

                    if (isset($cartItem['groupPriceEnabled'])) {
                        $product->setGroupPriceEnabled($cartItem['groupPriceEnabled']);
                        if ($cartItem['groupPriceEnabled'] !== 1 && is_int($this->getCustomerId())) {
                            $product->setGroupPriceEnabled(1);
                            $priceForGroup = Tools_GroupPriceTools::calculateGroupPrice($product, $cartItem['id']);
                            $product->setPrice($priceForGroup);
                            $cartItem['price'] = $priceForGroup;
                            $cartItem['groupPriceEnabled'] = 1;
                        }

                        if ($cartItem['groupPriceEnabled'] === 1 && !is_int($this->getCustomerId())) {
                            $cartItem['groupPriceEnabled'] = 0;
                            $product->setGroupPriceEnabled(0);
                            $product->setPrice($cartItem['originalPrice']);
                            $cartItem['price'] = $cartItem['originalPrice'];
                        }
                    }

					$cartItem['tax'] = Tools_Tax_Tax::calculateProductTax($product, isset($destinationAddress) ? $destinationAddress : null);
					$cartItem['taxPrice'] = $cartItem['price'] + $cartItem['tax'];
					$summary['subTotal'] += $cartItem['price'] * $cartItem['qty'];
					$summary['subTotalTax'] += $cartItem['tax'] * $cartItem['qty'];
				}
			}

            $summary['discount'] = $this->getDiscount();
            $summary['discountTaxRate'] = $this->getDiscountTaxRate();
            //process discount coupons
            $discountCoupons = Tools_CouponTools::filterCoupons($this->getCoupons(), Store_Model_Coupon::COUPON_TYPE_DISCOUNT);
            if(!empty($discountCoupons)){
                $summary['discount'] = 0;
                foreach ($discountCoupons as $coupon) {
                    $summary['discount'] += Tools_CouponTools::processDiscountCoupon($coupon, $summary);
                }
                $summary['discountTaxRate'] = isset($this->_shoppingConfig['couponDiscountTaxRate']) ? $this->_shoppingConfig['couponDiscountTaxRate'] : 0;
            }

            $summary['shipping'] = $shippingPrice;
            $freeShippingCoupons = Tools_CouponTools::filterCoupons($this->getCoupons(), Store_Model_Coupon::COUPON_TYPE_FREESHIPPING);
            if(!empty($freeShippingCoupons)){
                foreach ($freeShippingCoupons as $freeShippingCoupon) {
                    $freeCoupon = Tools_CouponTools::processFreeshippingCoupon($freeShippingCoupon);
                    if($freeCoupon){
                        $summary['shipping'] = 0;
                    }
                }
            }

            $summary['shippingTax'] = Tools_Tax_Tax::calculateShippingTax($summary['shipping'], isset($destinationAddress) ? $destinationAddress : null);
            $summary['discountTax'] = Tools_Tax_Tax::calculateDiscountTax($summary['discount'], $summary['discountTaxRate'], isset($destinationAddress) ? $destinationAddress : null);
            $summary['totalTax'] = $summary['subTotalTax'] + $summary['shippingTax'] - $summary['discountTax'];
            $summary['total']    = $summary['subTotal'] + $summary['totalTax'] + $summary['shipping'];

			foreach ($summary as $key => $value) {
				$methodName = 'set' . ucfirst($key);
				if (method_exists($this, $methodName)) {
					$this->$methodName($value);
				}
			}

			$summary['total'] -= $summary['discount'];

            //process freebies
            if(!empty($notFreebiesProductsInCart)){
                $freebiesProducts = Models_Mapper_ProductFreebiesSettingsMapper::getInstance()->getFreebiesByProdIds($notFreebiesProductsInCart);
                if(!empty($freebiesProducts)){
                    foreach($freebiesProducts as $freebiesProd){
                        $productForFreebies = $this->findBySid($notFreebiesProductsInCartStorageKeys[$freebiesProd['prod_id']]);
                        $freebiesProduct = Models_Mapper_ProductMapper::getInstance()->find($freebiesProd['freebies_id']);
                        $itemKey = $this->_generateStorageKey($freebiesProduct, array(0 => 'freebies_'.$freebiesProd['prod_id']));
                        if($freebiesProd['quantity'] <= $productForFreebies['qty'] && $freebiesProd['price_value'] < $summary['total']){
                            if (!$this->findBySid($itemKey)){
                                if($freebiesProduct instanceof Models_Model_Product){
                                    $freebiesProduct->setFreebies(1);
                                    $inStockCount = $freebiesProduct->getInventory();
                                    if(is_null($inStockCount)) {
                                        $this->add($freebiesProduct, array(0 => 'freebies_'.$freebiesProd['prod_id']), $freebiesProd['freebies_quantity'], false);
                                    }elseif(intval($inStockCount) >= $freebiesProd['freebies_quantity']){
                                        $this->add($freebiesProduct, array(0 => 'freebies_'.$freebiesProd['prod_id']), $freebiesProd['freebies_quantity'], false);
                                    }
                                }
                            }
                        }elseif($this->findBySid($itemKey)){
                            unset($this->_content[$itemKey]);
                            $this->_save();
                        }

                    }
                }
            }

			$this->setDiscount($summary['discount'])
					->setTotal($summary['total']);
		}
		return array(
			'subTotal'        => $this->getSubTotal(),
			'discount'        => $this->getDiscount(),
			'totalTax'        => $this->getTotalTax(),
			'total'           => $this->getTotal(),
            'shippingTax'     => $this->getShippingTax(),
            'discountTax'     => $this->getDiscountTax(),
            'subTotalTax'     => $this->getSubTotalTax(),
			'shipping'        => $shippingPrice
			//'showPriceIncTax' => (bool)$this->_shoppingConfig['showPriceIncTax']
		);
	}

	/**
	 * Remove item from the storage
	 *
	 * @param string|Models_Model_Product $item
	 * @param boolean                     $complete Remove completely or just decrease item qty
	 * @return boolean
	 * @throws Exceptions_SeotoasterException
	 */
	public function remove($item, $complete = true) {
		if (is_string($item)) {
			$storageKey = $item;
		} elseif ($item instanceof Models_Model_Product) {
			$storageKey = $this->findSidById($item->getId());
		} else {
			throw new Exceptions_SeotoasterException('Shopping cart storage key or Models_Model_Product object expected.');
		}
		if (array_key_exists($storageKey, $this->_content)) {
			if ($complete) {
                //remove freebies
                $productHasFreebies = Models_Mapper_ProductFreebiesSettingsMapper::getInstance()->getFreebies($this->_content[$storageKey]['id']);
                if(!empty($productHasFreebies)){
                    foreach($productHasFreebies as $freebies){
                        $freebiesProduct = Models_Mapper_ProductMapper::getInstance()->find($freebies['freebies_id']);
                        $itemKey = $this->_generateStorageKey($freebiesProduct, array(0 => 'freebies_'.$freebies['prod_id']));
                        if (array_key_exists($itemKey, $this->_content)) {
                            unset($this->_content[$itemKey]);
                        }
                    }
                }
                unset($this->_content[$storageKey]);
            } else {
				$this->_content[$storageKey]['qty']--;
			}
			$this->calculate(true);
			$this->_save();
			return true;
		}
		return false;
	}

	/**
	 * Recount item qty
	 *
	 * @param string|Models_Model_Product $item
	 * @param integer                     $newQty
	 * @return boolean
	 * @throws Exceptions_SeotoasterException
	 */
	public function updateQty($item, $newQty) {
		if (is_string($item)) {
			$storageKey = $item;
		} else if ($item instanceof Models_Model_Product) {
			$storageKey = $this->findSidById($item->getId());
		} else {
			throw new Exceptions_SeotoasterException('Shopping cart storage key or Models_Model_Product object expected.');
		}
		if (array_key_exists($storageKey, $this->_content)) {
			$this->_content[$storageKey]['qty'] += ($newQty - $this->_content[$storageKey]['qty']);
			if ($this->_content[$storageKey]['qty'] <= 0) {
				unset($this->_content[$storageKey]);
			}
			$this->calculate(true);
			$this->_save();
			return true;
		}
		return false;
	}

	public function calculateCartWeight($withFreeShipping = false) {
		$totalWeight = 0;
        if(is_array($this->_content) && !empty($this->_content)) {
            foreach($this->_content as $cartItem) {
                if(!$withFreeShipping){
                    if($cartItem['freeShipping'] == 0){
                        $totalWeight += $cartItem['weight'] * $cartItem['qty'];
                    }
                }else{
                    $totalWeight += $cartItem['weight'] * $cartItem['qty'];
                }
            }
        }
        return $totalWeight;
    }

	public function calculateCartPrice() {
		$totalPrice = 0;
		if (is_array($this->_content) && !empty($this->_content)) {
			foreach ($this->_content as $cartItem) {
				$totalPrice += $cartItem['price'] * $cartItem['qty'];
			}
		}
		return $totalPrice;
	}

	public function clean() {
		$this->_session->unsetAll();
	}

	private function _load() {
		$reflection = new Zend_Reflection_Class(__CLASS__);
		foreach ($reflection->getProperties() as $prop) {
			$name = $prop->getName();
			/**
			 * @var $prop Zend_Reflection_Property
			 */
			if ($prop->isStatic()) {
				continue;
			}
			$setter = 'set' . $this->_normalizeOptionsKey($name);

			if ($reflection->hasMethod($setter) && isset($this->_session->{$name})) {
				$value = @unserialize($this->_session->{$name});
				$this->$setter($value === false ? $this->_session->{$name} : $value);
			}
		}

		return $this;
	}

	private function _save() {
		$reflection = new Zend_Reflection_Class(__CLASS__);
		foreach ($reflection->getProperties() as $prop) {
			$name = $prop->getName();
			/**
			 * @var $prop Zend_Reflection_Property
			 */
			if ($prop->isStatic()) {
				continue;
			}
			$getter = 'get' . $this->_normalizeOptionsKey($name);

			if ($reflection->hasMethod($getter)) {
				$value = $this->$getter();
				$this->_session->__set($name, is_array($value) ? serialize($value) : $value);
			}
		}

		return $this;
	}

	protected function _normalizeOptionsKey($key) {
		return join('', array_map('ucfirst', explode('_', $key)));
	}

	/**
	 * Saves current cart data to database
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

		$cartSession->setCartContent($this->getContent())
				->setIpAddress($_SERVER['REMOTE_ADDR'])
				->setOptions($this->calculate());

		if ($customer->getId() !== null) {
			$cartSession->setUserId($customer->getId())
					->setShippingAddressId($this->_shippingAddressKey)
					->setBillingAddressId($this->_billingAddressKey);
		}

		if ($customer->getReferer()) {
			$cartSession->setReferer($customer->getReferer());
		}

        if (null !== ($shippingData = $this->getShippingData())) {
            $cartSession->setShippingPrice($shippingData['price']);
            if (isset($shippingData['type'])) {
                $cartSession->setShippingType($shippingData['type']);
            } else {
                $cartSession->setShippingType(null);
            }
            if (isset($shippingData['service'])) {
                $cartSession->setShippingService($shippingData['service']);
            } else {
                $cartSession->setShippingService(null);
            }
        }

		if ($this->getNotes()) {
			$cartSession->setNotes($this->getNotes());
		}

		$result = Models_Mapper_CartSessionMapper::getInstance()->save($cartSession);
		if ($result && $this->getCartId() === null) {
			$cartSession->notifyObservers();
			$this->setCartId($cartSession->getId())->save();
		}

		//saving "one use per client" coupons to DB
		if (sizeof($this->getCoupons())){
            $shoppingCouponUsage = Store_Mapper_CouponMapper::getInstance();
            $shoppingCouponUsage->saveCouponsToCart($this);
            $shoppingCouponUsage->saveCouponSales($this);
		}

		return $this;
	}

	public function restoreCartSession($cartId) {
		$sessionHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('session');
		$currentUser = $sessionHelper->getCurrentUser();

		$cartSession = Models_Mapper_CartSessionMapper::getInstance()->find(intval($cartId));
		if ($cartSession !== null) {
			//preventing user to restore foreign cart
			//			if ( $cartSession->getUserId() === null ||
			//              $currentUser->getRoleId() !== Shopping::ROLE_CUSTOMER ||
			//				$currentUser->getId() !== $cartSession->getUserId()){
			//				return false;
			//			}

			$this->setCartId($cartSession->getId())
					->setContent(is_null($cartSession->getCartContent()) ? array() : $cartSession->getCartContent())
					->save();
		}
	}

	private function _generateStorageKey($item, $options = array()) {
		return substr(md5($item->getName() . $item->getSku() . http_build_query($options)), 0, 10);
	}

	private function _parseOptions(Models_Model_Product $item, $options = array()) {
		$modifiers = array();
		if (!empty($options)) {
			$defaultOptions = $item->getDefaultOptions();
			foreach ($defaultOptions as $defaultOption) {
				switch ($defaultOption['type']) {
					case Models_Model_Option::TYPE_DROPDOWN:
					case Models_Model_Option::TYPE_RADIO:
						foreach ($options as $optionId => $selectionId) {
							if ($defaultOption['id'] != $optionId) {
								continue;
							}
							$defaultSelections = $defaultOption['selection'];
							if (empty($defaultSelections)) {
								return array();
							}
							foreach ($defaultSelections as $defaultSelection) {
								if ($defaultSelection['id'] != $selectionId) {
									continue;
								}
								$modifiers[$defaultOption['title']] = $defaultSelection;
							}
						}
						break;
					case Models_Model_Option::TYPE_DATE:
					case Models_Model_Option::TYPE_TEXT:
						$textValue = '';
						if (!empty($options[$defaultOption['id']])) {
							$textValue = $options[$defaultOption['id']];
						}
						$modifiers[$defaultOption['title']] = array(
							'option_id'   => $defaultOption['id'],
							'title'       => $textValue,
							'priceSign'   => null,
							'priceType'   => null,
							'priceValue'  => null,
							'weightSign'  => null,
							'weightValue' => null
						);
						break;
				}
			}
		}
		return $modifiers;
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

	/**
	 * @deprecated
	 */
	public function setAddressKey($type, $addressKey) {
		switch ($type) {
			case Models_Model_Customer::ADDRESS_TYPE_BILLING:
				$this->_billingAddressKey = $addressKey;
				break;
			case Models_Model_Customer::ADDRESS_TYPE_SHIPPING:
				$this->_shippingAddressKey = $addressKey;
				break;
		}

		return $this;
	}

	/**
	 * @deprecated
	 */
	public function getAddressKey($type) {
		switch ($type) {
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
		return $address ? $address->toArray() : null;
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

	public function setCoupons($coupons) {
		$this->_coupons = $coupons;
		return $this;
	}

	public function getCoupons() {
		return $this->_coupons;
	}

	public function setDiscount($discount) {
		$this->_discount = $discount;
		return $this;
	}

	public function getDiscount() {
		return $this->_discount;
	}

	public function setSubTotal($subTotal) {
		$this->_subTotal = $subTotal;
		return $this;
	}

	public function getSubTotal() {
		return $this->_subTotal;
	}

	public function setTotal($total) {
		$this->_total = $total;
		return $this;
	}

	public function getTotal() {
		return $this->_total;
	}

	public function setTotalTax($totalTax) {
		$this->_totalTax = $totalTax;
		return $this;
	}

	public function getTotalTax() {
		return $this->_totalTax;
	}

    public function setShippingTax($shippingTax) {
        $this->_shippingTax = $shippingTax;
        return $this;
    }

    public function getShippingTax() {
        return $this->_shippingTax;
    }

    public function setDiscountTax($discountTax) {
        $this->_discountTax = $discountTax;
        return $this;
    }

    public function getDiscountTax() {
        return $this->_discountTax;
    }

    public function setSubTotalTax($subTotalTax) {
        $this->_subTotalTax = $subTotalTax;
        return $this;
    }

    public function getSubTotalTax() {
        return $this->_subTotalTax;
    }

	public function setBillingAddressKey($billingAddressKey) {
		$this->_billingAddressKey = $billingAddressKey;
		return $this;
	}

	public function getBillingAddressKey() {
		return $this->_billingAddressKey;
	}

	public function setShippingAddressKey($shippingAddressKey) {
		$this->_shippingAddressKey = $shippingAddressKey;
		return $this;
	}

	public function getShippingAddressKey() {
		return $this->_shippingAddressKey;
	}

    public function setDiscountTaxRate($discountTaxRate) {
        $this->_discountTaxRate = $discountTaxRate;
        return $this;
    }

    public function getDiscountTaxRate() {
        return $this->_discountTaxRate;
    }

    /**
     * @return string
     */
    public function getRecurringPaymentType()
    {
        return $this->_recurringPaymentType;
    }

    /**
     * @param string $recurringPaymentType
     * @return string
     */
    public function setRecurringPaymentType($recurringPaymentType)
    {
        $this->_recurringPaymentType = $recurringPaymentType;

        return $this;
    }




}
