<?php
/**
 * Class Store_Model_PickupLocation
 */
class Store_Model_PickupLocation extends Application_Model_Models_Abstract
{

    protected $_name;

    protected $_country;

    protected $_city;

    protected $_address1;

    protected $_address2;

    protected $_phone;

    protected $_zip;

    protected $_workingHours;

    protected $_locationCategoryId;

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setAddress1($address1)
    {
        $this->_address1 = $address1;
    }

    public function getAddress1()
    {
        return $this->_address1;
    }

    public function setAddress2($address2)
    {
        $this->_address2 = $address2;
    }

    public function getAddress2()
    {
        return $this->_address2;
    }

    public function setCity($city)
    {
        $this->_city = $city;
    }

    public function getCity()
    {
        return $this->_city;
    }

    public function setCountry($country)
    {
        $this->_country = $country;
    }

    public function getCountry()
    {
        return $this->_country;
    }

    public function setPhone($phone)
    {
        $this->_phone = $phone;
    }

    public function getPhone()
    {
        return $this->_phone;
    }

    public function setZip($zip)
    {
        $this->_zip = $zip;
    }

    public function getZip()
    {
        return $this->_zip;
    }

    public function setWorkingHours($workingHours)
    {
        $this->_workingHours = $workingHours;
    }

    public function getWorkingHours()
    {
        return $this->_workingHours;
    }

    public function setLocationCategoryId($locationCategoryId)
    {
        $this->_locationCategoryId = $locationCategoryId;
    }

    public function getLocationCategoryId()
    {
        return $this->_locationCategoryId;
    }


}