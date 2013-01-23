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
	 * Option for the client page
	 */
    const OPTION_STORE_CLIENT_LOGIN = 'option_storeclientlogin';
    
    /**
     * Option for the page options system.
     */
    const OPTION_CHECKOUT       = 'option_checkout';
    
    const OPTION_STORE_SHIPPING_TERMS  = 'option_storeshippingterms';

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

	const SHIPPING_TOC_STATUS   = 'checkoutShippingTocRequire';

	const SHIPPING_TOC_LABEL    = 'checkoutShippingTocLabel';

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
	        'clients'
        ),
	    Tools_Security_Acl::ROLE_ADMIN => array(
		   
	    ),
	    Tools_Security_Acl::ROLE_GUEST => array(

        )
    );

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
        
        $this->_addVersionToAdminPanel();
        
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
        $acl->allow(self::ROLE_SALESPERSON, Tools_Security_Acl::RESOURCE_MEDIA);
        $acl->allow(self::ROLE_SALESPERSON, Tools_Security_Acl::RESOURCE_PAGES);
        $acl->allow(self::ROLE_SALESPERSON, Tools_Security_Acl::RESOURCE_PLUGINS_MENU);
        $acl->allow(self::ROLE_SALESPERSON, Tools_Security_Acl::RESOURCE_PLUGINS);
        $acl->allow(self::ROLE_SALESPERSON, Tools_Security_Acl::RESOURCE_THEMES);
        Zend_Registry::set('acl', $acl);
        
  		$layout = Zend_Layout::getMvcInstance();
		$layout->getView()->headScript()->appendFile($this->_websiteUrl.'plugins/shopping/web/js/product-options.js');
	
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
        $this->_layout->sectionId = Tools_Misc::SECTION_STORE_CONFIG;
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
		$this->_view->shoppingConfig = $this->_configMapper->getConfigParams();

        $markupConfig = Models_Mapper_ShippingConfigMapper::getInstance()->find(self::SHIPPING_MARKUP);
        $markupForm = new Forms_Shipping_MarkupShipping();
        if(isset($markupConfig['config']) && !empty($markupConfig['config'])){
            $markupForm->populate($markupConfig['config']);
        }
        $freeShippingForm = new Forms_Shipping_FreeShipping();
        $freeShippingConfig = Models_Mapper_ShippingConfigMapper::getInstance()->find(self::SHIPPING_FREESHIPPING);
        if(isset($freeShippingConfig['config']) && !empty($freeShippingConfig['config'])){
            $freeShippingForm->populate($freeShippingConfig['config']);
        }
       	$this->_view->config = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
		$this->_view->freeForm = $freeShippingForm;
        $this->_view->markupForm = $markupForm;

		$this->_view->shippingPlugins  = array_filter(Tools_Plugins_Tools::getEnabledPlugins(), function($plugin){
			$reflection = new Zend_Reflection_Class(ucfirst($plugin->getName()));
			return $reflection->implementsInterface('Interfaces_Shipping');
		});
		$this->_layout->content  = $this->_view->render('shipping.phtml');
        $this->_layout->sectionId = Tools_Misc::SECTION_STORE_SHIPPINGCONFIG;
		echo $this->_layout->render();
	}

	protected function shippingconfigAction(){
		if (!Tools_Security_Acl::isAllowed(self::RESOURCE_API)){
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
		if (!$customer->getId()){
			if (null === ($existingCustomer = Models_Mapper_CustomerMapper::getInstance()->findByEmail($data['email']))) {
				$customer->setRoleId(Shopping::ROLE_CUSTOMER)
					->setEmail($data['email'])
					->setFullName($data['firstname'] . ' ' . $data['lastname'])
					->setIpaddress($_SERVER['REMOTE_ADDR'])
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
        $zonesMapper = Models_Mapper_Zone::getInstance();
		if ($this->_request->isPost()){
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
        $this->_view->zones     = array_map(function($zone){
            return $zone->toArray();
        }, $zonesMapper->fetchAll());
        $this->_view->states    = Tools_Geo::getState();
        $this->_view->countries = Tools_Geo::getCountries();
		$this->_layout->content = $this->_view->render('zones.phtml');
		echo $this->_layout->render();
	}

	/**
	 * Method renders tax configuration screen and handling tax saving
	 * @return html
	 */
	protected function taxesAction() {
		$this->_view->priceIncTax = $this->_configMapper->getConfigParam('showPriceIncTax');
        $this->_view->rules       = array_map(function($rule){
            return $rule->toArray();
        }, Models_Mapper_Tax::getInstance()->fetchAll());
        $this->_view->zones       = array_map(function($zone){
            return $zone->toArray();
        }, Models_Mapper_Zone::getInstance()->fetchAll());
		$this->_layout->content   = $this->_view->render('taxes.phtml');
        $this->_layout->sectionId = Tools_Misc::SECTION_STORE_TAXES;
		echo $this->_layout->render();
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

            $this->_view->websiteConfig = $this->_websiteConfig;

            $this->_layout->content = $this->_view->render('product.phtml');
            $this->_layout->sectionId = Tools_Misc::SECTION_STORE_ADDEDITPRODUCT;
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
		$content    = '';
		$nextPage       = filter_var($this->_request->getParam('nextpage'), FILTER_SANITIZE_NUMBER_INT);
		$order      = $this->_request->getParam('order');
		$tags       = $this->_request->getParam('tags');
		$brands     = $this->_request->getParam('brands');

		$offset     = intval($nextPage) * Widgets_Productlist_Productlist::DEFAULT_LIMIT;
		$products   = Models_Mapper_ProductMapper::getInstance()->fetchAll("enabled='1'", $order, $offset, Widgets_Productlist_Productlist::DEFAULT_LIMIT, null, $tags, $brands );
		if (!empty($products)){
			$template = $this->_request->getParam('template');
			$widget   = Tools_Factory_WidgetFactory::createWidget('productlist', array($template, $offset + Widgets_Productlist_Productlist::DEFAULT_LIMIT));
			$content  = $widget->setProducts($products)->setCleanListOnly(true)->render();
			unset($widget);
		}
		if (null !== ($pageId = filter_var($this->_request->getParam('pageId'), FILTER_SANITIZE_NUMBER_INT))){
			$page = Application_Model_Mappers_PageMapper::getInstance()->find($pageId);
			if ($page instanceof Application_Model_Models_Page && !empty($content)){
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
		if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)){
			$this->_view->brands = Models_Mapper_Brand::getInstance()->fetchAll();
			$this->_view->tags = Models_Mapper_Tag::getInstance()->fetchAll();
			return $this->_view->render('manage_products.phtml');
		}
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
			$this->_layout->content = '<div id="profile" class="toaster-widget bg-content">'.$content.'</div>';
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
		if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)){
            $this->_layout->content = $this->_view->render('brandlogos.phtml');
            $this->_layout->sectionId = Tools_Misc::SECTION_STORE_BRANDLOGOS;
            echo $this->_layout->render();
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
                $userMapper = Application_Model_Mappers_UserMapper::getInstance();
                $userData = $userMapper->find($cartSession->getUserId());
                $newCustomerPassword = uniqid('customer_' . time());
                $userData->setPassword($newCustomerPassword);
                $newCustomerId = $userMapper->save($userData);
                $customer = Models_Mapper_CustomerMapper::getInstance()->find($cartSession->getUserId());
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
    
    protected function _addVersionToAdminPanel(){
        $shoppingVersion = Tools_Filesystem_Tools::getFile(__DIR__ . DIRECTORY_SEPARATOR .'version.txt');
        if($shoppingVersion != null && $shoppingVersion != ''){
            $this->_view->shoppingVersion = str_replace("\n",'', '+ Store '.$shoppingVersion);
            $this->_injectContent($this->_view->render('shoppingVersion.phtml'));
        }
    }
}
