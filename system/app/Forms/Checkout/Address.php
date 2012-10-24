<?php
/**
 * Shipping form
 *
 * @author Eugene I. Nezhuta <theneiam@gmail.com>
 */

class Forms_Checkout_Address extends Forms_Address_Abstract {

	public function init() {
		parent::init();

		$this->setLegend('Enter your shipping address')
			->setAttribs(array(
				'id'     => 'checkout-user-address',
				'class'  => array('toaster-checkout', 'address-form'),
				'method' => Zend_Form::METHOD_POST
			));

		$this->setDecorators(array('FormElements', 'Form'));

		$this->setElementFilters(array(
			new Zend_Filter_StripTags()
		));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'mobile',
			'label'    => 'Mobile'
		)));

		// setting required fields
		$this->getElement('lastname')->setRequired(true)->setAttrib('class', 'required');
		$this->getElement('email')->setRequired(true)
				->setAttrib('class', 'required')
				->setValidators(array('EmailAddress'));

		$this->addDisplayGroups(array(
			'lcol' => array(
				'firstname',
				'lastname',
				'company',
				'email',
				'phone',
				'mobile'

			),
			'rcol' => array(
				'address1',
				'address2',
				'city',
				'zip',
				'country',
				'state'
			)
		));

		$lcol = $this->getDisplayGroup('lcol')
			->setDecorators(array(
				'FormElements',
			    'Fieldset',
		))->setAttrib('class', 'col');

		$rcol = $this->getDisplayGroup('rcol')
			->setDecorators(array(
				'FormElements',
			    'Fieldset',
		))->setAttrib('class', 'col');

		$this->setElementDecorators(array(
			'ViewHelper',
			'Label',
			array('HtmlTag', array('tag' => 'div'))
		));

		$this->addElement('hidden', 'check', array(
			'value' => Shopping::KEY_CHECKOUT_ADDRESS,
			'decorators' => array('ViewHelper')
		));

		$this->addElement(new Zend_Form_Element_Submit(array(
			'name'   => 'checkout',
			'ignore' => true,
			'label'  => 'Next',
			'decorators' => array('ViewHelper')
		)));

	}

}
