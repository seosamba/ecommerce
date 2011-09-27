<?php

/**
 * ZoneState
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_ZoneState extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_zone_state';
	
	protected $_referenceMap = array(
		'Zone' => array(
			'columns'		=> 'zone_id',
			'refTableClass'	=> 'Models_DbTable_Zone',
			'refColumns'	=> 'id'
		),
		'State' => array(
			'columns'		=> 'state_id',
			'refTableClass'	=> 'Models_DbTable_State',
			'refColumns'	=> 'id'
		)
	);

}