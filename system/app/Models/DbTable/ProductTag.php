<?php

/**
 * Product to Tags relation table
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_ProductTag extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_product_has_tag';

	protected $_referenceMap = array(
		'Product' => array(
			'columns'		=> 'product_id',
			'refTableClass'	=> 'Models_DbTable_Product',
			'refColumns'	=> 'id'
		),
		'Tags' => array(
			'columns'		=> 'tag_id',
			'refTableClass'	=> 'Models_DbTable_Tag',
			'refColumns'	=> 'id'
		)
	);
}