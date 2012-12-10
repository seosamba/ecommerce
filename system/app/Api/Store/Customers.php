<?php
/**
 * Customers.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Store_Customers extends Api_Service_Abstract {

	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'delete')
		),
        Tools_Security_Acl::ROLE_ADMIN => array(
			'allow' => array('get', 'delete')
		),
        Shopping::ROLE_SALESPERSON => array(
			'allow' => array('get', 'delete')
		)
	);

	/**
	 * The get action handles GET requests and receives an 'id' parameter; it
	 * should respond with the server resource state of the resource identified
	 * by the 'id' value.
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
	 * The post action handles POST requests; it should accept and digest a
	 * POSTed resource representation and persist the resource state.
	 */
	public function postAction() {
		// TODO: Implement postAction() method.
	}

	/**
	 * The put action handles PUT requests and receives an 'id' parameter; it
	 * should update the server resource state of the resource identified by
	 * the 'id' value.
	 */
	public function putAction() {
		// TODO: Implement putAction() method.
	}

	/**
	 * The delete action handles DELETE requests and receives an 'id'
	 * parameter; it should update the server resource state of the resource
	 * identified by the 'id' value.
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
