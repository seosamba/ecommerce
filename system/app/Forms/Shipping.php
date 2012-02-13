<?php
/**
 * Shipping form
 *
 * @author Eugene I. Nezhuta <theneiam@gmail.com>
 */

class Forms_Shipping extends Zend_Form {

	public function init() {

		$this->setLegend('Shipping address')
			->setAttribs(array(
				'id'     => 'shipping-user-address',
				'action' => '/plugin/shopping/run/calculateandcheckout/',
				'method' => Zend_Form::METHOD_POST
			)
		);

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'firstName',
			'id'       => 'first-name',
			'label'    => 'First Name *',
			'required' => true
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'  => 'lastName',
			'id'    => 'last-name',
			'label' => 'Last Name'
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'  => 'company',
			'id'    => 'company',
			'label' => 'Company'
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'email',
			'id'       => 'email',
			'label'    => 'E-mail *',
			'required' => true
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'shippingAddress1',
			'id'       => 'shipping-address1',
			'label'    => 'Shipping Adress 1 *',
			'required' => true
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'shippingAddress2',
			'id'       => 'shipping-address2',
			'label'    => 'Shipping Adress 2'
		)));

		$this->addElement(new Zend_Form_Element_Select(array(
			'name'         => 'country',
			'id'           => 'country',
			'label'        => 'Country *',
			'multiOptions' =>  Tools_Geo::getCountries(true),
			'required'     => true
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'city',
			'id'       => 'city',
			'label'    => 'City *',
			'required' => true
		)));

		$this->addElement(new Zend_Form_Element_Select(array(
			'name'         => 'state',
			'id'           => 'state',
			'label'        => 'State',
			'multiOptions' =>  Tools_Geo::getState(null, true),
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'zipCode',
			'id'       => 'zip-code',
			'label'    => 'ZIP Code *',
			'required' => true
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'phone',
			'id'       => 'phone',
			'label'    => 'Phone'
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'mobile',
			'id'       => 'Mobile',
			'label'    => 'Mobile'
		)));

		$this->addElement(new Zend_Form_Element_Textarea(array(
			'name'     => 'shippingInstructions',
			'id'       => 'shipping-instructions',
			'label'    => 'Shipping instructions'
		)));

		$this->addDisplayGroup(array(
			'firstName',
			'lastName',
			'company',
			'email',
			'shippingAddress1',
			'shippingAddress2'
		), 'lcol');

		$lcol = $this->getDisplayGroup('lcol')
			->setDecorators(array(
				'FormElements',
			    'Fieldset',
			    array('HtmlTag',array('tag'=>'div'))
		));

		$this->addDisplayGroup(array(
			'country',
			'city',
			'state',
			'zipCode',
			'phone',
			'mobile'
		), 'rcol');

		$this->addDisplayGroup(array(
			'shippingInstructions'
		), 'bottom');

		$lcol = $this->getDisplayGroup('lcol')
			->setDecorators(array(
				'FormElements',
			    'Fieldset',
			    array('HtmlTag',array('tag'=>'div'))
		));

		$rcol = $this->getDisplayGroup('rcol')
			->setDecorators(array(
				'FormElements',
			    'Fieldset',
			    array('HtmlTag',array('tag'=>'div'))
		));

		$bottom = $this->getDisplayGroup('bottom')
			->setDecorators(array(
				'FormElements',
			    'Fieldset',
			    array('HtmlTag',array('tag'=>'div'))
		));

		$this->addElement(new Zend_Form_Element_Submit(array(
					'name'   => 'calculateAndCheckout',
					'ignore' => true,
					'label'  => 'Calculate shipping and checkout'
		)));
		$this->getElement('calculateAndCheckout')->removeDecorator('DtDdWrapper')
			->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'cart-form-submit'));
	}



}
