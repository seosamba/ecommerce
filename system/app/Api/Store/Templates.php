<?php
/**
 * Templates REST API controllers
 *
 * @package Store
 * @since 2.0.0
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Store_Templates extends Api_Service_Abstract {

	/**
	 * @var array Access Control List
	 */
	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get')
		),
		Tools_Security_Acl::ROLE_ADMIN => array(
			'allow' => array('get')
		)
	);

	/**
	 * Returns list of templates
	 *
	 * Resourse:
	 * : /api/store/templates/
	 *
	 * HttpMethod:
	 * : GET
	 *
	 * ## Parameters:
     * filter (type string)
     * : Type of template
	 *
	 * @return JSON Returns list of templates
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
	 * Reserved for future usage
	 */
	public function postAction() {
		// TODO: Implement getAction() method.
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
