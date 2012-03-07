<?php

/**
 * Product
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_Product extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_product';
	
	protected $_dependentTables = array(
		'Models_DbTable_Brand',
		'Models_DbTable_ProductTag',
		'Models_DbTable_ProductOption',
		'Models_DbTable_ProductRelated'
	);
}