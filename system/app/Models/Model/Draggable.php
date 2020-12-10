<?php

class Models_Model_Draggable extends Application_Model_Models_Abstract {

    protected $_id;

    protected $_data;

    protected $_updatedAt;

    protected $_userId;

    protected $_ipAddress;

    protected $_pageId;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }
    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->_updatedAt;
    }

    /**
     * @param mixed $updatedAt
     * @return Models_Model_Draggable
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->_updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->_userId;
    }

    /**
     * @param mixed $userId
     * @return Models_Model_Draggable
     */
    public function setUserId($userId)
    {
        $this->_userId = $userId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIpAddress()
    {
        return $this->_ipAddress;
    }

    /**
     * @param mixed $ipAddress
     * @return Models_Model_Draggable
     */
    public function setIpAddress($ipAddress)
    {
        $this->_ipAddress = $ipAddress;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageId()
    {
        return $this->_pageId;
    }

    /**
     * @param mixed $pageId
     * @return Models_Model_Draggable
     */
    public function setPageId($pageId)
    {
        $this->_pageId = $pageId;
        return $this;
    }


}
