<?php

/**
 * Brand
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_Brand extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_brands';

	protected $_referenceMap = array(
		'Product' => array(
			'columns'		=> 'id',
			'refTableClass'	=> 'Models_DbTable_Product',
			'refColumns'	=> 'brand_id'
			
		)
	);
}