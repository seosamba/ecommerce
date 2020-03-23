<?php

class Store_Mapper_ProductCustomParamsDataMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_ProductCustomParamsDataModel';

    protected $_dbTable = 'Store_DbTable_ProductCustomParamsDataDbTable';

    /**
     * Save product custom params config model to DB
     *
     * @param $model Store_Model_ProductCustomParamsDataModel
     * @return Store_Model_ProductCustomParamsDataModel
     * @throws Exception
     */
    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            $model = new $this->_model($model);
        }

        $data = array(
            'param_id' => $model->getParamId(),
            'product_id' => $model->getProductId(),
            'param_value' => $model->getParamValue(),
            'params_option_id' => $model->getParamsOptionId()
        );

        $paramExists = $this->checkIfParamExists($data['product_id'], $data['param_id']);
        if ($paramExists instanceof Store_Model_ProductCustomParamsDataModel) {
            $id = $paramExists->getId();
            $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);
            $this->getDbTable()->update($data, $where);
        } else {
            $id = $this->getDbTable()->insert($data);
            $model->setId($id);
        }

        return $model;
    }

    /**
     * @param $id
     * @return null
     */
    public function findById($id)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);

        return $this->_findWhere($where);
    }

    /**
     * Check if param already exists
     *
     * @param int $productId product id
     * @param int $paramId custom param id
     * @return Store_Model_ProductCustomParamsDataModel
     * @throws Exception
     */
    public function checkIfParamExists($productId, $paramId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $productId);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('param_id = ?', $paramId);

        return $this->_findWhere($where);
    }

    /**
     * Find product custom params by id
     *
     * @param int $productId product id
     * @return mixed
     * @throws Exception
     */
    public function findByProductId($productId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $productId);
        $select = $this->getDbTable()->getAdapter()->select()->from(array('spcpd' => 'shopping_product_custom_params_data'),
            array('spcpd.id', 'spcpd.param_id', 'spcpd.product_id', 'spcpd.param_value', 'spcpd.params_option_id', 'spcfc.param_type', 'spcfc.param_name'))
            ->join(array('spcfc' => 'shopping_product_custom_fields_config'),
                'spcfc.id=spcpd.param_id', array());

        $select->where($where);

        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }

    /**
     * Find product custom params by id
     *
     * @param int $productId product id
     * @return mixed
     * @throws Exception
     */
    public function findByProductIdAggregated($productId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('product_id = ?', $productId);
        $select = $this->getDbTable()->getAdapter()->select()->from(array('spcpd' => 'shopping_product_custom_params_data'),
            array('key' => new Zend_Db_Expr('CONCAT(spcfc.param_type,"_", spcfc.param_name)'),
                'spcpd.id',
                'spcpd.param_value',
                'option_val' => 'spcpod.option_value'
            ))
            ->join(array('spcfc' => 'shopping_product_custom_fields_config'),
                'spcfc.id=spcpd.param_id', array())
            ->joinLeft(array('spcpod' => 'shopping_product_custom_params_options_data'),
                'spcpod.id=spcpd.params_option_id', array());

        $select->where($where);

        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }

    /**
     * Delete record
     *
     * @param int $id id
     * @return mixed
     * @throws Exception
     */
    public function delete($id)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);

        return $this->getDbTable()->getAdapter()->delete('shopping_product_custom_params_data', $where);

    }

}
