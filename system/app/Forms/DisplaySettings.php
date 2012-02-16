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

        $plugins = Tools_Plugins_Tools::getEnabledPlugins();
        $list = array();
        foreach ($plugins as $plugin) {
//            @todo: add some check if it's a cart plugin
            $list[$plugin->getName()] = $plugin->getName();
        }

        $this->addElement('select', 'cartPlugin', array(
            'label' => 'Cart',
            'multiOptions' => $list
        ));

	}

}