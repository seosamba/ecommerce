<?php

/**
 * Product list widget.
 *
 */
class Widgets_Productlist_Productlist extends Widgets_Abstract {

	/**
	 * Suboption for the tags
	 *
	 */
	const OPTTYPE_TAGS    = 'tags';

	/**
	 *  Suboption for the brands
	 *
	 */
	const OPTTYPE_BRANDS        = 'brands';

	/**
	 * Suboption for the order
	 *
	 */
	const OPTTYPE_ORDER         = 'order';

	/**
	 * Product list default offset (used for portional load)
	 */
	const DEFAULT_OFFSET        = 100;

	/**
	 * Seotoaster website action helper
	 *
	 * @var Helpers_Action_Website
	 */
	protected $_websiteHelper   = null;

	/**
	 * Product mapper
	 *
	 * @var Models_Mapper_ProductMapper
	 */
	protected $_productMapper   = null;

	/**
	 * Current product template content
	 *
	 * @var string
	 */
	protected $_productTemplate = null;

	/**
	 * Flag that shows the widget to return only product list html (without any wrappers, etc...)
	 *
	 * @var boolean
	 */
	protected $_cleanListOnly   = false;

	/**
	 * Set of products to proccess
	 *
	 * @var array
	 */
	private $_products = array();

	public function _init() {
		parent::_init();
		if (empty($this->_options)){
			throw new Exceptions_SeotoasterWidgetException('No options provided');
		}
		$this->_productTemplate  = Application_Model_Mappers_TemplateMapper::getInstance()->find(array_shift($this->_options));
		if($this->_productTemplate === null) {
			throw new Exceptions_SeotoasterWidgetException('Product template doesn\'t exist');
		}
	}

	public function _load() {
        $this->_view             = new Zend_View(array('scriptPath' => __DIR__ . '/views/'));
        $this->_view->addHelperPath('ZendX/JQuery/View/Helper/', 'ZendX_JQuery_View_Helper');
        $this->_view->limit      = self::DEFAULT_OFFSET;
        $this->_websiteHelper    = Zend_Controller_Action_HelperBroker::getExistingHelper('website');
        $this->_view->websiteUrl = $this->_websiteHelper->getUrl();
        $this->_productMapper    = Models_Mapper_ProductMapper::getInstance();

		//$cacheKey = Helpers_Action_Cache::PREFIX_WIDGET . '.proccessed.' . implode('.', $this->_options);
		//if(!($content = $this->_cache->load($cacheKey, Helpers_Action_Cache::PREFIX_WIDGET))) {
			$content = $this->_processList();
			//$this->_cache->save($cacheKey, $content, Helpers_Action_Cache::PREFIX_WIDGET, array('productListWidget'), Helpers_Action_Cache::CACHE_NORMAL);
		//}
		if($this->_cleanListOnly) {
			return $content;
		}
		$this->_view->plContent       = $content;
		$this->_view->productTemplate = $this->_productTemplate->getName();
		if(!isset($this->_options[0])) {
			$this->_view->offset = self::DEFAULT_OFFSET;
		} else if(!intval($this->_options[0])) {
			return $this->_view->render('productlist.phtml');
		} else {
			$this->_view->offset = $this->_options[0];
		}
		return $this->_view->render('productlist.phtml');
	}

	/**
	 * The main list proccessing function
	 *
	 * @return string
	 * @throws Exceptions_SeotoasterWidgetException
	 */
	protected function _processList() {
		// loading product listing template
		$template = $this->_productTemplate;
		$products = $this->_products;
		if((isset($this->_options[0])) && $this->_options[0] == 'sametags') {
			$products = $this->_listSameTags();
		}
		if(empty($products)) {
			$products = $this->_loadProducts();
		}
		$wesiteData  = Zend_Registry::get('website');
		$confiHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('config');
		// init variables we will use in closure
		$renderedContent = '';
		$entityParser    = new Tools_Content_EntityParser();
		$currency        = Zend_Registry::isRegistered('Zend_Currency') ? Zend_Registry::get('Zend_Currency') : new Zend_Currency();
		$data            = array(
			'mediaPath'           => $this->_websiteHelper->getUrl() . $this->_websiteHelper->getMedia(),
			'templateContent'     => $template->getContent(),
			'websiteUrl'          => $wesiteData['url'],
			'domain'              => str_replace('www.', '', $wesiteData['url']),
			'mediaServersAllowed' => $confiHelper->getConfig('mediaServers')
		);

        if(empty($products)) {
            return '<!-- you do not have products -->';
        }

		$cacheTags = array();
		// here we go - proccessing the list
		array_walk($products, function($product) use(&$renderedContent, $entityParser, $currency, $data, &$cacheTags) {
			array_push($cacheTags, 'prodid_'.$product->getId());
			if (strpos($data['templateContent'], '$store:addtocart') !== false ){
				$storeWidget = Tools_Factory_WidgetFactory::createWidget('store', array('addtocart', $product->getId()));
			}
			//media servers (we are not using Tools_Content_Tools::applyMediaServers here because of the speed)
			if($data['mediaServersAllowed']) {
				$mediaServer = Tools_Content_Tools::getMediaServer();
				if($mediaServer) {
					$data['mediaPath'] = str_replace($data['websiteUrl'], $mediaServer . '.' . $data['domain'], $data['mediaPath']);
				}
			}
			// proccessing product photo and get some data
			$productPhotoData = explode('/', $product->getPhoto());
			$photoUrlPart     = $data['mediaPath'] . $productPhotoData[0];
			$shortDesc        = $product->getShortDescription();
			$templatePrepend  = '<!--pid="' . $product->getId() . '"-->';
			//setting up the entity parser
			$renderedContent .= $entityParser->setDictionary(array(
				'$product:name'              => $product->getName(),
                '$product:photourl'          => $photoUrlPart . '/product/' . $productPhotoData[1],
                '$product:photourl:product'  => $photoUrlPart . '/product/' . $productPhotoData[1],
                '$product:photourl:small'    => $photoUrlPart . '/small/' . $productPhotoData[1],
                '$product:photourl:medium'   => $photoUrlPart . '/medium/' . $productPhotoData[1],
                '$product:photourl:large'    => $photoUrlPart . '/large/' . $productPhotoData[1],
                '$product:photourl:original' => $photoUrlPart . '/original/' . $productPhotoData[1],
                '$product:url'               => $product->getPage() ? $product->getPage()->getUrl() : null,
                '$product:price'             => $currency->toCurrency((float)($product->getCurrentPrice() !== null?$product->getCurrentPrice():$product->getPrice())),
                '$product:brand'             => $product->getBrand(),
                '$product:weight'            => $product->getWeight(),
                '$product:mpn'               => $product->getMpn(),
                '$product:sku'               => $product->getSku(),
                '$product:id'                => $product->getId(),
                '$product:description:short' => $shortDesc,
                '$product:description'       => $shortDesc,
                '$product:description:full'  => $product->getFullDescription(),
				'$store:addtocart'           => isset($storeWidget) ? $storeWidget->render() : ''
			))->parse($templatePrepend . $data['templateContent']);
			unset($storeWidget);
		});
		$this->_cacheTags = array_merge($this->_cacheTags, $cacheTags);
		return $renderedContent;
	}

	protected function _listSameTags() {
		//get the product
		$sameTagsProducts = array();
		$product   = $this->_productMapper->findByPageId($this->_toasterOptions['id']);
		if(!$product instanceof Models_Model_Product) {
			throw new Exceptions_SeotoasterWidgetException('Use this widget only on product page');
		}
		$excludeId = $product->getId();
		$tags      = $product->getTags();
		unset($product);
		if(is_array($tags) && !empty($tags)) {
			$sameTagsProducts = $this->_productMapper->findByTags(array_map(function($item) {
				return $item['id'];
			}, $tags), false);
		}
		return array_filter($sameTagsProducts, function($product) use($excludeId) {
			return ($product->getId() != $excludeId);
		});
	}

	/**
	 * Render specific options using product widget
	 *
	 * @param $option
	 * @param $data
	 * @return mixed
	 */
	private function _renderProductWidgetOption($option, $data) {
        if (!is_array($option)){
            $option = (array) $option;
        }
        $widget  = Tools_Factory_WidgetFactory::createWidget('product', $option, $data);
		$content = $widget->render();
		unset($widget);
		return $content;
	}

	/**
	 * Load the wright products set
	 *
	 * @return array
	 */
	private function _loadProducts() {
		$products = array();
		if(empty($this->_options)) {
			return $this->_productMapper->fetchAll(null, array(), 0, self::DEFAULT_OFFSET);
		}
		foreach($this->_options as $option) {
			if(false === ($optData = $this->_processOption($option))) {
				continue;
			}
			switch($optData['type']) {
				case self::OPTTYPE_TAGS:
					$products = $this->_productMapper->findByTags($optData['values']);
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
					if(!empty($products)) {
						//$products = $this->_productMapper->fetchAll(null, array(), 0, 100);
						$products = $this->_sort($products, $optData['values']);
					}

				break;
			}
		}
		return $products;
	}

	/**
	 * Takes an option from the options array and find the specific constructions
	 *
	 * such as tags-id1,id2,idn; brands-name1,name2,namen, order-name,brand,price
	 * and makes an array: array('type' => 'tags', 'values' => 'id1,id2,idn')
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

	/**
	 * Sort the product list (name, brand, price)
	 *
	 * @param array $products The set of products
	 * @param array $sortingOrder can be array('name', 'brand', 'price')
	 * @return array Sorted set of products
	 */
	private function _sort($products, $sortingOrder) {
		uasort($products, function($productOne, $productTwo) use($sortingOrder) {
			$compareResult = 0;
			foreach($sortingOrder as $orderTerm) {
				$getter = 'get' . ucfirst($orderTerm);
				if(!method_exists($productOne, $getter) || !method_exists($productTwo, $getter)) {
					continue;
				}
				$productOneTerm = $productOne->$getter();
				$productTwoTerm = $productTwo->$getter();
				if(is_numeric($productOneTerm) && is_numeric($productTwoTerm)) {
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
		});
		return $products;
	}

	public function setProducts($products) {
		$this->_products = $products;
		return $this;
	}

	public function setCleanListOnly($cleanListOnly) {
		$this->_cleanListOnly = $cleanListOnly;
		return $this;
	}


}
