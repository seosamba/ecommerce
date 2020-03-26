<?php

class Models_Mapper_ShoppingShippingServiceLabelMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Models_Model_ShippingServiceLabel';

    protected $_dbTable = 'Models_DbTable_ShoppingShippingServiceLabel';

    public function save($data)
    {
        if (!is_array($data) || empty($data)) {
            throw new Exceptions_SeotoasterPluginException('Given parameter should be non empty array');
        }

        if (!isset($data['name']) || empty($data['name'])) {
            throw new Exceptions_SeotoasterPluginException('Given array should contain service name');
        } else {
            $data['name'] = strtolower($data['name']);
        }

        if (isset($data['label']) && !empty($data['label'])) {
            $data['label'] = $data['label'];
        }

        $row = $this->getDbTable()->fetchRow(array('name = ?' => $data['name']));
        if (is_null($row)) {
            $row = $this->getDbTable()->createRow($data);
        } else {
            $row->setFromArray($data);
        }

        try {
            return $row->save();
        } catch (Zend_Exception $e) {
            error_log($e->getTraceAsString());
            error_log($e->getMessage());
            return false;
        }
    }

    public function fetchAll($where = null)
    {
        $results = $this->getDbTable()->fetchAll($where);
        if (sizeof($results)) {
            return $results->toArray();
        }
        return array();
    }

    public function fetchAllAssoc()
    {
        $labels = [];
        $results = $this->getDbTable()->fetchAll();
        if (sizeof($results)) {
            foreach ($results->toArray() as $item) {
                $labels[$item['name']] = $item['label'];
            }
        }
        return $labels;
    }

    public function findByName($name)
    {
        if (!empty($name)) {
            $select = $this->getDbTable()->getAdapter()->select()->from('shopping_shipping_service_label', array(
                'label'
            ))
                ->where('name = ?', $name);

            return $this->getDbTable()->getAdapter()->fetchOne($select);
        }
        return array();
    }

    public function delete($name)
    {
        return $this->getDbTable()->delete($this->getDbTable()->getAdapter()->quoteInto('name = ?', $name));
    }

}