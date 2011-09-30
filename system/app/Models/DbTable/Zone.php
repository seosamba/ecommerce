<?php

/**
 * Zone
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_Zone extends Zend_Db_Table_Abstract {
	
	protected $_name = 'shopping_zone';
//	protected $_name = 'shopping_zones';
	
	protected $_dependentTables = array(
		'Models_DbTable_ZoneCountry',
		'Models_DbTable_ZoneState',
		'Models_DbTable_Zip',
		'Models_DbTable_Tax'
	);
	
	protected $_referenceMap = array(
		'Tax' => array(
			'columns'	=> 'id',
			'refTableClass'	=> 'Models_DbTable_Tax',
			'refColumns'	=> 'zoneId'
			)
	);
}