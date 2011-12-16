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
}