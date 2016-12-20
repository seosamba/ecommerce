<?php

class Models_Mapper_ShoppingShippingUrlMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Models_Model_ShippingUrl';

    protected $_dbTable = 'Models_DbTable_ShoppingShippingUrl';

    public function save($model)
    {

        if (!$model instanceof $this->_model) {
            $model = new $this->_model($model);
        }
        $data = array(
            'id' => $model->getId(),
            'name' => $model->getName(),
            'url' => $model->geturl(),
            'default_status' => $model->getDefaultStatus()
        );
        $userInfo = $this->getDbTable()->find($model->getId());
        if (!$userInfo->current()) {
            unset($data['id']);
            $id = $this->getDbTable()->insert($data);

            return $model->setId($id);
        }
        $this->getDbTable()->update($data, array('id = ?' => $model->getId()));

        return $model;

    }

    public function fetchAll($where = null)
    {
        $results = $this->getDbTable()->fetchAll($where);
        if (sizeof($results)) {
            return $results->toArray();
        }
        return array();
    }

    public function findByName($name)
    {
        $select = $this->getDbTable()->select();
        $select->where('name = ?', $name);
        $result = $this->getDbTable()->fetchRow($select);
        if(is_null($result)){
            return null;
        }
        $currentData = new $this->_model($result->toArray());

        return $currentData;
    }

    public function findDefaultStatus()
    {
        $select = $this->getDbTable()->select();
        $select->where('default_status = "1"');
        $result = $this->getDbTable()->fetchRow($select);
        if (is_null($result)) {
            return null;
        }
        $currentData = new $this->_model($result->toArray());

        return $currentData;
    }

    public function clearDefaultStatus()
    {
        $result = array();
        $defaultStatus = $this->findDefaultStatus();
        if (!is_null($defaultStatus)) {
            $data = array('default_status' => '0');
            $result = $this->getDbTable()->update($data);
        }

        return $result;
    }

    public function delete(Models_Model_ShippingUrl $model)
    {
        $result = $this->getDbTable()->delete($this->getDbTable()->getAdapter()->quoteInto('id = ?', $model->getId()));

        return $result;
    }
}