<?php
class Models_Mapper_CartSessionMapper extends Application_Model_Mappers_Abstract {

	protected $_dbTable	= 'Models_DbTable_CartSession';

	protected $_model	= 'Models_Model_CartSession';

	/**
	 * Save cart to database
	 * @param $model Models_Model_CartSession
	 * @return mixed
	 * @throws Exceptions_SeotoasterPluginException
	 */
	public function save($model) {
		if(!$model instanceof Models_Model_CartSession) {
			throw new Exceptions_SeotoasterPluginException('Wrong model type given.');
		}
		$data = array(
			'id'           => $model->getId(),
			'ip_address'   => $model->getIpAddress(),
			'user_id'      => $model->getUserId(),
			'status'       => $model->getStatus(),
			'gateway'      => $model->getGateway(),
			'shipping_address_id'   => $model->getShippingAddressId(),
			'billing_address_id'    => $model->getBillingAddressId(),
			'shipping_price'        => $model->getShippingPrice(),
			'shipping_type'         => $model->getShippingType(),
			'shipping_service'      => $model->getShippingService()
		);

		if(null === ($exists = $this->find($data['id']))) {
			$data['created_at'] = date(DATE_ATOM);
			$newId = $this->getDbTable()->insert($data);
			if ($newId){
				$model->setId($newId);
			}
		}
		else {
			$data['updated_at'] = date(DATE_ATOM);
			$this->getDbTable()->update($data, array('id = ?' => $exists->getId()));
		}

		$this->_processCartContent($model);

		return $model;
	}

	private function _processCartContent(Models_Model_CartSession $cartSession){
		$cartSessionContentDbTable = new Models_DbTable_CartSessionContent();
		$content = $cartSession->getCartContent();
		if (!empty($content)) {
			$cartSessionContentDbTable->getAdapter()->beginTransaction();
			$cartSessionContentDbTable->delete(array('cart_id = ?' => $cartSession->getId() ));
			foreach ($content as $item) {
				$item['options'] = http_build_query($item['options']);
				$item['cart_id'] = $cartSession->getId();

				$cartSessionContentDbTable->insert($item);
			}
			try {
				$cartSessionContentDbTable->getAdapter()->commit();
			} catch (Exception $e) {

			}
		}
	}

	public function find($id) {
		$result = $this->getDbTable()->find($id);
		if(0 == count($result)) {
			return null;
		}
		$row = $result->current();
		if ($row) {
			return $this->_toModel($row);
		}
		return null;
	}

	public function fetchAll($where = null, $order = array()) {
		$entries = array();
		$resultSet = $this->getDbTable()->fetchAll($where, $order);
		if(null === $resultSet) {
			return null;
		}
		foreach ($resultSet as $row) {
			$entries[] = $this->_toModel($row);
		}
		return $entries;
	}

	private function _toModel(Zend_Db_Table_Row_Abstract $row) {
		$model = new $this->_model($row->toArray());
		$content = $row->findDependentRowset('Models_DbTable_CartSessionContent')->toArray();
		if (!empty($content)){
			array_walk($content, function(&$item){
				parse_str($item['options'],$item['options']);
			});
			$model->setCartContent($content);
		}

		return $model;
	}
}
