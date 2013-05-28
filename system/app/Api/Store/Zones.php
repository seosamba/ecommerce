<?php
/**
 * Zones REST API controller
 *
 * @package Store
 * @since 2.0.0
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 *
 */
class Api_Store_Zones extends Api_Service_Abstract {

	/**
	 * @var array Access Control List
	 */
	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post', 'delete')
		),
		Tools_Security_Acl::ROLE_ADMIN => array(
			'allow' => array('get', 'post', 'delete')
		)
	);

	/**
	 * Find zone by ID
	 *
	 * Resourse:
	 * : /api/store/zones/id/:id
	 *
	 * HttpMethod:
	 * : GET
	 *
	 * @return json List of zones
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
	 * Saves zones into database
	 *
     * Resourse:
	 * : /api/store/zones
	 *
	 * HttpMethod:
	 * : POST
	 *
	 * ## Parameters:
	 * zones (type array)
	 * : List of zones to save
	 *
	 * @return json Passthroug incoming list of zones
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
	 * Reserved for future usage
	 */
	public function putAction() {
		// TODO: Implement putAction() method.
	}

	/**
	 * Delete zone by ID
	 *
	 * Resourse:
	 * : /api/store/zones/id/:id
	 *
	 * HttpMethod:
	 * : DELETE
	 *
	 * @return json List of zones
	 */
	public function deleteAction() {
		$id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);
		$zonesMapper = Models_Mapper_Zone::getInstance();
        if ($id){
            return $zonesMapper->delete($id);
        }
	}


}
