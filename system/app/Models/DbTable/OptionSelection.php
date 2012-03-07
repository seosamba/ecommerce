<?php

/**
 * ProductOption
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_OptionSelection extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_product_option_has_selection';

	protected $_referenceMap = array(
		'Option' => array(
			'columns'		=> 'option_id',
			'refTableClass'	=> 'Models_DbTable_Option',
			'refColumns'	=> 'id'
		),
		'Selection' => array(
			'columns'		=> 'selection_id',
			'refTableClass'	=> 'Models_DbTable_Selection',
			'refColumns'	=> 'id'
		)
	);
}