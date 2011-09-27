<?php

/**
 * Settings
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Forms_GeneralSettings extends Zend_Form {

	public function init() {
		$this->setLegend('Store info')
			->setDecorators(array('Form', 'FormElements'));
		
		
		$this->addElement('text', 'company', array(
			'label' => 'Company name'
		));
		
		$this->addElement('text', 'email', array(
			'label' => 'Company e-mail'
		));
		
		$this->addElement('text', 'address1', array(
			'label' => 'Address 1'
		));
		
		$this->addElement('text', 'address2', array(
			'label' => 'Address 2'
		));
		
		$coutryList  = Tools_Geo::getCountries();
		
		$this->addElement('select', 'country', array(
			'label' => 'Country',
			'multiOptions' => $coutryList
		));
		
		$this->addElement('text', 'city', array(
			'label' => 'City'
		));
		
		$this->addElement('select', 'state', array(
			'label' => 'State/Province/Region',
			'multiOptions' => Tools_Geo::getState($this->getElement('country')->getValue())
		));
		
		$this->addElement('text', 'zip', array(
			'label' => 'Zip/Postal Code'
		));
		
		
	}

	public function setDefault($name, $value) {
		switch ($name) {
			case 'state':
				$list = Tools_Geo::getState($this->getElement('country')->getValue());
				if (empty ($list) || !array_key_exists($value, $list)) {
					return $this;
				}
				$this->getElement($name)->setMultiOptions($list);
				break;
			case 'country':
				$this->getElement('state')->setMultiOptions(Tools_Geo::getState($value));
				break;
		}
		parent::setDefault($name, $value);
	}
}