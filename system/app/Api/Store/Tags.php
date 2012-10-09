<?php
/**
 * Tags.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Store_Tags extends Api_Service_Abstract {

	protected $_accessList = array(
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
		$id = array_filter(filter_var_array(explode(',', $this->_request->getParam('id')), FILTER_VALIDATE_INT));

		if ($id) {
			$data = Models_Mapper_Tag::getInstance()->find($id);
			if ($data instanceof Models_Model_Tag){
				return $data->toArray();
			} elseif (is_array($data) && !empty($data)){
				return array_map(function($tag){ return $tag->toArray(); }, $data);
			} else {

			}
		} else {
			$offset = filter_var($this->_request->getParam('offset', 0), FILTER_SANITIZE_NUMBER_INT);
			$limit  = filter_var($this->_request->getParam('limit', Shopping::PRODUCT_DEFAULT_LIMIT), FILTER_VALIDATE_INT);
			$count  = filter_var($this->_request->getParam('count', false), FILTER_VALIDATE_BOOLEAN);


			$result = Models_Mapper_Tag::getInstance()->fetchAll(null, array('name'), $offset, $limit, $count);
			if ($result){
				return array_map(function($tag){ return $tag->toArray(); }, $count ? $result['data'] : $result );
			}
		}
		$this->_error(null, self::REST_STATUS_NOT_FOUND);
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
