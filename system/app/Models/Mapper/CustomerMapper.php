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
		$userId     = $userMapper->save($customer);

		if (!$customer->getId()){
			$customer->setId($userId);
		}

		$this->_processAddresses($customer);
		//save customer info
		$data = array(
			'user_id'          => $customer->getId(),
			'shipping_address_id' => $customer->getShippingAddressId(),
			'billing_address_id'  => $customer->getBillingAddressId()
		);
		$userInfo = $this->getDbTable()->find($customer->getId());
		if(!$userInfo->current()) {
			return $this->getDbTable()->insert($data);
		} else {
			return $this->getDbTable()->update($data, array('user_id = ?' => $customer->getId()));
		}
	}

	private function _processAddresses($customer) {
		if (($addresses = $customer->getAddresses()) !== null) {
			$addressTable = new Models_DbTable_CustomerAddress();
			$addressTable->getAdapter()->beginTransaction();
			foreach ($addresses as $address) {
				$address['user_id'] = $customer->getId();
				if (isset($address['id'])){
					$row = $addressTable->find($address['id']);
				} else {
					$row = $addressTable->createRow($address);
				}
				$status = $row->save();
			}
			$addressTable->getAdapter()->commit();
		}
		return null;
	}

	public function find($id) {
		$userDbTable    = new Application_Model_DbTable_User();
		$user           = $userDbTable->find($id)->current();
		$userInfo       = $this->getDbTable()->fetchAll(array('user_id' => $id))->current();
		$addresses      = $userInfo->findDependentRowset('Models_DbTable_CustomerAddress')->toArray();

		return new $this->_model(array_merge($user->toArray(), $userInfo->toArray(), array('addresses' => $addresses)));
	}

	public function findByEmail($email) {
		$userDbTable = new Application_Model_DbTable_User();
		$user        = $userDbTable->fetchAll($userDbTable->getAdapter()->quoteInto("email=?", $email))->current();
		if($user === null) {
			return null;
		}
		$userInfo = $this->getDbTable()->fetchAll(array('user_id' => $user->id))->current();
		return new $this->_model(array_merge($user->toArray(), $userInfo->toArray()));
	}
}
