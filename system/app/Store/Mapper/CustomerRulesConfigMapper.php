<?php

/**
 * CustomerRulesConfigMapper.php
 *
 *
 * @method Store_Mapper_CustomerRulesConfigMapper  getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Store_Mapper_CustomerRulesConfigMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_CustomerRulesConfigModel';

    protected $_dbTable = 'Store_DbTable_CustomerRulesConfigDbTable';

    /**
     * Save model to DB
     * @param $model Store_Model_CustomerRulesConfigModel
     * @return Store_Model_CustomerRulesConfigModel
     */
    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            $model = new $this->_model($model);
        }

        $data = array(
            'rule_id' => $model->getRuleId(),
            'field_name' => $model->getFieldName(),
            'rule_comparison_operator' => $model->getRuleComparisonOperator(),
            'field_value' => $model->getFieldValue()
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
     * get rules
     * @param int $ruleId rule id
     * @return array
     */
    public function getFieldsByRuleId($ruleId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('scra.rule_id = ?', $ruleId);

        $select = $this->getDbTable()->getAdapter()->select()->from(array('scra' => 'shopping_customer_rules_actions'),
            array('scra.field_name', 'scra.id', 'scra.rule_comparison_operator', 'scra.field_value'))
            ->where($where)->order('scra.field_name');

        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }

    /**
     * Delete by rule id
     *
     * @param int $ruleId rule id
     * @return int
     */
    public function deleteByRuleId($ruleId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('rule_id = ?', $ruleId);

        return $this->getDbTable()->getAdapter()->delete('shopping_customer_rules_config', $where);
    }

    /**
     * Delete rules config record
     *
     * @param int $id config id
     * @return mixed
     * @throws Exception
     */
    public function delete($id)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);

        return $this->getDbTable()->getAdapter()->delete('shopping_customer_rules_config', $where);

    }

}
