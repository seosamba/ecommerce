<?php
/**
 * Shippers.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Store_Shippers extends Api_Service_Abstract {

	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post', 'put', 'delete')
		)
	);

	/**
	 * @return array
	 */
	public function getAction() {
		$name = filter_var($this->_request->getParam('name'), FILTER_SANITIZE_STRING);
		if ($name){
			return Models_Mapper_ShippingConfigMapper::getInstance()->find($name);
		} else {
			return Models_Mapper_ShippingConfigMapper::getInstance()->fetchAll();
		}
	}

	/**
	 * The post action handles POST requests; it should accept and digest a
	 * POSTed resource representation and persist the resource state.
	 */
	public function postAction() {
		return $this->putAction();
	}

	/**
	 * The put action handles PUT requests and receives an 'id' parameter; it
	 * should update the server resource state of the resource identified by
	 * the 'id' value.
	 */
	public function putAction() {
		$data = Zend_Json::decode($this->_request->getRawBody());
		if (is_array($data) && !empty($data)){
			if (Models_Mapper_ShippingConfigMapper::getInstance()->save($data)){
				return $data;
			}
		}
		$this->_error(null, self::REST_STATUS_BAD_REQUEST);
	}

	/**
	 * The delete action handles DELETE requests and receives an 'id'
	 * parameter; it should update the server resource state of the resource
	 * identified by the 'id' value.
	 */
	public function deleteAction() {
		// TODO: Implement deleteAction() method.
	}
}
