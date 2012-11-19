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
        
        $termsAndConditionsPage = current(Application_Model_Mappers_PageMapper::getInstance()->fetchByOption(Shopping::OPTION_STORE_SHIPPING_TERMS));
        $notesLabel = 'I authorized the parsel to be left at the delivery address without signature.';
        if(!empty($termsAndConditionsPage)){
            $notesLabel .= ' <a href="'.$websiteUrl.$termsAndConditionsPage->getUrl().'" target = _blank class="terms-page" title="Shipping Policy">Shipping Policy</a>';
        }
        
        $this->addElement(new Zend_Form_Element_Checkbox(array(
			'name'          => 'shippingNotes',
			'label'         => $notesLabel,
            'required'      => true,
            'checkedValue'  => 1,
            'allowEmpty'    => false,
            'uncheckedValue'=> null
        )));
        
        $this->addElement(new Zend_Form_Element_Textarea(array(
			'name'     => 'notes',
			'label'    => 'Dilivery comments',
            'rows'     => '3',
            'cols'     => '45'
		)));
               
        $this->getElement('notes')->addFilter('StripTags');
                
		$this->addElement(new Zend_Form_Element_Text(array(
			'name'     => 'mobile',
			'label'    => 'Mobile'
		)));

		// setting required fields
		$this->getElement('lastname')->setRequired(true)->setAttrib('class', 'required');
		$this->getElement('email')->setRequired(true)
				->setAttrib('class', 'required')
				->setValidators(array('EmailAddress'));
		$this->getElement('zip')->setRequired(true);
        $this->getElement('shippingNotes')->setRequired(true)->setAttrib('class', 'required');

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
                'shippingNotes'
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
        
        $this->getElement('shippingNotes')->getDecorator('Label')->setOption('escape',false);

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

	}

}
