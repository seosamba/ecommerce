<?php
class Models_Model_ShippingServiceLabel extends Application_Model_Models_Abstract {

    protected $_name;

    protected $_label;

    public function getName()
    {
        return $this->_name;
    }

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function getLabel()
    {
        return $this->_label;
    }

    public function setLabel($label)
    {
        $this->_label = $label;
    }

}