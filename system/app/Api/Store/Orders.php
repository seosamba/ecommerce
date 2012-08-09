<?php
/**
 * Orders.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Store_Orders extends Api_Service_Abstract {

	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post', 'put', 'delete')
		)
	);

	/**
	 * The get action handles GET requests and receives an 'id' parameter; it
	 * should respond with the server resource state of the resource identified
	 * by the 'id' value.
	 */
	public function getAction() {
		$id = filter_var($this->_request->getParam('id'), FILTER_VALIDATE_INT);
		if ($id){
			$order = Models_Mapper_CartSessionMapper::getInstance()->find($id);
			if (is_null($order)){
				$this->_error(null, self::REST_STATUS_NOT_FOUND);
			}
			if (!Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_ADMINPANEL)){
				if ((int)$order->getUserId() !== (int)Tools_ShoppingCart::getInstance()->getInstance()->getCustomer()->getId()) {
					$this->_error(null, self::REST_STATUS_FORBIDDEN);
				}
			}
			return $order->toArray();
		} else {
			$filter = $this->_request->getParam('pid');
			if ($filter !== null){
				$filter = filter_var($filter, FILTER_VALIDATE_INT);
				if ($filter !== false){
					return Models_Mapper_CartSessionMapper::getInstance()->findByProductId($filter);
				} else {
					$this->_error();
				}
			} else {
				$orderList = Models_Mapper_CartSessionMapper::getInstance()->fetchAll();
			}
			return array_map(function($order){ return $order->toArray(); }, $orderList);
		}
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
		// TODO: Implement deleteAction() method.
	}
}
