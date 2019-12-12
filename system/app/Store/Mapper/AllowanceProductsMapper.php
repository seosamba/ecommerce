<?php

class Store_Mapper_AllowanceProductsMapper extends Application_Model_Mappers_Abstract {

    protected $_model   = 'Store_Model_AllowanceProducts';

    protected $_dbTable = 'Store_DbTable_AllowanceProducts';

    public function save($model) {
        if (!$model instanceof $this->_model) {
            throw new Exceptions_SeotoasterPluginException('Wrong model type given.');
        }

        $data = array(
            'product_id' => $model->getProductId(),
            'allowance_due' => $model->getAllowanceDue()
        );

        $recordExists = $this->findByProductId($data['product_id']);

        if ($recordExists instanceof Store_Model_AllowanceProducts) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $model->getProductId());
            $this->getDbTable()->update($data, $where);
        } else {
            $this->getDbTable()->insert($data);
        }

        return $model;
    }


    public function findByProductId($productId) {
        $where = $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $productId);

        return $this->_findWhere($where);
    }

    public function deleteByProductId($productId){
        if(!empty($productId)) {
            $where = $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $productId);
            return (bool) $this->getDbTable()->delete($where);
        }

        return null;
    }

}