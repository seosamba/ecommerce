<?php

/**
 * Tax
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_Tax extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_tax';

	protected $_dependentTables = array(
		'Models_DbTable_Zone'
	);
	
	protected $_referenceMap = array(
		'Zone' => array(
			'columns'	=> 'zoneId',
			'refTableClass'	=> 'Models_DbTable_Zone',
			'refColumns'	=> 'id'
			)
	);
}