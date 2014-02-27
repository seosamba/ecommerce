<?php

/**
 * Tag
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 * @method Models_Mapper_Tag getInstance() getInstance()  Returns an instance of itself
 * @method Models_DbTable_Tag getDbTable() getDbTable()  Returns an instance of related Db Table
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
		
	public function find($id) {
		$result = $this->getDbTable()->find($id);
		if(0 == count($result)) {
			return null;
		} elseif (count($result) > 1) {
			$list = array();
			foreach ($result as $row) {
				array_push($list, new $this->_model($row->toArray()));
			}
			return $list;
		}

		return new $this->_model( $result->current()->toArray());
	}

	public function fetchAll($where = null, $order = null, $offset = null, $limit = null, $count = false){
		$entries = array();
		$select = $this->getDbTable()->select();
		if ($where) {
			$select->where($where);
		}

		if ($order){
			$select->order($order);
		}

		if ($count === false){
			$select->limit($limit, $offset);
		}

		$resultSet = $this->getDbTable()->fetchAll($select);

		if(count($resultSet) == 0) {
			return null;
		}

		$resultSet = $resultSet->toArray();

		if ($count === true ){
			$count = sizeof($resultSet);
			$resultSet = array_slice($resultSet, $offset, $limit);
		}

		foreach ($resultSet as $model) {
			$entries[] = new $this->_model($model);
		}

		return !$count ? $entries : array(
				'totalCount'    => $count,
				'data'          => $entries
			);
	}

	public function findByName($tagNames, $pairs = false){
		if (!is_array($tagNames)){
			$tagNames = (array) $tagNames;
		}
		$select = $this->getDbTable()->select()
				->where('name IN (?)', $tagNames);

		if ($pairs) {
			return $this->getDbTable()->getAdapter()->fetchPairs($select);
		} else {
			$rows = $this->getDbTable()->fetchAll($select);
			if ($rows->count()){
                return array_map(
                    function ($row) {
                        return new Models_Model_Tag($row);
                    },
                    $rows->toArray()
                );
			} else {
				return array();
			}
		}
	}
}