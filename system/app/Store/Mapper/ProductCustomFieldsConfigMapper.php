<?php

class Store_Mapper_ProductCustomFieldsConfigMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_ProductCustomFieldsConfigModel';

    protected $_dbTable = 'Store_DbTable_ProductCustomFieldsConfigDbTable';

    /**
     * Save product custom params config model to DB
     * @param $model Store_Model_ProductCustomFieldsConfigModel
     * @return Store_Model_ProductCustomFieldsConfigModel
     */
    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            $model = new $this->_model($model);
        }

        $data = array(
            'param_type' => $model->getParamType(),
            'param_name' => $model->getParamName(),
            'label' => $model->getLabel()
        );

        $id = $model->getId();
        if (!empty($id)) {
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
     * Get product custom param config by param type and name
     *
     * @param string $paramType param type
     * @param string $paramName param name
     * @return null
     */
    public function getByTypeName($paramType, $paramName)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('param_type = ?', $paramType);
        $where .= ' AND ' . $this->getDbTable()->getAdapter()->quoteInto('param_name = ?', $paramName);

        return $this->_findWhere($where);
    }

    /**
     * Get product custom param config by param name
     *
     * @param string $paramName param name
     * @return null
     */
    public function getByName($paramName)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('param_name = ?', $paramName);

        return $this->_findWhere($where);
    }


    public function fetchAll(
        $where = null,
        $order = null,
        $limit = null,
        $offset = null,
        $withoutCount = false,
        $singleRecord = false
    ) {
        $select = $this->getDbTable()->getAdapter()->select()
            ->from(array('spcfc' => 'shopping_product_custom_fields_config'),
                array(
                    'spcfc.id',
                    'spcfc.param_name',
                    'spcfc.param_type',
                    'spcfc.label',
                    'option_values' => new Zend_Db_Expr('GROUP_CONCAT(spcpod.option_value)'),
                    'option_ids' => new Zend_Db_Expr('GROUP_CONCAT(spcpod.id)')
                )
            )
            ->joinLeft(array('spcpod' => 'shopping_product_custom_params_options_data'),
                'spcpod.custom_param_id = spcfc.id', array())
            ->group('spcfc.id');
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
            $select->reset(Zend_Db_Select::GROUP);

            $select->from(array('spcfc' => 'shopping_product_custom_fields_config'),
                array('count' => 'COUNT(spcfc.id)'));
            $count = $this->getDbTable()->getAdapter()->fetchRow($select);

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
     * Get all custom params
     *
     * @return array
     */
    public function getCustomParamsConfig()
    {
        $select = $this->getDbTable()->getAdapter()->select()->from('shopping_product_custom_fields_config',
            array('id', 'param_type', 'param_name', 'label'));

        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }

    /**
     * Get all custom params
     *
     * @return array
     */
    public function getCustomParamsPairs()
    {
        $select = $this->getDbTable()->getAdapter()->select()->from('shopping_product_custom_fields_config',
            array('id', 'label'));

        return $this->getDbTable()->getAdapter()->fetchPairs($select);
    }


    /**
     * Delete lead record
     *
     * @param int $id lead id
     * @return mixed
     * @throws Exception
     */
    public function delete($id)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);

        return $this->getDbTable()->getAdapter()->delete('shopping_product_custom_fields_config', $where);

    }

}
