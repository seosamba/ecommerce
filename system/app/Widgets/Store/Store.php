<?php
/**
 * Store Widget - small proxy for shopping plugins
 * It contains few own generators and allows you to put {$store:%widget_name%} instead of {$plugin:%cart_plugin%:%method_name%}
 * Where %cart_plugin% will be automatically fetched from current shopping settings.
 * This shorthand way allows you easy switch between cart plugins without any impact for site
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Widgets_Store_Store extends Widgets_Abstract {

	/**
	 * @todo see how it works in real life
	 * @var bool
	 */
	protected $_cacheable      = false;

	private static $_zendRegistryKey = 'store-cart-plugin';

    protected function _load() {
	    $methodName = Tools_Plugins_Abstract::OPTION_MAKER_PREFIX.ucfirst(strtolower($this->_options[0]));
//	    $shoppingPlugin = Tools_Factory_PluginFactory::createPlugin('shopping', $this->_options, $this->_toasterOptions);
	    $shopPlugRefl = new Zend_Reflection_Class('Shopping');
//	    if (method_exists($shoppingPlugin, $methodName)) {
	    if ($shopPlugRefl->hasMethod($methodName)) {
			$shoppingPlugin = Tools_Factory_PluginFactory::createPlugin('shopping', $this->_options, $this->_toasterOptions);
		    return $shoppingPlugin->run();
	    } elseif (method_exists($this, $methodName)) {

		    return $this->$methodName();
	    }

	    $regKey = self::$_zendRegistryKey.implode('_', $this->_options);
	    if (Zend_Registry::isRegistered($regKey)){
		    $cart = Zend_Registry::get($regKey);
	    } else {
		    $cartPluginName = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('cartPlugin');
            if ($cartPluginName){
                $cart = Tools_Factory_PluginFactory::createPlugin($cartPluginName, $this->_options, $this->_toasterOptions);
	            Zend_Registry::set($regKey, $cart);
            } else{
                throw new Exceptions_SeotoasterWidgetException('No cart plugin selected');
            }
	    }
	    return $cart->run();
    }

	protected function _init() {
		$this->_view = new Zend_View();
		$this->_view->setScriptPath(realpath(__DIR__.DIRECTORY_SEPARATOR.'views'));
	}


	public static function getAllowedOptions() {
		$cartPluginName = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('cartPlugin');
		$classes = array('Shopping', __CLASS__, ucfirst($cartPluginName));

		$methods = array();
		foreach ($classes as $className) {
			$reflection = new Zend_Reflection_Class($className);
			$methods = array_merge($methods, $reflection->getMethods());
			unset($reflection, $className);
		}
		$allowedOptions = array();
		foreach ($methods as $method) {
			if (strpos($method->getName(), Tools_Plugins_Abstract::OPTION_MAKER_PREFIX) !== false){
				$name = str_replace(Tools_Plugins_Abstract::OPTION_MAKER_PREFIX,'', $method->getName());
				try {
					$description = $method->getDocblock()->getShortDescription();
				} catch (Exception $e) {
					$description = null;
				}
				array_push($allowedOptions, array(
					'alias'  => 'Store' .' '. $name. (isset($description) ? ' - '.$description: ''),
					'option' => 'store:'.strtolower($name)
				));
			}
		}
		array_multisort($allowedOptions);
		return $allowedOptions;
	}

	/**
	 * Generates user profile
	 * @return string Html content
	 */
	protected function _makeOptionProfile(){
		if (Tools_ShoppingCart::getInstance()->getCustomer()->getId()) {
			return $this->_view->render('profile.phtml');
		}
	}
}