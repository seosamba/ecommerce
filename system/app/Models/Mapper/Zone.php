<?php

/**
 * Zone
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Mapper_Zone extends Application_Model_Mappers_Abstract {

	protected $_dbTable = 'Models_DbTable_Zone';
	protected $_model	= 'Models_Model_Zone';


	public function save($model) {
        if (!$model instanceof $this->_model){
            $model = new $this->_model($model);
        }
		$data = array(
			'name' => $model->getName()
		);
		if ($model->getId() === null){
			$result = $this->getDbTable()->insert($data);
            $model->setId($result);
		} else {
			$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId());
			$result = (bool) $this->getDbTable()->update($data, $where);
		}
		
		$this->_updateCountries($model);
		$this->_updateStates($model);
		$this->_updateZip($model);
		
		return $result;
	}

	public function find($id) {
		$result = $this->getDbTable()->find($id);
		if ($result->count() == 0){
			return null;
		}
		
		$data = $result->current();
		
		$zone = new $this->_model($data->toArray());
			
		$zoneCountries = $data->findManyToManyRowset('Models_DbTable_Country','Models_DbTable_ZoneCountry');
		$zone->setCountries($zoneCountries->toArray());
		
		$zoneStates = $data->findManyToManyRowset('Models_DbTable_State','Models_DbTable_ZoneState');
		$zone->setStates($zoneStates->toArray());
		
		$zoneZip = $data->findDependentRowset('Models_DbTable_Zip');
		$zone->setZip($zoneZip->toArray());
		
		return $zone->toArray();
	}
	
	public function fetchAll($where = null, $order = array()) {
		$entries = array();
		$resultSet = $this->getDbTable()->fetchAll($where, $order);
		if(null !== $resultSet) {
			foreach ($resultSet as $row) {
				$zone = new $this->_model($row->toArray());
				$zone->setCountries($row->findManyToManyRowset('Models_DbTable_Country','Models_DbTable_ZoneCountry')->toArray());
				$zone->setStates($row->findManyToManyRowset('Models_DbTable_State','Models_DbTable_ZoneState')->toArray());
				$zone->setZip($row->findDependentRowset('Models_DbTable_Zip')->toArray());
				$entries[] = $zone;
				unset ($zone);
			}
		}
		return $entries;
	}

	public function createModel($data = null){
		return new $this->_model($data);
	}
	
	private function _updateCountries($zone){
		$countryTable = new Models_DbTable_Country();
		$newCountries = $zone->getCountries();
        $newCodesList = array();
        foreach ($newCountries as $country) {
            array_push($newCodesList, $country['country']);
        }

        if (!empty($newCodesList)){
			$sql = $countryTable->getAdapter()->select()->from($countryTable->info('name'))
				->where('country in (?)', $newCodesList);
			$list = $countryTable->getAdapter()->fetchAll($sql);
		} else {
			$list = array();
		}
			
		$zoneCountryTable = new Models_DbTable_ZoneCountry();
		$zoneCountryTable->delete($zoneCountryTable->getAdapter()->quoteInto('zone_id = ?', $zone->getId()));
		if (!empty ($list)) {
			$zoneCountryTable->getAdapter()->beginTransaction();
			foreach ($list as $country) {
				$zoneCountryTable->insert(array(
					'zone_id'	 => $zone->getId(),
					'country_id' =>	$country['id']
				));
			}
			$zoneCountryTable->getAdapter()->commit();
		}
		
		return $this;
	}
	
	private function _updateStates($zone){
		$states = $zone->getStates();
		
		$zoneStateTable = new Models_DbTable_ZoneState();
		
		$zoneStateTable->delete($zoneStateTable->getAdapter()->quoteInto('zone_id = ?', $zone->getId()));
		if (!empty($states)){
			$zoneStateTable->getAdapter()->beginTransaction();
			foreach ($states as $state) {
				$zoneStateTable->insert(array(
					'zone_id'	=> $zone->getId(),
					'state_id'	=> $state['id']
				));
			}
			$zoneStateTable->getAdapter()->commit();
		}
		
		return $this;
	}
	
	private function _updateZip($zone) {
		$zip = $zone->getZip();
		$zoneZipTable = new Models_DbTable_Zip();
		
		$zoneZipTable->delete($zoneZipTable->getAdapter()->quoteInto('zone_id = ?', $zone->getId()));
		if (!empty ($zip)){
			$zoneZipTable->getAdapter()->beginTransaction();
			foreach ($zip as $code){
				$zoneZipTable->insert(array(
					'zone_id'	=> $zone->getId(),
					'zip'		=> $code
				));
			}
			$zoneZipTable->getAdapter()->commit();
		}
		
		return $this;
	}
	
	public function delete($id) {
		$result = array();
		$rowset = $this->getDbTable()->find($id);
		foreach ($rowset as $row) {
			$result[$row->id] = $row->delete();
		}
		return $result;
	}
}