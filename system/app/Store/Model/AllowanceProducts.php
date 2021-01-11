<?php

class Store_Model_AllowanceProducts extends Application_Model_Models_Abstract {

    protected $_productId;

    protected $_allowanceDue;

    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->_productId;
    }

    /**
     * @param mixed $productId
     */
    public function setProductId($productId)
    {
        $this->_productId = $productId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAllowanceDue()
    {
        return $this->_allowanceDue;
    }

    /**
     * @param mixed $allowanceDue
     */
    public function setAllowanceDue($allowanceDue)
    {
        $this->_allowanceDue = $allowanceDue;

        return $this;
    }

}