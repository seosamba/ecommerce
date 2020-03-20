<?php

/**
 * Class Api_Store_Productcustomfieldsconfig
 */
class Api_Store_Productcustomfieldsconfig extends Api_Service_Abstract
{

    /*
     * Product custom field select
     */
    const PRODUCT_CUSTOM_FIELD_TYPE_SELECT = 'select';

    /*
     * Product custom field text
     */
    const PRODUCT_CUSTOM_FIELD_TYPE_TEXT = 'text';

    /**
     * Mandatory fields
     *
     * @var array
     */
    protected $_mandatoryParams = array();

    /**
     * System response helper
     *
     * @var null
     */
    protected $_responseHelper = null;

    /**
     * @var array Access Control List
     */
    protected $_accessList = array(
        Tools_Security_Acl::ROLE_SUPERADMIN => array(
            'allow' => array('get', 'post', 'put', 'delete')
        ),
        Tools_Security_Acl::ROLE_ADMIN => array(
            'allow' => array('get', 'post', 'put', 'delete')
        )
    );


    public function init()
    {
        parent::init();
        $this->_responseHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('response');
    }


    /**
     *
     * Resource:
     * : /api/productcustomfieldsconfig/
     *
     * HttpMethod:
     * : GET
     *
     * @return JSON
     */
    public function getAction()
    {
        $limit = filter_var($this->_request->getParam('limit'), FILTER_SANITIZE_NUMBER_INT);
        $offset = filter_var($this->_request->getParam('offset'), FILTER_SANITIZE_NUMBER_INT);
        $sortOrder = filter_var($this->_request->getParam('order', 'spcfc.param_name'), FILTER_SANITIZE_STRING);
        $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);

        $productCustomFieldsConfigMapper = Store_Mapper_ProductCustomFieldsConfigMapper::getInstance();
        if ($id) {
            $where = $productCustomFieldsConfigMapper->getDbTable()->getAdapter()->quoteInto('spcfc.id = ?', $id);
            $data = $productCustomFieldsConfigMapper->fetchAll($where);
        } else {
            $data = $productCustomFieldsConfigMapper->fetchAll(null, $sortOrder, $limit, $offset);
        }

        return $data;
    }

    /**
     * Create new product custom param config
     *
     * Resource:
     * : /api/productcustomfieldsconfig/
     *
     * HttpMethod:
     * : POST
     *
     * @return JSON
     */
    public function postAction()
    {
        $data = $this->getRequest()->getParams();
        $translator = Zend_Registry::get('Zend_Translate');

        $fieldDataMissing = array_filter($this->_mandatoryParams, function ($param) use ($data) {
            if (!array_key_exists($param, $data) || empty($data[$param])) {
                return $param;
            }
        });

        if (!empty($fieldDataMissing)) {
            return array('status' => 'error', 'message' => $translator->translate('Missing mandatory params'));
        }

        $secureToken = $this->getRequest()->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
        $tokenValid = Tools_System_Tools::validateToken($secureToken, Shopping::SHOPPING_SECURE_TOKEN);
        if (!$tokenValid) {
            $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
            $websiteUrl = $websiteHelper->getUrl();
            return array('status' => 'error', 'message' => $translator->translate('Your session has timed-out. Please Log back in '.'<a href="'.$websiteUrl.'go">here</a>'));
        }

        if(preg_match('~[^\w-]~ui', $data['param_name'])) {
            return array('status' => 'error', 'message' => $translator->translate('Invalid param name. You can use only alphabet and digits. You can also use "-". White Spaces not allowed'));
        }

        $productCustomFieldsConfigMapper = Store_Mapper_ProductCustomFieldsConfigMapper::getInstance();
        $productCustomFieldsConfigModel = $productCustomFieldsConfigMapper->getByName($data['param_name']);
        if ($productCustomFieldsConfigModel instanceof Store_Model_ProductCustomFieldsConfigModel) {
            return array('status' => 'error', 'message' => $translator->translate('Custom param with such name already exists'));
        }

        $productCustomFieldsConfigModel = new Store_Model_ProductCustomFieldsConfigModel();

        $productCustomFieldsConfigModel->setOptions($data);
        $productCustomFieldsConfigMapper->save($productCustomFieldsConfigModel);

        $productCustomFieldsOptionsDataMapper = Store_Mapper_ProductCustomFieldsOptionsDataMapper::getInstance();

        $customFieldParamId = $productCustomFieldsConfigModel->getId();

        if ($data['param_type'] == self::PRODUCT_CUSTOM_FIELD_TYPE_SELECT) {
            $productCustomFieldsOptionsData = $productCustomFieldsOptionsDataMapper->findByCustomParamId($customFieldParamId);

            if(empty($productCustomFieldsOptionsData)) {
                foreach ($data['dropdownParams'] as $key => $params) {
                    $productCustomFieldsOptionsDataModel = new Store_Model_ProductCustomFieldsOptionsDataModel();

                    $productCustomFieldsOptionsDataModel->setCustomParamId($customFieldParamId);
                    $productCustomFieldsOptionsDataModel->setOptionValue($params['value']);

                    $productCustomFieldsOptionsDataMapper->save($productCustomFieldsOptionsDataModel);
                }
            } else {
                return array('status' => 'error', 'message' => $translator->translate('Custom param with such name already exists'));
            }
        }

        return array('status' => 'ok', 'message' => $translator->translate('Custom param has been created'));

    }

    /**
     * Update product custom param config
     *
     * Resource:
     * : /api/productcustomfieldsconfig/
     *
     * HttpMethod:
     * : PUT
     *
     * ## Parameters:
     * id (type integer)
     * : product custom param id to update
     *
     * @return JSON
     */
    public function putAction()
    {
        $data = json_decode($this->_request->getRawBody(), true);
        if (!empty($data['id']) && !empty($data[Tools_System_Tools::CSRF_SECURE_TOKEN])) {
            $translator = Zend_Registry::get('Zend_Translate');
            $secureToken = $data[Tools_System_Tools::CSRF_SECURE_TOKEN];
            $tokenValid = Tools_System_Tools::validateToken($secureToken, Shopping::SHOPPING_SECURE_TOKEN);
            if (!$tokenValid) {
                $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
                $websiteUrl = $websiteHelper->getUrl();
                return array('status' => 'error', 'message' => $translator->translate('Your session has timed-out. Please Log back in '.'<a href="'.$websiteUrl.'go">here</a>'));

            }
            $customParamId = filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT);

            $productCustomFieldsConfigMapper = Store_Mapper_ProductCustomFieldsConfigMapper::getInstance();
            $productCustomFieldsConfigModel = $productCustomFieldsConfigMapper->find($customParamId);

            if (!$productCustomFieldsConfigModel instanceof Store_Model_ProductCustomFieldsConfigModel) {
                return array('status' => 'error', 'message' => $translator->translate('Config doesn\'t exists'));
            }

            $oldParamName = $productCustomFieldsConfigModel->getParamName();

            if(preg_match('~[^\w-]~ui', $data['param_name'])) {
                return array('status' => 'error', 'message' => $translator->translate('Invalid param name. You can use only alphabet and digits. You can also use "-". White Spaces not allowed'));
            }
            $currentParamName = $data['param_name'];
            if ($oldParamName !== $currentParamName) {
                $validateTypeExists = new Zend_Validate_Db_RecordExists(array(
                    'table' => 'shopping_product_custom_fields_config',
                    'field' => 'param_name'
                ));
                if ($validateTypeExists->isValid($currentParamName)) {
                    return array('status' => 'error', 'message' => $translator->translate('You have another custom param with such name'));
                }
            }

            $productCustomFieldsConfigModel->setOptions($data);
            $productCustomFieldsConfigMapper->save($productCustomFieldsConfigModel);

            $productCustomFieldsOptionsDataMapper = Store_Mapper_ProductCustomFieldsOptionsDataMapper::getInstance();

            if ($data['param_type'] == self::PRODUCT_CUSTOM_FIELD_TYPE_SELECT) {
                $newDropdownParams = $data['dropdownParams'];
                $productCustomFieldsOptionsData = $productCustomFieldsOptionsDataMapper->findByCustomParamId($customParamId);

                if(!empty($productCustomFieldsOptionsData) && !empty($newDropdownParams)) {
                    foreach ($productCustomFieldsOptionsData as $key => $customFieldsOption) {
                        foreach ($newDropdownParams as $newDropdown) {
                            $savedDropId = $customFieldsOption->getId();
                            if($savedDropId == $newDropdown['id']) {
                                $customFieldsOption->setOptionValue($newDropdown['value']);
                                $productCustomFieldsOptionsDataMapper->save($customFieldsOption);

                                unset($productCustomFieldsOptionsData[$key]);
                            }
                        }
                    }

                    if(!empty($productCustomFieldsOptionsData)) {
                        foreach ($productCustomFieldsOptionsData as $customFieldsOption) {
                            $productCustomFieldsOptionsDataMapper->delete($customFieldsOption->getId());
                        }
                    }

                    foreach ($newDropdownParams as $newDropdown) {
                        if(empty($newDropdown['id'])) {
                            $productCustomFieldsOptionsDataModel = new Store_Model_ProductCustomFieldsOptionsDataModel();
                            $productCustomFieldsOptionsDataModel->setCustomParamId($customParamId);
                            $productCustomFieldsOptionsDataModel->setOptionValue($newDropdown['value']);

                            $productCustomFieldsOptionsDataMapper->save($productCustomFieldsOptionsDataModel);
                        }
                    }

                    return array('status' => 'ok', 'message' => $translator->translate('Options were successfully updated'));
                } else {
                    return array('status' => 'error', 'message' => $translator->translate('Custom param with such name already exists'));
                }
            }
            return array('status' => 'ok', 'message' => $translator->translate('Custom param has been updated'));
        }

    }

    /**
     * Delete product custom param config
     *
     * Resource:
     * : /api/productcustomfieldsconfig/
     *
     * HttpMethod:
     * : DELETE
     *
     * ## Parameters:
     * id (type integer)
     * : product custom param id to delete
     *
     * @return JSON
     */
    public function deleteAction()
    {
        $translator = Zend_Registry::get('Zend_Translate');
        $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);

        if (!$id) {
            return array('status' => 'error', 'message' => $translator->translate('error'));
        }

        $productCustomFieldsConfigMapper = Store_Mapper_ProductCustomFieldsConfigMapper::getInstance();
        $productCustomFieldsConfigModel = $productCustomFieldsConfigMapper->find($id);
        if ($productCustomFieldsConfigModel instanceof Store_Model_ProductCustomFieldsConfigModel) {
            $productCustomFieldsConfigMapper->delete($id);

            return array('status' => 'ok', 'message' => $translator->translate('Product custom field has been deleted'));
        } else {
            return array('status' => 'error', 'message' => $translator->translate('error'));
        }
    }

}
