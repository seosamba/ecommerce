<?php
/**
 * OrdersMapper.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 * @method Models_Mapper_OrdersMapper getInstance() getInstance()  Returns an instance of itself
 * @method Zend_Db_Table getDbTable() getDbTable()  Returns an instance of Zend_Db_Table
 */
class Models_Mapper_OrdersMapper extends Application_Model_Mappers_Abstract {

	protected $_dbTable	= 'Models_DbTable_CartSession';

	protected $_model	= 'Models_Model_CartSession';

	protected static $_lastQueryResultCount = false;

	public function save($model) {
		// TODO: Implement save() method.
	}

	public function lastQueryResultCount($flag){
		self::$_lastQueryResultCount = (bool) $flag;
		return $this;
	}

	public function fetchAll($where = null, $order = null, $limit = null, $offset = null){
		$select = $this->getDbTable()->select(Zend_Db_Table::SELECT_WITHOUT_FROM_PART)
				->setIntegrityCheck(false)
				->from(array('order' => 'shopping_cart_session'))
				->joinLeft(array('oc' => 'shopping_cart_session_content'), 'oc.cart_id = order.id', array('total_products' => 'COUNT(DISTINCT oc.id)'))
				->joinLeft(array('s_adr' => 'shopping_customer_address'), 's_adr.id = order.shipping_address_id', array(
					'shipping_firstname' => 'firstname',
					'shipping_lastname' => 'lastname',
					'shipping_company' => 'company',
					'shipping_email' => 'email',
					'shipping_phone' => 'phone',
					'shipping_mobile' => 'mobile',
					'shipping_country' => 'country',
					'shipping_city' => 'city',
					'shipping_state' => 'state',
					'shipping_zip' => 'zip',
					'shipping_address1' => 'address1',
					'shipping_address2' => 'address2'
				))
				->joinLeft(array('b_adr' => 'shopping_customer_address'), 'b_adr.id = order.billing_address_id', array(
					'billing_firstname' => 'firstname',
					'billing_lastname' => 'lastname',
					'billing_company' => 'company',
					'billing_email' => 'email',
					'billing_phone' => 'phone',
					'billing_mobile' => 'mobile',
					'billing_country' => 'country',
					'billing_city' => 'city',
					'billing_state' => 'state',
					'billing_zip' => 'zip',
					'billing_address1' => 'address1',
					'billing_address2' => 'address2'
				))
				->joinLeft(array('u' => 'user'), 'u.id = order.user_id', array(
					'full_name', 'email'
				))
				->group('order.id');

		if ($where){
			$where = (array) $where;
			$this->_parseWhere($select, $where);
		}

		if (!empty($order)){
			$select->order($order);
		}

		Tools_System_Tools::debugMode() && error_log($select->__toString());

		if (self::$_lastQueryResultCount){
			$data = $this->getDbTable()->fetchAll($select)->toArray();

			return array(
				'totalRecords'  => sizeof($data),
				'data'          => array_slice($data, $offset, $limit),
				'offset'        => $offset,
				'limit'         => $limit
			);
		}

		$select->limit($limit, $offset);

		return $this->getDbTable()->fetchAll($select)->toArray();
	}

	private function _parseWhere(Zend_Db_Table_Select $select, $where){
		if (isset($where['product-id']) || isset($where['product-key'])){
			$select->join(array('p' => 'shopping_product'), 'p.id = oc.product_id', null);
		}
		if (is_string($where)){
			return $select->where($where);
		}
		if (!is_array($where)){
			return $select;
		}
		foreach ($where as $key => $val){
			if (is_int($key)) {
				$select->where($val);
			} else {
				$key = strtolower($key);
				switch($key){
					case 'product-id':
						$select->where('p.id = ?', $val);
						break;
					case 'product-key':
						$likeWhere = "p.name LIKE ? OR p.sku LIKE ? OR p.mpn LIKE ?";
						$select->where($likeWhere, '%'.$val.'%');
						break;
					case 'country':
					case 'state':
						$key = sprintf('s_adr.%1$s = ? OR b_adr.%1$s = ?', $key);
						$select->where($key, $val);
						break;
					case 'carrier':
						$select->where('order.shipping_service = ?', $val);
						break;
					case 'date-from':
						$select->where('order.created_at > ?', date(DATE_ATOM, strtotime($val)));
						break;
					case 'date-to':
						$select->where('order.created_at < ?', date(DATE_ATOM, strtotime($val)));
						break;
					case 'amount-from':
						$val = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT);
						$select->where('order.total >= ?', $val);
						break;
					case 'amount-to':
						$val = filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT);
						$select->where('order.total <= ?', $val);
						break;
                    case 'gateway':
                        $val = filter_var($val, FILTER_SANITIZE_STRING);
                        $select->where('order.gateway = ?', $val);
                        break;
                    case 'exclude_gateway':
                        $val = filter_var($val, FILTER_SANITIZE_STRING);
                        $select->where('order.gateway <> ?', $val);
                        break;
                    case 'exclude_empty_address':
                        $select->where('s_adr.firstname IS NOT NULL');
                        break;
					case 'status':
						$val = filter_var($val, FILTER_SANITIZE_STRING);
						$select->where('order.status = ?', $val);
				}
			}
		}
		return $select;
	}

}
