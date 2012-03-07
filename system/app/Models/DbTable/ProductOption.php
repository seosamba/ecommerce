<?php

/**
 * ProductOption
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_ProductOption extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_product_has_option';
	protected $_primary = array('product_id', 'option_id');
	
	protected $_referenceMap = array(
		'Product' => array(
			'columns'		=> 'product_id',
			'refTableClass'	=> 'Models_DbTable_Product',
			'refColumns'	=> 'id'
		),
		'Option' => array(
			'columns'		=> 'option_id',
			'refTableClass'	=> 'Models_DbTable_Option',
			'refColumns'	=> 'id'
		)
	);
}