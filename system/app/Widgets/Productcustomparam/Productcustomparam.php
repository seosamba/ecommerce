<?php

class Widgets_Productcustomparam_Productcustomparam extends Widgets_Abstract
{

    const READ_ONLY = 'readonly';

    protected $_cacheable = false;

    protected $_isReadOnly = false;

    protected $_productCustomParamsDataMapper;

    protected $_customParamsData = array();

    protected $_customParamKey = '';

    protected function _load()
    {
        if (empty($this->_options)) {
            return $this->_translator->translate('No options provided');
        }

        try {
            $productMapper = Models_Mapper_ProductMapper::getInstance();
            if (is_numeric($this->_options[0])) {
                $productModel = $productMapper->find(intval($this->_options[0]));
                array_shift($this->_options);
            } else {
                $productModel = $productMapper->findByPageId($this->_toasterOptions['id']);
            }

            if (!$productModel instanceof Models_Model_Product) {
                return $this->_translator->translate('Product not found');
            }

            $productId = $productModel->getId();
            $registryKey = 'productCustomParamsData' . $productId;

            $this->_productCustomParamsDataMapper = Store_Mapper_ProductCustomParamsDataMapper::getInstance();
            if (!Zend_Registry::isRegistered($registryKey)) {
                $this->_customParamsData = $this->_productCustomParamsDataMapper->findByProductIdAggregated($productId);
                Zend_Registry::set($registryKey, $this->_customParamsData);
            } else {
                $this->_customParamsData = Zend_Registry::get($registryKey);
            }

            $this->_customParamKey = $this->_options[0] . '_' . $this->_options[1];
            $method = strtolower(array_shift($this->_options));

            if (empty($this->_options[0])) {
                return $this->_translator->translate('Please specify custom param name');
            }

            if (in_array(self::READ_ONLY, $this->_options)) {
                $this->_isReadOnly = true;
            }

            if (method_exists($this, $method)){
                return $this->{'_render' . ucfirst($method)}();
            }

            return '<b>Method ' . $method . ' doesn\'t exist</b>';

        } catch (Exception $e) {
            return '<b>Method ' . $method . ' doesn\'t exist</b>';
        }
    }

    /**
     * @return string
     */
    private function _renderText()
    {

        if ($this->_isReadOnly === true) {
            if (array_key_exists($this->_customParamKey, $this->_customParamsData)) {
                return $this->_customParamsData[$this->_customParamKey]['param_value'];
            }

            return '';
        }

        return 'editing hasn\'t implemented yet';
    }

    /**
     * @return string
     */
    private function _renderSelect()
    {
        if ($this->_isReadOnly === true) {
            if (array_key_exists($this->_customParamKey, $this->_customParamsData)) {
                return $this->_customParamsData[$this->_customParamKey]['option_val'];
            }

            return '';
        }

        return 'editing hasn\'t implemented yet';
    }
}
