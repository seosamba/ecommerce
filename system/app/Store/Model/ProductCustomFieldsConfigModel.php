<?php

class Store_Model_ProductCustomFieldsConfigModel extends Application_Model_Models_Abstract
{

    const CUSTOM_PARAM_TYPE_TEXT = 'text';

    const CUSTOM_PARAM_TYPE_SELECT = 'select';

    protected $_paramType = '';

    protected $_paramName = '';

    protected $_label = '';

    /**
     * @return string
     */
    public function getParamType()
    {
        return $this->_paramType;
    }

    /**
     * @param string $paramType
     */
    public function setParamType($paramType)
    {
        $this->_paramType = $paramType;

        return $this;
    }

    /**
     * @return string
     */
    public function getParamName()
    {
        return $this->_paramName;
    }

    /**
     * @param string $paramName
     */
    public function setParamName($paramName)
    {
        $this->_paramName = $paramName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->_label = $label;

        return $this;
    }


}
