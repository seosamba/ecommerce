<?php
/**
 * Orders.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Store_Orders extends Api_Service_Abstract {

	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post', 'put', 'delete')
		),
        Tools_Security_Acl::ROLE_ADMIN => array(
			'allow' => array('get', 'post', 'put', 'delete')
		),
        Shopping::ROLE_SALESPERSON => array(
			'allow' => array('get', 'post')
		)
	);

	/**
	 * The get action handles GET requests and receives an 'id' parameter; it
	 * should respond with the server resource state of the resource identified
	 * by the 'id' value.
	 */
	public function getAction() {
		$id = filter_var($this->_request->getParam('id'), FILTER_VALIDATE_INT);
		$orderMapper = Models_Mapper_OrdersMapper::getInstance();
		$count = (bool) $this->_request->has('count');
		if ($count){
			$orderMapper->lastQueryResultCount($count);
		}
		if ($id){
			$where = $orderMapper->getDbTable()->getAdapter()->quoteInto('order.id = ?', intval($id));
			$order = $orderMapper->fetchAll($where);
			if (is_null($order)){
				$this->_error(null, self::REST_STATUS_NOT_FOUND);
			}
			if (!Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_ADMINPANEL)){
				if ((int)$order->getUserId() !== (int)Tools_ShoppingCart::getInstance()->getInstance()->getCustomer()->getId()) {
					$this->_error(null, self::REST_STATUS_FORBIDDEN);
				}
			}
			return $order;
		} else {
			$filter = $this->_request->getParam('filter');
			$limit = filter_var($this->_request->getParam('limit'), FILTER_SANITIZE_NUMBER_INT);
			$offset = filter_var($this->_request->getParam('offset'), FILTER_SANITIZE_NUMBER_INT);
			$order = filter_var_array($this->_request->getParam('order', array()), FILTER_SANITIZE_STRING);
			if (is_array($filter)){
				$filter['product-id'] = filter_var($this->_request->getParam('productid'), FILTER_SANITIZE_NUMBER_INT);
				$filter = array_filter(filter_var_array($filter, FILTER_SANITIZE_STRING));
				return $orderMapper->fetchAll($filter, $order, $limit, $offset);
			} else {
				$orderList = $orderMapper->fetchAll();
			}
			return $orderList;
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
