<?php

/**
 * Brand
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Model_Brand extends Application_Model_Models_Abstract {

	protected $_name;
	
	public function getName() {
		return $this->_name;
	}

	public function setName($_name) {
		$this->_name = $_name;
	}

}