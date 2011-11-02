<?php

/**
 * Option
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Mapper_Option extends Application_Model_Mappers_Abstract {
	
	protected $_model = 'Models_Model_Option';
	
	protected $_dbTable = 'Models_DbTable_Option';
	
	public function save($model){
		if (! $model instanceof  $this->_model){
			if (isset($model['selection']) && !empty ($model['selection'])){
				$selection = $model['selection'];
				unset ($model['selection']);
			}
			$model = new $this->_model($model);
		}
		
		$data = array(
			'title' => $model->getTitle(),
			'type'	=> $model->getType()
		);
		
		if ($model->getId()){
			$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId());
			$result = $this->getDbTable()->update($data, $where);
		} else {
			$id = $this->getDbTable()->insert($data);
			$model->setId($id);
		}
		
		if (isset($selection)){
			$this->_proccessSelection($model->getId(), $selection);
		}
		
		return $model;
	}
	
	public function find($id) {
		if (is_array($id) && !empty ($id)){
			foreach ($id as $i) {
				$this->find($i);
			}
		}
		
		$result = $this->getDbTable()->find($id);
		if(0 == count($result)) {
			return null;
		}
		$row = $result->current();
		$model = new $this->_model($row->toArray());
		
		if ($model->getType() === $model::TYPE_DROPDOWN || $model->getType() === $model::TYPE_RADIO) {
//			$selections = $row->findDependentRowset('Models_DbTable_Selection', 'Models_DbTable_OptionSelection');
			$selections = $row->findDependentRowset('Models_DbTable_Selection');
			if ($selections->count()){
				$model->setSelection($selections->toArray());
			}
		}
		
		return $model;
	}

	public function _proccessSelection($modelId, array $selectionList){
		$selectionTable = new Models_DbTable_Selection();
		$selectionTable->getAdapter()->beginTransaction();
		
		foreach ($selectionList as $item) {
			$data = array(
				'option_id'		=> $modelId,
				'title'			=> $item['title'],
				'priceSign'		=> $item['priceSign'],
				'priceValue'	=> $item['priceValue'],
				'priceType'		=> $item['priceType'],
				'weightValue'	=> $item['weightValue'],
				'weightSign'	=> $item['weightSign'],
				'isDefault'		=> $item['isDefault']
			);
			if (isset($item['id'])) {
				$where = $selectionTable->getAdapter()->quoteInto('id = ?', $item['id']);
				if (isset($item['_deleted']) && $item['_deleted'] == true){
					$selectionTable->delete($where);
					continue;
				}
				$selectionTable->update($data, $where);
			} else {
				$selectionTable->insert($data);
			}
		}
		
		return $selectionTable->getAdapter()->commit();
	}
}