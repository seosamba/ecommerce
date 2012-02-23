<?php
/**
 * Shipping form
 *
 * @author Eugene I. Nezhuta <theneiam@gmail.com>
 */

class Forms_Shipping extends Forms_Address_Abstract {

	public function init() {
		parent::init();

		$this->setLegend('Shipping address')
			->setAttribs(array(
				'id'     => 'shipping-user-address',
				'class'  => 'toaster-checkout',
				'action' => '/plugin/shopping/run/checkout/',
				'method' => Zend_Form::METHOD_POST
			));

		$this->setDecorators(array('FormElements', 'Form'));

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
			    'Fieldset',
//			    array('HtmlTag',array('tag'=>'div'))
		));

		$rcol = $this->getDisplayGroup('rcol')
			->setDecorators(array(
				'FormElements',
			    'Fieldset',
//			    array('HtmlTag',array('tag'=>'div'))
		));

		$bottom = $this->getDisplayGroup('bottom')
			->setDecorators(array(
				'FormElements',
			    'Fieldset',
//			    array('HtmlTag',array('tag'=>'div'))
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
