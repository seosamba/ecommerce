<?php

/**
 * ProductRelated
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_ProductRelated extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_product_has_related';
	protected $_primary = array('product_id', 'related_id');


	protected $_referenceMap = array(
		'Product' => array(
			'columns'		=> 'product_id',
			'refTableClass'	=> 'Models_DbTable_Product',
			'refColumns'	=> 'id'
		),
		'Related' => array(
			'columns'		=> 'related_id',
			'refTableClass'	=> 'Models_DbTable_Product',
			'refColumns'	=> 'id'
		)
	);
}