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
	    $shopPlugRefl = new Zend_Reflection_Class('Shopping');
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
		    $cartPluginName = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('cartPlugin1');
		    is_null($cartPluginName) && $cartPluginName = Shopping::DEFAULT_CART_PLUGIN;
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
		$this->_view->websiteUrl = Zend_Controller_Action_HelperBroker::getExistingHelper('website')->getUrl();
		$this->_view->setScriptPath(realpath(__DIR__.DIRECTORY_SEPARATOR.'views'));
//		$this->_view->addScriptPath(realpath(__DIR__.'/../../../views/'));
	}


	public static function getAllowedOptions() {
		$classes = array('Shopping', __CLASS__);
		$cartPluginName = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('cartPlugin');
		if ($cartPluginName){
			array_push($classes, ucfirst(strtolower($cartPluginName)));
		}

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

	/**
	 * Generates manage orders grid
	 * @return string Html content
	 */
	protected function _makeOptionOrders() {
		if (Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT)){
			$this->_view->noLayout = true;
			$this->_view->brands = Models_Mapper_Brand::getInstance()->fetchAll();
			$this->_view->tags = Models_Mapper_Tag::getInstance()->fetchAll();
			$this->_view->shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
            $shippingPlugins = Models_Mapper_ShippingConfigMapper::getInstance()->fetchAll();
            $shippingServices = array('');
            if(!empty($shippingPlugins)){
                foreach($shippingPlugins as $shippingPlugin){
                   $shippingServices[$shippingPlugin['name']] = $shippingPlugin['name'];
                }
                $shippingServices = array_merge(array(''=>'shipping carrier'), $shippingServices);
            }
            $this->_view->shippingServices = $shippingServices;
            $enabledInvoicePlugin = Application_Model_Mappers_PluginMapper::getInstance()->findByName('invoicetopdf');
            if($enabledInvoicePlugin != null){
                if($enabledInvoicePlugin->getStatus() == 'enabled'){
                    $this->_view->invoicePlugin = 1;
                }
            }
			return $this->_view->render('orders.phtml');
		}
	}

	/**
	 * Generates order summary for current user
	 * @return string Html content
	 */
	protected function _makeOptionPostPurchaseReport() {
		$sessionHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('session');
		if (isset($sessionHelper->storeCartSessionKey)){
			$cartId = intval($sessionHelper->storeCartSessionKey);
            $cartSession = Models_Mapper_CartSessionMapper::getInstance()->find($cartId);
            if(isset($this->_options[1]) && $this->_options[1] == 'mailreport'){
                $this->_view->mailReport = 1;
            }else{
                unset($sessionHelper->storeCartSessionKey);
            }
			if ($cartSession instanceof Models_Model_CartSession){
				$cartContent = $cartSession->getCartContent();
                $productMapper = Models_Mapper_ProductMapper::getInstance();
                $shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
				$this->_view->shoppingConfig = $shoppingConfig;
                foreach ($cartContent as $key=>$product){
                    $productObject = $productMapper->find($product['product_id']);
                    if(!empty($product['options'])){
                        $optionsData = $this->_getOptions($product['product_id'], $product['options']);
                        $cartContent[$key]['options'] = $optionsData;
                    }
                    if($productObject !=null){
                        $cartContent[$key]['photo']      = $productObject->getPhoto();
                        $cartContent[$key]['productUrl'] = $productObject->getPage()->getUrl();
                        $cartContent[$key]['taxRate']    = Tools_Tax_Tax::calculateProductTax($productObject, null, true);
                    }
                }
                $this->_view->showPriceIncTax = $shoppingConfig['showPriceIncTax'];
                $this->_view->weightSign = $shoppingConfig['weightUnit'];
                $this->_view->cartContent = $cartContent;
                $this->_view->cart = $cartSession;
				return $this->_view->render('post_purchase_report.phtml');
			}
			return;
		}
		$errmsg = 'store:postpurchasereport missing cart id';
		return Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_CONTENT) ? '<b>'.$errmsg.'</b>' : '<!-- '.$errmsg.' -->' ;
	}
    
    protected function _getOptions($productId, $options) {
		$actualOptions  = array();
		$product        = Models_Mapper_ProductMapper::getInstance()->find($productId);
		$defaultOptions = $product->getDefaultOptions();
		foreach($options as $optionId => $selectionId) {
			foreach($defaultOptions as $defaultOption) {
				if($optionId != $defaultOption['id']) {
					continue;
				}
				$actualOptions = array_filter($defaultOption['selection'], function($selection) use($selectionId) {
					if($selectionId == $selection['id']) {
						return $selection;
					}
				});
			}
		}
		return $actualOptions;
	}
    
    /**
	 * Generates login form for client
	 * @return string 
	 */
	protected function _makeOptionClientLogin() {
        $clientPage = Application_Model_Mappers_PageMapper::getInstance()->fetchByOption(Shopping::OPTION_STORE_CLIENT_LOGIN, true);
        if($clientPage != null){
            return '{$member:login:'.$clientPage->getUrl().'}'; 
        }else{
            return '{$member:login:index.html}';
        }
        
        
    }
}
