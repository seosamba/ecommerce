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
     * Get presets
     *
     * @param string $where SQL where clause
     * @param string $order OPTIONAL An SQL ORDER clause.
     * @param int $limit OPTIONAL An SQL LIMIT count.
     * @param int $offset OPTIONAL An SQL LIMIT offset.
     * @param bool $withoutCount flag to get with or without records quantity
     * @param bool $singleRecord flag fetch single record
     * @return array
     */
    public function fetchAllData(
        $where = null,
        $order = null,
        $limit = null,
        $offset = null,
        $withoutCount = false,
        $singleRecord = false
    ) {

        $select = $this->getDbTable()->getAdapter()->select()
            ->from(array('sg' => 'shopping_group'),
                array(
                    'sg.id',
                    'sg.groupName',
                    'sg.priceSign',
                    'sg.priceType',
                    'sg.priceValue',
                    'sg.nonTaxable'
                )
            );


        $select->group('sg.id');
        if (!empty($order)) {
            $select->order($order);
        }

        if (!empty($where)) {
            $select->where($where);
        }

        $select->limit($limit, $offset);

        if ($singleRecord) {
            $data = $this->getDbTable()->getAdapter()->fetchRow($select);
        } else {
            $data = $this->getDbTable()->getAdapter()->fetchAll($select);
        }

        if ($withoutCount === false) {
            $select->reset(Zend_Db_Select::COLUMNS);
            $select->reset(Zend_Db_Select::FROM);
            $select->reset(Zend_Db_Select::LIMIT_OFFSET);
            $select->reset(Zend_Db_Select::LIMIT_COUNT);

            $count = array('count' => new Zend_Db_Expr('COUNT(DISTINCT(sg.id))'));

            $select->from(array('sg' => 'shopping_group'), $count);


            $select = $this->getDbTable()->getAdapter()->select()
                ->from(
                    array('subres' => $select),
                    array('count' => 'SUM(count)')
                );

            $count = $this->getDbTable()->getAdapter()->fetchRow($select);

            if (empty($count['count'])) {
                $count['count'] = 0;
            }

            return array(
                'totalRecords' => $count['count'],
                'data' => $data,
                'offset' => $offset,
                'limit' => $limit
            );
        } else {
            return $data;
        }
    }

    /**
     * Find by group name
     *
     * @param string $groupName group name
     * @return Store_Model_Group|null
     */
    public function findByGroupName($groupName)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('groupName = ?', $groupName);
        return $this->_findWhere($where);
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
        $select = $dbTable->select()->from('shopping_group', array('id', 'groupName', 'priceSign', 'priceType', 'priceValue', 'nonTaxable'));
        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }

    public function fetchPairs(){
        $dbTable = new Store_DbTable_Group();
        $select = $dbTable->select()->from('shopping_group', array('id', 'groupName'));
        return $this->getDbTable()->getAdapter()->fetchPairs($select);
    }

    public function fetchGroupList() {
        $dbTable = new Store_DbTable_Group();
        $select = $dbTable->select()->from('shopping_group', array('id', 'groupName'));
        return $this->getDbTable()->getAdapter()->fetchPairs($select);
    }

}
