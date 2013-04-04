<?php
/**
 * iamne Eugene I. Nezhuta <eugene@seotoaster.com>
 * @deprecated
 */

class Tools_Shipping_Shipping {
	const SHIPPING_TYPE_PICKUP   = 'pickup';
	const SHIPPING_TYPE_EXTERNAL = 'external';
	const SHIPPING_TYPE_INTERNAL = 'internal';

	protected $_shoppingConfig = array();

	protected $_sessionHelper  = null;

	public function __construct(array $shoppingConfig) {
		$this->_shoppingConfig = $shoppingConfig;
		$this->_sessionHelper  = Zend_Controller_Action_HelperBroker::getStaticHelper('session');
	}

	public function calculateShipping() {
		$method = ($this->_shoppingConfig['shippingType'] != self::SHIPPING_TYPE_EXTERNAL) ? self::SHIPPING_TYPE_INTERNAL : $this->_shoppingConfig['shippingType'];
		$shippingCalculator = '_calculate' . ucfirst($method);

		if(!method_exists($this, $shippingCalculator)) {
			throw new Exceptions_SeotoasterPluginException('Wrong shipping calculator type');
		}
		$result = $this->$shippingCalculator();
		if ($method === self::SHIPPING_TYPE_INTERNAL){
			$result = array('service' => self::SHIPPING_TYPE_INTERNAL,
				'rates' => array($result)
			);
		}
		return array($result);
	}

	/**
	 * Calculate internal shipping
	 * @return array List of results
	 */
	protected function _calculateInternal() {
		$shoppingCart        = Tools_ShoppingCart::getInstance();
		$shippingPrice       = 0;
		$orderPrice          = floatval($shoppingCart->calculateCartPrice());
		$orderWeight         = floatval($shoppingCart->calculateCartWeight());
		$shippingType        = $this->_shoppingConfig['shippingType'];
		$shippingGeneral     = $this->_shoppingConfig['shippingGeneral'];
		$userShippingAddress = $this->_getDestination();
		if($shippingType === self::SHIPPING_TYPE_PICKUP || !$userShippingAddress || empty($userShippingAddress)) {
			return array('type' => $shippingType, 'price' => $shippingPrice);
		}
		$shippingSettings = $this->_shoppingConfig['shipping' . ucfirst($shippingType)];
		$freeShippingSettings = $shippingGeneral;
		$locationType = $this->_shoppingConfig['country'] == $userShippingAddress['country'] ? 'national' : 'international';
		if (is_array($freeShippingSettings) && !empty($freeShippingSettings)){
			$freeLimit = floatval($freeShippingSettings['freeShippingOver']);
			if ($freeShippingSettings['freeShippingOptions'] === 'both'
					&& $orderPrice > $freeLimit) {
				return $shippingPrice;
			} elseif ($locationType === $freeShippingSettings['freeShippingOptions']
					&& $orderPrice > $freeLimit) {
				return $shippingPrice;
			}
		}

		if (is_array($shippingSettings) && !empty($shippingSettings)) {
			foreach($shippingSettings as $ruleNumber => $settingsData) {
				if($orderPrice > $settingsData['limit']) {
					if($ruleNumber < 3) {
						continue;
					}
					$shippingPrice = $settingsData[$locationType] ;
					break;
				}
				$shippingPrice = $settingsData[$locationType] ;
				break;
			}
		}
		return array('type' => 'flat per '.$shippingType, 'price' => $shippingPrice);
	}

	/**
	 * Calculate external shipping - run shipping plugin
	 * @todo add handling of multiple Shipping services
	 */
	protected function _calculateExternal() {
		$shippingServiceClass = ucfirst($this->_shoppingConfig['shippingPlugin']);
		if(class_exists($shippingServiceClass)) {
			$shippingServicePlugin = Tools_Factory_PluginFactory::createPlugin($shippingServiceClass, array(), array());
			$shippingServicePlugin->setConfig($this->_shoppingConfig['shippingExternal']);
			$shippingServicePlugin->setOrigination($this->_getOrigination());
			$shippingServicePlugin->setDestination($this->_getDestination());
			$shippingServicePlugin->setWeight(Tools_ShoppingCart::getInstance()->calculateCartWeight(), $this->_shoppingConfig['weightUnit']);
			return array('service' => $shippingServiceClass,
						 'rates' => $shippingServicePlugin->run());
		}
	}

	protected function _getOrigination() {
		return array(
			'address1' => $this->_shoppingConfig['address1'],
			'address2' => $this->_shoppingConfig['address2'],
			'city'     => $this->_shoppingConfig['city'],
			'state'    => isset($this->_shoppingConfig['state']) ? $this->_shoppingConfig['state'] : '',
			'zip'      => $this->_shoppingConfig['zip'],
			'country'  => $this->_shoppingConfig['country'],
			'phone'    => isset($this->_shoppingConfig['phone']) ? $this->_shoppingConfig['phone'] : ''
		);
	}

	protected function _getDestination() {
		return Tools_ShoppingCart::getAddressById(Tools_ShoppingCart::getInstance()->getAddressKey(Models_Model_Customer::ADDRESS_TYPE_SHIPPING));
	}

}
