<?php
/**
 * Product Tags REST API controller
 *
 * @package Store
 * @since   2.0.0
 *
 * @author  Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Api_Store_Tags extends Api_Service_Abstract {

	/**
	 * @var array Access Control List
	 */
	protected $_accessList = array(
		Tools_Security_Acl::ROLE_SUPERADMIN => array(
			'allow' => array('get', 'post', 'put', 'delete')
		),
		Tools_Security_Acl::ROLE_ADMIN      => array(
			'allow' => array('get', 'post', 'put', 'delete')
		),
		Shopping::ROLE_SALESPERSON      => array(
			'allow' => array('get')
		)
	);

	/**
	 * Find product tag by ID
	 *
	 * Resourse:
	 * : /api/store/tags/id/:id
	 *
	 * HttpMethod:
	 * : GET
	 *
	 * ## Parameters:
	 * id (type *mixed*)
	 * : Tag ID or comma separated list of IDs
	 *
	 * limit (type *int*)
	 * : Specifies the number of records to retrieve. If omitted, will return all existing records.
	 *
	 * offset (type *int*)
	 * : Number of records to skip. Used for pagination result set.
	 *
	 * count (type *boolean*)
	 * : When set to true, 1 or non empty string total number of records will be returned in response in 'totalCount' key
	 *
	 * @return JSON Single tag or set of tags
	 */
	public function getAction() {
		/**
		 * @var mixed ID or comma-separated list of IDs
		 */
		$id = array_filter(filter_var_array(explode(',', $this->_request->getParam('id')), FILTER_VALIDATE_INT));

		if ($id) {
			$data = Models_Mapper_Tag::getInstance()->find($id);
			if ($data instanceof Models_Model_Tag) {
				return $data->toArray();
			} elseif (is_array($data) && !empty($data)) {
				return array_map(function ($tag) {
					return $tag->toArray();
				}, $data);
			} else {

			}
		} else {
			$offset = filter_var($this->_request->getParam('offset', 0), FILTER_SANITIZE_NUMBER_INT);
			$limit  = filter_var($this->_request->getParam('limit', false), FILTER_VALIDATE_INT);
			$count  = filter_var($this->_request->getParam('count', false), FILTER_VALIDATE_BOOLEAN);
            $name   = filter_var($this->_request->getParam('name', false), FILTER_SANITIZE_STRING);
			$result = Models_Mapper_Tag::getInstance()->fetchAll(
                (!empty($name)) ? "name LIKE '$name%'" : null,
                array('name'),
                $offset,
                $limit,
                $count
            );
			if ($result) {
				if ($count && isset($result['data'])) {
					$result['data'] = array_map(function ($tag) {
						return $tag->toArray();
					}, $result['data']);
					return $result;
				} else {
					return array_map(function ($tag) {
						return $tag->toArray();
					}, $result);
				}
			}
		}
		$this->_error(null, self::REST_STATUS_NOT_FOUND);
	}

	/**
	 * Create new product tag
	 *
	 * @return JSON
	 */
	public function postAction() {
		$rawData = json_decode($this->_request->getRawBody(), true);
		if (!empty($rawData)) {
			$rawData['name'] = ucfirst($rawData['name']);
			if (strpos($rawData['name'], ',') !== false){
				$this->_error('Tag should not contain comma in name', self::REST_STATUS_BAD_REQUEST);
			}
			$result = Models_Mapper_Tag::getInstance()->save($rawData);
		} else {
			$this->_error();
		}
		if ($result === null) {
			$this->_error('This tag already exists', self::REST_STATUS_BAD_REQUEST);
		} else {
			return $result->toArray();
		}
	}

	/**
	 * Update an existing product tag
	 *
	 * @return JSON Returns updated models representations
	 */
	public function putAction() {
		$rawData = json_decode($this->_request->getRawBody(), true);
		if (!empty($rawData)) {
			$rawData['name'] = ucfirst($rawData['name']);
			$result = Models_Mapper_Tag::getInstance()->save($rawData);
		} else {
			$this->_error();
		}
		if ($result === null) {
			$this->_error('This tag already exists', self::REST_STATUS_BAD_REQUEST);
		} else {
			return $result->toArray();
		}
	}

	/**
	 * Deletes product tag by tag id
	 *
	 * Resourse:
	 * : /api/store/tags/
	 *
	 * HttpMethod:
	 * : DELETE
	 *
	 * ## Parameters:
	 * id (type integer) Tag id
	 *
	 */
	public function deleteAction() {
		$id = filter_var($this->_request->getParam('id'), FILTER_VALIDATE_INT);
		if ($id !== false) {
			return Models_Mapper_Tag::getInstance()->delete($id);
		} else {
			$this->_error(null, self::REST_STATUS_NOT_FOUND);
		}
	}

}
