<?php
class Models_Model_CartSession extends Application_Model_Models_Abstract {

	const CART_STATUS_NEW           = 'new';

	const CART_STATUS_PENDING       = 'pending';

	const CART_STATUS_PROCESSING    = 'processing';

	const CART_STATUS_COMPLETED     = 'completed';

	const CART_STATUS_UNPROCESSED   = 'unprocessed';

	const CART_STATUS_CANCELED      = 'canceled';

	const CART_STATUS_ERROR         = 'error';

	protected $_cartContent = null;

	protected $_ipAddress   = '';

	protected $_id          = '';

	protected $_userId      = null;

	protected $_status      = null;

	protected $_gateway     = null;

	protected $_shippingAddressId = null;

	protected $_billingAddressId = null;

	protected $_shippingPrice = null;

	protected $_shippingType  = null;

	protected $_shippingService = null;

	public function setCartContent($cartContent) {
		$this->_cartContent = $cartContent;
		return $this;
	}

	public function getCartContent() {
		return $this->_cartContent;
	}

	public function setIpAddress($ipAddress) {
		$this->_ipAddress = $ipAddress;
		return $this;
	}

	public function getIpAddress() {
		return $this->_ipAddress;
	}

	public function setId($id) {
		$this->_id = $id;
		return $this;
	}

	public function getId()	{
		return $this->_id;
	}

	public function setUserId($userId) {
		$this->_userId = $userId;
		return $this;
	}

	public function getUserId()	{
		return $this->_userId;
	}

	public function setStatus($status) {
		$this->_status = $status;
		return $this;
	}

	public function getStatus() {
		return $this->_status;
	}

	public function setGateway($gateway) {
		$this->_gateway = $gateway;
		return $this;
	}

	public function getGateway() {
		return $this->_gateway;
	}

	public function setBillingAddressId($billingAddressId) {
		$this->_billingAddressId = $billingAddressId;

		return $this;
	}

	public function getBillingAddressId() {
		return $this->_billingAddressId;
	}

	public function setShippingAddressId($shippingAddressId) {
		$this->_shippingAddressId = $shippingAddressId;

		return $this;
	}

	public function getShippingAddressId() {
		return $this->_shippingAddressId;
	}

	public function setShippingPrice($shippingPrice) {
		$this->_shippingPrice = $shippingPrice;

		return $this;
	}

	public function getShippingPrice() {
		return $this->_shippingPrice;
	}

	public function setShippingService($shippingService) {
		$this->_shippingService = $shippingService;

		return $this;
	}

	public function getShippingService() {
		return $this->_shippingService;
	}

	public function setShippingType($shippingType) {
		$this->_shippingType = $shippingType;

		return $this;
	}

	public function getShippingType() {
		return $this->_shippingType;
	}

}
