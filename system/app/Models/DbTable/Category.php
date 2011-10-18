<?php

/**
 * Category
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_Category extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_categories';

	protected $_dependentTables = array(
		'Models_DbTable_ProductCategory'
	);
}