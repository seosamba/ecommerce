<?php

class Widgets_Productcustomparam_Productcustomparam extends Widgets_Abstract
{

    const READ_ONLY = 'readonly';

    protected $_cacheable = false;

    protected $_isReadOnly = false;

    protected $_productCustomParamsDataMapper;

    protected $_customParamsData = array();

    protected $_customParamKey = '';

    protected $_productId = '';

    protected $_websiteHelper  = null;

    protected function _init()
    {
        $this->_view = new Zend_View(array('scriptPath' => __DIR__ . '/views'));
        $this->_websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');

        $this->_view->websiteUrl  = $this->_websiteHelper->getUrl();
    }

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

            $this->_productId = $productModel->getId();
            $this->_productCustomParamsDataMapper = Store_Mapper_ProductCustomParamsDataMapper::getInstance();
            if (!Zend_Registry::isRegistered($registryKey)) {
                $this->_customParamsData = $this->_productCustomParamsDataMapper->findByProductIdAggregated($productId);
                Zend_Registry::set($registryKey, $this->_customParamsData);
            } else {
                $this->_customParamsData = Zend_Registry::get($registryKey);
            }

            $this->_customParamKey = $this->_options[0] . '_' . $this->_options[1];
            $method = '_render' .ucfirst(strtolower(array_shift($this->_options)));

            if (empty($this->_options[0])) {
                return $this->_translator->translate('Please specify custom param name');
            }

            if (in_array(self::READ_ONLY, $this->_options)) {
                $this->_isReadOnly = true;
            }

            if (method_exists($this, $method)){
                return $this->$method();
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
        $customParamData = '';
        $customParamId = '';

        $productCustomFieldsConfigMapper = Store_Mapper_ProductCustomFieldsConfigMapper::getInstance();
        $productCustomFieldsConfigModel = $productCustomFieldsConfigMapper->getByName($this->_options[0]);

        if(!$productCustomFieldsConfigModel instanceof Store_Model_ProductCustomFieldsConfigModel) {
            return '';
        }

        if (array_key_exists($this->_customParamKey, $this->_customParamsData)) {
            $customParamId = $this->_customParamsData[$this->_customParamKey]['id'];
            $customParamData = $this->_customParamsData[$this->_customParamKey]['param_value'];
        }

        if ($this->_isReadOnly === true) {
            if(!empty($customParamData)) {
                return $customParamData;
            }

            return '';
        } else {
            $customer = Tools_ShoppingCart::getInstance()->getCustomer();

            if($customer->getRoleId() === Tools_Security_Acl::ROLE_ADMIN || $customer->getRoleId() === Tools_Security_Acl::ROLE_SUPERADMIN ) {
                $isNew = 1;
                if(!empty($customParamId)) {
                    $isNew = 0;
                }

                $this->_view->isNew = $isNew;
                $this->_view->type = Api_Store_Productcustomfieldsconfig::PRODUCT_CUSTOM_FIELD_TYPE_TEXT;
                $this->_view->uniqueName = $this->_options[0];
                $this->_view->customParamData = $customParamData;
                $this->_view->paramId = $productCustomFieldsConfigModel->getId();
                $this->_view->customParamProductId = $this->_productId;

                return $this->_view->render('productcustomparamEditor.phtml');
            }

            if(!empty($customParamData)) {
                return $customParamData;
            }

            return '';
        }
    }

    /**
     * @return string
     */
    private function _renderSelect()
    {
        $customParamValue = '';
        $customParamId = '';

        $productCustomFieldsConfigMapper = Store_Mapper_ProductCustomFieldsConfigMapper::getInstance();
        $productCustomFieldsConfigModel = $productCustomFieldsConfigMapper->getByName($this->_options[0]);

        if(!$productCustomFieldsConfigModel instanceof Store_Model_ProductCustomFieldsConfigModel) {
            return '';
        }

        if (array_key_exists($this->_customParamKey, $this->_customParamsData)) {
            $customParamId = $this->_customParamsData[$this->_customParamKey]['id'];
            $customParamValue = $this->_customParamsData[$this->_customParamKey]['option_val'];
        }

        if ($this->_isReadOnly === true) {
            if(!empty($customParamValue)) {
                return $customParamValue;
            }

            return '';
        } else {
            $customer = Tools_ShoppingCart::getInstance()->getCustomer();

            if($customer->getRoleId() === Tools_Security_Acl::ROLE_ADMIN || $customer->getRoleId() === Tools_Security_Acl::ROLE_SUPERADMIN ) {
                $isNew = 1;
                if(!empty($customParamId)) {
                    $isNew = 0;
                }

                $productCustomFieldsOptionsDataMapper = Store_Mapper_ProductCustomFieldsOptionsDataMapper::getInstance();
                $productCustomFieldsOptionsData = $productCustomFieldsOptionsDataMapper->findByCustomParamId($productCustomFieldsConfigModel->getId());

                if(!empty($productCustomFieldsOptionsData)) {
                    $this->_view->optionsData = $productCustomFieldsOptionsData;
                }

                $this->_view->isNew = $isNew;
                $this->_view->type = Api_Store_Productcustomfieldsconfig::PRODUCT_CUSTOM_FIELD_TYPE_SELECT;
                $this->_view->uniqueName = $this->_options[0];
                $this->_view->customParamData = $customParamValue;
                $this->_view->paramId = $productCustomFieldsConfigModel->getId();
                $this->_view->customParamProductId = $this->_productId;

                return $this->_view->render('productcustomparamEditor.phtml');
            }

            if(!empty($customParamValue)) {
                return $customParamValue;
            }

            return '';
        }
    }
}
