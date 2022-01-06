<?php

class Models_Model_FilterPresetModel extends Application_Model_Models_Abstract
{

    protected $_creatorId = '';

    protected $_filterPresetName = '';

    protected $_filterPresetData = '';

    protected $_isDefault = '';

    /**
     * @return string
     */
    public function getCreatorId()
    {
        return $this->_creatorId;
    }

    /**
     * @param string $creatorId
     * @return Models_Model_FilterPresetModel
     */
    public function setCreatorId($creatorId)
    {
        $this->_creatorId = $creatorId;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilterPresetName()
    {
        return $this->_filterPresetName;
    }

    /**
     * @param string $filterPresetName
     * @return Models_Model_FilterPresetModel
     */
    public function setFilterPresetName($filterPresetName)
    {
        $this->_filterPresetName = $filterPresetName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilterPresetData()
    {
        return $this->_filterPresetData;
    }

    /**
     * @param string $filterPresetData
     * @return Models_Model_FilterPresetModel
     */
    public function setFilterPresetData($filterPresetData)
    {
        $this->_filterPresetData = $filterPresetData;
        return $this;
    }

    /**
     * @return string
     */
    public function getIsDefault()
    {
        return $this->_isDefault;
    }

    /**
     * @param string $isDefault
     * @return Models_Model_FilterPresetModel
     */
    public function setIsDefault($isDefault)
    {
        $this->_isDefault = $isDefault;
        return $this;
    }



}
