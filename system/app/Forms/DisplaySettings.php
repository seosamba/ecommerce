<?php

/**
 * DisplaySettings
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Forms_DisplaySettings extends Zend_Form {

	public function init() {
		$this->setLegend('Units')
			 ->setDecorators(array('Form', 'FormElements'));
		
		$this->addElement('select', 'currency', array(
			'label' => 'Currency',
			'multiOptions' => Tools_Misc::$_currencies
		));
		
		$this->addElement('select', 'weightUnit', array(
			'label'	=> 'Weight unit',
			'multiOptions' => Tools_Misc::$_weightUnits
		));
		
	}

}