<?php
/**
 * Group
 *
 *
 */
class Store_Model_GroupPrice extends Application_Model_Models_Abstract {

	protected $_groupId;
    protected $_productId;
    protected $_priceValue;
    protected $_priceSign;
    protected $_priceType;

	public function setGroupId($groupId) {
		$this->_groupId = $groupId;
		return $this;
	}

	public function getGroupId() {
		return $this->_groupId;
	}

    public function setProductId($productId) {
        $this->_productId = $productId;
        return $this;
    }

    public function getProductId() {
        return $this->_productId;
    }

    public function setPriceValue($priceValue) {
        $this->_priceValue = $priceValue;
        return $this;
    }

    public function getPriceValue() {
        return $this->_priceValue;
    }

    public function setPriceSign($priceSign) {
        $this->_priceSign = $priceSign;
        return $this;
    }

    public function getPriceSign() {
        return $this->_priceSign;
    }

    public function setPriceType($priceType) {
        $this->_priceType = $priceType;
        return $this;
    }

    public function getPriceType() {
        return $this->_priceType;
    }
}