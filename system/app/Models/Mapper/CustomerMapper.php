<?php
/**
 * Eugene I. Nezhuta <eugene@seotoaster.com>
 *
 */

class Models_Mapper_CustomerMapper extends Application_Model_Mappers_Abstract {

	protected $_dbTable = 'Models_DbTable_Customer';

	protected $_model   = 'Models_Model_Customer';

	public function save($customer) {

		//save user data
		$userMapper = Application_Model_Mappers_UserMapper::getInstance();
		$userId     = $userMapper->save($customer);
		$userId     = ($customer->getId()) ? $customer->getId() : $userId;

		//save customer info
		$data = array(
			'user_id'          => $userId,
			'shipping_address' => $customer->getShippingAddress(false),
			'billing_address'  => $customer->getBillingAddress(false),
			'company'          => $customer->getCompany(),
			'phone'            => $customer->getPhone(),
			'mobile'           => $customer->getMobile()
		);

		if(($id = $customer->getId()) == null) {
			return $this->getDbTable()->insert($data);
		} else {
			return $this->getDbTable()->update($data, array('user_id = ?' => $id));
		}

	}
}
