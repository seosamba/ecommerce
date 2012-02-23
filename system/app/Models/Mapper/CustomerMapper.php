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
			'shipping_address_id'   => $customer->getShippingAddressId(),
			'billing_address_id'    => $customer->getBillingAddressId()
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
		$customerInfo       = $this->getDbTable()->find($id)->current();
		if ($customerInfo) {
			$userData = array_merge($user->toArray(), $customerInfo->toArray(), array('addresses' => array()));
			$customerAddresses = $customerInfo->findDependentRowset('Models_DbTable_CustomerAddress')->toArray();
			foreach ($customerAddresses as $addr){
				$addr = Tools_Misc::clenupAddress($addr);
				$userData['addresses'][Tools_Misc::getAddressUniqKey($addr)] = $addr;
				unset($addr);
			}
			return new $this->_model($userData);
		}
		return null;
	}

	public function findByEmail($email) {
		$userDbTable = new Application_Model_DbTable_User();
		$user        = $userDbTable->fetchAll($userDbTable->getAdapter()->quoteInto("email=?", $email))->current();
		if($user === null) {
			return null;
		}
		return $this->find($user->id);
	}
}
