<?php

/**
 * MAGICSPACE: grouponly
 * {grouponly} ... {/grouponly} - Grouponly magicspace displays content for customers
 *
 * Class MagicSpaces_Grouponly_Grouponly
 */
class MagicSpaces_Grouponly_Grouponly extends Tools_MagicSpaces_Abstract
{
    /**
     * Customer Magic Space
     * {grouponly[:group1,group2,...]}
     * Here you can put content that will be available just for users with special group
     * {/grouponly}
     * @return string
     */
    protected function _run()
    {
        $result = '';

        if ((Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_PLUGINS))) {
            $result = $this->_spaceContent;
        } else {
            $allowedGroups = explode(',', filter_var($this->_params[0], FILTER_SANITIZE_STRING));

            $sessionHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('session');
            $user = $sessionHelper->getCurrentUser();
            $dbTable = new Zend_Db_Table();
            $select = $dbTable->getAdapter()->select()
                ->from(array('sg' => 'shopping_group'), array('sg.groupName'))
                ->join(array('sci' => 'shopping_customer_info'), 'sg.id = sci.group_id', array())
                ->where('sci.user_id = ' . $user->getId());
            $userInGroup = $dbTable->getAdapter()->fetchRow($select);
            if (!empty($userInGroup['groupName']) && in_array($userInGroup['groupName'], $allowedGroups)
            ) {
                $result = $this->_spaceContent;
            }
        }

        return $result;
    }

}
