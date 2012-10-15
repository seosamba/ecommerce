<?php
/**
 * Signup.php
 * @author Eugene I. Nezhuta <theneiam@gmail.com>
 * Date: 10/10/12
 * Time: 4:04 PM
 */
class Forms_Signup extends Zend_Form {

    public function init() {

        parent::init();

        $this->setLegend('Sign up')
            ->setAttribs(array(
            'id'     => 'checkout-signup',
            'class'  => array('toaster-checkout-signup', 'signup-form'),
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
            'required' => true
        )));

        $this->addElement(new Zend_Form_Element_Text(array(
            'name'     => 'lastname',
            'label'    => 'Last Name',
            'required' => true
        )));

        $this->addElement(new Zend_Form_Element_Text(array(
            'name'       => 'email',
            'label'      => 'E-mail',
            'validators' => array('EmailAddress'),
            'required'   => true
        )));

        $this->addElement('hidden', 'check', array(
            'value' => Shopping::KEY_CHECKOUT_SIGNUP,
            'decorators' => array('ViewHelper')
        ));

        $this->addElement(new Zend_Form_Element_Submit(array(
            'name'   => 'signup',
            'ignore' => true,
            'label'  => 'Next',
            'decorators' => array('ViewHelper')
        )));

        $this->setElementDecorators(array(
            'ViewHelper',
            'Errors',
            'Label',
            array('HtmlTag', array('tag' => 'p'))
        ));

        $this->getElement('signup')->removeDecorator('Label');

    }

}
