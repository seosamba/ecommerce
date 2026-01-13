<?php


class Models_Model_CartLocationInventoryModel extends Application_Model_Models_Abstract
{

    protected $_cartId = '';
    protected $_productId = '';
    protected $_locationId = '';
    protected $_locationInventory = '';
    protected $_productStatus = '';

    public function getCartId()
    {
        return $this->_cartId;
    }

    public function setCartId($cartId)
    {
        $this->_cartId = $cartId;

        return $this;
    }

    public function getProductId()
    {
        return $this->_productId;
    }

    public function setProductId($productId)
    {
        $this->_productId = $productId;

        return $this;
    }

    public function getLocationId()
    {
        return $this->_locationId;
    }

    public function setLocationId($locationId)
    {
        $this->_locationId = $locationId;

        return $this;
    }

    public function getLocationInventory()
    {
        return $this->_locationInventory;
    }

    public function setLocationInventory($locationInventory)
    {
        $this->_locationInventory = $locationInventory;

        return $this;
    }

    public function getProductStatus()
    {
        return $this->_productStatus;
    }

    public function setProductStatus($productStatus)
    {
        $this->_productStatus = $productStatus;

        return $this;
    }







}

