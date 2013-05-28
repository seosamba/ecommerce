<?php

/**
 * Option
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_Selection extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_product_option_selection';

	protected $_dependentTables = array(
//		'Models_DbTable_OptionSelection'
	);
	
	protected $_referenceMap = array(
		'Option' => array(
			'columns'		=> 'option_id',
			'refTableClass'	=> 'Models_DbTable_Option',
			'refColumns'	=> 'id'
			
		)
	);
}