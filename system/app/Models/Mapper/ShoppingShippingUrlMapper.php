<?php
class Models_Mapper_ShoppingShippingUrlMapper extends Application_Model_Mappers_Abstract {

    protected  function __construct(){
        $this->_dbTable = new Zend_Db_Table('shopping_shipping_url');
    }
    protected $_model	= 'Models_Model_ShippingUrl';

    public function save($model){

        if (!$model instanceof $this->_model){
            $model = new $this->_model($model);
        }

        $data = array(
            'id'                =>$model->getId(),
            'name'              => $model->getName(),
            'url'               => $model->geturl(),
            'default_status'    => $model->getDefaultStatus()
        );
        $userInfo = $this->getDbTable()->find($model->getId());
        if(!$userInfo->current()) {
            unset($data['id']);
           $id = $this->getDbTable()->insert($data);
            return $id;
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $model->getId()));
            return $model->getId();
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

    public function delete($name) {
        $result = array();
        $rowset = $this->getDbTable()->find($name);
        foreach ($rowset as $row) {
            $result[$row->name] = $row->delete();
        }
        return $result;
    }
}