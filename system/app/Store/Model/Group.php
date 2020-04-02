<?php
/**
 * Group
 *
 *
 */
class Store_Model_Group extends Application_Model_Models_Abstract {

	protected $_groupName;
    protected $_priceType;
    protected $_priceSign;
    protected $_priceValue;
    protected $_nonTaxable;

	public function setGroupName($groupName) {
		$this->_groupName = $groupName;
		return $this;
	}

	public function getGroupName() {
		return $this->_groupName;
	}

    public function setPriceType($priceType) {
        $this->_priceType = $priceType;
        return $this;
    }

    public function getPriceType() {
        return $this->_priceType;
    }

    public function setPriceSign($priceSign) {
        $this->_priceSign = $priceSign;
        return $this;
    }

    public function getPriceSign() {
        return $this->_priceSign;
    }

    public function setPriceValue($priceValue) {
        $this->_priceValue = $priceValue;
        return $this;
    }

    public function getPriceValue() {
        return $this->_priceValue;
    }

    /**
     * @return mixed
     */
    public function getNonTaxable()
    {
        return $this->_nonTaxable;
    }

    /**
     * @param mixed $nonTaxable
     * @return mixed
     */
    public function setNonTaxable($nonTaxable)
    {
        $this->_nonTaxable = $nonTaxable;

        return $this;
    }



}