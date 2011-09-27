<?php

/**
 * Country
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_Country extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_list_country';
	
	protected $_dependentTables = array(
		'Models_DbTable_ZoneCountry'
	);
	
}