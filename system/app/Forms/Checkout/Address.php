<?php
/**
 * Shipping form
 *
 * @author Eugene I. Nezhuta <theneiam@gmail.com>
 */

class Forms_Checkout_Address extends Forms_Address_Abstract {

	const CSS_CLASS_REQUIRED = 'required';

	public function init() {
		parent::init();

		$this->setLegend('Enter your shipping address')
			->setAttribs(array(
				'id'     => 'checkout-user-address',
				'class'  => 'toaster-checkout address-form',
				'method' => Zend_Form::METHOD_POST
			));

		$this->setDecorators(array('FormElements', 'Form'));

		$this->setElementFilters(array(
			new Zend_Filter_StripTags()
		));
        $websiteUrl = Zend_Controller_Action_HelperBroker::getExistingHelper('website')->getUrl();

		$shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();

		if (isset($shoppingConfig[Shopping::SHIPPING_TOC_STATUS]) && (bool)$shoppingConfig[Shopping::SHIPPING_TOC_STATUS]){
			if (!isset($shoppingConfig[Shopping::SHIPPING_TOC_LABEL]) || empty($shoppingConfig[Shopping::SHIPPING_TOC_LABEL]) ){
				$tocPage = Application_Model_Mappers_PageMapper::getInstance()->fetchByOption(Shopping::OPTION_STORE_SHIPPING_TERMS);
				$shippingTocLabel = 'I authorize the parcel to be left at the delivery address without signature.';
		        if(!empty($tocPage)){
			        $tocPage = current($tocPage);
		            $shippingTocLabel .= ' <a href="'.$websiteUrl.$tocPage->getUrl().'" target = _blank class="terms-page" title="Shipping Policy">Shipping Policy</a>';
		        }
			} else {
				$shippingTocLabel = $shoppingConfig[Shopping::SHIPPING_TOC_LABEL];
			}

			$shippingTocCheckbox = new Zend_Form_Element_Checkbox(array(
				'name'          => 'shippingToc',
				'label'         => $shippingTocLabel,
		        'required'      => true,
		        'checkedValue'  => 1,
		        'allowEmpty'    => false,
		        'uncheckedValue'=> null
		    ));
			$shippingTocCheckbox->addErrorMessage('This field is required');
	        $this->addElement($shippingTocCheckbox);
		}

        $this->addElement(new Zend_Form_Element_Textarea(array(
			'name'     => 'notes',
			'label'    => 'Delivery Comments',
            'rows'     => '3',
            'cols'     => '45'
		)));
               
        $this->getElement('notes')->addFilter('StripTags');

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
            'value'    => '',
            'style'    => 'width: 58.333%;'
		)));

		$emailValidator = new Zend_Validate_EmailAddress(Zend_Validate_Hostname::ALLOW_DNS | Zend_Validate_Hostname::ALLOW_LOCAL);
		$emailValidator->setMessages(array(
			Zend_Validate_EmailAddress::INVALID_FORMAT => "'%value%' is not a valid email address",
		));

		// setting required fields
		$this->getElement('email')->setValidators(array($emailValidator));

		$this->addDisplayGroups(array(
			'lcol' => array(
				'firstname',
				'lastname',
				'company',
				'email',
				'phone',
				'mobile',
                'notes'
			),
			'rcol' => array(
				'address1',
				'address2',
				'city',
				'zip',
				'country',
				'state',
                isset($shippingTocCheckbox) ? 'shippingToc' : null
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
			'Errors',
			array('HtmlTag', array('tag' => 'p'))
		));

		if (isset($shippingTocCheckbox)){
			$shippingTocCheckbox->getDecorator('Label')->setOption('escape',false);
		}

		$this->addElement('hidden', 'step', array(
			'value' => Shopping::KEY_CHECKOUT_ADDRESS,
			'decorators' => array('ViewHelper'),
			'ignore'    => true
		));

		$this->addElement(new Zend_Form_Element_Submit(array(
			'name'   => 'checkout',
			'ignore' => true,
			'label'  => 'Next',
            'type'   => 'submit',
			'decorators' => array('ViewHelper')
		)));

		$this->resetRequiredFields(array(
			'lastname', 'email', 'zip', 'shippingToc'
		));

        $this->getElement('step')->removeDecorator('HtmlTag');
        $this->getElement('mobilecountrycode')->removeDecorator('HtmlTag');
        $this->getElement('mobile')->removeDecorator('HtmlTag');
	}

	/**
	 * Reset form required fields
	 * @param $fields array List of required fields names
	 * @return $this
	 */
	public function resetRequiredFields($fields) {
		if (empty($fields)) return $this;

		if (!is_array($fields)){
			$fields = array($fields);
		}

		foreach ($this->getElements() as $element) {
			if (in_array($element->getName(), $fields)){
				$element->setRequired(true);
			} else {
				$element->setRequired(false);
			}

			$cssClass = $element->getAttrib('class');
			if ($element->isRequired()){
				$cssClass .= strpos($cssClass, self::CSS_CLASS_REQUIRED) !== false ? '' : ' '.self::CSS_CLASS_REQUIRED;
			} else {
				if (!empty($cssClass)){
					$cssClass = str_replace(self::CSS_CLASS_REQUIRED, '', $cssClass);
				}
			}

			$element->setAttrib('class', trim($cssClass));
		}

		return $this;
	}

}
