<?php

/**
 * Product
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Widgets_Product_Product extends Widgets_Abstract {

    const TYPE_PRODUCTLISTING = 'productlisting';

	const PRICE_MODE_NOCURRENCY = 'nocurrency';

	const PRICE_MODE_LIFERELOAD = 'realtimeupdate';

	const PRICE_MODE_CURRENCY   = 'currency';

	/**
     * @var Models_Mapper_ProductMapper Product Mapper
     */
    protected $_productMapper;

    /**
     * @var array Contains payment config
     * @static
     */
	protected static $_shoppingConfig = null;

    /**
	 * @var Models_Model_Product Product instance
	 */
	protected $_product = null;

    /**
     * @var null|string Type of widget
     */
    private $_type = null;

    /**
     * @var null|Zend_Currency Zend_Currency holder
     */
    private $_currency = null;

	protected function _load(){
		if (empty($this->_options)){
			throw new Exceptions_SeotoasterWidgetException('No options provided');
		}

		$this->_websiteUrl = Zend_Controller_Action_HelperBroker::getExistingHelper('website')->getUrl();

		self::$_shoppingConfig || self::$_shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();

		$this->_view = new Zend_View(array(
			'scriptPath' => dirname(__FILE__) . '/views'
		));
		$this->_view->setHelperPath(APPLICATION_PATH . '/views/helpers/');
		$this->_view->addHelperPath('ZendX/JQuery/View/Helper/', 'ZendX_JQuery_View_Helper');
        $this->_view->websiteUrl = $this->_websiteUrl;

		$this->_productMapper = Models_Mapper_ProductMapper::getInstance();

		if (is_numeric($this->_options[0])){
			$this->_product = $this->_productMapper->find(intval($this->_options[0]));
			$this->_type = self::TYPE_PRODUCTLISTING;
			array_shift($this->_options);
		} else {
			$productCacheId = __CLASS__.'_byPage_'.$this->_toasterOptions['id'];
			if (null === ($this->_product = $this->_cache->load($productCacheId, 'store_'))){
				$this->_product = $this->_productMapper->findByPageId($this->_toasterOptions['id']);
				if (null !== $this->_product){
					$this->_cache->save($productCacheId, $this->_product, 'store_', array('productwidget', 'prodid_'.$this->_product->getId()), Helpers_Action_Cache::CACHE_FLASH);
				}
			}
			$this->_type = array_shift($this->_options);
		}

		//initializing Zend Currency for future use
        if ($this->_currency === null){
            $this->_currency = Zend_Registry::isRegistered('Zend_Currency') ? Zend_Registry::get('Zend_Currency') : new Zend_Currency();
        }

	    if ($this->_product === null || $this->_type === null) {
            if (Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_ADMINPANEL)) {
                 return "<b>Product doesn&apos;t exist or wrong options provided</b>";
            }
            return "<!--Product doesn&apos;t exist or wrong options provided-->";
        }
		array_push($this->_cacheTags, 'prodid_'.$this->_product->getId());
        $this->_view->product = $this->_product;

        $methodName = '_render'.ucfirst(strtolower($this->_type));
		if (method_exists($this, $methodName)){
			return $this->$methodName();
		}
		return '<b>Method '. $this->_type .' doesn\'t exist</b>';
	}

    private function _renderProductlisting(){
        if (!isset($this->_options[0]) || empty($this->_options[0])){
            throw new Exceptions_SeotoasterWidgetException('No template specified');
        }

        $template = Application_Model_Mappers_TemplateMapper::getInstance()->find($this->_options[0]);
        if ($template !== null) {

	        $templatePrepend = '<!--pid="' . $this->_product->getId() . '"-->';

	        $themeConfig = Zend_Registry::get('theme');
            $parserOptions = array(
                'websiteUrl'   => $this->_websiteUrl,
                'websitePath'  => Zend_Controller_Action_HelperBroker::getExistingHelper('website')->getPath(),
                'currentTheme' => Zend_Controller_Action_HelperBroker::getExistingHelper('config')->getConfig('currentTheme'),
                'themePath'    => $themeConfig['path']
            );
            unset($themeConfig);

            $parser = new Tools_Content_Parser($templatePrepend . $template->getContent(), $this->_product->getPage()->toArray(), $parserOptions);

	        if ((bool)$this->_product->getEnabled()){
		        return $parser->parse();
	        } elseif (Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_CONTENT)) {
		        return '<div style="border: 1px dashed #cd5c5c; overflow: hidden;"><span>'.
				        $this->_translator->translate('This product is disabled').
				        '</span>'.$parser->parse().'</div>';
	        }else{
                return '';
            }
        }

        throw new Exceptions_SeotoasterWidgetException('Product template doesn\'t exist');
    }

    private function _renderEditproduct(){
        if (!Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_ADMINPANEL)) {
             return false;
        }
        $html = sprintf('<a href="javascript:;" data-url="%splugin/shopping/run/product/id/%d" class="tpopup">%s</a>',
            $this->_websiteUrl,
            $this->_product->getId(),
            $this->_translator->translate('Edit product')
        );
        return  $html;
    }

    private function _renderId(){
        return $this->_product->getId();
    }

    private function _renderName() {
		return $this->_product->getName();
	}
	
	private function _renderPhotourl() {
		$websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
        $photoSrc      = $this->_product->getPhoto();
		if (!empty($this->_options) && in_array($this->_options[0], array('small', 'medium', 'large', 'original'))) {
            $photoSrc = str_replace('/', '/'.$this->_options[0].'/', $photoSrc);
        } else {
            $photoSrc = str_replace('/', '/product/', $photoSrc);
        }
        return $this->_websiteUrl . $websiteHelper->getMedia() . $photoSrc;
	}
	
	private function _renderPrice() {
		$noCurrency = array_search(self::PRICE_MODE_NOCURRENCY, $this->_options);
		$lifeReload = array_search(self::PRICE_MODE_LIFERELOAD, $this->_options);
        $currency   = array_search(self::PRICE_MODE_CURRENCY, $this->_options);

		if ($noCurrency !== false){
			unset($this->_options[$noCurrency]);
			$noCurrency = true;
		}
        if ($lifeReload !== false){
	        unset($this->_options[$lifeReload]);
	        $lifeReload = true;
	        $lifeReloadClass = array();
		}
        if ($currency !== false){
	        $currencyCode =  $currency + 1;
			if(!isset($this->_options[$currency]) || empty($this->_options[$currency])){
                return false;
            }
            $newCurrency = strtoupper($this->_options[$currencyCode]);
	        unset($this->_options[$currency], $this->_options[$currencyCode], $currencyCode);
            $currency = true;
        }
        
		if (!empty($this->_options)){
            $pluginName = strtolower($this->_options[0]);
			if ($pluginName === 'original'){
				if (is_null($this->_product->getCurrentPrice())){
					return null;
				} else {
					if ($lifeReload){
						array_push($lifeReloadClass, 'original-price');
					}
					$this->_product->setCurrentPrice(null);
				}
			} else {
				$plugin = Tools_Plugins_Tools::findPluginByName($pluginName);
			    if ($plugin->getStatus() === Application_Model_Models_Plugin::ENABLED){
				    $price = Tools_Factory_PluginFactory::createPlugin($pluginName, array('price', $this->_product->getId()), $this->_toasterOptions)->run();
				    if (is_numeric($price)){
					    $price = floatval($price);
					    $this->_product->setCurrentPrice($price);
				    }
				    unset($price);
			    } else {
				    if (Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_CONTENT)){
					    throw new Exceptions_SeotoasterWidgetException('Plugin '.$pluginName. ' does not exists');
				    }
				    return false;
			    }
			}
        }

		$itemDefaultOptionsArray = array();
        foreach($this->_product->getDefaultOptions() as $option){
            foreach ($option['selection'] as $item) {
                if($item['isDefault'] == 1){
                    $itemDefaultOptionsArray[$option['id']] = $item['id'];
                }
            }
        }
      
        $price = Tools_ShoppingCart::getInstance()->calculateProductPrice($this->_product, $itemDefaultOptionsArray);
        if($currency === true){
            if (null === ($changedPrice = $this->_cache->load('product_prodid_'.$this->_product->getId().'_currency_'.$newCurrency.'_price_'.$price, 'store_'))){
                $cacheCurrencyTime = strtotime('tomorrow') - strtotime('now');
                $changedPrice = Tools_Misc::getConvertedPriceByCurrency($price, $newCurrency);
                $this->_cache->save('product_prodid_'.$this->_product->getId().'_currency_'.$newCurrency.'_price_'.$price, $changedPrice, 'store_', array(), $cacheCurrencyTime);
            }
            $price = $changedPrice;
	        $noCurrency = true;     // disabling wrapping converted values
	        $lifeReload = false;    // life reload is not allowed
        }

		$price = !$noCurrency ? $this->_currency->toCurrency($price): $price;

        if($lifeReload){
	        $lifeReloadClass = implode(' ', $lifeReloadClass);
            return '<span class="price-lifereload-'.$this->_product->getId().' '.$lifeReloadClass.'">'.$price.'</span>';
        }

		return $price;
	}
	
	private function _renderBrand() {
		return $this->_product->getBrand();
	}
	
	private function _renderOptions() {
		$this->_view->taxRate = Tools_Tax_Tax::calculateProductTax($this->_product, null, true);
		return $this->_view->render('options.phtml');
	}
	
	private function _renderDescription() {
		switch (isset($this->_options[0])?$this->_options[0]:'small') {
			case 'full':
				$description = $this->_product->getFullDescription();
				break;
			case 'short':
			default:
				$description = $this->_product->getShortDescription();
				break;
		}
		
		return nl2br($description);
	}

    private function _renderWeight() {
        return $this->_product->getWeight() . ' ' .self::$_shoppingConfig['weightUnit'];
    }

    private function _renderMpn() {
        return $this->_product->getMpn();
    }

    private function _renderUrl() {
        $page = $this->_product->getPage();
        return $this->_websiteUrl . $page->getUrl();
    }

    private function _renderSku() {
        return $this->_product->getSku();
    }

    private function _renderTags() {
        $pageMapper = Application_Model_Mappers_PageMapper::getInstance();
	    $tags = $this->_product->getTags();
	    if (!empty($tags)){
	        $pageHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('page');
	        $pagesList = $pageMapper->getDbTable()->getAdapter()->fetchCol($pageMapper->getDbTable()->select()->from($pageMapper->getDbTable()->info('name'), 'url')->where("system = '0'"));
            foreach ($tags as &$tag) {
	            $url = $pageHelper->filterUrl($tag['name']);
                if (in_array($url, $pagesList)){
                    $tag['url'] = $url;
                }
            }
		    if (isset($this->_options[0]) && strtolower($this->_options[0]) === 'json' ){
			    return json_encode($tags);
		    } else {
	            $this->_view->tags = $tags;
	            return $this->_view->render('tags.phtml');
	        }
	    }
    }

    private function _renderRelated() {
        $ids = $this->_product->getRelated();
	    if (empty($ids)){
            return null;
        } else {
		    foreach($ids as $id) {
			    array_push($this->_cacheTags, 'prodid_'.$id);
		    }
	    }
        $related = $this->_productMapper->find($ids);
        $checkoutPage = Tools_Misc::getCheckoutPage();
        $checkoutPageUrl = $checkoutPage != null?$checkoutPage->getUrl():'';
        $imageSize = 'small';
        if ($related !== null) {
            $this->_view->related = $related instanceof Models_Model_Product ? array($related) : $related ;
            $this->_view->imageSize = (isset($this->_options[0])) ? $this->_options[0] : $imageSize;
            if(isset($this->_options[1]) && $this->_options[1] == 'addtocart'){
               $this->_view->checkoutPageUrl = $checkoutPageUrl;
            }
            return $this->_view->render('related.phtml');
        }
        return false;
    }

	private function  _renderInventory() {
		$inventoryCount = $this->_product->getInventory();
		if (is_null($inventoryCount)){
			return $this->_translator->translate('In stock');
		}
		return $inventoryCount > 0 ? $inventoryCount : $this->_translator->translate('Out of stock');
	}

    public static function getAllowedOptions() {
		$translator = Zend_Registry::get('Zend_Translate');
	    $allowedOptions = array();

	    $methods = get_class_methods(__CLASS__);
	    $generators = preg_grep('/^_render(?!Productlisting)/', $methods);
	    foreach ($generators as $method) {
		    $type = strtolower(str_replace('_render', '', $method));
		    array_push($allowedOptions, array(
			    'alias'  => $translator->translate('Product '.$type),
			    'option' => 'product:'.$type
		    ));
	    }

	    return $allowedOptions;
	}

}
