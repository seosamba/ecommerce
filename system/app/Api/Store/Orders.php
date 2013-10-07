<?php
/**
 * Orders REST API controller
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 *
 * @package Store
 * @since 2.0.0
 */
class Api_Store_Orders extends Api_Service_Abstract {


    const GATEWAY_QUOTE = 'Quote';

	/**
	 * @var array Access Control List
	 */
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
	 * Get orders by giving contitions
	 *
	 * Resourse:
	 * : /api/store/orders/id/:id
	 *
	 * Method:
	 * : GET
	 *
	 * ## Parameters:
	 * id (type string)
	 * : Order id to fetch single product
	 *
	 * ## Optional parameters (оnly if ID is not defined)
	 *
	 * productid (type integer)
	 * : Filter orders that contains products with given id
	 *
	 * filter (type array)
	 * : Set of filters. Possible arguments: country, state, date-from, date-to, product-id
	 *
	 * limit (type integer)
	 * : Maximum number of results
	 *
	 * offset (type integer)
	 * : Number of results to skip
	 *
	 * order (type string)
	 * : Sorting fields
	 *
	 * @return JSON List of orders
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
			$filter = filter_var_array($this->_request->getParam('filter'), FILTER_SANITIZE_STRING);
			$limit = filter_var($this->_request->getParam('limit'), FILTER_SANITIZE_NUMBER_INT);
			$offset = filter_var($this->_request->getParam('offset'), FILTER_SANITIZE_NUMBER_INT);
			$sortOrder = filter_var($this->_request->getParam('order'), FILTER_SANITIZE_STRING);

			if ($sortOrder){
				$sortOrder = strtr($sortOrder, array(
					'name'  => 'u.full_name',
					'email' => 'u.email',
					'date'  => 'order.created_at',
					'status' => 'order.status',
					'products' => 'total_products',
					'total' => 'order.total',
					'shipping_price' => 'order.shipping_price'
				));
			} else {
				$sortOrder = 'order.created_at DESC';
			}

			if (is_array($filter)){
				if (isset($filter['country'])) {
					if (!preg_match('/[A-Z]{2}/', $filter['country'])) {
	                     unset($filter['country']);
	                }
				}
				if (isset($filter['state']) && $filter['state'] === '0') {
					unset($filter['state']);
				}
				if (isset($filter['date-from']) && !empty($filter['date-from'])) {
					$filter['date-from'] = date(Tools_System_Tools::DATE_MYSQL, strtotime($filter['date-from']));
				}
				if (isset($filter['date-to']) && !empty($filter['date-to'])) {
					$filter['date-to'] = date(Tools_System_Tools::DATE_MYSQL, strtotime($filter['date-to']));
				}
                $filter['product-id'] = filter_var($this->_request->getParam('productid'), FILTER_SANITIZE_NUMBER_INT);
				$filter = array_filter(filter_var_array($filter, FILTER_SANITIZE_STRING));
                if(isset($filter['status']) && ($filter['status'] == Models_Model_CartSession::CART_STATUS_PENDING || $filter['status'] == Models_Model_CartSession::CART_STATUS_PROCESSING || $filter['status'] == Models_Model_CartSession::CART_STATUS_CANCELED)){
                    $filter['exclude_gateway'] = self::GATEWAY_QUOTE;
                }
                if(isset($filter['status']) && $filter['status'] == Tools_Misc::CS_ALIAS_PENDING){
                    $filter['status']  = Models_Model_CartSession::CART_STATUS_PENDING;
                    $filter['gateway'] = self::GATEWAY_QUOTE;
                }
                if(isset($filter['status']) && $filter['status'] == Tools_Misc::CS_ALIAS_PROCESSING){
                    $filter['status']  = Models_Model_CartSession::CART_STATUS_PROCESSING;
                    $filter['gateway'] = self::GATEWAY_QUOTE;
                }
                if(isset($filter['status']) && $filter['status'] == Tools_Misc::CS_ALIAS_LOST_OPPORTUNITY){
                    $filter['status']  = Models_Model_CartSession::CART_STATUS_CANCELED;
                    $filter['gateway'] = self::GATEWAY_QUOTE;
                }
                $filter['exclude_empty_address'] = '';
				$orderList = $orderMapper->fetchAll($filter, $sortOrder, $limit, $offset);
			} else {
				$orderList = $orderMapper->fetchAll();
			}
			return $orderList;
		}
	}

	/**
	 * Reserved for future usage
	 */
	public function postAction() {
		// TODO: Implement postAction() method.
	}

	/**
	 * Reserved for future usage
	 */
	public function putAction() {
		// TODO: Implement putAction() method.
	}

	/**
	 * Reserved for future usage
	 */
	public function deleteAction() {
		// TODO: Implement deleteAction() method.
	}
}
