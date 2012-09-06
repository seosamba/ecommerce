<?php
/**
 * Tags.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Store_Tags extends Api_Service_Abstract {

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
		$id = filter_var($this->_request->getParam('id'), FILTER_VALIDATE_INT);
		if ($id) {
			$result = Models_Mapper_Tag::getInstance()->find($id);
			if ($result !== null){
				return $result->toArray();
			}
		}

		return array_map(function($tag){ return $tag->toArray(); }, Models_Mapper_Tag::getInstance()->fetchAll(null, array('name')) );
	}

	/**
	 * The post action handles POST requests; it should accept and digest a
	 * POSTed resource representation and persist the resource state.
	 */
	public function postAction() {
		$rawData = json_decode($this->_request->getRawBody(), true);
		if (!empty($rawData)){
			$rawData['name'] = ucfirst($rawData['name']);
			$result = Models_Mapper_Tag::getInstance()->save($rawData);
		} else {
			$this->_error();
		}
		if ($result === null){
			$this->_error('This tag already exists', self::REST_STATUS_BAD_REQUEST);
		} else {
			return $result->toArray();
		}
	}

	/**
	 * The put action handles PUT requests and receives an 'id' parameter; it
	 * should update the server resource state of the resource identified by
	 * the 'id' value.
	 */
	public function putAction() {
		$rawData = json_decode($this->_request->getRawBody(), true);
		if (!empty($rawData)){
			$rawData['name'] = ucfirst($rawData['name']);
			$result = Models_Mapper_Tag::getInstance()->save($rawData);
		} else {
			$this->_error();
		}
		if ($result === null){
			$this->_error('This tag already exists', self::REST_STATUS_BAD_REQUEST);
		} else {
			return $result->toArray();
		}
	}

	/**
	 * The delete action handles DELETE requests and receives an 'id'
	 * parameter; it should update the server resource state of the resource
	 * identified by the 'id' value.
	 */
	public function deleteAction() {
		$id = filter_var($this->_request->getParam('id'), FILTER_VALIDATE_INT);
		if ($id !== false){
			return Models_Mapper_Tag::getInstance()->delete($id);
		} else {
			$this->_error(null, self::REST_STATUS_NOT_FOUND);
		}
	}

}
