<?php

/**
 * Tax
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Mapper_Tax extends Application_Model_Mappers_Abstract{

	protected $_dbTable	= 'Models_DbTable_Tax';

	protected $_model	= 'Models_Model_Tax';

	public function save($model) {
		if (!$model instanceof $this->_model){
			$model = new $this->_model($model);
		}
		
		if ($model->getId()){
			$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId());
			$this->getDbTable()->update($model->toArray(), $where);
		} else {
			$this->getDbTable()->insert($model->toArray());
		}
		
		return true;
	}

	public function findByZoneId($zoneId) {
		$where = $this->getDbTable()->getAdapter()->quoteInto('zoneId=?', $zoneId);
		return $this->_findWhere($where);
	}

	public function getDefaultRule(){
		return $this->_findWhere("isDefault='1'");
	}

	public function delete($id) {
		if (is_array($id)){
			foreach ($id as $_id){
				$this->delete($_id);
			}
		}
		
		$entity = $this->getDbTable()->find($id);
		
		if ($entity = $entity->current()){
			return $entity->delete();
		}
		return null;
	}
}