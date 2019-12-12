<?php

/**
 * Class Store_Model_WishedProducts
 */
class Store_Model_WishedProducts extends Application_Model_Models_Abstract {

    protected $_userId;

    protected $_productId;

    protected $_addedDate;

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->_userId;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->_userId = $userId;

        return $this;
    }

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
    public function getAddedDate()
    {
        return $this->_addedDate;
    }

    /**
     * @param mixed $addedDate
     */
    public function setAddedDate($addedDate)
    {
        $this->_addedDate = $addedDate;

        return $this;
    }

}