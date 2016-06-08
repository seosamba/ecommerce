<?php

/**
 * Class Models_DbTable_CartSessionDiscount
 */
class Models_DbTable_CartSessionDiscount extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_cart_session_discount';

	protected $_referenceMap = array(
		'CartSession' => array(
			'columns'		=> 'cart_id',
			'refTableClass'	=> 'Models_DbTable_CartSession',
			'refColumns'	=> 'id'
		)
	);
}
