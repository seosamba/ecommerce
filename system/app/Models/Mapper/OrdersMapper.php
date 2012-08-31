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

	public function save($model) {
		// TODO: Implement save() method.
	}

	public function fetchAll(){
		$select = $this->getDbTable()->select(Zend_Db_Table::SELECT_WITHOUT_FROM_PART)
				->setIntegrityCheck(false)
				->from(array('order' => 'shopping_cart_session'))
				->joinLeft(array('oc' => 'shopping_cart_session_content'), 'oc.cart_id = order.id', array('total_products' => 'COUNT(oc.id)'))
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
				));

		APPLICATION_ENV === 'development' && error_log($select->__toString());
		return $this->getDbTable()->fetchAll($select)->toArray();
	}
}
