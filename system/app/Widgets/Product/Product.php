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
     */
	protected $_shoppingConfig;

    /**
	 * @var Models_Model_Product Product instance
	 */
	protected $_product = null;

    /**
     * @var null|string Type of widget
     */
    private $_type = null;


	protected function  _init() {
		parent::_init();
		if (empty($this->_options)){
			throw new Exceptions_SeotoasterWidgetException('No options provided');
		}
        $this->_websiteHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('website');
        $this->_configHelper  = Zend_Controller_Action_HelperBroker::getExistingHelper('config');

		$this->_view = new Zend_View(array(
			'scriptPath' => dirname(__FILE__) . '/views'
		));
        $this->_view->websiteUrl = $this->_websiteHelper->getUrl();

		$this->_productMapper = Models_Mapper_ProductMapper::getInstance();
		$this->_shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
		if (is_numeric($this->_options[0])){
			$this->_product = $this->_productMapper->find(intval($this->_options[0]));
			$this->_type = self::TYPE_PRODUCTLISTING;
			array_shift($this->_options);
		} else {
			$this->_product = $this->_productMapper->findByPageId($this->_toasterOptions['id']);
			$this->_type = $this->_options[0];
			array_shift($this->_options);
		}

    }

    protected function _load() {
        if ($this->_product === null || $this->_type === null) {
            if (Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_ADMINPANEL)) {
                 return "<b>Product doesn't exist or wrong options provided</b>";
            }
            return "<!--Product doesn't exist or wrong options provided-->";
        }

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

        $template = Application_Model_Mappers_TemplateMapper::getInstance()->findByName($this->_options[0]);
        if ($template !== null) {
            $themeConfig = Zend_Registry::get('theme');
            $parserOptions = array(
                'websiteUrl'   => $this->_websiteHelper->getUrl(),
                'websitePath'  => $this->_websiteHelper->getPath(),
                'currentTheme' => $this->_configHelper->getConfig('currentTheme'),
                'themePath'    => $themeConfig['path']
            );
            unset($themeConfig);

            $parser = new Tools_Content_Parser($template->getContent(), $this->_product->getPage()->toArray(), $parserOptions);
            return $parser->parse();
        }

        throw new Exceptions_SeotoasterWidgetException('Product template doesn\'t exist');
    }

    private function _renderEditproduct(){
        if (!Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_ADMINPANEL)) {
             return false;
        }
        $html = sprintf('<a href="javascript:;" data-url="%splugin/shopping/run/product#edit/%d" class="tpopup">%s</a>',
            $this->_websiteHelper->getUrl(),
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
        return $this->_websiteHelper->getUrl() .'media/' . $photoSrc;
	}
	
	private function _renderPrice() {

		$currency = new Zend_Currency(
			Zend_Locale::getLocaleToTerritory($this->_shoppingConfig['country']),
			$this->_shoppingConfig['currency']
		);
				
		return $currency->toCurrency($this->_product->getPrice());
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
        return $this->_product->getWeight() . ' ' .$this->_shoppingConfig['weightUnit'];
    }

    private function _renderMpn() {
        return $this->_product->getMpn();
    }

    private function _renderUrl() {
        $page = $this->_product->getPage();
        return $this->_websiteHelper->getUrl() . $page->getUrl();
    }

    private function _renderSku() {
        return $this->_product->getSku();
    }

    private function _renderCategories() {
        $pageMapper = Application_Model_Mappers_PageMapper::getInstance();
        $categories = $this->_product->getCategories();
        if (!empty($categories)){
            foreach ($categories as &$category) {
                if ($url = $pageMapper->findByUrl($category['name'].'.html')){
                    $category['url'] = $url;
                }
            }
        }
        $this->_view->categories = $categories;
        return $this->_view->render('categories.phtml');
    }

    private function _renderRelated() {
        $ids = $this->_product->getRelated();
        if (empty($ids)){
            return null;
        }
        $where = $this->_productMapper->getDbTable()->select()->where('id IN (?)', $ids);
        $related = $this->_productMapper->fetchAll($where);

        if ($related !== null) {
            $this->_view->related = $related;
            $this->_view->withImg = (isset($this->_options[0]) && $this->_options[0] == 'img') ? true : false;
            return $this->_view->render('related.phtml');
        }
        return false;
    }

    public static function getAllowedOptions() {
		$translator = Zend_Registry::get('Zend_Translate');
		return array(
			array(
				'alias'  => $translator->translate('Product name'),
				'option' => 'product:name'
			)
		);
	}

}