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
	const OPTTYPE_TAGS = 'tags';

	/**
	 *  Suboption for the brands
	 *
	 */
	const OPTTYPE_BRANDS = 'brands';

	/**
	 * Suboption for the order
	 *
	 */
	const OPTTYPE_ORDER = 'order';

	/**
	 * Product list default offset (used for portional load)
	 */
	const DEFAULT_LIMIT = 50;

    /**
     *  Product limit
     */
    protected  $_limit = null;

	/**
	 * Seotoaster website action helper
	 *
	 * @var Helpers_Action_Website
	 */
	protected $_websiteHelper = null;

	/**
	 * Product mapper
	 *
	 * @var Models_Mapper_ProductMapper
	 */
	protected $_productMapper = null;

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
	protected $_cleanListOnly = false;

	/**
	 * Set of products to proccess
	 *
	 * @var array
	 */
	private $_products        = array();

    /**
     * Show which logic should be used when selecting products by tags AND or OR (default)
     */
    private $_strictTagsCount = false;

	public function _init() {
		parent::_init();
		if (empty($this->_options)) {
			throw new Exceptions_SeotoasterWidgetException('No options provided');
		}
		$this->_productTemplate = Application_Model_Mappers_TemplateMapper::getInstance()->find(array_shift($this->_options));
		if ($this->_productTemplate === null) {
			throw new Exceptions_SeotoasterWidgetException('Product template doesn\'t exist');
		}
		$layout = Zend_Layout::getMvcInstance();
		$layout->getView()->headScript()->appendFile(Zend_Controller_Action_HelperBroker::getExistingHelper('website')->getUrl()
				. 'plugins/shopping/web/js/product-options.js');
	}

	public function _load() {
		$this->_view = new Zend_View(array('scriptPath' => __DIR__ . '/views/'));
		$this->_view->addHelperPath('ZendX/JQuery/View/Helper/', 'ZendX_JQuery_View_Helper');
        $last = end($this->_options);
        if (is_numeric($last)) {
            $last = abs(intval($last));
        }
        if ($last !== 0 && count($this->_options) > 1) {
            $this->_limit = $last;
            $this->_view->limit = $this->_limit;
        } else {
		    $this->_view->limit = self::DEFAULT_LIMIT;
            $this->_limit = self::DEFAULT_LIMIT;
        }
		$this->_websiteHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('website');
		$this->_view->websiteUrl = $this->_websiteHelper->getUrl();
		$this->_productMapper = Models_Mapper_ProductMapper::getInstance();
        $this->_strictTagsCount = (strtolower(end($this->_options)) == 'and');


		//$cacheKey = Helpers_Action_Cache::PREFIX_WIDGET . '.proccessed.' . implode('.', $this->_options);
		//if(!($content = $this->_cache->load($cacheKey, Helpers_Action_Cache::PREFIX_WIDGET))) {
		$content = $this->_processList();
		//$this->_cache->save($cacheKey, $content, Helpers_Action_Cache::PREFIX_WIDGET, array('productListWidget'), Helpers_Action_Cache::CACHE_NORMAL);
		//}
		if ($this->_cleanListOnly) {
			return $content;
		}
		$this->_view->pageId = $this->_toasterOptions['id'];
		$this->_view->plContent = $content;
		$this->_view->productTemplate = $this->_productTemplate->getName();
		array_push($this->_cacheTags, preg_replace('/[^\w\d_]/', '', $this->_view->productTemplate));
		if (!isset($this->_options[0])) {
			$this->_view->offset = self::DEFAULT_LIMIT;
		} else if (!intval($this->_options[0])) {
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
		if ((isset($this->_options[0])) && $this->_options[0] == 'sametags') {
			$products = $this->_listSameTags();
		} elseif (empty($products)) {
			$products = $this->_loadProducts();
		}
		$this->_view->totalCount = sizeof($products);
		$wesiteData = Zend_Registry::get('website');
		$confiHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('config');
		// init variables we will use in closure
		$renderedContent = '';
		$entityParser = new Tools_Content_EntityParser();
		$currency = Zend_Registry::isRegistered('Zend_Currency') ? Zend_Registry::get('Zend_Currency') : new Zend_Currency();
		$data = array(
			'mediaPath'           => $this->_websiteHelper->getUrl() . $this->_websiteHelper->getMedia(),
			'templateContent'     => $template->getContent(),
			'websiteUrl'          => $wesiteData['url'],
			'domain'              => str_replace('www.', '', $wesiteData['url']),
			'mediaServersAllowed' => $confiHelper->getConfig('mediaServers'),
            'noZeroPrice'         => Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('noZeroPrice')
		);

		if (empty($products)) {
			return '';
		}

		$cacheTags = array();
		// here we go - proccessing the list
		array_walk($products, function ($product) use (&$renderedContent, $entityParser, $currency, $data, &$cacheTags) {
			array_push($cacheTags, 'prodid_' . $product->getId());
			if (strpos($data['templateContent'], '$store:addtocart') !== false) {
				$storeWidgetAddToCart = Tools_Factory_WidgetFactory::createWidget('store', array('addtocart', $product->getId()));
			}
			if (strpos($data['templateContent'], '$store:addtocart:checkbox') !== false) {
				$storeWidgetAddToCartCheckbox = Tools_Factory_WidgetFactory::createWidget('store', array('addtocart', $product->getId(), 'checkbox'));
			}
			//media servers (we are not using Tools_Content_Tools::applyMediaServers here because of the speed)
			if ($data['mediaServersAllowed']) {
				$mediaServer = Tools_Content_Tools::getMediaServer();
				if ($mediaServer) {
					$data['mediaPath'] = str_replace($data['websiteUrl'], $mediaServer . '.' . $data['domain'], $data['mediaPath']);
				}
			}
			// proccessing product photo and get some data
//			$productPhotoData = explode('/', $product->getPhoto());
//			$photoUrlPart = $data['mediaPath'] . $productPhotoData[0];
			$shortDesc = $product->getShortDescription();
			$templatePrepend = '<!--pid="' . $product->getId() . '"-->';

			if (strpos($data['templateContent'], '$product:options') !== false) {
				$view = new Zend_View(array('scriptPath' => dirname(__DIR__) . '/Product/views/'));
				$view->taxRate = Tools_Tax_Tax::calculateProductTax($product, null, true);
				$view->product = $product;
				$productOptionsView = $view->render('options.phtml');
			}
			//preparing default price with applied default options
			$itemDefaultOptionsArray = array();
            $productDefaultOptions   = $product->getDefaultOptions();
            if(is_array($productDefaultOptions) && !empty($productDefaultOptions)) {
                foreach ($productDefaultOptions as $option) {
                    if(!isset($option['selection'])) {
                        continue;
                    }
                    foreach ($option['selection'] as $item) {
                        if ($item['isDefault'] == 1) {
                            $itemDefaultOptionsArray[$option['id']] = $item['id'];
                        }
                    }
                }
            }

			//setting up the entity parser
			$entityParser->addToDictionary(array(
				'$product:name'                         => $product->getName(),
//				'$product:photourl'                     => $photoUrlPart . '/product/' . $productPhotoData[1],
//				'$product:photourl:product'             => $photoUrlPart . '/product/' . $productPhotoData[1],
//				'$product:photourl:small'               => $photoUrlPart . '/small/' . $productPhotoData[1],
//				'$product:photourl:medium'              => $photoUrlPart . '/medium/' . $productPhotoData[1],
//				'$product:photourl:large'               => $photoUrlPart . '/large/' . $productPhotoData[1],
//				'$product:photourl:original'            => $photoUrlPart . '/original/' . $productPhotoData[1],
				'$product:url'                          => $product->getPage() ? $product->getPage()->getUrl() : null,
				'$product:brand'                        => $product->getBrand(),
				'$product:weight'                       => $product->getWeight(),
				'$product:mpn'                          => $product->getMpn(),
				'$product:sku'                          => $product->getSku(),
				'$product:id'                           => $product->getId(),
				'$product:description:short'            => nl2br($shortDesc),
				'$product:description'                  => nl2br($shortDesc),
				'$product:description:full'             => nl2br($product->getFullDescription()),
				'$store:addtocart'                      => isset($storeWidgetAddToCart) ? $storeWidgetAddToCart->render() : '',
				'$store:addtocart:' . $product->getId() => isset($storeWidgetAddToCart) ? $storeWidgetAddToCart->render() : '',
				'$store:addtocart:checkbox'             => isset($storeWidgetAddToCartCheckbox) ? $storeWidgetAddToCartCheckbox->render() : '',
				'$product:options'                      => isset($productOptionsView) ? $productOptionsView : ''
			));

			// fetching $product:price and $product:freeshipping widgets and rendering them via native widget
			if (preg_match_all('~{\$product:((?:price|freeshipping|photourl):?[^}]*)}~', $data['templateContent'], $productPriceWidgets)) {
				$replacements = array();
				foreach ($productPriceWidgets[1] as $key => $widgetData) {
                    $page = $product->getPage();
                    if(!$page instanceof Application_Model_Models_Page) {
                        continue;
                    }
					$args = array_filter(explode(':', $widgetData));
					$widget = Tools_Factory_WidgetFactory::createWidget('product', $args, array('id' => $page->getId()));
					$key = trim($productPriceWidgets[0][$key], '{}');
                    $replacements[$key] = $widget->render();

                    // if noZeroPrice in config set to 1 - do not show zero prices and "Add to cart" becomes "Go to product"
                    if($widgetData === 'price' && $data['noZeroPrice'] && floatval($product->getPrice()) === 0) {
                        $replacements[$key]                                    = '';
                        $replacements['$store:addtocart']                      = '<a class="tcart-add" href="' . ($product->getPage() ? $product->getPage()->getUrl() : 'javascript:;') . '">Go to product</a>';
                        $replacements['$store:addtocart:' . $product->getId()] = $replacements['$store:addtocart'];
                        $replacements['$store:addtocart:checkbox']             = $replacements['$store:addtocart'];
                    }
				}
				if (!empty($replacements)) {
					$entityParser->addToDictionary($replacements);
					unset($replacements, $productPriceWidgets);
				}
			}

			$renderedContent .= $entityParser->parse($templatePrepend . $data['templateContent']);
			unset($storeWidget);
		});
		$this->_cacheTags = array_merge($this->_cacheTags, $cacheTags);
		return $renderedContent;
	}


	protected function _listSameTags() {
		//get the product
		$product = $this->_productMapper->findByPageId($this->_toasterOptions['id']);
		if (!$product instanceof Models_Model_Product) {
			throw new Exceptions_SeotoasterWidgetException('Use this widget only on product page');
		}
		if (!$product->getTags()) {
			return null;
		}
		$ids = array_map(function ($tag) {
			return $tag['id'];
		}, $product->getTags());

		$where = $this->_productMapper->getDbTable()->getAdapter()->quoteInto('p.id != ?', $product->getId());
		unset($product);
		return $this->_productMapper->fetchAll($where, null, null, null, null, $ids);
	}

	/**
	 * Render specific options using product widget
	 *
	 * @param $option
	 * @param $data
	 * @return mixed
	 */
	private function _renderProductWidgetOption($option, $data) {
		if (!is_array($option)) {
			$option = (array)$option;
		}
		$widget = Tools_Factory_WidgetFactory::createWidget('product', $option, $data);
		$content = $widget->render();
		unset($widget);
		return $content;
	}

	/**
	 * Load the right products set
	 *
	 * @return array
	 */
	private function _loadProducts($enabled = true) {
		$enabledOnly = $this->_productMapper->getDbTable()->getAdapter()->quoteInto('enabled=?', $enabled);
		if (empty($this->_options)) {
			array_push($this->_cacheTags, 'prodid_all');
			return $this->_productMapper->fetchAll($enabledOnly, null, 0, $this->_limit);
		}
		$filters = array(
			'tags'   => null,
			'brands' => null,
			'order'  => null
		);
		foreach ($this->_options as $option) {
			if (preg_match('/^(brands|tag(?:name)?s|order)-(.*)$/u', $option, $parts)) {
				$filters[$parts[1]] = explode(',', $parts[2]);
			}
		}
		if (is_array($filters['order']) && !empty($filters['order'])) {
			//normalization to proper column names
			$filters['order'] = array_map(function ($field) {
				switch (trim($field)) {
                    case 'brand':
                        return $field = 'b.name'; break;
                    case 'date':
                        return $field = 'p.created_at DESC'; break;
                    default:
                        return $field =  'p.' . $field;
                }
			}, $filters['order']);
		}

		if (isset($filters['tagnames']) && !empty($filters['tagnames'])){
			$tags = Models_Mapper_Tag::getInstance()->findByName($filters['tagnames'], true);
			if ($tags){
				$filters['tags'] = array_keys($tags);
			} else {
				$filters['tags'] = array(0);
			}
			unset($tags, $filters['tagnames']);
		}

		if (!empty($filters['tags'])) {
			foreach ($filters['tags'] as $tagId) {
				array_push($this->_cacheTags, 'prodtag_' . $tagId);
			}
		}

		if (!empty($filters['brands'])) {
			foreach ($filters['brands'] as $brand) {
				array_push($this->_cacheTags, 'prodbrand_' . $brand);
			}
		}

		$this->_view->filters = $filters;

		//if no filters passed in the product list we will check if it is a PL of product ids
		if (preg_match('~^[0-9,]+$~', $this->_options[0])) {
			$idsWhere = 'p.id IN (' . $this->_options[0] . ')';
			$enabledOnly = $idsWhere . ' AND ' . $enabledOnly;
		}

		return $this->_productMapper->fetchAll($enabledOnly, $filters['order'],
			(isset($this->_options[0]) && is_numeric($this->_options[0]) ? intval($this->_options[0]) : null), $this->_limit,
			null, $filters['tags'], $filters['brands'], $this->_strictTagsCount);
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
		if (sizeof($exploded) != 2) {
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
	 * @param array $products     The set of products
	 * @param array $sortingOrder can be array('name', 'brand', 'price')
	 * @return array Sorted set of products
	 */
	private function _sort($products, $sortingOrder) {
		uasort($products, function ($productOne, $productTwo) use ($sortingOrder) {
			$compareResult = 0;
			foreach ($sortingOrder as $orderTerm) {
				$getter = 'get' . ucfirst($orderTerm);
				if (!method_exists($productOne, $getter) || !method_exists($productTwo, $getter)) {
					continue;
				}
				$productOneTerm = $productOne->$getter();
				$productTwoTerm = $productTwo->$getter();
				if (is_numeric($productOneTerm) && is_numeric($productTwoTerm)) {
					$compareResult = ($productOneTerm - $productTwoTerm) ? ($productOneTerm - $productTwoTerm) / abs($productOneTerm - $productTwoTerm) : 0;
					if ($compareResult !== 0) {
						return $compareResult;
					}
				} else {
					$compareResult = strcasecmp($productOneTerm, $productTwoTerm);
					if ($compareResult !== 0) {
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
