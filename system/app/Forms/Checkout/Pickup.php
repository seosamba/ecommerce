<?php
/**
 * Pickup.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Forms_Checkout_Pickup extends Zend_Form {

	public function init(){
		parent::init();

		$this->setLegend('Enter pick up information')
            ->setAttribs(array(
            'id'     => 'checkout-pickup',
            'class'  => array('toaster-checkout'),
            'method' => Zend_Form::METHOD_POST
        ));

        $this->setDecorators(array('FormElements', 'Form'));

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
            'value'    => '+'.Zend_Locale::getTranslation(Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('country'), 'phoneToTerritory')
//            'required' => true
        )));

        $this->addElement(new Zend_Form_Element_Text(array(
                'name'     => 'mobile',
                'label'    => 'Mobile'
        )));

        $this->addElement(new Zend_Form_Element_Text(array(
            'name'       => 'email',
            'label'      => 'E-mail',
            'validators' => array('EmailAddress'),
            'required'   => true,
            'class'    => array('required')
        )));

        $this->addElement('hidden', 'step', array(
            'value' => Shopping::KEY_CHECKOUT_PICKUP,
            'decorators' => array('ViewHelper'),
	        'ignore'    => true 
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

        $this->setElementFilters(array(
            new Zend_Filter_StripTags(),
            new Zend_Filter_StringTrim()
        ));

        $this->getElement('submitpickup')->removeDecorator('Label');
        $this->getElement('submitpickup')->removeDecorator('HtmlTag');
        $this->getElement('step')->removeDecorator('HtmlTag');
	}

}
