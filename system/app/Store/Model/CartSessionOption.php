<?php
/**
 *
 *
 */
class Store_Model_CartSessionOption extends Application_Model_Models_Abstract {

	protected $_cartId = '';

	protected $_productId = '';

	protected $_optionId = '';

	protected $_optionTitle = '';

	protected $_optionType = '';

	protected $_title = '';

	protected $_priceSign = '';

	protected $_priceValue = '';

	protected $_priceType = '';

	protected $_weightSign = '';

	protected $_weightValue = '';

	protected $_cartContentId = '';

	protected $_cartItemKey = '';

	protected $_cartItemOptionKey = '';

	protected $_optionSelectionId = '';

    /**
     * @return string
     */
    public function getCartId()
    {
        return $this->_cartId;
    }

    /**
     * @param string $cartId
     * @return string
     */
    public function setCartId($cartId)
    {
        $this->_cartId = $cartId;

        return $this;
    }

    /**
     * @return string
     */
    public function getProductId()
    {
        return $this->_productId;
    }

    /**
     * @param string $productId
     * @return string
     */
    public function setProductId($productId)
    {
        $this->_productId = $productId;

        return $this;
    }

    /**
     * @return string
     */
    public function getOptionId()
    {
        return $this->_optionId;
    }

    /**
     * @param string $optionId
     * @return string
     */
    public function setOptionId($optionId)
    {
        $this->_optionId = $optionId;

        return $this;
    }

    /**
     * @return string
     */
    public function getOptionTitle()
    {
        return $this->_optionTitle;
    }

    /**
     * @param string $optionTitle
     * @return string
     */
    public function setOptionTitle($optionTitle)
    {
        $this->_optionTitle = $optionTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getOptionType()
    {
        return $this->_optionType;
    }

    /**
     * @param string $optionType
     * @return string
     */
    public function setOptionType($optionType)
    {
        $this->_optionType = $optionType;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * @param string $title
     * @return string
     */
    public function setTitle($title)
    {
        $this->_title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getPriceSign()
    {
        return $this->_priceSign;
    }

    /**
     * @param string $priceSign
     * @return string
     */
    public function setPriceSign($priceSign)
    {
        $this->_priceSign = $priceSign;

        return $this;
    }

    /**
     * @return string
     */
    public function getPriceValue()
    {
        return $this->_priceValue;
    }

    /**
     * @param string $priceValue
     * @return string
     */
    public function setPriceValue($priceValue)
    {
        $this->_priceValue = $priceValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getPriceType()
    {
        return $this->_priceType;
    }

    /**
     * @param string $priceType
     * @return string
     */
    public function setPriceType($priceType)
    {
        $this->_priceType = $priceType;

        return $this;
    }

    /**
     * @return string
     */
    public function getWeightSign()
    {
        return $this->_weightSign;
    }

    /**
     * @param string $weightSign
     * @return string
     */
    public function setWeightSign($weightSign)
    {
        $this->_weightSign = $weightSign;

        return $this;
    }

    /**
     * @return string
     */
    public function getWeightValue()
    {
        return $this->_weightValue;
    }

    /**
     * @param string $weightValue
     * @return string
     */
    public function setWeightValue($weightValue)
    {
        $this->_weightValue = $weightValue;

        return $this;
    }

    /**
     * @return string
     */
    public function getCartContentId()
    {
        return $this->_cartContentId;
    }

    /**
     * @param string $cartContentId
     * @return string
     */
    public function setCartContentId($cartContentId)
    {
        $this->_cartContentId = $cartContentId;

        return $this;
    }

    /**
     * @return string
     */
    public function getCartItemKey()
    {
        return $this->_cartItemKey;
    }

    /**
     * @param string $cartItemKey
     * @return string
     */
    public function setCartItemKey($cartItemKey)
    {
        $this->_cartItemKey = $cartItemKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getCartItemOptionKey()
    {
        return $this->_cartItemOptionKey;
    }

    /**
     * @param string $cartItemOptionKey
     * @return string
     */
    public function setCartItemOptionKey($cartItemOptionKey)
    {
        $this->_cartItemOptionKey = $cartItemOptionKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getOptionSelectionId()
    {
        return $this->_optionSelectionId;
    }

    /**
     * @param string $optionSelectionId
     * @return string
     */
    public function setOptionSelectionId($optionSelectionId)
    {
        $this->_optionSelectionId = $optionSelectionId;

        return $this;
    }



}