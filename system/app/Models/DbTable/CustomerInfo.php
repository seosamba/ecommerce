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
}
