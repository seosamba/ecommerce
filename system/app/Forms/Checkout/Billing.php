<?php
/**
 * Shipping form
 *
 * @author Eugene I. Nezhuta <theneiam@gmail.com>
 */

class Forms_Checkout_Billing extends Forms_Address_Abstract {

	public function init() {
		parent::init();

		$this->setLegend('Billing address')
			->setAttribs(array(
				'id'     => 'billing-user-address',
				'class'  => 'toaster-checkout',
				'action' => '/plugin/shopping/run/checkout/',
				'method' => Zend_Form::METHOD_POST
			));

		$this->setDecorators(array('FormElements', 'Form'));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'mobile',
			'label'    => 'Mobile'
		)));

		// setting required fields
		$this->getElement('lastname')->setRequired(true)->setAttrib('class', 'required');
		$this->getElement('email')->setRequired(true)->setAttrib('class', 'required');

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
			)
		));

		$lcol = $this->getDisplayGroup('lcol')
			->setDecorators(array(
				'FormElements',
			    'Fieldset',
		));

		$rcol = $this->getDisplayGroup('rcol')
			->setDecorators(array(
				'FormElements',
			    'Fieldset',
		));

		$this->setElementDecorators(array(
			'ViewHelper',
			'Label',
			array('HtmlTag', array('tag' => 'div'))
		));

		$this->addElement(new Zend_Form_Element_Submit(array(
			'name'   => 'checkout',
			'ignore' => true,
			'label'  => 'Checkout',
			'decorators' => array('ViewHelper')
		)));

	}

}
