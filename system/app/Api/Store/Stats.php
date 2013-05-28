<?php
/**
 * Product sales stats REST API controller
 *
 * @package Store
 * @since 2.0.0
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Store_Stats extends Api_Service_Abstract {

	/**
	 * @var array Access Control List
	 */
	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get')
		),
		Tools_Security_Acl::ROLE_ADMIN => array(
			'allow' => array('get')
		),
		Shopping::ROLE_SALESPERSON => array(
			'allow' => array('get')
		)
	);

	/**
	 * Fetch product sales count for given product IDs
	 *
	 * Resourse:
	 * : /api/store/stats/id/:id
	 *
	 * HttpMethod:
	 * : GET
	 *
	 * ## Parameters:
	 * id (type *mixed*)
	 * : Product ID or comma separated list of IDs
	 *
	 * @return JSON Array of product sales count with corresponding product IDs as key
	 */
	public function getAction() {
		$id = array_filter(filter_var_array(explode(',', $this->_request->getParam('id')), FILTER_VALIDATE_INT));
		if (is_array($id) && !empty($id)){
			return Models_Mapper_ProductMapper::getInstance()->fetchProductSalesCount($id);
		}
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
	 * Reserved for future usage
	 */
	public function deleteAction() {
		// TODO: Implement deleteAction() method.
	}

}
