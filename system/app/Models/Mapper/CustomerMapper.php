<?php
/**
 * Eugene I. Nezhuta <eugene@seotoaster.com>
 *
 */
class Models_Mapper_CustomerMapper extends Application_Model_Mappers_Abstract {

	protected $_dbTable = 'Models_DbTable_CustomerInfo';

	protected $_model   = 'Models_Model_Customer';

	/**
	 * @param $customer Models_Model_Customer
	 * @return mixed
	 */
	public function save($customer) {
		//save user data
		$userMapper = Application_Model_Mappers_UserMapper::getInstance();

		if (!$customer->getId()){
			$userId = $userMapper->save($customer);
			$customer->setId($userId);
		}

		$this->_processAddresses($customer);
		//save customer info
		$data = array(
			'user_id'               => $customer->getId(),
			'default_shipping_address_id'   => $customer->getDefaultShippingAddressId(),
			'default_billing_address_id'    => $customer->getDefaultBillingAddressId(),
            'group_id'                      => $customer->getGroupId()
		);
		$userInfo = $this->getDbTable()->find($customer->getId());
		if(!$userInfo->current()) {
			return $this->getDbTable()->insert($data);
		} else {
			return $this->getDbTable()->update($data, array('user_id = ?' => $customer->getId()));
		}
	}

	/**
	 * @param $customer Models_Model_Customer
	 * @return Models_Model_Customer
	 */
	private function _processAddresses($customer) {
		if (($addresses = $customer->getAddresses()) !== null) {
			$addressTable = new Models_DbTable_CustomerAddress();
			$addressTable->getAdapter()->beginTransaction();
			foreach ($addresses as &$address) {
				$address['user_id'] = $customer->getId();
				if (isset($address['id'])){
					$row = $addressTable->find($address['id'])->current();
					if ($row) {
						$row->setFromArray($address)->save();
						continue;
					}
				}
				$row = $addressTable->createRow($address);
				$status = $row->save();
				$address['id'] = $status;
			}
			$addressTable->getAdapter()->commit();
			$customer->setAddresses($addresses);
		}
		return $customer;
	}

	public function find($id) {
		$userDbTable    = new Application_Model_DbTable_User();
		$user           = $userDbTable->find($id)->current();
		if (!$user){
			return null;
		}

		$customer = new $this->_model($user->toArray());
		$customerAddresses = $user->findDependentRowset('Models_DbTable_CustomerAddress')->toArray();

		if (!empty($customerAddresses)){
			$customer->setAddresses($customerAddresses);
		}

		$customerInfo = $user->findDependentRowset($this->_dbTable)->current();
		if ($customerInfo) {
			$customer->setOptions($customerInfo->toArray());
		}

		return $customer;
	}

	public function findByEmail($email) {
		$userDbTable = new Application_Model_DbTable_User();
		$user        = $userDbTable->fetchAll($userDbTable->getAdapter()->quoteInto("email=?", $email))->current();
		if($user === null) {
			return null;
		}
		return $this->find($user->id);
	}

	public function addAddress(Models_Model_Customer $customer, $address, $type = null){
		$addressTable = new Models_DbTable_CustomerAddress();
		if (!empty($address)){
			if ($type !== null) {
				$address['address_type'] = $type;
			}
			$address = Tools_Misc::clenupAddress($address);
			$address['id'] = Tools_Misc::getAddressUniqKey($address);
			$address['user_id'] = $customer->getId();
			if (null === ($row = $addressTable->find($address['id'])->current())) {
				$row = $addressTable->createRow();
			}
			$row->setFromArray($address);

			return $row->save();
		}
		return null;
	}

	public function fetchAll($where = null, $order = array()){
		$this->_dbTable = 'Application_Model_DbTable_User';
		$entries = array();

		$resultSet = $this->getDbTable()->fetchAll($where, $order);
		if(null === $resultSet) {
			return null;
		}
		foreach ($resultSet as $row) {
			$model = new $this->_model($row->toArray());
			$model->setPassword(null);
			$customerAddresses = $row->findDependentRowset('Models_DbTable_CustomerAddress')->toArray();
			if ($customerAddresses) {
				$model->setAddresses($customerAddresses);
			}
			array_push($entries, $model);
		}
		return $entries;
	}

	/**
	 * Method for fetching users for customer dashboard with special set of agregated fields
	 * @param null| $where
	 * @param null $order
	 * @param null $limit
	 * @param null $offset
	 * @param null $search
	 * @return array
	 */
	public function listAll($where = null, $order = null, $limit = null, $offset = null, $search = null) {
		$userDbTable = new Application_Model_DbTable_User();
        $joinCondition = '(cart.user_id = user.id)';
        $joinCondition .= ' AND ('.$userDbTable->getAdapter()->quoteInto('cart.status=?', Models_Model_CartSession::CART_STATUS_COMPLETED);
        $joinCondition .= ' OR '.$userDbTable->getAdapter()->quoteInto('cart.status=?', Models_Model_CartSession::CART_STATUS_PENDING);
        $joinCondition .= ' OR '.$userDbTable->getAdapter()->quoteInto('cart.status=?', Models_Model_CartSession::CART_STATUS_SHIPPED);
        $joinCondition .= ' OR '.$userDbTable->getAdapter()->quoteInto('cart.status=?', Models_Model_CartSession::CART_STATUS_DELIVERED). ')';
        $joinConditionCustomer = ('userattr.user_id = user.id').' AND '.$userDbTable->getAdapter()->quoteInto('userattr.attribute LIKE ?', 'customer_%');

        $select = $userDbTable->select()
				->setIntegrityCheck(false)
				->from('user',array('id', 'full_name', 'email', 'reg_date', 'mobile_phone' ))
				->joinLeft(
					array('cart' => 'shopping_cart_session'),
                    $joinCondition,
					array(
						'total_amount' => 'SUM(cart.total)',
						'total_orders'=>'COUNT(cart.id)'
					))
                ->joinLeft(
                    array('customerinfo' => 'shopping_customer_info'),
                   ('customerinfo.user_id = user.id'),
                    array(
                        'group_id' => 'customerinfo.group_id',
                   ))
            ->joinLeft(
                array('userattr' => 'user_attributes'),
                $joinConditionCustomer,
                array('customer_attr'=>'(GROUP_CONCAT(DISTINCT(userattr.attribute), \'||\', userattr.value))'))
            ->group('user.id');

		if (!Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_USERS)) {
			$select->where('user.role_id NOT IN (?)', array(
				Tools_Security_Acl::ROLE_SUPERADMIN,
				Tools_Security_Acl::ROLE_ADMIN
			));
		}

		if ($where) {
			$select->where($where);
		}
		if ($order) {
			$select->order($order);
		}

		if ($search) {
			$select->orWhere('user.full_name LIKE ?', '%'.$search.'%')
					->orWhere('user.email LIKE ?', '%'.$search.'%');
		}

		$select->limit($limit, $offset);

		return $userDbTable->fetchAll($select)->toArray();
	}

    public function getUserAddressByUserId($userId, $addressId = false)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('user_id = ?', $userId);
        if ($addressId) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $addressId);
        }
        $select = $this->getDbTable()->getAdapter()->select()
            ->from('shopping_customer_address', array(
                'id',
                'user_id',
                'address_type',
                'firstname',
                'lastname',
                'company',
                'email',
                'address1',
                'address2',
                'country',
                'city',
                'state',
                'zip',
                'phone',
                'mobile'
            ))
            ->where($where);
        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }

    public function getUserAddressOrdersByUserId($userId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('c_adr.user_id = ?', $userId);

        $select = $this->getDbTable()->getAdapter()->select()
            ->from(array('c_adr' =>'shopping_customer_address'), array(
                'c_adr.id',
                'c_adr.user_id',
                'c_adr.address_type',
                'c_adr.firstname',
                'c_adr.lastname',
                'c_adr.company',
                'c_adr.email',
                'c_adr.address1',
                'c_adr.address2',
                'c_adr.country',
                'c_adr.city',
                'c_adr.state',
                'c_adr.zip',
                'c_adr.phone',
                'c_adr.mobile',
            ))
            ->joinLeft(array('s_cart' => 'shopping_cart_session'), 's_cart.shipping_address_id = c_adr.id', array('shippingId' => '(GROUP_CONCAT(s_cart.id))'))
            ->joinLeft(array('b_cart' => 'shopping_cart_session'), 'b_cart.billing_address_id = c_adr.id', array('billingId' => '(GROUP_CONCAT(b_cart.id))'))
            ->where($where)->group('c_adr.id');

        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }
}
