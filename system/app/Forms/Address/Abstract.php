<?php
/**
 * Address Form
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
abstract class Forms_Address_Abstract extends Zend_Form {

	public static $_zipLabel = array(
		'default' => 'ZIP Code',
		'AU' => 'Postcode'
	);

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
        $this->_mobile = preg_replace('/^(\+' . $countryPhoneCode . ')(\d+)/', '$2', $_mobile); //{
        $this->getElement('mobile')->setValue($this->_mobile);
    }

	public function init() {
		$shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();

		$this->setMethod(Zend_Form::METHOD_POST);

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'firstname',
			'label'    => 'First Name'
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'lastname',
			'label'    => 'Last Name',
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'  => 'company',
			'label' => 'Company'
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'email',
			'label'    => 'E-mail'
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'address1',
			'label'    => 'Address 1',
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'address2',
			'label'    => 'Address 2'
		)));

		$this->addElement(new Zend_Form_Element_Select(array(
			'name'         => 'country',
            'class'        => 'country',
			'label'        => 'Country',
			'multiOptions' =>  Tools_Geo::getCountries(true)
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'city',
			'label'    => 'City'
		)));

		$this->addElement(new Zend_Form_Element_Select(array(
			'name'         => 'state',
			'label'        => 'State',
            'class'        => 'state',
			'multiOptions' =>  Tools_Geo::getState(null, true)
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'zip',
			'label'    => self::$_zipLabel[!empty($shoppingConfig['country']) && array_key_exists($shoppingConfig['country'], self::$_zipLabel) ? $shoppingConfig['country'] : 'default' ]
		)));

		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'phone',
			'label'    => 'Phone',
            'value'    => '',
            'placeholder' => '+'.Zend_Locale::getTranslation(Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('country'), 'phoneToTerritory')
		)));

//		$this->addElement(new Zend_Form_Element_Hash(array(
//			'name'       => 'nocsrf',
//			'salt'       => SITE_NAME
//		)));

		$this->getElement('state')->addValidator(new Zend_Validate_Db_RecordExists(array(
			'table' => 'shopping_list_state',
			'field' => 'id'
		)), true);
	}

	public function setDefault($name, $value) {
		switch ($name) {
			case 'state':
				$list = Tools_Geo::getState($this->getElement('country')->getValue(), true);
				if (empty ($list) || !array_key_exists($value, $list)) {
					return $this;
				}
				if (!count($this->getElement('country')->getMultiOptions())) {
					$this->getElement('state')->setMultiOptions(Tools_Geo::getState($value, true));
				}
				$this->getElement($name)->setMultiOptions($list);
				break;
			case 'country':
				$this->getElement('state')->setMultiOptions(Tools_Geo::getState($value, true));
				break;
		}
		parent::setDefault($name, $value);
	}

	public function isValid($data) {
		$valid = parent::isValid($data);

		foreach ($this->getElements() as $element) {
			if ($element->hasErrors()){
				$currentClass = $element->getAttrib('class');
				if (!empty($currentClass)){
					$element->setAttrib('class', $currentClass.' notvalid');
				} else {
					$element->setAttrib('class', 'notvalid');
				}
			}
		}

		return $valid;
	}
}
