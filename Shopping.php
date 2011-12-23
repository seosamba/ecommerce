<?php
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(__DIR__ . '/system/app'),
    get_include_path(),
)));

/**
 * Ecommerce plugin for SEOTOASTER 2.0
 * @todo Authorization check
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 * @see http://www.seotoaster.com
 */
class Shopping extends Tools_Plugins_Abstract {
	const PRODUCT_CATEGORY_NAME	= 'Product Pages';
	const PRODUCT_CATEGORY_URL	= 'product-pages';

	/**
	 * @var Zend_Controller_Action_Helper_Json json helper for sending well-formated json response
	 */
	protected $_jsonHelper;


    /**
     * @var array
     */
	private $_websiteConfig;

    /**
     * @var Models_Mapper_ShoppingConfig
     */
	private $_configMapper = null;

	public function  __construct($options, $seotoasterData) {
		parent::__construct($options, $seotoasterData);
		$this->_view->setUseStreamWrapper(true)->setScriptPath(__DIR__ . '/system/views/');
		$this->_jsonHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$this->_websiteConfig	= Zend_Registry::get('website');
		$this->_configMapper = Models_Mapper_ShoppingConfig::getInstance();
	}

	public function run($requestedParams = array()) {
		$dispatchersResult = parent::run($requestedParams);
		if($dispatchersResult) {
			return $dispatchersResult;
		}
	}

    public static function getTabContent() {
        $translator = Zend_Registry::get('Zend_Translate');
        $view       = new Zend_View(array(
            'scriptPath' => dirname(__FILE__) . '/system/views'
        ));
        $websiteHelper    = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
	    $view->websiteUrl = $websiteHelper->getUrl();

	    //getting product listing templates
	    $view->productTemplates = Application_Model_Mappers_TemplateMapper::getInstance()->findByType(Application_Model_Models_Template::TYPE_LISTING);
        return array(
            'title'   => '<span id="products">' . $translator->translate('Products') . '</span>',
            'content' => $view->render('uitab.phtml')
        );
        unset($translator);
        unset($view);
    }

	/**
	 * Method renders shopping config screen and handling config saving.
     * @return html
	 */
	protected function configAction(){
		$config = $this->_configMapper->getConfigParams();

		$form = new Forms_Config();
		if ($this->_request->isPost()){
			if ($form->isValid($this->_requestedParams)){
				foreach ($form->getValues() as $key => $subFormValues) {
					$this->_configMapper->save($subFormValues);
				}
				$this->_jsonHelper->direct($form->getValues());
			} else {
				$this->_jsonHelper->direct($form->getMessages());
			}
		}
		$form->populate($config);
		$this->_view->form = $form;

		echo $this->_view->render('config.phtml');
	}

	protected function setConfigAction(){
		$status = false;
		if ($this->_request->isPost()){
			$configMapper = Models_Mapper_ShoppingConfig::getInstance();
			$configParams = $this->_request->getParam('config');
			if ($configParams && is_array($configParams) && !empty ($configParams)){
				$status = $configMapper->save($configParams);
			}
		}
		$this->_jsonHelper->direct(array('done' => $status));
	}

	/**
	 * Method renders zones screen and handling zone saving
	 * @return html|json
	 * @todo better response
	 */
	protected function zonesAction(){
		if ($this->_request->isPost()){
			$zonesMapper = Models_Mapper_Zone::getInstance();
			$toRemove = $this->_request->getParam('toRemove');
			if (is_array($toRemove) && !empty ($toRemove)){
				$deleted = $zonesMapper->delete($toRemove);
			}
			$zones = $this->_request->getParam('zones');
			if (is_array($zones) && !empty ($zones)){
				$result = array();
				foreach ($zones as $id => $zone) {
					$zone = $zonesMapper->createModel($zone);
					$result[$id] = $zonesMapper->save($zone);
				}
			}
			$this->_jsonHelper->direct(array(
				'done'=>true,
				'id' => $result,
				'deleted' => isset($deleted) ? $deleted : null
				));
		}
		echo $this->_view->render('zones.phtml');
	}

	/**
	 * Method renders tax configuration screen and handling tax saving
	 * @return html
	 */
	protected function taxesAction() {
		if ($this->_request->isPost()){
			$taxMapper = Models_Mapper_Tax::getInstance();
			$toRemove = $this->_request->getParam('toRemove');
			if ($toRemove){
				$taxMapper->delete($toRemove);
			}

			$rules = $this->_request->getParam('rules');
			if ($rules) {
				foreach($rules as $rule){
					$taxMapper->save($rule);
				}
			}

			$this->_jsonHelper->direct(array('done'=>true));
		}
		$configMapper = Models_Mapper_ShoppingConfig::getInstance();
		$this->_view->priceIncTax = $configMapper->getConfigParam('showPriceIncTax');

		echo $this->_view->render('taxes.phtml');
	}

	/**
	 * Method used to get data from db
	 * for usage in AJAX scope
	 * @return json
	 */
	protected function getdataAction(){
		$data = array();
		if (isset($this->_requestedParams['type'])){
			$methodName = '_'.strtolower($this->_requestedParams['type']).'RESTService';
			if (method_exists($this, $methodName)){
				$data = $this->$methodName();
				if (!isset($data['error'])){
					return $this->_jsonHelper->direct($data);
				} else {
					$this->_response->clearAllHeaders()->clearBody();
					return $this->_response->setHttpResponseCode( isset($data['code']) ? intval($data['code']) : 400)
							->setBody(json_encode($data['message']))
							->sendResponse();
				}
			}
		}
		$this->_response->setHttpResponseCode(403)->sendResponse();
	}

	private function _countrylistRESTService(){
		$data = Tools_Geo::getCountries();
		asort($data);
		return $data;
	}

	private function _stateslistRESTService() {
		return Tools_Geo::getState();
	}

	private function _statesRESTService() {
		$country = $this->_request->getParam('country');
		return Tools_Geo::getState($country?$country:null, true);
	}

	private function _zonesRESTService() {
		$zonesMapper = Models_Mapper_Zone::getInstance();
		$zones = $zonesMapper->fetchAll();
		$data = array();
		foreach ($zones as $zone) {
			$data[] = $zone->toArray();
		}
		return $data;
	}

	private function _taxrulesRESTService() {
		$taxMapper = Models_Mapper_Tax::getInstance();
		$rules = $taxMapper->fetchAll();
		$data = array();
		foreach ($rules as $rule) {
			$data[] = $rule->toArray();
		}
		return $data;
	}

	private function _brandsRESTService() {
		$brandsList = Models_Mapper_Brand::getInstance()->fetchAll(null, array('name'));

        $pageMapper = Application_Model_Mappers_PageMapper::getInstance();
        $pagesUrls = $pageMapper->fetchAllUrls();

        $data = array();
        switch (strtolower($this->_request->getMethod())){
            default:
            case 'get':
                foreach ($brandsList as $brand) {
                    $item = $brand->toArray();
                    if (in_array(strtolower($brand->getName()).'.html', $pagesUrls)){
                        $item['url'] = strtolower($brand->getName()).'.html';
                    }
                    array_push($data, $item);
                }
                break;
            case 'post':
                $postData = json_decode($this->_request->getRawBody(), true);
                if (!empty($postData)){
                    $brand = Models_Mapper_Brand::getInstance()->save($postData);
                    if ($brand instanceof Models_Model_Brand){
                        $data = $brand->toArray();
                    }
                } else {
                    $data = array(
                        'error' => true,
                        'message' => 'No data provided'
                    );
                }
                break;
        }
		return $data;
	}

	private function _categoriesRESTService() {
		$data = array();
		$catMapper = Models_Mapper_Category::getInstance();
		$id = isset($this->_requestedParams['id']) ? filter_var($this->_requestedParams['id'], FILTER_VALIDATE_INT) : false;
		switch (strtolower($this->_request->getMethod())){
			case 'get':
				if ($id) {
					$result = $catMapper->find($id);
					if ($result !== null){
						$data = $result->toArray();
					}
				} else {
					foreach ($catMapper->fetchAll(null, array('name')) as $cat){
						array_push($data, $cat->toArray());
					}
				}
				break;
			case 'post':
			case 'put':
				$rawData = json_decode($this->_request->getRawBody(), true);
				if (!empty($rawData)){
					$result = $catMapper->save($rawData);
				} else {
					continue;
				}
				if ($result === null){
					$data = array('error'=>true, 'message' => 'Category with such name already exists', 'code' => 400);
				} else {
					$data = $result->toArray();
				}
				break;
			case 'delete':
				if ($id !== false){
					$result = $catMapper->delete($id);
				} else {
					$result = false;
				}
				$data = array('done'=>(bool)$result, 'deleted'=>$id);
				break;
		}
		return $data;
	}

	protected function _productRESTService(){
		$productMapper = Models_Mapper_ProductMapper::getInstance();
		$method        =  $this->_request->getMethod();
		$data          = array();
		$cacheHelper   = Zend_Controller_Action_HelperBroker::getStaticHelper('cache');
		switch ($method){
			case 'GET':
				$id = isset ($this->_requestedParams['id']) ? $this->_requestedParams['id'] : null;
				if ($id !== null) {
					$product              = $productMapper->find($id);
					if ($product !== null) {
						$data = $product->toArray();
					}
				} else {
					$filter['categories'] = isset($this->_requestedParams['fcat']) ? $this->_requestedParams['fcat'] : null;
					$filter['brands']     = isset($this->_requestedParams['fbrand']) ? $this->_requestedParams['fbrand'] : null;
					$categoryPart         = (is_array($filter['categories']) && !empty($filter['categories'])) ? implode('.', $filter['categories']) : 'allcategories';
					$brandPart            = (is_array($filter['brands']) && !empty($filter['brands'])) ? implode('.', $filter['brands']) : 'allbrands';
					$cacheKey             = $categoryPart . $brandPart;
					if(($data = $cacheHelper->load($cacheKey, 'store_')) === null) {
						$data = array();
						if(is_array($filter['categories']) && !empty($filter['categories'])) {
							$products = $productMapper->findByCategories($filter['categories']) ;
						}
						else {
							$products = $productMapper->fetchAll();
						}
						foreach ($products as $product) {
							if(is_array($filter['brands']) && !empty($filter['brands'])) {
								if(!in_array($product->getBrand(), $filter['brands'])) {
									continue;
								}
							}
							array_push($data, $product->toArray());
						}
						$cacheHelper->save($cacheKey, $data, 'store_', array(), Helpers_Action_Cache::CACHE_NORMAL);
					}
				}
				break;
			case 'POST':
				$srcData = Zend_Json_Decoder::decode($this->_request->getRawBody());
                $isUniq = $productMapper->fetchAll(array('sku = ?' => $srcData['sku']));
                if (!empty($isUniq)){
                    return array(
                        'error' => true,
                        'message' => 'You already have a product with this SKU'
                    );
                }
                try {
                    $newProduct = $productMapper->save($srcData);
                } catch (Exception $e){
                    error_log($e->getMessage());
                    return array(
                        'error' => true,
                        'message' => "Can't save product"
                    );
                }
				if ($newProduct instanceof Models_Model_Product){
					$page = $this->_savePageForProduct($newProduct, $srcData['pageTemplate']);
					$newProduct->setPage($page);
					$productMapper->updatePageIdForProduct($newProduct);

					$data = $newProduct->toArray();
				} else {
					$data = array(
						'error' => true,
						'code'	=> 404,
						'message' => "Can't create product"
						);
					$data = $newProduct->toArray();
				}
				break;
			case 'PUT':
				$srcData = json_decode($this->_request->getRawBody(), true);
				$product = $productMapper->save($srcData);
				if (!$product->getPage()){
					$page = $this->_savePageForProduct($product, $srcData['pageTemplate']);
					$product->setPage($page);
					$productMapper->updatePageIdForProduct($product);
				} else {
					$page = new Application_Model_Models_Page($product->getPage());
					$pageMapper = Application_Model_Mappers_PageMapper::getInstance();

					if (isset($srcData['pageTemplate']) && $srcData['pageTemplate'] !== $page->getTemplateId()){
						$page->setTemplateId($srcData['pageTemplate']);
					}

					$page->setDraft((bool)$product->getEnabled()?'0':'1');

					if ($pageMapper->save($page)){
						$product->setPage($page);
					}
				}
				$data = $product->toArray();
				break;
			case 'DELETE':
				$uri = trim($this->_request->getRequestUri(), '/\ ');
				$uri = substr($uri, strrpos($uri, '/'));
				$id = filter_var($uri, FILTER_SANITIZE_NUMBER_INT);
				if ($product = $productMapper->find($id)) {
					if (!$productMapper->delete($product)){
						$data = array(
							'error' => true,
							'code' => 404,
							'message' => 'Can not delete product #'.$id
						);
					}
				} else {
					$data = array(
						'error'		=> true,
						'code'		=> 404,
						'message'	=> 'Requested product not found'
						);
				}
				break;
			default:
				break;
		}

		return $data;
	}

    protected function _optionsRESTService(){
        $optionMapper = Models_Mapper_OptionMapper::getInstance();
        return $optionMapper->fetchAll(array('parentId = ?' => 0), null, false);
    }

	protected function _savePageForProduct(Models_Model_Product $product, $templateId = null){
		$pageMapper = Application_Model_Mappers_PageMapper::getInstance();
		$prodCatPage = $pageMapper->findByUrl(self::PRODUCT_CATEGORY_URL);
		if (!$prodCatPage){
			$prodCatPage = new Application_Model_Models_Page(array(
				'h1Tag'			=> self::PRODUCT_CATEGORY_NAME,
				'headerTitle'	=> self::PRODUCT_CATEGORY_NAME,
				'url'			=> self::PRODUCT_CATEGORY_URL,
				'navName'		=> self::PRODUCT_CATEGORY_NAME,
				'metaDescription'	=> '',
				'teaserText'	=> '',
				'templateId'	=> $templateId ? $templateId : 'default',
				'parentId'		=> 0,
				'system'		=> 1,
				'is404page'		=> 0,
				'protected'		=> 0,
				'memLanding'	=> 0,
				'siloId'		=> 0,
				'lastUpdate'	=> date(DATE_ATOM),
				'showInMenu'	=> 0,
				'targetedKey'	=> self::PRODUCT_CATEGORY_NAME
			));
			$prodCatPage->setId( $pageMapper->save($prodCatPage) );
		}
		$page = new Application_Model_Models_Page();
		$uniqName = array_map(function($str){
            $filter = new Zend_Filter_PregReplace(array(
                   'match'   => '/[^\w\d]+/',
                   'replace' => '-'
                ));
            return trim($filter->filter($str), ' -');
            }
            , array( $product->getBrand(), $product->getName(), $product->getSku() ));
		$uniqName = implode('-', $uniqName);
		$page->setTemplateId($templateId ? $templateId : 'default' );
		$page->setParentId($prodCatPage->getId());
		$page->setNavName($product->getName().($product->getSku()?' - '.$product->getSku():''));
        $page->setMetaDescription(strip_tags($product->getShortDescription()));
		$page->setMetaKeywords('');
		$page->setHeaderTitle($uniqName);
		$page->setH1($product->getName());
		$page->setUrl(strtolower($uniqName).'.html');
		$page->setTeaserText(strip_tags($product->getShortDescription()));
		$page->setLastUpdate(date(DATE_ATOM));
		$page->setIs404page(0);
		$page->setShowInMenu(1);
		$page->setSiloId(0);
		$page->setTargetedKey(self::PRODUCT_CATEGORY_NAME);
		$page->setProtected(0);
		$page->setSystem(0);
		$page->setDraft((bool)$product->getEnabled()?'0':'1');
		$page->setMemLanding(0);
		$page->setNews(0);

		$id = $pageMapper->save($page);

		if($id) {
			$page->setId($id);
            //setting product photo as page preview
            if ($product->getPhoto() != null){
                $miscConfig = Zend_Registry::get('misc');
                $savePath = $this->_websiteConfig['path'] . $this->_websiteConfig['preview'];
                $existingFiles = preg_grep('~^'.strtolower($uniqName).'\.(png|jpg|gif)$~i', Tools_Filesystem_Tools::scanDirectory($savePath, false, false));
                if  (!empty($existingFiles)){
                    foreach ($existingFiles as $file) {
                        Tools_Filesystem_Tools::deleteFile($savePath.$file);
                    }
                }
                $productImg = $this->_websiteConfig['path'] . $this->_websiteConfig['media'] . str_replace('/', '/small/' , $product->getPhoto());
                $pagePreviewImg = $savePath.strtolower($uniqName).'.'.pathinfo($productImg, PATHINFO_EXTENSION);
                if (copy($productImg, $pagePreviewImg)) {
                    Tools_Image_Tools::resize($pagePreviewImg, $miscConfig['pageTeaserSize'], true, null, true);
                }
            }
		} else {
			return null;
		}
		return $page;
	}

	/**
     * Method renders product management screen
     * @var $pageMapper Application_Model_Mappers_PageMapper
     */
    protected function productAction(){
		$this->_view->generalConfig = $this->_configMapper->getConfigParams();

		$templateMapper = Application_Model_Mappers_TemplateMapper::getInstance();
		$templateList = $templateMapper->findByType(Application_Model_Models_Template::TYPE_PRODUCT);
		$this->_view->templateList = $templateList;

		$listFolders = Tools_Filesystem_Tools::scanDirectoryForDirs($this->_websiteConfig['path'].$this->_websiteConfig['media']);
		if (!empty ($listFolders)){
			$listFolders = array('select folder') + array_combine($listFolders, $listFolders);
		}
		$this->_view->imageDirList = $listFolders;

        $this->_view->plugins = array();
        foreach (Tools_Plugins_Tools::getEnabledPlugins() as $plugin){
            if ($plugin->getTag() === 'ecommerce') {
                array_push($this->_view->plugins, $plugin->getName());
            }
        }

		echo $this->_view->render('product.phtml');
	}

	protected function debugAction(){

	}

    /**
     * for test purposes only
     * @todo remove it
     */
    protected function _makeOptionCart(){
        return '<div style="border: 1px dashed red;">This is dummy cart widget<br />But nothing here yet<hr /><a href="#">go to cart</a></div>';
    }

    /**
     * for test purposes only
     * @todo remove it
     */
    protected function _makeOptionAddtocart(){
        return '<div style="border: 1px dashed red;"><button>ADD TO CART</button></div>';
    }
}
