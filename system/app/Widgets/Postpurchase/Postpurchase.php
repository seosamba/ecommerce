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
     * Wrap link description
     */
    const WRAP_DESCRIPTION_LINK = 'wraplink';

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
     * @var null|Zend_Currency Zend_Currency holder
     */
    private $_currency = null;


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
                        $cartContent[$key]['short_description'] = $productObject->getShortDescription();
                        $cartContent[$key]['full_description'] = $productObject->getFullDescription();
                        $cartContent[$key]['brand'] = $productObject->getBrand();
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

        //initializing Zend Currency for future use
        if ($this->_currency === null){
            $this->_currency = Zend_Registry::isRegistered('Zend_Currency') ? Zend_Registry::get('Zend_Currency') : new Zend_Currency();
        }
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
        $total = $this->_cart->getTotal();

        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $total;
        }

        $total = $this->_currency->toCurrency($total);

        return $total;
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

        $subTotal = $this->_currency->toCurrency($subTotal);

        return $subTotal;
    }

    /**
     * Return cart total tax
     *
     * @return mixed
     */
    protected function _renderTotaltax()
    {
        $totalTax = $this->_cart->getTotalTax();

        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $totalTax;
        }

        $totalTax = $this->_currency->toCurrency($totalTax);

        return $totalTax;
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
        if (intval($this->_shoppingConfig['showPriceIncTax']) === 1 && $shippingPrice != 0 && !in_array(self::WITHOUT_TAX, $this->_options)) {
            $shippingPrice = $shippingPrice + $this->_cart->getShippingTax();
        }

        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $shippingPrice;
        }

        $shippingPrice = $this->_currency->toCurrency($shippingPrice);

        return $shippingPrice;
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
     * @return mixed
     */
    protected function _renderUserId()
    {
        return $this->_cart->getUserId();
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
        } else {
            $shippingService = $this->_cart->getShippingService();
            $serviceLabelMapper = Models_Mapper_ShoppingShippingServiceLabelMapper::getInstance();
            $shippingServiceLabel = $serviceLabelMapper->findByName($shippingService);
        }
        return !empty($shippingServiceLabel) ? $shippingServiceLabel : $this->_translator->translate($shippingService);
    }

    /**
     * @return string Coupon Name
     */
    protected function _renderCoupon()
    {
        $couponName = '';
        $cartId = $this->_cart->getId();
        $coupon = Store_Mapper_CouponMapper::getInstance()->findByCartId($cartId);
        if(!empty($coupon)){
            $couponName = $coupon['coupon_code'];
        }
       return $this->_translator->translate($couponName);
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
     * Return cart additional info
     *
     * @return mixed
     */

    protected function _renderAdditionalInfo()
    {
        return $this->_cart->getAdditionalInfo();
    }

    /**
     * Return cart discount. Depends on tax include config.
     *
     * @return mixed
     */
    protected function _renderDiscount()
    {
        $discount = (is_null($this->_cart->getDiscount())) ? 0 : $this->_cart->getDiscount();
        if (intval($this->_shoppingConfig['showPriceIncTax']) === 1 && $discount != 0 && !in_array(self::WITHOUT_TAX, $this->_options)) {
            $discount = $discount + $this->_cart->getDiscountTax();
        }

        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $discount;
        }

        $discount = $this->_currency->toCurrency($discount);

        return $discount;
    }


    /**
     * Return cart shipping tax
     *
     * @return mixed
     *
     */
    protected function _renderShippingtax()
    {
        $shippingTax = $this->_cart->getShippingTax();

        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $shippingTax;
        }

        $shippingTax = $this->_currency->toCurrency($shippingTax);

        return $shippingTax;

    }

    /**
     * Return cart discount tax
     *
     * @return mixed
     */
    protected function _renderDiscounttax()
    {
        $discountTax = $this->_cart->getDiscountTax();

        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $discountTax;
        }

        $discountTax = $this->_currency->toCurrency($discountTax);

        return $discountTax;
    }


    /**
     * Return cart subtotal tax
     *
     * @return mixed
     */
    protected function _renderSubtotaltax()
    {
        $subTotalTax = $this->_cart->getSubTotalTax();

        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $subTotalTax;
        }

        $subTotalTax = $this->_currency->toCurrency($subTotalTax);

        return $subTotalTax;

    }

    protected function _renderRefundamount()
    {
        if($this->_cart->getRefundAmount() === null) {
            return '';
        }

        $refundAmount = $this->_cart->getRefundAmount();

        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $refundAmount;
        }

        $refundAmount = $this->_currency->toCurrency($refundAmount);

        return $refundAmount;
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
     * Return is a gift message
     *
     * @return string
     */
    protected function _renderIsGift()
    {
       if (!empty($this->_cart->getIsGift())) {
           if (!empty($this->_options[0])) {
               return $this->_options[0];
           }
           return $this->_translator->translate('Is a gift');
       }

       return '';

    }

    /**
     * Return email of the gift receiver
     *
     * @return string
     */
    protected function _renderGiftEmail()
    {
        if (!empty($this->_cart->getIsGift()) && !empty($this->_cart->getGiftEmail())) {
            return $this->_cart->getGiftEmail();
        }

        return '';

    }


    /**
     * Return product sku for single item in cart
     *
     * @param $sid
     * @return string
     */
    protected function _renderCartItemSku($sid)
    {
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '';
        }

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
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '';
        }

        return $this->_cartContent[$sid]['mpn'];
    }

    /**
     * Return product gtin for single item in cart
     *
     * @param $sid
     * @return string
     */
    protected function _renderCartItemGtin($sid)
    {
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '';
        }

        return $this->_cartContent[$sid]['gtin'];
    }

    /**
     * Return product price without tax for single item in cart
     *
     * @param $sid
     * @return mixed
     */
    protected function _renderCartItemPrice($sid)
    {
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '';
        }

        $price = (is_null($this->_cartContent[$sid]['price'])) ? 0 : $this->_cartContent[$sid]['price'];

        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $price;
        } elseif (intval($this->_cartContent[$sid]['freebies']) === 1) {
            return $this->_translator->translate('free');
        }

        $price = $this->_currency->toCurrency($price);

        return $price;
    }

    /**
     * Return product quantity for single item in cart
     *
     * @param $sid
     * @return int
     */

    protected function _renderCartItemQty($sid)
    {
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '';
        }

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
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '&nbsp;';
        }

        return $this->_cartContent[$sid]['name'];
    }

    /**
     * Return product short description for single item in cart
     *
     * @param $sid
     * @return string
     */
    protected function _renderCartItemShortdescription($sid)
    {
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '';
        }

        if (!empty($this->_cartContent[$sid]['short_description'])) {
            if (in_array(self::WRAP_DESCRIPTION_LINK, $this->_options, true) && preg_match('~((http|https):\/\/(.*))~ui', $this->_cartContent[$sid]['short_description'], $matched)) {
                if (!empty($matched) && !empty($matched['0']) && !empty($this->_options[1])) {
                    return '<a target="_blank" href="' . trim($matched['0']) . '">' . $this->_options[1] . '</a>';
                }
            }

            return $this->_cartContent[$sid]['short_description'];
        }

        return '';
    }


    /**
     * Return product full description for single item in cart
     *
     * @param $sid
     * @return string
     */
    protected function _renderCartItemFulldescription($sid)
    {
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '';
        }

        if (!empty($this->_cartContent[$sid]['full_description'])) {
            if (in_array(self::WRAP_DESCRIPTION_LINK, $this->_options, true) && preg_match('~((http|https):\/\/(.*))~ui', $this->_cartContent[$sid]['full_description'], $matched)) {
                if (!empty($matched) && !empty($matched['0']) && !empty($this->_options[1])) {
                    return '<a target="_blank" href="' . trim($matched['0']) . '">' . $this->_options[1] . '</a>';
                }
            }
            return $this->_cartContent[$sid]['full_description'];
        }

        return '';
    }

    /**
     * Return product tax for single item in cart
     * @param $sid
     * @return mixed
     */
    protected function _renderCartItemTax($sid)
    {
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '';
        }

        $productTax = $this->_cartContent[$sid]['tax'];

        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $productTax;
        }

        $productTax = $this->_currency->toCurrency($productTax);

        return $productTax;
    }

    /**
     * Return product price with tax for single item in cart
     *
     * @param $sid
     * @return mixed
     */
    protected function _renderCartItemTaxprice($sid)
    {
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '';
        }

        $price = (is_null($this->_cartContent[$sid]['tax_price'])) ? 0 : $this->_cartContent[$sid]['tax_price'];

        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $price;
        } elseif (intval($this->_cartContent[$sid]['freebies']) === 1) {
            return $this->_translator->translate('free');
        }

        $price = $this->_currency->toCurrency($price);

        return $price;
    }

    /**
     * Return product freebies for single item in cart
     *
     * @param $sid
     * @return mixed
     */
    protected function _renderCartItemFreebies($sid)
    {
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '';
        }

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
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '';
        }

        return $this->_cart->getId();
    }

    protected function _renderCartItemTotal($sid)
    {
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '';
        }

        $priceWithTax = (is_null($this->_cartContent[$sid]['tax_price'])) ? 0 : $this->_cartContent[$sid]['tax_price'];
        $priceWithTax = $priceWithTax * $this->_cartContent[$sid]['qty'];

        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return $priceWithTax;
        } elseif (intval($this->_cartContent[$sid]['freebies']) === 1) {
            return $this->_translator->translate('free');
        }

        $priceWithTax = $this->_currency->toCurrency($priceWithTax);

        return $priceWithTax;
    }

    /**
     * Return product photo for single item in cart
     *
     * @param $sid
     * @return string
     */
    protected function _renderCartItemPhoto($sid)
    {
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '';
        }

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
     * Return product photo for single item in cart
     *
     * @param $sid
     * @return string
     */
    protected function _renderCartItemPhotourl($sid)
    {
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '';
        }

        if (isset($this->_options[0])) {
            $folder = $this->_options[0];
        } else {
            $folder = 'product';
        }
        $photoSrc = $this->_cartContent[$sid]['photo'];
        $photoSrc = Tools_Misc::prepareProductImage($photoSrc, $folder);
        return $photoSrc;
    }


    /**
     * Return product options for single item in cart
     *
     * @param $sid
     * @return string
     */
    protected function _renderCartItemOptions($sid)
    {
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '';
        }

        $productOptions = $this->_cartContent[$sid]['options'];
        if (!empty($productOptions)) {
            $optionResult = '';
            foreach ($productOptions as $optionTitle => $optData) {
                if (is_array($optData)) {
                    $optDataTitle = trim($optData['title']);
                    if (!empty($optDataTitle)) {
                        $optionStr = '<span>'.$optionTitle. ':</span> <span>'.$optData['title'].'</span> ';

                        if(!empty($optData['optionType']) && $optData['optionType'] == Models_Model_Option::TYPE_ADDITIONALPRICEFIELD) {
                            $optionStr = '<span>'.$optionTitle. ':</span>';
                            if (in_array(self::CLEAN_OPTIONS_PRICE, $this->_options)) {
                                $optionStr .= '<span>'.$optData['title'].'</span> ';
                            }
                        }
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
                                $optPriceMod = $this->_currency->toCurrency($optPriceMod);

                                $optionStr .= '<span>(' . $optData['priceSign'] . $optPriceMod .')</span>';
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
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '';
        }

        return $this->_cartContent[$sid]['productUrl'];
    }

    /**
     * * Return product brand for single item in cart
     *
     * @param $sid
     * @return mixed
     */
    protected function _renderCartItemBrand($sid)
    {
        if($this->_cartContent[$sid]['price'] == 0 && empty($this->_cartContent[$sid]['isEnabled'])) {
            return '';
        }

        return $this->_cartContent[$sid]['brand'];
    }

    /**
     * Return Quote disclaimer
     *
     * @return mixed
     */
    protected function _renderQuoteNote(){
        $cartId = $this->_cart->getId();
        $quote = Quote_Models_Mapper_QuoteMapper::getInstance()->findByCartId($cartId);
        if(!empty($quote)){
            return $quote->getDisclaimer();
        }
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
                } elseif(self::ADDRESS_MOBILE === $addressKey) {
                    return $addressData['mobile_country_code_value'].$addressData[$addressKey];
                }
                if (self::ADDRESS_PHONE === $addressKey && in_array(self::CLEAN_CART_PARAM, $this->_options)) {
                    return str_replace('+', '', $addressData[$addressKey]);
                } elseif(self::ADDRESS_PHONE === $addressKey) {
                    return $addressData['phone_country_code_value'].$addressData[$addressKey];
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
        return Tools_Geo::getStateByParam($stateId);
    }


    /**
     * Return partial amount
     *
     * @return mixed
     */
    protected function _renderPartialamount(){
        $cartId = $this->_cart->getId();
        $quote = Quote_Models_Mapper_QuoteMapper::getInstance()->findByCartId($cartId);
        if(!empty($quote)){
            $partialAmountPaid = $this->_cart->getPartialPaidAmount();
            if (!empty((int) $partialAmountPaid)) {
                if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
                    return $this->_cart->getPartialPaidAmount();
                }

                return $this->_view->currency($this->_cart->getPartialPaidAmount());
            }
        }
    }

    /**
     * Return partial percentage
     *
     * @return mixed
     */
    protected function _renderPartialpercentage(){
        $cartId = $this->_cart->getId();
        $quote = Quote_Models_Mapper_QuoteMapper::getInstance()->findByCartId($cartId);
        if(!empty($quote)){
            $partialAmountPaid = $this->_cart->getPartialPercentage();
            if (!empty((int) $partialAmountPaid)) {
                return round($this->_cart->getPartialPercentage(), 1);
            }

            return '';
        }
    }

    protected function _renderOutstandingamount()
    {
        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return round($this->_cart->getTotal() - $this->_cart->getPartialPaidAmount(),2);
        }

        return $this->_view->currency(round($this->_cart->getTotal() - $this->_cart->getPartialPaidAmount(), 2));
    }

    protected function _renderCompletionpaymentamount()
    {

        if (in_array(self::CLEAN_CART_PARAM, $this->_options)) {
            return round($this->_cart->getTotal() - round(($this->_cart->getTotal() * $this->_cart->getPartialPercentage()) / 100,
                    2), 2);
        }

        return $this->_view->currency(round($this->_cart->getTotal() - round(($this->_cart->getTotal() * $this->_cart->getPartialPercentage()) / 100,
                2), 2));


        return '';
    }

}
