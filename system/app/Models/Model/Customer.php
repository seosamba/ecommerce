<?php
/**
 * Customer Model
 * @todo add address validator
 */
class Models_Model_Customer extends Application_Model_Models_User {

	protected $_addresses;

	protected $_billing_address_id;

	protected $_shipping_address_id;

	public function getBillingAddress() {
		if ($this->_billing_address_id){
			return $this->getAddressById($this->_billing_address_id);
		}
		return reset($this->_filterAddress('address_type', 'billing'));
	}

	public function getShippingAddress() {
		if ($this->_shipping_address_id){
			return $this->getAddressById($this->_shipping_address_id);
		}
		return reset($this->_filterAddress('address_type', 'shipping'));
	}

	public function setAddresses($addresses) {
		$this->_addresses = $addresses;
		return $this;
	}

	public function getAddresses() {
		return $this->_addresses;
	}

	public function addAddress($address, $type) {
		if ($this->_addresses === null) {
			$this->_addresses = array();
		}
		$address = array_merge($address, array('address_type' => $type));
		array_push($this->_addresses, $address);

		return $this;
	}

	public function setBillingAddressId($billing_address_id) {
		$this->_billing_address_id = $billing_address_id;
	}

	public function getBillingAddressId() {
		return $this->_billing_address_id;
	}

	public function setShippingAddressId($shipping_address_id) {
		$this->_shipping_address_id = $shipping_address_id;
	}

	public function getShippingAddressId() {
		return $this->_shipping_address_id;
	}

	public function getAddressById($id) {
		return $this->_filterAddress('id', $id);
	}

	private function _filterAddress($param, $value){
		return array_filter($this->_addresses, function($address) use ($param, $value) { return $address[$param] === $value; });
	}

}
