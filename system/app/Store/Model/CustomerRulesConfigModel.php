<?php

class Store_Model_CustomerRulesConfigModel extends Application_Model_Models_Abstract
{

    const RULE_COMPARISON_OPERATOR_EQUAL = 'equal';

    const RULE_COMPARISON_OPERATOR_NOTEQUAL = 'notequal';

    const RULE_COMPARISON_OPERATOR_LIKE = 'like';

    const RULE_COMPARISON_OPERATOR_IN = 'in';

    const RULE_COMPARISON_GREATER_THAN = 'greaterthan';

    const RULE_COMPARISON_LESS_THAN = 'lessthan';

    public static $_allowedComparisonOperators = array(
        self::RULE_COMPARISON_OPERATOR_EQUAL,
        self::RULE_COMPARISON_OPERATOR_NOTEQUAL,
        self::RULE_COMPARISON_OPERATOR_LIKE,
        self::RULE_COMPARISON_OPERATOR_IN,
        self::RULE_COMPARISON_GREATER_THAN,
        self::RULE_COMPARISON_LESS_THAN
    );

    protected $_ruleId = '';

    protected $_fieldName = '';

    protected $_ruleComparisonOperator = self::RULE_COMPARISON_OPERATOR_EQUAL;

    protected $_fieldValue = '';

    /**
     * @return string
     */
    public function getRuleId()
    {
        return $this->_ruleId;
    }

    /**
     * @param string $ruleId
     * @return string
     */
    public function setRuleId($ruleId)
    {
        $this->_ruleId = $ruleId;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->_fieldName;
    }

    /**
     * @param string $fieldName
     * @return string
     */
    public function setFieldName($fieldName)
    {
        $this->_fieldName = $fieldName;

        return $this;
    }

    /**
     * @return string
     */
    public function getRuleComparisonOperator()
    {
        return $this->_ruleComparisonOperator;
    }

    /**
     * @param string $ruleComparisonOperator
     * @return string
     */
    public function setRuleComparisonOperator($ruleComparisonOperator)
    {
        $this->_ruleComparisonOperator = $ruleComparisonOperator;

        return $this;
    }

    /**
     * @return string
     */
    public function getFieldValue()
    {
        return $this->_fieldValue;
    }

    /**
     * @param string $fieldValue
     * @return string
     */
    public function setFieldValue($fieldValue)
    {
        $this->_fieldValue = $fieldValue;

        return $this;
    }

}