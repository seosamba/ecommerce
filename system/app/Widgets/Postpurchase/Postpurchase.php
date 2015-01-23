<?php

/**
 * Postpurchase widget. Process data for postpurchase page (where user arrives after successful checkout)
 *
 * Class Widgets_Postpurchase_Postpurchase
 */
class Widgets_Postpurchase_Postpurchase extends Widgets_Abstract
{

    /**
     * Clean cart params without currency, html etc...
     */
    const CLEAN_CART_PARAM = 'clean';

    /**
     * Show price without price
     */
    const WITHOUT_TAX = 'withouttax';

    /**
     * shipping or billing address element state
     */
    const ADDRESS_STATE = 'state';

    /**
     * shipping or billing address element country
     */
    const ADDRESS_COUNTRY = 'country';

    /**
     * shipping address
     */
    const ADDRESS_TYPE_SHIPPING = 'shipping';

    /**
     * billing address
     */
    const ADDRESS_TYPE_BILLING = 'billing';

    /**
     * Website config
     *
     * @var null
     */
    protected $_websiteHelper = null;

    /**
     * full cart
     *
     * @var null
     */
    protected $_cart = null;

    /**
     * Cart content (products)
     *
     * @var null
     */
    protected $_cartContent = null;

    /**
     * translation
     *
     * @var null
     */
    protected $_translator = null;

    /**
     * Shopping config
     *
     * @var array
     */
    protected $_shoppingConfig = array();

    protected $_session = null;

    protected $_cacheable = false;


    /**
     * Prepare cart content
     */
    protected function  _init()
    {
        parent::_init();
        $this->_view = new Zend_View(array(
            'scriptPath' => __DIR__ . '/views/'
        ));
        $this->_websiteHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('website');
        $this->_view->websiteUrl = $this->_websiteHelper->getUrl();
        $this->_translator = Zend_Registry::get('Zend_Translate');
        $this->_session = Zend_Controller_Action_HelperBroker::getExistingHelper('session');
        if (Zend_Registry::isRegistered('postPurchaseCart')) {
            $this->_cart = Zend_Registry::get('postPurchaseCart');
        } elseif (isset($this->_session->storeCartSessionConversionKey)) {
            $this->_cart = Models_Mapper_CartSessionMapper::getInstance()->find(
                intval($this->_session->storeCartSessionConversionKey)
            );
            if ($this->_cart instanceof Models_Model_CartSession) {
                $productMapper = Models_Mapper_ProductMapper::getInstance();
                $cartContent = $this->_cart->getCartContent();
                foreach ($cartContent as $key => $product) {
                    $productObject = $productMapper->find($product['product_id']);
                    if ($productObject instanceof Models_Model_Product) {
                        $cartContent[$key]['mpn'] = $productObject->getMpn();
                        $cartContent[$key]['photo'] = $productObject->getPhoto();
                        $cartContent[$key]['productUrl'] = $productObject->getPage()->getUrl();
                        $cartContent[$key]['taxRate'] = Tools_Tax_Tax::calculateProductTax($productObject, null, true);
                    }
                }
                $this->_cart->setCartContent($cartContent);
                $billingAddressId = $this->_cart->getBillingAddressId();
                if (null !== $billingAddressId) {
                    $this->_cart->setBillingAddressId(Tools_ShoppingCart::getAddressById($billingAddressId));
                }
                $shippingAddressId = $this->_cart->getShippingAddressId();
                if (null !== $shippingAddressId) {
                    $this->_cart->setShippingAddressId(Tools_ShoppingCart::getAddressById($shippingAddressId));
                }

            }
            Zend_Registry::set('postPurchaseCart', $this->_cart);
            unset($this->_session->storeCartSessionConversionKey);
        }
        if ($this->_cart instanceof Models_Model_CartSession) {
            $this->_cartContent = $this->_cart->getCartContent();
        }
        $this->_shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
    }

    protected function _load()
    {
        if (!isset($this->_options[0]) || empty($this->_cart)) {
            return '';
        }

        //Analyze single cart item
        if (in_array('cartitem', $this->_options, true)) {
            unset($this->_options[array_search('cartitem', $this->_options, true)]);
            $sid = array_shift($this->_options);
            if (isset($this->_cartContent[$sid])) {
                $option = strtolower(array_shift($this->_options));
                $rendererName = '_renderCartItem' . ucfirst($option);
                if (method_exists($this, $rendererName)) {
                    return $this->$rendererName($sid);
                }
            }
        }elseif(in_array('config', $this->_options, true)) {
            if(isset($this->_shoppingConfig[$this->_options[1]])){
                return $this->_shoppingConfig[$this->_options[1]];
            }
            return '';
        } else {
            $option = strtolower(array_shift($this->_options));
            $methodName = '_render' . ucfirst(strtolower(trim($option)));
            if (method_exists($this, $methodName)) {
                return $this->$methodName();
            }
        }
        return '';
    }

    /**
     * Return cart total include tax, discount and shipping price
     *
     * @return mixed
     */
    protected function _renderTotal()
    {
        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $this->_cart->getTotal();
        }
        return $this->_cart->getTotal();
    }

    /**
     * Return cart subtotal include tax if enable
     *
     * @return mixed
     */
    protected function _renderSubtotal()
    {
        $subTotal = (is_null($this->_cart->getSubTotal())) ? 0 : $this->_cart->getSubTotal();
        if (intval($this->_shoppingConfig['showPriceIncTax']) === 1 && $subTotal != 0 && !in_array(
            self::WITHOUT_TAX,
            $this->_options
        )
        ) {
            $subTotal = $subTotal + $this->_cart->getSubTotalTax();
        }
        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $subTotal;
        }
        return $this->_view->currency($subTotal);
    }

    /**
     * Return cart total tax
     *
     * @return mixed
     */
    protected function _renderTotaltax()
    {
        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $this->_cart->getTotalTax();
        }
        return $this->_view->currency($this->_cart->getTotalTax());
    }

    /**
     * Return payment gateway name
     *
     * @return mixed
     */
    protected function _renderGateway()
    {
        return $this->_cart->getGateway();
    }

    /**
     * Return billing address element
     *
     * @return string
     */
    protected function _renderBilling()
    {
        $billingAddress = $this->_cart->getBillingAddressId();
        if (null !== $billingAddress) {
            return $this->_prepareAddress(self::ADDRESS_TYPE_BILLING);
        }
        return '';

    }

    /**
     * Return shipping address element
     *
     * @return string
     */
    protected function _renderShipping()
    {
        $shippingAddress = $this->_cart->getShippingAddressId();
        if (null !== $shippingAddress) {
            return $this->_prepareAddress(self::ADDRESS_TYPE_SHIPPING);
        }
        return '';
    }

    /**
     * Return cart shipping price. Depends on tax include config.
     *
     * @return mixed
     */
    protected function _renderShippingprice()
    {
        $shippingPrice = (is_null($this->_cart->getShippingPrice())) ? 0 : $this->_cart->getShippingPrice();
        if (intval($this->_shoppingConfig['showPriceIncTax']) === 1 && $shippingPrice != 0) {
            $shippingPrice = $shippingPrice + $this->_cart->getShippingTax();
        }
        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $shippingPrice;
        }
        return $this->_view->currency($shippingPrice);
    }

    /**
     * Return shipping type
     *
     * @return mixed
     */
    protected function _renderShippingtype()
    {
        return $this->_cart->getShippingType();
    }

    /**
     * Return shipping service type
     *
     * @return mixed
     */

    protected function _renderShippingservice()
    {
        $shippingService = 'Shipping Address';
        if ($this->_cart->getShippingService() === Shopping::SHIPPING_PICKUP) {
            $shippingService = 'Pickup information';
        }
        return $this->_translator->translate($shippingService);
    }

    /**
     * Return cart referer
     *
     * @return mixed
     */
    protected function _renderReferer()
    {
        return $this->_cart->getReferer();
    }

    /**
     * Return cart created date in d-M-Y format
     *
     * @return string
     */

    protected function _renderCreated()
    {
        return date("d-M-Y", strtotime($this->_cart->getCreatedAt()));
    }

    /**
     * Return cart updated date in d-M-Y format
     *
     * @return string
     */

    protected function _renderUpdated()
    {
        return date("d-M-Y", strtotime($this->_cart->getUpdatedAt()));
    }

    /**
     * Return cart notes
     *
     * @return mixed
     */

    protected function _renderNotes()
    {
        return $this->_cart->getNotes();
    }

    /**
     * Return cart discount. Depends on tax include config.
     *
     * @return mixed
     */
    protected function _renderDiscount()
    {
        $discount = (is_null($this->_cart->getDiscount())) ? 0 : $this->_cart->getDiscount();
        if (intval($this->_shoppingConfig['showPriceIncTax']) === 1 && $discount != 0) {
            $discount = $discount + $this->_cart->getDiscountTax();
        }
        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $discount;
        }
        return $this->_view->currency($discount);
    }


    /**
     * Return cart shipping tax
     *
     * @return mixed
     *
     */
    protected function _renderShippingtax()
    {
        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $this->_cart->getShippingTax();
        }
        return $this->_view->currency($this->_cart->getShippingTax());

    }

    /**
     * Return cart discount tax
     *
     * @return mixed
     */
    protected function _renderDiscounttax()
    {
        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $this->_cart->getDiscountTax();
        }
        return $this->_view->currency($this->_cart->getDiscountTax());
    }


    /**
     * Return cart subtotal tax
     *
     * @return mixed
     */
    protected function _renderSubtotaltax()
    {
        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $this->_cart->getSubTotalTax();
        }
        return $this->_view->currency($this->_cart->getSubTotalTax());

    }

    /**
     * Return cart id
     *
     * @return int
     */
    protected function _renderId()
    {
        return intval($this->_cart->getId());
    }

    /**
     * Return product sku for single item in cart
     *
     * @param $sid
     * @return string
     */
    protected function _renderCartItemSku($sid)
    {
        return $this->_cartContent[$sid]['sku'];
    }

    /**
     * Return product mpn for single item in cart
     *
     * @param $sid
     * @return string
     */
    protected function _renderCartItemMpn($sid)
    {
        return $this->_cartContent[$sid]['mpn'];
    }

    /**
     * Return product price without tax for single item in cart
     *
     * @param $sid
     * @return mixed
     */
    protected function _renderCartItemPrice($sid)
    {
        $price = (is_null($this->_cartContent[$sid]['price'])) ? 0 : $this->_cartContent[$sid]['price'];
        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $price;
        } elseif (intval($this->_cartContent[$sid]['freebies']) === 1) {
            return $this->_translator->translate('free');
        }
        return $this->_view->currency($price);
    }

    /**
     * Return product quantity for single item in cart
     *
     * @param $sid
     * @return int
     */

    protected function _renderCartItemQty($sid)
    {
        return $this->_cartContent[$sid]['qty'];
    }

    /**
     * Return product name for single item in cart
     *
     * @param $sid
     * @return string
     */
    protected function _renderCartItemName($sid)
    {
        return $this->_cartContent[$sid]['name'];
    }

    /**
     * Return product tax for single item in cart
     * @param $sid
     * @return mixed
     */
    protected function _renderCartItemTax($sid)
    {
        $productTax = $this->_cartContent[$sid]['tax'];
        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $productTax;
        }
        return $this->_view->currency($productTax);
    }

    /**
     * Return product price with tax for single item in cart
     *
     * @param $sid
     * @return mixed
     */
    protected function _renderCartItemTaxprice($sid)
    {
        $price = (is_null($this->_cartContent[$sid]['tax_price'])) ? 0 : $this->_cartContent[$sid]['tax_price'];
        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $price;
        } elseif (intval($this->_cartContent[$sid]['freebies']) === 1) {
            return $this->_translator->translate('free');
        }
        return $this->_view->currency($price);
    }

    /**
     * Return product freebies for single item in cart
     *
     * @param $sid
     * @return mixed
     */
    protected function _renderCartItemFreebies($sid)
    {
        return $this->_cartContent[$sid]['freebies'];
    }

    /**
     * Return cart id of purchase
     *
     * @param $sid
     * @return int
     */
    protected function _renderCartItemCartid($sid)
    {
        return $this->_cart->getId();
    }

    protected function _renderCartItemTotal($sid)
    {
        $priceWithTax = (is_null($this->_cartContent[$sid]['tax_price'])) ? 0 : $this->_cartContent[$sid]['tax_price'];
        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $priceWithTax * $this->_cartContent[$sid]['qty'];
        } elseif (intval($this->_cartContent[$sid]['freebies']) === 1) {
            return $this->_translator->translate('free');
        }
        return $this->_view->currency($priceWithTax * $this->_cartContent[$sid]['qty']);
    }

    /**
     * Return product photo for single item in cart
     *
     * @param $sid
     * @return string
     */
    protected function _renderCartItemPhoto($sid)
    {
        if (isset($this->_options[0])) {
            $folder = $this->_options[0];
        } else {
            $folder = 'product';
        }
        $photoSrc = $this->_cartContent[$sid]['photo'];
        $photoSrc = Tools_Misc::prepareProductImage($photoSrc, $folder);
        return '<img class="cart-product-image" src="' . $photoSrc . '" alt="' . $this->_cartContent[$sid]['name'] . '">';
    }


    /**
     * Return product options for single item in cart
     *
     * @param $sid
     * @return string
     */
    protected function _renderCartItemOptions($sid)
    {
        $productOptions = $this->_cartContent[$sid]['options'];
        if (!empty($productOptions)) {
            $optionResult = '';
            foreach ($productOptions as $optionTitle => $optData) {
                if (is_array($optData)) {
                    $optionStr = $optionTitle . ': ' . $optData['title'];
                    if (isset($optData['priceValue']) && intval($optData['priceValue'])) {
                        if ((bool)$this->_cartContent[$sid]['taxRate'] && (bool)$this->_shoppingConfig['showPriceIncTax'] === true) {
                            $optPriceMod = $optData['priceValue'] * (100 + $this->_cartContent[$sid]['taxRate']) / 100;
                        } else {
                            $optPriceMod = $optData['priceValue'];
                        }
                        $optionStr .= '<span>(&nbsp;' . $optData['priceSign'] . $this->_view->currency(
                            $optPriceMod
                        ) . '&nbsp;)</span>';
                    }
                    if (isset($optData['weightValue']) && intval($optData['weightValue'])) {
                        $optionStr .= '<span>(&nbsp;' . $optData['weightSign'] . ' ' . $optData['weightValue'] . ' ' . $this->_shoppingConfig['weightUnit'] . '&nbsp;)</span>';
                    }
                } else {
                    $optData = trim($optData);
                    if (!empty($optData)) {
                        $optionStr = $optionTitle . ': ' . $optData;
                    } else {
                        $optionStr = '';
                    }
                }
                $optionResult .= '<span class="post-purchase-report-product-options">' . $optionStr . '</span>';
            }
            return $optionResult;
        }
    }

    /**
     * Return product page url for single item in cart
     *
     * @param $sid
     * @return mixed
     */
    protected function _renderCartItemProducturl($sid)
    {
        return $this->_cartContent[$sid]['productUrl'];
    }

    /**
     * Return proper address element
     *
     * @param $addressType
     *
     */
    private function _prepareAddress($addressType)
    {
        if ($addressType === self::ADDRESS_TYPE_SHIPPING) {
            $addressData = $this->_cart->getShippingAddressId();
        }
        if ($addressType === self::ADDRESS_TYPE_BILLING) {
            $addressData = $this->_cart->getBillingAddressId();
        }
        if (isset($this->_options[0])) {
            $addressKey = $this->_options[0];
            if (isset($addressData[$addressKey])) {
                if (self::ADDRESS_COUNTRY === $addressKey) {
                    if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
                        return $addressData[$addressKey];
                    }
                    $countries = Tools_Geo::getCountries(true);
                    return $countries[$addressData[$addressKey]];

                }
                if (self::ADDRESS_STATE === $addressKey) {
                    $state = Tools_Geo::getStateById($addressData[$addressKey]);
                    if (!empty($state['state'])) {
                        return $state['state'];
                    }
                    return '';
                }
                return $addressData[$addressKey];
            }
        }
        return '';
    }

}
