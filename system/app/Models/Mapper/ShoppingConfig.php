<?php

/**
 * ShoppingConfig Mapper
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Mapper_ShoppingConfig extends Application_Model_Mappers_Abstract {

	protected $_dbTable = 'Models_DbTable_ShoppingConfig';
	
	public function save($config) {
		if (!is_array($config) || empty ($config)){
			throw new Exceptions_SeotoasterPluginException('Given parameter should be non empty array');
		}
		
		array_walk($config, function($value, $key, $dbTable){
			$dbTable->updateParam($key, $value);
		}, $this->getDbTable());
		
		return true;
	}
	
	public function getConfig() {
		return $this->getDbTable()->selectConfig();
	}
}