<?php
class Models_Mapper_ShoppingShippingUrlMapper extends Application_Model_Mappers_Abstract {

    protected  function __construct(){
        $this->_dbTable = new Zend_Db_Table('shopping_shipping_url');
    }

    public function save($shippingUrl){
        $row = $this->getDbTable()->fetchRow(array('name = ?' => $shippingUrl['name']));
        if (is_null($row)){
            $row =  $this->getDbTable()->createRow($shippingUrl);
        } else {
            $row->setFromArray($shippingUrl);
        }
        $result = $this->getDbTable()->find($shippingUrl['name']);
        if ($result->count() == 1){
            $row->save();
            return false;
        }
        try {
            return $row->save();
        } catch (Zend_Exception $e){
            error_log($e->getTraceAsString());
            error_log($e->getMessage());
            return false;
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