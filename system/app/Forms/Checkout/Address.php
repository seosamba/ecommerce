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
        $websiteUrl = Zend_Controller_Action_HelperBroker::getExistingHelper('website')->getUrl();

		$shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();

		if (isset($shoppingConfig[Shopping::SHIPPING_TOC_STATUS]) && (bool)$shoppingConfig[Shopping::SHIPPING_TOC_STATUS]){
			if (!isset($shoppingConfig[$shoppingConfig[Shopping::SHIPPING_TOC_LABEL]]) || empty($shoppingConfig[Shopping::SHIPPING_TOC_LABEL]) ){
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
                
		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'mobile',
			'label'    => 'Mobile'
		)));

		$emailValidator = new Zend_Validate_EmailAddress(Zend_Validate_Hostname::ALLOW_DNS | Zend_Validate_Hostname::ALLOW_LOCAL);
		$emailValidator->setMessages(array(
			Zend_Validate_EmailAddress::INVALID_FORMAT => "'%value%' is not a valid email address",
		));

		// setting required fields
		$this->getElement('lastname')->setRequired(true);
		$this->getElement('email')->setRequired(true)->setValidators(array($emailValidator));
		$this->getElement('zip')->setRequired(true);

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
			array('HtmlTag', array('tag' => 'div'))
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
			'decorators' => array('ViewHelper')
		)));

		foreach ($this->getElements() as $element){
			if ($element->isRequired()) {
				$currentClass = $element->getAttrib('class');
				if (!empty($currentClass)){
					$element->setAttrib('class', $currentClass.' required');
				} else {
					$element->setAttrib('class', 'required');
				}
			}
		}
	}

}
