<?php

/**
 * ZoneCountry
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_ZoneCountry extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_zone_country';
	
	protected $_referenceMap = array(
		'Zone' => array(
			'columns'		=> 'zone_id',
			'refTableClass'	=> 'Models_DbTable_Zone',
			'refColumns'	=> 'id'
		),
		'Country' => array(
			'columns'		=> 'country_id',
			'refTableClass'	=> 'Models_DbTable_Country',
			'refColumns'	=> 'id'
		)
	);

}