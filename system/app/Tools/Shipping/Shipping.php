<?php
/**
 * iamne Eugene I. Nezhuta <eugene@seotoaster.com>
 *
 */

class Tools_Shipping_Shipping {

	protected $_shoppingConfig = array();

	protected $_sessionHelper  = null;

	/**
	 * @var null|Models_Model_Customer
	 */
	protected $_customer       = null;

	protected $_shippingData   = array();

	protected $_mailValidator  = null;

	public function __construct(array $shoppingConfig) {
		$this->_shoppingConfig = $shoppingConfig;
		$this->_sessionHelper  = Zend_Controller_Action_HelperBroker::getStaticHelper('session');
		$this->_mailValidator  = new Zend_Validate_Db_NoRecordExists(array(
			'table' => 'user',
			'field' => 'email'
		));
	}

	public function calculateShipping($shippingData) {
		$this->_shippingData = $shippingData;
		$this->_customer     = $this->_sessionHelper->getCurrentUser();
		if ($this->_customer->getRoleId() === Tools_Security_Acl::ROLE_GUEST) {
			$this->_saveNewCustomer($shippingData);
			$this->_sessionHelper->setCurrentUser($this->_customer);
		} elseif ($this->_customer->getId() && $this->_customer->getRoleId() !== Shopping::ROLE_CUSTOMER) {
			//user exists and logged in, trying to fetch customer data
			$this->_customer->addAddress($shippingData, Models_Model_Customer::ADDRESS_TYPE_SHIPPING, true);
			Models_Mapper_CustomerMapper::getInstance()->save($this->_customer);
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
		return $cartStorage->calculate();
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
			$shippingServicePlugin->setDestination($this->_customer->getShippingAddress());
			$shippingServicePlugin->setWeight(Tools_ShoppingCart::getInstance()->calculateCartWeight(), $this->_shoppingConfig['weightUnit']);
			return $shippingServicePlugin->run();
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

	/**
	 * Save new customer to the databse
	 *
	 * @param array $customerData
	 * @return Models_Model_Customer
	 */
	protected function _saveNewCustomer($customerData) {
		if(null !== (Models_Mapper_CustomerMapper::getInstance()->findByEmail($customerData['email']))) {
			throw new Exception('User with given email already exists');
		}

		$this->_customer->setRoleId(Shopping::ROLE_CUSTOMER)
			->setEmail($customerData['email'])
			->setFullName($customerData['firstname'] . ' ' . $customerData['lastname'])
			->setIpaddress($_SERVER['REMOTE_ADDR'])
			->setPassword(md5(uniqid('customer_' . time())))
			->addAddress($customerData, Models_Model_Customer::ADDRESS_TYPE_SHIPPING);

		$result = Models_Mapper_CustomerMapper::getInstance()->save($this->_customer);
		if ($result) {
			$this->_customer->setId($result);
		}

		return $this->_customer;
	}

}
