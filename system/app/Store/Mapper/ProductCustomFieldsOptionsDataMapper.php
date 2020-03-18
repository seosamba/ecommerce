<?php

/**
 * Class ProductCustomFieldsOptionsDataMapper
 */
class Store_Mapper_ProductCustomFieldsOptionsDataMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_ProductCustomFieldsOptionsDataModel';

    protected $_dbTable = 'Store_DbTable_ProductCustomFieldsOptionsDataDbTable';

    /**
     * @param $model
     * @return mixed
     */
    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            $model = new $this->_model($model);
        }

        $data = array(
            'custom_param_id' => $model->getCustomParamId(),
            'option_value' => $model->getOptionValue()
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
     * @param int $id custom param id
     * @param bool $pairs fetch in pairs flag
     * @return array|null
     * @throws Exception
     */
    public function findByCustomParamId($id, $pairs = false)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('custom_param_id = ?', $id);

        if ($pairs === true) {
            $select = $this->getDbTable()->getAdapter()->select()->from('shopping_product_custom_params_options_data',
                array('id', 'option_value'));
            $select->where($where);
            return $this->getDbTable()->getAdapter()->fetchPairs($select);
        }
        return $this->fetchAll($where);
    }

    /**
     * @param $paramId
     * @param $optionValue
     * @return null
     */
    public function findOptionIdByCustomParamId($paramId, $optionValue)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('custom_param_id = ?', $paramId);
        $where .= ' AND '. $this->getDbTable()->getAdapter()->quoteInto('option_value = ?', $optionValue);
        return $this->_findWhere($where);
    }


    /**
     * Get all options data custom params
     *
     * @return array
     * @throws Exception
     */
    public function getCustomParamsOptionsDataConfig()
    {
        $select = $this->getDbTable()->getAdapter()->select()->from('shopping_product_custom_params_options_data',
            array('id', 'custom_param_id', 'option_value'));

        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
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

        return $this->getDbTable()->getAdapter()->delete('shopping_product_custom_params_options_data', $where);

    }

}
