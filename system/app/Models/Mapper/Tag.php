<?php

/**
 * Tag
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Mapper_Tag extends Application_Model_Mappers_Abstract {

	protected $_model = 'Models_Model_Tag';

	protected $_dbTable = 'Models_DbTable_Tag';
	
	public function save($model) {
		if (!$model instanceof $this->_model){
			$model = new $this->_model($model);
		}
		
		if ($model->getId()){
			$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId());
			try {
				$result = $this->getDbTable()->update($model->toArray(), $where);
			} catch (Exception $e) {
				error_log($e->getMessage());
				return null;
			}
		} else {
			try{
				$id = $this->getDbTable()->insert($model->toArray());
			} catch (Exception $e){
				error_log($e->getMessage());
				return null;
			}
			if ($id){
				$model->setId($id);
			}
		}
		
		return $model;
	}

	public function delete($id){
		$result = $this->getDbTable()->find($id);
		if ($result->count()){
			$result = $result->current();
			if ($result) {
				$relations = $result->findDependentRowset('Models_DbTable_ProductTag');
				if ($relations->count()){
					foreach ($relations as $relation) {
						$relation->delete();
					}
				}
				return $result->delete();
			}
		}
		return false;
	}
		

}