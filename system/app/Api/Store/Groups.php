<?php
/**
 * Groups REST API controller
 *
 *
 * @package Store
 * @since   2.0.0
 */
class Api_Store_Groups extends Api_Service_Abstract {


    const GROUPS_SECURE_TOKEN = 'GroupsToken';

	/**
	 * @var array Access Control List
	 */
	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post', 'put', 'delete')
		),
		Tools_Security_Acl::ROLE_ADMIN      => array(
			'allow' => array('get', 'post', 'put', 'delete')
		),
		Shopping::ROLE_SALESPERSON          => array(
			'allow' => array('get', 'post', 'put', 'delete')
		)
	);

    /**
     * Get groups data
     *
     * Resourse:
     * : /api/store/groups/id/:id
     *
     * HttpMethod:
     * : GET
     *
     * ## Parameters:
     * groupId (type string)
     * : Group Id
     *
     * pairs (type sting)
     * : If given data will be returned as key-value array
     *
     * @return JSON List of groups
     */
	public function getAction() {
		$groupId = filter_var($this->_request->getParam('groupId'), FILTER_SANITIZE_STRING);
		if ($groupId) {
            $storeMapper = Store_Mapper_GroupMapper::getInstance();
            $where = $storeMapper->getDbTable()->getAdapter()->quoteInto('id=?', $groupId);
            $data = $storeMapper->fetchAll($where);
		} else {
			$data = Store_Mapper_GroupMapper::getInstance()->fetchAll();
		}
        return array_map(function ($group) {
				return $group->toArray();
		}, $data);

	}

    /**
     * New group creation
     *
     * Resourse:
     * : /api/store/groups/
     *
     * HttpMethod:
     * : POST
     *
     * @return JSON New group model
     */
	public function postAction() {
		$data = filter_var_array($this->getRequest()->getPost(), FILTER_SANITIZE_STRING);
        $cache = Zend_Controller_Action_HelperBroker::getStaticHelper('Cache');

        $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
        $valid = Tools_System_Tools::validateToken($tokenToValidate, self::GROUPS_SECURE_TOKEN);
        if (!$valid) {
            exit;
        }
        unset($data[Tools_System_Tools::CSRF_SECURE_TOKEN]);

		if (!isset($data['groupName']) && !isset($data['groupPriceValue'])) {
			$this->_error();
		}
        $translator = Zend_Registry::get('Zend_Translate');
        if(trim($data['groupName']) == ''){
            $this->_error($translator->translate('Group Name Can\'t be empty'));
        }

        if(!is_numeric($data['priceValue'])){
            $this->_error($translator->translate('Price Value must be numeric'));
        }

        if(trim($data['priceValue']) == ''){
            $this->_error($translator->translate('Price Value Can\'t be empty'));
        }

        $data['groupName'] = trim($data['groupName']);

		$model = new Store_Model_Group($data);
		if (is_array($data) && isset($data['groupName'])) {
			foreach ($data as $key => $value) {
				$model->{'set' . ucfirst($key)}($value);
			}
		}

		Store_Mapper_GroupMapper::getInstance()->save($model);

        $cache->clean('', '', array('0'=>'product_price'));
        $cache->clean('products_groups_price', 'store_');
        $cache->clean('customers_groups', 'store_');
		return $model->toArray();
	}

	public function putAction() {


    }

    /**
     * Delete group
     *
     * Resourse:
     * : /api/store/groups/
     *
     * HttpMethod:
     * : DELETE
     *
     * ## Parameters:
     * id (type integer)
     * : group ID to delete
     *
     * @return JSON Result of operations
     */
	public function deleteAction() {
		$id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);
        $cache = Zend_Controller_Action_HelperBroker::getStaticHelper('Cache');

		if (!$id) {
			$this->_error();
		}

        Store_Mapper_GroupPriceMapper::getInstance()->deleteByGroupId($id);
        $customerInfoDbTable = new Models_DbTable_CustomerInfo();
        $groupId = null;
        $where = $customerInfoDbTable->getAdapter()->quoteInto('group_id = ?', $id);
        $customerInfoDbTable->update(array('group_id'=>$groupId), $where);
        $cache->clean('', '', array('0'=>'product_price'));
        $cache->clean('products_groups_price', 'store_');
        $cache->clean('customers_groups', 'store_');
		return Store_Mapper_GroupMapper::getInstance()->delete($id);
	}


}
