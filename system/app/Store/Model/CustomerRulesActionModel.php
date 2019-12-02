<?php

class Store_Model_CustomerRulesActionModel extends Application_Model_Models_Abstract
{

    const ACTION_TYPE_ASSIGN_GROUP = 'assign_group';

    /**
     * Allowed action types
     *
     * @var array
     */
    public static $_allowedActionTypes = array(
        self::ACTION_TYPE_ASSIGN_GROUP
    );

    protected $_ruleId = '';

    protected $_actionType = '';

    /**
     * Serialized action config data
     *
     * @var string
     */
    protected $_actionConfig = '';

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
    public function getActionType()
    {
        return $this->_actionType;
    }

    /**
     * @param string $actionType
     * @return string
     */
    public function setActionType($actionType)
    {
        $this->_actionType = $actionType;

        return $this;
    }

    /**
     * @return string
     */
    public function getActionConfig()
    {
        return $this->_actionConfig;
    }

    /**
     * @param string $actionConfig
     * @return string
     */
    public function setActionConfig($actionConfig)
    {
        $this->_actionConfig = $actionConfig;

        return $this;
    }


}