<?php
/**
 * CartSessionContent
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_CartSessionContent extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_cart_session_content';

	protected $_referenceMap = array(
		'CartSession' => array(
			'columns'		=> 'cart_id',
			'refTableClass'	=> 'Models_DbTable_CartSession',
			'refColumns'	=> 'id'
		)
	);
}
