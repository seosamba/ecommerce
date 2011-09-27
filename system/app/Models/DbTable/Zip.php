<?php

/**
 * Zip
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_Zip extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_zone_zip';

	protected $_referenceMap = array(
		'Zone' => array(
			'columns'	=> 'zone_id',
			'refTableClass'	=> 'Models_DbTable_Zone',
			'refColumns'	=> 'id'
			)
	);
}