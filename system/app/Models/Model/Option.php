<?php

/**
 * Option
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Model_Option extends Application_Model_Models_Abstract {

	const TYPE_DROPDOWN = 'dropdown';
	
	const TYPE_RADIO = 'radio';
	
	const TYPE_TEXT = 'text';
	
	const TYPE_DATE = 'date';
	
	const TYPE_FILE = 'file';
	
	protected $_id;
	
	protected $_parentId;
	
	protected $_title;
	
	protected $_type;
	
	protected $_selection;
	
	public function getId() {
		return $this->_id;
	}

	public function setId($_id) {
		$this->_id = $_id;
	}

	public function getTitle() {
		return $this->_title;
	}

	public function setTitle($_title) {
		$this->_title = $_title;
	}

	public function getType() {
		return $this->_type;
	}

	public function setType($_type) {
		$this->_type = $_type;
	}

	public function getSelection() {
		return $this->_selection;
	}

	public function setSelection($_selection) {
		$this->_selection = $_selection;
	}

	public function getParentId() {
		return $this->_parentId;
	}

	public function setParentId($_parentId) {
		$this->_parentId = $_parentId;
	}



}