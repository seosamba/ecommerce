<?php
/**
 * Pickup.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Forms_Checkout_Pickup extends Zend_Form {

    protected $_mobilecountrycode = null;

    protected $_mobile = null;

    public function setMobilecountrycode($_mobilecountrycode)
    {
        $this->_mobilecountrycode = $_mobilecountrycode;
        $this->getElement('mobilecountrycode')->setValue($this->_mobilecountrycode);
    }

    public function getMobilecountrycode()
    {
        return $this->_mobilecountrycode;
    }

    public function setMobile($_mobile)
    {
        $countryPhoneCode = Zend_Locale::getTranslation($this->_mobilecountrycode, 'phoneToTerritory');
        $this->_mobile = preg_replace('/^(\+' . $countryPhoneCode . ')(\d+)/', '$2', $_mobile);
        $this->getElement('mobile')->setValue($this->_mobile);
    }

    public function getMobile()
    {
        return $this->_mobile;
    }

	public function init(){
		parent::init();

		$this->setLegend('Enter pick up information')
            ->setAttribs(array(
            'id'     => 'checkout-pickup',
            'class'  => 'toaster-checkout',
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
            'value'    => ''
//            'required' => true
        )));

        $this->addElement(new Zend_Form_Element_Select(array(
            'name'         => 'mobilecountrycode',
            'label'        => null,
            'multiOptions' => Tools_System_Tools::getCountryPhoneCodesList(),
            'value'        => $this->_mobilecountrycode,
            'style'        => 'width: 41.667%;'
        )));

        $this->addElement(new Zend_Form_Element_Text(array(
                'name'     => 'mobile',
                'label'    => null,
                'style'    => 'width: 58.333%;',
                'value'    => $this->_mobile
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
        $this->getElement('mobilecountrycode')->removeDecorator('HtmlTag');
        $this->getElement('mobile')->removeDecorator('HtmlTag');
	}

}
