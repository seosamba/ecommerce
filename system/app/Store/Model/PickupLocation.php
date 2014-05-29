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

    protected $_lng;

    protected $_lat;

    protected $_workingHours;

    protected $_locationCategoryId;

    protected $_notes;

    protected $_weight;

    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setAddress1($address1)
    {
        $this->_address1 = $address1;
        return $this;
    }

    public function getAddress1()
    {
        return $this->_address1;
    }

    public function setAddress2($address2)
    {
        $this->_address2 = $address2;
        return $this;
    }

    public function getAddress2()
    {
        return $this->_address2;
    }

    public function setCity($city)
    {
        $this->_city = $city;
        return $this;
    }

    public function getCity()
    {
        return $this->_city;
    }

    public function setCountry($country)
    {
        $this->_country = $country;
        return $this;
    }

    public function getCountry()
    {
        return $this->_country;
    }

    public function setPhone($phone)
    {
        $this->_phone = $phone;
        return $this;
    }

    public function getPhone()
    {
        return $this->_phone;
    }

    public function setZip($zip)
    {
        $this->_zip = $zip;
        return $this;
    }

    public function getZip()
    {
        return $this->_zip;
    }

    public function setWorkingHours($workingHours)
    {
        $this->_workingHours = $workingHours;
        return $this;
    }

    public function getWorkingHours()
    {
        return $this->_workingHours;
    }

    public function setLocationCategoryId($locationCategoryId)
    {
        $this->_locationCategoryId = $locationCategoryId;
        return $this;
    }

    public function getLocationCategoryId()
    {
        return $this->_locationCategoryId;
    }

    public function setLng($lng)
    {
        $this->_lng = $lng;
        return $this;
    }

    public function getLng()
    {
        return $this->_lng;
    }

    public function setLat($lat)
    {
        $this->_lat = $lat;
        return $this;
    }

    public function getLat()
    {
        return $this->_lat;
    }

    public function setNotes($notes)
    {
        $this->_notes = $notes;
        return $this;
    }

    public function getNotes()
    {
        return $this->_notes;
    }

    public function setWeight($weight)
    {
        $this->_weight = $weight;
        return $this;
    }

    public function getWeight()
    {
        return $this->_weight;
    }


}