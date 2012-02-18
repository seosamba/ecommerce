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
			$this->_customer = $this->_saveNewCustomer($shippingData);
			$this->_sessionHelper->setCurrentUser($this->_customer);
		} else if ($this->_customer->getId() && $this->_customer->getRoleId() !== Shopping::ROLE_CUSTOMER) {
			//user exists and logged in, trying to fetch customer data
			$customer = Models_Mapper_CustomerMapper::getInstance()->find($this->_customer->getId());
			if ($customer !== null) {
				$customer->addAddress($shippingData, Models_Model_Customer::ADDRESS_TYPE_SHIPPING, true);
			} else {
				$customer = new Models_Model_Customer($this->_customer->toArray());
				$customer->addAddress($shippingData, Models_Model_Customer::ADDRESS_TYPE_SHIPPING);
				Models_Mapper_CustomerMapper::getInstance()->save($customer);
			}
			$this->_customer = $customer;
			$this->_sessionHelper->setCurrentUser($customer);
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
		return $cartStorage->setCustomer($this->_customer)->calculate();
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
		if($this->_customer->getEmail() != $customerData['email']) {
//			if(!$this->_mailValidator->isValid($customerData['email'])) {
//				throw new Exceptions_SeotoasterPluginException('We already have user with such e-mail in te database');
//			}
		}
		$customer = Models_Mapper_CustomerMapper::getInstance()->findByEmail($customerData['email']);
		if(!$customer) {
			$customer = new Models_Model_Customer();
		}

		$customer->setRoleId(Shopping::ROLE_CUSTOMER)
			->setEmail($customerData['email'])
			->setFullName($customerData['firstname'] . ' ' . $customerData['lastname'])
			->setIpaddress($_SERVER['REMOTE_ADDR'])
			->setPassword(md5(uniqid('customer_' . time())))
			->addAddress($customerData, Models_Model_Customer::ADDRESS_TYPE_SHIPPING);

		$result = Models_Mapper_CustomerMapper::getInstance()->save($customer);

		return $customer;
	}

}
