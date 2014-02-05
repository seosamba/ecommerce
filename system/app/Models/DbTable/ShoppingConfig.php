<?php

/**
 * ShoppingConfig
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_DbTable_ShoppingConfig extends Zend_Db_Table_Abstract {

	protected $_name = 'shopping_config';
	
	public function updateParam($name, $value, $allowSaveNull = array()) {
		if ($value === null && !in_array($name, $allowSaveNull)) {
			return false;
		}
		$rowset = $this->find($name);
		$row = $rowset->current();
		if ($row === null) {
			$row = $this->createRow( array(
				'name'	=> $name,
				'value' => $value
			));			
		} else {
			$row->value = $value;
		}

		return $row->save();
	}

	public function selectConfig() {
		return $this->getAdapter()->fetchPairs('SELECT `name`, `value` from `'. $this->_name.'`');
	}
}