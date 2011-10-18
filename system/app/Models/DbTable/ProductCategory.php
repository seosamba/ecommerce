<?php

/**
 * ProductCategory
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_ProductCategory extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_product_category';

	protected $_referenceMap = array(
		'Product' => array(
			'columns'		=> 'product_id',
			'refTableClass'	=> 'Models_DbTable_Product',
			'refColumns'	=> 'id'
		),
		'Category' => array(
			'columns'		=> 'category_id',
			'refTableClass'	=> 'Models_DbTable_Category',
			'refColumns'	=> 'id'
		)
	);
}