<?php
/**
 * Discount model
 *
 * Class Store_Model_DiscountProduct
 */
class Store_Model_DiscountProduct extends Application_Model_Models_Abstract
{
    protected $_productId;
    protected $_quantity;
    protected $_priceSign;
    protected $_priceType;
    protected $_status;
    protected $_amount;

    public function setProductId($id)
    {
        $this->_productId = $id;
        return $this;
    }

    public function getProductId()
    {
        return $this->_productId;
    }

    public function setPriceSign($priceSign)
    {
        $this->_priceSign = $priceSign;
        return $this;
    }

    public function getPriceSign()
    {
        return $this->_priceSign;
    }

    public function setPriceType($priceType)
    {
        $this->_priceType = $priceType;
        return $this;
    }

    public function getPriceType()
    {
        return $this->_priceType;
    }

    public function setQuantity($quantity)
    {
        $this->_quantity = $quantity;
        return $this;
    }

    public function getQuantity()
    {
        return $this->_quantity;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
        return $this;
    }

    public function getStatus()
    {
        return $this->_status;
    }

    public function setAmount($amount)
    {
        $this->_amount = $amount;
        return $this;
    }

    public function getAmount()
    {
        return $this->_amount;
    }

}