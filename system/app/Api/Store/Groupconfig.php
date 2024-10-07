<?php

class Api_Store_Groupconfig extends Api_Service_Abstract
{


    /**
     * Secure token
     */
    const SHOPPING_SECURE_TOKEN = 'ShoppingToken';

    /**
     * System response helper
     *
     * @var null
     */
    protected $_responseHelper = null;

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
        ),
        Shopping::ROLE_SALESPERSON => array(
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
     * : /api/store/groupconfig/
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
        $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);
        $where = '';

        $groupMapper = Store_Mapper_GroupMapper::getInstance();

        if (!empty($id)) {
            $where = $groupMapper->getDbTable()->getAdapter()->quoteInto('sg.id = ?', $id);
            $data = $groupMapper->fetchAllData($where, null, null, null, true, true);
        } else {
            $order = 'sg.groupName';
            $data = $groupMapper->fetchAllData($where, $order, $limit, $offset, false, false);
            $groups = Store_Mapper_GroupMapper::getInstance()->fetchGroupList();
            $groupsList = array();
            if (!empty($groups)) {
                foreach ($groups as $key => $group) {
                    $groupsList[$key] = $group;
                }
            }

            $data['additionalInfo']['groupsList'] = $groupsList;
        }

        $data['additionalInfo']['currencyAbbr'] = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('currency');
        $data['additionalInfo']['defaultGroupId'] = intval(Models_Mapper_ShoppingConfig::getInstance()->getConfigParam(Shopping::DEFAULT_USER_GROUP));

        return $data;
    }

    /**
     *
     * Resource:
     * : /api/store/groupconfig/
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

        $secureToken = $this->getRequest()->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
        $tokenValid = Tools_System_Tools::validateToken($secureToken, self::SHOPPING_SECURE_TOKEN);
        if (!$tokenValid) {
            $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
            $websiteUrl = $websiteHelper->getUrl();
            $this->_error($translator->translate('Your session has timed-out. Please Log back in ' . '<a href="' . $websiteUrl . 'go">here</a>'));
        }

        $groupMapper = Store_Mapper_GroupMapper::getInstance();

        if (empty($data['groupName'])) {
            return array(
                'error' => '1',
                'message' => $translator->translate('Group Name Can\'t be empty')
            );
        }

        if (!is_numeric($data['priceValue'])) {
            return array(
                'error' => '1',
                'message' => $translator->translate('Price Value must be numeric')
            );
        }

        if (trim($data['priceValue']) == '') {
            return array(
                'error' => '1',
                'message' => $translator->translate('Price Value Can\'t be empty')
            );
        }

        $data['groupName'] = trim($data['groupName']);

        $groupModel = $groupMapper->findByGroupName($data['groupName']);
        if ($groupModel instanceof Store_Model_Group) {
            return array(
                'error' => '1',
                'message' => $translator->translate('Group already exists')
            );
        }

        if (empty($data['nonTaxable'])) {
            $data['nonTaxable'] = '0';
        }

        $storeModelGroup = new Store_Model_Group($data);
        $storeModelGroup->setGroupName($data['groupName']);
        $storeModelGroup->setNonTaxable($data['nonTaxable']);
        $storeModelGroup->setPriceSign($data['priceSign']);
        $storeModelGroup->setPriceType($data['priceType']);
        $storeModelGroup->setPriceValue($data['priceValue']);

        $groupMapper->save($storeModelGroup);

        $cache = Zend_Controller_Action_HelperBroker::getStaticHelper('Cache');
        $cache->clean('', '', array('0' => 'product_price'));
        $cache->clean('products_groups_price', 'store_');
        $cache->clean('customers_groups', 'store_');

        return array(
            'error' => '0',
            'message' => $translator->translate('Group has been added')
        );
    }

    /**
     *
     * Resource:
     * : /api/store/groupconfig/
     *
     * HttpMethod:
     * : PUT
     *
     * ## Parameters:
     * id (source integer)
     *
     * @return JSON
     */
    public function putAction()
    {
        $data = json_decode($this->_request->getRawBody(), true);
        if (!empty($data['id']) && !empty($data[Tools_System_Tools::CSRF_SECURE_TOKEN])) {
            $translator = Zend_Registry::get('Zend_Translate');
            $secureToken = $data[Tools_System_Tools::CSRF_SECURE_TOKEN];
            $tokenValid = Tools_System_Tools::validateToken($secureToken, self::SHOPPING_SECURE_TOKEN);
            if (!$tokenValid) {
                $websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
                $websiteUrl = $websiteHelper->getUrl();
                $this->_error($translator->translate('Your session has timed-out. Please Log back in ' . '<a href="' . $websiteUrl . 'go">here</a>'));
            }

            $groupMapper = Store_Mapper_GroupMapper::getInstance();

            $groupModel = $groupMapper->find($data['id']);
            if ($groupModel instanceof Store_Model_Group) {
                $data['groupName'] = filter_var($data['groupName'], FILTER_SANITIZE_STRING);
                if ($groupModel->getGroupName() !== $data['groupName']) {
                    $groupWithNameExists = $groupMapper->findByGroupName($data['groupName']);
                    if ($groupWithNameExists instanceof Store_Model_Group) {
                        return array(
                            'error' => '1',
                            'message' => $translator->translate('Group already exists')
                        );
                    }
                }

                if (empty($data['groupName'])) {
                    return array(
                        'error' => '1',
                        'message' => $translator->translate('Group Name Can\'t be empty')
                    );
                }

                if (!is_numeric($data['priceValue'])) {
                    return array(
                        'error' => '1',
                        'message' => $translator->translate('Price Value must be numeric')
                    );
                }

                if (trim($data['priceValue']) == '') {
                    return array(
                        'error' => '1',
                        'message' => $translator->translate('Price Value Can\'t be empty')
                    );
                }

                $groupModel->setGroupName($data['groupName']);
                $groupModel->setPriceValue($data['priceValue']);
                $groupModel->setPriceType($data['priceType']);
                $groupModel->setPriceSign($data['priceSign']);
                $groupModel->setNonTaxable($data['nonTaxable']);

                $groupMapper->save($groupModel);

                $cache = Zend_Controller_Action_HelperBroker::getStaticHelper('Cache');
                $cache->clean('', '', array('0' => 'product_price'));
                $cache->clean('products_groups_price', 'store_');
                $cache->clean('customers_groups', 'store_');

                return array(
                    'error' => '0',
                    'message' => $translator->translate('Group has been updated')
                );

            }
        }

        return array(
            'error' => '1',
            'message' => ''
        );

    }

    /**
     *
     * Resource:
     * : /api/store/groupconfig/
     *
     * HttpMethod:
     * : DELETE
     *
     * ## Parameters:
     * id (source integer)
     *
     * @return JSON
     */
    public function deleteAction()
    {
        $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);
        if (!$id) {
            $this->_error();
        }
        $translator = Zend_Registry::get('Zend_Translate');


        $groupMapper = Store_Mapper_GroupMapper::getInstance();
        $groupModel = $groupMapper->find($id);
        if ($groupModel instanceof Store_Model_Group) {
            Store_Mapper_GroupPriceMapper::getInstance()->deleteByGroupId($id);
            $customerInfoDbTable = new Models_DbTable_CustomerInfo();
            $groupId = null;
            $where = $customerInfoDbTable->getAdapter()->quoteInto('group_id = ?', $id);
            $customerInfoDbTable->update(array('group_id' => $groupId), $where);

            $shoppingConfigMapper = Models_Mapper_ShoppingConfig::getInstance();

            $defaultUserGroupId = intval($shoppingConfigMapper->getConfigParam(Shopping::DEFAULT_USER_GROUP));
            if (!empty($defaultUserGroupId) && $defaultUserGroupId == $id) {
                $shoppingConfigMapper->save(array(Shopping::DEFAULT_USER_GROUP => 0));
            }

            $cache = Zend_Controller_Action_HelperBroker::getStaticHelper('Cache');
            $cache->clean('', '', array('0' => 'product_price'));
            $cache->clean('products_groups_price', 'store_');
            $cache->clean('customers_groups', 'store_');
            $groupMapper->delete($id);

            return array(
                'error' => '0',
                'message' => $translator->translate('Group has been deleted')
            );
        } else {
            $this->_error();
        }

    }

}
