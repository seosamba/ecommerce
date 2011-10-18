<?php

/**
 * Category
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Model_Category extends Application_Model_Models_Abstract {

	protected  $_id;
	
	protected $_name;
	
	public function getId() {
		return $this->_id;
	}

	public function setId($_id) {
		$this->_id = $_id;
	}

	public function getName() {
		return $this->_name;
	}

	public function setName($_name) {
		$this->_name = $_name;
	}

}