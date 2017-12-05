<?php

/**
 * DisplaySettings
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Forms_BasicsSettings extends Zend_Form {

	public function init() {

        $translator =  Zend_Registry::get('Zend_Translate');

		$this->setLegend('Basics')
			 ->setDecorators(array('Form', 'FormElements'));

		$this->addElement('select', 'currency', array(
			'label' => 'Currency',
            'disableTranslator' => 'true',
            'class' => 'grid_6 alpha',
			'multiOptions' => Tools_Misc::getCurrencyList()
		));
		
		$this->addElement('select', 'weightUnit', array(
			'label'	=> 'Weight unit',
            'class' => 'grid_6 alpha',
			'multiOptions' => Tools_Misc::$_weightUnits
		));

        $this->addElement('select', 'lengthUnit', array(
            'label'	=> 'Length unit',
            'class' => 'grid_6 alpha',
            'multiOptions' => Tools_Misc::$_lengthUnits
        ));

		$this->addElement('checkbox', 'forceSSLCheckout', array(
			'label' => 'Force use HTTPS for checkout page',
            'class' => 'grid_6 alpha'
		));

        $this->addElement('checkbox', 'productLimit', array(
            'label' => $translator->translate('Use products stock threshold'),
            'class' => 'grid_6 alpha switcher'
        ));

        $this->addElement('text', 'productLimitInput', array(
            'label' => 'Default stock alert threshold',
            'class' => 'grid_6 alpha tooltip',
            'title' => $translator->translate('Set a default catalog-wide stock alert threshold for all products without a specified threshold value.'),
            'validators' => array(new Zend_Validate_Int())
        ));

        $this->addElement('text', 'operationalHours', array(
            'label' => 'store operational hours'
        ));

        $timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        array_pop($timezones);

        $this->addElement(new Zend_Form_Element_Select(
            array(
                'name' => 'timezone',
                'id' => 'user-timezone',
                'label' => 'Timezone',
                'class' => 'grid_6 alpha mb10px',
                'multiOptions' => array('0' => $translator->translate('Select timezone')) + array_combine($timezones, $timezones)
            )
        ));
	}

}