<?php
/**
 * Customer Model
 * @todo add address validator
 */
class Models_Model_Customer extends Application_Model_Models_User {

	const ADDRESS_TYPE_BILLING = 'billing';

	const ADDRESS_TYPE_SHIPPING = 'shipping';

	protected $_addresses;

	protected $_billing_address_id;

	protected $_shipping_address_id;

	public function getBillingAddress() {
		if ($this->_billing_address_id){
			return $this->getAddressById($this->_billing_address_id);
		}
		$list = $this->_filterAddresses('address_type', self::ADDRESS_TYPE_BILLING);
		if (!empty($list)){
			return reset($list);
		}
		return null;
	}

	public function getShippingAddress() {
		if ($this->_shipping_address_id){
			return $this->getAddressById($this->_shipping_address_id);
		}
		$list = $this->_filterAddresses('address_type', self::ADDRESS_TYPE_SHIPPING);
		if (!empty($list)){
			return reset($list);
		}
		return null;
	}

	public function setAddresses($addresses) {
		$this->_addresses = $addresses;
		return $this;
	}

	public function getAddresses() {
		return $this->_addresses;
	}

	public function addAddress($address, $type, $useByDefault = false) {
		if ($this->_addresses === null) {
			$this->_addresses = array();
		}
		$address = array_merge($address, array('address_type' => $type));
		array_push($this->_addresses, $address);

		return $this;
	}

	public function setBillingAddressId($billing_address_id) {
		$this->_billing_address_id = $billing_address_id;
		return $this;
	}

	public function getBillingAddressId() {
		return $this->_billing_address_id;
	}

	public function setShippingAddressId($shipping_address_id) {
		$this->_shipping_address_id = $shipping_address_id;
		return $this;
	}

	public function getShippingAddressId() {
		return $this->_shipping_address_id;
	}

	public function getAddressById($id) {
		return $this->_filterAddresses('id', $id);
	}

	private function _filterAddresses($param, $value){
		if (!is_array($this->_addresses) || empty($this->_addresses)){
			return null;
		}
		return array_filter($this->_addresses, function($address) use ($param, $value) { return $address[$param] === $value; });
	}

}
