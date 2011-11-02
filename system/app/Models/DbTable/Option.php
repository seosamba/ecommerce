<?php

/**
 * Option
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_Option extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_product_option';

	protected $_dependentTables = array(
		'Models_DbTable_ProductOption'
	);
}