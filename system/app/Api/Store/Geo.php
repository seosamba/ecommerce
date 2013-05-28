<?php
/**
 * Geo data REST API controller
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 *
 * @package Store
 * @since 2.0.0
 */
class Api_Store_Geo extends Api_Service_Abstract {

	/**
	 * @var array Access Control List
	 */
	protected $_accessList = array(
		Tools_Security_Acl::ROLE_GUEST => array(
			'allow' => array('get')
		),
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post', 'put', 'delete')
		),
		Tools_Security_Acl::ROLE_ADMIN => array(
			'allow' => array('get')
		),
		Shopping::ROLE_SALESPERSON => array(
			'allow' => array('get')
		)
	);

	/**
	 * Get geographical data
	 *
	 * Resourse:
	 * : /api/store/geo/type/:type
	 *
	 * HttpMethod:
	 * : GET
	 *
	 * ## Parameters:
     * type (type string)
     * : Type of data. Possible values: country, state
	 *
	 * pairs (type sting)
	 * : If given data will be returned as key-value array
	 *
	 * @return JSON Data
	 */
	public function getAction() {
		$type = strtolower(filter_var($this->_request->getParam('type'), FILTER_SANITIZE_STRING));
		switch ($type){
			case 'country':
				$pairs = $this->_request->has('pairs');
				$countries = Tools_Geo::getCountries($pairs);
				asort($countries);
				return $countries;
				break;
			case 'state':
				$pairs = $this->_request->has('pairs');
				$country = filter_var($this->_request->getParam('country'), FILTER_SANITIZE_STRING);
				return Tools_Geo::getState(!empty($country)?$country:null, $pairs);
				break;
		}
		$this->_error();
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
