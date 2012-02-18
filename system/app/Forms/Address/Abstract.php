<?php
/**
 * Address Form
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
abstract class Forms_Address_Abstract extends Zend_Form {

	public function init() {
		$this->setMethod(Zend_Form::METHOD_POST);

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'firstname',
			'label'    => 'First Name'
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'lastname',
			'label'    => 'Last Name *',
			'required' => true
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'  => 'company',
			'label' => 'Company'
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'email',
			'label'    => 'E-mail *',
			'required' => true
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'address1',
			'label'    => 'Address 1 *',
			'required' => true
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'address2',
			'label'    => 'Address 2'
		)));

		$this->addElement(new Zend_Form_Element_Select(array(
			'name'         => 'country',
			'label'        => 'Country *',
			'multiOptions' =>  Tools_Geo::getCountries(true),
			'required'     => true
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'city',
			'label'    => 'City *',
			'required' => true
		)));

		$this->addElement(new Zend_Form_Element_Select(array(
			'name'         => 'state',
			'label'        => 'State',
//			'multiOptions' =>  Tools_Geo::getState(null, true),
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'zip',
			'label'    => 'ZIP Code *',
			'required' => true
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'phone',
			'label'    => 'Phone'
		)));

	}
}
