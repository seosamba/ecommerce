<?php
/**
 * GroupMapper.php
 *
 *
 * @method Store_Mapper_GroupMapper   getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Store_Mapper_GroupMapper extends Application_Model_Mappers_Abstract {

	protected $_model   = 'Store_Model_Group';

	protected $_dbTable = 'Store_DbTable_Group';

	/**
	 * Save coupon model to DB
	 * @param $model Store_Model_Group
	 * @return Store_Model_Group
	 */
	public function save($model) {
		if (!$model instanceof $this->_model){
			$model = new $this->_model($model);
		}

        $data = $model->toArray();
		if (isset($data['action'])){
			unset($data['action']);
		}

        $where = $this->getDbTable()->getAdapter()->quoteInto('`groupName` = ?', $model->getGroupName());
        $existGroup = parent::fetchAll($where);

        if (!empty($existGroup)){
            unset($data['id']);
            $this->getDbTable()->update($data, $where);
        } else {
            $id = $this->getDbTable()->insert($data);
            if ($id){
                $model->setId($id);
            } else {
                throw new Exceptions_SeotoasterException('Can\'t save group');
            }
        }
		return $model;
	}


	public function find($id) {
		$group = parent::find($id);
		return $group;
	}

	public function fetchAll($where = null, $order = array()) {
		$groups = parent::fetchAll($where, $order);
		return $groups;
	}

	/**
	 * Delete group model from DB
	 * @param $model Store_Model_Group Group model
	 * @return bool Result of operation
	 */
	public function delete($model){
		if ($model instanceof $this->_model){
			$id = $model->getId();
		} elseif (is_numeric($model)) {
			$id = intval($model);
		}

		$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);
		return (bool) $this->getDbTable()->delete($where);
	}

    public function fetchAssocAll(){
        $dbTable = new Store_DbTable_Group();
        $select = $dbTable->select()->from('shopping_group', array('id', 'groupName', 'priceSign', 'priceType', 'priceValue'));
        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }

}
