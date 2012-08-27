<?php
/**
 * ShippingConfigMapper.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 *
 * @method Models_Mapper_ShippingConfigMapper getInstance() getInstance()  Returns an instance of itself
 * @method Zend_Db_Table getDbTable() getDbTable()  Returns an instance of Zend_Db_Table
 */
class Models_Mapper_ShippingConfigMapper extends Application_Model_Mappers_Abstract {

	const STATUS_ENABLED = '1';

	const STATUS_DISABLED = '0';

	protected  function __construct(){
		$this->_dbTable = new Zend_Db_Table('shopping_shipping_config');
	}

	public function save($plugin) {
		if (!is_array($plugin) || empty($plugin)){
			throw new Exceptions_SeotoasterPluginException('Given parameter should be non empty array');
		}

		if (!isset($plugin['name']) || empty($plugin['name'])){
			throw new Exceptions_SeotoasterPluginException('Given array should contain plugin name');
		} else {
			$plugin['name'] = strtolower($plugin['name']);
		}

		if (isset($plugin['config']) && !empty($plugin['config'])){
			$plugin['config'] = serialize($plugin['config']);
		}

		$row = $this->getDbTable()->fetchRow(array('name = ?' => $plugin['name']));
		if (is_null($row)){
			$row =  $this->getDbTable()->createRow($plugin);
		} else {
			$row->setFromArray($plugin);
		}

		try {
			return $row->save();
		} catch (Zend_Exception $e){
			error_log($e->getTraceAsString());
			error_log($e->getMessage());
			return false;
		}
	}

	public function find($name){
		$row = $this->getDbTable()->fetchRow(array('name = ?' => $name));
		if ($row){
			return $this->_prepareRow($row->toArray());
		}
	}

	public function fetchByStatus($status){
		return $this->fetchAll($this->getDbTable()->getAdapter()->quoteInto('enabled = ?', $status));
	}

	public function fetchAll($where = null, $order = array()) {
		$results = $this->getDbTable()->fetchAll($where, $order);
		if (sizeof($results)){
			return array_map(array($this, '_prepareRow'), $results->toArray());
		}
	}

	private function _prepareRow($row){
		if (!is_null($row['config'])){
			$conf = @unserialize($row['config']);
			$row['config'] = $conf !== false ? $conf : null ;
			unset($conf);
		}
		$row['enabled'] = intval($row['enabled']);
		return $row;
	}

}
