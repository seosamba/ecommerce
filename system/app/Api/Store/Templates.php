<?php
/**
 * Templates.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Store_Templates extends Api_Service_Abstract {

	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post')
		)
	);

	/**
	 * The get action handles GET requests and receives an 'id' parameter; it
	 * should respond with the server resource state of the resource identified
	 * by the 'id' value.
	 */
	public function getAction() {
		$type = filter_var($this->_request->getParam('filter'), FILTER_SANITIZE_STRING);
		if ($type){
			$data = Application_Model_Mappers_TemplateMapper::getInstance()->findByType($type);
		} else {
			$data = Application_Model_Mappers_TemplateMapper::getInstance()->fetchAll();
		}
		return array_map(function($template){
			return array_filter($template->toArray());
		}, $data);
	}

	/**
	 * The post action handles POST requests; it should accept and digest a
	 * POSTed resource representation and persist the resource state.
	 */
	public function postAction() {
		// TODO: Implement getAction() method.
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
