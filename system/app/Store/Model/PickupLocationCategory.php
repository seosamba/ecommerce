<?php
/**
 * Class Store_Model_PickupLocationCategory
 */
class Store_Model_PickupLocationCategory extends Application_Model_Models_Abstract
{
    protected $_name;

    protected $_img;

    protected $_externalCategory;

    public function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setImg($img)
    {
        $this->_img = $img;
        return $this;
    }

    public function getImg()
    {
        return $this->_img;
    }

    public function setExternalCategory($externalCategory)
    {
        $this->_externalCategory = $externalCategory;
        return $this;
    }

    public function getExternalCategory()
    {
        return $this->_externalCategory;
    }



}
