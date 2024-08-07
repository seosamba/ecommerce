<?php
/**
 * Customers REST API controller
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 *
 * @package Store
 * @since 2.0.0
 */
class Api_Store_Customers extends Api_Service_Abstract {


    const CUSTOMERS_SECURE_TOKEN = 'CustomersToken';

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
		$withCounter = filter_var($this->_request->getParam('withcounter'), FILTER_SANITIZE_STRING);

		if ($for === 'dashboard'){
			$order = filter_var($this->_request->getParam('order'), FILTER_SANITIZE_STRING);
			$limit = filter_var($this->_request->getParam('limit'), FILTER_SANITIZE_NUMBER_INT);
			$offset = filter_var($this->_request->getParam('offset'), FILTER_SANITIZE_NUMBER_INT);
			$search = filter_var($this->_request->getParam('search'), FILTER_SANITIZE_SPECIAL_CHARS);

            $roleId = filter_var($this->_request->getParam('roleId'), FILTER_SANITIZE_STRING);
            $clientsFilter = filter_var($this->_request->getParam('clientsFilter'), FILTER_SANITIZE_STRING);
			$currency = Zend_Registry::get('Zend_Currency');
            $where = null;
            if (!empty($id)) {
                $where = $customerMapper->getDbTable()->getAdapter()->quoteInto('user.id = ?', $id);
            }
            if (!empty($roleId)) {
                if (!empty($where)) {
                    $where .= ' AND '. $customerMapper->getDbTable()->getAdapter()->quoteInto('role_id = ?', $roleId);
                } else {
                    $where = $customerMapper->getDbTable()->getAdapter()->quoteInto('role_id = ?', $roleId);
                }
            }

            $listMasksMapper = Application_Model_Mappers_MasksListMapper::getInstance();
            $mobileMasks = $listMasksMapper->getListOfMasksByType(Application_Model_Models_MaskList::MASK_TYPE_MOBILE);
            $desktopMasks = $listMasksMapper->getListOfMasksByType(Application_Model_Models_MaskList::MASK_TYPE_DESKTOP);

            $allClientsCount = 0;
            $allAccountsCount = 0;

            if (!empty($withCounter)) {
                $result = $customerMapper->clientsFilterData($where, $order, $limit, $offset, false, false, $search, $clientsFilter);
                if (!empty($result['data'])) {
                    foreach ($result['data'] as $key => $resData) {
                        $result['data'][$key]['reg_date'] = date('d M, Y', strtotime($resData['reg_date']));
                        $result['data'][$key]['total_amount'] = $currency->toCurrency($resData['total_amount']);
                        $result['data'][$key]['mobileMasks'] = $mobileMasks;
                        $result['data'][$key]['desktopMasks'] = $desktopMasks;
                        if (!empty($resData['customer_attr'])) {
                            $attributes = explode(',', $resData['customer_attr']);
                            foreach ($attributes as $attribute) {
                                $attribute = explode('||', $attribute);
                                $result['data'][$key][preg_replace('`customer_`', '', $attribute[0])] = $attribute[1];
                            }
                        }
                    }

                    if (!empty($clientsFilter) && $clientsFilter === 'clients-only') {
                        if (!empty($result['totalRecords'])) {
                            $allClientsCount = $result['totalRecords'];
                        }

                        $resultAll = $customerMapper->clientsFilterData($where, $order, $limit, $offset, false, false, $search);
                        if (!empty($resultAll['totalRecords'])) {
                            $allAccountsCount = $resultAll['totalRecords'];
                        }

                    } else {
                        if (!empty($result['totalRecords'])) {
                            $allAccountsCount = $result['totalRecords'];
                        }
                        $clientsFilter = 'clients-only';
                        $resultAll = $customerMapper->clientsFilterData($where, $order, $limit, $offset, false, false, $search, $clientsFilter);
                        if (!empty($resultAll['totalRecords'])) {
                            $allClientsCount = $resultAll['totalRecords'];
                        }
                    }

                }

                $data = $result;
                $data['allClientsCount'] = $allClientsCount;
                $data['allAccountsCount'] = $allAccountsCount;
            } else {
                $data = array_map(function ($row) use ($currency, $mobileMasks, $desktopMasks) {
                    $row['reg_date'] = date('d M, Y', strtotime($row['reg_date']));
                    $row['total_amount'] = $currency->toCurrency($row['total_amount']);
                    $row['mobileMasks'] = $mobileMasks;
                    $row['desktopMasks'] = $desktopMasks;
                    if (!empty($row['customer_attr'])) {
                        $attributes = explode(',', $row['customer_attr']);
                        unset($row['customer_attr']);
                        foreach ($attributes as $attribute) {
                            $attribute = explode('||', $attribute);
                            $row[preg_replace('`customer_`', '', $attribute[0])] = $attribute[1];

                        }
                    } else {
                        unset($row['customer_attr']);
                    }
                    return $row;
                },
                    $customerMapper->listAll($where, $order, $limit, $offset, $search, $clientsFilter));
            }
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

        $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
        $valid = Tools_System_Tools::validateToken($tokenToValidate, self::CUSTOMERS_SECURE_TOKEN);
        if (!$valid) {
            exit;
        }

        if(isset($groupId)){

            $dataGroup = array('userId' => $userId, 'groupId' => $groupId);
            Tools_System_Tools::firePluginMethodByTagName('assigngroup', 'assignLeadGroup', $dataGroup, true);

            $customerInfoDbTable = new Models_DbTable_CustomerInfo();
            if($groupId == 0){
                $groupId = null;
            }
            $where = $customerInfoDbTable->getAdapter()->quoteInto('user_id = ?', $userId);
            $existingCustomerInfo = $customerInfoDbTable->find($userId)->current();
            if($existingCustomerInfo !== null){
                $customerInfoDbTable->update(array('group_id'=>$groupId), $where);
            }else{
                $data['user_id']  = $userId;
                $data['group_id'] = $groupId;
                $customerInfoDbTable->insert($data);
            }

            $cache->clean('', '', array('0'=>'product_price'));
            $cache->clean('products_groups_price', 'store_');
            $cache->clean('customers_groups', 'store_');
        }

	}

    /**
     * Assign groups for customer
     * Changing customer passwords
     *
     * Resourse:
     * : /api/store/customers/
     *
     * HttpMethod:
     * : PUT
     *
     * ## Parameters:
     * customerIds (type string)
     * : List of customer IDs
     *
     * groupId (type integer)
     * : group Id
     *
     * allGroups (type integer)
     * : all Groups
     *
     * changePassword (type integer)
     * : change Password flag
     *
     * @return JSON Result of operations
     */
	public function putAction() {
        $groupId        = filter_var($this->_request->getParam('groupId'), FILTER_SANITIZE_NUMBER_INT);
        $customerIds    = filter_var($this->_request->getParam('customerIds'), FILTER_SANITIZE_STRING);
        $allGroups      = filter_var($this->_request->getParam('allGroups'), FILTER_SANITIZE_NUMBER_INT);
        $changePassword = filter_var($this->_request->getParam('changePassword'), FILTER_SANITIZE_NUMBER_INT);

        $cache = Zend_Controller_Action_HelperBroker::getStaticHelper('Cache');
        $customersIdsArray = explode(',', $customerIds);
        if(!is_array($customersIdsArray)){
            $this->_error();
        }

        if($changePassword == 1){
            $userMapper = Application_Model_Mappers_UserMapper::getInstance();
            $where = $userMapper->getDbTable()->getAdapter()->quoteInto('id IN (?)', $customersIdsArray);
            $users = $userMapper->fetchAll($where);
            if(!empty($users)){
                foreach($users as $user){
                    $resetToken = Tools_System_Tools::saveResetToken($user->getEmail(),$user->getId(), '+1 day');
					$resetToken->registerObserver(new Tools_Mail_Watchdog(array(
						'trigger' => Tools_Mail_SystemMailWatchdog::TRIGGER_PASSWORDRESET
					)));
					$resetToken->notifyObservers();
				}
            }
        }


        if($groupId != ''){
            $customerInfoDbTable = new Models_DbTable_CustomerInfo();
            if($allGroups == 1){
                $updateField = $customerInfoDbTable->getAdapter()->quoteInto('group_id =?', $groupId);
                $customerInfoDbTable->getAdapter()->query('UPDATE `shopping_customer_info` SET '.$updateField);
            }else{
                $customerMapper = Models_Mapper_CustomerMapper::getInstance();
                $customersForMassGroupAssignment = $customerMapper->getCustomersForMassGroupAssignment($customersIdsArray);
                $customersInfoToInsert = array_diff($customersIdsArray, array_keys($customersForMassGroupAssignment));
                if (!empty($customersInfoToInsert)) {
                    foreach ($customersInfoToInsert as $customerInfo) {
                        $customerInfoDbTable->insert(
                            array(
                                'user_id'   => $customerInfo,
                            )
                        );
                    }
                }

                foreach ($customersIdsArray as $customerId) {
                    $dataGroup = array('userId' => $customerId, 'groupId' => $groupId);
                    Tools_System_Tools::firePluginMethodByTagName('assigngroup', 'assignLeadGroup', $dataGroup, true);
                }

                $where = $customerInfoDbTable->getAdapter()->quoteInto('user_id IN (?)', $customersIdsArray);
                $customerInfoDbTable->update(array('group_id'=>$groupId), $where);
            }

            $cache->clean('products_groups_price', 'store_');
            $cache->clean('customers_groups', 'store_');
        }
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
