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
					'full_name', 'email', 'originalTotal' => new Zend_Db_Expr('SUM(order.total+order.refund_amount)')
				))
                ->joinLeft(array('imp'=>'shopping_import_orders'), 'imp.real_order_id=order.id',
                    array('real_order_id'=>'imp.real_order_id'))
                ->joinLeft(array('shrp'=>'shopping_recurring_payment'), 'shrp.cart_id=order.id',
                    array('recurring_id'=>'shrp.cart_id'))
                ->joinLeft(array('shcoupon'=>'shopping_coupon_sales'), 'shcoupon.cart_id=order.id',
                    array('coupon_code'=>'shcoupon.coupon_code'))
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

    public function fetchOrdersForExport($orderIds = array(), $excludeFields = array(), $filter = array())
    {
        $defaultFields = array(
            'order_id' => 'order.id',
            'updated_at' => 'order.updated_at',
            'status' => 'order.status',
            'total_products' => 'COUNT(DISTINCT oc.id)',
            'sku' => '(GROUP_CONCAT(DISTINCT(sp.sku)))',
            'mpn' => '(GROUP_CONCAT(DISTINCT(sp.mpn)))',
            'product_name' => '(GROUP_CONCAT(DISTINCT(sp.name)))',
            'product_price' => '(GROUP_CONCAT(oc.price))',
            'product_tax' => '(GROUP_CONCAT(oc.tax))',
            'product_tax_price' => '(GROUP_CONCAT(oc.tax_price))',
            'product_qty' => '(GROUP_CONCAT(oc.qty))',
            'shipping_type' => 'order.shipping_type',
            'shipping_service' => 'order.shipping_service',
            'gateway' => 'order.gateway',
            'shipping_price' => 'order.shipping_price',
            'discount_tax_rate' => 'order.discount_tax_rate',
            'sub_total' => 'order.sub_total',
            'shipping_tax' => 'order.shipping_tax',
            'discount_tax' => 'order.discount_tax',
            'sub_total_tax' => 'order.sub_total_tax',
            'total_tax' => 'order.total_tax',
            'discount' => 'order.discount',
            'total' => 'order.total',
            'notes' => 'order.notes',
            'shipping_tracking_id' => 'order.shipping_tracking_id',
            'brand' => 'sb.name',
            'user_name' => 'u.full_name',
            'user_email' => 'u.email',
            'shipping_firstname' => 's_adr.firstname',
            'shipping_lastname' => 's_adr.lastname',
            'shipping_company' => 's_adr.company',
            'shipping_email' => 's_adr.email',
            'shipping_phone' => 's_adr.phone',
            'shipping_mobile' => 's_adr.mobile',
            'shipping_country' => 's_adr.country',
            'shipping_city' => 's_adr.city',
            'shipping_state' => 'sls_s.name',
            'shipping_zip' => 's_adr.zip',
            'shipping_address1' => 's_adr.address1',
            'shipping_address2' => 's_adr.address2',
            'billing_firstname' => 'b_adr.firstname',
            'billing_lastname' => 'b_adr.lastname',
            'billing_company' => 'b_adr.company',
            'billing_email' => 'b_adr.email',
            'billing_phone' => 'b_adr.phone',
            'billing_mobile' => 'b_adr.mobile',
            'billing_country' => 'b_adr.country',
            'billing_city' => 'b_adr.city',
            'billing_state' => 'sls_b.name',
            'billing_zip' => 'b_adr.zip',
            'billing_address1' => 'b_adr.address1',
            'billing_address2' => 'b_adr.address2'
        );

        if (!empty($excludeFields)) {
            foreach ($excludeFields as $fieldName => $fieldValue) {
                unset($defaultFields[$fieldName]);
            }
        }
        $select = $this->getDbTable()->select(Zend_Db_Table::SELECT_WITHOUT_FROM_PART)
            ->setIntegrityCheck(false)
            ->from(
                array('order' => 'shopping_cart_session'),
                $defaultFields
            )
            ->joinLeft(array('oc' => 'shopping_cart_session_content'), 'oc.cart_id = order.id', array(''))
            ->joinLeft(
                array('sp' => 'shopping_product'),
                'oc.product_id = sp.id',
                array('')
            )
            ->joinLeft(
                array('sb' => 'shopping_brands'),
                'sp.brand_id = sb.id',
                array()
            )
            ->joinLeft(
                array('u' => 'user'),
                'u.id = order.user_id',
                array('')
            )
            ->joinLeft(
                array('s_adr' => 'shopping_customer_address'),
                's_adr.id = order.shipping_address_id',
                array('')
            )
            ->joinLeft(
                array('b_adr' => 'shopping_customer_address'),
                'b_adr.id = order.billing_address_id',
                array('')
            )
            ->joinLeft(
                array('sls_s' => 'shopping_list_state'),
                'sls_s.id = s_adr.state',
                array('')
            )
            ->joinLeft(
                array('sls_b' => 'shopping_list_state'),
                'sls_b.id = b_adr.state',
                array('')
            )
            ->group('order.id');
        if (!empty($orderIds)) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('order.id IN (?)', $orderIds);
            $select->where($where);
        } else {
            $where = (array) $filter;
            $this->_parseWhere($select, $where);
        }
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
                    case 'user':
                        $likeWhere = "CONCAT(TRIM(s_adr.firstname), ' ',  TRIM(s_adr.lastname)) LIKE ?  OR CONCAT(TRIM(b_adr.firstname), ' ',  TRIM(b_adr.lastname)) LIKE ?";
                        $select->where($likeWhere, '%' . $val . '%');
                        break;
					case 'product-id':
						$select->where('p.id = ?', $val);
						break;
					case 'product-key':
                        $likeWhere = "p.name LIKE ? OR p.sku LIKE ? OR p.mpn LIKE ?";
                        if (strpos($val, ',')) {
                            $valArr = array_filter(explode(',', $val));
                            for ($i = 0; $i < sizeof($valArr); $i++) {
                                if ($i == 0) {
                                    $select->where($likeWhere, '%'.$valArr[$i].'%');
                                }
                                else {
                                    $select->orWhere($likeWhere, '%'.$valArr[$i].'%');
                                }
                            }
                        }
                        else {
                            $select->where($likeWhere, '%'.$val.'%');
                        }
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
                    case 'filter-order-type':
                        if ($val === 'recurring_id') {
                            $select->where('shrp.cart_id IS NOT NULL');
                        }
                        if ($val === 'real_order_id') {
                            $select->where('shrp.cart_id IS NULL');
                            $select->where('imp.real_order_id IS NULL');
                        }
                        if ($val === 'cart_imported_id') {
                            $select->where('imp.real_order_id IS NOT NULL');
                        }
                        break;
                    case 'filter-recurring-order-type':
                        $val = filter_var($val, FILTER_SANITIZE_STRING);
                        $select->where('shrp.recurring_status = ?', $val);
                        break;
                    case 'filter-by-coupon':
                        $val = trim(filter_var($val, FILTER_SANITIZE_STRING));
                        if (!empty($val)) {
                            $select->where('shcoupon.coupon_code = ?', $val);
                        }
                        break;
					case 'status':
						$val = filter_var_array($val, FILTER_SANITIZE_STRING);
						if (!empty($val)) {
							$filterWhere = '(';
							foreach ($val as $status) {
								$filterWhere .= $this->getDbTable()->getAdapter()->quoteInto('order.status = ?', $status['name']);
								if ($status[Tools_FilterOrders::GATEWAY_QUOTE]) {
									$filterWhere .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('order.gateway = ?', Tools_FilterOrders::GATEWAY_QUOTE);
								} else {
									$filterWhere .= ' AND (' .$this->getDbTable()->getAdapter()->quoteInto('order.gateway <> ?', Tools_FilterOrders::GATEWAY_QUOTE);
									$filterWhere .= ' OR order.gateway IS NULL)';
								}
								$filterWhere .= ') OR (';
							}
							$filterWhere = rtrim($filterWhere, ' OR (');
							$select->where($filterWhere);
						}
				}
			}
		}
		return $select;
	}


    /**
     * Update order data
     *
     * @param int $orderId order id
     * @param array $data array of order params ex: array('total' => '200', 'notes' => 'some info')
     * @return int
     * @throws Zend_Db_Adapter_Exception
     */
    public function updateOrderInfo($orderId, array $data = array())
    {
        if (!empty($data) && $orderId) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $orderId);
            return $this->getDbTable()->getAdapter()->update('shopping_cart_session', $data, $where);
        }
        return false;
    }

}
