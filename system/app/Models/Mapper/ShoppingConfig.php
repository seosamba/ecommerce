<?php

/**
 * ShoppingConfig Mapper
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 *
 * @method Models_Mapper_ShoppingConfig getInstance() getInstance()  Returns an instance of itself
 */
class Models_Mapper_ShoppingConfig extends Application_Model_Mappers_Abstract {

	protected $_dbTable = 'Models_DbTable_ShoppingConfig';
	
	public function save($plugin) {
		if (!is_array($plugin) || empty ($plugin)){
			throw new Exceptions_SeotoasterPluginException('Given parameter should be non empty array');
		}
		
		array_walk($plugin, function($value, $key, $dbTable){
			$dbTable->updateParam($key, $value, array('state'));
		}, $this->getDbTable());
		
		return true;
	}

    /**
     * Fetch all shopping config params as array
     * @return array
     */
	public function getConfigParams() {
		return $this->getDbTable()->selectConfig();
	}

    /**
     * Fetch from shopping config parameter by given name
     * @param $name Name of parameter
     * @return null|string Value of parameter
     */
	public function getConfigParam($name) {
		if (!$name) {
			return null;
		}
		
		$row = $this->getDbTable()->find($name);
		if ($row = $row->current()){
			return $row->value;
		}
		return null;
	}
}