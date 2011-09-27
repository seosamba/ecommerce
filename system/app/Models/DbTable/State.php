<?php

/**
 * State
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_State extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_list_state';
	
	protected $_dependentTables = array(
		'Models_DbTable_ZoneState'
	);

}