<?php
/**
 * Stats.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Store_Stats extends Api_Service_Abstract {

	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get')
		)
	);

	/**
	 * The get action handles GET requests and receives an 'id' parameter; it
	 * should respond with the server resource state of the resource identified
	 * by the 'id' value.
	 * @return null|array
	 */
	public function getAction() {
		$id = array_filter(filter_var_array(explode(',', $this->_request->getParam('id')), FILTER_VALIDATE_INT));
		if (is_array($id) && !empty($id)){
			return Models_Mapper_ProductMapper::getInstance()->fetchProductSalesCount($id);
		}
	}

	/**
	 * The post action handles POST requests; it should accept and digest a
	 * POSTed resource representation and persist the resource state.
	 */
	public function postAction() {
		// TODO: Implement postAction() method.
	}

	/**
	 * The put action handles PUT requests and receives an 'id' parameter; it
	 * should update the server resource state of the resource identified by
	 * the 'id' value.
	 */
	public function putAction() {
		// TODO: Implement putAction() method.
	}

	/**
	 * The delete action handles DELETE requests and receives an 'id'
	 * parameter; it should update the server resource state of the resource
	 * identified by the 'id' value.
	 */
	public function deleteAction() {
		// TODO: Implement deleteAction() method.
	}

}
