<?php
/**
 * iamne Eugene I. Nezhuta <eugene@seotoaster.com>
 *
 */

class Tools_Shipping_Shipping {

	protected $_shoppingConfig        = array();

	protected $_sessionHelper         = null;

	protected $_customer              = null;

	protected $_shippingData          = array();

	public function __construct(array $shoppingConfig) {
		$this->_shoppingConfig = $shoppingConfig;
		$this->_sessionHelper  = Zend_Controller_Action_HelperBroker::getStaticHelper('session');
	}

	public function calculateShipping($shippingData) {
		$this->_shippingData = $shippingData;
		$this->_customer     = $this->_sessionHelper->getCurrentUser();
		if($this->_customer->getRoleId() == Tools_Security_Acl::ROLE_GUEST) {
			$this->_customer = $this->_saveNewCustomer($shippingData);
			$this->_sessionHelper->setCurrentUser($this->_customer);
		}
		$shippingCalculator = '_calculate' . ucfirst((($this->_shoppingConfig['shippingType'] != 'external') ? 'internal' : $this->_shoppingConfig['shippingType']));

		if(!method_exists($this, $shippingCalculator)) {
			throw new Exceptions_SeotoasterPluginException('Wrong shipping calculator type');
		}

		return $this->$shippingCalculator();
	}

	/**
	 * Calculate internal shipping
	 */
	protected function _calculateInternal() {
		$cartStorage = Tools_ShoppingCart::getInstance();
		return $cartStorage->setCustomerInfo($this->_customer->toArray())->calculate();
	}

	/**
	 * Calculate external shipping - run shipping plugin
	 */
	protected function _calculateExternal() {
		$shippingServiceClass = ucfirst($this->_shoppingConfig['shippingPlugin']);
		if(class_exists($shippingServiceClass)) {
			$shippingServicePlugin = Tools_Factory_PluginFactory::createPlugin($shippingServiceClass, array(), array());
			$shippingServicePlugin->setConfig($this->_shoppingConfig['shippingExternal']);
			$shippingServicePlugin->setOrigination($this->_getOrigination());
			$shippingServicePlugin->setDestination($this->_getCustomerShippingAddress());
			$shippingServicePlugin->setWeight(Tools_ShoppingCart::getInstance()->calculateCartWeight(), $this->_shoppingConfig['weightUnit']);
			return $shippingServicePlugin->run();
		}
	}

	protected function _getCustomerShippingAddress() {
		return array(
			'firstName'    => $this->_shippingData['firstName'],
			'lastName'     => $this->_shippingData['lastName'],
			'company'      => $this->_shippingData['company'],
			'email'        => $this->_shippingData['email'],
			'address1'     => $this->_shippingData['shippingAddress1'],
			'address2'     => $this->_shippingData['shippingAddress2'],
			'city'         => $this->_shippingData['city'],
			'state'        => $this->_shippingData['state'],
			'zip'          => $this->_shippingData['zipCode'],
			'country'      => $this->_shippingData['country'],
			'phone'        => $this->_shippingData['phone'],
			'mobile'       => $this->_shippingData['mobile'],
			'instructions' => (isset($this->_shippingData['instructions'])) ? strip_tags($this->_shippingData['instructions']) : '',
			'referrer'     => $_SERVER['REMOTE_ADDR']
		);
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

	/**
	 * Save new customer to the databse
	 *
	 * @param array $customerData
	 * @return Models_Model_Customer
	 */
	protected function _saveNewCustomer($customerData) {
		$cutomer = new Models_Model_Customer();
		$cutomer->setRoleId(Shopping::ROLE_CUSTOMER);
		$cutomer->setEmail($customerData['email']);
		$cutomer->setFullName($customerData['firstName'] . ' ' . $customerData['lastName']);
		$cutomer->setIpaddress($_SERVER['REMOTE_ADDR']);
		$cutomer->setPassword(md5(uniqid('customer_' . time())));
		$cutomer->setShippingAddress(array(
			'shippingAddress1' => $customerData['shippingAddress1'],
			'shippingAddress2' => $customerData['shippingAddress2'],
			'country'          => $customerData['country'],
			'city'             => $customerData['city'],
			'state'            => $customerData['state'],
			'zipCode'          => $customerData['zipCode']
		));
		$cutomer->setBillingAddress(array());
		$cutomer->setCompany($customerData['company']);
		$cutomer->setMobile($customerData['mobile']);
		$customerId = Models_Mapper_CustomerMapper::getInstance()->save($cutomer);
		$cutomer->setId($customerId);
		return $cutomer;
	}

}
