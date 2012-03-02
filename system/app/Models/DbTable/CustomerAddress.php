<?php
/**
 * Eugene I. Nezhuta <eugene@seotoaster.com>
 *
 */

class Models_DbTable_CustomerAddress extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_customer_address';

	protected $_referenceMap = array(
			'Customer' => array(
				'columns'		=> 'user_id',
				'refTableClass'	=> 'Models_DbTable_CustomerInfo',
				'refColumns'	=> 'user_id'
			),
			'User' => array(
				'columns'		=> 'user_id',
				'refTableClass'	=> 'Application_Model_DbTable_User',
				'refColumns'	=> 'id'
			)
	);

}
