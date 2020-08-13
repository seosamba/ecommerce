<?php

/**
 * Class Store_Model_ProductCustomFieldsOptionsDataModel
 */
class Store_Model_ProductCustomFieldsOptionsDataModel extends Application_Model_Models_Abstract
{

    protected $_customParamId = '';

    protected $_optionValue = '';

    /**
     * @return string
     */
    public function getCustomParamId()
    {
        return $this->_customParamId;
    }

    /**
     * @param string $customParamId
     */
    public function setCustomParamId($customParamId)
    {
        $this->_customParamId = $customParamId;

        return $this;
    }

    /**
     * @return string
     */
    public function getOptionValue()
    {
        return $this->_optionValue;
    }

    /**
     * @param string $optionValue
     */
    public function setOptionValue($optionValue)
    {
        $this->_optionValue = $optionValue;

        return $this;
    }

}
