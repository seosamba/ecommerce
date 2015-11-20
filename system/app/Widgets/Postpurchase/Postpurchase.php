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
     * Remove price value from options
     */
    const CLEAN_OPTIONS_PRICE = 'cleanOptionPrice';

    /**
     * Add html wrapper if this option used into email template
     */
    const EMAIL_FORMAT = 'email';

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
     * shipping or billing address element phone
     */
    const ADDRESS_PHONE = 'phone';

    /**
     * shipping or billing address element mobile
     */
    const ADDRESS_MOBILE = 'mobile';

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
            if (!Zend_Registry::isRegistered('postPurchasePickup') && $this->_cart instanceof Models_Model_CartSession && $this->_cart->getShippingService() === 'pickup') {
                $pickupLocationConfigMapper = Store_Mapper_PickupLocationConfigMapper::getInstance();
                $pickupLocationData = $pickupLocationConfigMapper->getCartPickupLocationByCartId($this->_cart->getId());
                if (empty($pickupLocationData)) {
                    $shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams();
                    $pickupLocationData = array(
                        'name' => $shoppingConfig['company'],
                        'address1' => $shoppingConfig['address1'],
                        'address2' => $shoppingConfig['address2'],
                        'country' => $shoppingConfig['country'],
                        'city' => $shoppingConfig['city'],
                        'state' => $shoppingConfig['state'],
                        'zip' => $shoppingConfig['zip'],
                        'phone' => $shoppingConfig['phone']
                    );
                }
                $pickupLocationData['map_link'] = 'https://maps.google.com/?q='.$pickupLocationData['address1'].'+'.$pickupLocationData['city'].'+'.$pickupLocationData['state'];
                $pickupLocationData['map_src'] = Tools_Geo::generateStaticGmaps($pickupLocationData, 640, 300);
                Zend_Registry::set('postPurchasePickup', $pickupLocationData);
            }
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
        $widgetName = '{$postpurchase:' . implode(':', $this->_options) . '}';

        //Analyze single cart item
        if (in_array('cartitem', $this->_options, true)) {
            unset($this->_options[array_search('cartitem', $this->_options, true)]);
            $sid = array_shift($this->_options);
            if (isset($this->_cartContent[$sid]) && is_numeric($sid)) {
                $option = strtolower(array_shift($this->_options));
                $rendererName = '_renderCartItem' . ucfirst($option);
                if (method_exists($this, $rendererName)) {
                    return $this->$rendererName($sid);
                }
            } else {
                return $widgetName;
            }
        } elseif (in_array('config', $this->_options, true)) {
            if (isset($this->_shoppingConfig[$this->_options[1]])) {
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
        return $this->_view->currency($this->_cart->getTotal());
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

    protected function _renderRefundamount()
    {
        if($this->_cart->getRefundAmount() === null) {
            return '';
        }

        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $this->_cart->getRefundAmount();
        }
        return $this->_view->currency($this->_cart->getRefundAmount());
    }

    protected function _renderRefundNotes()
    {
        if($this->_cart->getRefundNotes() === null) {
            return '';
        }
        return $this->_cart->getRefundNotes();
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
                    $optDataTitle = trim($optData['title']);
                    if (!empty($optDataTitle)) {
                        $optionStr = '<span>'.$optionTitle. ':</span> <span>'.$optData['title'].'</span> ';
                    } else {
                        $optionStr = '';
                    }
                    if (isset($optData['priceValue']) && intval($optData['priceValue'])) {
                        if ((bool)$this->_cartContent[$sid]['taxRate'] && (bool)$this->_shoppingConfig['showPriceIncTax'] === true) {
                            $optPriceMod = $optData['priceValue'] * (100 + $this->_cartContent[$sid]['taxRate']) / 100;
                        } else {
                            $optPriceMod = $optData['priceValue'];
                        }
                        if (!in_array(self::CLEAN_OPTIONS_PRICE, $this->_options)) {
                            if ($optData['priceType'] === 'percent') {
                                $optionStr .= '<span>(' . $optData['priceSign'] . '%'. number_format($optPriceMod, 2) .')</span>';
                            } else {
                                $optionStr .= '<span>(' . $optData['priceSign'] . $this->_view->currency($optPriceMod) .')</span>';
                            }
                        }
                    }
                    if (isset($optData['weightValue']) && intval($optData['weightValue'])) {
                        $optionStr .= '<span>(' . $optData['weightSign'] . ' ' . $optData['weightValue'] . ' ' . $this->_shoppingConfig['weightUnit'] . ')</span>';
                    }
                } else {
                    $optData = trim($optData);
                    if (!empty($optData)) {
                        $optionStr = $optionTitle . ': ' . $optData;
                    } else {
                        $optionStr = '';
                    }
                }
                $optionResult .= '<div class="options">' . $optionStr . '</div>';
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
     * Return text if this order is recurring payment
     *
     * @return string
     */
    private function _renderRecurring()
    {
        if (isset($this->_options[0])) {
            $cartId = $this->_cart->getId();
            $recurringPayment = Store_Mapper_RecurringPaymentsMapper::getInstance()->checkRecurringOrder($cartId);
            if (!empty($recurringPayment)) {
                return $this->_options[0];
            }
        }

        return '';
    }

    /**
     * Return pickup location address element
     *
     * Can be used any keys from $pickupLocationData.
     * All widgets should be in {postpurchasepickup} magicspace.
     * Widgets exaples:
     * {$postpurchase:pickup:address1} - Display address
     * {$postpurchase:pickup:address1[:label:Working hours]} - Display address with custom label
     * {$postpurchase:pickup:working_hours[:sunday]} - Display working hours for Sunday
     * {$postpurchase:pickup:country[:clean]} - Display shortcode of country
     *
     * @return string
     */
    protected function _renderPickup()
    {
        if (!Zend_Registry::isRegistered('postPurchasePickup')) {
            return '';
        }
        $pickupLocationData = Zend_Registry::get('postPurchasePickup');
        $labelExists = array_search('label', $this->_options);
        if ($labelExists && $this->_options[$labelExists + 1]) {
            $label = $this->_translator->translate(filter_var($this->_options[$labelExists + 1],
                FILTER_SANITIZE_STRING));
        }
        if (!empty($this->_options[0]) && array_key_exists($this->_options[0], $pickupLocationData)) {
            $param = $this->_options[0];
            if (array_key_exists($param, $pickupLocationData)) {
                $result = false;
                if ($param === self::ADDRESS_STATE) {
                    $result = $this->getState($pickupLocationData[$param]);
                }
                if ($param === self::ADDRESS_COUNTRY) {
                    if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
                        $result = $pickupLocationData[$param];
                    } else {
                        $countries = Tools_Geo::getCountries(true);
                        $result = $countries[$pickupLocationData[$param]];
                    }
                }
                if ($param === 'working_hours') {
                    $wh = unserialize($pickupLocationData[$param]);
                    if (!empty($this->_options[1]) && array_key_exists($this->_options[1], $wh)) {
                        $result = $wh[$this->_options[1]];
                    } else {
                        $wh = array_filter($wh);
                        $result = implode(', ', array_map(function ($v, $k) {
                            return ucfirst($k) . ': ' . $v;
                        }, $wh, array_keys($wh)));
                    }
                }
                if ($result !== false) {
                    return (!empty($label) && !empty($result)) ? $label . ' ' . $result : $result;
                } else {
                    return (!empty($label) && !empty($pickupLocationData[$param])) ? $label . ' ' . $pickupLocationData[$param] : $pickupLocationData[$param];
                }
            }
        }
    }

    /**
     * Return proper address element
     *
     * @param string $addressType (billing, shipping)
     * @return string
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
                    return $this->getState($addressData[$addressKey]);
                }
                if (self::ADDRESS_MOBILE === $addressKey && in_array(self::CLEAN_CART_PARAM, $this->_options)) {
                   return str_replace('+', '', $addressData[$addressKey]);
                }
                if (self::ADDRESS_PHONE === $addressKey && in_array(self::CLEAN_CART_PARAM, $this->_options)) {
                    return str_replace('+', '', $addressData[$addressKey]);
                }
                return $addressData[$addressKey];
            }
        }
        return '';
    }

    /**
     * @param $stateId
     * @return string
     */
    protected function getState($stateId) {
        $state = Tools_Geo::getStateById($stateId);
        if (!empty($state['state'])) {
            return $state['state'];
        }
        return '';
    }

}
