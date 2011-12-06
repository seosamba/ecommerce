<?php

/**
 * Brand
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Mapper_Brand extends Application_Model_Mappers_Abstract {

	protected $_model = 'Models_Model_Brand';

	protected $_dbTable = 'Models_DbTable_Brand';
	
	public function save($model) {
		if (!$model instanceof $this->_model){
			$model = new $this->_model($model);
		}
		
		if ($model->getId()){
			$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId());
			$this->getDbTable()->update($model->toArray(), $where);
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

    /**
     * @param $name
     * @return null|Models_Model_Brand
     * @throws Exception
     */
	public function findByName($name){
		if (empty($name)){
			throw new Exception('Name can\'t be empty');
		}
		$row = $this->getDbTable()->fetchRow( $this->getDbTable()->getAdapter()->quoteInto('name = ?', $name) );
		if (count($row) == 0){
			return null;
		}
		return new $this->_model($row->toArray());
	}

    public function delete(Models_Model_Brand $model) {
        return $this->getDbTable()->delete($this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId()));
    }
}