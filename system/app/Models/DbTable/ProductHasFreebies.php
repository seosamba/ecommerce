<?php

/**
 * ProductFreebies
 *
 */
class Models_DbTable_ProductHasFreebies extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_product_has_freebies';
	protected $_primary = array('product_id', 'freebies_id');


	protected $_referenceMap = array(
		'Product' => array(
			'columns'		=> 'product_id',
			'refTableClass'	=> 'Models_DbTable_Product',
			'refColumns'	=> 'id'
		),
		'Freebies' => array(
			'columns'		=> 'freebies_id',
			'refTableClass'	=> 'Models_DbTable_Product',
			'refColumns'	=> 'id'
		)
	);
}