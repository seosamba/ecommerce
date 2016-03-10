<?php
class Models_Model_ShippingUrl extends Application_Model_Models_Abstract {

    protected $_name;

    protected $_url;

    protected $_default_status;


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
        return $this->_default_status;
    }

    public function setDefaultStatus($_default_status) {
        $this->_default_status = $_default_status;
    }

}