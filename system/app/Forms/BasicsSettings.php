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
	}

}