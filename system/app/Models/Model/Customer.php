<?php
/**
 * Customer Model
 * @todo add address validator
 */
class Models_Model_Customer extends Application_Model_Models_User {

	const ADDRESS_TYPE_BILLING = 'billing';

	const ADDRESS_TYPE_SHIPPING = 'shipping';

	protected $_addresses;

	protected $_default_billing_address_id;

	protected $_default_shipping_address_id;

    protected $_group_id;

	public function getDefaultAddress($type = self::ADDRESS_TYPE_SHIPPING) {
		if (empty($type)) {
			throw new Exception('Address type must be defined');
		}
		switch ($type) {
			case static::ADDRESS_TYPE_SHIPPING :
				if ($this->_default_shipping_address_id !== null) {
					return $this->getAddressById($this->_default_shipping_address_id);
				}
				break;
			case static::ADDRESS_TYPE_BILLING :
				if ($this->_default_billing_address_id !== null) {
					return $this->getAddressById($this->_default_billing_address_id);
				}
				break;
			default:
				throw new Exception('Unknown address type given');
				break;
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

	public function setDefaultBillingAddressId($billing_address_id) {
		$this->_default_billing_address_id = $billing_address_id;
		return $this;
	}

	public function getDefaultBillingAddressId() {
		return $this->_default_billing_address_id;
	}

	public function setDefaultShippingAddressId($shipping_address_id) {
		$this->_default_shipping_address_id = $shipping_address_id;
		return $this;
	}

	public function getDefaultShippingAddressId() {
		return $this->_default_shipping_address_id;
	}

    public function setGroupId($_group_id) {
        $this->_group_id = $_group_id;
        return $this;
    }

    public function getGroupId() {
        return $this->_group_id;
    }

	public function getAddressById($id) {
		$list = $this->_filterAddresses('id', $id);
		return $list !== null ? reset($list) : null;
	}

	private function _filterAddresses($param, $value){
		if (!is_array($this->_addresses) || empty($this->_addresses)){
			return null;
		}
		return array_filter($this->_addresses, function($address) use ($param, $value) { return $address[$param] === $value; });
	}

}
