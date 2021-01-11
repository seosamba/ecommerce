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

        $translator =  Zend_Registry::get('Zend_Translate');

        $this->setLegend('Sign up')
            ->setAttribs(array(
            'id'     => 'checkout-signup',
            'class'  => 'toaster-checkout signup-form',
            'method' => Zend_Form::METHOD_POST
        ));

        $this->setDecorators(array('FormElements', 'Form'));

        $this->addElement(new Zend_Form_Element_Checkbox(array(
            'name'       => 'subscribed',
            'id'         => 'user-subscribed',
            'label'      => 'Subscribe',
            'required'   => false,
            'value'      => $this->_subscribed
        )));

        $this->addElement(new Zend_Form_Element_Select(array(
            'name'         => 'prefix',
            'id'           => 'prefix',
            'label'        => $translator->translate('Prefix'),
            'value'        => $this->_prefix,
            'multiOptions' => array('' => $translator->translate('Select')) +  Tools_System_Tools::getAllowedPrefixesList()
        )));

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

        $this->getElement('email')->setErrorMessages(array("'%value%'".' '.$translator->translate('is not a valid email address')));

        $this->addElement(new Zend_Form_Element_Select(array(
                'name'         => 'mobilecountrycode',
                'label'        => null,
                'multiOptions' => Tools_System_Tools::getFullCountryPhoneCodesList(true, array(), true),
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

        $this->addElement(
            new Zend_Form_Element_Password(array(
                'name' => 'customerPassword',
                'id' => '',
                'label' => 'Password',
                'required' => true,
                'validators' => array(
                    'NotEmpty',
                    new Zend_Validate_StringLength(array(
                        'encoding' => 'UTF-8',
                        'min' => 4
                    )),
                ),
                'filters' => array('StringTrim')
            ))
        );

        $this->getElement('customerPassword')->getValidator('stringLength')->setMessage('*** is less than 4 characters long');
        $this->getElement('customerPassword')->getValidator('NotEmpty')->setMessage('Please enter a password');


        $this->addElement(
            new Zend_Form_Element_Password(array(
                'name' => 'customerPassConfirmation',
                'id' => '',
                'label' => 'Confirm password',
                'required' => true,
                'validators' => array(
                    'NotEmpty',
                    array('identical', false, array('token' => 'customerPassword'))
                ),
            ))
        );

        $this->getElement('customerPassConfirmation')->getValidator('NotEmpty')->setMessage('Please enter a password');
        $this->getElement('customerPassConfirmation')->getValidator('identical')->setMessage('Please make sure that your passwords match');

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
