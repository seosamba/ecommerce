<?php

/**
 * Category
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Mapper_Category extends Application_Model_Mappers_Abstract {

	protected $_model = 'Models_Model_Category';

	protected $_dbTable = 'Models_DbTable_Category';
	
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
			
			return $result->delete();
		}
		return false;
	}
		

}