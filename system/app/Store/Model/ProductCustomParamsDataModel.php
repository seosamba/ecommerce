<?php

class Store_Model_ProductCustomParamsDataModel extends Application_Model_Models_Abstract
{

    protected $_paramId = '';

    protected $_productId = '';

    protected $_paramValue = '';

    protected $_paramsOptionId = null;

    /**
     * @return string
     */
    public function getParamId()
    {
        return $this->_paramId;
    }

    /**
     * @param string $paramId
     * @return string
     */
    public function setParamId($paramId)
    {
        $this->_paramId = $paramId;

        return $this;
    }

    /**
     * @return string
     */
    public function getProductId()
    {
        return $this->_productId;
    }

    /**
     * @param string $productId
     * @return string
     */
    public function setProductId($productId)
    {
        $this->_productId = $productId;

        return $this;
    }

    /**
     * @return string
     */
    public function getParamValue()
    {
        return $this->_paramValue;
    }

    /**
     * @param string $paramValue
     * @return string
     */
    public function setParamValue($paramValue)
    {
        $this->_paramValue = $paramValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getParamsOptionId()
    {
        return $this->_paramsOptionId;
    }

    /**
     * @param string $paramsOptionId
     * @return string
     */
    public function setParamsOptionId($paramsOptionId)
    {
        $this->_paramsOptionId = $paramsOptionId;

        return $this;
    }



}
