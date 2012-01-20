<?php

class Forms_PerAmount extends Zend_Form {

	public function init() {

		$this->addElement(new Zend_Form_Element_Radio(array(
			'name'         => 'shippingType',
			'class'        => 'shipping-type',
			'multiOptions' => array(
				1 => 'Shipping per order amount'
			)
		)));

		//amount limit
		$this->addElement(new Zend_Form_Element_Text(array(
			'name'      => 'limit1',
			'label'     => 'up to',
			'class'     => 'incolumn numonly',
			'belongsTo' => 'limit'
		)));
		$this->addElement(new Zend_Form_Element_Text(array(
			'name'      => 'limit2',
			'label'     => 'up to',
			'class'     => 'incolumn numonly',
			'belongsTo' => 'limit'
		)));
		$this->addElement(new Zend_Form_Element_Text(array(
			'name'      => 'limit3',
			'label'     => 'up to',
			'class'     => 'incolumn numonly',
			'belongsTo' => 'limit'
		)));

		//national
		$this->addElement(new Zend_Form_Element_Text(array(
			'name'      => 'national1',
			'class'     => 'incolumn numonly',
			'belongsTo' => 'national'
		)));
		$this->addElement(new Zend_Form_Element_Text(array(
			'name'      => 'national2',
			'class'     => 'incolumn numonly',
			'belongsTo' => 'national'
		)));
		$this->addElement(new Zend_Form_Element_Text(array(
			'name'      => 'national3',
			'class'     => 'incolumn numonly',
			'belongsTo' => 'national'
		)));

		//international
		$this->addElement(new Zend_Form_Element_Text(array(
			'name'      => 'international1',
			'class'     => 'incolumn numonly',
			'belongsTo' => 'international'
		)));
		$this->addElement(new Zend_Form_Element_Text(array(
			'name'      => 'international2',
			'class'     => 'incolumn numonly',
			'belongsTo' => 'international'
		)));
		$this->addElement(new Zend_Form_Element_Text(array(
			'name'      => 'international3',
			'class'     => 'incolumn numonly',
			'belongsTo' => 'international'
		)));

		//free shipping
		$this->addElement(new Zend_Form_Element_Text(array(
			'name'  => 'freeShipping',
			'label' => 'Free shipping for order over'
		)));

		$this->addElement(new Zend_Form_Element_Select(array(
			'name'         => 'freeshipOptions',
			'label'        => 'delivery',
			'multiOptions' => array(
				'0'        => 'select',
				'national'      => 'National',
				'international' => 'International',
				'both'          => 'Both'
			)
		)));


	}

}
