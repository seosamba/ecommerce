<?php
/**
 * Product Options REST API controller
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 *
 * @package Store
 * @since 2.0.0
 */
class Api_Store_Options extends Api_Service_Abstract {

	/**
	 * @var array Access Control List
	 */
	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post', 'delete')
		),
		Tools_Security_Acl::ROLE_ADMIN => array(
			'allow' => array('get', 'post', 'delete')
		),
		Shopping::ROLE_SALESPERSON => array(
			'allow' => array('get', 'post', 'delete')
		)
	);

	/**
	 * Get product options list
	 *
	 * Resourse:
	 * : /api/store/options/
	 *
	 * HttpMethod:
	 * : GET
	 *
	 * @return JSON List of available product options
	 */
	public function getAction() {
        return Models_Mapper_OptionMapper::getInstance()->fetchAll(array('parentId = ?' => 0), null, false);
	}

	/**
	 * Reserved for future usage
	 */
	public function postAction() {
		// TODO: Implement postAction() method.
	}

	/**
	 * Reserved for future usage
	 */
	public function putAction() {
		// TODO: Implement putAction() method.
	}

    /**
     * Delete product option from library
     *
     * @return bool
     * @throws Exception
     */
	public function deleteAction() {
	    if(Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT)) {
            $optionId = filter_var($this->_request->getParam('optionId'), FILTER_SANITIZE_NUMBER_INT);

            if(!empty($optionId)) {
                return Models_Mapper_OptionMapper::getInstance()->deleteLibraryOption($optionId);
            }
        }

        return false;
	}

}
