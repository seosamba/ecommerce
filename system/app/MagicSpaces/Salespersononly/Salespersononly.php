<?php
/**
 * MAGICSPACE: salespersononly
 * {salespersononly} ... {/salespersononly} - Salesperson magicspace displays content for customers
 *
 * Class MagicSpaces_Salespersononly_Salespersononly
 */
class MagicSpaces_Salespersononly_Salespersononly extends Tools_MagicSpaces_Abstract {
	/**
	 * Salespersononly Magic Space
	 * {salespersononly}
     * {/salespersononly}
	 * @return string
	 */
	protected function _run()
	{
		$result = '';
        $session = Zend_Controller_Action_HelperBroker::getExistingHelper('session');

        $roleId = $session->getCurrentUser()->getRoleId();
        if ($roleId === Shopping::ROLE_SALESPERSON) {
            $result = $this->_spaceContent;
        }


		return $result;
	}

}
