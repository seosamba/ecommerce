<?php
class Models_Mapper_ShoppingShippingUrlMapper extends Application_Model_Mappers_Abstract {

    protected $_model	= 'Models_Model_ShippingUrl';

    protected $_dbTable = 'Models_DbTable_ShoppingShippingUrl';

    public function save($model){

        if (!$model instanceof $this->_model){
            $model = new $this->_model($model);
        }
        $data = array(
            'id'                => $model->getId(),
            'name'              => $model->getName(),
            'url'               => $model->geturl(),
            'default_status'    => $model->getDefaultStatus()
        );
        $userInfo = $this->getDbTable()->find($model->getId());
        if(!$userInfo->current()) {
            unset($data['id']);
           $id = $this->getDbTable()->insert($data);
            return $model->setId($id);
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $model->getId()));
            return $userInfo->toArray();
        }
    }

    public function fetchAll($where = null) {
        $results = $this->getDbTable()->fetchAll($where);
        if (sizeof($results)){
            return $results->toArray();
        }
    }

    public function fetchNames() {
        $select = $this->getDbTable()->select();
        $select->from($this->getDbTable(), array('name'))->order('name ASC');
      return  $this->getDbTable()->getAdapter()->fetchCol($select);
    }

    public function findByName($name) {
        $select = $this->getDbTable()->select();
        $select->where('name = ?', $name);
        $result = $this->getDbTable()->fetchRow($select);
      return $result->toArray();
    }

    public function findById($id) {
        $current = $this->getDbTable()->find($id)->current();
        if (!$current){
            return null;
        }
        $currentData = new $this->_model($current->toArray());
        return $currentData;
    }

    public function findDefaultStatus() {
        $select = $this->getDbTable()->select();
        $select->where('default_status = "1"');
        $result = $this->getDbTable()->fetchRow($select);
        if(is_null($result)){
            return false;
        }
        return $result->toArray();
    }

    public function clearDefaultStatus() {
        $result = array();
        $defaultStatus = $this->findDefaultStatus();
        if(!is_null($defaultStatus)){
            $data = array('default_status' => '0');
            $result = $this->getDbTable()->update($data);
        }
        return $result;
    }

    public function delete(Models_Model_ShippingUrl $model) {
        $result = $this->getDbTable()->delete($this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId()));
        return $result;
    }
}