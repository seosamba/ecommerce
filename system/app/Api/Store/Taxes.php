<?php
/**
 * Tax rules REST API controller
 *
 * @package Store
 * @since 2.0.0
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Store_Taxes extends Api_Service_Abstract {


	/**
	 * @var array Access Control List
	 */
	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post', 'put', 'delete')
		),
		Tools_Security_Acl::ROLE_ADMIN => array(
			'allow' => array('get', 'post', 'put', 'delete')
		),
	);

	/**
	 * Fetch list of tax rules by id or get a full list
	 *
	 * Resourse:
	 * : /api/store/templates/id/:id
	 *
	 * HttpMethod:
	 * : GET
	 *
	 * @return JSON Returns list of tax rules
	 */
	public function getAction() {
		$id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);
        if ($id) {
            $rule = Models_Mapper_Tax::getInstance()->find($id);
            if ($rule instanceof Models_Model_Tax){
                return $rule->toArray();
            }
	        $this->_error(null, self::REST_STATUS_NOT_FOUND);
        }

        $rules = Models_Mapper_Tax::getInstance()->fetchAll();

		return is_null($rules) ? null : array_map(function($rule){ return $rule->toArray(); }, $rules);
	}

	/**
	 * Saves tax rules into database
	 *
	 * Resourse:
	 * : /api/store/taxes/
	 *
	 * HttpMethod:
	 * : POST
	 *
	 * ## Parameters:
     * rules (type string)
     * : List of to save
	 *
	 * @return JSON Paththrough list of tax rules
	 */
	public function postAction() {
		$rules = $this->_request->getParam('rules', null);
        $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
        $valid = Tools_System_Tools::validateToken($tokenToValidate, Shopping::SHOPPING_SECURE_TOKEN);
        if (!$valid) {
            exit;
        }
        if ($rules) {
	        $data = array();
            foreach ($rules as $rule) {
                $data[] = Models_Mapper_Tax::getInstance()->save($rule);
            }
	        return $data;
        }
	}

	/**
	 * Reserved for future usage
	 */
	public function putAction() {
		// TODO: Implement putAction() method.
	}

	/**
	 * Deletes tax rule
	 *
	 * Resourse:
	 * : /api/store/taxes/
	 *
	 * HttpMethod:
	 * : DELETE
	 *
	 * ## Parameters:
	 * id (type integer)
	 *
	 */
	public function deleteAction() {
		$id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);
        if ($id){
            return Models_Mapper_Tax::getInstance()->delete($id);
        }
	}

}
