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
			'multiOptions' => Tools_Misc::getCurrencyList()
		));
		
		$this->addElement('select', 'weightUnit', array(
			'label'	=> 'Weight unit',
			'multiOptions' => Tools_Misc::$_weightUnits
		));

        $list = array();
		array_walk(Tools_Plugins_Tools::getEnabledPlugins(), function($plugin, $index) use(&$list){
			$reflection = new Zend_Reflection_Class(ucfirst($plugin->getName()));
			if ($reflection->isSubclassOf('Tools_Cart_Cart')) {
				$list[$plugin->getName()] = $plugin->getName();
			}
		});

        $this->addElement('select', 'cartPlugin', array(
            'label' => 'Cart',
            'multiOptions' => $list
        ));

	}

}