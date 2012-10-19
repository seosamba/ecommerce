<?php
/**
 * Ecommerce plugin for SEOTOASTER 2.0
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 * @see http://www.seotoaster.com
 * @todo remove deprecated before next release
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
	 * Resource descibes store management widgets and screens
	 */
	const RESOURCE_STORE_MANAGEMENT = 'storemanagement';

	/**
	 * Default cart plugin
	 */
	const DEFAULT_CART_PLUGIN = 'cart';

	/**
	 * Default cache id for checkout page
	 */
	const CHECKOUT_PAGE_CACHE_ID = 'cart_checkoutpage';

    /**
     * Option for the page options system.
     */
    const OPTION_CHECKOUT       = 'option_checkout';

	/**
	 * Option for the page options system
	 */
	const OPTION_THANKYOU       = 'option_storethankyou';

    const KEY_CHECKOUT_SIGNUP    = 'signup';
	const KEY_CHECKOUT_ADDRESS   = 'address';
	const KEY_CHECKOUT_SHIPPER   = 'shipper';
	const KEY_CHECKOUT_PICKUP    = 'pickup';

	const SHIPPING_FREESHIPPING = 'freeshipping';

	const SHIPPING_PICKUP       = 'pickup';
    
    const SHIPPING_MARKUP       = 'markup';
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
            //'product',
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

	/**
	 * @deprecated
	 */
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

	/**
	 * Method executed before controller launch
	 */
	public function beforeController(){
	    $cacheHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('cache');
	    if (null === ($checkoutPage = $cacheHelper->load(self::CHECKOUT_PAGE_CACHE_ID, self::CACHE_PREFIX))){
		    $checkoutPage = Tools_Misc::getCheckoutPage();
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
		if (!$acl->has(self::RESOURCE_STORE_MANAGEMENT)) {
			$acl->addResource(new Zend_Acl_Resource(self::RESOURCE_STORE_MANAGEMENT));
		}
        $acl->allow(self::ROLE_CUSTOMER, self::RESOURCE_CART);
	    $acl->deny(Tools_Security_Acl::ROLE_GUEST, self::RESOURCE_API);
	    $acl->deny(Tools_Security_Acl::ROLE_MEMBER, self::RESOURCE_API);
	    $acl->deny(self::ROLE_SALESPERSON);
	    $acl->allow(self::ROLE_SALESPERSON, self::RESOURCE_STORE_MANAGEMENT);
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
	 */
	protected function shippingAction() {
		if($this->_request->isPost()) {
			$shippingData = $this->_request->getParams();
			$this->_configMapper->save(array_map(function($param) {
				return (is_array($param)) ? serialize($param) : $param;
			}, $shippingData));
			$this->_jsonHelper->direct($shippingData);
		}
        $markupConfig = Models_Mapper_ShippingConfigMapper::getInstance()->find(self::SHIPPING_MARKUP);
        $markupForm = new Forms_Shipping_MarkupShipping();
        if(isset($markupConfig['config']) && !empty($markupConfig['config'])){
            $markupForm->populate($markupConfig['config']);
        }
        //$markupForm->getElement('price')->setValue($value);
      	$this->_view->config = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
		$this->_view->freeForm = new Forms_Shipping_FreeShipping();
        $this->_view->markupForm = $markupForm;

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
		$this->_jsonHelper->direct(Models_Mapper_ShippingConfigMapper::getInstance()->fetchAll());
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

	/**
	 * Method creates customer or returns existing one
	 * @static
	 * @param $data array Customer details
	 * @return Models_Model_Customer
	 */
	public static function processCustomer($data) {
		$session = Zend_Controller_Action_HelperBroker::getExistingHelper('session');

		$customer = Tools_ShoppingCart::getInstance()->getCustomer();
		if (!$customer->getId()){
			if (null === ($existingCustomer = Models_Mapper_CustomerMapper::getInstance()->findByEmail($data['email']))) {
				$customer->setRoleId(Shopping::ROLE_CUSTOMER)
					->setEmail($data['email'])
					->setFullName($data['firstname'] . ' ' . $data['lastname'])
					->setIpaddress($_SERVER['REMOTE_ADDR'])
					->setPassword(md5(uniqid('customer_' . time())));
				$newCustomerId = Models_Mapper_CustomerMapper::getInstance()->save($customer);
				if ($newCustomerId) {
					Tools_ShoppingCart::getInstance()->setCustomerId($newCustomerId)->save();
					$customer->setId($newCustomerId);
					$session->storeIsNewCustomer = true;
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
		$checkoutPage = Tools_Misc::getCheckoutPage();
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
			if (in_array($type, $this->_allowedApi) || Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)){
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
     */
	private function _stateslistRESTService() {
		return Tools_Geo::getState();
	}

	private function _statesRESTService() {
        $toPairs = $this->_request->getParam('pairs', false);
		$country = $this->_request->getParam('country');
		return Tools_Geo::getState($country?$country:null, $toPairs);
	}

	/**
	 * @deprecated
	 */
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

	/**
	 * @deprecated
	 */
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

	/**
	 * @deprecated
	 */
	private function _brandsRESTService() {
        $data = array();
        switch (strtolower($this->_request->getMethod())){
            default:
            case 'get':
		        $brandsList = Models_Mapper_Brand::getInstance()->fetchAll(null, array('name'));
                $pagesUrls = array(); //Application_Model_Mappers_PageMapper::getInstance()->fetchAllUrls();
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

	/**
	 * @deprecated
	 */
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

	/**
	 * @deprecated
	 */
	protected function _productRESTService(){
		$productMapper = Models_Mapper_ProductMapper::getInstance();
		$method        =  $this->_request->getMethod();
		$data          = array();
		$cacheHelper   = Zend_Controller_Action_HelperBroker::getStaticHelper('cache');
		switch ($method){
			case 'GET':
			    $id = array_filter(filter_var_array(explode(',', $this->_request->getParam('id')), FILTER_VALIDATE_INT));
				if (!empty($id)) {
					$product              = $productMapper->find($id);
					if ($product instanceof Models_Model_Product) {
						$data = $product->toArray();
					} elseif (is_array($product) && !empty($product)){
						$data = array_map(function($prod){
							return $prod->toArray();
						}, $product);
					}
				} else {
					$offset = isset($this->_requestedParams['offset']) ? $this->_requestedParams['offset'] : 0;
					$limit  = isset($this->_requestedParams['limit']) ? $this->_requestedParams['limit'] : self::PRODUCT_DEFAULT_LIMIT;
					$key    = filter_var($this->_request->getParam('key', null), FILTER_SANITIZE_STRING);
					$count  = filter_var($this->_request->getParam('count', false), FILTER_VALIDATE_BOOLEAN);

					$filter['tags'] = isset($this->_requestedParams['ftag']) ? $this->_requestedParams['ftag'] : null;
					$filter['brands']     = isset($this->_requestedParams['fbrand']) ? $this->_requestedParams['fbrand'] : null;
					$tagPart              = (is_array($filter['tags']) && !empty($filter['tags'])) ? implode('.', $filter['tags']) : 'alltags';
					$brandPart            = (is_array($filter['brands']) && !empty($filter['brands'])) ? implode('.', $filter['brands']) : 'allbrands';
					$cacheKey             = $method.'_product_'.md5($tagPart . $brandPart . $offset . $limit . $key . $count);
					if(($data = $cacheHelper->load($cacheKey, 'store_')) === null) {

						$products = $productMapper->logSelectResultLength($count)->fetchAll(null, array(), $offset, $limit, (bool)$key?$key:null,
							(is_array($filter['tags']) && !empty($filter['tags'])) ? $filter['tags'] : null,
							(is_array($filter['brands']) && !empty($filter['brands'])) ? $filter['brands']: null);

						$data = !is_null($products) ? array_map(function($prod){
							//cleanup unnecessary values
							if ($prod->getPage()){
								$prod->setPage(array(
	                                'id'         => $prod->getPage()->getId(),
	                                'url'        => $prod->getPage()->getUrl(),
	                                'templateId' => $prod->getPage()->getTemplateId()
	                            ));
							}
							return $prod->toArray();
						}, $products) : array();

						if ($count) {
							$data = array(
								'totalCount' => $productMapper->lastSelectResultLength(),
								'count'      => sizeof($data),
								'data'       => $data
							);
						}

						$cacheHelper->save($cacheKey, $data, 'store_', array('productlist'), Helpers_Action_Cache::CACHE_NORMAL);
					}
				}
				break;
			case 'POST':
				$srcData = Zend_Json_Decoder::decode($this->_request->getRawBody());
				$validator = new Zend_Validate_Db_NoRecordExists(array(
					'table' => 'shopping_product',
					'field' => 'sku'
				));

                if (!$validator->isValid($srcData['sku'])){
                    return array(
                        'error' => true,
                        'message' => $this->_translator->translate('You already have a product with this SKU')
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
				$id = array_filter(filter_var_array(explode(',', $this->_request->getParam('id')), FILTER_VALIDATE_INT));
				$srcData = json_decode($this->_request->getRawBody(), true);
				if (!empty($id) && !empty($srcData)){
					$products = $productMapper->find($id);
					!is_array($products) && $products = array($products);
					if (isset($srcData['id'])){
						unset($srcData['id']);
					}
				} elseif(!empty($srcData)) {
					$key    = filter_var($this->_request->getParam('key'), FILTER_SANITIZE_STRING);
					$tags   = filter_var_array($this->_request->getParam('ftag', array()), FILTER_SANITIZE_NUMBER_INT);
					$brands  = filter_var_array($this->_request->getParam('fbrand', array()), FILTER_SANITIZE_STRING);
					if (empty($key) && empty($tags) && empty($brands)){
						return array(
							'error'		=> true,
							'code'		=> 400,
							'message'	=> 'Bad request'
						);
					}

					$products = $productMapper->fetchAll(null, array(), null, null, $key, $tags, $brands);
				}

				if (!empty($products)){
					foreach ($products as $product) {
						$product->setOptions($srcData);
						if ($productMapper->save($product)){
							$data[] = $product->toArray();
						}
					}

					if (count($data) === 1){
						$data = array_shift($data);
					}
				}
				break;
			case 'DELETE':
				$ids = array_filter(filter_var_array(explode(',', $this->_request->getParam('id')), FILTER_VALIDATE_INT));

				if (!empty($ids)) {
					$products = $productMapper->find($ids);
				} else {
					$key    = filter_var($this->_request->getParam('key'), FILTER_SANITIZE_STRING);
					$tags   = filter_var_array($this->_request->getParam('ftag', array()), FILTER_SANITIZE_NUMBER_INT);
					$brands  = filter_var_array($this->_request->getParam('fbrand', array()), FILTER_SANITIZE_STRING);
					if (empty($key) && empty($tags) && empty($brands)){
						return array(
							'error'		=> true,
							'code'		=> 400,
							'message'	=> 'Bad request'
						);
					}

					$products = $productMapper->fetchAll(null, array(), null, null, $key, $tags, $brands);
				}

				if (isset($products) && !is_null($products)) {
					!is_array($products) && $products = array($products);
					$results = array();
					foreach ($products as $product){
						$results[$product->getId()] = $productMapper->delete($product);
                        unset($product);
					}
                    if (!empty($results)){
                        $data = in_array(false, $results) ? array(
                            'error' => true,
                            'code' => 409,
                            'message' => $results
                        ) : $results;
                    }
				} else {
					return array(
						'error'		=> true,
						'code'		=> 404,
						'message'	=> 'Requested product not found'
					);
				}

				break;
			default:
				return array(
					'error'		=> true,
					'code'		=> 400,
					'message'	=> 'Bad request'
				);
				break;
		}

        if (!$this->_request->isGet()){
            $cacheHelper->clean(null, null, array('productlist', 'productListWidget', 'productindex'));
	        Tools_FeedGenerator::getInstance()->generateProductFeed();
        }

		return $data;
	}

	/**
	 * @deprecated
	 */
	protected function _optionsRESTService(){
        $optionMapper = Models_Mapper_OptionMapper::getInstance();
        return $optionMapper->fetchAll(array('parentId = ?' => 0), null, false);
    }

	/**
	 * @deprecated
	 */
	protected function _templatesRESTService(){
		$templatesMapper = Application_Model_Mappers_TemplateMapper::getInstance();
		if ($this->_request->isGet()){
			$type = $this->_request->getParam('filter');
			if ($type){
				$data = $templatesMapper->findByType($type);
			} else {
				$data = $templatesMapper->fetchAll();
			}
			return array_map(function($template){
				return array_filter($template->toArray());
			}, $data);
		}
	}

	/**
     * Method renders product management screen
     * @var $pageMapper Application_Model_Mappers_PageMapper
     */
    protected function productAction(){
		if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)){

            $this->_view->generalConfig = $this->_configMapper->getConfigParams();

			$this->_view->templateList  = Application_Model_Mappers_TemplateMapper::getInstance()->findByType(Application_Model_Models_Template::TYPE_PRODUCT);
			$this->_view->brands        = Models_Mapper_Brand::getInstance()->fetchAll();

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

			if ($this->_request->has('id')){
				$id = filter_var($this->_request->getParam('id'), FILTER_VALIDATE_INT);
				if ($id){
					$this->_view->product = Models_Mapper_ProductMapper::getInstance()->find($id);
				}
			}

            $this->_layout->content = $this->_view->render('product.phtml');
            echo $this->_layout->render();
        }
	}

    public function searchindexAction(){
        $cacheHelper    = Zend_Controller_Action_HelperBroker::getStaticHelper('cache');

        if(($data = $cacheHelper->load('index', 'store_')) === null) {
            $data = Models_Mapper_ProductMapper::getInstance()->buildIndex();

            $cacheHelper->save('index', $data, 'store_', array('productindex'), Helpers_Action_Cache::CACHE_NORMAL);
        }

        echo json_encode($data);
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
			$parser = new Tools_Content_Parser($paymentZoneTmpl, Tools_Misc::getCheckoutPage()->toArray(), $parserOptions);
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
		//if (Tools_Security_Acl::isAllowed(__CLASS__.'-clients')){
		if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)){	
            $this->_view->noLayout = true;
			return $this->_view->render('clients.phtml');
		}
	}

	/**
	 * Generates product grid
	 * for admins only
	 * @return string Widget html content
	 */
	protected function _makeOptionProducts() {
		//if (Tools_Security_Acl::isAllowed(__CLASS__.'-clients')){
		if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)){	
            $this->_view->noLayout = true;
			$this->_view->brands = Models_Mapper_Brand::getInstance()->fetchAll();
			$this->_view->tags = Models_Mapper_Tag::getInstance()->fetchAll();
			return $this->_view->render('manage_products.phtml');
		}
	}

	/**
	 * @deprecated
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

		if ($customer->getRoleId() === Tools_Security_Acl::ROLE_ADMIN || $customer->getRoleId() === Tools_Security_Acl::ROLE_SUPERADMIN || $customer->getRoleId() === self::ROLE_SALESPERSON){
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

        $enabledInvoicePlugin = Application_Model_Mappers_PluginMapper::getInstance()->findByName('invoicetopdf');
        if($enabledInvoicePlugin != null){
            if($enabledInvoicePlugin->getStatus() == 'enabled'){
                $this->_view->invoicePlugin = 1;
            }
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

				if ($order->getShippingTrackingId() !== $params['shippingTrackingId']){
					$order->registerObserver(new Tools_Mail_Watchdog(array(
						'trigger' => Tools_StoreMailWatchdog::TRIGGER_SHIPPING_TRACKING_NUMBER
					)));
				}

				$order->setOptions($params);
				$status = Models_Mapper_CartSessionMapper::getInstance()->save($order);

				$order->notifyObservers();

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

	/**
	 * @deprecated
	 */
	protected function _statsRESTService(){
		$id = array_filter(filter_var_array(explode(',', $this->_request->getParam('id')), FILTER_VALIDATE_INT));
		if (is_array($id) && !empty($id)){
			return Models_Mapper_ProductMapper::getInstance()->fetchProductSalesCount($id);
		}
	}

	public function bundledshipperAction(){
		$name = filter_var($this->_request->getParam('shipper'), FILTER_SANITIZE_STRING);
		$bundledShippers = array(
			self::SHIPPING_FREESHIPPING,
			self::SHIPPING_PICKUP,
            self::SHIPPING_MARKUP
		);

		if (!in_array($name, $bundledShippers)){
			throw new Exceptions_SeotoasterException('Bad request');
		}

		if (Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_PLUGINS)){
			switch ($name){
				case self::SHIPPING_FREESHIPPING:
					$form = new Forms_Shipping_FreeShipping();
					break;
				case self::SHIPPING_PICKUP:
//					$form = new Forms_Shipping_Pickup();
					break;
                case self::SHIPPING_MARKUP:
					$form = new Forms_Shipping_MarkupShipping();
					break;
				default:
					break;
			}
			if ($this->_request->isPost()){
				if ($form->isValid($this->_request->getParams())){
					$config = array(
						'name' => $name,
						'config' => $form->getValues()
					);
					Models_Mapper_ShippingConfigMapper::getInstance()->save($config);
				}
			} else {
				$pluginConfig = Models_Mapper_ShippingConfigMapper::getInstance()->find($name);
				if (isset($pluginConfig['config']) && !empty($pluginConfig['config'])){
					$form->populate($pluginConfig['config']);
				}
			}
			$form->setAction(trim($this->_websiteUrl,'/').$this->_view->url(array('run'=>'config', 'name' => 'usps'),'pluginroute'));
			echo $form;
		}
	}

	/**
	 * Action redirects customer to post purchase 'thank you' page if exists
	 * If not redirects to index page
	 */
	public function thankyouAction(){
		$cartId = Tools_ShoppingCart::getInstance()->getCartId();

		if ($cartId){
			Tools_ShoppingCart::getInstance()->clean();
			$this->_sessionHelper->storeCartSessionKey = $cartId;
			if ($this->_sessionHelper->storeIsNewCustomer){
				$cartSession = Models_Mapper_CartSessionMapper::getInstance()->find($cartId);
                $customerMapper = Models_Mapper_CustomerMapper::getInstance();
                $userMapper = Application_Model_Mappers_UserMapper::getInstance();
                $userData = $userMapper->find($cartSession->getUserId());
                $newCustomerPassword = uniqid('customer_' . time());
                $userData->setPassword($newCustomerPassword);
                $newCustomerId = $userMapper->save($userData);
                $customer = $customerMapper->find($cartSession->getUserId());
                $customer->setPassword($newCustomerPassword);
                //$customer = Tools_ShoppingCart::getInstance()->getCustomer();
				$customer->registerObserver(new Tools_Mail_Watchdog(array(
					'trigger' => Tools_StoreMailWatchdog::TRIGGER_NEW_CUSTOMER
				)));
				$customer->notifyObservers();
			}
			$cartSession = Models_Mapper_CartSessionMapper::getInstance()->find($cartId);
			$cartSession->registerObserver(new Tools_Mail_Watchdog(array(
				'trigger' => Tools_StoreMailWatchdog::TRIGGER_NEW_ORDER
			)));
			$cartSession->notifyObservers();
		}


		$thankyouPage = Application_Model_Mappers_PageMapper::getInstance()->fetchByOption(self::OPTION_THANKYOU, true);
		if (!$thankyouPage){
			$this->_redirector->gotoUrl($this->_websiteHelper->getDefaultPage());
		}
		$this->_redirector->gotoUrl($thankyouPage->getUrl());
	}
}
