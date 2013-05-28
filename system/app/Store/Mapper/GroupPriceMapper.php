<?php
/**
 * GroupMapper.php
 *
 *
 * @method Store_Mapper_GroupPriceMapper   getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Store_Mapper_GroupPriceMapper extends Application_Model_Mappers_Abstract {

	protected $_model   = 'Store_Model_GroupPrice';

	protected $_dbTable = 'Store_DbTable_GroupPrice';

	/**
	 * Save coupon model to DB
	 * @param $model Store_Model_GroupPrice
	 * @return Store_Model_GroupPrice
	 */
	public function save($model) {
		if (!$model instanceof $this->_model){
			$model = new $this->_model($model);
		}

        $data = $model->toArray();
		if (isset($data['action'])){
			unset($data['action']);
		}
        unset($data['id']);
        $existGroupForProduct = $this->findByGroupIdProductId($model->getGroupId(), $model->getProductId());
        $where = $this->getDbTable()->getAdapter()->quoteInto('groupId = ?', $model->getGroupId());
        $where .= ' AND '.$this->getDbTable()->getAdapter()->quoteInto('productId = ?', $model->getProductId());
        if(!empty($existGroupForProduct)){
            $this->getDbTable()->update($data, $where);
        }else{
            $this->getDbTable()->insert($data);
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
	 * Delete group price from DB
	 * @return bool Result of operation
	 */
	public function delete($groupId,$productId){
        $groupId = intval($groupId);
        $productId = intval($productId);

		$where = $this->getDbTable()->getAdapter()->quoteInto('groupId = ?', $groupId);
        $where .= ' AND '.$this->getDbTable()->getAdapter()->quoteInto('productId = ?', $productId);
		return (bool) $this->getDbTable()->delete($where);
	}

    public function deleteByGroupId($groupId){
        $groupId = intval($groupId);
        $where = $this->getDbTable()->getAdapter()->quoteInto('groupId = ?', $groupId);
        return (bool) $this->getDbTable()->delete($where);
    }

    public function findByGroupIdProductId($groupId, $productId){
        $where = $this->getDbTable()->getAdapter()->quoteInto('groupId = ?', $groupId);
        $where .= ' AND '.$this->getDbTable()->getAdapter()->quoteInto('productId = ?', $productId);
        return parent::fetchAll($where);
    }

    public function fetchAssocAll(){
        $dbTable = new Store_DbTable_GroupPrice();
        $select = $dbTable->select()->from('shopping_group_price', array('groupPriceKey' => new Zend_Db_Expr("CONCAT(groupId, '_', productId)"), 'productId', 'productId', 'priceValue', 'priceSign', 'priceType'));
        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }
}
