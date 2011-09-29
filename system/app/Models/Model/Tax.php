<?php

/**
 * Tax
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Model_Tax extends Application_Model_Models_Abstract{

	protected $_id;
	
	protected $_isDefault;
	
	protected $_zoneId;
	
	protected $_rate1;
	
	protected $_rate2;
	
	protected $_rate3;
	
	public function getId() {
		return $this->_id;
	}

	public function setId($_id) {
		$this->_id = $_id;
	}

	public function getIsDefault() {
		return $this->_isDefault;
	}

	public function setIsDefault($_idDefault) {
		$this->_isDefault = $_idDefault;
	}

	public function getZoneId() {
		return $this->_zoneId;
	}

	public function setZoneId($_zoneId) {
		$this->_zoneId = $_zoneId;
	}

	public function getRate1() {
		return $this->_rate1;
	}

	public function setRate1($_rate1) {
		$this->_rate1 = $_rate1;
	}

	public function getRate2() {
		return $this->_rate2;
	}

	public function setRate2($_rate2) {
		$this->_rate2 = $_rate2;
	}

	public function getRate3() {
		return $this->_rate3;
	}

	public function setRate3($_rate3) {
		$this->_rate3 = $_rate3;
	}



}