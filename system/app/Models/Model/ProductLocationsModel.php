<?php


class Models_Model_ProductLocationsModel extends Application_Model_Models_Abstract
{


    protected $_productId = '';
    protected $_locationId = '';
    protected $_inventory = '';
    protected $_isDefaultLocation = '';
    protected $_isQuickProduct = '';

    /**
     * @return string
     */
    public function getProductId()
    {
        return $this->_productId;
    }

    /**
     * @param string $productId
     */
    public function setProductId($productId)
    {
        $this->_productId = $productId;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocationId()
    {
        return $this->_locationId;
    }

    /**
     * @param string $locationId
     */
    public function setLocationId($locationId)
    {
        $this->_locationId = $locationId;

        return $this;
    }

    /**
     * @return string
     */
    public function getInventory()
    {
        return $this->_inventory;
    }

    /**
     * @param string $inventory
     */
    public function setInventory($inventory)
    {
        $this->_inventory = $inventory;

        return $this;
    }

    /**
     * @return string
     */
    public function getIsDefaultLocation()
    {
        return $this->_isDefaultLocation;
    }

    /**
     * @param string $isDefaultLocation
     */
    public function setIsDefaultLocation($isDefaultLocation)
    {
        $this->_isDefaultLocation = $isDefaultLocation;

        return $this;
    }

    public function getIsQuickProduct()
    {
        return $this->_isQuickProduct;
    }

    public function setIsQuickProduct($isQuickProduct)
    {
        $this->_isQuickProduct = $isQuickProduct;

        return $this;
    }




}

