<?php

class Models_Model_Draggable extends Application_Model_Models_Abstract {

    protected $_id;
    protected $_data;

    public function getId() {
        return $this->_id;
    }

    public function setId($_id) {
        $this->_id = $_id;
    }
    public function getData() {
        return $this->_data;
    }

    public function setData($_data) {
        $this->_data = $_data;
    }

}