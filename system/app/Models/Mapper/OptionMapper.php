<?php
/**
 * Models_Mapper_OptionMapper
 *
 * @method Models_Mapper_OptionMapper getInstance() getInstance() Returns an instance of itself
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Mapper_OptionMapper extends Application_Model_Mappers_Abstract {

	protected $_model = 'Models_Model_Option';
	
	protected $_dbTable = 'Models_DbTable_Option';

    /**
     * Method saves model to DB
     * @param $model Models_Model_Option
     * @return Models_Model_Option
     */
	public function save($model){
		if (! $model instanceof  $this->_model){
			$model = new $this->_model($model);
		}
		
		$data = array(
			'title'     => $model->getTitle(),
			'type'	    => $model->getType(),
            'parentId'  => $model->getParentId()
		);
		
		if ($model->getId()){
			$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId());
			$result = $this->getDbTable()->update($data, $where);
		} else {
			$id = $this->getDbTable()->insert($data);
			$model->setId($id);
		}

		if ($model->getSelection()){
			$this->_proccessSelection($model);
		}
		
		return $model;
	}
	
	public function find($id, $array = false) {
		$result = $this->getDbTable()->find($id);
		if(0 === count($result)) {
			return null;
		}
        $models = array();
        foreach ($result as $row) {
            $model = new $this->_model($row->toArray());

            if ($model->getType() === $model::TYPE_DROPDOWN || $model->getType() === $model::TYPE_RADIO) {
                //			$selections = $row->findDependentRowset('Models_DbTable_Selection', 'Models_DbTable_OptionSelection');
                $selections = $row->findDependentRowset('Models_DbTable_Selection');
                if ($selections->count()){
                    $model->setSelection($selections->toArray());
                }
            }

            array_push($models, $array ? $model->toArray() : $model );
        }

		return $models;
	}

	private function _proccessSelection(Models_Model_Option $model){
		$selectionTable = new Models_DbTable_Selection();
		$selectionTable->getAdapter()->beginTransaction();

        $selectionList = $model->getSelection();
		foreach ($selectionList as &$item) {
			$data = array(
				'option_id'		=> $model->getId(),
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
				$id = $selectionTable->insert($data);
				if ($id) {
					$item['id'] = $id;
				}
			}
		}
		$model->setSelection($selectionList);

		return $selectionTable->getAdapter()->commit();
	}

    public function fetchAll($where = null, $order = array(), $objects = true) {
        $entries = array();
        $resultSet = $this->getDbTable()->fetchAll($where, $order);
        if(null === $resultSet) {
            return null;
        }
        foreach ($resultSet as $row) {
            $model = new $this->_model($row->toArray());

            if ($model->getType() === $model::TYPE_DROPDOWN || $model->getType() === $model::TYPE_RADIO) {
                $selections = $row->findDependentRowset('Models_DbTable_Selection');
                if ($selections->count()){
                    $model->setSelection($selections->toArray());
                }
            }

            array_push($entries, $objects ? $model : $model->toArray());
        }
        return $entries;
    }


}