<?php
/**
 * Zones.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Store_Zones extends Api_Service_Abstract {

	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post', 'delete')
		)
	);

	/**
	 * The get action handles GET requests and receives an 'id' parameter; it
	 * should respond with the server resource state of the resource identified
	 * by the 'id' value.
	 */
	public function getAction() {
		$id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);
        if ($id) {
            $rule = Models_Mapper_Zone::getInstance()->find($id);
            if ($rule instanceof Models_Model_Tax){
                return $rule->toArray();
            }
	        $this->_error(null, self::REST_STATUS_NOT_FOUND);
        }

		$zones = Models_Mapper_Zone::getInstance()->fetchAll();
        return array_map(function($zone){ return $zone->toArray(); }, $zones);
	}

	/**
	 * The post action handles POST requests; it should accept and digest a
	 * POSTed resource representation and persist the resource state.
	 */
	public function postAction() {
		$rules = $this->_request->getParam('zones', null);
		$data = array();
        if ($rules) {
	        $zonesMapper = Models_Mapper_Zone::getInstance();

            foreach ($rules as $rule) {
                $data[] = $zonesMapper->save($rule);
            }
        }
		return $data;
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
		$id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);
		$zonesMapper = Models_Mapper_Zone::getInstance();
        if ($id){
            return $zonesMapper->delete($id);
        }
	}


}
