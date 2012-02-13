<?php

class Models_Model_Customer extends Application_Model_Models_User {

	protected $_company         = '';

	protected $_shippingAddress = '';

	protected $_billingAddress  = '';

	protected $_phone           = '';

	protected $_mobile          = '';


	public function setBillingAddress($billingAddress) {
		$this->_billingAddress = is_array($billingAddress) ? serialize($billingAddress) : $billingAddress;
		return $this;
	}

	public function getBillingAddress($unserialize = true) {
		return ($unserialize) ? unserialize($this->_billingAddress) : $this->_billingAddress;
	}

	public function setCompany($company) {
		$this->_company = $company;
		return $this;
	}

	public function getCompany() {
		return $this->_company;
	}

	public function setMobile($mobile) {
		$this->_mobile = $mobile;
		return $this;
	}

	public function getMobile() {
		return $this->_mobile;
	}

	public function setPhone($phone) {
		$this->_phone = $phone;
		return $this;
	}

	public function getPhone() {
		return $this->_phone;
	}

	public function setShippingAddress($shippingAddress) {
		$this->_shippingAddress = is_array($shippingAddress) ? serialize($shippingAddress) : $shippingAddress;
		return $this;
	}

	public function getShippingAddress($unserialize = true) {
		return ($unserialize) ? unserialize($this->_shippingAddress) : $this->_shippingAddress;
	}

	public function toArray() {
		$explodedName = explode(' ', $this->_fullName);
		$data = array(
			'firstName' => $explodedName[0],
			'lastName'  => $explodedName[1],
			'company'    => $this->_company,
			'email'     => $this->_email,
			'mobile'    => $this->_mobile,
			'phone'     => $this->_phone
		);
		$data = array_merge($data, $this->getShippingAddress(), $this->getBillingAddress());
		return $data;
	}
}
