<?php

/**
 * Class Api_Store_ProductCustomFields
 */
class Api_Store_ProductCustomFields extends Api_Service_Abstract
{


    /**
     * Lead secure token
     */
    const PRODUCT_CUSTOM_FIELDS_TOKEN = 'ProductCustomFieldsToken';

    /**
     * Mandatory fields
     *
     * @var array
     */
    protected $_mandatoryParams = array();

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
     * : /api/productcustomfields/
     *
     * HttpMethod:
     * : GET
     *
     * @return JSON
     */
    public function getAction()
    {
        $limit = filter_var($this->_request->getParam('limit', null), FILTER_SANITIZE_NUMBER_INT);
        $offset = filter_var($this->_request->getParam('offset', null), FILTER_SANITIZE_NUMBER_INT);
        $order = filter_var($this->_request->getParam('order', 'scrgc.created_at DESC'), FILTER_SANITIZE_STRING);
        $id = filter_var($this->_request->getParam('id', null), FILTER_SANITIZE_NUMBER_INT);

        $where = null;
        $withoutCount = false;
        $singleRecord = false;
        $customerRulesGeneralConfigMapper = Store_Mapper_ProductCustomFieldsConfigMapper::getInstance();
        if (!empty($id)) {
            $where = $customerRulesGeneralConfigMapper->getDbTable()->getAdapter()->quoteInto('scrgc.id = ?', $id);
            $withoutCount = true;
            $singleRecord = true;
        }

        $rulesData = $customerRulesGeneralConfigMapper->fetchAll($where, $order, $limit, $offset, $withoutCount,
            $singleRecord);

        if (!empty($id)) {
            $customerRulesActionsMapper = Store_Mapper_CustomerRulesActionsMapper::getInstance();
            $actionsData = $customerRulesActionsMapper->getActionByRuleIds(array($id));
            $rulesData['actionsData'] = $actionsData;
            $customerRulesConfigMapper = Store_Mapper_CustomerRulesConfigMapper::getInstance();
            $fieldsData = $customerRulesConfigMapper->getFieldsByRuleId($id);
            $rulesData['fieldsData'] = $fieldsData;

        }

        $userMapper = Application_Model_Mappers_UserMapper::getInstance();
        $customParams = $userMapper->fetchUniqueAttributesNames();
        if (!empty($customParams)) {
            $customParams = array_combine($customParams, $customParams);
        }


        $customerGroupsMapper = Store_Mapper_GroupMapper::getInstance();
        $customerGroups = $customerGroupsMapper->fetchPairs();

        $rulesData['configData']['customParams'] = $customParams;
        $rulesData['configData']['customerGroups'] = $customerGroups;

        return array('rulesData' => $rulesData, 'status' => 'ok');
    }

    /**
     *
     * * * Resource:
     * : /api/productcustomfields/
     *
     * @return array
     */
    public function postAction()
    {
        /*$secureToken = $this->_request->getParam('secureToken', false);
        $tokenValid = Tools_System_Tools::validateToken($secureToken, self::PRODUCT_CUSTOM_FIELDS_TOKEN);
        if ($tokenValid !== true) {
            $this->_error(array('status' => 'error', 'message' => 'Access denied', 'errorCode' => '1'), 200);
        }

        $data = filter_var_array($this->getRequest()->getParams(), FILTER_SANITIZE_STRING);
        $translator = Zend_Registry::get('Zend_Translate');

        $fieldDataMissing = array_filter($this->_mandatoryParams, function ($param) use ($data) {
            if (!array_key_exists($param, $data) || empty($data[$param])) {
                return $param;
            }
        });

        if (!empty($fieldDataMissing)) {
            $this->_error(array(
                'status' => 'error',
                'message' => $translator->translate('Missing mandatory params'),
                'errorCode' => '1'
            ), 200);
        }

        if (empty($data['fieldsData']) || empty($data['actionsData'])) {
            $this->_error(array(
                'status' => 'error',
                'message' => $translator->translate('Missing fields and actions data'),
                'errorCode' => '1'
            ), 200);
        }

        $customerRulesGeneralConfigMapper = Store_Mapper_CustomerRulesGeneralConfigMapper::getInstance();
        $customerRulesConfigMapper = Store_Mapper_CustomerRulesConfigMapper::getInstance();
        $customerRulesActionsMapper = Store_Mapper_CustomerRulesActionsMapper::getInstance();

        $ruleName = $data['rule_name'];

        $ruleModel = $customerRulesGeneralConfigMapper->checkRuleExist($ruleName);
        if ($ruleModel instanceof Store_Model_CustomerRulesGeneralConfigModel) {
            $this->_error(array(
                'status' => 'error',
                'message' => $translator->translate('Rule name already exists'),
                'errorCode' => '1'
            ), 200);
        }

        $ruleModel = new Store_Model_CustomerRulesGeneralConfigModel();

        $createdAt = Tools_System_Tools::convertDateFromTimezone('now');
        if (empty($data['user_id'])) {
            $currentUserId = Zend_Controller_Action_HelperBroker::getStaticHelper('session')->getCurrentUser()->getId();
        } else {
            $currentUserId = $data['user_id'];
        }

        $ruleModel->setOptions($data);
        $ruleModel->setCreatorId($currentUserId);
        $ruleModel->setCreatedAt($createdAt);

        $ruleModel = $customerRulesGeneralConfigMapper->save($ruleModel);
        $ruleId = $ruleModel->getId();

        if (!empty($data['fieldsData']) && is_array($data['fieldsData'])) {
            foreach ($data['fieldsData'] as $fieldData) {
                if (empty($fieldData['name'])) {
                    continue;
                }
                $customerRulesConfigModel = new Store_Model_CustomerRulesConfigModel();
                $customerRulesConfigModel->setFieldName($fieldData['name']);
                if (is_array($fieldData['value'])) {
                    $fieldValue = implode(',', array_map('trim', $fieldData['value']));
                } else {
                    $fieldValue = implode(',', array_map('trim', explode(',', $fieldData['value'])));
                }
                $customerRulesConfigModel->setFieldValue($fieldValue);
                $customerRulesConfigModel->setRuleId($ruleId);
                if (!empty($fieldData['operator'])) {
                    $customerRulesConfigModel->setRuleComparisonOperator($fieldData['operator']);
                }
                $customerRulesConfigMapper->save($customerRulesConfigModel);
            }
        }

        if (!empty($data['actionsData']) && is_array($data['actionsData'])) {
            foreach ($data['actionsData'] as $actionData) {
                $customerRulesActionModel = new Store_Model_CustomerRulesActionModel();
                $customerRulesActionModel->setRuleId($ruleId);
                $customerRulesActionModel->setActionType($actionData['actionType']);
                unset($actionData['actionType']);
                $customerRulesActionModel->setActionConfig(json_encode($actionData));
                $customerRulesActionsMapper->save($customerRulesActionModel);
            }

            return array('status' => 'ok', 'message' => $translator->translate('Rule has been added'));
        }

        $this->_error(array('status' => 'error', 'message' => '', 'errorCode' => '1'), 200);*/
    }

    /**
     *
     * * * Resource:
     * : /api/productcustomfields/
     *
     *
     */
    public function putAction()
    {
        /*$data = json_decode($this->_request->getRawBody(), true);
        $translator = Zend_Registry::get('Zend_Translate');

        $tokenValid = Tools_System_Tools::validateToken($data['secureToken'], self::PRODUCT_CUSTOM_FIELDS_TOKEN);
        if ($tokenValid !== true) {
            $this->_error(array(
                'status' => 'error',
                'message' => $translator->translate('Access denied'),
                'errorCode' => '1'
            ), 200);
        }

        $id = filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT);


        if (!empty($id)) {
            if (empty($data['rule_name'])) {
                $this->_error(array('status' => 'error', 'message' => 'Rule name is missing', 'errorCode' => '1'), 200);
            }

            $customerRulesGeneralConfigMapper = Store_Mapper_CustomerRulesGeneralConfigMapper::getInstance();
            $customerRulesConfigMapper = Store_Mapper_CustomerRulesConfigMapper::getInstance();
            $customerRulesActionsMapper = Store_Mapper_CustomerRulesActionsMapper::getInstance();
            $customerRulesGeneralConfigModel = $customerRulesGeneralConfigMapper->find($id);
            if (!$customerRulesGeneralConfigModel instanceof Store_Model_CustomerRulesGeneralConfigModel) {
                $this->_error(array('status' => 'error', 'message' => 'Rule not found', 'errorCode' => '1'), 200);
            }

            if ($customerRulesGeneralConfigModel->getRuleName() != $data['rule_name']) {
                $anotherRuleWithSameName = $customerRulesGeneralConfigMapper->checkRuleExist($data['rule_name']);
                if ($anotherRuleWithSameName instanceof Store_Model_CustomerRulesGeneralConfigModel) {
                    $this->_error(array(
                        'status' => 'error',
                        'message' => $translator->translate('Another rule has the same name'),
                        'errorCode' => '1'
                    ), 200);
                }
            }

            if (empty($data['user_id'])) {
                $currentUserId = Zend_Controller_Action_HelperBroker::getStaticHelper('session')->getCurrentUser()->getId();
            } else {
                $currentUserId = $data['user_id'];
            }

            $customerRulesGeneralConfigModel->setEditorId($currentUserId);
            $customerRulesGeneralConfigModel->setUpdatedAt(Tools_System_Tools::convertDateFromTimezone('now'));
            $customerRulesGeneralConfigModel->setRuleName($data['rule_name']);
            $customerRulesGeneralConfigMapper->save($customerRulesGeneralConfigModel);


            $customerRulesConfigMapper->deleteByRuleId($id);
            $customerRulesActionsMapper->deleteByRuleId($id);

            if (!empty($data['fieldsData']) && is_array($data['fieldsData'])) {
                foreach ($data['fieldsData'] as $fieldData) {
                    if (empty($fieldData['name'])) {
                        continue;
                    }
                    $customerRulesConfigModel = new Store_Model_CustomerRulesConfigModel();
                    $customerRulesConfigModel->setFieldName($fieldData['name']);
                    if (is_array($fieldData['value'])) {
                        $fieldValue = implode(',', array_map('trim', $fieldData['value']));
                    } else {
                        $fieldValue = implode(',', array_map('trim', explode(',', $fieldData['value'])));
                    }
                    $customerRulesConfigModel->setFieldValue($fieldValue);
                    $customerRulesConfigModel->setRuleId($id);
                    if (!empty($fieldData['operator'])) {
                        $customerRulesConfigModel->setRuleComparisonOperator($fieldData['operator']);
                    }
                    $customerRulesConfigMapper->save($customerRulesConfigModel);
                }
            }

            if (!empty($data['actionsData']) && is_array($data['actionsData'])) {
                foreach ($data['actionsData'] as $actionData) {
                    $customerRulesActionModel = new Store_Model_CustomerRulesActionModel();
                    $customerRulesActionModel->setRuleId($id);
                    $customerRulesActionModel->setActionType($actionData['actionType']);
                    unset($actionData['actionType']);
                    $customerRulesActionModel->setActionConfig(json_encode($actionData));
                    $customerRulesActionsMapper->save($customerRulesActionModel);
                }
            }

            return array('error' => '0', 'message' => $translator->translate('Rule has been updated'));

        }

        $this->_error(array('status' => 'error', 'message' => 'Missing rule id', 'errorCode' => '1'), 200);*/

    }

    /**
     * Delete record
     *
     * * Resource:
     * : /api/productcustomfields/
     *
     * @return array
     */
    public function deleteAction()
    {
        /*$secureToken = $this->_request->getParam('secureToken', false);
        $tokenValid = Tools_System_Tools::validateToken($secureToken, self::PRODUCT_CUSTOM_FIELDS_TOKEN);
        $translator = Zend_Registry::get('Zend_Translate');

        if ($tokenValid !== true) {
            $this->_error(array(
                'status' => 'error',
                'message' => $translator->translate('Access denied'),
                'errorCode' => '1'
            ), 200);
        }

        $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);

        $customerRulesGeneralConfigMapper = Store_Mapper_CustomerRulesGeneralConfigMapper::getInstance();
        $customerRulesGeneralConfigModel = $customerRulesGeneralConfigMapper->find($id);
        if (!$customerRulesGeneralConfigModel instanceof Store_Model_CustomerRulesGeneralConfigModel) {
            $this->_error(array('status' => 'error', 'message' => 'Can\'t delete record', 'errorCode' => '1'), 200);
        }
        $customerRulesGeneralConfigMapper->delete($id);

        return array('status' => 'ok', 'message' => $translator->translate('Rule has been deleted'));*/
    }

}
