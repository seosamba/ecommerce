<?php
/**
 * Geo
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Store_Geo extends Api_Service_Abstract {

	protected $_accessList = array(
		Tools_Security_Acl::ROLE_GUEST => array(
			'allow' => array('get')
		),
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post', 'put', 'delete')
		)
	);

	/**
	 * The get action handles GET requests and receives an 'id' parameter; it
	 * should respond with the server resource state of the resource identified
	 * by the 'id' value.
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
