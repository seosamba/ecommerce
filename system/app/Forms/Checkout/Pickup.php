<?php
/**
 * Pickup.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Forms_Checkout_Pickup extends Zend_Form {

	public function init(){
		parent::init();

		$this->setLegend('Pick up person')
            ->setAttribs(array(
            'id'     => 'checkout-pickup',
            'class'  => array('toaster-checkout'),
            'method' => Zend_Form::METHOD_POST
        ));

        $this->setDecorators(array('FormElements', 'Form'));

        $this->setElementFilters(array(
            new Zend_Filter_StripTags(),
            new Zend_Filter_StringTrim()
        ));

        $this->addElement(new Zend_Form_Element_Text(array(
            'name'     => 'firstname',
            'label'    => 'First Name',
            'required' => true,
			'class'    => array('required')
        )));

        $this->addElement(new Zend_Form_Element_Text(array(
            'name'     => 'lastname',
            'label'    => 'Last Name',
            'required' => true,
            'class'    => array('required')
        )));

        $this->addElement(new Zend_Form_Element_Text(array(
            'name'     => 'phone',
            'label'    => 'Phone',
//            'required' => true
        )));

        $this->addElement(new Zend_Form_Element_Text(array(
            'name'       => 'email',
            'label'      => 'E-mail',
            'validators' => array('EmailAddress'),
            'required'   => true,
            'class'    => array('required')
        )));

        $this->addElement('hidden', 'check', array(
            'value' => Shopping::KEY_CHECKOUT_PICKUP,
            'decorators' => array('ViewHelper')
        ));

        $this->addElement('submit', 'submitpickup', array(
            'ignore' => true,
            'label'  => 'Next',
            'decorators' => array('ViewHelper')
        ));

        $this->setElementDecorators(array(
            'ViewHelper',
            'Errors',
            'Label',
            array('HtmlTag', array('tag' => 'p'))
        ));

        $this->getElement('submitpickup')->removeDecorator('Label');
	}

}
