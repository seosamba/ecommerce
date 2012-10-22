<?php

/**
 * Zone
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Models_Model_Zone extends Application_Model_Models_Abstract {
	protected $_id;
	protected $_name;
	protected $_countries = array();
	protected $_states    = array();
	protected $_zip       = array();

	
	public function getId() {
		return $this->_id;
	}

	public function setId($_id) {
		$this->_id = $_id;
		return $this;
	}

	public function getName() {
		return $this->_name;
	}

	public function setName($_name) {
		$this->_name = $_name;
		return $this;
	}

	public function getCountries($codesOnly = false) {
		if ($codesOnly) {
			return array_map(function($country){ return $country['country']; }, $this->_countries);
		}
		return $this->_countries;
	}

	public function setCountries($_countries) {
		if (is_array($_countries)){
			foreach ($_countries as $code) {
				$this->addCountry($code);
			}
		}
		return $this;
	}

	public function getStates() {
		return $this->_states;
	}

	public function setStates($_states) {
		if (is_array($_states)){
			foreach ($_states as $state) {
				$this->addState($state);
			}
		}
		return $this;
	}

	public function getZip() {
		return $this->_zip;
	}

	public function setZip($_zip) {
		if (is_array($_zip) && !empty ($_zip)){
			foreach ($_zip as $zip) {
				$this->addZip($zip);
			}
		} elseif (is_string($_zip)){
			$this->addZip($_zip);
		}
		return $this;
	}

	public function addCountry($country){
		if (is_array($country) && array_key_exists('country', $country)){
			$country = $country['country'];
		} else {
			throw new Exception('Wrong parameter given');
		}
		if (!array_key_exists($country, $this->_countries)){
			array_push($this->_countries, array(
				'country'	=> $country,
				'name'		=>	Zend_Locale::getTranslation($country, 'Country')
			));
		}
		return $this;
	}
	
	public function addState($state) {
		$keys = array('id', 'country', 'name', 'state');
		if (is_array($state)){
			foreach ($keys as $key) {
				if (!array_key_exists($key, $state)){
					throw new Exception("Given element doesn't have required entry: $key");
				}
			}
			if (!array_key_exists($state['id'], $this->_states)){
				array_push($this->_states, $state);
			}
		} else {
			throw new Exception('Wrong parameter given');
		}
		return $this;
	}
	
	public function addZip($zip){
		if (is_array($zip)){
			if (array_key_exists('zip', $zip)){
				$zip = $zip['zip'];
			} else {
				$this->addZip($zip);
			}
		}
		if (!in_array($zip, $this->_zip)){
			array_push($this->_zip, $zip);
		}
		return $this;
	}
}