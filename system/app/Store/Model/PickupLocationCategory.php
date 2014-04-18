<?php
/**
 * Class Store_Model_PickupLocationCategory
 */
class Store_Model_PickupLocationCategory extends Application_Model_Models_Abstract {

    protected $_name;

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function getName()
    {
        return $this->_name;
    }

}