<?php
/**
 * Customers REST API controller
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 *
 * @package Store
 * @since 2.0.0
 */
class Api_Store_Customers extends Api_Service_Abstract {

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

	/**
	 * Get customers data
	 *
	 * Resourse:
	 * : /api/store/customers/id/:id
	 *
	 * HttpMethod:
	 * : GET
	 *
	 * ## Parameters:
     * type (type string)
     * : Type of data. Possible values: country, state
	 *
	 * pairs (type sting)
	 * : If given data will be returned as key-value array
	 *
	 * @return JSON List of customers
	 */
	public function getAction() {
		$customerMapper = Models_Mapper_CustomerMapper::getInstance();
		$id = filter_var($this->_request->getParam('id'), FILTER_VALIDATE_INT);
		$for = filter_var($this->_request->getParam('for'), FILTER_SANITIZE_STRING);

		if ($for === 'dashboard'){
			$order = filter_var($this->_request->getParam('order'), FILTER_SANITIZE_STRING);
			$limit = filter_var($this->_request->getParam('limit'), FILTER_SANITIZE_NUMBER_INT);
			$offset = filter_var($this->_request->getParam('offset'), FILTER_SANITIZE_NUMBER_INT);
			$search = filter_var($this->_request->getParam('search'), FILTER_SANITIZE_SPECIAL_CHARS);

			$currency = Zend_Registry::get('Zend_Currency');
			$data = array_map(function($row) use ($currency){
				$row['reg_date'] = date('d M, Y', strtotime($row['reg_date']));
				$row['total_amount'] = $currency->toCurrency($row['total_amount']);
				return $row;
			},
			$customerMapper->listAll($id ? array('id = ?'=>$id) : null, $order, $limit, $offset, $search));
		} else {
			if ($id) {
				$result = $customerMapper->find($id);
				if ($result) {
					$data = $result->toArray();
				}
			} else {
				$result = $customerMapper->fetchAll();
				if ($result){
					$data = array_map(function($model){ return $model->toArray(); }, $result);
				}
			}
		}
		return $data;
	}

	/**
	 * Attaching user group for customers if groupId exist
     *
	 */
	public function postAction() {
        $userId = filter_var($this->_request->getParam('userId'), FILTER_VALIDATE_INT);
        $groupId = filter_var($this->_request->getParam('groupId'), FILTER_VALIDATE_INT);
        $cache = Zend_Controller_Action_HelperBroker::getStaticHelper('Cache');

        if(!isset($userId)){
            $this->_error();
        }

        if(isset($groupId)){
            $customerInfoDbTable = new Models_DbTable_CustomerInfo();
            if($groupId == 0){
                $groupId = null;
            }
            $where = $customerInfoDbTable->getAdapter()->quoteInto('user_id = ?', $userId);
            $customerInfoDbTable->update(array('group_id'=>$groupId), $where);
            $cache->clean('', '', array('0'=>'product_price'));
            $cache->clean('products_groups_price', 'store_');
            $cache->clean('customers_groups', 'store_');
        }

	}

	/**
	 * Reserved for future usage
	 */
	public function putAction() {
        $groupId     = filter_var($this->_request->getParam('groupId'), FILTER_SANITIZE_NUMBER_INT);
        $customerIds = filter_var($this->_request->getParam('customerIds'), FILTER_SANITIZE_STRING);
        $allGroups = filter_var($this->_request->getParam('allGroups'), FILTER_SANITIZE_NUMBER_INT);
        $cache = Zend_Controller_Action_HelperBroker::getStaticHelper('Cache');
        $customersIdsArray = explode(',', $customerIds);
        if(!isset($groupId) || !is_array($customersIdsArray)){
            $this->_error();
        }
        $customerInfoDbTable = new Models_DbTable_CustomerInfo();

        if($allGroups == 1){
            $updateField = $customerInfoDbTable->getAdapter()->quoteInto('group_id =?', $groupId);
            $customerInfoDbTable->getAdapter()->query('UPDATE `shopping_customer_info` SET '.$updateField);
        }else{
            $where = $customerInfoDbTable->getAdapter()->quoteInto('user_id IN (?)', $customersIdsArray);
            $customerInfoDbTable->update(array('group_id'=>$groupId), $where);
        }

        $cache->clean('products_groups_price', 'store_');
        $cache->clean('customers_groups', 'store_');
	}

	/**
	 * Delete customer
	 *
	 * Resourse:
	 * : /api/store/customers/
	 *
	 * HttpMethod:
	 * : DELETE
	 *
	 * ## Parameters:
     * ids (type string)
     * : List of customer IDs to delete
	 *
	 * @return JSON Result of operations
	 */
	public function deleteAction() {
		$customerMapper = Models_Mapper_CustomerMapper::getInstance();
		$rawBody = Zend_Json::decode($this->_request->getRawBody());
		if (isset($rawBody['ids'])){
			$ids = filter_var_array($rawBody['ids'], FILTER_SANITIZE_NUMBER_INT);
			if (!empty($ids)){
				$customers = $customerMapper->fetchAll(array('id IN (?)' => $ids, 'role_id <> ?' => Tools_Security_Acl::ROLE_SUPERADMIN));
				if ( !empty($customers) ) {
					foreach ($customers as $user) {
						$data[$user->getId()] = (bool)Application_Model_Mappers_UserMapper::getInstance()->delete($user);
					}
                    return $data;
				} else {
					$this->_error(null, self::REST_STATUS_NOT_FOUND);
				}
			}
		}
	}


}
