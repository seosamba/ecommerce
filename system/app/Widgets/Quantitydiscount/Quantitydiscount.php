<?php

/**
 * Class Widgets_Discountquantity_Discountquantity
 */
class Widgets_Quantitydiscount_Quantitydiscount extends Widgets_Abstract
{

    /**
     * type units
     */
    const PRICE_TYPE_UNIT = 'unit';

    /**
     * type percent
     */
    const PRICE_TYPE_PERCENT = 'percent';

    /**
     * local discount flag
     */

    const LOCAL_DISCOUNT_ENABLED = 'enabled';

    /**
     * @var Models_Mapper_ProductMapper Product Mapper
     */
    protected $_productMapper;

    /**
     * @var array Contains payment config
     * @static
     */
    protected static $_shoppingConfig = null;

    /**
     * @var Models_Model_Product Product instance
     */
    protected $_product = null;

    /**
     * @var null|Zend_Currency Zend_Currency holder
     */
    private $_currency = null;

    private $_type = null;

    protected $_cacheable = false;

    protected function _init()
    {
        parent::_init();
    }

    protected function _load()
    {
        if (empty($this->_options)) {
            throw new Exceptions_SeotoasterWidgetException('No options provided');
        }

        self::$_shoppingConfig || self::$_shoppingConfig = Models_Mapper_ShoppingConfig::getInstance()->getConfigParams(
        );

        $this->_view = new Zend_View(array(
            'scriptPath' => dirname(__FILE__) . '/views'
        ));

        $this->_productMapper = Models_Mapper_ProductMapper::getInstance();
        if ($this->_currency === null) {
            $this->_currency = Zend_Registry::isRegistered('Zend_Currency') ? Zend_Registry::get(
                'Zend_Currency'
            ) : new Zend_Currency();
        }
        $this->_view->currency = $this->_currency;
        if (is_numeric($this->_options[0])) {
            $this->_product = $this->_productMapper->find($this->_options[0]);
            $this->_type = $this->_options[1];
        } else {
            $this->_product = $this->_productMapper->findByPageId($this->_toasterOptions['id']);
            $this->_type = array_shift($this->_options);
        }
        if (!$this->_product instanceof Models_Model_Product) {
            if (Tools_Security_Acl::isAllowed(Tools_Security_Acl::RESOURCE_ADMINPANEL) || is_null($this->_type)) {
                return '<b>Product does not exist or wrong options provided</b>';
            }
            return '<!--Product does not exist or wrong options provided-->';
        }

        $this->_view->product = $this->_product;

        $methodName = '_render' . ucfirst(strtolower($this->_type));
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        }
        return '<b>Method ' . $this->_type . ' doesn\'t exist</b>';
    }

    /**
     * Render information for each quantity discounts
     *
     * @return string
     */
    private function _renderGrid()
    {
        $quantityDiscountConfig = Store_Mapper_DiscountMapper::getInstance()->getDiscountDataConfig(
            $this->_product->getId(),
            false,
            false,
            true
        );
        $quantityDiscountData = array();
        $currentPrice = $this->_product->getCurrentPrice();
        if (empty($currentPrice)) {
            $currentPrice = $this->_product->getPrice();
        }

        if ((bool)self::$_shoppingConfig['showPriceIncTax']) {
            $productTax = Tools_Tax_Tax::calculateProductTax($this->_product);
            $productPriceWithTax = $productTax + $currentPrice;
        }
        if (!empty($quantityDiscountConfig)) {
            foreach ($quantityDiscountConfig as $configItem) {
                if ($configItem['status'] === self::LOCAL_DISCOUNT_ENABLED) {
                    $configItem['discount'] = $configItem['amount'];
                    $configItem['sign'] = $configItem['price_sign'];
                    $configItem['type'] = $configItem['price_type'];

                    $quantityDiscountData[$configItem['quantity']]['price'] = Tools_DiscountTools::applyDiscountData(
                        $currentPrice,
                        $configItem
                    );
                    $configItem['unitSave'] = $currentPrice - $quantityDiscountData[$configItem['quantity']]['price'];

                    if ((bool)self::$_shoppingConfig['showPriceIncTax']) {
                        $quantityDiscountPriceTax = Tools_Tax_Tax::calculateDiscountTax(
                            $quantityDiscountData[$configItem['quantity']]['price'],
                            $this->_product->getTaxClass()
                        );

                        $quantityDiscountData[$configItem['quantity']]['price'] = $quantityDiscountData[$configItem['quantity']]['price'] + $quantityDiscountPriceTax;
                        $quantityDiscountData[$configItem['quantity']]['discount_price'] = $productPriceWithTax - $quantityDiscountData[$configItem['quantity']]['price'];
                    }else{
                        $quantityDiscountData[$configItem['quantity']]['discount_price'] = $configItem['discount'];
                    }
                    $quantityDiscountData[$configItem['quantity']]['type'] = $configItem['price_type'];
                    $quantityDiscountData[$configItem['quantity']]['discount'] = $configItem['discount'];
                    $quantityDiscountData[$configItem['quantity']]['unitSave'] = $configItem['unitSave'];
                }
            }
        }
        $this->_view->quantityDiscountPrices = $quantityDiscountData;
        return $this->_view->render('discount-quantity-grid.phtml');
    }

}
