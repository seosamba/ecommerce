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
     * Option to apply product list filtering via URI params
     */
    const OPTION_FILTERABLE = 'filterable';

    /**
     * Option to create custom product order in product list
     */
    const OPTION_DRAGGABLE = 'draggable';

    const OPTION_USER_ORDER = 'userorder';

    /**
     * Option to create a dropdown for product list sorting
     */
    const OPTION_USER_ORDER_SELECT = 'userorderselect';

    /**
     * Option to create a radio buttons for product list sorting
     */
    const OPTION_USER_ORDER_RADIO = 'userorderradio';

    /**
     * Option to create arrows for product list sorting
     */
    const OPTION_USER_ORDER_ARROW = 'userorderarrow';

    const SORTING_STYLE_SELECT = 'select';

    const SORTING_STYLE_RADIO = 'radio';

    const SORTING_STYLE_ARROW = 'arrow';

    /**
     * Option to apply "AND" logic for tags filtering
     */
    const OPTION_STRICT_TAGS_COUNT = 'and';

	/**
	 * Product list default offset (used for portional load)
	 */
	const DEFAULT_LIMIT = 50;

    /**
     * @var array
     */
    public $draglist = array();

    /**
     * @var bool
     */
    public $isDraggable = false;

    /**
     * @var bool
     */
    public $isArrowSortingStyle = false;

    public $userOrder = null;

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
	 * Set website url
	 *
	 * @var string
	 */
	protected $_websiteUrl;

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

        if (in_array(self::OPTION_FILTERABLE, $this->_options)) {
            $this->_cacheId = 'filtered_'.md5($this->_cacheId.$_SERVER['QUERY_STRING']);
        }

        if (in_array(self::OPTION_DRAGGABLE, $this->_options) || in_array(self::OPTION_FILTERABLE, $this->_options) || in_array(self::OPTION_USER_ORDER, $this->_options)) {
            $this->_cacheable = false;
        }
	}

	public function _load() {
		$this->_view = new Zend_View(array('scriptPath' => __DIR__ . '/views/'));
		$this->_view->addHelperPath('ZendX/JQuery/View/Helper/', 'ZendX_JQuery_View_Helper');
        $last = end($this->_options);
        $isPreview = filter_var(Zend_Controller_Front::getInstance()->getRequest()->getParam('prodListPreview'), FILTER_SANITIZE_STRING);
        if (!empty($isPreview)) {
            $this->_view->isPreview = $isPreview;
        }

        $dragListId = null;

        if (array_search(self::OPTION_DRAGGABLE, $this->_options) !== false && (strpos($_SERVER['QUERY_STRING'], 'userOrder') === false || strpos($_SERVER['QUERY_STRING'], 'userOrder=default') !== false)) {
            if(empty($isPreview) && Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT)) {
                $this->isDraggable = true;
            }

            $optionsForDragKey =  $this->_options;
            $withLimit = end($this->_options);
            if (is_numeric($withLimit)) {
                array_pop($optionsForDragKey);
            }
            $dragListId = md5(implode(',', $optionsForDragKey));
            $dragMapper = Models_Mapper_DraggableMapper::getInstance();
            $dragModel = $dragMapper->find($dragListId);
            if ($dragModel instanceof Models_Model_Draggable) {
                $this->draglist['list_id'] = $dragModel->getId();
                $this->draglist['data'] = unserialize($dragModel->getData());
            }
            $this->_view->dragListId = $dragListId;
        }

        if(in_array(self::OPTION_FILTERABLE, $this->_options)) {
            $this->_view->filterable = self::OPTION_FILTERABLE;
        }
        if (in_array(self::OPTION_USER_ORDER_ARROW, $this->_options)) {
            $this->isArrowSortingStyle = true;
        }

        if (is_numeric($last)) {
            $last = abs(intval($last));
            if ($last !== 0 && count($this->_options) > 1) {
                $this->_limit = $last;
            }
        }

        if (null === $this->_limit) {
            $this->_limit = self::DEFAULT_LIMIT;
        }
        $this->_view->limit = $this->_limit;
        $this->_websiteHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('website');
		$this->_websiteUrl = $this->_websiteHelper->getUrl();
        $this->_view->websiteUrl = $this->_websiteUrl;
		$this->_productMapper = Models_Mapper_ProductMapper::getInstance();
        $this->_strictTagsCount = in_array(self::OPTION_STRICT_TAGS_COUNT, $this->_options);

		$content = $this->_processList();

		if ($this->_cleanListOnly) {
			return $content;
		}
		$this->_view->pageId = $this->_toasterOptions['id'];
		$this->_view->plContent = $content;
		$this->_view->productTemplate = $this->_productTemplate->getName();

        if(!empty($this->_priceFilter)){
            $this->_view->price = $this->_priceFilter;
        }

		array_push($this->_cacheTags, preg_replace('/[^\w\d_]/', '', $this->_view->productTemplate));

        if (Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT) && array_search(self::OPTION_DRAGGABLE, $this->_options) && !$isPreview) {
            $this->_view->pageId = $this->_toasterOptions['id'];

            return $this->_view->render('draggable.phtml');
        }

        $orderSql = Zend_Db_Select::SQL_ASC;
        if(in_array('desc', $this->_options)){
            $orderSql = Zend_Db_Select::SQL_DESC;
        }
        if ($this->userOrder && $this->_view->filterable === self::OPTION_FILTERABLE) {
            $this->_view->filters['order'] = $this->userOrder[0] != 'date' ? array($this->userOrder[0]) : array('created_at');
            $orderSql = $this->userOrder[1];
        }
        $this->_view->sort = $orderSql;

        if(in_array('unwrap', $this->_options)){
            $this->_view->unwrap = true;
        }

        if (in_array(self::OPTION_USER_ORDER_SELECT, $this->_options) || in_array(self::OPTION_USER_ORDER_RADIO, $this->_options) || in_array(self::OPTION_USER_ORDER_ARROW, $this->_options)) {
            $userOrderOptions = [
                'default' => ['title' => $this->_translator->translate('Featured'), 'selected' => 1],
                'name_' . Zend_Db_Select::SQL_ASC => ['title' => $this->_translator->translate('Name: A-Z'), 'selected' => 0],
                'name_' . Zend_Db_Select::SQL_DESC => ['title' => $this->_translator->translate('Name: Z-A'), 'selected' => 0],
                'price_' . Zend_Db_Select::SQL_ASC => ['title' => $this->_translator->translate('Price: Low to High'), 'selected' => 0],
                'price_' . Zend_Db_Select::SQL_DESC => ['title' => $this->_translator->translate('Price: High to Low'), 'selected' => 0],
                'date_' . Zend_Db_Select::SQL_ASC => ['title' => $this->_translator->translate('Oldest to newest'), 'selected' => 0],
                'date_' . Zend_Db_Select::SQL_DESC => ['title' => $this->_translator->translate('Newest to oldest'), 'selected' => 0],

            ];
            if (!empty($this->_view->filters['order']) && isset($this->_view->filters['order'][0]) && !$dragListId) {
                if (strpos($this->_view->filters['order'][0], 'name') !== false) {
                    $userOrderOptions['name_' . $orderSql]['selected'] = 1;
                    $userOrderOptions['default']['selected'] = 0;
                } elseif (strpos($this->_view->filters['order'][0], 'price') !== false) {
                    $userOrderOptions['price_' . $orderSql]['selected'] = 1;
                    $userOrderOptions['default']['selected'] = 0;
                } elseif (strpos($this->_view->filters['order'][0], 'created_at') !== false) {
                    $userOrderOptions['date_' . $orderSql]['selected'] = 1;
                    $userOrderOptions['default']['selected'] = 0;
                }
            }
            $this->_view->userOrderOptions = $userOrderOptions;
            $this->_view->sortingStyle = in_array(self::OPTION_USER_ORDER_SELECT, $this->_options) ? self::SORTING_STYLE_SELECT : self::SORTING_STYLE_RADIO;


            if (in_array(self::OPTION_USER_ORDER_ARROW, $this->_options)) {
                foreach ($userOrderOptions as $key => $data) {
                    if ($key === 'default') {
                        continue;
                    }
                    if (strpos($key, 'name') !== false && !empty($userOrderOptions[$key])) {
                        $userOrderOptions[$key]['title'] = $this->_translator->translate('Name');
                    } elseif (strpos($key, 'price') !== false && !empty($userOrderOptions[$key])) {
                        $userOrderOptions[$key]['title'] = $this->_translator->translate('Price');
                    } elseif (strpos($key, 'date') !== false && !empty($userOrderOptions[$key])) {
                        $userOrderOptions[$key]['title'] = $this->_translator->translate('Date');
                    }
                    if ($data['selected'] === 0 && $userOrderOptions[explode('_', $key)[0] . '_' . Zend_Db_Select::SQL_DESC]['selected'] === 1) {
                        unset($userOrderOptions[$key]);
                    } elseif ($userOrderOptions[explode('_', $key)[0] . '_' . Zend_Db_Select::SQL_DESC]['selected'] === 0) {
                        unset($userOrderOptions[explode('_', $key)[0] . '_' . Zend_Db_Select::SQL_DESC]);
                    }
                }
                $this->_view->userOrderOptions = $userOrderOptions;
                $this->_view->sortingStyle = self::SORTING_STYLE_ARROW;
            }


        }
           /* $userOrderOptions = [
                'default' => ['title' => $this->_translator->translate('Featured'), 'selected' => 1],
                'date_' . Zend_Db_Select::SQL_ASC => ['title' => $this->_translator->translate('Date'), 'selected' => 0],
                'name_' . Zend_Db_Select::SQL_ASC => ['title' => $this->_translator->translate('Name'), 'selected' => 0],
                'price_' . Zend_Db_Select::SQL_ASC => ['title' => $this->_translator->translate('Price'), 'selected' => 0],
            ];

        }*/

		if (!isset($this->_options[0])) {
			$this->_view->offset = self::DEFAULT_LIMIT;
		} elseif (!intval($this->_options[0])) {
			return $this->_view->render('productlist.phtml');
		} else {
			$this->_view->offset = $this->_options[0];
		}

		return $this->_view->render('productlist.phtml');
	}

    protected function _dragListNewOrder()
    {
        if (isset($this->draglist) && is_array($this->draglist['data']) && isset($this->dragproducts) && is_array($this->dragproducts)) {
            $res = array();
            for ($i = 0; $i < count($this->draglist['data']); $i++) {
                foreach ($this->dragproducts as $product) {
                    $prodId = $product->getId();
                    if ($this->draglist['data'][$i] == $prodId) {
                        $res[$i] = $product;
                    }
                }
            }
            return $res;
        }
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
        if (!empty($this->draglist) && !empty($products)) {
            $productsToCompare = $products;
            if($this->_limit){
                $currentLimit = $this->_limit;
                unset($this->_limit);
                $productsToCompare = $this->_loadProducts();
                $this->_limit = $currentLimit;
            }
            $this->_compareProductsWithDraglist($productsToCompare);
        }
        if (!empty($this->_limit) && is_numeric($this->_limit) && !empty($this->draglist)) {
            $neededIds = array();

            if(!$this->isDraggable) {
                for ($i = 0; $i < $this->_limit; $i++) {
                    $neededIds[] = $this->draglist['data'][$i];
                }
            }else {
                $neededIds = $this->draglist['data'];
            }

            if (!empty($neededIds)) {
                $productMapper = Models_Mapper_ProductMapper::getInstance();
                $res = $productMapper->fetchAll($productMapper->getDbTable()->getAdapter()->quoteInto('p.id IN (?)',
                    $neededIds));
                $final = array();
                for ($i = 0; $i < count($neededIds); $i++) {
                    foreach ($res as $product) {
                        $prodId = $product->getId();
                        if ($neededIds[$i] == $prodId) {
                            $final[$i] = $product;
                        }
                    }
                }
                $products = $final;
            }

        } else {
            $this->dragproducts = $products;
        }

        $this->_view->dragproducts = $products;

        if (is_array($this->draglist['data']) && is_array($this->dragproducts)) {
            $dragOrderResult = $this->_dragListNewOrder();
            if (is_array($dragOrderResult) && (count($dragOrderResult) > 0)) {
                $products = $dragOrderResult;
                $this->_view->dragproducts = $products;
            }
        }

		$this->_view->totalCount = sizeof($products);
		$wesiteData = Zend_Registry::get('website');
		$confiHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('config');
		// init variables we will use in closure
		$renderedContent = array();

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

        if (!empty($this->_priceFilter)) {
            if(!empty($this->_priceFilter['additionalPrice'])) {
                $this->_priceFilter['min'] = $this->_priceFilter['additionalPrice']['min'];
                $this->_priceFilter['max'] = $this->_priceFilter['additionalPrice']['max'];
            }
            $data['priceFilter'] = $this->_priceFilter;
        }

		$cacheTags = array();
		// here we go - proccessing the list
        $websiteUrl = $this->_websiteUrl;
		array_walk($products, function ($product) use (&$renderedContent, $data, &$cacheTags, $websiteUrl) {
			array_push($cacheTags, 'prodid_' . $product->getId());
			if (strpos($data['templateContent'], '$store:addtocart') !== false) {
				$storeWidgetAddToCart = Tools_Factory_WidgetFactory::createWidget('store', array('addtocart', $product->getId()));
			}
			if (strpos($data['templateContent'], '$store:addtocart:checkbox') !== false) {
				$storeWidgetAddToCartCheckbox = Tools_Factory_WidgetFactory::createWidget('store', array('addtocart', $product->getId(), 'checkbox'));
			}
			//media servers (we are not using Tools_Content_Tools::applyMediaServers here because of the speed)
//			if ($data['mediaServersAllowed']) {
//				$mediaServer = Tools_Content_Tools::getMediaServer();
//				if ($mediaServer) {
//					$data['mediaPath'] = str_replace($data['websiteUrl'], $mediaServer . '.' . $data['domain'], $data['mediaPath']);
//				}
//			}
			// proccessing product photo and get some data
			$shortDesc = $product->getShortDescription();
			$templatePrepend = '<!--pid="' . $product->getId() . '"-->';

			if (strpos($data['templateContent'], '$product:options') !== false) {
				$view = new Zend_View(array('scriptPath' => dirname(__DIR__) . '/Product/views/'));
				$view->taxRate = Tools_Tax_Tax::calculateProductTax($product, null, true);
				$view->product = $product;
				$productOptionsView = $view->render('options.phtml');
			}

            $inventoryCount = $product->getInventory();

			if(!is_null($inventoryCount)) {
                $inventoryCount = trim($product->getInventory());
            }

            if (is_null($inventoryCount)){
                $productQty = '&infin;';
            } else {
                $productQty = $inventoryCount > 0 ? $inventoryCount : '0';
            }

			if(is_null($inventoryCount) || !empty($productQty)) {
                $inventoryCount = $this->_translator->translate('In stock');
            } else {
                $inventoryCount = $this->_translator->translate('Out of stock');
            }

            $dictionary = array(
                '$product:name'                       => htmlspecialchars($product->getName(),ENT_QUOTES,'UTF-8'),
                '$product:url'                        => $product->getPage() ? $websiteUrl . $product->getPage()->getUrl() : null,
                '$product:brand'                      => $product->getBrand(),
                '$product:weight'                     => $product->getWeight(),
                '$product:mpn'                        => $product->getMpn(),
                '$product:sku'                        => $product->getSku(),
                '$product:id'                         => $product->getId(),
                '$product:description:short'          => nl2br($shortDesc),
                '$product:description'                => nl2br($shortDesc),
                '$product:description:full'           => nl2br($product->getFullDescription()),
                '$store:addtocart'                    => isset($storeWidgetAddToCart) ? $storeWidgetAddToCart->render() : '',
                '$store:addtocart:'.$product->getId() => isset($storeWidgetAddToCart) ? $storeWidgetAddToCart->render() : '',
                '$store:addtocart:checkbox'           => isset($storeWidgetAddToCartCheckbox) ? $storeWidgetAddToCartCheckbox->render() : '',
                '$product:options'                    => isset($productOptionsView) ? $productOptionsView : '',
                '$product:inventory'                  => $inventoryCount,
                '$product:qty'                        => $productQty,
                '$product:wishlistqty'                => $product->getWishlistQty()
            );

            if (isset($data['priceFilter'])) {
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

                $price = Tools_ShoppingCart::getInstance()->calculateProductPrice(
                    $product,
                    $itemDefaultOptionsArray
                );

                $price = round($price, 2);

                if ($data['priceFilter']['min'] > $price || $data['priceFilter']['max'] < $price) {
                    return false;
                }
            }
            $renderedContent[] = Tools_Misc::preparingProductListing($templatePrepend.$data['templateContent'], $product, $dictionary, $data['noZeroPrice']);
		});
        if (!empty($this->_priceFilter)) {
            $this->_view->totalCount = sizeof($renderedContent);
        }
		$this->_cacheTags = array_merge($this->_cacheTags, $cacheTags);
		return implode('', $renderedContent);
	}

    protected function _compareProductsWithDraglist($products)
    {
        $productsIds = array();
        foreach ($products as $productModel) {
            $productsIds[] = $productModel->getId();
        }
        $notInDrag = array_diff($productsIds, $this->draglist['data']);
        $notInProducts = array_diff($this->draglist['data'], $productsIds);
        if (!empty($notInDrag)) {
            foreach ($notInDrag as $productId) {
                $this->draglist['data'][] = $productId;
            }
        }
        if (!empty($notInProducts)) {
            foreach ($notInProducts as $productId) {
                if (($i = array_search($productId, $this->draglist['data'])) !== false) {
                    unset($this->draglist['data'][$i]);
                }
            }

            if(!empty($this->draglist['data'])) {
                $this->draglist['data'] = array_values($this->draglist['data']);
            }
        }

        $currentUser = Zend_Controller_Action_HelperBroker::getStaticHelper('session')->getCurrentUser();
        $currentUserRole = $currentUser->getRoleId();
        $userId = $currentUser->getId();

        if ($currentUserRole === Tools_Security_Acl::ROLE_ADMIN || $currentUserRole === Tools_Security_Acl::ROLE_SUPERADMIN || $currentUserRole === Shopping::ROLE_SALESPERSON) {
            if (!empty($notInDrag) || !empty($notInProducts)) {
                $this->draglist['data'] = array_values($this->draglist['data']);
                $mapper = Models_Mapper_DraggableMapper::getInstance();
                $model = new Models_Model_Draggable();
                $model->setId($this->draglist['list_id']);
                $model->setData(serialize($this->draglist['data']));
                $model->setUpdatedAt(Tools_System_Tools::convertDateFromTimezone('now'));
                $model->setUserId($userId);
                $model->setIpAddress(Tools_System_Tools::getIpAddress());
                $model->setPageId($this->_toasterOptions['id']);
                $mapper->save($model);
            }
        }

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
     * @param bool $enabled Filter only enabled products
     * @return array|null
     */
    private function _loadProducts($enabled = true) {
        $allowedColumns = array(
            'id' => 'id',
            'parent_id' => 'parent_id',
            'page_id' => 'page_id',
            'enabled' => 'enabled',
            'sku' => 'sku',
            'name' => 'name',
            'mpn' => 'mpn',
            'weight' => 'weight',
            'brand_id' => 'brand',
            'photo' => 'photo',
            'short_description' => 'short_description',
            'full_description' => 'full_description',
            'price' => 'price',
            'tax_class' => 'tax_class',
            'created_at' => 'date',
            'updated_at' => 'updated_at',
            'base_price' => 'base_price',
            'inventory' => 'inventory',
            'free_shipping' => 'free_shipping'
        );

        $enabledOnly = $this->_productMapper->getDbTable()->getAdapter()->quoteInto('p.enabled=?', $enabled);


		if (empty($this->_options)) {
			array_push($this->_cacheTags, 'prodid_all');
			return $this->_productMapper->fetchAll($enabledOnly, null, 0, $this->_limit);
		}
		$filters = array(
			'tags'   => null,
			'brands' => null,
			'order'  => null
		);

        $orderSql = 'ASC';
        if(in_array('desc', $this->_options)){
            $orderSql = 'DESC';
        }

		foreach ($this->_options as $option) {
			if (preg_match('/^(brands|tag(?:name)?s|order)-(.*)$/u', $option, $parts)) {
				$filters[$parts[1]] = explode(',', $parts[2]);
			}
		}

        // fetching filters from query string
        $urlFilter = Filtering_Tools::normalizeFilterQuery();
        if($this->_view->filterable === self::OPTION_FILTERABLE && isset($urlFilter['userOrder']) && is_array($urlFilter['userOrder'])){
            $userOrder = explode('_', $urlFilter['userOrder'][0]);
            if(!empty($userOrder[0]) && !empty($userOrder[1])){
                $filters['order'] = array($userOrder[0]);
                $orderSql = $userOrder[1];
                $this->userOrder = $userOrder;
            }
        }
		if (is_array($filters['order']) && !empty($filters['order'])) {
			//normalization to proper column names
            $filters['order'] = array_map(function ($field) use ($allowedColumns) {
                if(in_array($field, $allowedColumns)) {
                    switch (trim($field)) {
                        case 'brand':
                            return $field = 'b.name'; break;
                        case 'date':
                            return $field = 'p.created_at'; break;
                        default:
                            return $field =  'p.' . $field;
                    }
                }
            }, $filters['order']);
            $filters['order'] = array_filter($filters['order']);
            if (empty($filters['order'])) {
                $filters['order'] = null;
            }
		}
        if (!empty($urlFilter['category'])) {
            $filters['tagnames'] = $urlFilter['category'];
            unset($urlFilter['category']);
        }
		if (isset($filters['tagnames']) && !empty($filters['tagnames'])) {
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

        if (!empty($urlFilter['brand'])) {
            $filters['brands'] = $urlFilter['brand'];
            unset($urlFilter['brand']);
        }
		if (!empty($filters['brands'])) {
			foreach ($filters['brands'] as $brand) {
				array_push($this->_cacheTags, 'prodbrand_' . $brand);
			}
		}

        $attributes = array();
        $priceFilter = array();
        $productPriceFilter = array();

        if (!empty($urlFilter) && in_array(self::OPTION_FILTERABLE, $this->_options)) {
            $attr = array_flip(Filtering_Mappers_Eav::getInstance()->getAttributeNames());
            if (!empty($urlFilter['price'])) {
                if(in_array('tax', $this->_options)) {
                    $tax = $this->getTax();
                }

                if (!empty($filters['tags'])) {
                    $sessionHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('session');
                    $currentUser = $sessionHelper->getCurrentUser()->getId();

                    $dbTable = new Models_DbTable_CustomerInfo();
                    $select = $dbTable->select()->from('shopping_customer_info', array('user_id', 'group_id'));
                    $allCustomersGroups =  $dbTable->getAdapter()->fetchAssoc($select);

                    if(!empty($allCustomersGroups)) {
                        if(array_key_exists($currentUser, $allCustomersGroups)){
                            $groupId = $allCustomersGroups[$currentUser]['group_id'];
                            $allProductsGroups = Store_Mapper_GroupMapper::getInstance()->fetchAssocAll();
                            if(isset($allProductsGroups[$groupId])){

                                $additionalPrice = array();
                                foreach ($urlFilter['price'] as $key => $range) {
                                    $priceNow = $range;
                                    $priceValue = $allProductsGroups[$groupId]['priceValue'];
                                    $priceSign  = $allProductsGroups[$groupId]['priceSign'];
                                    $priceType  = $allProductsGroups[$groupId]['priceType'];
                                    $nonTaxable = $allProductsGroups[$groupId]['nonTaxable'];

                                    if($priceType == 'percent'){
                                        if($priceSign == 'minus') {
                                            $remainder = 1 - ($priceValue / 100);
                                        }
                                        if($priceSign == 'plus') {
                                            $remainder = 1 + ($priceValue / 100);
                                        }

                                        if(!empty($tax) && empty($nonTaxable)) {
                                            if($tax < 10) {
                                                $tax = '0'. $tax;
                                            }

                                            $priceNow = $priceNow / "1.$tax";
                                        }

                                        $resultPrice = $priceNow / $remainder;
                                    }
                                    if($priceType == 'unit'){
                                        if(!empty($tax) && empty($nonTaxable)) {
                                            if($tax < 10) {
                                                $tax = '0'. $tax;
                                            }

                                            $priceNow = $priceNow / "1.$tax";
                                        }

                                        if($priceSign == 'minus') {
                                            $resultPrice = $priceNow + $priceValue;
                                        }
                                        if($priceSign == 'plus') {
                                            $resultPrice = $priceNow - $priceValue;
                                        }
                                    }

                                    $urlFilter['price'][$key] = $resultPrice;

                                    if($key == 'from') {
                                        $additionalPrice['min'] = $range;
                                    }
                                    if($key == 'to') {
                                        $additionalPrice['max'] = $range;
                                    }
                                }
                            }
                        } else {
                            if(!empty($tax)) {
                                if($tax < 10) {
                                    $tax = '0'. $tax;
                                }

                                $additionalPrice['min'] = $urlFilter['price']['from'];
                                $additionalPrice['max'] = $urlFilter['price']['to'];

                                $urlFilter['price']['from'] = $urlFilter['price']['from'] / "1.$tax";
                                $urlFilter['price']['to'] = $urlFilter['price']['to'] / "1.$tax";
                            }
                        }
                    }
                }

                $this->_priceFilter = array(
                    'min'   => $urlFilter['price']['from'],
                    'max'   => $urlFilter['price']['to'],
                    'additionalPrice' => $additionalPrice
                );
                unset($urlFilter['price']);
            }

            $options = array();
            foreach ($this->_options as $option) {
                if (preg_match('/^(additionalfilters)-(.*)$/u', $option, $parts)) {
                    $options = explode(',', $parts[2]);
                }
            }

            if (!empty($options)) {
                foreach ($options as $option) {
                    if(isset($urlFilter[$option])) {
                        $this->_productPriceFilter[] = array('min' => $urlFilter[$option]['from'], 'max' => $urlFilter[$option]['to']);
                        unset($urlFilter[$option]);
                    }
                }
            }
            // removing all
            $urlFilter = array_intersect_key($urlFilter, $attr);
            $idsWhere = '';
            if (!empty($urlFilter)) {
                $productIds = Filtering_Mappers_Eav::getInstance()->findProductIdsByAttributes($urlFilter);
                $attributes['attributes'] = $urlFilter;
                if (empty($productIds)) {
                    return null;
                }
                $idsWhere = Zend_Db_Table_Abstract::getDefaultAdapter()->quoteInto('p.id IN (?)', $productIds);
            }
        } elseif (preg_match('~^[0-9,]+$~', $this->_options[0])) {
            //if no filters passed in the product list we will check if it is a PL of product ids
			$idsWhere = 'p.id IN (' . $this->_options[0] . ')';
		}

        $this->_view->filterAttributes = $attributes;

        if (!empty($idsWhere)) {
            $enabledOnly = $idsWhere . ' AND ' . $enabledOnly;
        }

        if(isset($this->_priceFilter) && ($this->_priceFilter !== null) && (!empty($this->_priceFilter))){
            $priceFilter = $this->_priceFilter;
        }

        if(isset($this->_productPriceFilter) && ($this->_productPriceFilter !== null) && (!empty($this->_productPriceFilter)) && is_array($this->_productPriceFilter)){
            $productPriceFilter = $this->_productPriceFilter;
        }

        $limit = $this->_limit;

        if($this->isDraggable) {
            $limit = null;
        }

        $this->_view->filters = $filters;

		$data = $this->_productMapper->fetchAll(
		    $enabledOnly,
            $filters['order'],
            0,
            $limit,
            null,
            $filters['tags'],
            $filters['brands'],
            $this->_strictTagsCount,
            false,
            array(),
            $priceFilter,
            $orderSql,
            false,
            $productPriceFilter
        );
        return $data;
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

    public function getTax(){
        $filterTaxRate = Filtering_Mappers_Filter::getInstance()->getTaxRate();
        if(($filterTaxRate !== null) && (!empty($filterTaxRate))) {
            $tax = $filterTaxRate[0]['rate1'];
            return $tax;
        }else {
            $tax = '';
            return $tax;
        }
    }


}
