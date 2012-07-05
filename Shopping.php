<?php
/**
 * Ecommerce plugin for SEOTOASTER 2.0
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 * @see http://www.seotoaster.com
 */
class Shopping extends Tools_Plugins_Abstract {
	const PRODUCT_CATEGORY_NAME	= 'Product Pages';
	const PRODUCT_CATEGORY_URL	= 'product-pages.html';
	const PRODUCT_DEFAULT_LIMIT = 30;

	const BRAND_LOGOS_FOLDER = 'brands';
	/**
	 * New system role 'customer'
	 *
	 */
	const ROLE_CUSTOMER = 'customer';
	/**
	 * New system role 'salesperson'
	 */
	const ROLE_SALESPERSON = 'sales person';

	/**
	 * New system resource 'cart'
	 *
	 */
	const RESOURCE_CART = 'cart';

	/**
	 * New system resource 'api'
	 */
	const RESOURCE_API  = 'api';

	/**
	 * Default cart plugin
	 */
	const DEFAULT_CART_PLUGIN = 'cart';

	/**
	 * Default cache id for checkout page
	 */
	const CHECKOUT_PAGE_CACHE_ID = 'cart_checkoutpage';

	/**
	 * Cache prefix for use in shopping system
	 */
	const CACHE_PREFIX = 'store_';
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

    /**
     * @var array List of actions that should be secured
     */
    protected $_securedActions = array(
        Tools_Security_Acl::ROLE_SUPERADMIN => array(
            'config',
            'setConfig',
            'taxes',
            'zones',
            'product',
	        'shipping',
	        'clients',
	        'brandlogos'
        ),
	    Tools_Security_Acl::ROLE_ADMIN => array(
		    'brandlogos'
	    ),
	    Tools_Security_Acl::ROLE_GUEST => array(

	    )
    );

	private $_allowedApi = array('countrylist', 'states');

	public static $emailTriggers = array(
		'Tools_StoreMailWatchdog'
	);

	public function  __construct($options, $seotoasterData) {
		parent::__construct($options, $seotoasterData);

		$this->_layout = new Zend_Layout();
		$this->_layout->setLayoutPath(__DIR__ . '/system/views/');

		if ($viewScriptPath = Zend_Layout::getMvcInstance()->getView()->getScriptPaths()){
			$this->_view->setScriptPath($viewScriptPath);
		}
		$this->_view->addScriptPath(__DIR__ . '/system/views/');

		$this->_jsonHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$this->_websiteConfig	= Zend_Registry::get('website');
		$this->_configMapper = Models_Mapper_ShoppingConfig::getInstance();
	}

    public function beforeController(){
	    $cacheHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('cache');
	    if (null === ($checkoutPage = $cacheHelper->load(self::CHECKOUT_PAGE_CACHE_ID, self::CACHE_PREFIX))){
		    $checkoutPage = Tools_Page_Tools::getCheckoutPage();
		    $cacheHelper->save(self::CHECKOUT_PAGE_CACHE_ID, $checkoutPage, self::CACHE_PREFIX);
	    }
	    if (!$this->_request->isSecure()
			    && $checkoutPage instanceof Application_Model_Models_Page
			    && $checkoutPage->getUrl() === $this->_request->getParam('page')
			    && Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('forceSSLCheckout')){
		    $this->_redirector->gotoUrlAndExit(Zend_Controller_Request_Http::SCHEME_HTTPS.'://'.$this->_websiteConfig['url'].$checkoutPage->getUrl());
	    }

	    if (!Zend_Registry::isRegistered('Zend_Currency')){
            $shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
	        $locale = Zend_Locale::getLocaleToTerritory($shoppingConfig['country']);
	        try {
		        $currency = new Zend_Currency(
			        $shoppingConfig['currency'],
			        $locale
		        );
            } catch (Zend_Currency_Exception $e) {
                error_log($e->getMessage());
                error_log($e->getTraceAsString());
                $currency = new Zend_Currency();
            }
            Zend_Registry::set('Zend_Currency', $currency);
        }
	    $acl = Zend_Registry::get('acl');
        if(!$acl->hasRole(self::ROLE_CUSTOMER)) {
            $acl->addRole(new Zend_Acl_Role(self::ROLE_CUSTOMER), Tools_Security_Acl::ROLE_GUEST);
        }
	    if(!$acl->hasRole(self::ROLE_SALESPERSON)){
		    $acl->addRole(new Zend_Acl_Role(self::ROLE_SALESPERSON), Tools_Security_Acl::ROLE_MEMBER);
	    }
        if(!$acl->has(self::RESOURCE_CART)) {
            $acl->addResource(new Zend_Acl_Resource(self::RESOURCE_CART));
        }
	    if(!$acl->has(self::RESOURCE_API)) {
		    $acl->addResource(new Zend_Acl_Resource(self::RESOURCE_API));
	    }
        $acl->allow(self::ROLE_CUSTOMER, self::RESOURCE_CART);
	    $acl->deny(Tools_Security_Acl::ROLE_GUEST, self::RESOURCE_API);
	    $acl->deny(self::ROLE_SALESPERSON);
	    $acl->allow(self::ROLE_SALESPERSON, Tools_Security_Acl::RESOURCE_ADMINPANEL);
        Zend_Registry::set('acl', $acl);
    }

	public function run($requestedParams = array()) {
		$dispatchersResult = parent::run($requestedParams);
		if($dispatchersResult) {
			return $this->_getOption($dispatchersResult);
		}
	}

    public static function getTabContent() {
        $translator = Zend_Registry::get('Zend_Translate');
        $view       = new Zend_View(array(
            'scriptPath' => dirname(__FILE__) . '/system/views'
        ));
        $websiteHelper             = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
	    $configHelper              = Zend_Controller_Action_HelperBroker::getStaticHelper('config');
	    $view->websiteUrl          = $websiteHelper->getUrl();
	    $view->mediaServersAllowed = false;
	    if($configHelper->getConfig('mediaServers')) {
	        $view->websiteData         = Zend_Registry::get('website');
	        $view->domain              = str_replace('www.', '', $view->websiteData['url']);
		    $view->mediaServersAllowed = true;
	    }

	    //getting product listing templates
	    $view->productTemplates = Application_Model_Mappers_TemplateMapper::getInstance()->findByType(Application_Model_Models_Template::TYPE_LISTING);
        return array(
            'title'   => '<span id="products">' . $translator->translate('Products') . '</span>',
            'content' => $view->render('uitab.phtml')
        );
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
		$this->_view->form       = $form;
        $this->_view->configTabs = Tools_Plugins_Tools::getEcommerceConfigTabs();
		$this->_layout->content  = $this->_view->render('config.phtml');
		echo $this->_layout->render();
	}

	/**
	 * Shipping configuration action
	 * @todo Optimize view assertion - config and shipping plugins will be enough all others should be on the view
	 *
	 */
	protected function shippingAction() {
		if($this->_request->isPost()) {
			$shippingData = $this->_request->getParams();
			$this->_configMapper->save(array_map(function($param) {
				return (is_array($param)) ? serialize($param) : $param;
			}, $shippingData));
			$this->_jsonHelper->direct($shippingData);
		}
		$config                        = $this->_getConfig();
		$this->_view->config           = $config;
		$this->_view->shippingAmount   = isset($config['shippingAmount']) ? $config['shippingAmount'] : 0;
		$this->_view->shippingGeneral  = isset($config['shippingGeneral']) ? $config['shippingGeneral'] : 0;
		$this->_view->shippingWeight   = isset($config['shippingGeneral']) ? $config['shippingGeneral'] : 0;
		//$this->_view->shippingExternal = isset($config['shippingExternal']) ? json_encode($config['shippingExternal']) : 0;
		$this->_view->shippingPlugins  = array_filter(Tools_Plugins_Tools::getEnabledPlugins(), function($plugin){
			$reflection = new Zend_Reflection_Class(ucfirst($plugin->getName()));
			return $reflection->implementsInterface('Interfaces_Shipping');
		});
		$this->_layout->content  = $this->_view->render('shipping.phtml');
		echo $this->_layout->render();
	}

	protected function shippingconfigAction(){
		if (!Tools_Security_Acl::isAllowed(self::RESOURCE_API)){
			$this->_response->setHttpResponseCode(403)->sendResponse();
		}
		$plugin = filter_var($this->_request->getParam('pluginname'), FILTER_SANITIZE_STRING);
		if ($plugin){
			echo Tools_Misc::getShippingPluginContent($plugin);
		}
	}

	/**
	 * Checkout action
	 *
	 * @throws Exceptions_SeotoasterPluginException
	 */
	public function checkoutAction() {
		if($this->_request->isGet()) {
			throw new Exceptions_SeotoasterPluginException('Direct access not allowed');
		}
		if ($this->_request->isPut()) {
			$data = Zend_Json::decode($this->_request->getRawBody());
			if (!empty($data) && isset($this->_sessionHelper->tmpShippingRates)){
				if ($this->_applyCustomerShipping($data)) {
					$this->_responseHelper->success(array(
						'callback' => 'renderPaymentZone',
						'data'     => $this->_renderPaymentZone()
					));
				}
			}
			$this->_responseHelper->fail('Undefined error');
		}
		$shippingType = $this->_configMapper->getConfigParam('shippingType');
		if ($shippingType !== Tools_Shipping_Shipping::SHIPPING_TYPE_PICKUP) {
			$form = new Forms_Checkout_Shipping();
			$addressType = Models_Model_Customer::ADDRESS_TYPE_SHIPPING;
		} else {
			$form = new Forms_Checkout_Billing();
			$addressType = Models_Model_Customer::ADDRESS_TYPE_BILLING;
		}

		if ($form->isValid($this->_request->getParams())){
			$shoppingCart = Tools_ShoppingCart::getInstance();

			$formData = $form->getValues();

			$customer = $this->_processCustomer($formData);

			$addressId = Models_Mapper_CustomerMapper::getInstance()->addAddress($customer, $formData, $addressType);

			$shoppingCart->setAddressKey($addressType, $addressId);

			$shippingCalc = new Tools_Shipping_Shipping($this->_getConfig());
			try {
				$shippingData = $shippingCalc->calculateShipping();
				if (is_array($shippingData) && !empty($shippingData)){
					if (sizeof($shippingData) === 1 && sizeof($shippingData[0]['rates']) === 1){
						$shippingData = reset($shippingData);

						$shippingData['rates'] = reset($shippingData['rates']);
						$shoppingCart->setShippingData(array(
							'service' => $shippingData['service'],
							'type' => $shippingData['rates']['type'],
							'price' => $shippingData['rates']['price']
						));
						$responseData = array(
							'callback' => 'renderPaymentZone',
							'data'     => $this->_renderPaymentZone()
						);

					} else {
						$this->_sessionHelper->tmpShippingRates = $shippingData;
						$responseData = array(
							'callback' => 'showShippingDialog',
							'data'     => $shippingData
						);
					}
				}
			} catch (Exceptions_SeotoasterPluginException $spe) {
				$this->_responseHelper->fail($spe->getMessage());
			}
			//saving cart to session and db
			$shoppingCart->save()->saveCartSession($customer);
		} else {
			$this->_responseHelper->fail(Tools_Content_Tools::proccessFormMessagesIntoHtml($form->getMessages(),get_class($form)));
		}

		$this->_responseHelper->success($responseData);
	}

	private function _applyCustomerShipping($data) {
		foreach ($this->_sessionHelper->tmpShippingRates as $item){
			if (isset($item['service']) && $item['service'] === $data['service']){
				if (isset($item['rates'][$data['index']])){
					Tools_ShoppingCart::getInstance()->setShippingData(
						array(
							'service'   => $item['service'],
							'type'      => $item['rates'][$data['index']]['type'],
							'price'     => $item['rates'][$data['index']]['price']
						))->save()->saveCartSession(null);
					unset($this->_sessionHelper->tmpShippingRates);
					return true;
				}
			}
		}
		return false;
	}

	private function _processCustomer($data) {
		$customer = Tools_ShoppingCart::getInstance()->getCustomer();
		if (!$customer->getId()){
			if (null === ($existingCustomer = Models_Mapper_CustomerMapper::getInstance()->findByEmail($data['email']))) {
				$customer->setRoleId(Shopping::ROLE_CUSTOMER)
					->setEmail($data['email'])
					->setFullName($data['firstname'] . ' ' . $data['lastname'])
					->setIpaddress($_SERVER['REMOTE_ADDR'])
					->setPassword(md5(uniqid('customer_' . time())));
				$customer->registerObserver(new Tools_Mail_Watchdog(array(
					'trigger' => Tools_StoreMailWatchdog::TRIGGER_NEW_CUSTOMER
				)));
				$result = Models_Mapper_CustomerMapper::getInstance()->save($customer);
				if ($result) {
					$customer->setId($result);
					$customer->notifyObservers();
				}
			} else {
				return $existingCustomer;
			}
		}
		return $customer;
	}

	/**
	 * Redirects user to checkout page if it exists
	 * @throws Exceptions_SeotoasterPluginException
	 */
	public function cartAction() {
		$checkoutPage = Tools_Page_Tools::getCheckoutPage();
		if(!$checkoutPage instanceof Application_Model_Models_Page) {
			throw new Exceptions_SeotoasterPluginException('Error rendering cart. Please select a checkout page');
		}

		$this->_redirector->gotoUrl($this->_websiteUrl . $checkoutPage->getUrl());
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
		$this->_layout->content = $this->_view->render('zones.phtml');
		echo $this->_layout->render();
	}

	/**
	 * Method renders tax configuration screen and handling tax saving
	 * @return html
	 */
	protected function taxesAction() {
		$this->_view->priceIncTax = $this->_configMapper->getConfigParam('showPriceIncTax');
		$this->_layout->content = $this->_view->render('taxes.phtml');
		echo $this->_layout->render();
	}

	/**
	 * Method used to get data from db
	 * for usage in AJAX scope
	 * @return json
	 */
	protected function getdataAction(){
		$data = array();
		if (isset($this->_requestedParams['type'])){
			$type = strtolower($this->_requestedParams['type']);
			if (in_array($type, $this->_allowedApi) || Tools_Security_Acl::isAllowed(self::RESOURCE_API)){
				$methodName = '_'.$type.'RESTService';
				if (method_exists($this, $methodName)){
					$data = $this->$methodName();
					if (!isset($data['error'])){
						return $this->_jsonHelper->direct($data);
					} else {
						$this->_response->clearAllHeaders()->clearBody();
						return $this->_response->setHttpResponseCode( isset($data['code']) ? intval($data['code']) : 400)
								->setBody(json_encode($data['message']))
	                            ->setHeader('Content-Type', 'application/json', true)
								->sendResponse();
					}
				}
			}
		}
		$this->_response->setHttpResponseCode(403)->sendResponse();
	}

	private function _countrylistRESTService(){
        $toPairs = $this->_request->getParam('pairs', false);
		$data = Tools_Geo::getCountries($toPairs);
		asort($data);
		return $data;
	}

    /**
     * @deprecated
     * @return array|null
     */
	private function _stateslistRESTService() {
		return Tools_Geo::getState();
	}

	private function _statesRESTService() {
        $toPairs = $this->_request->getParam('pairs', false);
		$country = $this->_request->getParam('country');
		return Tools_Geo::getState($country?$country:null, $toPairs);
	}

	private function _zonesRESTService() {
		$zonesMapper = Models_Mapper_Zone::getInstance();
        switch (strtolower($this->_request->getMethod())){
            default:
            case 'get':
                $id = isset($this->_requestedParams['id']) ? filter_var($this->_requestedParams['id'], FILTER_SANITIZE_NUMBER_INT) : null;
                if ($id) {
                    $rule = $zonesMapper->find($id);
                    if ($rule instanceof Models_Model_Tax){
                        $data = $rule->toArray();
                    }
                } else {
                    $zones = $zonesMapper->fetchAll();
                    $data = array();
                    foreach ($zones as $zone) {
                        $data[] = $zone->toArray();
                    }
                }
                break;
            case 'post':
                $rules = $this->_request->getParam('zones', null);
                if ($rules) {
                    foreach ($rules as $rule) {
                        $data[] = $zonesMapper->save($rule);
                    }
                }
                break;
            case 'put':
                // for later use
                break;
            case 'delete':
                $id = isset($this->_requestedParams['id']) ? filter_var($this->_requestedParams['id'], FILTER_SANITIZE_NUMBER_INT) : null;
                if ($id){
                    $data = $zonesMapper->delete($id);
                }
                break;
        }

		return $data;
	}

	private function _taxrulesRESTService() {
		$taxMapper = Models_Mapper_Tax::getInstance();
        $data = array();
        switch (strtolower($this->_request->getMethod())){
            default:
            case 'get':
                $id = isset($this->_requestedParams['id']) ? filter_var($this->_requestedParams['id'], FILTER_SANITIZE_NUMBER_INT) : null;
                if ($id) {
                    $rule = $taxMapper->find($id);
                    if ($rule instanceof Models_Model_Tax){
                        $data = $rule->toArray();
                    }
                } else {
                    $rules = $taxMapper->fetchAll();
                    $data = array();
                    foreach ($rules as $rule) {
                        $data[] = $rule->toArray();
                    }
                }
                break;
            case 'post':
                $rules = $this->_request->getParam('rules', null);
                if ($rules) {
                    foreach ($rules as $rule) {
                        $data[] = $taxMapper->save($rule);
                    }
                }
                break;
            case 'put':
                // for later use
                break;
            case 'delete':
                $id = isset($this->_requestedParams['id']) ? filter_var($this->_requestedParams['id'], FILTER_SANITIZE_NUMBER_INT) : null;
                if ($id){
                    $data = $taxMapper->delete($id);
                }
                break;
        }
		return $data;
	}

	private function _brandsRESTService() {
        $data = array();
        switch (strtolower($this->_request->getMethod())){
            default:
            case 'get':
		        $brandsList = Models_Mapper_Brand::getInstance()->fetchAll(null, array('name'));
                $pagesUrls = Application_Model_Mappers_PageMapper::getInstance()->fetchAllUrls();
		        $data = array_map(function($brand) use ($pagesUrls) {
			        $item = $brand->toArray();
                    if (in_array(strtolower($brand->getName()).'.html', $pagesUrls)){
                        $item['url'] = strtolower($brand->getName()).'.html';
                    }
                    return $item;
		        }, $brandsList);
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

	private function _tagsRESTService() {
		$data = array();
		$tagMapper = Models_Mapper_Tag::getInstance();
		$id = isset($this->_requestedParams['id']) ? filter_var($this->_requestedParams['id'], FILTER_VALIDATE_INT) : false;
		switch (strtolower($this->_request->getMethod())){
			case 'get':
				if ($id) {
					$result = $tagMapper->find($id);
					if ($result !== null){
						$data = $result->toArray();
					}
				} else {
					foreach ($tagMapper->fetchAll(null, array('name')) as $cat){
						array_push($data, $cat->toArray());
					}
				}
				break;
			case 'post':
			case 'put':
				$rawData = json_decode($this->_request->getRawBody(), true);
				if (!empty($rawData)){
					$rawData['name'] = ucfirst($rawData['name']);
					$result = $tagMapper->save($rawData);
				} else {
					continue;
				}
				if ($result === null){
					$data = array(
						'error'=>true,
					    'message' => $this->_translator->translate('This tag already exists'),
					    'code' => 400
					);
				} else {
					$data = $result->toArray();
				}
				break;
			case 'delete':
				if ($id !== false){
					$result = $tagMapper->delete($id);
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
					$offset = isset($this->_requestedParams['offset']) ? $this->_requestedParams['offset'] : 0;
					$limit  = isset($this->_requestedParams['limit']) ? $this->_requestedParams['limit'] : self::PRODUCT_DEFAULT_LIMIT;
					$key    = isset($this->_requestedParams['key']) ? filter_var($this->_requestedParams['key'], FILTER_SANITIZE_STRING) : null;

					$filter['tags'] = isset($this->_requestedParams['ftag']) ? $this->_requestedParams['ftag'] : null;
					$filter['brands']     = isset($this->_requestedParams['fbrand']) ? $this->_requestedParams['fbrand'] : null;
					$tagPart              = (is_array($filter['tags']) && !empty($filter['tags'])) ? implode('.', $filter['tags']) : 'alltags';
					$brandPart            = (is_array($filter['brands']) && !empty($filter['brands'])) ? implode('.', $filter['brands']) : 'allbrands';
					$cacheKey             = $tagPart . $brandPart . $offset . $limit . $key;
					if(($data = $cacheHelper->load($cacheKey, 'store_')) === null) {
						$data = array();
						if(is_array($filter['tags']) && !empty($filter['tags'])) {
							$products = $productMapper->findByTags($filter['tags']) ;
						}
						else {
                            if(is_array($filter['brands']) && !empty($filter['brands'])) {
								$products = $productMapper->fetchAll(null, array());
							} else {
								$products = $productMapper->fetchAll(null, array(), $offset, $limit, (bool)$key?$key:null);
							}
						}
						if(!empty($products)) {
							foreach ($products as $product) {
								if(is_array($filter['brands']) && !empty($filter['brands'])) {
									if(!in_array($product->getBrand(), $filter['brands'])) {
										continue;
									}
								}
	                            //cleanup unnecessary values
								if ($product->getPage()){
		                            $product->setPage(array(
		                                'id'         => $product->getPage()->getId(),
		                                'url'        => $product->getPage()->getUrl(),
		                                'templateId' => $product->getPage()->getTemplateId()
		                            ));
								}

	                            array_push($data, $product->toArray());
							}
							$cacheHelper->save($cacheKey, $data, 'store_', array('productlist'), Helpers_Action_Cache::CACHE_NORMAL);
						}
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
                    $pageMapper = Application_Model_Mappers_PageMapper::getInstance();
                    $page = $pageMapper->find($srcData['page']['id']);
                    $isModified = false;

					if (isset($srcData['pageTemplate']) && $srcData['pageTemplate'] !== $page->getTemplateId()){
						$page->setTemplateId($srcData['pageTemplate']);
                        $isModified = true;
					}

                    if ($page->getDraft() !== $product->getEnabled()){
                        $page->setDraft(!(bool)$product->getEnabled());
	                    $cacheHelper->clean(Helpers_Action_Cache::KEY_DRAFT, Helpers_Action_Cache::PREFIX_DRAFT);
                        $isModified = true;
                    }

					if ($isModified){
                        $pageMapper->save($page);
					}
				}
				$cacheHelper->clean('Widgets_Product_Product_byPage_'.$page->getId(), 'store_');
				$cacheHelper->clean(false, false, array('prodid_'.$product->getId(), 'pageid_'.$page->getId()));
				$data = $product->toArray();
				break;
			case 'DELETE':
				preg_match_all('~product/(.*)$~', $this->_request->getRequestUri(), $uri);

				$ids = filter_var_array(explode('/', $uri[1][0]), FILTER_VALIDATE_INT);
                $ids = array_filter(array_unique($ids), function($id) {return (!empty($id) && is_numeric($id)); } );

				if (!empty($ids) && null !== ($products = $productMapper->fetchAll(array('id IN(?)' => $ids))) ) {
                    $result = array();
                    foreach ($products as $product){
                        $result[$product->getId()] = $productMapper->delete($product);
                        unset($product);
					}
                    if (!empty($result)){
                        $data = in_array(false, $result) ? array(
                            'error' => true,
                            'code' => 409,
                            'message' => $result
                        ) : $result;
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

        if (!$this->_request->isGet()){
            $cacheHelper->clean(null, null, array('productlist', 'productListWidget', 'productindex'));
	        Tools_FeedGenerator::getInstance()->generateProductFeed();
        }

		return $data;
	}

    protected function _optionsRESTService(){
        $optionMapper = Models_Mapper_OptionMapper::getInstance();
        return $optionMapper->fetchAll(array('parentId = ?' => 0), null, false);
    }

	protected function _savePageForProduct(Models_Model_Product $product, $templateId = null){
		$pageMapper = Application_Model_Mappers_PageMapper::getInstance();
        $pageHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('page');
		$prodCatPage = $pageMapper->findByUrl(self::PRODUCT_CATEGORY_URL);
		if (!$prodCatPage){
			$prodCatPage = new Application_Model_Models_Page(array(
				'h1'			=> self::PRODUCT_CATEGORY_NAME,
				'headerTitle'	=> self::PRODUCT_CATEGORY_NAME,
				'url'			=> self::PRODUCT_CATEGORY_URL,
				'navName'		=> self::PRODUCT_CATEGORY_NAME,
				'templateId'	=> Application_Model_Models_Template::ID_DEFAULT,
				'parentId'		=> 0,
				'system'		=> 1,
				'is404page'		=> 0,
				'protected'		=> 0,
				'memLanding'	=> 0,
				'showInMenu'	=> 0,
				'targetedKey'	=> self::PRODUCT_CATEGORY_NAME
			));
			$prodCatPage->setId( $pageMapper->save($prodCatPage) );
		}
		$page = new Application_Model_Models_Page();
		$uniqName = array_map(function($str){
            $filter = new Zend_Filter_PregReplace(array(
                   'match'   => '/[^\w\d]+/u',
                   'replace' => '-'
                ));
            return trim($filter->filter($str), ' -');
            }
            , array( $product->getBrand(), $product->getName(), $product->getSku() ));
		$uniqName = implode('-', $uniqName);
		$page->setTemplateId($templateId ? $templateId : Application_Model_Models_Template::ID_DEFAULT );
		$page->setParentId($prodCatPage->getId());
		$page->setNavName($product->getName().' - '.$product->getBrand());
        $page->setMetaDescription(strip_tags($product->getShortDescription()));
		$page->setMetaKeywords('');
		$page->setHeaderTitle($product->getBrand().' '.$product->getName());
		$page->setH1($product->getName());
		//$page->setUrl(strtolower($uniqName).'.html');
        $page->setUrl($pageHelper->filterUrl($uniqName));
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
        foreach (Tools_Plugins_Tools::getPluginsByTags(array('ecommerce')) as $plugin){
            if ($plugin->getTags() && in_array('merchandising', $plugin->getTags())) {
                array_push($this->_view->plugins, $plugin->getName());
            }
        }

		$this->_layout->content = $this->_view->render('product.phtml');
	    echo $this->_layout->render();
	}

    protected  function _indexRESTService(){
        $cacheHelper    = Zend_Controller_Action_HelperBroker::getStaticHelper('cache');

        if(($data = $cacheHelper->load('index', 'store_')) === null) {
            $data = Models_Mapper_ProductMapper::getInstance()->buildIndex();

            $cacheHelper->save('index', $data, 'store_', array('productindex'), Helpers_Action_Cache::CACHE_NORMAL);
        }

        return $data;
    }

	protected function _getConfig() {
		return array_map(function($param) {
			$unserialized = @unserialize($param);
			return ($unserialized === 'b:0' || $unserialized !== false) ? $unserialized : $param;
		}, $this->_configMapper->getConfigParams());
	}


	/**
	 * This action is used to help product list gets an portional content
	 *
	 * @throws Exceptions_SeotoasterPluginException
	 */
	public function renderproductsAction() {
		if(!$this->_request->isPost()) {
			throw new Exceptions_SeotoasterPluginException('Direct access not allowed');
		}
		$products = $this->_request->getParam('products');
		$offset   = $this->_request->getParam('offset');
		if(empty($products)) {
			$this->_responseHelper->success(array('content' => ''));
		}
		$products = array_map(function($item) {
			$product = new Models_Model_Product($item);
			$product->setPage(new Application_Model_Models_Page($item['page']));
			return $product;
		}, $products);
		$template = $this->_request->getParam('template');
		$widget   = Tools_Factory_WidgetFactory::createWidget('productlist', array($template, $offset + Widgets_Productlist_Productlist::DEFAULT_OFFSET));
		$content  = $widget->setProducts($products)->setCleanListOnly(true)->render();
		$widget->setProducts(array());
		$this->_responseHelper->success(array('content' => $content));
	}

	protected function _renderPaymentZone() {
		$paymentZoneTmpl = isset($this->_sessionHelper->paymentZoneTmpl) ? $this->_sessionHelper->paymentZoneTmpl : null;
		if ($paymentZoneTmpl !== null) {
			$themeData = Zend_Registry::get('theme');
			$extConfig = Zend_Registry::get('extConfig');
			$parserOptions = array(

				'websiteUrl'   => $this->_websiteHelper->getUrl(),
				'websitePath'  => $this->_websiteHelper->getPath(),
				'currentTheme' => $extConfig['currentTheme'],
				'themePath'    => $themeData['path'],
			);
			$parser = new Tools_Content_Parser($paymentZoneTmpl, Tools_Page_Tools::getCheckoutPage()->toArray(), $parserOptions);
			return $parser->parse();
		}
	}

	protected function _getOption($option) {
		$config = $this->_configMapper->getConfigParams();
		if(isset($config[$option])) {
			if($option == 'country') {
				$countries = Tools_Geo::getCountries(true);
				$option   = $countries[$config[$option]];
			} else {
				$option = $config[$option];
			}
		} else {
		   if($option == 'state') {
			   $option = '';
		   }
		}
		return $option;
	}

	public function clientsAction() {
		$content = $this->_view->render('clients.phtml');
		$this->_layout->content = $content;
		echo $this->_layout->render();
	}

	/**
	 * Generates list of website clients
	 * for admins only
	 * @return string Html content
	 */
	protected function _makeOptionClients() {
		if (Tools_Security_Acl::isAllowed(__CLASS__.'-clients')){
			$this->_view->noLayout = true;
			return $this->_view->render('clients.phtml');
		}
	}

	protected function _makeOptionProducts() {
		if (Tools_Security_Acl::isAllowed(__CLASS__.'-clients')){
			$this->_view->noLayout = true;
			return $this->_view->render('manage_products.phtml');
		}
	}

	/**
	 * @return array
	 */
	private function _customerRESTService() {
		$data = array();
		$customerMapper = Models_Mapper_CustomerMapper::getInstance();
		$id = isset($this->_requestedParams['id']) ? filter_var($this->_requestedParams['id'], FILTER_VALIDATE_INT) : false;
		$for = isset($this->_requestedParams['for']) ? filter_var($this->_requestedParams['for'], FILTER_SANITIZE_STRING) : false;
		switch (strtolower($this->_request->getMethod())){
			default:
			case 'get':
				if ($for === 'dashboard'){
					$order = filter_var($this->_request->getParam('order'), FILTER_SANITIZE_STRING);
					$limit = filter_var($this->_request->getParam('limit'), FILTER_SANITIZE_NUMBER_INT);
					$offset = filter_var($this->_request->getParam('offset'), FILTER_SANITIZE_NUMBER_INT);
					$search = filter_var($this->_request->getParam('search'), FILTER_SANITIZE_SPECIAL_CHARS);

					$c = Zend_Registry::get('Zend_Currency');
					$data = array_map(function($row) use ($c){
							$row['reg_date'] = date('d M, Y', strtotime($row['reg_date']));
							$row['total_amount'] = $c->toCurrency($row['total_amount']);
							return $row;
						},
						$customerMapper->listAll($id ? array('id = ?'=>$id) : null, $order, $limit, $offset, $search));
				} else {
					if ($id) {
						$result = $customerMapper->find($id);
						if ($result) {
							$data = $result->toArray();
						}
					} else {
						$result = $customerMapper->fetchAll();
						if ($result){
							$data = array_map(function($model){ return $model->toArray(); }, $result);
						}
					}
				}
	            break;
	        case 'post':
	            break;
			case 'put':
				break;
			case 'delete':
				$rawBody = Zend_Json::decode($this->_request->getRawBody());
				if (isset($rawBody['ids'])){
					$ids = filter_var_array($rawBody['ids'], FILTER_SANITIZE_NUMBER_INT);
					if (!empty($ids)){
						$customers = $customerMapper->fetchAll(array('id IN (?)' => $ids, 'role_id <> ?' => Tools_Security_Acl::ROLE_SUPERADMIN));
						if ( !empty($customers) ) {
							foreach ($customers as $user) {
								$data[$user->getId()] = (bool)Application_Model_Mappers_UserMapper::getInstance()->delete($user);
							}
						} else {
							$data = array(
								'error'		=> true,
								'code'		=> 404,
								'message'	=> 'Requested users not found'
							);
						}
					}
				}
				break;
	    }
		return $data;
	}

	public function profileAction(){
		$customer = Tools_ShoppingCart::getInstance()->getCustomer();

		if ($customer->getId() === null){
			$this->_redirector->gotoUrl($this->_websiteUrl);
		}

		if ($customer->getRoleId() === Tools_Security_Acl::ROLE_ADMIN || $customer->getRoleId() === Tools_Security_Acl::ROLE_SUPERADMIN){
			$id = isset($this->_requestedParams['id']) ? filter_var($this->_requestedParams['id'], FILTER_VALIDATE_INT) : false;
		}
		if (!isset($id) || $id === false){
			$id = $customer->getId();
		}

		$customer = Models_Mapper_CustomerMapper::getInstance()->find($id);
		if ($customer) {
			$this->_view->customer  = $customer;
			$orders = Models_Mapper_CartSessionMapper::getInstance()->fetchAll(array('user_id = ?' => $customer->getId()));
			$this->_view->stats = array(
				'total'     => sizeof($orders),
				'new' => sizeof(array_filter($orders, function($order){
					return ( !$order->getStatus() || ($order->getStatus() === Models_Model_CartSession::CART_STATUS_NEW));
					})
				),
				'completed' => sizeof(array_filter($orders, function($order){ return $order->getStatus() === Models_Model_CartSession::CART_STATUS_COMPLETED; })),
				'pending'   => sizeof(array_filter($orders, function($order){ return $order->getStatus() === Models_Model_CartSession::CART_STATUS_PENDING; }))
			);
			$this->_view->orders = $orders;
		}

		$content = $this->_view->render('profile.phtml');

		if ($this->_request->isXmlHttpRequest()){
			echo $content;
		} else {
			$this->_layout->content = '<div id="profile">'.$content.'</div>';
			echo $this->_layout->render();
		}
	}

	public function orderAction(){
		$id = isset($this->_requestedParams['id']) ? filter_var($this->_requestedParams['id'], FILTER_VALIDATE_INT) : false;
		if ($id) {
			$order = Models_Mapper_CartSessionMapper::getInstance()->find($id);
			$customer = Tools_ShoppingCart::getInstance()->getInstance()->getCustomer();
			if (!$order) {
				throw new Exceptions_SeotoasterPluginException('Order not found');
			}
			if (!Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_ADMINPANEL)){
				if ((int)$order->getUserId() !== (int)$customer->getId()) {
					throw new Exceptions_SeotoasterPluginException('Not allowed action');
				}
			}

			if ($this->_request->isPost()) {
				$params = filter_var_array($this->_request->getPost(), FILTER_SANITIZE_STRING);

				$order->setOptions($params);
				$status = Models_Mapper_CartSessionMapper::getInstance()->save($order);

				$this->_responseHelper->response($status->toArray(),false);
			}
			$this->_view->order = $order;
			$this->_layout->content = $this->_view->render('order.phtml');
			echo $this->_layout->render();
		}
	}

	public function brandlogosAction(){
		$this->_layout->content = $this->_view->render('brandlogos.phtml');
		echo $this->_layout->render();
	}
}
