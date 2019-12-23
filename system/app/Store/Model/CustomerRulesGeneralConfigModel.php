<?php

class Store_Model_CustomerRulesGeneralConfigModel extends Application_Model_Models_Abstract
{

    protected $_ruleName = '';

    protected $_createdAt = '';

    protected $_creatorId = '';

    protected $_updatedAt = '';

    protected $_editorId = null;

    /**
     * @return string
     */
    public function getRuleName()
    {
        return $this->_ruleName;
    }

    /**
     * @param string $ruleName
     * @return string
     */
    public function setRuleName($ruleName)
    {
        $this->_ruleName = $ruleName;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_createdAt;
    }

    /**
     * @param string $createdAt
     * @return string
     */
    public function setCreatedAt($createdAt)
    {
        $this->_createdAt = $createdAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreatorId()
    {
        return $this->_creatorId;
    }

    /**
     * @param string $creatorId
     * @return string
     */
    public function setCreatorId($creatorId)
    {
        $this->_creatorId = $creatorId;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->_updatedAt;
    }

    /**
     * @param string $updatedAt
     * @return string
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->_updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return null
     */
    public function getEditorId()
    {
        return $this->_editorId;
    }

    /**
     * @param null $editorId
     * @return null
     */
    public function setEditorId($editorId)
    {
        $this->_editorId = $editorId;

        return $this;
    }


}