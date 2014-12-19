<?php
/**
 * Shipping form
 *
 * @author Eugene I. Nezhuta <theneiam@gmail.com>
 */

class Forms_Checkout_Shipping extends Forms_Address_Abstract {

	public function init() {
		parent::init();

		$this->setLegend('Shipping address')
			->setAttribs(array(
				'id'     => 'shipping-user-address',
				'class'  => 'toaster-checkout',
				'action' => '/plugin/shopping/run/checkout/',
				'method' => Zend_Form::METHOD_POST
			))
			->setDecorators(array('FormElements', 'Form'));

		$this->getElement('lastname')->setRequired(true)->setAttrib('class', 'required');
		$this->getElement('email')->setRequired(true)->setAttrib('class', 'required');
		$this->getElement('address1')->setRequired(true)->setAttrib('class', 'required');
		$this->getElement('country')->setRequired(true)->setAttrib('class', 'country required');
		$this->getElement('city')->setRequired(true)->setAttrib('class', 'required');
		$this->getElement('zip')->setRequired(true)->setAttrib('class', 'required');


		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'mobile',
			'label'    => 'Mobile'
		)));

		$this->addElement(new Zend_Form_Element_Textarea(array(
			'name'     => 'shippingInstructions',
			'id'       => 'shipping-instructions',
			'label'    => 'Shipping instructions'
		)));

		$this->addDisplayGroups(array(
			'lcol' => array(
				'firstname',
				'lastname',
				'company',
				'email',
				'address1',
				'address2'
			),
			'rcol' => array(
				'country',
				'city',
				'state',
				'zip',
				'phone',
				'mobile'
			),
			'bottom' => array('shippingInstructions')
		));

		$lcol = $this->getDisplayGroup('lcol')
			->setDecorators(array(
				'FormElements',
			    'Fieldset'
		));

		$rcol = $this->getDisplayGroup('rcol')
			->setDecorators(array(
				'FormElements',
			    'Fieldset'
		));

		$bottom = $this->getDisplayGroup('bottom')
			->setDecorators(array(
				'FormElements',
			    'Fieldset'
		));

		$this->setElementDecorators(array(
			'ViewHelper',
			'Label',
			array('HtmlTag', array('tag' => 'p'))
		));

		$this->addElement(new Zend_Form_Element_Button(array(
			'name'   => 'calculateAndCheckout',
			'ignore' => true,
			'label'  => 'Calculate shipping and checkout',
            'type'   => 'submit',
			'decorators' => array('ViewHelper')
		)));

	}



}
