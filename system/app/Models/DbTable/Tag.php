<?php

/**
 * Tag
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_Tag extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_tags';

	protected $_dependentTables = array(
		'Models_DbTable_ProductTag'
	);
}