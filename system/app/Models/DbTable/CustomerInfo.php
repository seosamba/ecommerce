<?php
/**
 * Eugene I. Nezhuta <eugene@seotoaster.com>
 *
 */

class Models_DbTable_CustomerInfo extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_customer_info';

	protected $_dependentTables = array(
		'Models_DbTable_CustomerAddress'
	);

	protected $_referenceMap = array(
			'User' => array(
				'columns'		=> 'user_id',
				'refTableClass'	=> 'Application_Model_DbTable_User',
				'refColumns'	=> 'id'
			)
	);
}
