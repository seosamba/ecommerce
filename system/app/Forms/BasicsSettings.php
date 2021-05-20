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
            'label' => $translator->translate('Deduct from inventory of products used in sets'),
            'class' => 'grid_6 alpha'
        ));

        $this->addElement('checkbox', 'minimumOrder', array(
            'label' => $translator->translate('Minimum order'),
            'class' => 'grid_6 alpha'
        ));

        $this->addElement('text', 'operationalHours', array(
            'label' => $translator->translate('store operational hours')
        ));

        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        array_pop($timezones);

        $this->addElement(new Zend_Form_Element_Select(
            array(
                'name' => 'timezone',
                'id' => 'user-timezone',
                'label' => $translator->translate('Timezone'),
                'class' => 'grid_6 alpha mb10px',
                'multiOptions' => array('0' => $translator->translate('Select timezone')) + array_combine($timezones, $timezones)
            )
        ));

        $this->addElement('checkbox', 'smartFilter', array(
            'label' => $translator->translate('Smart product list filter'),
            'class' => 'grid_6 alpha'
        ));

    }

}
