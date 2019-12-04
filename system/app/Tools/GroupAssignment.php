<?php

/**
 * Tool GroupAssignment.php
 */
class Tools_GroupAssignment
{


    /**
     * Assign user group based on the user custom param data
     *
     * @param int $userId system user id
     * @param array $userCustomParams custom params name value pair
     * @return array
     */
    public static function processGroupsByUserCustomParams($userId, $userCustomParams)
    {

        if (empty($userCustomParams)) {
            return array('error' => '1', 'message' => 'Empty custom params');
        }

        $userMapper = Application_Model_Mappers_UserMapper::getInstance();
        $userModel = $userMapper->find($userId);
        if (!$userModel instanceof Application_Model_Models_User) {
            return array('error' => '1', 'User not found');
        }

        $attributes = $userMapper->fetchUniqueAttributesNames();
        if (empty($attributes)) {
            return array('error' => '1', 'message' => 'There are no attributes has been found.');
        }

        $cleanAttributes = array();
        foreach ($userCustomParams as $attrName => $attrValue) {
            //Disabled until we have custom params config
            //if (in_array($attrName, $attributes, true)) {
                $cleanAttributes[$attrName] = $attrValue;
            //}
        }

        if (empty($cleanAttributes)) {
            return array('error' => '1', 'message' => 'There are no attributes has been found.');
        }

        $customerRulesGeneralConfigMapper = Store_Mapper_CustomerRulesGeneralConfigMapper::getInstance();
        $rules = $customerRulesGeneralConfigMapper->getConfigIds();

        if (empty($rules)) {
            return array('error' => '1', 'message' => 'No rules');
        }

        $ruleIds = array_combine(array_keys($rules), array_keys($rules));
        $rulesWithFields = $customerRulesGeneralConfigMapper->getRulesByIds(array_keys($ruleIds));
        if (!empty($rulesWithFields)) {
            foreach ($rulesWithFields as $rulesWithField) {
                unset($ruleIds[$rulesWithField['id']]);
            }
            $ruleIdsToAdd = self::getRuleIdsBasedOnData($rulesWithFields, $cleanAttributes);
            if (!empty($ruleIdsToAdd)) {
                foreach ($ruleIdsToAdd as $ruleIdToAdd => $ruleToAdd) {
                    $ruleIds[$ruleIdToAdd] = $ruleIdToAdd;
                }
            }
        }

        if (!empty($ruleIds)) {
            self::applyRuleActions($ruleIds, $userModel, $cleanAttributes);
        }


        return array('error' => '0');
    }

    /**
     * Get rule ids based on form data
     *
     * @param array $rules form rules
     * @param array $data form data
     * @return array
     */
    public static function getRuleIdsBasedOnData($rules, $data)
    {
        $ruleIds = array();
        $skipId = 0;

        foreach ($rules as $rule) {
            $skipStatus = false;
            $operator = $rule['rule_comparison_operator'];
            $fieldName = $rule['field_name'];
            $fieldNameAlternative = $rule['field_name'] . '[]';
            $fieldValue = mb_strtolower($rule['field_value']);
            if (isset($data[$fieldNameAlternative])) {
                $fieldName = $fieldNameAlternative;
            }

            if (!isset($data[$fieldName])) {
                if (isset($ruleIds[$rule['id']])) {
                    unset($ruleIds[$rule['id']]);
                }
                $skipId = $rule['id'];
                continue;
            }
            if (is_array($data[$fieldName])) {
                $dataValue = array_map('mb_strtolower', $data[$fieldName]);
            } else {
                $dataValue = mb_strtolower($data[$fieldName]);
            }
            $ruleId = $rule['id'];
            if ($skipId === $ruleId) {
                continue;
            }
            if (array_key_exists($fieldName, $data)) {
                if ($operator === Leads_Model_LeadsFormRulesConfigModel::RULE_COMPARISON_OPERATOR_EQUAL) {
                    if (is_array($dataValue)) {
                        if (!in_array($fieldValue, $dataValue)) {
                            $skipStatus = true;
                        }
                    } else {
                        if ($dataValue !== $fieldValue) {
                            $skipStatus = true;
                        }
                    }
                }
                if ($operator === Leads_Model_LeadsFormRulesConfigModel::RULE_COMPARISON_OPERATOR_IN) {
                    $compareToArray = explode(',', $fieldValue);
                    $compareToArray = array_map("mb_strtolower", $compareToArray);
                    if (is_array($dataValue)) {
                        $comparisonResult = array_intersect($compareToArray, $dataValue);
                        if (count($comparisonResult) == 0) {
                            $skipStatus = true;
                        }

                    } else {
                        if (!in_array($dataValue, $compareToArray)) {
                            $skipStatus = true;
                        }
                    }

                }
                if ($operator === Leads_Model_LeadsFormRulesConfigModel::RULE_COMPARISON_OPERATOR_LIKE) {
                    if (is_array($dataValue)) {
                        $inputSearchPattern = preg_quote($fieldValue, '~');
                        $inputSearchResult = preg_grep('~' . $inputSearchPattern . '~', $dataValue);
                        if (empty($inputSearchResult)) {
                            $skipStatus = true;
                        }
                    } else {
                        $matchFound = mb_strpos($dataValue, $fieldValue);
                        if ($matchFound === false) {
                            $skipStatus = true;
                        }
                    }
                }
                if ($operator === Leads_Model_LeadsFormRulesConfigModel::RULE_COMPARISON_OPERATOR_NOTEQUAL) {
                    if (is_array($dataValue)) {
                        if (in_array($fieldValue, $dataValue)) {
                            $skipStatus = true;
                        }
                    } else {
                        if ($dataValue === $fieldValue) {
                            $skipStatus = true;
                        }
                    }
                }
                if ($operator === Leads_Model_LeadsFormRulesConfigModel::RULE_COMPARISON_GREATER_THAN) {
                    if (is_numeric($dataValue)) {
                        if ($fieldValue > $dataValue) {
                            $skipStatus = true;
                        }
                    } elseif (is_array($dataValue)) {
                        foreach ($dataValue as $value) {
                            if (is_numeric($value)) {
                                if ($fieldValue > $dataValue) {
                                    $skipStatus = true;
                                }
                            } else {
                                $skipStatus = true;
                            }
                        }
                    } else {
                        $skipStatus = true;
                    }
                }
                if ($operator === Leads_Model_LeadsFormRulesConfigModel::RULE_COMPARISON_LESS_THAN) {
                    if (is_numeric($dataValue)) {
                        if ($fieldValue < $dataValue) {
                            $skipStatus = true;
                        }
                    } elseif (is_array($dataValue)) {
                        foreach ($dataValue as $value) {
                            if (is_numeric($value)) {
                                if ($fieldValue < $dataValue) {
                                    $skipStatus = true;
                                }
                            } else {
                                $skipStatus = true;
                            }
                        }
                    } else {
                        $skipStatus = true;
                    }
                }

                if ($skipStatus === true) {
                    $skipId = $ruleId;
                    unset($ruleIds[$ruleId]);
                } else {
                    $ruleIds[$ruleId] = $ruleId;
                }
            }
        }

        return $ruleIds;
    }

    /**
     * Apply actions based on the matched rules
     *
     * @param array $ruleIds rule ids to apply
     * @param Application_Model_Models_User $userModel user Model
     * @param array $data form data
     */
    public static function applyRuleActions(
        $ruleIds = array(),
        Application_Model_Models_User $userModel,
        $data = array()
    ) {
        $actions = Store_Mapper_CustomerRulesActionsMapper::getInstance()->getActionByRuleIds($ruleIds);
        if (!empty($actions)) {
            foreach ($actions as $action) {
                $actionType = $action['action_type'];
                $actionConfig = $action['action_config'];
                if (!empty($actionConfig)) {
                    $actionConfig = json_decode($actionConfig, true);
                    if ($actionType === Store_Model_CustomerRulesActionModel::ACTION_TYPE_ASSIGN_GROUP) {
                        self::assignGroup($actionConfig, $userModel, $data);
                    }
                }
            }
        }
    }

    /**
     * Assign customer group
     *
     * @param array $actionConfig action config
     * @param Application_Model_Models_User $userModel user model
     * @param array $data
     */
    public static function assignGroup($actionConfig = array(), Application_Model_Models_User $userModel, $data)
    {
        $groupId = $actionConfig['customer_group_id'];
        $groupMapper = Store_Mapper_GroupMapper::getInstance();
        $groupModel = $groupMapper->find($groupId);
        if ($groupModel instanceof Store_Model_Group) {
            $customerMapper = Models_Mapper_CustomerMapper::getInstance();
            $customerModel = $customerMapper->find($userModel->getId());
            if ($customerModel instanceof Models_Model_Customer) {
                $customerModel->setGroupId($groupId);
                $customerMapper->save($customerModel);
            }
        }

    }

}