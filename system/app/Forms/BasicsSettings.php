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

		$this->addElement('checkbox', 'forceSSLCheckout', array(
			'label' => 'Force use HTTPS for checkout page',
            'class' => 'grid_6 alpha'
		));

        $this->addElement('text', 'productLimit', array(
            'label' => 'Default stock alert threshold',
            'class' => 'grid_6 alpha tooltip',
            'title' => $translator->translate('Set a default catalog-wide stock alert threshold for all products without a specified threshold value.'),
            'validators' => array(new Zend_Validate_Int())
        ));
	}

}