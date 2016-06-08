<?php
/**
 * Discount model
 *
 * Class Store_Model_Discount
 */
class Store_Model_Discount extends Application_Model_Models_Abstract
{

    protected $_discountQuantity;
    protected $_discountPriceType;
    protected $_discountPriceSign;
    protected $_applyScope;
    protected $_discountAmount;

    public function setApplyScope($applyScope)
    {
        $this->_applyScope = $applyScope;
        return $this;
    }

    public function getApplyScope()
    {
        return $this->_applyScope;
    }

    public function setDiscountPriceSign($discountPriceSign)
    {
        $this->_discountPriceSign = $discountPriceSign;
        return $this;
    }

    public function getDiscountPriceSign()
    {
        return $this->_discountPriceSign;
    }

    public function setDiscountPriceType($discountPriceType)
    {
        $this->_discountPriceType = $discountPriceType;
        return $this;
    }

    public function getDiscountPriceType()
    {
        return $this->_discountPriceType;
    }

    public function setDiscountQuantity($discountQuantity)
    {
        $this->_discountQuantity = $discountQuantity;
        return $this;
    }

    public function getDiscountQuantity()
    {
        return $this->_discountQuantity;
    }

    public function setDiscountAmount($discountAmount)
    {
        $this->_discountAmount = $discountAmount;
        return $this;
    }

    public function getDiscountAmount()
    {
        return $this->_discountAmount;
    }




}