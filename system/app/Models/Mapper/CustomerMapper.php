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
			'default_billing_address_id'    => $customer->getDefaultBillingAddressId()
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


	public function listAll($where = null, $order = null, $limit = null, $offset = null, $search = null) {
		$userDbTable = new Application_Model_DbTable_User();
		$select = $userDbTable->select()
				->setIntegrityCheck(false)
				->from('user',array('id', 'full_name', 'email', 'reg_date' => "DATE_FORMAT(reg_date, '%d %b, %Y')" ))
				->joinLeft(
					array('cart' => 'shopping_cart_session'),
					'cart.user_id = user.id',
					array(
						'total_amount' => 'SUM(cart.total)',
						'total_orders'=>'COUNT(cart.id)'
					))
				->group('user.id')
				//@todo filter only paid carts
//				->where('cart.status = ?', Models_Model_CartSession::CART_STATUS_COMPLETED )
//				->orWhere('total_orders > 0')
		;

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
//		error_log($select->__toString());
		return $userDbTable->fetchAll($select)->toArray();
	}
}
