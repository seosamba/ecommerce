<?php

/**
 * CustomerRulesActionsMapper.php
 *
 *
 * @method Store_Mapper_CustomerRulesActionsMapper getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Store_Mapper_CustomerRulesActionsMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_CustomerRulesActionModel';

    protected $_dbTable = 'Store_DbTable_CustomerRulesActionsDbTable';

    /**
     * Save event actions model to DB
     * @param $model Store_Model_CustomerRulesActionModel
     * @return Store_Model_CustomerRulesActionModel
     */
    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            $model = new $this->_model($model);
        }

        $data = array(
            'rule_id' => $model->getRuleId(),
            'action_type' => $model->getActionType(),
            'action_config' => $model->getActionConfig()
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
     * Get action by rule ids
     *
     * @param array $ruleIds rule ids
     * @return array
     */
    public function getActionByRuleIds($ruleIds)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('rule_id IN (?)', $ruleIds);

        $select = $this->getDbTable()->getAdapter()->select()->from('shopping_customer_rules_actions')->where($where);

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

        return $this->getDbTable()->getAdapter()->delete('shopping_customer_rules_actions', $where);
    }


    /**
     * Delete rules actions config record
     *
     * @param int $id config id
     * @return mixed
     * @throws Exception
     */
    public function delete($id)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);

        return $this->getDbTable()->getAdapter()->delete('shopping_customer_rules_actions', $where);

    }

}
