<?php

/**
 * Settings
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Forms_Settings extends Zend_Form {

	public function init() {
		$this->setName('shopping-settings')
			->setAction($this->getView()->websiteUrl . 'plugin/shopping/run/config' )
			->setDecorators(array('Form', 'FormElements'));
		
		$shopInfo = new Zend_Form();
		
		$shopInfo->addElement('text', 'company', array(
			'label' => 'Company name'
		));
		
		$shopInfo->addElement('text', 'email', array(
			'label' => 'Company e-mail'
		));
		
		$shopInfo->addElement('text', 'address1', array(
			'label' => 'Address 1'
		));
		
		$shopInfo->addElement('text', 'address2', array(
			'label' => 'Address 2'
		));
		
		$shopInfo->addElement('text', 'city', array(
			'label' => 'City'
		));
		
		$shopInfo->addElement('select', 'state', array(
			'label' => 'State/Province/Region'
		));
		
		$shopInfo->addElement('text', 'zip', array(
			'label' => 'Zip/Postal Code'
		));
		
		$coutryList  = Tools_Geo::getCountries();
		
		$shopInfo->addElement('select', 'country', array(
			'label' => 'Country',
			'multiOptions' => $coutryList
		));
		
		
		$this->addSubForm($shopInfo, 'store-info');
	}

}