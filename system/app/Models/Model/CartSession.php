<?php
class Models_Model_CartSession extends Application_Model_Models_Abstract {

	const CART_STATUS_NEW       = 'new';

	const CART_STATUS_PENDING   = 'pending';

	const CART_STATUS_COMPLETED = 'completed';

	const CART_STATUS_CANCELED  = 'canceled';

	const CART_STATUS_ERROR     = 'error';

	protected $_cartContent = '';

	protected $_ipAddress   = '';

	protected $_id          = '';

	protected $_userId      = null;

	protected $_status      = null;

	protected $_gateway     = null;

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
	}

	public function getStatus() {
		return $this->_status;
	}

	public function setGateway($gateway) {
		$this->_gateway = $gateway;
	}

	public function getGateway() {
		return $this->_gateway;
	}


}
