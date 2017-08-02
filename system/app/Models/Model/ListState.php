<?php
class Models_Model_ListState extends Application_Model_Models_Abstract {

    protected $_id;

    protected $_country;

    protected $_state;

    protected $_name;

    public function getCountry() {
        return $this->_country;
    }

    public function setCountry($_country) {
        $this->_country = $_country;
    }

    public function getState() {
        return $this->_state;
    }

    public function setState($_state) {
        $this->_state = $_state;
    }

    public function getName() {
        return $this->_name;
    }

    public function setName($_name) {
        $this->_name = $_name;
    }
}