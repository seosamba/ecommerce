<?php
class Models_DbTable_CartSession extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_cart_session';

	protected $_dependentTables = array(
		'Models_DbTable_CartSessionContent'
	);
}

