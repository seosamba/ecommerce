<?php
class Models_Model_ShippingUrl extends Application_Model_Models_Abstract {

    protected $_name;

    protected $_url;

    protected $_defaultStatus;


    public function getName() {
        return $this->_name;
    }

    public function setName($_name) {
        $this->_name = $_name;
    }

    public function getUrl() {
        return $this->_url;
    }

    public function setUrl($_url) {
        $this->_url = $_url;
    }

    public function getDefaultStatus() {
        return $this->_defaultStatus;
    }

    public function setDefaultStatus($_defaultStatus) {
        $this->_defaultStatus = $_defaultStatus;
    }

}