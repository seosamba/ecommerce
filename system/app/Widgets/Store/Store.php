<?php
/**
 * Store Widget - small proxy for cart plugin
 * It allows you to put {$store:%widget_name%} instead of {$plugin:%plugin_name%:%method_name%}
 * This shorthand way allows you easy switch between cart plugins without any impact for site
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Widgets_Store_Store extends Widgets_Abstract {

    protected function _load() {
        $cartPluginName = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('cartPlugin');
        if ($cartPluginName){
            $cart = Tools_Factory_PluginFactory::createPlugin($cartPluginName, $this->_options, $this->_toasterOptions);
            return $cart->run();
        } else{
            throw new Exceptions_SeotoasterWidgetException('No cart plugin selected');
        }
        return false;
    }

	public static function getAllowedOptions() {
		$cartPluginName = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('cartPlugin');
		$reflection = new Zend_Reflection_Class(ucfirst($cartPluginName));
		$methods = $reflection->getMethods();

		$allowedOptions = array();
		foreach ($methods as $method) {
			if (strpos($method->getName(), Tools_Plugins_Abstract::OPTION_MAKER_PREFIX) !== false){
				$name = str_replace(Tools_Plugins_Abstract::OPTION_MAKER_PREFIX,'', $method->getName());
				array_push($allowedOptions, array(
					'alias'  => 'Store' .' '. $name,
					'option' => 'store:'.strtolower($name)
				));
			}
		}
		return $allowedOptions;
	}
}