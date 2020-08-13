<?php

/**
 * CustomerRulesGeneralConfigMapper.php
 *
 *
 * @method Store_Mapper_CustomerRulesGeneralConfigMapper  getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Store_Mapper_CustomerRulesGeneralConfigMapper extends Application_Model_Mappers_Abstract
{

    protected $_model = 'Store_Model_CustomerRulesGeneralConfigModel';

    protected $_dbTable = 'Store_DbTable_CustomerRulesGeneralConfigDbTable';

    /**
     * Save general config model to DB
     * @param $model Store_Model_CustomerRulesGeneralConfigModel
     * @return Store_Model_CustomerRulesGeneralConfigModel
     */
    public function save($model)
    {
        if (!$model instanceof $this->_model) {
            $model = new $this->_model($model);
        }

        $data = array(
            'rule_name' => $model->getRuleName(),
            'created_at' => $model->getCreatedAt(),
            'creator_id' => $model->getCreatorId(),
            'updated_at' => $model->getUpdatedAt(),
            'editor_id' => $model->getEditorId()
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
     * Get rules data
     *
     * @param string $where SQL where clause
     * @param string $order OPTIONAL An SQL ORDER clause.
     * @param int $limit OPTIONAL An SQL LIMIT count.
     * @param int $offset OPTIONAL An SQL LIMIT offset.
     * @param bool $withoutCount flag to get with or without records quantity
     * @param bool $singleRecord flag fetch single record
     * @param bool $withTimezoneOffset select data with processed dates based on logged user timezone
     * @return array
     */
    public function fetchAll(
        $where = null,
        $order = null,
        $limit = null,
        $offset = null,
        $withoutCount = false,
        $singleRecord = false,
        $withTimezoneOffset = false
    ) {

        $mysqlOffset = Tools_System_Tools::getUtcOffset('P');

        $createdAt = 'scrgc.created_at';

        if ($withTimezoneOffset === true) {
            $createdAt = new Zend_Db_Expr("CONVERT_TZ(`scrgc`.`created_at`, '+00:00', '$mysqlOffset')");
        }

        $updatedAt = 'scrgc.updated_at';

        if ($withTimezoneOffset === true) {
            $updatedAt = new Zend_Db_Expr("CONVERT_TZ(`scrgc`.`updated_at`, '+00:00', '$mysqlOffset')");
        }

        $select = $this->getDbTable()->getAdapter()->select()
            ->from(array('scrgc' => 'shopping_customer_rules_general_config'),
                array(
                    'scrgc.id',
                    'scrgc.rule_name',
                    'scrgc.creator_id',
                    'createdAt' => $createdAt,
                    'scrgc.editor_id',
                    'updatedAt' => $updatedAt,
                    'creatorName' => 'u.full_name',
                    'editorName' => 'uc.full_name',
                    'actionTypes' => new Zend_Db_Expr('GROUP_CONCAT(DISTINCT(scra.action_type))')
                )
            )
            ->joinLeft(array('scra' => 'shopping_customer_rules_actions'), 'scra.rule_id=scrgc.id', array())
            ->joinLeft(array('u' => 'user'), 'u.id=scrgc.creator_id', array())
            ->joinLeft(array('uc' => 'user'), 'uc.id=scrgc.editor_id', array());


        $select->group('scrgc.id');
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
            $select->reset(Zend_Db_Select::LIMIT_COUNT);

            $count = array('count' => new Zend_Db_Expr('COUNT(DISTINCT(scrgc.id))'));

            $select->from(array('scrgc' => 'shopping_customer_rules_general_config'), $count)
                ->joinLeft(array('scra' => 'shopping_customer_rules_actions'), 'scra.rule_id=scrgc.id', array())
                ->joinLeft(array('u' => 'user'), 'u.id=scrgc.creator_id', array())
                ->joinLeft(array('uc' => 'user'), 'uc.id=scrgc.editor_id', array());


            $select =  $this->getDbTable()->getAdapter()->select()
                ->from(
                    array('subres' => $select),
                    array('count' => 'SUM(count)')
                );

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
     * Get assigned rules ids
     *
     * @param array $ruleIds rule ids
     * @return array
     */
    public function getRulesByIds($ruleIds)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('scrgc.id IN (?)', $ruleIds);

        $select = $this->getDbTable()->getAdapter()->select()->from(array('scrgc' => 'shopping_customer_rules_general_config'),
            array('uniqueKey' => new Zend_Db_Expr("CONCAT(scrgc.id, '_', scrc.id)"), 'scrgc.id', 'scrc.field_name', 'scrc.rule_comparison_operator', 'scrc.field_value'))
            ->join(array('scrc' => 'shopping_customer_rules_config'), 'scrc.rule_id=scrgc.id', array())
            ->where($where);

        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }


    /**
     * get rules
     * @return array
     */
    public function getConfigIds()
    {
        $select = $this->getDbTable()->getAdapter()->select()->from(array('scrc' => 'shopping_customer_rules_general_config'),
            array('scrc.id'));

        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }


    /**
     * get rules
     * @param int $ruleId rule id
     * @return array
     */
    public function getFieldsByRuleId($ruleId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('scrc.rule_id = ?', $ruleId);

        $select = $this->getDbTable()->getAdapter()->select()->from(array('scrc' => 'shopping_customer_rules_config'),
            array('scrc.field_name', 'scrc.id', 'scrc.rule_comparison_operator', 'scrc.field_value'))
            ->where($where)->order('scrc.field_name');

        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }

    /**
     * get rules
     * @param int $ruleId rule id
     * @return array
     */
    public function getActionsByRuleId($ruleId)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('scra.rule_id = ?', $ruleId);

        $select = $this->getDbTable()->getAdapter()->select()->from(array('scra' => 'shopping_customer_rules_actions'),
            array('scra.action_type', 'scra.id', 'scra.action_config'))
            ->where($where);

        return $this->getDbTable()->getAdapter()->fetchAssoc($select);
    }

    /**
     * Check if rule already exists
     *
     * @param string $ruleName unique rule name
     * @return null
     */
    public function checkRuleExist($ruleName)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('rule_name = ?', $ruleName);

        return $this->_findWhere($where);
    }



    /**
     * Delete rules general config record
     *
     * @param int $id config id
     * @return mixed
     * @throws Exception
     */
    public function delete($id)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);

        return $this->getDbTable()->getAdapter()->delete('shopping_customer_rules_general_config', $where);

    }

}
