<?php

/**
 * Product
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Widgets_Product_Product extends Widgets_Abstract {

    const TYPE_PRODUCTLISTING = 'productlisting';

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
            return $parser->parse();
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
		$photoSrc = $this->_product->getPhoto();
		if (!empty($this->_options) && in_array($this->_options[0], array('small', 'medium', 'large', 'original'))) {
            $photoSrc = str_replace('/', '/'.$this->_options[0].'/', $photoSrc);
        } else {
            $photoSrc = str_replace('/', '/product/', $photoSrc);
        }
        return $this->_websiteUrl .'media/' . $photoSrc;
	}
	
	private function _renderPrice() {
		$noCurrency = (strtolower(end($this->_options)) === 'nocurrency');
		if ($noCurrency === true){
			array_pop($this->_options);
		}

		if (empty($this->_options)){
			$price = $this->_product->getCurrentPrice() !== null ? $this->_product->getCurrentPrice() : $this->_product->getPrice();
		} else {
            $pluginName = strtolower($this->_options[0]);
			if ($pluginName === 'original'){
				$price = !$noCurrency ? $this->_currency->toCurrency($this->_product->getPrice()) : $this->_product->getPrice() ;
			} else {
	            $plugin = Tools_Plugins_Tools::findPluginByName($pluginName);
	            if ($plugin){ //$plugin->getStatus() === Application_Model_Models_Plugin::ENABLED){
	                return Tools_Factory_PluginFactory::createPlugin($plugin->getName(), array('price', $this->_product->getId()), $this->_toasterOptions)->run();
	            }
				return false;
			}
        }

		if ((bool)self::$_shoppingConfig['showPriceIncTax']){
			$price += Tools_Tax_Tax::calculateProductTax($this->_product);
		}

		return !$noCurrency ? $this->_currency->toCurrency($price): $price;
	}
	
	private function _renderBrand() {
		return $this->_product->getBrand();
	}
	
	private function _renderOptions() {
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
        }
//        $where = $this->_productMapper->getDbTable()->getAdapter()->quoteInto('id IN (?)', $ids);
        $related = $this->_productMapper->find($ids);

        if ($related !== null) {
            $this->_view->related = $related;
            $this->_view->withImg = (isset($this->_options[0]) && $this->_options[0] == 'img') ? true : false;
            return $this->_view->render('related.phtml');
        }
        return false;
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
