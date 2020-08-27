<?php
/**
 * Store Widget - small proxy for shopping plugins
 * It contains few own generators and allows you to put {$store:%widget_name%} instead of {$plugin:%cart_plugin%:%method_name%}
 * Where %cart_plugin% will be automatically fetched from current shopping settings.
 * This shorthand way allows you easy switch between cart plugins without any impact for site
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Widgets_Store_Store extends Widgets_Abstract {

    const COUNT_PER_PAGE = 10;

	/**
	 * @var bool
	 */
	protected $_cacheable      = false;

    protected $_sessionHelper = null;

    protected $_websiteUrl = null;

    protected $_websiteHelper = null;

	private static $_zendRegistryKey = 'store-cart-plugin';

    protected function _load() {
	    $methodName = Tools_Plugins_Abstract::OPTION_MAKER_PREFIX.ucfirst(strtolower($this->_options[0]));
	    $shopPlugRefl = new Zend_Reflection_Class('Shopping');
	    if ($shopPlugRefl->hasMethod($methodName)) {
			$shoppingPlugin = Tools_Factory_PluginFactory::createPlugin('shopping', $this->_options, $this->_toasterOptions);
		    return $shoppingPlugin->run();
	    } elseif (method_exists($this, $methodName)) {

		    return $this->$methodName();
	    }

	    $regKey = self::$_zendRegistryKey.implode('_', $this->_options);
	    if (Zend_Registry::isRegistered($regKey)){
		    $cart = Zend_Registry::get($regKey);
	    } else {
		    $cartPluginName = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('cartPlugin1');
		    is_null($cartPluginName) && $cartPluginName = Shopping::DEFAULT_CART_PLUGIN;
            if ($cartPluginName){
                $cart = Tools_Factory_PluginFactory::createPlugin($cartPluginName, $this->_options, $this->_toasterOptions);
	            Zend_Registry::set($regKey, $cart);
            } else{
                throw new Exceptions_SeotoasterWidgetException('No cart plugin selected');
            }
	    }
	    return $cart->run();
    }

	protected function _init() {
		$this->_view = new Zend_View();
        $this->_websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
		$this->_view->websiteUrl = $this->_websiteHelper->getUrl();
        $this->_websiteUrl = $this->_websiteHelper->getUrl();
        $this->_sessionHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('session');
		$this->_view->setScriptPath(realpath(__DIR__.DIRECTORY_SEPARATOR.'views'));
	}


	public static function getAllowedOptions() {
        $translator = Zend_Registry::get('Zend_Translate');
        $classes = array('Shopping', __CLASS__);
		$cartPluginName = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('cartPlugin');
		if ($cartPluginName){
			array_push($classes, ucfirst(strtolower($cartPluginName)));
		}

		$methods = array();
		foreach ($classes as $className) {
			$reflection = new Zend_Reflection_Class($className);
			$methods = array_merge($methods, $reflection->getMethods());
			unset($reflection, $className);
		}
		$allowedOptions = array();
		foreach ($methods as $method) {
			if (strpos($method->getName(), Tools_Plugins_Abstract::OPTION_MAKER_PREFIX) !== false){
				$name = str_replace(Tools_Plugins_Abstract::OPTION_MAKER_PREFIX,'', $method->getName());
				try {
					$description = $method->getDocblock()->getShortDescription();
				} catch (Exception $e) {
					$description = null;
				}
				array_push($allowedOptions, array(
                    'group'  => $translator->translate('Shopping Shortcuts'),
					'alias'  => $translator->translate('Store' .' '. $name. (isset($description) ? ' - '.$description: '')),
					'option' => 'store:'.strtolower($name)
				));
			}
		}
		array_multisort($allowedOptions);
		return $allowedOptions;
	}

	/**
	 * Generates user profile
	 * @return string Html content
	 */
	protected function _makeOptionProfile(){
		if (Tools_ShoppingCart::getInstance()->getCustomer()->getId()) {
			return $this->_view->render('profile.phtml');
		}
	}

	/**
	 * Generates manage orders grid
	 * @return string Html content
	 */
	protected function _makeOptionOrders() {
		if (Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT)){
            $translator = Zend_Registry::get('Zend_Translate');
			$this->_view->noLayout = true;
			$this->_view->brands = Models_Mapper_Brand::getInstance()->fetchAll();
			$this->_view->tags = Models_Mapper_Tag::getInstance()->fetchAll();
			$this->_view->shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
            $this->_view->usedCoupons = Store_Mapper_CouponMapper::getInstance()->getCouponCodes();
            $shippingPlugins = Models_Mapper_ShippingConfigMapper::getInstance()->fetchAll();
            $shippingServices = array('');
            if(!empty($shippingPlugins)){
                foreach($shippingPlugins as $shippingPlugin){
                   $shippingServices[$shippingPlugin['name']] = $translator->translate($shippingPlugin['name']);
                }
                $shippingServices = array_merge(array(''=>'shipping carrier'), $shippingServices);
            }
            $this->_view->shippingServices = $shippingServices;
            $enabledInvoicePlugin = Application_Model_Mappers_PluginMapper::getInstance()->findByName('invoicetopdf');
            if($enabledInvoicePlugin != null){
                if($enabledInvoicePlugin->getStatus() == 'enabled'){
                    $this->_view->invoicePlugin = 1;
                }
            }

            $ordersMapper = Models_Mapper_OrdersMapper::getInstance();
            $orderSubtypes = $ordersMapper->getUniqueSubtypes();
            $orderSubtypes = array_filter($orderSubtypes);
            $this->_view->orderSubtypes = $orderSubtypes;
			return $this->_view->render('orders.phtml');
		}
	}

	/**
	 * Generates order summary for current user
	 * @return string Html content
	 */
	protected function _makeOptionPostPurchaseReport() {
		$sessionHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('session');
		if (isset($sessionHelper->storeCartSessionKey)){
			$cartId = intval($sessionHelper->storeCartSessionKey);
            $cartSession = Models_Mapper_CartSessionMapper::getInstance()->find($cartId);
            if(isset($this->_options[1]) && $this->_options[1] == 'mailreport'){
                $this->_view->mailReport = 1;
            }else{
                unset($sessionHelper->storeCartSessionKey);
            }
            if(isset($this->_options[2]) && $this->_options[2] != ''){
                $additionalTableRows = explode(',', $this->_options[2]);
                $this->_view->additionalTableRows = $additionalTableRows;
            }
            
            if(isset($this->_options[2]) && $this->_options[2] != '' && isset($this->_options[3]) && $this->_options[3] != ''){
                $renamedTableRows = explode(',', $this->_options[3]);
                $this->_view->renamedTableRows = $renamedTableRows;
            }
            
			if ($cartSession instanceof Models_Model_CartSession){
				$cartContent = $cartSession->getCartContent();
                $productMapper = Models_Mapper_ProductMapper::getInstance();
                $shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
				$this->_view->shoppingConfig = $shoppingConfig;
                foreach ($cartContent as $key=>$product){
                    $productObject = $productMapper->find($product['product_id']);
                    if($productObject !== null){
                        $cartContent[$key]['mpn']      = $productObject->getMpn();
                        $cartContent[$key]['photo']      = $productObject->getPhoto();
                        $cartContent[$key]['productUrl'] = $productObject->getPage()->getUrl();
                        $cartContent[$key]['taxRate']    = Tools_Tax_Tax::calculateProductTax($productObject, null, true);
                    }
                }

                $defaultPickup = true;
                $pickupLocationConfigMapper = Store_Mapper_PickupLocationConfigMapper::getInstance();
                $pickupLocationData = $pickupLocationConfigMapper->getCartPickupLocationByCartId($cartId);
                if (!empty($pickupLocationData)) {
                    $defaultPickup = false;
                    $this->_view->pickupLocationData = $pickupLocationData;
                }
                $this->_view->defaultPickup = $defaultPickup;

                $this->_view->showPriceIncTax = $shoppingConfig['showPriceIncTax'];
                $this->_view->weightSign = $shoppingConfig['weightUnit'];
                $this->_view->cartContent = $cartContent;
                $this->_view->cart = $cartSession;
				return $this->_view->render('post_purchase_report.phtml');
			}
			return;
		}
		$errmsg = 'store:postpurchasereport missing cart id';
		return Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_CONTENT) ? '<b>'.$errmsg.'</b>' : '<!-- '.$errmsg.' -->' ;
	}

    protected function _getOptions($productId, $options) {
		$actualOptions  = array();
		$product        = Models_Mapper_ProductMapper::getInstance()->find($productId);
		$defaultOptions = $product->getDefaultOptions();
        $resultOptions = array();
		foreach($options as $optionId => $selectionId) {
			foreach($defaultOptions as $defaultOption) {
				if($optionId != $defaultOption['id']) {
					continue;
				}
				$actualOptions = array_filter($defaultOption['selection'], function($selection) use($selectionId) {
					if($selectionId == $selection['id']) {
						return $selection;
					}
				});
                $resultOptions = array_merge($actualOptions, $resultOptions);
                $resultOptions[0]['optionTitle'] = $defaultOption['title'];
			}
		}
		return $resultOptions;
	}
    
    /**
	 * Generates login form for client
	 * @return string 
	 */
	protected function _makeOptionClientLogin() {
        $clientPage = Application_Model_Mappers_PageMapper::getInstance()->fetchByOption(Shopping::OPTION_STORE_CLIENT_LOGIN, true);
        if($clientPage != null){
            return '{$member:login:'.$clientPage->getUrl().'}'; 
        }else{
            return '{$member:login:index.html}';
        }
        
        
    }

    protected function _makeOptionCoupon()
    {
        $shoppingCart = Tools_ShoppingCart::getInstance();

        if (!$shoppingCart->getCustomerId()) {
            return null;
        }

        $currentAppliedCoupons = $shoppingCart->getCoupons();
        $appliedCoupons = array();
        if (!empty($currentAppliedCoupons)) {
            foreach ($currentAppliedCoupons as $coupon) {
                $appliedCoupons[] = $coupon->getCode();
            }
        }

        $sessionHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('session');

        $customCouponError = preg_grep('/coupon-error=*/', $this->_options);
        if (!empty($customCouponError)) {
            $customErrorKey = key($customCouponError);
            $customErrorMessage = str_replace('coupon-error=', '', current(preg_grep('/coupon-error=*/', $this->_options)));
            $sessionHelper->customCouponErrorMessage = $customErrorMessage;
            unset($this->_options[$customErrorKey]);
        }

        if (isset($this->_options[1])) {
            $sessionHelper->customCouponMessageApply = $this->_options[1];
        } elseif(isset($sessionHelper->customCouponMessageApply)){
            unset($sessionHelper->customCouponMessageApply);
        }

        if (isset($this->_options[2]) && $this->_options[2] === 'success') {
            $sessionHelper->forceCouponSuccessStatus = true;
            $this->_view->forceCouponSuccessStatus = true;
        } elseif(isset($sessionHelper->forceCouponSuccessStatus)) {
            unset($sessionHelper->forceCouponSuccessStatus);
        }

        $this->_view->currentAppliedCoupons = $appliedCoupons;

        $this->_view->returnUrl = Tools_Misc::getCheckoutPage()->getUrl();

        return $this->_view->render('coupon.phtml');
    }

    protected function _makeOptionRecurring() {
        $shoppingCart = Tools_ShoppingCart::getInstance();
        if (!$shoppingCart->getCustomerId()){
            return null;
        }
        if (isset($this->_options[1])) {
            $this->_view->customSelectLabel = $this->_options[1];
        }

        $this->_view->currentRecurringPaymentType = $shoppingCart->getRecurringPaymentType();
        $this->_view->activeRecurringPaymentTypes = Store_Mapper_RecurringPaymentsMapper::getInstance()->getRecurringTypes();
        return $this->_view->render('recurring.phtml');
    }

    /**
     * Generates a list of suppliers - dashboard section suppliers widget
     *
     * @return string
     */
    protected function _makeOptionSuppliers()
    {
        if (Tools_Security_Acl::isAllowed(Shopping::RESOURCE_STORE_MANAGEMENT)) {

            $this->_view->allCompanies = Store_Mapper_CompaniesMapper::getInstance()->fetchAll();

            return $this->_view->render('suppliers.phtml');
        }
    }

    /**
     * {$store:labelGenerationGrid}
     *
     * @return string
     * @throws Zend_Paginator_Exception
     */
    protected function _makeOptionLabelGenerationGrid()
    {
        $currentUser = $this->_sessionHelper->getCurrentUser();
        $currentUserRole = $currentUser->getRoleId();

        if($currentUserRole === Tools_Security_Acl::ROLE_SUPERADMIN || $currentUserRole === Tools_Security_Acl::ROLE_ADMIN || $currentUserRole === Shopping::ROLE_SALESPERSON || $currentUserRole === Shopping::ROLE_SUPPLIER) {
            $request = Zend_Controller_Front::getInstance()->getRequest();

            $limit = self::COUNT_PER_PAGE;
            $offset = 0;
            if (!empty($this->_options[1])) {
                $limit = intval($this->_options[1]);
            }

            $gridPageNumber = intval(
                filter_var($request->getParam('gridListNum', 0), FILTER_SANITIZE_NUMBER_INT)
            );

            $filterBy = filter_var($request->getParam('filterBy', false), FILTER_SANITIZE_STRING);

            $filterByDateFrom = filter_var($request->getParam('filterByDateFrom', false), FILTER_SANITIZE_STRING);
            $filterByDateFromOriginal = filter_var($request->getParam('filterByDateFromOriginal', false), FILTER_SANITIZE_STRING);

            $filterByDateTo = filter_var($request->getParam('filterByDateTo', false), FILTER_SANITIZE_STRING);
            $filterByDateToOriginal = filter_var($request->getParam('filterByDateToOriginal', false), FILTER_SANITIZE_STRING);

            $orderByStatus = filter_var($request->getParam('orderBy', false), FILTER_SANITIZE_STRING);

            if ($gridPageNumber) {
                $offset = $limit * ($gridPageNumber - 1);
            }

            $dbTable = new Models_DbTable_CartSession();

            $select = $dbTable->select()->setIntegrityCheck(false)->from(
                array('scs' => 'shopping_cart_session'),
                array(
                    'scs.id',
                    'customer' => 'user.full_name',
                    'scs.gateway',
                    'scs.total',
                    'scs.purchased_on',
                    'scs.status',
                    'scs.shipping_service',
                    'scs.shipping_availability_days',
                    'scs.shipping_tax',
                    'scs.shipping_price',
                    'viewUrl' => new Zend_Db_Expr("CONCAT('". $this->_websiteUrl ."', 'plugin/shopping/run/order/id/', scs.id)")
                )
            )
                ->join(array('user' => 'user'), 'scs.user_id = user.id', null);

            if(empty($orderByStatus)) {
                $where = '(' . $dbTable->getAdapter()->quoteInto('scs.status = ?', Models_Model_CartSession::CART_STATUS_COMPLETED);
                $where .= ' OR ' . $dbTable->getAdapter()->quoteInto('scs.status = ?', Models_Model_CartSession::CART_STATUS_SHIPPED);
                $where .= ')';
            } elseif($orderByStatus == Models_Model_CartSession::CART_STATUS_SHIPPED){
                $where = $dbTable->getAdapter()->quoteInto('scs.status = ?', Models_Model_CartSession::CART_STATUS_SHIPPED);
            }  else {
                $where = $dbTable->getAdapter()->quoteInto('scs.status = ?', Models_Model_CartSession::CART_STATUS_COMPLETED);
            }

            if ($filterBy) {
                $where .= ' AND (' . $dbTable->getAdapter()->quoteInto('scs.id = ?', $filterBy . '%');
                $where .= ' OR ' . $dbTable->getAdapter()->quoteInto('scs.gateway LIKE ?', $filterBy . '%');
                $where .= ')';
            }

            if($filterByDateFrom) {
                $where .= ' AND ' . $dbTable->getAdapter()->quoteInto('scs.purchased_on >= ?',  $filterByDateFrom . ' 00:00:00' . '%');
            }

            if($filterByDateTo) {
                $where .= ' AND ' . $dbTable->getAdapter()->quoteInto('scs.purchased_on <= ?',  $filterByDateTo . ' 23:59:59' . '%');
            }

            $select->where($where);
            $select->order('scs.purchased_on DESC');

            $adapter = new Zend_Paginator_Adapter_DbSelect($select);
            $ordersData = $adapter->getItems($offset, $limit);

            if (!empty($ordersData)) {
                //pagination
                $paginator = new Zend_Paginator($adapter);
                $paginator->setCurrentPageNumber($gridPageNumber);
                $paginator->setItemCountPerPage($limit);

                $view = new Zend_View(array('scriptPath' => __DIR__ . '/views/'));
                $ordersPager = $view->paginationControl(
                    $paginator,
                    'Sliding',
                    'pagination.phtml',
                    array(
                        'filterBy' => $filterBy,
                        'filterByDateFrom' => $filterByDateFrom,
                        'filterByDateFromOriginal' => $filterByDateFromOriginal,
                        'filterByDateTo' => $filterByDateTo,
                        'filterByDateToOriginal' => $filterByDateToOriginal,
                        'orderBy' => $orderByStatus,
                        'url' => $this->_websiteUrl . $this->_toasterOptions['url']
                    )
                );

                $this->_view->ordersPager = $ordersPager;
                $this->_view->ordersData = $ordersData;
            }

            $shippingPlaginTags = Models_Mapper_ShippingConfigMapper::getInstance()->getShippingPlaginTags();

            $this->_view->shippingPlaginTags = $shippingPlaginTags;

            $shippingTaxRate = Models_Mapper_ShoppingConfig::getInstance()->getConfigParam('shippingTaxRate');

            $this->_view->shippingTaxRate = $shippingTaxRate;

            if (empty($ordersData)) {
                $this->_view->searchErrorMessage = $this->_translator->translate('Any orders don\'t meet the search criteria.');
            }

            $this->_view->pageUrl = $this->_toasterOptions['url'];
            $this->_view->websiteUrl = $this->_websiteUrl;

            $this->_view->filterBy = $filterBy;
            $this->_view->filterByDateFrom = $filterByDateFromOriginal;
            $this->_view->filterByDateTo = $filterByDateToOriginal;
            $this->_view->orderByStatus = $orderByStatus;

            return $this->_view->render('label-generation-grid.phtml');
        }

        return '';
    }
}
