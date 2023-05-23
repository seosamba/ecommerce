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
     * System role 'supplier'
     */
    const ROLE_SUPPLIER = 'supplier';

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

    const SHIPPING_TRACKING_URL = 'trackingurl';

	const SHIPPING_TOC_STATUS = 'checkoutShippingTocRequire';

    const SHIPPING_SINGLE_RESULT = 'skipSingleShippingResult';

	const SHIPPING_TOC_LABEL = 'checkoutShippingTocLabel';

    const SHIPPING_ERROR_MESSAGE = 'checkoutShippingErrorMessage';

    const SHIPPING_SUCCESS_MESSAGE = 'checkoutShippingSuccessMessage';

    const SHIPPING_TAX_RATE     = 'shippingTaxRate';

    const SHIPPING_IS_GIFT = 'checkoutShippingIsGift';

    const COUPON_DISCOUNT_TAX_RATE  = 'couponDiscountTaxRate';

    const COUPON_ZONE = 'zoneId';


    const QUANTITY_PICKUP_LOCATION_ON_SCREEN = 6;

    const AMOUNT_TYPE_UP_TO = 'up to';

    const AMOUNT_TYPE_OVER = 'over';

    const AMOUNT_TYPE_EACH_OVER = 'eachover';

    const COMPARE_BY_AMOUNT = 'amount';

    const COMPARE_BY_WEIGHT = 'weight';

    const ORDER_CONFIG  = 'orderconfig';

    const ORDER_EXPORT_CONFIG = 'order_export_config';

    const ORDER_IMPORT_CONFIG = 'order_import_config';

    const DEFAULT_USER_GROUP = 'default_user_group';

    const THROTTLE_TRANSACTIONS = 'throttleTransactions';

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

    /**
     * Plugin api actions
     *
     * @return array
     */
    public static function pluginApiActions()
    {
        return array('getApiProducts');
    }

    /**
     * @var null|Zend_Layout
     */
    protected $_layout = null;

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

			$country = $shoppingConfig['country'];
			if(!empty($shoppingConfig['currencyCountry'])) {
                $country = $shoppingConfig['currencyCountry'];
            }

			$locale = Zend_Locale::getLocaleToTerritory($country);
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
        if (!$acl->hasRole(self::ROLE_SUPPLIER)) {
            $acl->addRole(new Zend_Acl_Role(self::ROLE_SUPPLIER), Tools_Security_Acl::ROLE_GUEST);
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
		$acl->allow(self::ROLE_SUPPLIER, self::RESOURCE_CART);
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
        $acl->allow(self::ROLE_SALESPERSON, Tools_Security_Acl::RESOURCE_USERS);

		Zend_Registry::set('acl', $acl);
        Tools_System_Tools::firePluginMethodByTagName('salespermission', 'extendPermission');

        //self::getApiProducts(array());
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

            if (empty($this->_requestedParams['useOperationalHoursForOrders'])){
                $this->_requestedParams['useOperationalHoursForOrders'] = '0';
            }

            if ($form->isValid($this->_requestedParams)) {
				foreach ($form->getValues() as $key => $subFormValues) {
                    if (!empty($subFormValues['operationalHours'])) {
                        $subFormValues['operationalHours'] = serialize($subFormValues['operationalHours']);
                    }
                    if(!empty($subFormValues['outOfStock'])){
                        $subFormValues['outOfStock'] = strip_tags($subFormValues['outOfStock'], '<br><a><hr>');
                    }
                    if(!empty($subFormValues['limitQty'])){
                        $subFormValues['limitQty'] = strip_tags($subFormValues['limitQty'], '<br><a><hr>');
                    }

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
        $trackingUrlForm = new Forms_Shipping_TrackingUrl();

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

        $this->_view->trackingUrlForm = $trackingUrlForm;

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
     * @param $customParams custom params
	 * @return Models_Model_Customer
	 */
	public static function processCustomer($data, $customParams = array()) {
		$session = Zend_Controller_Action_HelperBroker::getExistingHelper('session');

		$customer = Tools_ShoppingCart::getInstance()->getCustomer();
		if (!$customer->getId()) {
			if (null === ($existingCustomer = Models_Mapper_CustomerMapper::getInstance()->findByEmail($data['email']))) {
                $prefix = isset($data['prefix']) ? $data['prefix'] : '';
                $fullname = isset($data['firstname']) ? $data['firstname'] : '';
                $fullname .= isset($data['lastname']) ? ' ' . $data['lastname'] : '';
                $mobilePhone = isset($data['mobile']) ? $data['mobile'] : '';
                $desktopPhone = isset($data['phone']) ? $data['phone'] : '';
                $mobileCountryCode = isset($data['mobilecountrycode']) ? $data['mobilecountrycode'] : '';
                $mobileCountryCodeValue = isset($data['mobile_country_code_value']) ? $data['mobile_country_code_value'] : null;
                $desktopCountryCode = isset($data['phonecountrycode']) ? $data['phonecountrycode'] : '';
                $desktopCountryCodeValue = isset($data['phone_country_code_value']) ? $data['phone_country_code_value'] : null;
                $shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
                $defaultMobilePhoneCountryCode = $shoppingConfig['country'];
                $subscribed = isset($data['subscribed']) ? $data['subscribed'] : '0';

                if (empty($desktopCountryCode)) {
                    $desktopCountryCode = $defaultMobilePhoneCountryCode;
                }

                $configHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('config');
                $userDefaultTimezone = $configHelper->getConfig('userDefaultTimezone');

                if (empty($data['timezone']) && !empty($userDefaultTimezone)) {
                    $customer->setTimezone($userDefaultTimezone);
                }
				if (!empty($data['customerPassword'])) {
                    $password = $data['customerPassword'];
                } else {
                    $password = md5(uniqid('customer_' . time()));
                }
                $customer->setRoleId(Shopping::ROLE_CUSTOMER)
						->setEmail($data['email'])
						->setFullName($fullname)
						->setIpaddress($_SERVER['REMOTE_ADDR'])
                        ->setMobilePhone($mobilePhone)
                        ->setMobileCountryCode($mobileCountryCode)
                        ->setMobileCountryCodeValue($mobileCountryCodeValue)
                        ->setDesktopPhone($desktopPhone)
                        ->setDesktopCountryCode($desktopCountryCode)
                        ->setDesktopCountryCodeValue($desktopCountryCodeValue)
						->setPassword($password)
                        ->setSubscribed($subscribed)
                        ->setPrefix($prefix);

                $defaultUserGroupId = intval(Models_Mapper_ShoppingConfig::getInstance()->getConfigParam(self::DEFAULT_USER_GROUP));

                if(!empty($defaultUserGroupId)) {
                    $customer->setGroupId($defaultUserGroupId);
                }

                $customerMapper = Models_Mapper_CustomerMapper::getInstance();
				$newCustomerId = $customerMapper->save($customer);

				if (!empty($customParams)) {
                    foreach ($customParams as $paramName => $paramLabel) {
                        $customer->setAttribute($paramName, $data[$paramName]);
                    }
                    Application_Model_Mappers_UserMapper::getInstance()->saveUserAttributes($customer);
                }

				if ($newCustomerId) {
                    $customParamsAssignment = false;
				    if (!empty($customParams)) {
                        $preparedCustomParams = array();
                        foreach ($customParams as $paramName => $paramLabel) {
                            $preparedCustomParams[$paramName] = $data[$paramName];
                        }
                        $processCustomParamsResult = Tools_GroupAssignment::processGroupsByUserCustomParams($newCustomerId, $preparedCustomParams);
                        if (!empty($processCustomParamsResult) && empty($processCustomParamsResult['error'])) {
                            $customParamsAssignment = true;
                        }
                        $customerInfo = $customerMapper->find($newCustomerId);
                        $groupId = $customerInfo->getGroupId();
                        if (!empty($groupId)) {
                            $customer->setGroupId($groupId);
                        }
                    }

//					Tools_ShoppingCart::getInstance()->setCustomerId($newCustomerId)->save();
					$customer->setId($newCustomerId);
					$session->storeIsNewCustomer = true;
                    if (!empty($data['customerPassword'])) {
                        $session->clientWithNewPassword = true;
                    } elseif(isset($session->clientWithNewPassword)) {
                        unset($session->clientWithNewPassword);
                    }

                    if(!empty($defaultUserGroupId) || $customParamsAssignment === true){
                        $userMapper = Application_Model_Mappers_UserMapper::getInstance();
                        $userModel = $userMapper->find($newCustomerId);

                        if($userModel instanceof Application_Model_Models_User) {
                            $userModel->setLastLogin(date(Tools_System_Tools::DATE_MYSQL));

                            $userMapper->save($userModel);

                            $session->setCurrentUser($userModel);
                            Zend_Session::regenerateId();
                            $cacheHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('cache');
                            $cacheHelper->clean();
                        }
                    }

				} elseif(isset($session->clientWithNewPassword)) {
                    unset($session->clientWithNewPassword);
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

    public function fetchShippingUrlNamesAction(){
        $shippingUrlMapper = Models_Mapper_ShoppingShippingUrlMapper::getInstance();
        $trackingData = $shippingUrlMapper->fetchAll();
        $defaultSelection = $shippingUrlMapper->findDefaultStatus();
        $arrData = array($this->_translator->translate('Custom Shipper'));
        $arrDataDefault = $arrData;
        if($defaultSelection instanceof Models_Model_ShippingUrl) {
            $arrDataDefault = array($defaultSelection->getName() => $defaultSelection->getDefaultStatus());
        }

        $orderId = $this->_request->getParam('orderId');
        $order = Models_Mapper_CartSessionMapper::getInstance()->find($orderId);

        $shippingTrackingCodeId = '';
        $shippingTrackingId = '';
        $trackingName = '';
        if($order instanceof Models_Model_CartSession) {
            $shippingTrackingCodeId = $order->getShippingTrackingCodeId();
            $shippingTrackingId = $order->getShippingTrackingId();

            if(!empty($shippingTrackingId)) {
                $trackingName = $shippingTrackingId;
            }
        }

        $shippingConfigMapper = Models_Mapper_ShippingConfigMapper::getInstance();
        $trackingurlConfig = $shippingConfigMapper->find(self::SHIPPING_TRACKING_URL);

        if(!empty($trackingData) && !empty($trackingurlConfig['enabled'])) {
            foreach ($trackingData as $dataValue) {
                if($dataValue['id'] == $shippingTrackingCodeId) {
                    $trackingName = str_replace($dataValue['url'], '', $shippingTrackingId);
                }
                $arrData[$dataValue['id']] = $dataValue['name'];
            }

        }
        return  $this->_responseHelper->success(array('data' => $arrData, 'defaultSelection' => $arrDataDefault, 'shippingTrackingCodeId' => $shippingTrackingCodeId, 'trackingName' => $trackingName));
    }


    public function setShippingUrlDataAction()
    {
        if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT) && $this->_request->isPost()) {
            $data = $this->_request->getParams();
            $data = array_map("trim", $data);

            $tokenToValidate = $data['secureToken'];
            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                exit;
            }

            $shippingUrlMapper = Models_Mapper_ShoppingShippingUrlMapper::getInstance();

            if (empty($data['trackingName'])) {
                return $this->_responseHelper->fail('Required parameters is missing');
            }
            $update = false;
            $msg = $this->_translator->translate('Saved');
            $shippingUrlModel = $shippingUrlMapper->find($data['currentId']);
            if ($shippingUrlModel) {
                $shippingUrlModel->setId($data['currentId']);
                $shippingUrlModel->setDefaultStatus(0);
                $update = true;
                $msg = $this->_translator->translate('Updated');
            } else {
                $findCurrentName = $shippingUrlMapper->findByName($data['trackingName']);
                if($findCurrentName instanceof Models_Model_ShippingUrl){
                    $this->_responseHelper->fail(array('msg' => 'This name is already exists, please select proper name from the dropdown!'));
                }
                $shippingUrlModel = new Models_Model_ShippingUrl();
                $shippingUrlModel->setDefaultStatus(0);

            }
            $shippingUrlModel->setName($data['trackingName']);
            $shippingUrlModel->setUrl($data['url']);
            $shippingUrlModel = $shippingUrlMapper->save($shippingUrlModel);

            $this->_responseHelper->success(array(
                'msg' => $this->_translator->translate($msg),
                'optionUpdateStatus' => $update,
                'optionName' => $shippingUrlModel->getName(),
                'optionId' => $shippingUrlModel->getId()
            ));
        }
        $this->_responseHelper->fail('');
    }

    public function getShippingUrlDataAction()
    {
        if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT) && $this->_request->isPost()) {
            $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                $this->_responseHelper->fail('');
            }
            $id = filter_var($this->_request->getParam('id'), FILTER_SANITIZE_NUMBER_INT);
            if (!empty($id)) {
                $shippingUrlMapper = Models_Mapper_ShoppingShippingUrlMapper::getInstance();
                $currentData = $shippingUrlMapper->find($id);
                if ($currentData instanceof Models_Model_ShippingUrl) {
                    $this->_responseHelper->success(array(
                        'name' => $currentData->getName(),
                        'url' => $currentData->getUrl(),
                        'current' => $currentData->getId()
                    ));
                }
            }
        }
        $this->_responseHelper->fail('');

    }

    public function deleteShippingUrlDataAction()
    {
        if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT) && $this->_request->isDelete()) {
            $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                exit;
            }

            $id = filter_var($this->_request->getParam('selectId'), FILTER_SANITIZE_NUMBER_INT);
            if (!empty($id)) {
                $shippingUrlMapper = Models_Mapper_ShoppingShippingUrlMapper::getInstance();
                $current = $shippingUrlMapper->find($id);
                if (!empty($current)) {
                    $shippingUrlMapper->delete($current);
                    $this->_responseHelper->success(array(
                        'msg' => $this->_translator->translate('Deleted'),
                        'optionId' => $current->getId()
                    ));
                }
            }
        }
        $this->_responseHelper->fail('');
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

            $this->_view->brands = Models_Mapper_Brand::getInstance()->getAllBrands();

			$listFolders = Tools_Filesystem_Tools::scanDirectoryForDirs($this->_websiteConfig['path'] . $this->_websiteConfig['media']);
			if (!empty ($listFolders)) {
                $listFolders = $this->_processNotEmptyDirs($listFolders);
                $dolderDefaultSelectName = $this->_translator->translate('select folder');
				$listFolders = array($dolderDefaultSelectName) + array_combine($listFolders, $listFolders);
			}
			$this->_view->imageDirList = $listFolders;

            $plugins = array();
            $pluginsToReorder = array();
            $configTabs = Tools_Misc::$_productConfigTabs;
            $excludeProductTabs = array();
            if (!empty($this->_view->generalConfig['excludeProductTabs'])) {
                $excludeProductTabs = explode(',', $this->_view->generalConfig['excludeProductTabs']);
                foreach ($configTabs as $key => $configTab) {
                    if (in_array($configTab['tabId'], $excludeProductTabs)) {
                        unset($configTabs[$key]);
                    }
                }
            }

            $this->_view->excludeProductTabs = $excludeProductTabs;

            foreach (Tools_Plugins_Tools::getPluginsByTags(array('ecommerce')) as $plugin) {
				if ($plugin->getTags() && in_array('merchandising', $plugin->getTags())) {
					array_push($plugins, $plugin->getName());
				}
			}

			if ($this->_request->has('id')) {
				$id = filter_var($this->_request->getParam('id'), FILTER_VALIDATE_INT);
				if ($id) {
					$product = Models_Mapper_ProductMapper::getInstance()->find($id);
                    $productPrice = $product->getPrice();

                    if(!empty($productPrice)) {
                        if(fmod($productPrice, 1) !== 0.00){
                            $processindPrice = (float) $productPrice;
                            $explodeDigits = explode('.', $processindPrice);

                            $countNum = mb_strlen($explodeDigits[1]);

                            $productPrice = $processindPrice;
                            if($countNum == 1) {
                                $productPrice = number_format($processindPrice, 2, ".", "");
                            }
                        } else {
                            $productPrice = (int) $productPrice;
                        }

                        $product->setPrice($productPrice);

                        $companyProductsMapper = Store_Mapper_CompanyProductsMapper::getInstance();
                        $savedCompanies = $companyProductsMapper->getColByProductIds(array($product->getId()));

                        if(!empty($savedCompanies)) {
                            $product->setCompanyProducts($savedCompanies);
                        }

                        $this->_view->product = $product;
                    }
				}
			}

            if (!empty($plugins)) {
                foreach ($plugins as $plugin) {
                    $pluginClass = new Zend_Reflection_Class(ucfirst(strtolower($plugin)));
                    $title = $pluginClass->hasConstant('DISPLAY_NAME') ? $pluginClass->getConstant('DISPLAY_NAME') : ucfirst($plugin);
                    if (!$pluginClass->hasMethod('tabAction')) {
                        continue;
                    }
                    if ($pluginClass->hasConstant('TAB_ORDER')) {
                        $pluginsToReorder[] = array('tabId' => $plugin, 'tabName' => $title, 'type' => 'external', 'tabOrderId' => $pluginClass->getConstant('TAB_ORDER'));
                    } else {
                        $configTabs[] = array('tabId' => $plugin, 'tabName' => $title, 'type' => 'external');
                    }
                }
            }

            if (!empty($pluginsToReorder)) {
                foreach ($pluginsToReorder as $pluginOrder) {
                    $elementsAfterPosition = array_slice($configTabs, $pluginOrder['tabOrderId'] -1);
                    $elementsBeforePosition = array_slice($configTabs, 0, $pluginOrder['tabOrderId'] -1);
                    $elementsBeforePosition[] = array('tabId' => $pluginOrder['tabId'], 'tabName' => $pluginOrder['tabName'], 'type' => 'external');
                    $configTabs = array_merge($elementsBeforePosition, $elementsAfterPosition);
                }
            }

            $this->_view->plugins = $plugins;
			$this->_view->websiteConfig = $this->_websiteConfig;
            $this->_view->configTabs = $configTabs;
            $configParamsData = Store_Mapper_ProductCustomFieldsOptionsDataMapper::getInstance()->getCustomParamsOptionsDataConfig(array('custom_param_id ASC'));
            if (!empty($configParamsData)) {
                $configParamsData = Tools_CustomParamsTools::prepareCustomParamsOptions($configParamsData);
            } else {
                $configParamsData = array();
            }
            $this->_view->productCustomParamsConfig = Store_Mapper_ProductCustomFieldsConfigMapper::getInstance()->getCustomParamsConfig();
            $this->_view->productCustomParamsOptions = $configParamsData;

            $this->_view->helpSection = Tools_Misc::SECTION_STORE_ADDEDITPRODUCT;
            $defaultTaxes = Models_Mapper_Tax::getInstance()->getDefaultRule();
            $this->_view->defaultTaxes = $defaultTaxes;

            $companyMapper = Store_Mapper_CompaniesMapper::getInstance();
            $this->_view->companies = $companyMapper->fetchAll();

            $this->_layout->content = $this->_view->render('product.phtml');
			echo $this->_layout->render();
		}
	}

    /**
     * Check medias product folder
     * @param $folders
     * @return mixed
     */
    protected function _processNotEmptyDirs($folders)
    {
        foreach ($folders as $key => $folder) {
            $listFolders = Tools_Filesystem_Tools::scanDirectoryForDirs($this->_websiteConfig['path'] . $this->_websiteConfig['media'] . $folder);
            if (!empty($listFolders) && in_array('product', $listFolders)) {
                $files = Tools_Filesystem_Tools::scanDirectory($this->_websiteConfig['path'] . $this->_websiteConfig['media'] . $folder . DIRECTORY_SEPARATOR . 'product',
                    false, false);
                if (empty($files)) {
                    unset($folders[$key]);
                }
            } else {
                unset($folders[$key]);
            }
        }

        return $folders;
    }

    public function searchindexAction() {
        $searchTerm = filter_var($this->_request->getParam('searchTerm'), FILTER_SANITIZE_STRING);
        $data = Models_Mapper_ProductMapper::getInstance()->buildIndex($searchTerm);

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
        $dragListId = filter_var($this->_request->getParam('draglist_id'), FILTER_SANITIZE_STRING);
        $filterable = filter_var($this->_request->getParam('filterable'), FILTER_SANITIZE_STRING);
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
        $sort = $this->_request->getParam('sort');
        $offset = intval($nextPage) * $limit;
        $useUserOrder = filter_var($this->_request->getParam('useUserOrder'), FILTER_VALIDATE_BOOLEAN);

        $productMapper = Models_Mapper_ProductMapper::getInstance();
        if (empty($dragListId) || $useUserOrder) {
            $products = $productMapper->fetchAll("p.enabled='1'", $order, $offset, $limit,
                null, $tags, $brands, false, false, $attributes, $price, $sort);
        } else {
            $dragMapper = Models_Mapper_DraggableMapper::getInstance();
            $dragModel = $dragMapper->find($dragListId);
            if ($dragModel instanceof Models_Model_Draggable) {
                $dragList['list_id'] = $dragModel->getId();
                $dragList['data'] = unserialize($dragModel->getData());

                if(!empty($attributes) || !empty($tags) || !empty($brands) || !empty($price)) {
                    $productsToSort = $productMapper->fetchAll($productMapper->getDbTable()->getAdapter()->quoteInto('p.enabled = ?', '1'), null, null, null,
                        null, $tags, $brands, false, false, $attributes, $price, null);

                    if(!empty($productsToSort)) {
                        $dragListDataSorted = array();

                        foreach ($productsToSort as $key => $product) {
                            $searchKey  = array_search($product->getId(), $dragList['data']);

                            if(in_array($product->getId(), $dragList['data'])) {
                                $dragListDataSorted[$searchKey] = $product->getId();
                            }
                        }

                        if(!empty($dragListDataSorted)) {
                            ksort($dragListDataSorted);

                            $dragListDataSorted = array_values($dragListDataSorted);
                            $dragList['data'] = $dragListDataSorted;
                        }
                    }
                }

                $currentProductsId = array();

                for ($i = $offset; $i < ($offset + $limit); $i++) {
                    if (count($dragList['data']) > $offset && isset($dragList['data'][$i])) {
                        $currentProductsId[] = $dragList['data'][$i];
                    }
                }

                if (!empty($currentProductsId)) {
                    $where = $productMapper->getDbTable()->getAdapter()->quoteInto('p.id IN (?)',
                        $currentProductsId);
                    $where .= ' AND ' . $productMapper->getDbTable()->getAdapter()->quoteInto('p.enabled = ?', '1');

                    $productsListData = $productMapper->fetchAll($where, $order, null, null, null, null, null, false, false, array(), array(), $sort) ;

                    $productsListDataSorted = array();
                    foreach ($productsListData as $key => $product) {
                        $currentProductRightOrder = array_search($product->getId(), $currentProductsId);
                        $productsListDataSorted[$currentProductRightOrder] = $product;
                    }

                    ksort($productsListDataSorted);

                    $productsListData = $productsListDataSorted;

                    $productsListDataResult = array();

                    foreach ($productsListData as $product) {
                        $prodId = $product->getId();
                        if(in_array($prodId, $currentProductsId)) {
                            $productsListDataResult[] = $product;
                        }
                    }
                    $products = $productsListDataResult;
                }
            }
        }

        $tagsPart = (!empty($tags) && is_array($tags)) ? implode(',', $tags) : '';

        if (!empty($products)) {
			$template = $this->_request->getParam('template');
            if (!empty($productsListDataResult)) {
                $widget = Tools_Factory_WidgetFactory::createWidget('productlist', array($template, $offset + $limit, md5(filter_var($this->_request->getParam('pageId'), FILTER_SANITIZE_NUMBER_INT) . $tagsPart), Widgets_Productlist_Productlist::OPTION_DRAGGABLE));
            } elseif ($useUserOrder) {
                $widget = Tools_Factory_WidgetFactory::createWidget('productlist', array($template, $offset + $limit, md5(filter_var($this->_request->getParam('pageId'), FILTER_SANITIZE_NUMBER_INT) . $tagsPart), $filterable, Widgets_Productlist_Productlist::OPTION_USER_ORDER));
            } else {
                $widget = Tools_Factory_WidgetFactory::createWidget('productlist', array($template, $offset + $limit, md5(filter_var($this->_request->getParam('pageId'), FILTER_SANITIZE_NUMBER_INT) . $tagsPart), $filterable));
            }

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
            } elseif ($option == 'state') {
                $stateId = $config[$option];
                $option = Tools_Geo::getStateById($stateId)['name'];
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
            $listMasksMapper = Application_Model_Mappers_MasksListMapper::getInstance();
            $this->_view->mobileMasks = $listMasksMapper->getListOfMasksByType(Application_Model_Models_MaskList::MASK_TYPE_MOBILE);
            $this->_view->phoneCountryCodes = Tools_System_Tools::getFullCountryPhoneCodesList(true, array(), true);
            $attributes = Application_Model_Mappers_UserMapper::getInstance();
            $query = $attributes->getDbTable()->getAdapter()->select()->distinct()->from('user_attributes', array('attribute'))->where('attribute LIKE ?', 'customer_%');
            $customerAttributes = $attributes->getDbTable()->getAdapter()->fetchCol($query);
            foreach ($customerAttributes as $key => $attrName) {
                $customerAttributes[$key] = preg_replace('`customer_`', '', $attrName);
            }
            $this->_view->customerAttributes = $customerAttributes;
            $this->_view->superAdmin = Tools_ShoppingCart::getInstance()->getCustomer()->getRoleId() === Tools_Security_Acl::ROLE_SUPERADMIN;
            $this->_view->shoppingConfigParams = $this->_configMapper->getConfigParams();

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
            $shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();

			$this->_view->brands = Models_Mapper_Brand::getInstance()->fetchAll();
			$this->_view->tags = Models_Mapper_Tag::getInstance()->fetchAll();
			$this->_view->currency = Zend_Registry::isRegistered('Zend_Currency') ? Zend_Registry::get('Zend_Currency') : new Zend_Currency();
			$this->_view->currencyUnit = $shoppingConfig['currency'];
            $productsData = Models_Mapper_ProductMapper::getInstance()->getProductsInventory();

            if(!empty($productsData['inventory'])){
                $inventory =  explode(',' , $productsData['inventory']);
                sort($inventory, SORT_NUMERIC);
                $this->_view->inventory = $inventory;
            }

            $enablePromoPlugin = false;

            $enabledPromoPlugin = Application_Model_Mappers_PluginMapper::getInstance()->findByName('promo');
            if ($enabledPromoPlugin != null) {
                if ($enabledPromoPlugin->getStatus() == 'enabled') {
                    $enablePromoPlugin = true;
                }
            }

            $this->_view->promoPlugin = $enablePromoPlugin;


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
        $this->_view->userPrefixes  = Tools_ShoppingCart::$userPrefixes;
        if($customerAddress) {
            $this->_view->customerAddress = $customerAddress;
        }

        if (!$customer instanceof Models_Model_Customer) {
            $this->_responseHelper->fail('customer doesn\'t exist');
        }

        $userEmail = $customer->getEmail();

        $leadsPlugin = Application_Model_Mappers_PluginMapper::getInstance()->findByName('leads');
        if ($leadsPlugin instanceof Application_Model_Models_Plugin) {
            $leadsPluginStatus = $leadsPlugin->getStatus();

            if ($leadsPluginStatus === 'enabled') {
                $leadMapper = Leads_Mapper_LeadsMapper::getInstance();
                $leadModel = $leadMapper->findByEmail($userEmail);

                if($leadModel instanceof Leads_Model_LeadsModel) {
                    $websiteHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('website');
                    $websiteUrl = $websiteHelper->getUrl();

                    $leadLink = $websiteUrl.'dashboard/leads/#lead/'.$leadModel->getId();

                    $this->_view->leadLink = $leadLink;
                }
            }
        }

		if ($customer) {
			$this->_view->customer = $customer;
            $userRole = filter_var($this->_request->getParam('userRole'), FILTER_SANITIZE_STRING);
            if ($userRole === Shopping::ROLE_SUPPLIER) {
                $this->_view->supplier = true;
                $this->_responseHelper->success($this->_view->render('profile.phtml'));
            }
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
            $serviceLabelMapper = Models_Mapper_ShoppingShippingServiceLabelMapper::getInstance();
            $shippingServiceLabels = $serviceLabelMapper->fetchAllAssoc();
			if(!empty($orders) && !empty($shippingServiceLabels)){
                foreach ($orders as $index => $order) {
                    if (isset($shippingServiceLabels[$order->getShippingService()])) {
                        $orders[$index]->setShippingService($shippingServiceLabels[$order->getShippingService()]);
                    }
                }
            }
			$this->_view->orders = $orders;

            $userMapper = Application_Model_Mappers_UserMapper::getInstance();

            $currentUserModel = $userMapper->find($customer->getId());

            $userAttributes = array();
            if($currentUserModel instanceof Application_Model_Models_User) {
                $userMapper->loadUserAttributes($currentUserModel);

                $userAttributes = $currentUserModel->getAttributes();
            }

            $this->_view->userAttributes = $userAttributes;

            $allGroups = Store_Mapper_GroupMapper::getInstance()->fetchAll();
            $this->_view->allGroups = $allGroups;
		}

		$enabledInvoicePlugin = Application_Model_Mappers_PluginMapper::getInstance()->findByName('invoicetopdf');
		if ($enabledInvoicePlugin != null) {
			if ($enabledInvoicePlugin->getStatus() == 'enabled') {
				$this->_view->invoicePlugin = 1;
			}
		}

        $this->_view->phoneCountryCodes = Tools_System_Tools::getFullCountryPhoneCodesList(true, array(), true);

        $listMasksMapper = Application_Model_Mappers_MasksListMapper::getInstance();
        $this->_view->mobileMasks = $listMasksMapper->getListOfMasksByType(Application_Model_Models_MaskList::MASK_TYPE_MOBILE);
        $this->_view->desktopMasks = $listMasksMapper->getListOfMasksByType(Application_Model_Models_MaskList::MASK_TYPE_DESKTOP);

		$content = $this->_view->render('profile.phtml');

		if ($this->_request->isXmlHttpRequest()) {
			echo $content;
		} else {
			$this->_layout->content = '<div id="profile" class="toaster-widget bg-content">' . $content . '</div>';
			echo $this->_layout->render();
		}
	}

	public function changeOrderStatusAction()
    {
        if (!Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT)) {
            throw new Exceptions_SeotoasterPluginException('Not allowed action');
        }
        $tokenToValidate = $this->_request->getParam('secureToken', false);
        $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
        if (!$valid) {
            exit;
        }

        $orderId = $this->_request->getParam('orderId', false);
        if ($orderId) {
            $order = Models_Mapper_CartSessionMapper::getInstance()->find($orderId);
            if ($order instanceof Models_Model_CartSession) {
                $isFirstPaymentManuallyPaid = $order->getIsFirstPaymentManuallyPaid();
                if (!empty($isFirstPaymentManuallyPaid)) {
                    $order->setIsFullOrderManuallyPaid('1');
                }
                $order->setStatus(Models_Model_CartSession::CART_STATUS_COMPLETED);
                $order->setGateway(Models_Model_CartSession::MANUALLY_PAYED_GATEWAY_QUOTE);
                $order->setSecondPartialPaidAmount(round($order->getTotal() - $order->getFirstPartialPaidAmount(), 2));
                $order->setSecondPaymentGateway(Models_Model_CartSession::MANUALLY_PAYED_GATEWAY_QUOTE);
                $order->setIsSecondPaymentManuallyPaid('1');
                Models_Mapper_CartSessionMapper::getInstance()->save($order);
                $this->_responseHelper->success( $this->_translator->translate('Status has been changed'));
            }
        }

    }

	public function orderAction() {
		$id = isset($this->_requestedParams['id']) ? filter_var($this->_requestedParams['id'], FILTER_VALIDATE_INT) : false;
		if ($id) {
			$order = Models_Mapper_CartSessionMapper::getInstance()->find($id, true);
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
                $currenttrackingUrlId = '';
                $order->registerObserver(new Tools_InventoryObserver($order->getStatus()));
                $order->registerObserver(new Tools_SupplierObserver($order->getStatus()));
                $params = filter_var_array($this->_request->getPost(), FILTER_SANITIZE_STRING);
                if (isset($params['shippingTrackingId'])) {
                    $shippingUrlMapper = Models_Mapper_ShoppingShippingUrlMapper::getInstance();
                    $selectedName = '';
                    $url = '';
                    $paramData = $params['shippingTrackingId'];
                    $currentData = '';
                    $shippingUrlMapper->clearDefaultStatus();
                    if (!empty($params['trackingUrlId'])) {
                        $currentData = $shippingUrlMapper->find($params['trackingUrlId']);
                        if ($currentData instanceof Models_Model_ShippingUrl) {
                            $selectedName = $currentData->getName();
                            $url = $currentData->getUrl();
                            $currentData->setDefaultStatus(1);
                            $shippingUrlData = $shippingUrlMapper->save($currentData);

                            $currenttrackingUrlId = $shippingUrlData->getId();
                        }
                    }
                    unset($params['trackingUrlId'], $params['id']);
                    if ($currentData instanceof Models_Model_ShippingUrl) {
                        $params['shippingTrackingId'] = trim($currentData->getUrl()) . trim($paramData);
                    }
                    $order->registerObserver(new Tools_Mail_Watchdog(array(
                        'trigger' => Tools_StoreMailWatchdog::TRIGGER_SHIPPING_TRACKING_NUMBER,
                        'name' => $selectedName,
                        'code' => $paramData,
                        'url' => $url
                    )));
                    $params['status'] = Models_Model_CartSession::CART_STATUS_SHIPPED;

                    $params['shippingTrackingCodeId'] = $currenttrackingUrlId;
                }

                if (isset($params['pickupNotification'])) {
                    $order->registerObserver(new Tools_Mail_Watchdog(array(
                        'trigger' => Tools_StoreMailWatchdog::TRIGGER_SHIPPING_PICKUP_NOTIFICATION,
                    )));

                    if ($order->getStatus() !== Models_Model_CartSession::CART_STATUS_SHIPPED) {
                        $params['status'] = Models_Model_CartSession::CART_STATUS_SHIPPED;
                    }
                    $params['pickup_notification_sent_on'] = Tools_System_Tools::convertDateFromTimezone('now');
                    $params['is_pickup_notification_sent'] = '1';
                }

                if (!empty($params['status']) && $params['status'] === Models_Model_CartSession::CART_STATUS_DELIVERED) {
                    $order->registerObserver(new Tools_Mail_Watchdog(array(
                        'trigger' => Tools_StoreMailWatchdog::TRIGGER_DELIVERED
                    )));
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
            $serviceLabelMapper = Models_Mapper_ShoppingShippingServiceLabelMapper::getInstance();
            $shippingServiceLabel = $serviceLabelMapper->findByName($order->getShippingService());
            if (!empty($shippingServiceLabel)) {
                $this->_view->shippingServiceLabel = $shippingServiceLabel;
            }

            $quoteId = '';
            $quoteTitle = '';
            $orderId = $order->getId();
            $quoteEnabled = Tools_Plugins_Tools::findPluginByName('quote');
            if ($quoteEnabled->getStatus() == Application_Model_Models_Plugin::ENABLED) {
                $quoteMapper = Quote_Models_Mapper_QuoteMapper::getInstance();
                $quoteModel = $quoteMapper->findByCartId($orderId);
                if ($quoteModel instanceof Quote_Models_Model_Quote) {
                    $quoteId = $quoteModel->getId();
                    $quoteTitle = $quoteModel->getTitle();

                    $quoteDraggableMapper = Quote_Models_Mapper_QuoteDraggableMapper::getInstance();
                    $quoteDraggableModel = $quoteDraggableMapper->findByQuoteId($quoteId);

                    if($quoteDraggableModel instanceof Quote_Models_Model_QuoteDraggableModel) {
                        $dragOrder = $quoteDraggableModel->getData();

                        if(!empty($dragOrder)) {
                            $dragOrder = explode(',', $dragOrder);
                            $cartContent = $order->getCartContent();
                            $prepareContentSids = array();
                            foreach ($cartContent as $key => $content) {
                                $product = Models_Mapper_ProductMapper::getInstance()->find($content['product_id']);
                                $options = ($content['options']) ? $content['options'] : Quote_Tools_Tools::getProductDefaultOptions($product);
                                $prodSid = Quote_Tools_Tools::generateStorageKey($product, $options);
                                $prepareContentSids[$prodSid] = $content;
                            }

                            $sortedCartContent = array();
                            foreach ($dragOrder as $productSid) {
                                if(!empty($prepareContentSids[$productSid])) {
                                    $sortedCartContent[$productSid] = $prepareContentSids[$productSid];
                                }
                            }
                            $preparedCartContent = array_merge($sortedCartContent, $prepareContentSids);

                            $cartContent = array();

                            foreach ($preparedCartContent as $cContent) {
                                $cartContent[] = $cContent;
                            }

                            $order->setCartContent($cartContent);
                        }
                    }

                }
            }

            $this->_view->quoteId = $quoteId;
            $this->_view->quoteTitle = $quoteTitle;

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
                                'gmapsZoom' => $pickupLocationConfig['gmapsZoom'],
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
				if (!isset($this->_sessionHelper->clientWithNewPassword)) {
                    $userMapper = Application_Model_Mappers_UserMapper::getInstance();
                    $userData = $userMapper->find($cartSession->getUserId());
                    $newCustomerPassword = uniqid('customer_' . time());
                    $userData->setPassword($newCustomerPassword);
                    $userMapper->save($userData);
                }
				$customer = Models_Mapper_CustomerMapper::getInstance()->find($cartSession->getUserId());
                if (!isset($this->_sessionHelper->clientWithNewPassword)) {
                    $customer->setPassword($newCustomerPassword);
                }
				$customer->registerObserver(new Tools_Mail_Watchdog(array(
					'trigger' => Tools_StoreMailWatchdog::TRIGGER_NEW_CUSTOMER
				)));
				$customer->notifyObservers();
			}
			$cartSession = Models_Mapper_CartSessionMapper::getInstance()->find($cartId);

			$userId = $cartSession->getUserId();
			$cartStatus = $cartSession->getStatus();
            $cartContent = $cartSession->getCartContent();

            if(!empty($userId) && $cartStatus == Models_Model_CartSession::CART_STATUS_COMPLETED) {
                $notifiedProductsMapper = Store_Mapper_NotifiedProductsMapper::getInstance();

                if(!empty($cartContent)) {
                    $productMapper = Models_Mapper_ProductMapper::getInstance();

                    foreach ($cartContent as $cContent) {
                        $productId = $cContent['product_id'];

                        $product = $productMapper->find($productId);
                        $productNegativeStock = $product->getNegativeStock();

                        if(($product->getInventory() == '0' || $product->getInventory() < '0') && empty($productNegativeStock)) {
                            $currentNotifiedProduct = $notifiedProductsMapper->findByUserIdProductId($userId, $productId);

                            if($currentNotifiedProduct instanceof Store_Model_NotifiedProductsModel && $currentNotifiedProduct->getSendNotification() == '1') {
                                $notifiedProductsMapper->delete($currentNotifiedProduct);
                            }

                            $where = $notifiedProductsMapper->getDbTable()->getAdapter()->quoteInto("product_id = ?", $productId);
                            $allOtherNotifiedProducts = $notifiedProductsMapper->fetchAll($where);

                            if(!empty($allOtherNotifiedProducts)) {
                                foreach ($allOtherNotifiedProducts as $notifiedProduct) {
                                    if($notifiedProduct->getSendNotification() == '1') {
                                        $notifiedProduct->setSendNotification('0');

                                        $notifiedProductsMapper->save($notifiedProduct);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($cartSession->getStatus() !== Models_Model_CartSession::CART_STATUS_PARTIAL) {
                $cartSession->registerObserver(new Tools_Mail_Watchdog(array(
                    'trigger' => Tools_StoreMailWatchdog::TRIGGER_NEW_ORDER
                )));
            }

            $shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();

            if (!empty($shoppingConfig[Shopping::SHIPPING_IS_GIFT])){
                if (!empty($cartSession->getIsGift())) {
                    $cartSession->registerObserver(new Tools_Mail_Watchdog(array(
                        'trigger' => Tools_StoreMailWatchdog::TRIGGER_STORE_GIFT_ORDER
                    )));
                }
            }

            if ($cartSession->getStatus() === Models_Model_CartSession::CART_STATUS_PARTIAL) {
                $cartSession->registerObserver(new Tools_Mail_Watchdog(array(
                    'trigger' => Tools_StoreMailWatchdog::TRIGGER_STORE_PARTIALPAYMENT
                )));
            }

            if ($cartSession->getStatus() !== Models_Model_CartSession::CART_STATUS_PARTIAL) {
                if (class_exists('Tools_AppsServiceWatchdog')) {
                    $cartSession->registerObserver(new Tools_AppsServiceWatchdog());
                }
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

            $plugins = array();
            $pluginsToReorder = array();
            $configTabs = Tools_Misc::$_merchandisingConfigTabs;
			foreach (Tools_Plugins_Tools::getPluginsByTags(array('ecommerce')) as $plugin) {
				$tags = $plugin->getTags();
				if (!empty($tags) && in_array('merchandising', $tags)) {
					array_push($plugins, $plugin->getName());
				}
				unset($tags);
			}

            if (!empty($plugins)) {
                foreach ($plugins as $plugin) {
                    $pluginClass = new Zend_Reflection_Class(ucfirst(strtolower($plugin)));
                    $title = $pluginClass->hasConstant('DISPLAY_NAME') ? $pluginClass->getConstant('DISPLAY_NAME') : ucfirst($plugin);
                    if ($pluginClass->hasConstant('WITHOUT_TAB')) {
                        continue;
                    }
                    if ($pluginClass->hasConstant('TAB_MERCHANDISE_ORDER')) {
                        $pluginsToReorder[] = array('tabId' => $plugin, 'tabName' => $title, 'type' => 'external', 'tabOrderId' => $pluginClass->getConstant('TAB_MERCHANDISE_ORDER'));
                    } else {
                        $configTabs[] = array('tabId' => $plugin, 'tabName' => $title, 'type' => 'external');
                    }
                }
            }

            if (!empty($pluginsToReorder)) {
                $configTabs = Tools_Misc::reorderPluginTabs($pluginsToReorder, $configTabs);
            }

            $this->_view->configTabs = $configTabs;
            $this->_view->plugins = $plugins;
            $this->_view->helpSection = Tools_Misc::SECTION_STORE_MERCHANDISING;
            $defaultUserGroupId = intval(Models_Mapper_ShoppingConfig::getInstance()->getConfigParam(Shopping::DEFAULT_USER_GROUP));
            $this->_view->defaultGroupId = $defaultUserGroupId;
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
					    if(in_array(Tools_CouponTools::STATUS_FAIL_ONE_TIME_USED, $status)) {
                            $defaultErrorMessage = $this->_translator->translate('Sorry, some coupon codes you provided had already been used.') . '</br>' . $this->_translator->translate('Go back to swap promo codes or proceed with shipping information to checkout.');
                        }

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
                        $this->_responseHelper->fail($this->_translator->translate('User with this email already exists'));
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
                $this->_responseHelper->success(array('message'=>$this->_translator->translate('Your account information has been updated'), 'email'=> $data['newEmail']));
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
                            $validator = new Tools_System_CustomEmailValidator();
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
                                $this->_responseHelper->fail($this->_translator->translate('User with this email already exists'));
                            }
                        break;
                        case 'prefix':
                            $user->setPrefix($data['profileValue']);
                            break;
                        case 'signature':
                            $user->setSignature($data['profileValue']);
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
                    $user->setPassword(null);
                    $userMapper->save($user);
                    $updateUserInfoStatus = Tools_System_Tools::firePluginMethodByTagName('userupdate', 'updateUserInfo', $user->getId());

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
        $this->_view->countries = Tools_Geo::getCountries(true);
        $this->_view->defaultCountries = Zend_Locale::getTranslationList('territory', 'en_GB', 2);
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
     * Check if this digital product was sold
     */
    public function checkDigitalProductUsageAction()
    {
        if ($this->_request->isPost() && Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)) {
            $productId = filter_var($this->_request->getParam('productId', false), FILTER_SANITIZE_NUMBER_INT);
            if ($productId) {

                $productSold = Store_Mapper_DigitalProductMapper::getInstance()->checkDigitalProductInCart($productId);
                $sold = false;
                if (!empty($productSold)) {
                    $sold = true;
                }
                $this->_responseHelper->success(array('productSold' => $sold));
            }
            $this->_responseHelper->fail('Product id missed');
        }
        $this->_responseHelper->fail('not authorized');
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
            if(!empty($data['fieldName']) && !empty($data['userId'])){
                $customerToken = '';
                $countries = Zend_Locale::getTranslationList('territory', null, 2);
                $cartSessionMapper = Models_Mapper_CartSessionMapper::getInstance();
                $customerTable = new Models_DbTable_CustomerAddress();

                $data['fieldValue'] = trim($data['fieldValue']);
                if(empty($data['clientToken'])){
                    $this->_responseHelper->fail();
                }
                $customerMapper = Models_Mapper_CustomerMapper::getInstance();
                $currentCustomer = $customerMapper->find($data['userId']);
                $customerAddress = $customerMapper->getUserAddressByUserId($data['userId'], $data['clientToken']);
                if(!empty($customerAddress)) {
                    foreach ($customerAddress as $value) {
                        if($data['fieldName'] === 'country' || $data['fieldName'] === 'state' || $data['fieldName'] === 'mobile' || $data['fieldName'] === 'phone') {
                            if ($data['fieldName'] === 'country') {
                                $currentCountry = array_search($data['fieldValue'], $countries);
                                if ($currentCountry === false) {
                                    $this->_responseHelper->fail(array('oldToken'=> $data['clientOldToken'], 'respText'=> $this->_translator->translate('Can\'t update Country')));
                                }
                                $value[$data['fieldName']] = $currentCountry;
                            }
                            if ($data['fieldName'] === 'state') {
                                $currentState = Tools_Geo::getStateByCode($data['fieldValue']);
                                if ($currentState === null) {
                                    $this->_responseHelper->fail(array('oldToken'=> $data['clientOldToken'], 'respText'=> $this->_translator->translate('Can\'t update State')));
                                }
                                $value[$data['fieldName']] = $currentState['id'];
                            }
                            if ($data['fieldName'] === 'mobile') {
                                $value['mobilecountrycode'] = $data['countryCode'];
                                $value['mobile'] = $data['fieldValue'];
                                $value = $this->_normalizeMobilePhoneNumber($value);
                            }

                            if ($data['fieldName'] === 'phone') {
                                $value['phonecountrycode'] = $data['countryCode'];
                                $value['phone'] = $data['fieldValue'];
                                $value = $this->_normalizeMobilePhoneNumber($value);
                            }

                            $addressValues = $value;

                        }else{
                            $value[$data['fieldName']] = $data['fieldValue'];

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
            $countryMobileCode = Zend_Locale::getTranslation($arr['mobilecountrycode'], 'phoneToTerritory');
            $countryPhoneCode = Zend_Locale::getTranslation($arr['phonecountrycode'], 'phoneToTerritory');
            $arr['mobile'] = preg_replace('~\D~ui', '', $arr['mobile']);
            $mobileNumber = Apps_Tools_Twilio::normalizePhoneNumberToE164($arr['mobile'], $countryMobileCode);
            if ($mobileNumber !== false) {
                $arr['mobile_country_code_value'] = '+'.$countryMobileCode;
            }
            if (empty($arr['phone'])) {
                $arr['phone'] = '';
            } else {
                $arr['phone'] = preg_replace('~\D~ui', '', $arr['phone']);
            }
            $phoneNumber = Apps_Tools_Twilio::normalizePhoneNumberToE164($arr['phone'], $countryPhoneCode);
            if ($phoneNumber !== false) {
                $arr['phone_country_code_value'] = '+'.$countryPhoneCode;
            }
        }
        return $arr;
    }

    public function saveDragListOrderAction()
    {
        if (Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT) && $this->_request->isPost()) {
            $dragList = filter_var_array($this->_request->getParams(), FILTER_SANITIZE_STRING);
            if (!empty($dragList['list_id'])) {
                $currentUser = Zend_Controller_Action_HelperBroker::getStaticHelper('session')->getCurrentUser();
                $userId = $currentUser->getId();

                $pageId = filter_var($this->_request->getParam('pageId'), FILTER_SANITIZE_NUMBER_INT);

                $mapper = Models_Mapper_DraggableMapper::getInstance();
                $listId = $dragList['list_id'];
                $model = new Models_Model_Draggable();
                $model->setId($listId);
                $model->setData(serialize($dragList['list_data']));
                $model->setUpdatedAt(Tools_System_Tools::convertDateFromTimezone('now'));
                $model->setUserId($userId);
                $model->setIpAddress(Tools_System_Tools::getIpAddress());
                $model->setPageId($pageId);
                $mapper->save($model);

                $this->_responseHelper->success($this->_translator->translate('Order has been updated'));
            }
        }
        $this->_responseHelper->fail('');
    }

    public function getUsersAction()
    {
        if ($this->_request->isPost() && Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)) {
            $userMapper = Models_Mapper_CustomerMapper::getInstance();

            $users = $userMapper->getUsersWithGroupsList();
            if (!empty($users)) {
                $headers = array(
                    $this->_translator->translate('E-mail'),
                    $this->_translator->translate('Role'),
                    $this->_translator->translate('Prefix'),
                    $this->_translator->translate('Full name'),
                    $this->_translator->translate('Last login date'),
                    $this->_translator->translate('Registration date'),
                    $this->_translator->translate('IP address'),
                    $this->_translator->translate('Referer url'),
                    $this->_translator->translate('Google plus profile'),
                    $this->_translator->translate('Mobile country code'),
                    $this->_translator->translate('Mobile country code value'),
                    $this->_translator->translate('Mobile phone'),
                    $this->_translator->translate('Notes'),
                    $this->_translator->translate('Timezone'),
                    $this->_translator->translate('Desktop country code'),
                    $this->_translator->translate('Desktop country code value'),
                    $this->_translator->translate('Desktop phone'),
                    $this->_translator->translate('Group Name'),
                    $this->_translator->translate('Subscribed')
                );

                $userAttributes = $userMapper->getUserAttributesNames();

                if(!empty($userAttributes)){
                    foreach ($userAttributes as $attribute){
                        $headers[] = 'attribute_'.$attribute['attribute'];
                    }
                }

                $exportResult = Tools_System_Tools::arrayToCsv($users, $headers);
                if ($exportResult) {
                    $usersArchive = Tools_System_Tools::zip($exportResult);

                    $this->_response->setHeader('Content-Disposition', 'attachment; filename=' . Tools_Filesystem_Tools::basename($usersArchive))
                        ->setHeader('Content-type', 'application/force-download');
                    readfile($usersArchive);
                    $this->_response->sendResponse();
                }
            }
            exit;
        }
    }

    public static function processPhoneCodes($userModel){
        if($userModel instanceof Application_Model_Models_User){
            $customerMapper = Models_Mapper_CustomerMapper::getInstance();
            $customerAddressToProcess = $customerMapper->getUserAddressWithPhonesByUserId($userModel->getId());
            $customerTable = new Models_DbTable_CustomerAddress();

            if (!empty($customerAddressToProcess)) {
                foreach ($customerAddressToProcess as $customerAddressToProcesKey => $customerAddressToProces) {
                    $customer = $customerMapper->find($userModel->getId());

                    $oldMobileCountryCode = $customerAddressToProces['oldMobileCountryCode'];
                    $mobileCountryPhoneCode = Zend_Locale::getTranslation($oldMobileCountryCode, 'phoneToTerritory');
                    $mobileCountryCodeValue = '+' . $mobileCountryPhoneCode;
                    $mobilePhone = str_replace($mobileCountryCodeValue, '', $customerAddressToProces['mobile']);

                    $customerAddressToProces['mobilecountrycode'] = $oldMobileCountryCode;
                    $customerAddressToProces['mobile_country_code_value'] = $mobileCountryCodeValue;
                    $customerAddressToProces['mobile'] = $mobilePhone;

                    unset($customerAddressToProces['oldMobileCountryCode']);
                    $customerToken = $customerMapper->addAddress($customer, $customerAddressToProces, null);

                    $cartSessionMapper = Models_Mapper_CartSessionMapper::getInstance();

                    $currentCartSession = $cartSessionMapper->fetchOrders($customer->getId());

                    if(!empty($currentCartSession) && (!empty($customerToken))) {
                        foreach ($currentCartSession as $cartSession) {
                            $newToken = array();
                            if($customerAddressToProces['address_type'] === 'shipping' && $cartSession->getShippingAddressId() == $customerAddressToProces['id']) {
                                $newToken['shipping_address_id'] = $customerToken;
                            } else if($cartSession->getBillingAddressId() == $customerAddressToProces['id']){
                                $newToken['billing_address_id'] = $customerToken;
                            }
                            $newToken['updated_at'] = date(DATE_ATOM);
                            if(!empty($newToken['shipping_address_id']) || !empty($newToken['billing_address_id'])){
                                $cartSessionMapper->updateAddress($customerAddressToProces['id'], $customerAddressToProces['address_type'], $newToken);
                            }
                        }
                    }

                    $lastData =  $customerMapper->getUserAddressByUserId($customer->getId(),$customerAddressToProces['id']);
                    if(!empty($lastData) && ($customerAddressToProces['id'] !== $customerToken)){
                        $where = $customerTable->getAdapter()->quoteInto('id =?', $customerAddressToProces['id']);
                        $customerTable->delete($where);
                    }
                }
            }
        }

    }

    /**
     * Process shipping label
     */
    public function shippingLabelAction()
    {
        $tokenToValidate = $this->_request->getParam('secureToken', false);
        $orderId = filter_var($this->_request->getParam('orderId'), FILTER_SANITIZE_NUMBER_INT);
        $availabilityDate = filter_var($this->_request->getParam('availabilityDate'), FILTER_SANITIZE_STRING);
        $availabilityTime = filter_var($this->_request->getParam('availabilityTime'), FILTER_SANITIZE_STRING);
        $regenerateLabel = filter_var($this->_request->getParam('regenerate'), FILTER_SANITIZE_STRING);
        $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
        if (!$valid) {
            exit;
        }
        if ($this->_request->isPost() && Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT) && !empty($orderId)) {
            $cartSessionMapper = Models_Mapper_CartSessionMapper::getInstance();
            $orderModel = $cartSessionMapper->find($orderId);
            if ($orderModel instanceof Models_Model_CartSession) {
                $orderStatus = $orderModel->getStatus();

                $notMachStatus = true;
                if($orderStatus === Models_Model_CartSession::CART_STATUS_COMPLETED || $orderStatus === Models_Model_CartSession::CART_STATUS_SHIPPED) {
                    $notMachStatus = false;
                }

                if ($notMachStatus) {
                    $this->_responseHelper->fail($this->_translator->translate('You can create label only for completed or shipped orders'));
                }
            }

            $data = array('orderId' => $orderId, 'availabilityDate' => $availabilityDate, 'availabilityTime' => $availabilityTime, 'regenerateLabel' => $regenerateLabel);
            $shippingLabelInfo = Tools_System_Tools::firePluginMethodByPluginName($orderModel->getShippingService(),
                'generateLabel', $data, false);
            if (empty($shippingLabelInfo)) {
                $this->_responseHelper->fail($this->_translator->translate('Service doesn\'t allow label generation'));
            }

            if ($shippingLabelInfo['error']) {
                if (!empty($shippingLabelInfo['regenerate'])) {
                    $this->_responseHelper->fail(array('regenerate' => true, 'message' => $this->_translator->translate($shippingLabelInfo['message'])));
                }
                $this->_responseHelper->fail($this->_translator->translate($shippingLabelInfo['message']));
            }

            $this->_responseHelper->success(array(
                'shipping_label_link' => $shippingLabelInfo['shipping_label_link'],
                'message' => $this->_translator->translate($shippingLabelInfo['message'])
            ));
        }
    }


    /**
     * Get refund shipment screen info
     */
    public function getRefundShipmentScreenInfoAction()
    {
        $tokenToValidate = $this->_request->getParam('secureToken', false);
        $orderId = filter_var($this->_request->getParam('orderId'), FILTER_SANITIZE_NUMBER_INT);
        $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
        if (!$valid) {
            exit;
        }
        if ($this->_request->isPost() && Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT) && !empty($orderId)) {
            $cartSessionMapper = Models_Mapper_CartSessionMapper::getInstance();
            $orderModel = $cartSessionMapper->find($orderId);
            if ($orderModel instanceof Models_Model_CartSession) {
                $orderStatus = $orderModel->getStatus();
                if ($orderStatus === Models_Model_CartSession::CART_STATUS_COMPLETED && $orderStatus === Models_Model_CartSession::CART_STATUS_SHIPPED) {
                    $this->_responseHelper->fail($this->_translator->translate('You can do shipment refund only for the completed or shipped orders'));
                }
            }

            $data = array('orderId' => $orderId);
            $shipmentRefundServiceInfo = Tools_System_Tools::firePluginMethodByPluginName($orderModel->getShippingService(),
                'shipmentRefundServiceInfo', $data, false);
            if (empty($shipmentRefundServiceInfo)) {
                $this->_responseHelper->fail($this->_translator->translate('Service doesn\'t allow shipment refund'));
            }

            if ($shipmentRefundServiceInfo['error'] === true || $shipmentRefundServiceInfo['error'] === 1) {
                $this->_responseHelper->fail($this->_translator->translate($shipmentRefundServiceInfo['message']));
            }

            $this->_responseHelper->success(array(
                'shipment_refund_screen_description' => $shipmentRefundServiceInfo['shipment_refund_screen_description'],
                'shipment_refund_button_status' => $shipmentRefundServiceInfo['shipment_refund_button_status'],
                'message' => $this->_translator->translate($shipmentRefundServiceInfo['message'])
            ));
        }
    }

    /*
     * @throws Exceptions_SeotoasterPluginException
     *
     * Change default user group
     */
    public function changeDefaultUserGroupAction() {
        if ($this->_request->isPost() && Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)) {
            $defaultGroupId = filter_var($this->_request->getParam('defaultGroupId'), FILTER_SANITIZE_NUMBER_INT);

            $this->_configMapper->save(array(self::DEFAULT_USER_GROUP => $defaultGroupId));

            $this->_responseHelper->success('');
        }
    }

    /**
     * Check if coupon found in shopping_coupon_usage DbTable
     */
    public function checkUseCouponAction() {
        if ($this->_request->isGet() && Tools_Security_Acl::isAllowed(self::RESOURCE_STORE_MANAGEMENT)) {
            $couponId = filter_var($this->_request->getParam('cid'), FILTER_SANITIZE_NUMBER_INT);
            if(!empty($couponId)) {
                $coupon = Store_Mapper_CouponMapper::getInstance()->findCouponUsageByCouponId($couponId);

                if(!empty($coupon)) {
                    $this->_responseHelper->success(array('used' => $this->_translator->translate('was used in purchase.')));
                } else {
                    $this->_responseHelper->success('');
                }
            }
            $this->_responseHelper->fail('');
        }
        $this->_responseHelper->fail('');
    }

    /**
     * Added product to wishlist and wishListQty increase by one
     */
    public function addToWishListAction() {
        if (!$this->_request->isPost()) {
            throw new Exceptions_SeotoasterPluginException('Direct access not allowed');
        }

        $productId = $this->_request->getParam('pid');
        $qty = $this->_request->getParam('qty');
        $user = $this->_sessionHelper->getCurrentUser();
        $userId = $user->getId();
        $userRole = $user->getRoleId();

        if(!empty($productId) && !empty($userId) && $userRole !== Tools_Security_Acl::ROLE_GUEST) {
            $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                $this->_responseHelper->fail('');
            }

            $productMapper = Models_Mapper_ProductMapper::getInstance();
            $product = $productMapper->find($productId);
            if($product instanceof Models_Model_Product) {
                $wishedProductsMapper = Store_Mapper_WishedProductsMapper::getInstance();
                $wishedProduct = $wishedProductsMapper->findByUserIdProductId($userId, $productId);
                if(!$wishedProduct instanceof Store_Model_WishedProducts) {
                    $wishedProduct = new Store_Model_WishedProducts();
                    $wishedProduct->setUserId($userId);
                    $wishedProduct->setProductId($product->getId());
                    $wishedProduct->setAddedDate(date(Tools_System_Tools::DATE_MYSQL));

                    $wishedProductsMapper->save($wishedProduct);

                    $productWishedQty = $product->getWishlistQty();
                    $product->setWishlistQty($productWishedQty + $qty);

                    $productMapper->save($product);

                    $this->_responseHelper->success(array('lastAddedUser' => $user->getFullName(), 'addedToList' => $this->_translator->translate('Added to Wishlist')));
                } else {
                    $this->_responseHelper->success(array('alreadyWished' => $this->_translator->translate('Product already added to Wishlist')));
                }
            }
        } else {
            $this->_responseHelper->fail($this->_translator->translate('Can\'t add product to Wishlist! Please re-login into system.'));
        }
    }

    /**
     * This action is used to help Wishlist gets an portional content
     *
     * @throws Exceptions_SeotoasterException
     * @throws Exceptions_SeotoasterPluginException
     */
    public function renderwishlistproductsAction() {
        if (!$this->_request->isPost()) {
            throw new Exceptions_SeotoasterPluginException($this->_translator->translate('Direct access not allowed'));
        }
        $content = '';
        $nextPage = filter_var($this->_request->getParam('nextpage'), FILTER_SANITIZE_NUMBER_INT);
        if (is_numeric($this->_request->getParam('limit'))) {
            $limit = filter_var($this->_request->getParam('limit'), FILTER_SANITIZE_NUMBER_INT);
        } else {
            $limit = Widgets_Storewishlist_Storewishlist::DEFAULT_LIMIT;
        }

        $offset = intval($nextPage) * $limit;

        $productIds = $this->_request->getParam('productIds');
        $productIds = explode(',', $productIds);

        $productMapper = Models_Mapper_ProductMapper::getInstance();
        $enabledOnly = $productMapper->getDbTable()->getAdapter()->quoteInto('p.enabled=?', '1');
        $idsWhere = Zend_Db_Table_Abstract::getDefaultAdapter()->quoteInto('p.id IN (?)', $productIds);

        if (!empty($idsWhere)) {
            $enabledOnly = $idsWhere . ' AND ' . $enabledOnly;
        }

        $products = Models_Mapper_ProductMapper::getInstance()->fetchAll($enabledOnly, null, $offset, $limit,
            null, null, null, false, false, array(), array(), null);

        if (!empty($products)) {
            $template = $this->_request->getParam('template');
            $widget = Tools_Factory_WidgetFactory::createWidget('storewishlist', array('wishList', $template, $offset + $limit, md5(filter_var($this->_request->getParam('pageId'), FILTER_SANITIZE_NUMBER_INT))));

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

    /**
     * Remove wished product
     */
    public function removeWishedProductAction() {
        if (!$this->_request->isPost()) {
            throw new Exceptions_SeotoasterPluginException($this->_translator->translate('Direct access not allowed'));
        }
        $productId = filter_var($this->_request->getParam('pid'), FILTER_SANITIZE_NUMBER_INT);
        $currentUserModel = $this->_sessionHelper->getCurrentUser();
        $userRole = $currentUserModel->getRoleId();

        if($userRole !== Tools_Security_Acl::ROLE_GUEST) {
            $userId = $currentUserModel->getId();

            if ($userId) {
                $productMapper = Models_Mapper_ProductMapper::getInstance();

                if(!empty($productId)) {
                    $product = $productMapper->find($productId);
                    if($product instanceof Models_Model_Product) {
                        $wishedProductsMapper = Store_Mapper_WishedProductsMapper::getInstance();
                        $wishedProduct = $wishedProductsMapper->findByUserIdProductId($userId, $productId);

                        if($wishedProduct instanceof Store_Model_WishedProducts) {
                            $wishedProductsMapper->delete($wishedProduct);
                            $wishlistQty = $product->getWishlistQty();
                            $product->setWishlistQty($wishlistQty - 1);

                            $productMapper->save($product);

                            $this->_responseHelper->success($this->_translator->translate('Removed'));
                        }
                    }
                }
            }
        }
        $this->_responseHelper->fail($this->_translator->translate('Can\'t remove wished product! Please re-login into system.'));
    }

    /**
     * Get saved countries by all zones
     */
    public function getUsedZoneCountriesAction() {
        $zonesMapper = Models_Mapper_Zone::getInstance();
        $countries = $zonesMapper->getSavedZoneCountries();

        $this->_responseHelper->success(array('savedCounties' => $countries));
    }

    /**
     * Added product to notification list
     * @throws Exceptions_SeotoasterPluginException
     */
    public function addToNotifyListAction() {
        if (!$this->_request->isPost()) {
            throw new Exceptions_SeotoasterPluginException('Direct access not allowed');
        }

        $productId = $this->_request->getParam('pid');
        $user = $this->_sessionHelper->getCurrentUser();
        $userId = $user->getId();
        $userRole = $user->getRoleId();

        if(!empty($productId) && !empty($userId) && $userRole !== Tools_Security_Acl::ROLE_GUEST) {
            $tokenToValidate = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                $this->_responseHelper->fail('');
            }

            $productMapper = Models_Mapper_ProductMapper::getInstance();
            $product = $productMapper->find($productId);
            if($product instanceof Models_Model_Product) {
                $notifiedProductsMapper = Store_Mapper_NotifiedProductsMapper::getInstance();
                $notifiedProduct = $notifiedProductsMapper->findByUserIdProductId($userId, $productId);

                if(!$notifiedProduct instanceof Store_Model_NotifiedProductsModel && ($product->getInventory() == '0' || $product->getInventory() < '0')) {
                    $notifiedProduct = new Store_Model_NotifiedProductsModel();
                    $notifiedProduct->setUserId($userId);
                    $notifiedProduct->setProductId($product->getId());
                    $notifiedProduct->setAddedDate(date(Tools_System_Tools::DATE_MYSQL));
                    $notifiedProduct->setSendNotification('0');

                    $notifiedProductsMapper->save($notifiedProduct);

                    $this->_responseHelper->success(array('addedToList' => $this->_translator->translate('Added to notification list')));
                } else {
                    $this->_responseHelper->success(array('alreadyNotified' => $this->_translator->translate('Product already added to notification list')));
                }
            }
        } else {
            $this->_responseHelper->fail($this->_translator->translate('Can\'t add product to notification list! Please re-login into system.'));
        }
    }

    /**
     * Remove notified product
     */
    public function removeNotifiedProductAction() {
        if (!$this->_request->isPost()) {
            throw new Exceptions_SeotoasterPluginException($this->_translator->translate('Direct access not allowed'));
        }
        $productId = filter_var($this->_request->getParam('pid'), FILTER_SANITIZE_NUMBER_INT);
        $currentUserModel = $this->_sessionHelper->getCurrentUser();
        $userRole = $currentUserModel->getRoleId();

        if($userRole !== Tools_Security_Acl::ROLE_GUEST) {
            $userId = $currentUserModel->getId();

            if ($userId && !empty($productId)) {
                $notifiedProductsMapper = Store_Mapper_NotifiedProductsMapper::getInstance();
                $notifiedProduct = $notifiedProductsMapper->findByUserIdProductId($userId, $productId);

                if($notifiedProduct instanceof Store_Model_NotifiedProductsModel) {
                    $notifiedProductsMapper->delete($notifiedProduct);

                    $this->_responseHelper->success($this->_translator->translate('Removed'));
                }
            }
        }
        $this->_responseHelper->fail($this->_translator->translate('Can\'t remove notified product! Please re-login into system.'));
    }

    /**
     * This action is used to help Notify list gets an portional content
     *
     * @throws Exceptions_SeotoasterException
     * @throws Exceptions_SeotoasterPluginException
     */
    public function rendernotifiedlistproductsAction() {
        if (!$this->_request->isPost()) {
            throw new Exceptions_SeotoasterPluginException($this->_translator->translate('Direct access not allowed'));
        }
        $content = '';
        $nextPage = filter_var($this->_request->getParam('nextpage'), FILTER_SANITIZE_NUMBER_INT);
        if (is_numeric($this->_request->getParam('limit'))) {
            $limit = filter_var($this->_request->getParam('limit'), FILTER_SANITIZE_NUMBER_INT);
        } else {
            $limit = Widgets_Notifyme_Notifyme::DEFAULT_LIMIT;
        }

        $offset = intval($nextPage) * $limit;

        $productIds = $this->_request->getParam('productIds');
        $productIds = explode(',', $productIds);

        $productMapper = Models_Mapper_ProductMapper::getInstance();
        $enabledOnly = $productMapper->getDbTable()->getAdapter()->quoteInto('p.enabled = ?', '1');
        $idsWhere = Zend_Db_Table_Abstract::getDefaultAdapter()->quoteInto('p.id IN (?)', $productIds);

        if (!empty($idsWhere)) {
            $enabledOnly = $idsWhere . ' AND ' . $enabledOnly;
        }

        $products = Models_Mapper_ProductMapper::getInstance()->fetchAll($enabledOnly, null, $offset, $limit,
            null, null, null, false, false, array(), array(), null);

        if (!empty($products)) {
            $template = $this->_request->getParam('template');
            $widget = Tools_Factory_WidgetFactory::createWidget('notifyme', array('notifylist', $template, $offset + $limit, md5(filter_var($this->_request->getParam('pageId'), FILTER_SANITIZE_NUMBER_INT))));

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

    public function productCustomFieldsConfigAction()
    {
        if (Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_PLUGINS)) {
            if ($this->_request->isGet()) {
                $this->_layout->content = $this->_view->render('product-custom-fields-config.phtml');
                echo $this->_layout->render();
            }
        }
    }

    /**
     * This action is used to change custom params values for product
     */
    public function updateProductCustomParamAction()
    {
        if ($this->_request->isPost() && Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT)) {
            $tokenToValidate = $this->_request->getParam('secureToken', false);

            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                $this->_responseHelper->fail('');
            }

            $customparamsData = $this->_request->getParams();

            $currentCustomParamValue = filter_var($this->_request->getParam('currentCustomParamValue'), FILTER_SANITIZE_STRING);

            $productCustomParamsDataMapper = Store_Mapper_ProductCustomParamsDataMapper::getInstance();

            if(!empty($customparamsData['paramId']) && !empty($customparamsData['customParamProductId'])) {
                if($customparamsData['isNew']) {
                    $productCustomParamsDataModel = new Store_Model_ProductCustomParamsDataModel();

                    $productCustomParamsDataModel->setParamId($customparamsData['paramId']);
                    $productCustomParamsDataModel->setProductId($customparamsData['customParamProductId']);

                    if($customparamsData['type'] == Api_Store_Productcustomfieldsconfig::PRODUCT_CUSTOM_FIELD_TYPE_TEXT) {
                        $productCustomParamsDataModel->setParamValue($currentCustomParamValue);
                    } elseif ($customparamsData['type'] == Api_Store_Productcustomfieldsconfig::PRODUCT_CUSTOM_FIELD_TYPE_SELECT) {
                        $productCustomParamsDataModel->setParamsOptionId($currentCustomParamValue);
                    }

                    $productCustomParamsDataMapper->save($productCustomParamsDataModel);

                    $this->_responseHelper->success('');

                } else {
                    $productCustomParamsDataExists = $productCustomParamsDataMapper->checkIfParamExists($customparamsData['customParamProductId'], $customparamsData['paramId']);

                    if($productCustomParamsDataExists instanceof Store_Model_ProductCustomParamsDataModel) {
                        if($customparamsData['type'] == Api_Store_Productcustomfieldsconfig::PRODUCT_CUSTOM_FIELD_TYPE_TEXT) {
                            $productCustomParamsDataExists->setParamValue($currentCustomParamValue);
                        } elseif ($customparamsData['type'] == Api_Store_Productcustomfieldsconfig::PRODUCT_CUSTOM_FIELD_TYPE_SELECT) {
                            $productCustomParamsDataExists->setParamsOptionId($currentCustomParamValue);
                        }

                        $productCustomParamsDataMapper->save($productCustomParamsDataExists);

                        $this->_responseHelper->success('');
                    } else{
                        $this->_responseHelper->fail($this->_translator->translate('Unknown product custom param type'));
                    }
                }
            } else {
                $this->_responseHelper->fail($this->_translator->translate('Can\'t update product custom param'));
            }
        }
    }

    public function throttleCheckLimitAction()
    {
        if ($this->_request->isPost()) {
            if (Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('throttleTransactions') === 'true' && Tools_Misc::checkThrottleTransactionsLimit() === false) {
                $throttleTransactionsLimitMessage = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('throttleTransactionsLimitMessage');
                $throttleTransactionsLimitMessage = !empty($throttleTransactionsLimitMessage) ? $throttleTransactionsLimitMessage : Tools_Misc::THROTTLE_TRANSACTIONS_DEFAULT_MESSAGE;
                $this->_responseHelper->fail($throttleTransactionsLimitMessage);
            };
        }
        $this->_responseHelper->success('');
    }

    /**
     * @throws Zend_Reflection_Exception
     *
     * {$store:labelGenerationGrid}
     */
    public function generateLabelAction()
    {
        if ($this->_request->isPost()) {
            $currentUser = $this->_sessionHelper->getCurrentUser();
            $currentUserRole = $currentUser->getRoleId();

            if($currentUserRole === Tools_Security_Acl::ROLE_SUPERADMIN || $currentUserRole === Tools_Security_Acl::ROLE_ADMIN || $currentUserRole === Shopping::ROLE_SALESPERSON || $currentUserRole === Shopping::ROLE_SUPPLIER) {
                $secureToken = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
                $tokenValid = Tools_System_Tools::validateToken($secureToken, self::SHOPPING_SECURE_TOKEN);
                if (!$tokenValid) {
                    $this->_responseHelper->fail($this->_translator->translate('Can\'t generate label'));
                }

                $cartId = filter_var($this->_request->getParam('orderId', 0), FILTER_SANITIZE_NUMBER_INT);

                $additionalParams = filter_var_array($this->_request->getParam('additionalParams'), FILTER_SANITIZE_STRING);
                $regenerateLabel = filter_var($this->_request->getParam('regenerate'), FILTER_SANITIZE_STRING);

                if(!empty($cartId)) {
                    $cartSession = Models_Mapper_CartSessionMapper::getInstance()->find(intval($cartId));

                    if ($cartSession instanceof Models_Model_CartSession) {
                        $orderStatus = $cartSession->getStatus();

                        $notMachStatus = true;
                        if($orderStatus === Models_Model_CartSession::CART_STATUS_COMPLETED || $orderStatus === Models_Model_CartSession::CART_STATUS_SHIPPED) {
                            $notMachStatus = false;
                        }

                        if ($notMachStatus) {
                            $this->_responseHelper->fail($this->_translator->translate('You can create label only for completed or shipped orders'));
                        }

                        $shippingService = $cartSession->getShippingService();

                        if(!empty($shippingService)) {
                            if(in_array($shippingService, Tools_Misc::$systemShippingServices)) {
                                $this->_responseHelper->fail($this->_translator->translate('Service doesn\'t allow label generation'));
                            }

                            $data = array('orderId' => $cartId, 'regenerateLabel' => $regenerateLabel);

                            if(!empty($additionalParams) && is_array($additionalParams)) {
                                $data = array_merge($data, $additionalParams);
                            }

                            $shippingLabelInfo = Tools_System_Tools::firePluginMethodByPluginName($shippingService, 'generateLabel', $data, false);
                            if (empty($shippingLabelInfo)) {
                                $this->_responseHelper->fail($this->_translator->translate('Service doesn\'t allow label generation'));
                            }

                            if ($shippingLabelInfo['error']) {
                                if (!empty($shippingLabelInfo['regenerate'])) {
                                    $this->_responseHelper->fail(array('regenerate' => true, 'message' => $this->_translator->translate($shippingLabelInfo['message'])));
                                }

                                $this->_responseHelper->fail($this->_translator->translate($shippingLabelInfo['message']));
                            }
                        } else {
                            $this->_responseHelper->fail($this->_translator->translate('Shipping service is empty!'));
                        }
                    }
                }
            } else {
                $this->_responseHelper->fail($this->_translator->translate('Can\'t generate label'));
            }
        } else {
            $this->_responseHelper->fail($this->_translator->translate('Can\'t generate label'));
        }
    }


    public function sendPaymentInfoEmailAction()
    {
        if ($this->_request->isPost()) {
            $currentUser = $this->_sessionHelper->getCurrentUser();
            $currentUserRole = $currentUser->getRoleId();

            if ($currentUserRole === Tools_Security_Acl::ROLE_SUPERADMIN || $currentUserRole === Tools_Security_Acl::ROLE_ADMIN || $currentUserRole === Shopping::ROLE_SALESPERSON) {
                $secureToken = $this->_request->getParam(Tools_System_Tools::CSRF_SECURE_TOKEN, false);
                $tokenValid = Tools_System_Tools::validateToken($secureToken, self::SHOPPING_SECURE_TOKEN);
                if (!$tokenValid) {
                    $this->_responseHelper->fail($this->_translator->translate('Can\'t generate label'));
                }

                $paymentInfoMessage = $this->_request->getParam('sendPaymentRequestMessage');
                $orderId = $this->_request->getParam('orderId');
                $cartSessionMapper = Models_Mapper_CartSessionMapper::getInstance();
                $cartSessionModel = $cartSessionMapper->find($orderId);
                $partialNotificationMapper = Store_Mapper_PartialNotificationLogMapper::getInstance();
                if ($cartSessionModel instanceof Models_Model_CartSession) {
                    $partialNotificationLogModel = $partialNotificationMapper->findByCartId($orderId);
                    if (!$partialNotificationLogModel instanceof Store_Model_PartialNotificationLog) {
                        $partialNotificationLogModel = new Store_Model_PartialNotificationLog();
                    }
                    $cartSession = $cartSessionMapper->find($orderId);
                    $cartSession->registerObserver(new Tools_Mail_Watchdog(array(
                        'trigger' => Tools_StoreMailWatchdog::TRIGGER_STORE_PARTIALPAYMENT_NOTIFICATION,
                        'customInfoMessage' => $paymentInfoMessage
                    )));

                    $cartSession->notifyObservers();

                    $date = date(Tools_System_Tools::DATE_MYSQL);

                    $partialNotificationLogModel->setCartId($orderId);
                    $partialNotificationLogModel->setNotifiedAt($date);
                    $cartSessionModel->setPartialNotificationDate($date);
                    $cartSessionMapper->save($cartSessionModel);
                    $partialNotificationMapper->save($partialNotificationLogModel);

                }

                $this->_responseHelper->success($this->_translator->translate('Payment request has been sent'));

            }

            $this->_responseHelper->fail('');
        }

    }

    /**
     * Update user attributes on Dashboard clients grid
     *
     * @return void
     */
    public function updateUserAttributeAction()
    {
        if ($this->_request->isPost() && Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT)) {
            $tokenToValidate = $this->_request->getParam('secureToken', false);

            $valid = Tools_System_Tools::validateToken($tokenToValidate, self::SHOPPING_SECURE_TOKEN);
            if (!$valid) {
                $this->_responseHelper->fail('');
            }

            $attributeParamsData = $this->_request->getParams();

            $userId = $attributeParamsData['userId'];
            $attributeType = $attributeParamsData['attributeType'];
            $oldFieldValue = $attributeParamsData['oldFieldValue'];
            $fieldValue = $attributeParamsData['fieldValue'];

            if(!empty($userId)) {
                $userMapper = Application_Model_Mappers_UserMapper::getInstance();
                $currentUserModel = $userMapper->find($userId);

                $userAttributes = array();
                if($currentUserModel instanceof Application_Model_Models_User) {
                    $userMapper->loadUserAttributes($currentUserModel);

                    $userAttributes = $currentUserModel->getAttributes();
                }

                $dbTable = new Zend_Db_Table('user_attributes');

                if(!empty($userAttributes)) {
                    $dbTable->delete(array('user_id = ?' => $userId));

                    $attributeBelonging = $attributeParamsData['attributeBelonging'];

                    foreach ($userAttributes as $attribute => $value) {
                        if($attributeType == 'attribute-name') {
                            if($attribute == $oldFieldValue) {
                                $attribute = $fieldValue;
                            }
                        } elseif ($attributeType == 'attribute-value') {
                            if($value == $oldFieldValue && $attributeBelonging == $attribute) {
                                $value = $fieldValue;
                            }
                        }

                        $dbTable->insert(array(
                            'user_id' => $userId,
                            'attribute' => $attribute,
                            'value' => $value
                        ));
                    }

                    $this->_responseHelper->success($this->_translator->translate('Updated'));
                }

                $this->_responseHelper->fail($this->_translator->translate('Nothing to delete'));
            }

            $this->_responseHelper->fail($this->_translator->translate('Empty userId'));
        }
    }


    public static function getApiProducts($data)
    {

//        $data = array(
//            'limit' => 4,
//            'offset' => '',
//            'searchParams' =>
//                array(
//                    'productName' => 'Académiq'
//                )
//        );

        if (empty($data) || empty($data['limit'])) {
            return array('error' => '1', 'message' => 'Missing limit query limit');
        }

        $limit = $data['limit'];
        $offset = null;
        if ($data['offset']) {
            $offset = $data['offset'];
        }

        $productMapper = Models_Mapper_ProductMapper::getInstance();
        $where = $productMapper->getDbTable()->getAdapter()->quoteInto('enabled = ?', '1');

        if (!empty($data['searchParams'])) {
            if (!empty($data['searchParams']['productName'])) {
                $searchProductName = $data['searchParams']['productName'];
                $where .= ' AND '. $productMapper->getDbTable()->getAdapter()->quoteInto('sp.name LIKE ?', '%' .$searchProductName. '%');
            }
        }

        $productsDataInfo = $productMapper->fetchAllData($where, null, $limit, $offset);

        if (empty($productsDataInfo) || empty($productsDataInfo['data'])) {
            return array();
        }

        $productInfo = array();

        $currency = Zend_Registry::get('Zend_Currency');
        $websiteHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('website');
        $websiteUrl = $websiteHelper->getUrl();
        $shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();

        foreach ($productsDataInfo['data'] as $key => $product) {
            $productPageUrl = $product['url'];
            $productInfo[$key]['id'] = $product['id'];
            $productInfo[$key]['title'] = $product['name'];
            $productInfo[$key]['sku'] = $product['sku'];
            $productInfo[$key]['link'] = $websiteUrl . $productPageUrl;
            $productInfo[$key]['short_description'] = $product['short_description'];
            $productInfo[$key]['full_description'] = $product['full_description'];
            $productInfo[$key]['brand'] = $product['brandName'];
            $productInfo[$key]['mpn'] = $product['mpn'];
            $productInfo[$key]['gtin'] = $product['gtin'];
            $productInfo[$key]['imageLinkSmall'] = Tools_Misc::prepareProductImage($product['photo'], 'small');

            $productModel = $productMapper->find($product['id']);

            if ($productModel->getCurrentPrice() !== null && $productModel->getExtraProperties()) {
                $productModel->setCurrentPrice(null);
            }

            $price = number_format(Tools_ShoppingCart::getInstance()->calculateProductPrice($productModel, $productModel->getDefaultOptions()), 2, '.', '');
            $productInfo[$key]['price'] = $price;
            $productInfo[$key]['priceWithCurrency'] = $currency->toCurrency($price);
        }

        $productsDataInfo['data'] = $productInfo;

        return $productsDataInfo;
    }


    public function buyAgainAction()
    {
        $sessionHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('session');
        $userSession = $sessionHelper->getCurrentUser();
        $userId = $userSession->getId();

        if ($this->_request->isPost() && !empty($userId)) {
            $orderId = $this->_request->getParam('orderId');
            $dataOrderSubtype = $this->_request->getParam('dataOrderSubtype');
            if (!empty($orderId)) {
                $cartMapper    = Models_Mapper_CartSessionMapper::getInstance();
                $productMapper = Models_Mapper_ProductMapper::getInstance();
                $quoteMapper = Quote_Models_Mapper_QuoteMapper::getInstance();
                $currentCart = $cartMapper->find($orderId);
                if ($currentCart instanceof Models_Model_CartSession){
                    $currentCartStatus = $currentCart->getStatus();
                    if (!in_array($currentCartStatus, array(Models_Model_CartSession::CART_STATUS_COMPLETED, Models_Model_CartSession::CART_STATUS_SHIPPED, Models_Model_CartSession::CART_STATUS_DELIVERED, Models_Model_CartSession::CART_STATUS_REFUNDED, Models_Model_CartSession::CART_STATUS_NEW))) {
                        $this->_responseHelper->fail('status not allowed');
                    }

                    if ($dataOrderSubtype !== 'with-quote') {
                        $quoteModel = $quoteMapper->findByCartId($orderId);
                        if ($quoteModel instanceof Quote_Models_Model_Quote) {
                            $this->_responseHelper->fail('You can\'t purchase quote again');
                        }
                    }

                    $cartSession = Tools_ShoppingCart::getInstance();
                    $cartSession->setContent(array());
                    $cartSession->save();
                    $cartSession->setShippingAddressKey($currentCart->getShippingAddressId());
                    $notFreebiesInCart = array();
                    $freebiesInCart = array();
                    $productsFreebiesRelation = array();
                    $cartContent = $currentCart->getCartContent();

                    foreach ($cartContent as $key => $product) {
                        if ($product['freebies'] === '1') {
                            $freebiesInCart[$product['product_id']] = $product['product_id'];
                        } else {
                            $notFreebiesInCart[$product['product_id']] = $product['product_id'];
                        }
                    }
                    if (!empty($freebiesInCart)) {
                        $where = $productMapper->getDbTable()->getAdapter()->quoteInto(
                            'sphp.freebies_id IN (?)',
                            $freebiesInCart
                        );
                        $where .= ' AND ' . $productMapper->getDbTable()->getAdapter()->quoteInto(
                                'sphp.product_id IN (?)',
                                $notFreebiesInCart
                            );
                        $select = $productMapper->getDbTable()->getAdapter()->select()
                            ->from(
                                array('spfs' => 'shopping_product_freebies_settings'),
                                array(
                                    'freebiesGroupKey' => new Zend_Db_Expr("CONCAT(sphp.freebies_id, '_', sphp.product_id)"),
                                    'price_value'
                                )
                            )
                            ->joinleft(
                                array('sphp' => 'shopping_product_has_freebies'),
                                'spfs.prod_id = sphp.product_id'
                            )
                            ->where($where);
                        $productFreebiesSettings = $productMapper->getDbTable()->getAdapter()->fetchAssoc($select);
                    }

                    if (!empty($productFreebiesSettings)) {
                        foreach ($productFreebiesSettings as $prodInfo) {
                            if (array_key_exists($prodInfo['freebies_id'], $freebiesInCart)) {
                                if (isset($productsFreebiesRelation[$prodInfo['freebies_id']])) {
                                    $productsFreebiesRelation[$prodInfo['freebies_id']][$prodInfo['product_id']] = $prodInfo['product_id'];
                                } else {
                                    $productsFreebiesRelation[$prodInfo['freebies_id']] = array($prodInfo['product_id'] => $prodInfo['product_id']);
                                }
                            }
                        }
                    }

                    foreach ($cartContent as $key => $product) {
                        $productObject = $productMapper->find($product['product_id']);
                        if ($productObject instanceof Models_Model_Product) {
                            if ($product['freebies'] === '1' && !empty($productsFreebiesRelation)) {
                                foreach ($productsFreebiesRelation[$product['product_id']] as $realProductId) {
                                    $itemKey = Tools_ShoppingCart::generateStorageKey(
                                        $productObject,
                                        array(0 => 'freebies_' . $realProductId)
                                    );
                                    if (!$cartSession->findBySid($itemKey)) {
                                        $productObject->setFreebies(1);
                                        $cartSession->add(
                                            $productObject,
                                            array(0 => 'freebies_' . $realProductId),
                                            $product['qty'], true
                                        );
                                    }
                                }
                            } else {
                                $options = array();
                                if (is_array($product['options'])) {
                                    $options = Tools_ShoppingCart::parseProductOptions($product['options']);
                                }
                                $productObject->setPrice($product['price']);
                                $productObject->setOriginalPrice($product['original_price']);
                                $productObject->setCurrentPrice(floatval($productObject->getPrice()));
                                $cartSession->add($productObject, $options, $product['qty'], true);
                            }
                        }
                    }
                    $cartSession->setDiscount(0);
                    $cartSession->setShippingData(array());
                    $cartSession->setDiscountTaxRate(0);
                    $cartSession->calculate(true);
                    $cartSession->save();

                    $this->_responseHelper->success('');
                }
            }
        }
    }


}
