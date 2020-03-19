<?php

/**
 * Class Api_Store_Productcustomfieldsconfig
 */
class Api_Store_Productcustomfieldsconfig extends Api_Service_Abstract
{

    /**
     * Product custom fields secure token
     */
    const PRODUCT_CUSTOM_FIELDS_CONFIG_TOKEN = 'ProductcustomfieldsconfigToken';

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
        /*$dropdownOptionValues = array();
        $dropdownOptionIds = array();

        if(!empty($data['custom_param_name'])){
            $dropdownOptionValues = array_unique(explode(',' , $data['custom_param_name']));

            if(!empty($dropdownOptionValues)) {
                foreach ($dropdownOptionValues as $value) {
                    $notValid = Tools_LeadTools::customParamValidate($value);

                    if($notValid) {
                        return array('status' => 'error', 'message' => $translator->translate('Invalid param name. You can use only alphabet and digits. You can also use "-".'));

                    }
                }
            }
        }

        if(!empty($data['custom_param_options_ids'])){
            $dropdownOptionIds = array_unique(explode(',' , $data['custom_param_options_ids']));
        }*/

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
        if(!empty($data['custom_param_id'])){
            /*$leadCustomParamConfigModel = $leadCustomParamConfigMapper->findById($data['custom_param_id']);
            if (!$leadCustomParamConfigModel instanceof Leads_Model_LeadsCustomParamsConfigModel) {
                return array('status' => 'error', 'message' => $translator->translate('Custom param with such name already exists'));
            }*/
        }else{
            $productCustomFieldsConfigModel = $productCustomFieldsConfigMapper->getByName($data['param_name']);
            if ($productCustomFieldsConfigModel instanceof Store_Model_ProductCustomFieldsConfigModel) {
                return array('status' => 'error', 'message' => $translator->translate('Custom param with such name already exists'));
            }

            $productCustomFieldsConfigModel = new Store_Model_ProductCustomFieldsConfigModel();
        }

        $productCustomFieldsConfigModel->setOptions($data);
        $productCustomFieldsConfigMapper->save($productCustomFieldsConfigModel);

        if ($productCustomFieldsConfigModel instanceof Leads_Model_LeadsCustomParamsConfigModel) {
            /*$productCustomFieldsOptionsDataMapper = Store_Mapper_ProductCustomFieldsOptionsDataMapper::getInstance();

            $leadCustomParamId = $productCustomFieldsConfigModel->getId();

            if ($data['param_type'] == Tools_LeadImportTools::ATTRIBUTE_TYPE_CHECKBOX) {
                $defCheckboxValues = array();

                $notValid = Tools_LeadTools::customParamValidate($data['checkbox_yes']);

                if($notValid) {
                    return array('status' => 'error', 'message' => $translator->translate('Invalid param name. You can use only alphabet and digits. You can also use "-".'));

                }

                array_push($defCheckboxValues, $data['checkbox_yes']);

                if (empty($data['checkbox_yes'])) {
                    $defCheckboxValues[0] = 'yes';
                }

                $data = $leadCustomParamOptionsDataMapper->findByCustomParamId($leadCustomParamId);

                Tools_LeadTools::processCustomParamsOptions($data, $defCheckboxValues, Leads::LEADS_TYPE, $leadCustomParamId);
            } elseif ($data['param_type'] == Tools_LeadImportTools::ATTRIBUTE_TYPE_SELECT || $data['param_type'] == Tools_LeadImportTools::ATTRIBUTE_TYPE_RADIO) {
                $data = $leadCustomParamOptionsDataMapper->findByCustomParamId($leadCustomParamId);

                foreach ($data as $key => $customParam) {
                    $customParamId = $customParam->getId();
                    if(in_array($customParamId, $dropdownOptionIds)) {
                        $optionValue = $dropdownOptionValues[$key];
                        $data[$key]->setOptionValue($optionValue);
                    }
                }

                Tools_LeadTools::processCustomParamsOptions($data, $dropdownOptionValues, Leads::LEADS_TYPE, $leadCustomParamId);
            }*/
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
        $customDropdownSelectionFlag = $this->_request->getParam('customDropdownSelectionFlag');
        $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);

        if (!$id) {
            return array('status' => 'error', 'message' => $translator->translate('error'));
        }

        if(!empty($customDropdownSelectionFlag)){
            /*$productCustomFieldsOptionsDataMapper = Store_Mapper_ProductCustomFieldsOptionsDataMapper::getInstance();
            $productCustomFieldsOptionsDataModel = $productCustomFieldsOptionsDataMapper->find($id);
            if ($productCustomFieldsOptionsDataModel instanceof Store_Model_ProductCustomFieldsOptionsDataModel) {
                $productCustomFieldsOptionsDataModel->delete($id);

                return array('status' => 'ok', 'message' => $translator->translate('Product custom option field has been deleted'));
            } else {
                return array('status' => 'error', 'message' => $translator->translate('error'));
            }*/
        }else{
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

}
