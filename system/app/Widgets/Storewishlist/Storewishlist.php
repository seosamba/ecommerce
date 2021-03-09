<?php

/**
 * Class Widgets_Storewishlist_Storewishlist
 */
class Widgets_Storewishlist_Storewishlist extends Widgets_Abstract {

    const DEFAULT_LIMIT = 20;

    protected $_cacheable      = false;

    protected $_redirector = null;

    protected $_websiteHelper = null;

    protected $_request = null;

    protected $_sessionHelper;

    protected $_productMapper = null;

    protected  $_limit = null;

    protected $_productTemplate = null;

    protected $_cleanListOnly = false;

    protected function _load() {
        $this->_request = Zend_Controller_Front::getInstance()->getRequest();
        $this->_productMapper = Models_Mapper_ProductMapper::getInstance();

        $currentController = $this->_request->getParam('controller');
        if (!preg_match('~backend_~', $currentController)) {
            $layout = Zend_Layout::getMvcInstance();
            $layout->getView()->inlineScript()
                ->appendFile($this->_websiteHelper->getUrl() . 'plugins/shopping/web/js/storewishlist.min.js');
        }

        $methodName = Tools_Plugins_Abstract::OPTION_MAKER_PREFIX.ucfirst(strtolower($this->_options[0]));
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }
    }

    protected function _init() {
        parent::_init();
        if (empty($this->_options)) {
            throw new Exceptions_SeotoasterWidgetException('No options provided');
        }
        $this->_view = new Zend_View();
        $this->_view->websiteUrl = Zend_Controller_Action_HelperBroker::getExistingHelper('website')->getUrl();
        $this->_view->setScriptPath(realpath(__DIR__.DIRECTORY_SEPARATOR.'views'));
        $this->_websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
        $this->_redirector = new Zend_Controller_Action_Helper_Redirector();
        $this->_sessionHelper = Zend_Controller_Action_HelperBroker::getExistingHelper('session');
    }

    /**
     * {$storewishlist:addtowishlist:{$product:id}[:htmlclass:calass class2 class3[:btnname:sometext[:profile]]]]}
     *
     * @return string
     * @throws Zend_Exception
     */
    protected function _makeOptionAddToWishList() {
        $translator = Zend_Registry::get('Zend_Translate');

        $toFavoritesBtnName = $translator->translate('Add to favorites');
        $readyWished = false;
        $isLogged = false;
        $goToProgile = 0;
        $htmlClassName = '';

        if(in_array('btnname', $this->_options)) {
            $btnOptionKey = array_search('btnname', $this->_options);
            $btnName = $this->_options[$btnOptionKey+1];
            if(!empty($btnName)) {
                $toFavoritesBtnName = preg_replace('~[^A-Za-z\s]+~','',$btnName);
            }
        }

        if(in_array('htmlclass', $this->_options)) {
            $classOptionKey = array_search('htmlclass', $this->_options);
            $htmlClassName = $this->_options[$classOptionKey+1];
            if(!empty($htmlClassName)) {
                $htmlClassName = preg_replace('~[^a-z1-9-_\s]+~','', $htmlClassName);
            }
        }

        $clientPage = Application_Model_Mappers_PageMapper::getInstance()->fetchByOption(Shopping::OPTION_STORE_CLIENT_LOGIN, true);
        $page = $this->_websiteHelper->getDefaultPage();
        if ($clientPage != null) {
            $page = $clientPage->getUrl();
        }

        $user = $this->_sessionHelper->getCurrentUser();
        $userId = $user->getId();

        if ($userId) {
            if(!empty($this->_options[1]) && is_numeric($this->_options[1])) {
                $productId = intval($this->_options[1]);
                if(!empty($productId)) {
                    $this->_view->productId = $productId;
                    $wishedProductsMapper = Store_Mapper_WishedProductsMapper::getInstance();
                    $wishedProduct = $wishedProductsMapper->findByUserIdProductId($userId, $productId);

                    if($wishedProduct instanceof Store_Model_WishedProducts) {
                        $readyWished = true;
                    }
                }
            }

            if(in_array('profile', $this->_options)) {
                $goToProgile = 1;
            }
            $isLogged = true;
        }

        $this->_view->isLogged = $isLogged;
        $this->_view->clientPage = $page;
        $this->_view->readyWished = $readyWished;
        $this->_view->toFavoritesBtnName = $toFavoritesBtnName;
        $this->_view->goToProgile = $goToProgile;
        $this->_view->htmlClass = $htmlClassName;

        return $this->_view->render('to-client-page.phtml');
    }

    /**
     * {$storewishlist:removeproduct:{$product:id}[htmlclass:calass class2 class3[:btnname:sometext]]}
     *
     * @return string
     * @throws Zend_Exception
     */
    protected function  _makeOptionRemoveproduct() {

        $currentUserModel = $this->_sessionHelper->getCurrentUser();
        $userRole = $currentUserModel->getRoleId();
        $htmlClassName = '';

        if($userRole !== Tools_Security_Acl::ROLE_GUEST) {
            $translator = Zend_Registry::get('Zend_Translate');
            if(!empty($this->_options[1]) && is_numeric($this->_options[1])) {
                $productId = intval($this->_options[1]);
                if(!empty($productId)) {
                    $this->_view->productId = $productId;

                    if(in_array('htmlclass', $this->_options)) {
                        $classOptionKey = array_search('htmlclass', $this->_options);
                        $htmlClassName = $this->_options[$classOptionKey+1];
                        if(!empty($htmlClassName)) {
                            $htmlClassName = preg_replace('~[^a-z1-9-_\s]+~','', $htmlClassName);
                        }
                    }

                    $this->_view->htmlClass = $htmlClassName;

                    $btnOptionName = $translator->translate('Remove wished product');
                    $useBtn = false;
                    if(in_array('btnname', $this->_options)) {
                        $useBtn = true;
                        $btnOptionKey = array_search('btnname', $this->_options);
                        $btnName = $this->_options[$btnOptionKey+1];
                        if(!empty($btnName)) {
                            $btnOptionName = preg_replace('~[^A-Za-z\s]+~','',$btnName);
                        }
                    }
                    $this->_view->useBtn = $useBtn;
                    $this->_view->btnName = $btnOptionName;
                    $this->_view->translator = $translator;

                    return $this->_view->render('remove-product.phtml');
                }
            }
        }
    }

    /**
     * {$storewishlist:wishList:_products wishlist list[:limit[:10]]}
     *
     * @return string
     * @throws Exceptions_SeotoasterWidgetException
     * @throws Zend_Exception
     */
    protected function _makeOptionWishList() {
        $currentUserModel = $this->_sessionHelper->getCurrentUser();
        $userRole = $currentUserModel->getRoleId();

        if($userRole !== Tools_Security_Acl::ROLE_GUEST){
            $userId = $currentUserModel->getId();

            if(!empty($this->_options[1])) {
                $this->_productTemplate = Application_Model_Mappers_TemplateMapper::getInstance()->find($this->_options[1]);
                if ($this->_productTemplate === null) {
                    throw new Exceptions_SeotoasterWidgetException('Product template doesn\'t exist');
                }

                $last = end($this->_options);

                $this->_productMapper = Models_Mapper_ProductMapper::getInstance();
                $wishListMapper = Store_Mapper_WishedProductsMapper::getInstance();

                $products = $wishListMapper->findProductsByUserId($userId);

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
                $productIds = array();

                if(!empty($products)){
                    foreach ($products as $product){
                        $productIds[] = $product['productId'];
                    }

                    $content = $this->_processList($productIds);

                    if ($this->_cleanListOnly) {
                        return $content;
                    }

                    $this->_view->productIds = implode(',' , $productIds);
                    $this->_view->plContent = $content;
                    $this->_view->pageId = $this->_toasterOptions['id'];
                    $this->_view->productTemplate = $this->_productTemplate->getName();

                    return $this->_view->render('wishlist-products-list.phtml');
                }
            }
        }
    }

    /**
     * @param array $productIds
     * @return string
     * @throws Zend_Exception
     */
    protected function _processList($productIds = array()) {
        // loading product listing template
        $template = $this->_productTemplate;
        $products = $this->_products;

        if(!empty($productIds) && empty($products)){
            $products = $this->_loadProducts(true, $productIds);
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

            if(is_null($inventoryCount) || !empty($inventoryCount)) {
                $inventoryCount = $this->_translator->translate('In stock');
            } else {
                $inventoryCount = $this->_translator->translate('Out of stock');
            }

            $dictionary = array(
                '$product:name'                       => $product->getName(),
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
                '$product:qty'                        => $productQty
            );
            $renderedContent[] = Tools_Misc::preparingProductListing($templatePrepend.$data['templateContent'], $product, $dictionary, $data['noZeroPrice']);
        });

        $this->_cacheTags = array_merge($this->_cacheTags, $cacheTags);
        return implode('', $renderedContent);
    }

    /**
     * @param bool $enabled
     * @param array $productIds
     * @return mixed
     */
    private function _loadProducts($enabled = true, $productIds = array()) {

        $enabledOnly = $this->_productMapper->getDbTable()->getAdapter()->quoteInto('p.enabled=?', $enabled);

        $idsWhere = Zend_Db_Table_Abstract::getDefaultAdapter()->quoteInto('p.id IN (?)', $productIds);

        if (!empty($idsWhere)) {
            $enabledOnly = $idsWhere . ' AND ' . $enabledOnly;
        }

        return $this->_productMapper->fetchAll($enabledOnly, null, (isset($this->_options[0]) && is_numeric($this->_options[0]) ? intval($this->_options[0]) : null), $this->_limit, null, null, null, false,false,array(),array(), null);
    }

    /**
     * @param $products
     * @return $this
     */
    public function setProducts($products) {
        $this->_products = $products;
        return $this;
    }

    /**
     * @param $cleanListOnly
     * @return $this
     */
    public function setCleanListOnly($cleanListOnly) {
        $this->_cleanListOnly = $cleanListOnly;
        return $this;
    }

    /**
     * {$storewishlist:lastaddeduserwishlist:{$product:id}}
     */
    protected function _makeOptionLastAddedUserWishlist() {
        if(!empty($this->_options[1]) && is_numeric($this->_options[1])) {
            $productId = intval($this->_options[1]);

            if(!empty($productId)) {
                $wishedProductsMapper = Store_Mapper_WishedProductsMapper::getInstance();

                $lastUser = $wishedProductsMapper->findLastUserAdded($productId);

                if(!empty($lastUser)) {
                    $this->_view->lastUserName = $lastUser['full_name'];
                }

                $this->_view->productId = $productId;

                return $this->_view->render('last-added-user.phtml');
            }
        }
    }



}
