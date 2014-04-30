<?php
/**
 * Class Store_Model_PickupLocationConfig
 */
class Store_Model_PickupLocationConfig extends Application_Model_Models_Abstract
{

    protected $_amountTypeLimit;

    protected $_amountLimit;

    protected $_locationZones;

    public function setAmountLimit($amountLimit)
    {
        $this->_amountLimit = $amountLimit;
        return $this;
    }

    public function getAmountLimit()
    {
        return $this->_amountLimit;
    }

    public function setAmountTypeLimit($amountTypeLimit)
    {
        $this->_amountTypeLimit = $amountTypeLimit;
        return $this;
    }

    public function getAmountTypeLimit()
    {
        return $this->_amountTypeLimit;
    }

    public function setLocationZones($locationZones)
    {
        $this->_locationZones = $locationZones;
        return $this;
    }

    public function getLocationZones()
    {
        return $this->_locationZones;
    }
}