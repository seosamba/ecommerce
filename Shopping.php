<?php
/**
 * Ecommerce plugin for SEOTOASTER 2.0
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 * @see    http://www.seotoaster.com
 */
class Shopping extends Tools_Plugins_Abstract {
	const PRODUCT_CATEGORY_NAME = 'Product Pages';
	const PRODUCT_CATEGORY_URL = 'product-pages.html';
	const PRODUCT_DEFAULT_LIMIT = 30;
	const PRODUCT_PAGE_TYPE = 2;

	const BRAND_LOGOS_FOLDER  = 'brands';
    const PICKUP_LOGOS_FOLDER = 'pickup-logos';
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
	const RESOURCE_API = 'api';

    /**
     * quote gateway
     */
    const GATEWAY_QUOTE = 'Quote';

	/**
	 * Resource describes store management widgets and screens
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
	 * Option for the client page
	 */
	const OPTION_STORE_CLIENT_LOGIN = 'option_storeclientlogin';

	/**
	 * Option for the page options system.
	 */
	const OPTION_CHECKOUT = 'option_checkout';

	const OPTION_STORE_SHIPPING_TERMS = 'option_storeshippingterms';

	/**
	 * Option for the page options system
	 */
	const OPTION_THANKYOU = 'option_storethankyou';

	const KEY_CHECKOUT_SIGNUP = 'signup';
	const KEY_CHECKOUT_ADDRESS = 'address';
	const KEY_CHECKOUT_SHIPPER = 'shipper';
	const KEY_CHECKOUT_PICKUP = 'pickup';

	const SHIPPING_FREESHIPPING = 'freeshipping';

	const SHIPPING_PICKUP = 'pickup';

	const SHIPPING_MARKUP = 'markup';

	const SHIPPING_FLATRATE = 'flatrateshipping';

	const SHIPPING_TOC_STATUS = 'checkoutShippingTocRequire';

	const SHIPPING_TOC_LABEL = 'checkoutShippingTocLabel';

    const SHIPPING_ERROR_MESSAGE = 'checkoutShippingErrorMessage';

    const SHIPPING_SUCCESS_MESSAGE = 'checkoutShippingSuccessMessage';

    const SHIPPING_TAX_RATE     = 'shippingTaxRate';

    const COUPON_DISCOUNT_TAX_RATE  = 'couponDiscountTaxRate';


    const QUANTITY_PICKUP_LOCATION_ON_SCREEN = 6;

    const AMOUNT_TYPE_UP_TO = 'up to';

    const AMOUNT_TYPE_OVER = 'over';

    const AMOUNT_TYPE_EACH_OVER = 'eachover';

    const COMPARE_BY_AMOUNT = 'amount';

    const COMPARE_BY_WEIGHT = 'weight';

    const ORDER_CONFIG  = 'orderconfig';

    const ORDER_EXPORT_CONFIG = 'order_export_config';

    const ORDER_IMPORT_CONFIG = 'order_import_config';

    /**
     * shipping restriction key
     */
    const SHIPPING_RESTRICTION_ZONES = 'shippingzones';

    /**
	 * Cache prefix for use in shopping system
	 */
	const CACHE_PREFIX = 'store_';

    const SHOPPING_SECURE_TOKEN = 'ShoppingToken';

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
			'clients'
		),
		Tools_Security_Acl::ROLE_ADMIN      => array(),
		Tools_Security_Acl::ROLE_GUEST      => array()
	);

	public static $emailTriggers = array(
		'Tools_StoreMailWatchdog'
	);

	public function  __construct($options, $seotoasterData) {
		parent::__construct($options, $seotoasterData);

		$this->_layout = new Zend_Layout();
        $this->_layout->setLayoutPath(Zend_Layout::getMvcInstance()->getLayoutPath());

		if ($viewScriptPath = Zend_Layout::getMvcInstance()->getView()->getScriptPaths()) {
			$this->_view->setScriptPath($viewScriptPath);
		}
		$this->_view->addScriptPath(__DIR__ . '/system/views/');

		$this->_jsonHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('json');
		$this->_websiteConfig = Zend_Registry::get('website');
		$this->_configMapper = Models_Mapper_ShoppingConfig::getInstance();
	}

	/**
	 * Method executed before controller launch
	 */
	public function beforeController() {
		$cacheHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('cache');
		if (null === ($checkoutPage = $cacheHelper->load(self::CHECKOUT_PAGE_CACHE_ID, self::CACHE_PREFIX))) {
			$checkoutPage = Tools_Misc::getCheckoutPage();
			$cacheHelper->save(self::CHECKOUT_PAGE_CACHE_ID, $checkoutPage, self::CACHE_PREFIX);
		}
		if (!$this->_request->isSecure()
				&& $checkoutPage instanceof Application_Model_Models_Page
				&& $checkoutPage->getUrl() === $this->_request->getParam('page')
				&& Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('forceSSLCheckout')
		) {
			$this->_redirector->gotoUrlAndExit(Zend_Controller_Request_Http::SCHEME_HTTPS . '://' . $this->_websiteConfig['url'] . $checkoutPage->getUrl());
		}

		$this->_addVersionToAdminPanel();

		if (!Zend_Registry::isRegistered('Zend_Currency')) {
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
		if (!$acl->hasRole(self::ROLE_CUSTOMER)) {
			$acl->addRole(new Zend_Acl_Role(self::ROLE_CUSTOMER), Tools_Security_Acl::ROLE_GUEST);
		}
		if (!$acl->hasRole(self::ROLE_SALESPERSON)) {
			$acl->addRole(new Zend_Acl_Role(self::ROLE_SALESPERSON), Tools_Security_Acl::ROLE_MEMBER);
		}
		if (!$acl->has(self::RESOURCE_CART)) {
			$acl->addResource(new Zend_Acl_Resource(self::RESOURCE_CART));
		}
		if (!$acl->has(self::RESOURCE_API)) {
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
		$acl->allow(self::ROLE_SALESPERSON, Tools_Security_Acl::RESOURCE_MEDIA);
		$acl->allow(self::ROLE_SALESPERSON, Tools_Security_Acl::RESOURCE_PAGES);
		$acl->allow(self::ROLE_SALESPERSON, Tools_Security_Acl::RESOURCE_PLUGINS_MENU);
		$acl->allow(self::ROLE_SALESPERSON, Tools_Security_Acl::RESOURCE_PLUGINS);
		$acl->allow(self::ROLE_SALESPERSON, Tools_Security_Acl::RESOURCE_THEMES);
        $acl->allow(self::ROLE_SALESPERSON, Tools_Security_Acl::RESOURCE_CONFIG);
		Zend_Registry::set('acl', $acl);
	}

	public function run($requestedParams = array()) {
		$dispatchersResult = parent::run($requestedParams);
		if ($dispatchersResult) {
			return $this->_getOption($dispatchersResult);
		}
	}

	public static function getTabContent() {
		$translator = Zend_Registry::get('Zend_Translate');
		$view = new Zend_View(array(
			'scriptPath' => dirname(__FILE__) . '/system/views'
		));
		$websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
		$configHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('config');
		$view->websiteUrl = $websiteHelper->getUrl();
		$view->mediaServersAllowed = false;
		if ($configHelper->getConfig('mediaServers')) {
			$view->websiteData = Zend_Registry::get('website');
			$view->domain = str_replace('www.', '', $view->websiteData['url']);
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
	protected function configAction() {
		$config = $this->_configMapper->getConfigParams();

		$form = new Forms_Config();
		if ($this->_request->isPost()) {
            $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                exit;
            }
            if ($form->isValid($this->_requestedParams)) {
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
		$this->_view->configTabs = Tools_Plugins_Tools::getEcommerceConfigTabs();
        $this->_view->helpSection = Tools_Misc::SECTION_STORE_CONFIG;
        $this->_layout->content = $this->_view->render('config.phtml');
		echo $this->_layout->render();
	}

	/**
	 * Shipping configuration action
	 */
	protected function shippingAction() {
		if ($this->_request->isPost()) {
			$shippingData = $this->_request->getParams();
			$this->_configMapper->save(array_map(function ($param) {
				return (is_array($param)) ? serialize($param) : $param;
			}, $shippingData));
			$this->_jsonHelper->direct($shippingData);
		}
		$this->_view->shoppingConfig = $this->_configMapper->getConfigParams();

        $shippingConfigMapper = Models_Mapper_ShippingConfigMapper::getInstance();
		$markupConfig = $shippingConfigMapper->find(self::SHIPPING_MARKUP);
		$markupForm = new Forms_Shipping_MarkupShipping();
		if (isset($markupConfig['config']) && !empty($markupConfig['config'])) {
			$markupForm->populate($markupConfig['config']);
		}
        $orderConfig =  $shippingConfigMapper->find(self::ORDER_CONFIG);
        $orderConfigForm = new Forms_Shipping_OrderConfig();
        if(isset($orderConfig['config'])){
            $orderConfigForm->populate($orderConfig['config']);
        }
		$freeShippingForm = new Forms_Shipping_FreeShipping();
		$freeShippingConfig = $shippingConfigMapper->find(self::SHIPPING_FREESHIPPING);
		if (isset($freeShippingConfig['config']) && !empty($freeShippingConfig['config'])) {
			$freeShippingForm->populate($freeShippingConfig['config']);
		}

        $shippingRestriction = new Forms_Shipping_ShippingRestriction();
        $shippingRestrictionConfig = $shippingConfigMapper->find(self::SHIPPING_RESTRICTION_ZONES);
        if (!empty($shippingRestrictionConfig['config'])) {
            $shippingRestriction->populate($shippingRestrictionConfig['config']);
            $this->_view->shippingRestrictionConfig = $shippingRestrictionConfig;
        }

        $pickupShippingForm = new Forms_Shipping_PickupShipping();
        $pickupShippingConfig = $shippingConfigMapper->find(self::SHIPPING_PICKUP);
        $defaultPickup = true;
        if (isset($pickupShippingConfig['config']) && !empty($pickupShippingConfig['config'])) {
            $defaultPickup = false;
            if($pickupShippingConfig['config']['defaultPickupConfig'] === '1'){
                $defaultPickup = true;
            }
            $pickupShippingForm->populate($pickupShippingConfig['config']);
        }
        if($defaultPickup){
            $pickupShippingForm->getElement('defaultPickupConfig')->setValue(1);
        }
        $pickupLocationsCategories = Store_Mapper_PickupLocationCategoryMapper::getInstance()->fetchAll();
        $this->_view->locationCategories = $pickupLocationsCategories;
        $this->_view->defaultPickup = $defaultPickup;
		$this->_view->config = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
		$this->_view->freeForm = $freeShippingForm;
		$this->_view->markupForm = $markupForm;
        $this->_view->pickupForm = $pickupShippingForm;
        $this->_view->shippingRestriction = $shippingRestriction;
        $pickupLocationMapper = Store_Mapper_PickupLocationConfigMapper::getInstance();
        $this->_view->pickupLocationConfigZones = $pickupLocationMapper->getLocationZones();
        $this->_view->locationZonesInfo = $pickupLocationMapper->getLocationZonesInfo(self::QUANTITY_PICKUP_LOCATION_ON_SCREEN);
        $pickupLocationConfig = $pickupLocationMapper->getConfig();
        if(empty($pickupLocationConfig)) {
            $pickupLocationConfig = array('1'=>array('id'=>1, 'amount_type_limit'=>Shopping::AMOUNT_TYPE_UP_TO, 'amount_limit'=>0));
        }
        $this->_view->pickupLocationConf = $pickupLocationConfig;
        $this->_view->orderConfigForm = $orderConfigForm;
		$this->_view->shippingPlugins = array_filter(Tools_Plugins_Tools::getEnabledPlugins(), function ($plugin) {
			$reflection = new Zend_Reflection_Class(ucfirst($plugin->getName()));
			return $reflection->implementsInterface('Interfaces_Shipping');
		});
        $this->_view->helpSection = Tools_Misc::SECTION_STORE_SHIPPINGCONFIG;
        $this->_layout->content = $this->_view->render('shipping.phtml');
		echo $this->_layout->render();
	}

	protected function shippingconfigAction() {
		if (!Tools_Security_Acl::isAllowed(self::RESOURCE_API)) {
			$this->_response->setHttpResponseCode(403)->sendResponse();
		}
		$this->_jsonHelper->direct(Models_Mapper_ShippingConfigMapper::getInstance()->fetchAll());
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
		if (!$customer->getId()) {
			if (null === ($existingCustomer = Models_Mapper_CustomerMapper::getInstance()->findByEmail($data['email']))) {
                $fullname = isset($data['firstname']) ? $data['firstname'] : '';
                $fullname .= isset($data['lastname']) ? ' ' . $data['lastname'] : '';
                $mobilePhone = isset($data['mobile']) ? $data['mobile'] : '';
				$customer->setRoleId(Shopping::ROLE_CUSTOMER)
						->setEmail($data['email'])
						->setFullName($fullname)
						->setIpaddress($_SERVER['REMOTE_ADDR'])
                        ->setMobilePhone($mobilePhone)
						->setPassword(md5(uniqid('customer_' . time())));
				$newCustomerId = Models_Mapper_CustomerMapper::getInstance()->save($customer);
				if ($newCustomerId) {
//					Tools_ShoppingCart::getInstance()->setCustomerId($newCustomerId)->save();
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
		if (!$checkoutPage instanceof Application_Model_Models_Page) {
			throw new Exceptions_SeotoasterPluginException('Error rendering cart. Please select a checkout page');
		}

		$this->_redirector->gotoUrl($this->_websiteUrl . $checkoutPage->getUrl());
	}

	protected function setConfigAction() {
		$status = false;
		if ($this->_request->isPost()) {
            $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                exit;
            }
            $configMapper = Models_Mapper_ShoppingConfig::getInstance();
			$configParams = $this->_request->getParam('config');
			if ($configParams && is_array($configParams) && !empty ($configParams)) {
				$status = $configMapper->save($configParams);
			}
		}
		$this->_jsonHelper->direct(array('done' => $status));
	}

	public function setSettingsAction() {
		$status = false;
		if ($this->_request->isPost()) {
			$setSettingsMapper = Models_Mapper_ProductSetSettingsMapper::getInstance();
			$settingsParams = $this->_request->getParam('settings');
			if ($settingsParams && is_array($settingsParams) && !empty ($settingsParams)) {
				$status = $setSettingsMapper->save($settingsParams);
			}
		}
		$this->_jsonHelper->direct(array('done' => $status));
	}

	/**
	 * Method renders zones screen
	 * @return html|json
	 */
	protected function zonesAction() {
		$zonesMapper = Models_Mapper_Zone::getInstance();
		$this->_view->zones = array_map(function ($zone) {
			return $zone->toArray();
		}, $zonesMapper->fetchAll());
		$this->_view->states = Tools_Geo::getState();
		$this->_view->countries = Tools_Geo::getCountries();
        $this->_view->helpSection = Tools_Misc::SECTION_STORE_MANAGEZONES;
        $this->_layout->content = $this->_view->render('zones.phtml');
		echo $this->_layout->render();
	}

	/**
	 * Method renders tax configuration screen and handling tax saving
	 * @return html
	 */
	protected function taxesAction() {
		$this->_view->priceIncTax = $this->_configMapper->getConfigParam('showPriceIncTax');
		$this->_view->rules = array_map(function ($rule) {
			return $rule->toArray();
		}, Models_Mapper_Tax::getInstance()->fetchAll());
		$this->_view->zones = array_map(function ($zone) {
			return $zone->toArray();
		}, Models_Mapper_Zone::getInstance()->fetchAll());
        $this->_view->helpSection = Tools_Misc::SECTION_STORE_TAXES;
        $this->_layout->content = $this->_view->render('taxes.phtml');
		echo $this->_layout->render();
	}

	/**
	 * Method renders product management screen
	 * @var $pageMapper Application_Model_Mappers_PageMapper
	 */
	protected function productAction() {
		if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)) {

			$this->_view->generalConfig = $this->_configMapper->getConfigParams();

			$this->_view->templateList = Application_Model_Mappers_TemplateMapper::getInstance()->findByType(Application_Model_Models_Template::TYPE_PRODUCT);
			$this->_view->brands = Models_Mapper_Brand::getInstance()->fetchAll();

			$listFolders = Tools_Filesystem_Tools::scanDirectoryForDirs($this->_websiteConfig['path'] . $this->_websiteConfig['media']);
			if (!empty ($listFolders)) {
				$listFolders = array('select folder') + array_combine($listFolders, $listFolders);
			}
			$this->_view->imageDirList = $listFolders;

			$this->_view->plugins = array();
			foreach (Tools_Plugins_Tools::getPluginsByTags(array('ecommerce')) as $plugin) {
				if ($plugin->getTags() && in_array('merchandising', $plugin->getTags())) {
					array_push($this->_view->plugins, $plugin->getName());
				}
			}

			if ($this->_request->has('id')) {
				$id = filter_var($this->_request->getParam('id'), FILTER_VALIDATE_INT);
				if ($id) {
					$this->_view->product = Models_Mapper_ProductMapper::getInstance()->find($id);
				}
			}

			$this->_view->websiteConfig = $this->_websiteConfig;

            $this->_view->helpSection = Tools_Misc::SECTION_STORE_ADDEDITPRODUCT;
            $this->_layout->content = $this->_view->render('product.phtml');
			echo $this->_layout->render();
		}
	}

	public function searchindexAction() {
		$cacheHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('cache');

		if (($data = $cacheHelper->load('index', 'store_')) === null) {
			$data = Models_Mapper_ProductMapper::getInstance()->buildIndex();

			$cacheHelper->save('index', $data, 'store_', array('productindex'), Helpers_Action_Cache::CACHE_NORMAL);
		}

		echo json_encode($data);
	}

	protected function _getConfig() {
		return array_map(function ($param) {
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
		if (!$this->_request->isPost()) {
			throw new Exceptions_SeotoasterPluginException('Direct access not allowed');
		}
		$content = '';
		$nextPage = filter_var($this->_request->getParam('nextpage'), FILTER_SANITIZE_NUMBER_INT);
        if (is_numeric($this->_request->getParam('limit'))) {
            $limit = filter_var($this->_request->getParam('limit'), FILTER_SANITIZE_NUMBER_INT);
        } else {
            $limit = Widgets_Productlist_Productlist::DEFAULT_LIMIT;
        }

		$order = $this->_request->getParam('order');
		$tags = $this->_request->getParam('tags');
		$brands = $this->_request->getParam('brands');
        $attributes = $this->_request->getParam('attributes');
        $price = $this->_request->getParam('price');

		$offset = intval($nextPage) * $limit;
		$products = Models_Mapper_ProductMapper::getInstance()->fetchAll("enabled='1'", $order, $offset, $limit, null, $tags, $brands, false, false, $attributes, $price);
		if (!empty($products)) {
			$template = $this->_request->getParam('template');
			$widget = Tools_Factory_WidgetFactory::createWidget('productlist', array($template, $offset + $limit, md5(filter_var($this->_request->getParam('pageId'), FILTER_SANITIZE_NUMBER_INT))));
			$content = $widget->setProducts($products)->setCleanListOnly(true)->render();
			unset($widget);
		}
		if (null !== ($pageId = filter_var($this->_request->getParam('pageId'), FILTER_SANITIZE_NUMBER_INT))) {
			$page = Application_Model_Mappers_PageMapper::getInstance()->find($pageId);
			if ($page instanceof Application_Model_Models_Page && !empty($content)) {
				$content = $this->_renderViaParser($content, $page);
			}
		}
		echo $content;
	}

	private function _renderViaParser($content, Application_Model_Models_Page $page) {
		$themeData = Zend_Registry::get('theme');
		$extConfig = Zend_Registry::get('extConfig');
		$parserOptions = array(
			'websiteUrl'   => $this->_websiteHelper->getUrl(),
			'websitePath'  => $this->_websiteHelper->getPath(),
			'currentTheme' => $extConfig['currentTheme'],
			'themePath'    => $themeData['path'],
		);
		$parser = new Tools_Content_Parser($content, $page->toArray(), $parserOptions);
		return $parser->parse();
	}

	protected function _getOption($option) {
		$config = $this->_configMapper->getConfigParams();
		if (isset($config[$option])) {
			if ($option == 'country') {
				$countries = Tools_Geo::getCountries(true);
				$option = $countries[$config[$option]];
			} else {
				$option = $config[$option];
			}
		} else {
			if ($option == 'state') {
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
	 * Generates a list of clients - only visible to admin
	 *
     * @return string Html content
	 */
	protected function _makeOptionClients() {
		//if (Tools_Security_Acl::isAllowed(__CLASS__.'-clients')){
		if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)) {
			$this->_view->noLayout = true;
			$allGroups = Store_Mapper_GroupMapper::getInstance()->fetchAll();
            $this->_view->allGroups = $allGroups;
            $attributes = Application_Model_Mappers_UserMapper::getInstance();
            $query = $attributes->getDbTable()->getAdapter()->select()->distinct()->from('user_attributes', array('attribute'))->where('attribute LIKE ?', 'customer_%');
            $customerAttributes = $attributes->getDbTable()->getAdapter()->fetchCol($query);
            foreach ($customerAttributes as $key => $attrName) {
                $customerAttributes[$key] = preg_replace('`customer_`', '', $attrName);
            }
            $this->_view->customerAttributes = $customerAttributes;
            $this->_view->superAdmin = Tools_ShoppingCart::getInstance()->getCustomer()->getRoleId() === Tools_Security_Acl::ROLE_SUPERADMIN;
			return $this->_view->render('clients.phtml');
		}
	}

	/**
	 * Generates product grid for admins only
     *
	 * @return string Widget html content
	 */
	protected function _makeOptionProducts() {
		if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)) {
			$this->_view->brands = Models_Mapper_Brand::getInstance()->fetchAll();
			$this->_view->tags = Models_Mapper_Tag::getInstance()->fetchAll();
			$this->_view->currency = Zend_Registry::isRegistered('Zend_Currency') ? Zend_Registry::get('Zend_Currency') : new Zend_Currency();
			return $this->_view->render('manage_products.phtml');
		}
	}

	public function profileAction() {
		$customer = Tools_ShoppingCart::getInstance()->getCustomer();

		if ($customer->getId() === null) {
			$this->_redirector->gotoUrl($this->_websiteUrl);
		}

		if ($customer->getRoleId() === Tools_Security_Acl::ROLE_ADMIN || $customer->getRoleId() === Tools_Security_Acl::ROLE_SUPERADMIN || $customer->getRoleId() === self::ROLE_SALESPERSON) {
			$id = isset($this->_requestedParams['id']) ? filter_var($this->_requestedParams['id'], FILTER_VALIDATE_INT) : false;
		}
		if (!isset($id) || $id === false) {
			$id = $customer->getId();
		}

		$customer = Models_Mapper_CustomerMapper::getInstance()->find($id);
        $customerAddress = Models_Mapper_CustomerMapper::getInstance()->getUserAddressOrdersByUserId($id);
        if($customerAddress) {
            $this->_view->customerAddress = $customerAddress;
        }
		if ($customer) {
			$this->_view->customer = $customer;
			$orders = Models_Mapper_CartSessionMapper::getInstance()->fetchOrders($customer->getId());
			$this->_view->stats = array(
				'total'     => sizeof($orders),
				'new'       => sizeof(array_filter($orders, function ($order) {
						return (!$order->getStatus() || ($order->getStatus() === Models_Model_CartSession::CART_STATUS_NEW));
					})
				),
				'completed' => sizeof(array_filter($orders, function ($order) {
					return $order->getStatus() === Models_Model_CartSession::CART_STATUS_COMPLETED && !$order->getRecurringId();
				})),
				'pending'   => sizeof(array_filter($orders, function ($order) {
					return ($order->getStatus() === Models_Model_CartSession::CART_STATUS_PENDING && $order->getGateway() !== Shopping::GATEWAY_QUOTE);
				})),
				'shipped'   => sizeof(array_filter($orders, function ($order) {
					return $order->getStatus() === Models_Model_CartSession::CART_STATUS_SHIPPED;
				})),
				'delivered' => sizeof(array_filter($orders, function ($order) {
					return $order->getStatus() === Models_Model_CartSession::CART_STATUS_DELIVERED;
				})),
                'recurring_orders' => sizeof(array_filter($orders, function ($order) {
                    $recurringId = $order->getRecurringId();
                    if (!empty($recurringId)) {
                        return $recurringId;
                    }
                }))
			);
			$this->_view->orders = $orders;
		}

		$enabledInvoicePlugin = Application_Model_Mappers_PluginMapper::getInstance()->findByName('invoicetopdf');
		if ($enabledInvoicePlugin != null) {
			if ($enabledInvoicePlugin->getStatus() == 'enabled') {
				$this->_view->invoicePlugin = 1;
			}
		}

		$content = $this->_view->render('profile.phtml');

		if ($this->_request->isXmlHttpRequest()) {
			echo $content;
		} else {
			$this->_layout->content = '<div id="profile" class="toaster-widget bg-content">' . $content . '</div>';
			echo $this->_layout->render();
		}
	}

	public function orderAction() {
		$id = isset($this->_requestedParams['id']) ? filter_var($this->_requestedParams['id'], FILTER_VALIDATE_INT) : false;
		if ($id) {
			$order = Models_Mapper_CartSessionMapper::getInstance()->find($id);
			$customer = Tools_ShoppingCart::getInstance()->getInstance()->getCustomer();
			if (!$order) {
				throw new Exceptions_SeotoasterPluginException('Order not found');
			}

			if (!Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT)) {
				$orderUserId = $order->getUserId();
				if (empty($orderUserId) || (int) $orderUserId !== (int)$customer->getId()) {
					throw new Exceptions_SeotoasterPluginException('Not allowed action');
				}
			}

			if ($this->_request->isPost()) {
				$order->registerObserver(new Tools_InventoryObserver($order->getStatus()));

				$params = filter_var_array($this->_request->getPost(), FILTER_SANITIZE_STRING);

				if (isset($params['shippingTrackingId']) && $order->getShippingTrackingId() !== $params['shippingTrackingId']) {
					$order->registerObserver(new Tools_Mail_Watchdog(array(
						'trigger' => Tools_StoreMailWatchdog::TRIGGER_SHIPPING_TRACKING_NUMBER
					)));
					$params['status'] = Models_Model_CartSession::CART_STATUS_SHIPPED;
				}

				$order->setOptions($params);
				$status = Models_Mapper_CartSessionMapper::getInstance()->save($order);

				$this->_responseHelper->response($status->toArray(), false);
			}

            $defaultPickup = true;
            $pickupLocationConfigMapper = Store_Mapper_PickupLocationConfigMapper::getInstance();
            $pickupLocationData = $pickupLocationConfigMapper->getCartPickupLocationByCartId($id);
            if (!empty($pickupLocationData)) {
                $defaultPickup = false;
                $this->_view->pickupLocationData = $pickupLocationData;
            }
            $this->_view->defaultPickup = $defaultPickup;

			$this->_view->order = $order;
            $this->_view->showPriceIncTax = $this->_configMapper->getConfigParam('showPriceIncTax');
            $this->_view->weightSign = $this->_configMapper->getConfigParam('weightUnit');
			$this->_layout->content = $this->_view->render('order.phtml');
			echo $this->_layout->render();
		}
	}

	public function brandlogosAction() {
		if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)) {
            $this->_view->helpSection = Tools_Misc::SECTION_STORE_BRANDLOGOS;
            $this->_layout->content = $this->_view->render('brandlogos.phtml');
			echo $this->_layout->render();
		}
	}

	public function bundledshipperAction() {
		$name = filter_var($this->_request->getParam('shipper'), FILTER_SANITIZE_STRING);
		$bundledShippers = array(
			self::SHIPPING_FREESHIPPING,
			self::SHIPPING_PICKUP,
			self::SHIPPING_MARKUP,
            self::ORDER_CONFIG,
            self::SHIPPING_RESTRICTION_ZONES
		);

		if (!in_array($name, $bundledShippers)) {
			throw new Exceptions_SeotoasterException('Bad request');
		}

		if (Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_PLUGINS)) {
			switch ($name) {
				case self::SHIPPING_FREESHIPPING:
					$form = new Forms_Shipping_FreeShipping();
					break;
				case self::SHIPPING_PICKUP:
					$form = new Forms_Shipping_PickupShipping();
					break;
				case self::SHIPPING_MARKUP:
					$form = new Forms_Shipping_MarkupShipping();
					break;
                case self::ORDER_CONFIG:
                    $form = new Forms_Shipping_OrderConfig();
                    break;
                case self::SHIPPING_RESTRICTION_ZONES:
                    $form = new Forms_Shipping_ShippingRestriction();
                    break;
				default:
					break;
			}
			if ($this->_request->isPost()) {
                if ($form->isValid($this->_request->getParams())) {
                    if ($name === self::SHIPPING_PICKUP) {
                        $pickupLocationConfig = $this->_request->getParams();
                        $config = array(
                            'name' => $name,
                            'config' => array(
                                'title' => $pickupLocationConfig['title'],
                                'units' => $pickupLocationConfig['units'],
                                'defaultPickupConfig' => $pickupLocationConfig['defaultPickupConfig'],
                                'searchEnabled' => $pickupLocationConfig['searchEnabled']
                            )
                        );
                        if (isset($pickupLocationConfig['configData']) && !empty($pickupLocationConfig['configData'])) {
                            $pickupLocationsConfigMapper = Store_Mapper_PickupLocationConfigMapper::getInstance();
                            $pickupLocationsConfigModel = new Store_Model_PickupLocationConfig();
                            foreach ($pickupLocationConfig['configData'] as $location) {
                                if ($location['amountLimit'] === '0') {
                                    $pickupLocationsConfigMapper->deleteConfig($location['configRowId']);
                                } else {
                                    $pickupLocationsConfigModel->setId($location['configRowId']);
                                    $pickupLocationsConfigModel->setAmountLimit($location['amountLimit']);
                                    $pickupLocationsConfigModel->setAmountTypeLimit($location['amountType']);
                                    $pickupLocationsConfigModel->setLocationZones($location['zoneWithAmount']);
                                    $pickupLocationsConfigMapper->save($pickupLocationsConfigModel);
                                }
                            }
                        }
                    } elseif($name === self::SHIPPING_RESTRICTION_ZONES){
                        $data = $this->_request->getParams();
                        $config = array(
                            'name' => $name,
                            'config' => array(
                                'restrictDestination' => $data['restrictDestination'],
                                'restrictionMessage' => $data['restrictionMessage']
                            )
                        );
                        if ($data['restrictDestination'] === Forms_Shipping_ShippingRestriction::DESTINATION_ZONE) {
                            $config['config']['restrictZones'] = $data['restrictZones'];
                        }
                    } else {
                        $config = array(
                            'name' => $name,
                            'config' => $form->getValues()
                        );
                    }
					Models_Mapper_ShippingConfigMapper::getInstance()->save($config);
				}
			} else {
				$pluginConfig = Models_Mapper_ShippingConfigMapper::getInstance()->find($name);
				if (isset($pluginConfig['config']) && !empty($pluginConfig['config'])) {
					$form->populate($pluginConfig['config']);
				}
			}
			$form->setAction(trim($this->_websiteUrl, '/') . $this->_view->url(array('run' => 'config', 'name' => 'usps'), 'pluginroute'));
			echo $form;
		}
	}

	/**
	 * Action redirects customer to post purchase 'thank you' page if exists
	 * If not redirects to index page
	 */
	public function thankyouAction() {
		$cartId = Tools_ShoppingCart::getInstance()->getCartId();

		if ($cartId) {
			Tools_ShoppingCart::getInstance()->clean();
			$this->_sessionHelper->storeCartSessionKey = $cartId;
            $this->_sessionHelper->storeCartSessionConversionKey = $cartId;
			if ($this->_sessionHelper->storeIsNewCustomer) {
				$cartSession = Models_Mapper_CartSessionMapper::getInstance()->find($cartId);
				$userMapper = Application_Model_Mappers_UserMapper::getInstance();
				$userData = $userMapper->find($cartSession->getUserId());
				$newCustomerPassword = uniqid('customer_' . time());
				$userData->setPassword($newCustomerPassword);
				$newCustomerId = $userMapper->save($userData);
				$customer = Models_Mapper_CustomerMapper::getInstance()->find($cartSession->getUserId());
				$customer->setPassword($newCustomerPassword);
				$customer->registerObserver(new Tools_Mail_Watchdog(array(
					'trigger' => Tools_StoreMailWatchdog::TRIGGER_NEW_CUSTOMER
				)));
				$customer->notifyObservers();
			}
			$cartSession = Models_Mapper_CartSessionMapper::getInstance()->find($cartId);
			$cartSession->registerObserver(new Tools_Mail_Watchdog(array(
				'trigger' => Tools_StoreMailWatchdog::TRIGGER_NEW_ORDER
			)));
            if (class_exists('Tools_AppsServiceWatchdog')) {
                $cartSession->registerObserver(new Tools_AppsServiceWatchdog());
            }
			$cartSession->notifyObservers();
		}


		$thankyouPage = Application_Model_Mappers_PageMapper::getInstance()->fetchByOption(self::OPTION_THANKYOU, true);
		if (!$thankyouPage) {
			$this->_redirector->gotoUrl($this->_websiteHelper->getDefaultPage());
		}
		$this->_redirector->gotoUrl($thankyouPage->getUrl());
	}

	protected function _addVersionToAdminPanel() {
        if (Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_ADMINPANEL) && !$this->_request->isXmlHttpRequest()){
            $shoppingVersion = Tools_Filesystem_Tools::getFile(__DIR__ . DIRECTORY_SEPARATOR . 'version.txt');
            if (!empty($shoppingVersion) && defined('Tools_System_Tools::PLACEHOLDER_SYSTEM_VERSION')) {
                $shoppingVersion = str_replace(array("\r\n", "\n", "\r"), '', '+ Store ' . $shoppingVersion);
                Zend_Layout::getMvcInstance()->getView()->placeholder(Tools_System_Tools::PLACEHOLDER_SYSTEM_VERSION)->append($shoppingVersion);
            }
		}
	}

	public static function getSitemapProducts() {
		return Tools_FeedGenerator::getInstance()->generateProductFeed();
	}

	public function merchandisingAction() {
		if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)) {
			$this->_view->currency = $this->_configMapper->getConfigParam('currency');
			$this->_view->couponTypes = Store_Mapper_CouponMapper::getInstance()->getCouponTypes(true);

			$this->_view->plugins = array();
			foreach (Tools_Plugins_Tools::getPluginsByTags(array('ecommerce')) as $plugin) {
				$tags = $plugin->getTags();
				if (!empty($tags) && in_array('merchandising', $tags)) {
					array_push($this->_view->plugins, $plugin->getName());
				}
				unset($tags);
			}

            $this->_view->helpSection = Tools_Misc::SECTION_STORE_MERCHANDISING;
			$this->_layout->content = $this->_view->render('merchandising.phtml');
			echo $this->_layout->render();
		}
	}

	/**
	 * Action receives and apply coupon codes submitted by user
	 */
	public function couponAction() {
		if ($this->_request->isPost()) {
			$code = trim(filter_var($this->_request->getParam('code'), FILTER_SANITIZE_STRING));

			if (!empty($code)) {
				$code = array_unique(explode(' ', $code));
				$numCodeReceived = count($code);

				$coupons = Store_Mapper_CouponMapper::getInstance()->findByCode($code);

				$msg = array();

                $defaultErrorMessage = $this->_translator->translate("Sorry, some coupon codes you provided are invalid or cannot be combined with the ones you've already captured in. Go back to swap promo codes or proceed with shipping information to checkout.");
                if (isset($this->_sessionHelper->customCouponErrorMessage)) {
                    $defaultErrorMessage = $this->_sessionHelper->customCouponErrorMessage;
                }
				if (!empty($coupons)) {
					$status = Tools_CouponTools::applyCoupons($coupons);
					if (!empty($status)) {
						$hasErrors = count(array_filter($status, function ($status) {
							return $status !== true;
						}));
						if ($hasErrors) {
                            $this->_responseHelper->fail($defaultErrorMessage);
						}
					}

                    $shoppingCart = Tools_ShoppingCart::getInstance();

                    $discount = $shoppingCart->getDiscount();
                    if ($discount) {
                        if($this->_configMapper->getConfigParam('showPriceIncTax')){
                            $discount += $shoppingCart->getDiscountTax();
                        }
                        $msgPartOne = $this->_translator->translate('Congratulations, you save');
                        $msgPartTwo = $this->_translator->translate('on this order. Proceed to checkout now.');
                        $msg = array('msg' => "$msgPartOne " . $this->_view->currency($discount) . " $msgPartTwo");
                    }

                    //processing freeshipping coupons
                    if (Tools_CouponTools::processCoupons($shoppingCart->getCoupons(),
                        Store_Model_Coupon::COUPON_TYPE_FREESHIPPING)
                    ) {
                        $msg = array('msg' => $this->_translator->translate('Congratulations, your order is now available for free shipping. Please proceed to checkout.'));
                    }elseif(!isset($this->_sessionHelper->forceCouponSuccessStatus) && !$discount) {
                        $this->_responseHelper->fail($this->_translator->translate('Coupon not available for that order amount'));
                    }

                    if (isset($this->_sessionHelper->customCouponMessageApply)) {
                        $msg['msg'] = $this->_sessionHelper->customCouponMessageApply;
                    }

                    $coupons = $shoppingCart->getCoupons();
                    if (!empty($coupons)) {
                        $appliedCoupons = array();
                        foreach ($coupons as $coupon) {
                            $appliedCoupons[] = $coupon->getCode();
                        }
                        $msg['couponCodes'] = implode(',', $appliedCoupons);
                    }

                    $this->_responseHelper->success($msg);
				} else {
					$this->_responseHelper->fail($defaultErrorMessage);
				}

			}
		}

        $this->_responseHelper->fail($this->_translator->translate('Failed'));
	}

	/**
	 * Export hook for the website backup
	 *
	 */
	public static function exportWebsiteData() {

		// fetching the pages
		$pages = array();
		$category = Application_Model_Mappers_PageMapper::getInstance()->findByUrl(self::PRODUCT_CATEGORY_URL);

		if (!$category instanceof Application_Model_Models_Page) {
			return array('pages' => $pages);
		}

		// fetching all product pages
		$pagesSql = "SELECT * FROM `page` WHERE system = '0' AND draft = '0' AND (`id` = " . $category->getId() . " OR `parent_id` = " . $category->getId() . ")  ORDER BY `order` ASC;";
		$dbAdapter = Zend_Registry::get('dbAdapter');

		try {
			$pages = $dbAdapter->fetchAll($pagesSql);
		} catch (Exception $e) {
			if (Tools_System_Tools::debugMode()) {
				error_log($e->getMessage());
			}
			return array('pages' => $pages);
		}

		$productsSql = "SELECT * FROM `shopping_product` WHERE `page_id` IN (" . implode(',', array_map(function ($page) {
			return $page['id'];
		}, $pages)) . ");";
		$productsIds = implode(',', array_map(function ($product) {
			return $product['id'];
		}, $dbAdapter->fetchAll($productsSql)));

		//fetch list of product images
		$productImages = $dbAdapter->fetchCol("SELECT DISTINCT photo FROM `shopping_product`");

        $result = array('pages'  => $pages,
                        'media'  => empty($productImages) ? null : array_map(function ($img) {
                                    list($folder, $file) = explode(DIRECTORY_SEPARATOR, $img);
                                    return implode(DIRECTORY_SEPARATOR, array('media', $folder, 'original', $file));
                                }, $productImages)
        );

        if(!empty($productsIds)) {
            $result = array_merge($result, array(
                'tables' => array(
                    'shopping_product'                  => $productsSql,
                    'shopping_brands'                   => "SELECT * FROM `shopping_brands`;",
                    'shopping_product_option'           => "SELECT * FROM `shopping_product_option`;",
                    'shopping_product_option_selection' => "SELECT * FROM `shopping_product_option_selection`;",
                    'shopping_product_set_settings'     => "SELECT * FROM `shopping_product_set_settings` WHERE productId IN (" . $productsIds . ")",
                    'shopping_tags'                     => "SELECT * FROM `shopping_tags`;",
                    'shopping_product_has_option'       => "SELECT * FROM `shopping_product_has_option` WHERE product_id IN (" . $productsIds . ")",
                    'shopping_product_has_part'         => "SELECT * FROM `shopping_product_has_part` WHERE product_id IN (" . $productsIds . ")",
                    'shopping_product_has_related'      => "SELECT * FROM `shopping_product_has_related` WHERE product_id IN (" . $productsIds . ")",
                    'shopping_product_has_tag'          => "SELECT * FROM `shopping_product_has_tag` WHERE product_id IN (" . $productsIds . ")"
                )
            ));
        }
        // return prepared data to the toaster
        return $result;
	}

    public function editAccountAction(){
        if ($this->_request->isPost() && $this->_sessionHelper->getCurrentUser()->getRoleId() != Tools_Security_Acl::ROLE_GUEST) {
            $data = $this->_request->getParams();
            $form = new Forms_User();
            $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                exit;
            }
            if ($form->isValid($data)) {
                $userMapper = Application_Model_Mappers_UserMapper::getInstance();
                $userData = $userMapper->find($this->_sessionHelper->getCurrentUser()->getId());
                if($userData instanceof Application_Model_Models_User){
                    $userExistingPassword = $userData->getPassword();
                    if($userExistingPassword != md5($data['currentPassword'])){
                        $this->_responseHelper->fail($this->_translator->translate('Current password not valid'));
                    }
                    $where = $userMapper->getDbTable()->getAdapter()->quoteInto("id <> ?", $this->_sessionHelper->getCurrentUser()->getId());
                    $where .= ' AND '.$userMapper->getDbTable()->getAdapter()->quoteInto("email = ?", $data['newEmail']);
                    $emailAlreadyExist = $userMapper->fetchAll($where, array(), true);
                    if(!empty($emailAlreadyExist)){
                        $this->_responseHelper->fail($this->_translator->translate('User with this email already exist'));
                    }
                    $userData->setPassword($data['newPassword']);
                    $userData->setEmail($data['newEmail']);
                    $userMapper->save($userData);
                    $userData->registerObserver(new Tools_Mail_Watchdog(array(
                        'trigger' => Tools_StoreMailWatchdog::TRIGGER_NEW_USER_ACCOUNT
                    )));
                    $userData->notifyObservers();
                }else{
                    $this->_responseHelper->fail($this->_translator->translate('Autification failed'));
                }
                $this->_responseHelper->success(array('message'=>$this->_translator->translate('New account information send at your email'), 'email'=> $data['newEmail']));
            }else{
                $errorMessage = $form->getErrors();
                $singleMessage = 0;
                if(!empty($errorMessage)){
                    $resultMessage = '';
                    foreach($errorMessage as $message){
                        foreach($message as $msg){
                            if($msg != ''){
                                if($singleMessage == 0){
                                    $singleMessage = 1;
                                    $resultMessage .= $this->_translator->translate($msg).' </br>';
                                }
                            }
                        }
                    }
                    $this->_responseHelper->fail($resultMessage);
                }

            }
        }
    }

    public function setFreebiesAction(){
        if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT) && $this->_request->isPost()) {
            $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                exit;
            }
            $productId = filter_var($this->_request->getParam('productId'), FILTER_SANITIZE_NUMBER_INT);
            $priceValue = filter_var($this->_request->getParam('priceValue'), FILTER_SANITIZE_NUMBER_FLOAT);
            $quantity = filter_var($this->_request->getParam('quantity'), FILTER_SANITIZE_NUMBER_INT);
            $freebiesSettingsMapper = Models_Mapper_ProductFreebiesSettingsMapper::getInstance();
            $freebiesSettingsMapper->save(array('prod_id' => $productId, 'price_value' => $priceValue, 'quantity' => $quantity));
            $this->_responseHelper->success('');
        }
    }

    public function editUserProfileAction(){
        if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT) && $this->_request->isPost()) {
            $data = $this->_request->getParams();

            $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                exit;
            }
            if(!empty($data['profileElement']) && isset($data['profileValue']) && !empty($data['userId'])){
                $userMapper = Application_Model_Mappers_UserMapper::getInstance();
                $user = $userMapper->find($data['userId']);
                $data['profileValue'] = trim($data['profileValue']);
                if($user instanceof Application_Model_Models_User){
                    switch($data['profileElement']) {
                        case 'email':
                            $validator = new Zend_Validate_EmailAddress();
                            if ($validator->isValid($data['profileValue'])) {
                                $user->setEmail($data['profileValue']);
                            }else{
                                $this->_responseHelper->fail($this->_translator->translate('Email not valid'));
                            }
                            $validator = new Zend_Validate_Db_RecordExists(
                                array(
                                    'table' => 'user',
                                    'field' => 'email'
                                )
                            );
                            if ($validator->isValid($data['profileValue'])) {
                                $this->_responseHelper->fail($this->_translator->translate('User with this email already exist'));
                            }
                        break;
                        case 'fullname':
                            $user->setFullName($data['profileValue']);
                        break;
                        case 'notes':
                            $user->setNotes($data['profileValue']);
                        break;
                        default:
                            $this->_responseHelper->fail($this->_translator->translate('Element doesn\'t exists'));

                    }

                    $userMapper->save($user);
                    $this->_responseHelper->success('');
                }
                $this->_responseHelper->fail();
            }
        }
    }

    public function saveDiscountTaxRateAction(){
        if(Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT) && $this->_request->isPost()) {
            $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                exit;
            }
            $couponDiscountTaxRate = filter_var($this->_request->getParam('discountTaxValue'), FILTER_SANITIZE_NUMBER_INT);
            $shoppingConfigParams = $this->_configMapper->getConfigParams();
            $shoppingConfigParams['couponDiscountTaxRate'] = $couponDiscountTaxRate;
            $this->_configMapper->save($shoppingConfigParams);
            $this->_responseHelper->success('');
        }
    }

    public function precalculateDiscountTaxAction(){
        if(Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT) && $this->_request->isPost()) {
            $couponDiscountTaxRate = filter_var($this->_request->getParam('discountTaxValue'), FILTER_SANITIZE_NUMBER_INT);
            $couponDiscountAmount  = $this->_request->getParam('discountAmount');
            $getRate = 'getRate'.$couponDiscountTaxRate;
            $shoppingConfig = $this->_configMapper->getConfigParams();
            if(isset($shoppingConfig['showPriceIncTax']) && (bool)$shoppingConfig['showPriceIncTax'] == 1){
                $tax = Models_Mapper_Tax::getInstance()->getDefaultRule();
                if($tax instanceof Models_Model_Tax){
                    $couponAmountWithDiscount = $couponDiscountAmount + ($couponDiscountAmount / 100 * $tax->$getRate());
                    $this->_responseHelper->success(array('discountResultValue' => $couponAmountWithDiscount));
                }
                $this->_responseHelper->success(array('discountResultValue' => $couponDiscountAmount));
            }
            $this->_responseHelper->success(array('discountResultValue' => $couponDiscountAmount));
        }
    }

    /**
     * Pickup locations zones config
     */
    protected function pickupLocationAction()
    {
        $pickupLocationCategory = Store_Mapper_PickupLocationCategoryMapper::getInstance();
        $this->_view->pickupLocationsCategories = array_map(
            function ($pickupCategory) {
                return $pickupCategory->toArray();
            },
            $pickupLocationCategory->fetchAll()
        );
        $this->_view->countries = Tools_Geo::getCountries();
        $this->_view->helpSection = Tools_Misc::SECTION_STORE_MANAGELOCATION;
        $this->_layout->content = $this->_view->render('pickup-location.phtml');
        $this->_layout->sectionId = Tools_Misc::SECTION_STORE_MANAGEZONES;
        echo $this->_layout->render();
    }

    /**
     * Delete pickup location config row
     */
    public function deletePickupLocationAction()
    {
        if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT) && $this->_request->isDelete()) {
            $locationId = filter_var($this->_request->getParam('locationId'), FILTER_SANITIZE_NUMBER_INT);
            if ($locationId) {
                Store_Mapper_PickupLocationConfigMapper::getInstance()->deleteConfig($locationId);
                $this->_responseHelper->success('');
            }
        }
    }

    public function ordersImportConfigAction()
    {
        if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)) {
            $importConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam(self::ORDER_IMPORT_CONFIG);
            if ($importConfig !== null) {
                $importConfig = unserialize($importConfig);
                $this->_view->importConfig = $importConfig;
            }
            $this->_view->ordersImportTemplates = array(
                Tools_ExportImportOrders::DEFAULT_IMPORT_ORDER => $this->_translator->translate('Default template'),
                Tools_ExportImportOrders::PRESTASHOP_IMPORT_ORDER => $this->_translator->translate(
                    'Prestashop template'
                ),
                Tools_ExportImportOrders::MAGENTO_IMPORT_ORDER => $this->_translator->translate('Magento template')
            );
            $this->_view->translator = $this->_translator;
            $this->_view->defaultImportsFileds = Tools_ExportImportOrders::getDefaultOrderExportConfig();
            $this->_view->helpSection = Tools_Misc::SECTION_STORE_IMPORTORDERS;
            $this->_layout->content = $this->_view->render('orders-import.phtml');
            echo $this->_layout->render();
        }
    }

    public function importOrdersAction()
    {
        if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)) {
            ini_set("max_execution_time", 300);
            $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                exit;
            }
            $uploader = new Zend_File_Transfer_Adapter_Http();
            $ordersCsv = $uploader->getFileInfo();
            $importOrdersFields = $this->_request->getParam('importOrdersFields');
            $importOrdersFields = explode(',', $importOrdersFields);
            $realOrdersFields = $this->_request->getParam('realOrdersFields');
            $currentTemplateName = $this->_request->getParam('currentTemplateName');
            $defaultOrderStatus = $this->_request->getParam('defaultOrderStatus');
            $realOrdersFields = explode(',', $realOrdersFields);
            $importOrdersFieldsData = array_combine($realOrdersFields, $importOrdersFields);
            if (!$uploader->isValid()) {
                $this->_responseHelper->fail('');
            }
            $ordersData = Tools_ExportImportOrders::createOrdersCsv($ordersCsv, $importOrdersFieldsData, $currentTemplateName, $defaultOrderStatus);
            if ($ordersData['error'] === true) {
                if (isset($ordersData['errorMessage'])) {
                    $this->_responseHelper->fail($ordersData['errorMessage']);
                }
                $this->_sessionHelper->importOrdersErrors = $ordersData['importErrorsIds'];
                $this->_responseHelper->fail(
                    $this->_translator->translate(
                        'Some orders have error during the import'
                    ) . '<br/><a id="downloadOrdersImportReport" href="' . $this->_websiteHelper->getUrl(
                    ) . 'plugin/shopping/run/downloadImportOrdersReport/" >' . $this->_translator->translate(
                        'click download report'
                    ) . '</a>'
                );
            }
            $this->_responseHelper->success($this->_translator->translate('Order import finished'));
        }
    }

    public function downloadImportOrdersReportAction()
    {
        if (Tools_Security_Acl::isAllowed(
            self::RESOURCE_STORE_MANAGEMENT
        ) && isset($this->_sessionHelper->importOrdersErrors)
        ) {
            Tools_ExportImportOrders::prepareImportOrdersReport($this->_sessionHelper->importOrdersErrors);
        }
    }

    public function exportOrdersAction()
    {
        $ordersIds = filter_var($this->_request->getParam('orderIds'), FILTER_SANITIZE_STRING);
        $data = $this->_request->getParams();
        $ordersIds = ($data['allOrders'] == 1 || empty($ordersIds)) ? array() : explode(',', $ordersIds);
        if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)) {
            if (!empty($ordersIds)) {
                Tools_ExportImportOrders::prepareOrdersDataForExport($data, $ordersIds);
            } else {
                parse_str($data['filters'], $res);
                $data['filters'] = Tools_FilterOrders::filter($res);
                Tools_ExportImportOrders::prepareOrdersDataForExport($data, $ordersIds);
            }
        }
    }

    public function getOrderExportConfigAction()
    {
        if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)) {
            $exportConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam(self::ORDER_EXPORT_CONFIG);
            if ($exportConfig !== null) {
                $exportConfig = unserialize($exportConfig);
            }
            $defaultOrderExportConfig = Tools_ExportImportOrders::getDefaultOrderExportConfig();
            $this->_responseHelper->success(
                array('export_config' => $exportConfig, 'defaultConfig' => $defaultOrderExportConfig)
            );
        }
    }

    public function getOrdersImportSampleDataAction()
    {
        if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)) {
            Tools_ExportImportOrders::getSampleOrdersData();

        }
    }

    /**
     * Update recurring payment(subscription) status
     */
    public function updateSubscriptionAction()
    {
        $user = $this->_sessionHelper->getCurrentUser();
        $roleId = $user->getRoleId();
        $currentUserId = $user->getId();
        if (Tools_Security_Acl::ROLE_GUEST !== $roleId && $this->_request->isPost()) {
            $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                exit;
            }
            $cartId = filter_var($this->_request->getParam('cartId'), FILTER_SANITIZE_NUMBER_INT);
            $changeSubscription = filter_var($this->_request->getParam('changeSubscription'), FILTER_SANITIZE_STRING);
            $nextBillingDate = filter_var($this->_request->getParam('nextBillingDate'), FILTER_SANITIZE_STRING);
            if ($nextBillingDate) {
                $changeSubscription = 'update';
            }
            $cart = Models_Mapper_CartSessionMapper::getInstance()->find($cartId);
            $cartUserId = intval($cart->getUserId());
            $recurringPaymentParams = $this->_request->getParams();
            $allowedUserStatuses = array(
                Store_Model_RecurringPayments::ACTIVE_RECURRING_PAYMENT,
                Store_Model_RecurringPayments::SUSPENDED_RECURRING_PAYMENT,
                Store_Model_RecurringPayments::CANCELED_RECURRING_PAYMENT,
                'update'
            );
            if ($cart instanceof Models_Model_CartSession && ($cartUserId === $currentUserId || Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT))) {
                if (!Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT) && !in_array($changeSubscription,
                        $allowedUserStatuses)
                ) {
                    $this->_responseHelper->fail($this->_translator->translate('Recurring status not allowed'));
                }
                $result = Tools_RecurringPaymentTools::updateSubscription($cartId, $changeSubscription,
                    $recurringPaymentParams);
                $responseMessage = $this->_translator->translate($result['message']);
                if ($result['error']) {
                    $this->_responseHelper->fail($responseMessage);
                } else {
                    $recurrentMapper = Store_Mapper_RecurringPaymentsMapper::getInstance();
                    if ($changeSubscription !== 'update') {
                        $recurrentMapper->updateRecurringStatus($cartId, $changeSubscription);
                    } else {
                        $date = date('Y-m-d', strtotime($nextBillingDate));
                        $recurrentMapper->updateRecurringDate($cartId, $date);
                    }
                    $this->_responseHelper->success($responseMessage);
                }
            } else {
                $this->_responseHelper->fail($this->_translator->translate('Cart id not provided'));
            }

        }
        $this->_responseHelper->fail('');
    }

    /**
     * Change recurring payment type
     */
    public function changeRecurringTypeAction()
    {
        if ($this->_request->isPost()) {
            $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                exit;
            }
            $recurringPaymentType = filter_var($this->_request->getParam('recurringPaymentType'),
                FILTER_SANITIZE_STRING);
            $paymentType = false;
            if (in_array($recurringPaymentType, Api_Store_Recurringtypes::$recurringAcceptType)) {
                $paymentType = $recurringPaymentType;
            }
            $shoppingCart = Tools_ShoppingCart::getInstance();
            $customer = $shoppingCart->getCustomer();
            $shoppingCart->setRecurringPaymentType($paymentType);
            $shoppingCart->save()->saveCartSession($customer);
        }

    }

    /**
     * Update recurring data
     *
     * @throws Exceptions_SeotoasterPluginException
     * @throws Zend_Db_Table_Exception
     */

    public function updateRecurringDataAction()
    {
        $user = $this->_sessionHelper->getCurrentUser();
        $roleId = $user->getRoleId();
        $currentUserId = $user->getId();
        $acceptedForChangeData = array('shipping', 'payment_cycle');
        if (Tools_Security_Acl::ROLE_GUEST !== $roleId && $this->_request->isPost()) {
            $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                exit;
            }
            $cartId = filter_var($this->_request->getParam('cartId'), FILTER_SANITIZE_NUMBER_INT);
            $changeType = filter_var($this->_request->getParam('changeType'), FILTER_SANITIZE_STRING);
            $cartMapper = Models_Mapper_CartSessionMapper::getInstance();
            $cart = $cartMapper->find($cartId);
            $cartUserId = intval($cart->getUserId());
            if (!in_array($changeType, $acceptedForChangeData)) {
                $this->_responseHelper->fail($this->_translator->translate('Data type not accepted'));
            }
            if ($cart instanceof Models_Model_CartSession && ($cartUserId === $currentUserId || Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT))
            ) {
                switch ($changeType) {
                    case 'shipping':
                        $this->_updateRecurringShipping($cart, $currentUserId);
                        break;
                    case 'payment_cycle':
                        $this->_updateRecurringCycle($cart);
                        break;
                    default:
                        $this->_responseHelper->fail($this->_translator->translate('Data type not accepted'));
                }
            } else {
                $this->_responseHelper->fail($this->_translator->translate('Cart id not provided'));
            }
        }
    }

    /**
     * Update recurring cycle
     *
     * @param Models_Model_CartSession $cart cart object
     */
    private function _updateRecurringCycle($cart)
    {
        $params = $this->_request->getParams();
        $activeRecurringPaymentTypes = Store_Mapper_RecurringPaymentsMapper::getInstance()->getRecurringTypes();

        if (array_key_exists(strtolower('recurring-payment-' . $params['paymentCycle']),
            $activeRecurringPaymentTypes)) {
            $result = Tools_RecurringPaymentTools::updateSubscription($cart->getId(), '', $params);
            $responseMessage = $this->_translator->translate($result['message']);
            if ($result['error']) {
                $this->_responseHelper->fail($responseMessage);
            } else {
                $this->_responseHelper->success($responseMessage);
            }
        }
        $this->_responseHelper->fail($this->_translator->translate('You can\'t change this payment cycle'));
    }


    /**
     * Update recurring shipping data
     *
     * @param Models_Model_CartSession $cart cart object
     * @param int $currentUserId current user Id
     * @throws Exceptions_SeotoasterPluginException
     * @throws Zend_Db_Table_Exception
     */
    private function _updateRecurringShipping($cart, $currentUserId)
    {
        $shippingAddressId = $cart->getShippingAddressId();
        $firstName = filter_var($this->_request->getParam('firstName'), FILTER_SANITIZE_STRING);
        $lastName = filter_var($this->_request->getParam('lastName'), FILTER_SANITIZE_STRING);
        $address1 = filter_var($this->_request->getParam('address1'), FILTER_SANITIZE_STRING);
        $address2 = filter_var($this->_request->getParam('address2'), FILTER_SANITIZE_STRING);
        $zip = filter_var($this->_request->getParam('zip'), FILTER_SANITIZE_STRING);
        $shippingAddress = Models_Mapper_CustomerMapper::getInstance()->getUserAddressByUserId($currentUserId,
            $shippingAddressId);
        $address = $shippingAddress[$shippingAddressId];
        $address['firstname'] = $firstName;
        $address['lastname'] = $lastName;
        $address['address1'] = $address1;
        $address['address2'] = $address2;
        $address['address_type'] = Models_Model_Customer::ADDRESS_TYPE_SHIPPING;
        $address['zip'] = $zip;
        $address = Tools_Misc::clenupAddress($address);
        $address['id'] = Tools_Misc::getAddressUniqKey($address);
        $address['user_id'] = $currentUserId;
        $addressTable = new Models_DbTable_CustomerAddress();
        if (null === ($row = $addressTable->find($address['id'])->current())) {
            $cartMapper = Models_Mapper_CartSessionMapper::getInstance();
            $row = $addressTable->createRow();
            $row->setFromArray($address);
            $newShippingAddress = $row->save();
            $cart->setShippingAddressId($newShippingAddress);
            $cartMapper->save($cart);
        }
        $this->_responseHelper->success($this->_translator->translate('Shipping address updated'));
    }


    /**
     * Refund order (full or partial order refund)
     */
    public function refundPaymentAction()
    {
        $orderId = filter_var($this->_request->getParam('orderId'), FILTER_SANITIZE_NUMBER_INT);
        $refundAmount = filter_var($this->_request->getParam('refundAmount'), FILTER_SANITIZE_STRING);
        $refundNotes = filter_var($this->_request->getParam('refundInfo'), FILTER_SANITIZE_STRING);
        $paymentGateway = strtolower(filter_var($this->_request->getParam('paymentGateway')));
        $refundUsingPaymentGateway = (bool)$this->_request->getParam('refundUsingPaymentGateway', false);
        $tokenToValidate = $this->_request->getParam('secureToken', false);
        $taxRule = filter_var($this->_request->getParam('refundTax', false), FILTER_SANITIZE_NUMBER_INT);
        $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
        if (!$valid) {
            exit;
        }
        if ($this->_request->isPost() && Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT) && $orderId) {
            $orderModel = Models_Mapper_CartSessionMapper::getInstance()->find($orderId);
            if ($orderModel instanceof Models_Model_CartSession) {
                $orderStatus = $orderModel->getStatus();
                if ($orderStatus !== Models_Model_CartSession::CART_STATUS_COMPLETED && $orderStatus !== Models_Model_CartSession::CART_STATUS_SHIPPED && $orderStatus !== Models_Model_CartSession::CART_STATUS_DELIVERED) {
                    $this->_responseHelper->fail($this->_translator->translate('You can refund only orders with status completed'));
                }
                $total = $orderModel->getTotal();
                if (empty($refundAmount)) {
                    $refundAmount = $total;
                }
                $refundResultMessage = '';
                $finalTax = 0;
                if ($taxRule) {
                    $finalTax = Tools_Tax_Tax::calculateDiscountTax($refundAmount, $taxRule);
                }
                if ($total >= $refundAmount) {
                    if ($orderModel->getTotalTax() <= 0) {
                        $totalTax = $finalTax;
                    } else {
                        $totalTax = $orderModel->getTotalTax() - $finalTax;
                    }
                    $data = array(
                        'refund_notes' => $refundNotes,
                        'refund_amount' => $refundAmount,
                        'status' => Models_Model_CartSession::CART_STATUS_REFUNDED,
                        'total_tax' => $totalTax,
                        'sub_total' => $orderModel->getSubTotal() - $refundAmount - $finalTax,
                        'total' => $total - $refundAmount
                    );
                    if ($data['total'] <= 0) {
                        $data['total'] = 0;
                    }
                    if ($data['total_tax'] <= 0) {
                        $data['total_tax'] = 0;
                    }
                    if ($data['total'] === 0) {
                        $data['sub_total'] = 0;
                    }
                    if ($refundUsingPaymentGateway) {
                        $paymentGatewayInfo = Application_Model_Mappers_PluginMapper::getInstance()->findByName($paymentGateway);
                        if ($paymentGatewayInfo instanceof Application_Model_Models_Plugin) {
                            $pluginGatewayStatus = $paymentGatewayInfo->getStatus();
                            if ($pluginGatewayStatus === Application_Model_Models_Plugin::ENABLED) {
                                $paymentPluginClassName = ucfirst($paymentGateway);
                                if (class_exists($paymentPluginClassName) && method_exists($paymentPluginClassName,
                                        'refund')
                                ) {
                                    $reflection = new ReflectionMethod($paymentPluginClassName, 'refund');
                                    if (!$reflection->isPublic()) {
                                        $this->_responseHelper->fail($this->_translator->translate('Can\'t access payment gateway refund method'));
                                    }
                                    $pageData = array('websiteUrl' => $this->_websiteHelper->getUrl());
                                    try {
                                        $plugin = Tools_Factory_PluginFactory::createPlugin($paymentGateway, array(),
                                            $pageData);
                                        $result = $plugin->refund($orderId, $refundAmount, $refundNotes);
                                        if (!isset($result['error'])) {
                                            $this->_responseHelper->fail($this->_translator->translate('Unexpected error happened. Please contact support'));
                                        } elseif (!empty($result['error']) && $result['error'] === 1) {
                                            if (!empty($result['errorMessage'])) {
                                                $this->_responseHelper->fail($this->_translator->translate($result['errorMessage']));
                                            } else {
                                                $this->_responseHelper->fail($this->_translator->translate('Payment gateway error happened'));
                                            }
                                        }
                                    } catch (Exception $e) {
                                        $this->_responseHelper->fail($e->getMessage());
                                    }
                                } else {
                                    $this->_responseHelper->fail($this->_translator->translate('Payment gateway doesn\'t have refund functionality'));
                                }
                            } else {
                                $this->_responseHelper->fail($this->_translator->translate('Payment plugin disabled'));
                            }

                        } else {
                            $this->_responseHelper->fail($this->_translator->translate('Payment plugin doesn\'t exists'));
                        }
                        $currency = Zend_Registry::get('Zend_Currency');
                        $refundSuccessMessage = $this->_translator->translate('You\'ve successfully refunded');
                        $refundSuccessMessage .= ' ' . $currency->toCurrency($refundAmount);
                        $refundSuccessMessage .= ' '. $this->_translator->translate('to your client’s credit card');
                    } else {
                        $refundSuccessMessage = $this->_translator->translate('You\'ve added a note. No amount refunded to credit card by this system');
                    }

                    Models_Mapper_OrdersMapper::getInstance()->updateOrderInfo($orderId, $data);

                    $orderModel = Models_Mapper_CartSessionMapper::getInstance()->find($orderId);
                    $orderModel->registerObserver(new Tools_Mail_Watchdog(array(
                        'trigger' => Tools_StoreMailWatchdog::TRIGGER_REFUND,
                        'refundNotes' => $refundNotes,
                        'refundAmount' => $refundAmount
                    )));
                    $orderModel->notifyObservers();

                    $data['total'] = round($data['total'], 2);

                    $this->_responseHelper->success(array('message' => $refundSuccessMessage, 'total' => $data['total']));
                }
                $this->_responseHelper->fail($this->_translator->translate('Sorry, you can\'t refund more than the order\'s original amount.'));
            }

            $this->_responseHelper->fail($this->_translator->translate('Order doesn\'t exists'));


        }
        $this->_responseHelper->fail($this->_translator->translate('Order id missing'));
    }

    public function editUserProfileAddressAction(){
        if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT) && $this->_request->isPost()) {
            $data = $this->_request->getParams();

            $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                exit;
            }
            if(!empty($data['profileField']) && !empty($data['userId'])){
                $customerToken = '';
                $countries = Zend_Locale::getTranslationList('territory', null, 2);
                $cartSessionMapper = Models_Mapper_CartSessionMapper::getInstance();
                $customerTable = new Models_DbTable_CustomerAddress();

                $data['profileValue'] = trim($data['profileValue']);
                if(empty($data['clientToken'])){
                    $this->_responseHelper->fail();
                }
                $customerMapper = Models_Mapper_CustomerMapper::getInstance();
                $currentCustomer = $customerMapper->find($data['userId']);
                $customerAddress = $customerMapper->getUserAddressByUserId($data['userId'], $data['clientToken']);
                if(!empty($customerAddress)) {
                    foreach ($customerAddress as $value) {
                        if($data['profileField'] === 'country' || $data['profileField'] === 'state') {
                            if ($data['profileField'] === 'country') {
                                $currentCountry = array_search($data['profileValue'], $countries);
                                if ($currentCountry === false) {
                                    $this->_responseHelper->fail(array('oldToken'=> $data['clientOldToken'], 'respText'=> $this->_translator->translate('Can\'t update Country')));
                                }
                                $value[$data['profileField']] = $currentCountry;
                            }
                            if ($data['profileField'] === 'state') {
                                $currentState = Tools_Geo::getStateByCode($data['profileValue']);
                                if ($currentState === null) {
                                    $this->_responseHelper->fail(array('oldToken'=> $data['clientOldToken'], 'respText'=> $this->_translator->translate('Can\'t update State')));
                                }
                                $value[$data['profileField']] = $currentState['id'];
                            }
                            $addressValues = $value;

                        }else{
                            $value[$data['profileField']] = $data['profileValue'];

                            if($value['address_type'] === 'shipping'){
                                $value['mobilecountrycode'] = $value['country'];
                                $value = $this->_normalizeMobilePhoneNumber($value);
                            }
                            $addressValues = Tools_Misc::clenupAddress($value);

                        }
                        $customerToken = $customerMapper->addAddress($currentCustomer, $addressValues, $data['addressType']);
                        $currentCartSession = $cartSessionMapper->fetchOrders($currentCustomer->getId());

                        if(!empty($currentCartSession) && (!empty($customerToken))) {
                            if($value['address_type'] === 'shipping') {
                                $newToken['shipping_address_id'] = $customerToken;
                            }else{
                                $newToken['billing_address_id'] = $customerToken;
                            }
                            $newToken['updated_at'] = date(DATE_ATOM);
                            $cartSessionMapper->updateAddress($data['clientToken'], $data['addressType'], $newToken);

                        }
                            $lastData =  $customerMapper->getUserAddressByUserId($currentCustomer->getId(),$customerToken);
                            if(!empty($lastData) && ($data['clientToken'] !== $customerToken)){
                                $where = $customerTable->getAdapter()->quoteInto('id =?', $data['clientToken']);
                                $customerTable->delete($where);
                            }

                    }
                    $this->_responseHelper->success(array('newToken'=> $customerToken, 'oldToken'=> $data['clientOldToken']));
                }
            }
            $this->_responseHelper->fail();
        }
    }

    private function _normalizeMobilePhoneNumber($arr) {
        if(!empty($arr['mobile'])) {
            $countryPhoneCode = Zend_Locale::getTranslation($arr['mobilecountrycode'], 'phoneToTerritory');
            $mobileNumber = Apps_Tools_Twilio::normalizePhoneNumberToE164($arr['mobile'], $countryPhoneCode);
            if ($mobileNumber !== false) {
                $arr['mobile'] = $mobileNumber;
            }
        }
        return $arr;
    }

}
