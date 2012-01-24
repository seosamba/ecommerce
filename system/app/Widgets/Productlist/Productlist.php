<?php

class Widgets_Productlist_Productlist extends Widgets_Abstract {

	/**
	 * Suboption for the categories
	 */
	const OPTTYPE_CATEGORIES    = 'categories';

	/**
	 *  Suboption for the brands
	 */
	const OPTTYPE_BRANDS        = 'brands';

	/**
	 * Suboption for the order
	 */
	const OPTTYPE_ORDER         = 'order';

	protected $_configHelper    = null;

	protected $_websiteHelper   = null;

	protected $_productMapper   = null;

	protected $_renderedContent = '';

	protected $_themeConfig     = null;

	protected $_templateContent = null;

	protected $_orderSequence   = array();

	protected $_parser          = null;

	protected $_entityParser   = null;

	protected $_mediaPath      = '';

	public function _init() {
		parent::_init();
		if (empty($this->_options)){
			throw new Exceptions_SeotoasterWidgetException('No options provided');
		}
		$this->_view = new Zend_View(array(
			'scriptPath' => dirname(__FILE__) . '/views'
		));
		$this->_websiteHelper    = Zend_Controller_Action_HelperBroker::getExistingHelper('website');
	    $this->_configHelper     = Zend_Controller_Action_HelperBroker::getExistingHelper('config');
	    $this->_view->websiteUrl = $this->_websiteHelper->getUrl();
		$this->_themeConfig      = Zend_Registry::get('theme');
		$this->_productMapper    = Models_Mapper_ProductMapper::getInstance();
		$this->_entityParser     = new Tools_Content_EntityParser();
		$this->_mediaPath        = $this->_websiteHelper->getUrl() . $this->_websiteHelper->getMedia();
	}

	public function _load() {
		$template = Application_Model_Mappers_TemplateMapper::getInstance()->findByName(array_shift($this->_options));
		if($template === null) {
			throw new Exceptions_SeotoasterWidgetException('Product template doesn\'t exist');
		}
		$products = $this->_loadProducts();
		if(!empty($products)) {
			$this->_templateContent = $template->getContent();
			//array_walk($products, array($this, '_parsingCallback'));

			$start = microtime(1);

			//array_walk($products, array($this, '_parsingCallback'));

			$productsCount = sizeof($products)-1;
			for($i = 0; $i <= $productsCount; $i++) {
				$product = $products[$i];
				$productPhotoData = explode('/', $product->getPhoto());
				$photoUrlPart     = $this->_mediaPath . $productPhotoData[0];
				$shortDesc        = $product->getShortDescription();
				$this->_entityParser->setDictionary(array(
					'$product:name'              => $product->getName(),
					'$product:photourl'          => $photoUrlPart . '/product/' . $productPhotoData[1],
					'$product:photourl:product'  => $photoUrlPart . '/product/' . $productPhotoData[1],
					'$product:photourl:small'    => $photoUrlPart . '/small/' . $productPhotoData[1],
					'$product:photourl:medium'   => $photoUrlPart . '/medium/' . $productPhotoData[1],
					'$product:photourl:large'    => $photoUrlPart . '/large/' . $productPhotoData[1],
					'$product:photourl:original' => $photoUrlPart . '/original/' . $productPhotoData[1],
					'$product:url'               => $product->getPage()->getUrl(),
					//'$product:price'             => $this->_renderProductWidgetOption(array($product->getId(), 'price'), $product->getPage()->toArray()),
					'$product:price'             => $product->getPrice(),
					'$product:brand'             => $product->getBrand(),
					'$product:weight'            => $product->getWeight(),
					'$product:mpn'               => $product->getMpn(),
					'$product:sku'               => $product->getSku(),
					'$product:id'                => $product->getId(),
					'$product:description:short' => $shortDesc,
					'$product:description'       => $shortDesc,
					'$product:description:full'  => $product->getFullDescription(),
					'$product:options'           => '',//$this->_renderProductWidgetOption(array($product->getId(), 'options'), $product->getPage()->toArray()),
					'$product:editproduct'       => ''//$this->_renderProductWidgetOption('editproduct', $product->getPage()->toArray())
				));
		        $this->_renderedContent .= $this->_entityParser->parse($this->_templateContent);
			}


			var_dump(round(microtime(1) - $start, 4));


			//var_dump(time() - $start);die();
			return $this->_renderedContent;
		}
		return '';
	}

	private function _loadProducts() {
		$cacheKey = 'productsList' . implode('.', $this->_options);
		if(($products = $this->_cache->load($cacheKey, Helpers_Action_Cache::PREFIX_WIDGET)) === null) {
			$products = $this->_prepareProducts();
		}
		return $products;
	}

	private function _parsingCallback($product) {
		$productPhotoData = explode('/', $product->getPhoto());
		$photoUrlPart     = $this->_mediaPath . $productPhotoData[0];
		$shortDesc        = $product->getShortDescription();
		$this->_entityParser->setDictionary(array(
			'$product:name'              => $product->getName(),
			'$product:photourl'          => $photoUrlPart . '/product/' . $productPhotoData[1],
			'$product:photourl:product'  => $photoUrlPart . '/product/' . $productPhotoData[1],
			'$product:photourl:small'    => $photoUrlPart . '/small/' . $productPhotoData[1],
			'$product:photourl:medium'   => $photoUrlPart . '/medium/' . $productPhotoData[1],
			'$product:photourl:large'    => $photoUrlPart . '/large/' . $productPhotoData[1],
			'$product:photourl:original' => $photoUrlPart . '/original/' . $productPhotoData[1],
			'$product:url'               => $product->getPage()->getUrl(),
			'$product:price'             => $this->_renderProductWidgetOption(array($product->getId(), 'price'), $product->getPage()->toArray()),
			'$product:brand'             => $product->getBrand(),
			'$product:weight'            => $product->getWeight(),
			'$product:mpn'               => $product->getMpn(),
			'$product:sku'               => $product->getSku(),
			'$product:id'                => $product->getId(),
			'$product:description:short' => $shortDesc,
			'$product:description'       => $shortDesc,
			'$product:description:full'  => $product->getFullDescription(),
			//'$product:options'           => $this->_renderProductWidgetOption('options', $product->getPage()->toArray()),
			//'$product:editproduct'       => $this->_renderProductWidgetOption('editproduct', $product->getPage()->toArray())
		));
        $this->_renderedContent .= $this->_entityParser->parse($this->_templateContent);
	}

	private function _renderProductWidgetOption($option, $data) {
        if (!is_array($option)){
            $option = (array) $option;
        }
        $widget  = Tools_Factory_WidgetFactory::createWidget('product', $option, $data);
		$content = $widget->render();
		unset($widget);
		return $content;
	}

	private function _renderPrice() {

	}

	private function _prepareProducts() {
		$products = array();
		if(empty($this->_options)) {
			return $this->_productMapper->fetchAll();
		}
		foreach($this->_options as $option) {
			if(false === ($optData = $this->_processOption($option))) {
				continue;
			}
			switch($optData['type']) {
				case self::OPTTYPE_CATEGORIES:
					$products = $this->_productMapper->findByCategories($optData['values']);
				break;
				case self::OPTTYPE_BRANDS:
					if(empty($products)) {
						$products = $this->_productMapper->findByBrands($optData['values']);
					}
					foreach($products as $key => $product) {
						if(!in_array($product->getBrand(), $optData['values'])) {
							unset($products[$key]);
						}
					}
				break;
				case self::OPTTYPE_ORDER:
					if(empty($products)) {
						$products = $this->_productMapper->fetchAll();
					}
					$this->_orderSequence = $optData['values'];
					$products             = $this->_sort($products);
				break;
			}
		}
		return $products;
	}

	/**
	 * Takes an option from the options array and find the specific constructions
	 *
	 * such as categories-id1,id2,idn; brands-name1,name2,namen, order-name,brand,price
	 * and makes an array array('type' => 'categories', 'values' => 'id1,id2,idn')
	 *
	 * @param $option string
	 * @return mixed
	 */
	private function _processOption($option) {
		$exploded = explode('-', $option);
		if(sizeof($exploded) != 2) {
			return false;
		}
		return array(
			'type'   => $exploded[0],
			'values' => explode(',', $exploded[1])
		);
	}

	private function _sort($products) {
		uasort($products, array($this, '_sortingCallback'));
		return $products;
	}

	private function _sortingCallback($productOne, $productTwo) {
		if($this->_orderSequence) {
			$compareResult = 0;
			foreach($this->_orderSequence as $orderTerm) {
				$getter = 'get' . ucfirst($orderTerm);
				if(!method_exists($productOne, $getter) || !method_exists($productTwo, $getter)) {
					continue;
				}
				$productOneTerm = $productOne->$getter();
				$productTwoTerm = $productTwo->$getter();
				if(is_integer($productOneTerm) && is_integer($productTwoTerm)) {
					$compareResult = ($productOneTerm - $productTwoTerm) ? ($productOneTerm - $productTwoTerm) / abs($productOneTerm - $productTwoTerm) : 0;
					if($compareResult !== 0) {
						return $compareResult;
					}
				}
				else {
					$compareResult = strcasecmp($productOneTerm, $productTwoTerm);
					if($compareResult !== 0) {
						return $compareResult;
					}
				}
			}
			return $compareResult;
		}
	}
}
