<?php

/**
 * MAGICSPACE: withoutgroups
 * {withoutgroups} ... {/withoutgroups} - Withoutgroups magicspace displays content when customer without a group
 *
 * Class MagicSpaces_Withoutgroups_Withoutgroups
 */
class MagicSpaces_Withoutgroups_Withoutgroups extends Tools_MagicSpaces_Abstract
{
    /**
     * Customer Magic Space
     * {withoutgroups}
     * Here you can put content that will be available just for users without any group
     * {/withoutgroups}
     * @return string
     */
    protected function _run()
    {
        $result = '';

        $sessionHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('session');
        $user = $sessionHelper->getCurrentUser();

        if (empty($user->getId())) {
            return $this->_spaceContent;
        } else {
            $dbTable = new Zend_Db_Table();
            $select = $dbTable->getAdapter()->select()
                ->from(array('sg' => 'shopping_group'), array('sg.groupName'))
                ->join(array('sci' => 'shopping_customer_info'), 'sg.id = sci.group_id', array())
                ->where('sci.user_id = ' . $user->getId());
            $userInGroup = $dbTable->getAdapter()->fetchRow($select);

            if (empty($userInGroup['groupName'])) {
                $result = $this->_spaceContent;
            }
        }

        return $result;
    }

}
