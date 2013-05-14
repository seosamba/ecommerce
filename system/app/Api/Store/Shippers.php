<?php
/**
 * Shipping plugins config REST API controller
 *
 * @package Store
 * @since 2.0.0
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Store_Shippers extends Api_Service_Abstract {

	/**
	 * @var array Access Control List
	 */
	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post', 'put', 'delete')
		),
		Tools_Security_Acl::ROLE_ADMIN => array(
			'allow' => array('get', 'post', 'put', 'delete')
		)
	);

	/**
	 * Fetch a shipping module configuration
	 *
	 * Resourse:
	 * : /api/store/shippers/
	 *
	 * HttpMethod:
	 * : GET
	 *
	 * ## Parameters:
	 * name (type string)
	 * : Name of shipping plugin
	 *
	 * @return JSON List of shipping plugins configurations
	 */
	public function getAction() {
		$name = filter_var($this->_request->getParam('name'), FILTER_SANITIZE_STRING);
		if ($name){
			return Models_Mapper_ShippingConfigMapper::getInstance()->find($name);
		} else {
			return Models_Mapper_ShippingConfigMapper::getInstance()->fetchAll();
		}
	}

	/**
	 * Alias for PUT method
	 * @see Api_Store_Shippers::put()
	 */
	public function postAction() {
		return $this->putAction();
	}

	/**
	 * Saves shipping plugin config in database
	 *
	 * Resourse:
	 * : /api/store/shippers/
	 *
	 * HttpMethod:
	 * : PUT
	 *
	 * @return JSON Passthrough config
	 */
	public function putAction() {
		$data = Zend_Json::decode($this->_request->getRawBody());
		if (is_array($data) && !empty($data)){
			if (Models_Mapper_ShippingConfigMapper::getInstance()->save($data)){
				return $data;
			}
		}
		$this->_error(null, self::REST_STATUS_BAD_REQUEST);
	}

	/**
	 * Reserved for future usage
	 */
	public function deleteAction() {
		// TODO: Implement deleteAction() method.
	}
}
