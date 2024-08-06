<?php

/**
 * DisplaySettings
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Forms_BasicsSettings extends Zend_Form {

	public function init() {
		$this->setLegend('Basics')
			 ->setDecorators(array('Form', 'FormElements'));

        $translator = Zend_Registry::get('Zend_Translate');

		$this->addElement('select', 'currency', array(
			'label' => $translator->translate('Currency'),
            'disableTranslator' => 'true',
            'class' => 'grid_6 alpha',
			'multiOptions' => Tools_Misc::getCurrencyList()
		));

        $this->addElement('select', 'currencyCountry', array(
            'label' => $translator->translate('Currency country'),
            'disableTranslator' => 'true',
            'class' => 'grid_6 alpha',
            'multiOptions' => array('0' => $translator->translate('Select country')) + Tools_Geo::getCountries(true)
        ));
		
		$this->addElement('select', 'weightUnit', array(
			'label'	=> $translator->translate('Weight unit'),
            'class' => 'grid_6 alpha',
			'multiOptions' => Tools_Misc::$_weightUnits
		));

        $this->addElement('select', 'lengthUnit', array(
            'label'	=> $translator->translate('Length unit'),
            'class' => 'grid_6 alpha',
            'multiOptions' => Tools_Misc::$_lengthUnits
        ));

		$this->addElement('checkbox', 'forceSSLCheckout', array(
			'label' => $translator->translate('Force use HTTPS for checkout page'),
            'class' => 'grid_6 alpha'
		));

        $this->addElement('checkbox', 'deductSetStock', array(
            'label' => $translator->translate('Deduct products used in sets from inventory'),
            'class' => 'grid_6 alpha'
        ));

        $this->addElement('checkbox', 'minimumOrder', array(
            'label' => $translator->translate('Enable individual product minimum order handling (set values in product screen)'),
            'class' => 'grid_6 alpha'
        ));

        $this->addElement('checkbox', 'smartFilter', array(
            'label' => $translator->translate('Smart product list filter (hides unavailable product filter combinations)'),
            'class' => 'grid_6 alpha'
        ));

    }

}
