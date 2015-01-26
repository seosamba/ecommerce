<?php
/**
 * Signup.php
 * @author Eugene I. Nezhuta <theneiam@gmail.com>
 * Date: 10/10/12
 * Time: 4:04 PM
 */
class Forms_Signup extends Zend_Form {

    protected $_mobilecountrycode = null;

    public function setMobilecountrycode($_mobilecountryphonecode)
    {
        $this->_mobilecountrycode = $_mobilecountryphonecode;
        $this->getElement('mobilecountrycode')->setValue($this->_mobilecountrycode);
    }

    public function getCurrentTheme()
    {
        return $this->_mobilecountrycode;
    }

    public function init() {

        parent::init();

        $this->setLegend('Sign up')
            ->setAttribs(array(
            'id'     => 'checkout-signup',
            'class'  => 'toaster-checkout signup-form',
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
            'name'       => 'email',
            'label'      => 'E-mail',
            'validators' => array('EmailAddress'),
            'required'   => true,
	        'class'      => array('required')
        )));

        $this->addElement(new Zend_Form_Element_Select(array(
                'name'         => 'mobilecountrycode',
                'label'        => null,
                'multiOptions' => Tools_System_Tools::getCountryPhoneCodesList(),
                'value'        => Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('country'),
                'style'        => 'width: 41.667%;'
        )));

        $this->addElement(new Zend_Form_Element_Text(array(
                'name'     => 'mobile',
                'label'    => null,
                'style'    => 'width: 58.333%;',
                'value'    => ''
        )));

        $this->addElement('hidden', 'step', array(
            'value' => Shopping::KEY_CHECKOUT_SIGNUP,
            'decorators' => array('ViewHelper'),
	        'ignore'    => true
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

        $this->setElementFilters(array(
            new Zend_Filter_StripTags(),
            new Zend_Filter_StringTrim()
        ));

        $this->getElement('signup')->removeDecorator('Label');
        $this->getElement('signup')->removeDecorator('HtmlTag');
        $this->getElement('step')->removeDecorator('HtmlTag');
        $this->getElement('mobilecountrycode')->removeDecorator('HtmlTag');
        $this->getElement('mobile')->removeDecorator('HtmlTag');

    }

}
