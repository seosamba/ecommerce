<?php
class Models_Model_CartSession extends Application_Model_Models_Abstract {

	/**
	 * New cart registered in system.
	 */
	const CART_STATUS_NEW           = 'new';

	/**
	 * Payment information have been sent to payment gateway but needs to be verified or awaiting confirmation on gateway.
	 * This status will also be used by offline payment options.
	 */
	const CART_STATUS_PENDING       = 'pending';

	/**
	 * Payment information have been sent to payment gateway and waiting for instant response.
	 */
	const CART_STATUS_PROCESSING    = 'processing';

	/**
	 * Payment has been applied on payment gateway or successful transaction notification for pending operation received.
	 */
	const CART_STATUS_COMPLETED     = 'completed';

	/**
	 * Payment transaction has been denied/cancelled.
	 */
	const CART_STATUS_CANCELED      = 'canceled';

	/**
	 * Error occured during payment processing.
	 */
	const CART_STATUS_ERROR         = 'error';

	/**
	 * Order has been sent via shipping service.
	 */
	const CART_STATUS_SHIPPED       = 'shipped';

	/**
	 * Order has been delivered
	 */
	const CART_STATUS_DELIVERED     = 'delivered';

	/**
	 * Order was canceled after successfull payment and money was sent back to customer
	 */
	const CART_STATUS_REFUNDED      = 'refunded';

    /**
     * Order partially paid
     */
	const CART_STATUS_PARTIAL = 'partial';

	const MANUALLY_PAYED_GATEWAY_QUOTE = 'Quote';

	const MANUALLY_PAYED_GATEWAY_MANUALL = 'Manual';

    /**
     * Order not verified
     */
	const CART_STATUS_NOT_VERIFIED = 'not_verified';

	const CART_PARTIAL_PAYMENT_TYPE_AMOUNT = 'amount';

	const CART_PARTIAL_PAYMENT_TYPE_PERCENTAGE = 'percentage';


	protected $_cartContent = null;

	protected $_ipAddress   = '';

	protected $_userId      = null;

	protected $_status      = null;

	protected $_gateway     = null;

	protected $_shippingAddressId = null;

	protected $_billingAddressId = null;

	protected $_shippingPrice = null;

	protected $_shippingType  = null;

	protected $_shippingService = null;

	protected $_subTotal = 0;

	protected $_shippingTrackingId = null;

	protected $_shippingTrackingCodeId = null;

	protected $_totalTax = 0;

	protected $_total = 0;

	protected $_referer = null;

	protected $_createdAt;

	protected $_updatedAt;
    
    protected $_notes = null;

	protected $_discount = 0;

    protected $_shippingTax = 0;

    protected $_discountTax = 0;

    protected $_subTotalTax = 0;

    protected $_discountTaxRate = 0;

    protected $_freeCart = 0;

    protected $_recurringId = null;

    protected $_refundAmount = '';

    protected $_refundNotes = '';

    protected $_shippingServiceId = null;

    protected $_shippingAvailabilityDays = null;

    protected $_shippingServiceInfo = null;

    protected $_shippingLabelLink = null;

    protected $_purchasedOn = null;

    protected $_additionalInfo   = null;

    protected $_isGift = '0';

    protected $_giftEmail = '';

    protected $_orderSubtype = '';

    protected $_partialPercentage = '';

    protected $_isPartial = '';

    protected $_partialPaidAmount = '';

    protected $_partialPurchasedOn = null;

    protected $_partialType = null;

    protected $_partialNotificationDate = null;

    protected $_purchaseErrorMessage = '';

    protected $_isFirstPaymentManuallyPaid = '0';

    protected $_isFullOrderManuallyPaid = '0';

    protected $_isSecondPaymentManuallyPaid = '0';

    protected $_firstPaymentGateway = '';

    protected $_secondPaymentGateway = '';

    protected $_firstPartialPaidAmount = '';

    protected $_secondPartialPaidAmount = '';

    protected $_pickupNotificationSentOn = null;

    protected $_isPickupNotificationSent = '0';

	public function setCartContent($cartContent) {
		$this->_cartContent = $cartContent;
		return $this;
	}

	public function getCartContent() {
		return $this->_cartContent;
	}

	public function setIpAddress($ipAddress) {
		$this->_ipAddress = $ipAddress;
		return $this;
	}

	public function getIpAddress() {
		return $this->_ipAddress;
	}

	public function setId($id) {
		$this->_id = $id;
		return $this;
	}

	public function getId()	{
		return $this->_id;
	}

	public function setUserId($userId) {
		$this->_userId = $userId;
		return $this;
	}

	public function getUserId()	{
		return $this->_userId;
	}

	public function setStatus($status) {
		$this->_status = $status;
		return $this;
	}

	public function getStatus() {
		return $this->_status;
	}

	public function setGateway($gateway) {
		$this->_gateway = $gateway;
		return $this;
	}

	public function getGateway() {
		return $this->_gateway;
	}

	public function setBillingAddressId($billingAddressId) {
		$this->_billingAddressId = $billingAddressId;

		return $this;
	}

	public function getBillingAddressId() {
		return $this->_billingAddressId;
	}

	public function setShippingAddressId($shippingAddressId) {
		$this->_shippingAddressId = $shippingAddressId;

		return $this;
	}

	public function getShippingAddressId() {
		return $this->_shippingAddressId;
	}

	public function setShippingPrice($shippingPrice) {
		$this->_shippingPrice = $shippingPrice;

		return $this;
	}

	public function getShippingPrice() {
		return $this->_shippingPrice;
	}

	public function setShippingService($shippingService) {
		$this->_shippingService = $shippingService;

		return $this;
	}

	public function getShippingService() {
		return $this->_shippingService;
	}

	public function setShippingType($shippingType) {
		$this->_shippingType = $shippingType;

		return $this;
	}

	public function getShippingType() {
		return $this->_shippingType;
	}

	public function setTotal($total) {
		$this->_total = $total;
		return $this;
	}

	public function getTotal() {
		return $this->_total;
	}

	public function setSubTotal($subTotal) {
		$this->_subTotal = $subTotal;
		return $this;
	}

	public function getSubTotal() {
		return $this->_subTotal;
	}

	public function setTotalTax($totalTax) {
		$this->_totalTax = $totalTax;
		return $this;
	}

	public function getTotalTax() {
		return $this->_totalTax;
	}

	public function setShippingTrackingId($shippingTrackingId) {
		$this->_shippingTrackingId = $shippingTrackingId;
		return $this;
	}

	public function getShippingTrackingId() {
		return $this->_shippingTrackingId;
	}

    public function setShippingTrackingCodeId($shippingTrackingCodeId) {
        $this->_shippingTrackingCodeId = $shippingTrackingCodeId;
        return $this;
    }

    public function getShippingTrackingCodeId() {
        return $this->_shippingTrackingCodeId;
    }

	public function setCreatedAt($createdAt) {
		$this->_createdAt = $createdAt;
		return $this;
	}

	public function getCreatedAt() {
		return $this->_createdAt;
	}

	public function setUpdatedAt($updatedAt) {
		$this->_updatedAt = $updatedAt;
		return $this;
	}

	public function getUpdatedAt() {
		return $this->_updatedAt;
	}

	public function setReferer($referer) {
		$this->_referer = $referer;
		return $this;
	}

	public function getReferer() {
		return $this->_referer;
	}
    
    public function setNotes($notes) {
		$this->_notes = $notes;
		return $this;
	}

	public function getNotes() {
		return $this->_notes;
	}

	public function setDiscount($discount) {
		$this->_discount = $discount;
		return $this;
	}

	public function getDiscount() {
		return $this->_discount;
	}

    public function setShippingTax($shippingTax) {
        $this->_shippingTax = $shippingTax;
        return $this;
    }

    public function getShippingTax() {
        return $this->_shippingTax;
    }

    public function setDiscountTax($discountTax) {
        $this->_discountTax = $discountTax;
        return $this;
    }

    public function getDiscountTax() {
        return $this->_discountTax;
    }

    public function setSubTotalTax($subTotalTax) {
        $this->_subTotalTax = $subTotalTax;
        return $this;
    }

    public function getSubTotalTax() {
        return $this->_subTotalTax;
    }

    public function setDiscountTaxRate($discountTaxRate) {
        $this->_discountTaxRate = $discountTaxRate;
        return $this;
    }

    public function getDiscountTaxRate() {
        return $this->_discountTaxRate;
    }

    public function setFreeCart($freeCart) {
        $this->_freeCart = $freeCart;
        return $this;
    }

    public function getFreeCart() {
        return $this->_freeCart;
    }

    /**
     * @return null
     */
    public function getRecurringId()
    {
        return $this->_recurringId;
    }

    /**
     * @param null $recurringId
     * @return null
     */
    public function setRecurringId($recurringId)
    {
        $this->_recurringId = $recurringId;
        return $this;
    }

    /**
     * @return string
     */
    public function getRefundAmount()
    {
        return $this->_refundAmount;
    }

    /**
     * @param string $refundAmount
     * @return string
     */
    public function setRefundAmount($refundAmount)
    {
        $this->_refundAmount = $refundAmount;

        return $this;
    }

    /**
     * @return string
     */
    public function getRefundNotes()
    {
        return $this->_refundNotes;
    }

    /**
     * @param string $refundNotes
     * @return string
     */
    public function setRefundNotes($refundNotes)
    {
        $this->_refundNotes = $refundNotes;

        return $this;
    }

    /**
     * @return null
     */
    public function getShippingServiceId()
    {
        return $this->_shippingServiceId;
    }

    /**
     * @param null $shippingServiceId
     * @return null
     */
    public function setShippingServiceId($shippingServiceId)
    {
        $this->_shippingServiceId = $shippingServiceId;

        return $this;
    }

    /**
     * @return null
     */
    public function getShippingAvailabilityDays()
    {
        return $this->_shippingAvailabilityDays;
    }

    /**
     * @param null $shippingAvailabilityDays
     * @return null
     */
    public function setShippingAvailabilityDays($shippingAvailabilityDays)
    {
        $this->_shippingAvailabilityDays = $shippingAvailabilityDays;

        return $this;
    }

    /**
     * @return null
     */
    public function getShippingServiceInfo()
    {
        return $this->_shippingServiceInfo;
    }

    /**
     * @param null $shippingServiceInfo
     * @return null
     */
    public function setShippingServiceInfo($shippingServiceInfo)
    {
        $this->_shippingServiceInfo = $shippingServiceInfo;

        return $this;
    }

    /**
     * @return null
     */
    public function getShippingLabelLink()
    {
        return $this->_shippingLabelLink;
    }

    /**
     * @param null $shippingLabelLink
     * @return null
     */
    public function setShippingLabelLink($shippingLabelLink)
    {
        $this->_shippingLabelLink = $shippingLabelLink;
    }

    public function getPurchasedOn()
    {
        return $this->_purchasedOn;
    }

    /**
     * @param null $purchasedOn
     * @return null
     */
    public function setPurchasedOn($purchasedOn)
    {
        $this->_purchasedOn = $purchasedOn;

        return $this;
    }

    /**
     * @return null
     */
    public function getAdditionalInfo()
    {
        return $this->_additionalInfo;
    }

    /**
     * @param null $additionalInfo
     */
    public function setAdditionalInfo($additionalInfo)
    {
        $this->_additionalInfo = $additionalInfo;
        return $this;
    }

    /**
     * @return string
     */
    public function getIsGift()
    {
        return $this->_isGift;
    }

    /**
     * @param string $isGift
     * @return string
     */
    public function setIsGift($isGift)
    {
        $this->_isGift = $isGift;

        return $this;
    }

    /**
     * @return string
     */
    public function getGiftEmail()
    {
        return $this->_giftEmail;
    }

    /**
     * @param string $giftEmail
     * @return string
     */
    public function setGiftEmail($giftEmail)
    {
        $this->_giftEmail = $giftEmail;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderSubtype()
    {
        return $this->_orderSubtype;
    }

    /**
     * @param string $orderSubtype
     * @return Models_Model_CartSession
     */
    public function setOrderSubtype($orderSubtype)
    {
        $this->_orderSubtype = $orderSubtype;
        return $this;
    }

    /**
     * @return string
     */
    public function getPartialPercentage()
    {
        return $this->_partialPercentage;
    }

    /**
     * @param string $partialPercentage
     * @return string
     */
    public function setPartialPercentage($partialPercentage)
    {
        $this->_partialPercentage = $partialPercentage;

        return $this;
    }

    /**
     * @return string
     */
    public function getIsPartial()
    {
        return $this->_isPartial;
    }

    /**
     * @param string $isPartial
     * @return string
     */
    public function setIsPartial($isPartial)
    {
        $this->_isPartial = $isPartial;

        return $this;
    }

    /**
     * @return string
     */
    public function getPartialPaidAmount()
    {
        return $this->_partialPaidAmount;
    }

    /**
     * @param string $partialPaidAmount
     * @return string
     */
    public function setPartialPaidAmount($partialPaidAmount)
    {
        $this->_partialPaidAmount = $partialPaidAmount;

        return $this;
    }

    /**
     * @return null
     */
    public function getPartialPurchasedOn()
    {
        return $this->_partialPurchasedOn;
    }

    /**
     * @param null $partialPurchasedOn
     * @return null
     */
    public function setPartialPurchasedOn($partialPurchasedOn)
    {
        $this->_partialPurchasedOn = $partialPurchasedOn;

        return $this;
    }

    /**
     * @return null
     */
    public function getPartialType()
    {
        return $this->_partialType;
    }

    /**
     * @param null $partialType
     * @return null
     */
    public function setPartialType($partialType)
    {
        $this->_partialType = $partialType;

        return $this;
    }

    /**
     * @return null
     */
    public function getPartialNotificationDate()
    {
        return $this->_partialNotificationDate;
    }

    /**
     * @param null $partialNotificationDate
     * @return null
     */
    public function setPartialNotificationDate($partialNotificationDate)
    {
        $this->_partialNotificationDate = $partialNotificationDate;

    }

    /**
     * @return string
     */
    public function getPurchaseErrorMessage()
    {
        return $this->_purchaseErrorMessage;
    }

    /**
     * @param string $purchaseErrorMessage
     * @return string
     */
    public function setPurchaseErrorMessage($purchaseErrorMessage)
    {
        $this->_purchaseErrorMessage = $purchaseErrorMessage;

        return $this;
    }

    /**
     * @return string
     */
    public function getIsFirstPaymentManuallyPaid()
    {
        return $this->_isFirstPaymentManuallyPaid;
    }

    /**
     * @param string $isFirstPaymentManuallyPaid
     * @return string
     */
    public function setIsFirstPaymentManuallyPaid($isFirstPaymentManuallyPaid)
    {
        $this->_isFirstPaymentManuallyPaid = $isFirstPaymentManuallyPaid;

        return $this;
    }

    /**
     * @return string
     */
    public function getIsFullOrderManuallyPaid()
    {
        return $this->_isFullOrderManuallyPaid;
    }

    /**
     * @param string $isFullOrderManuallyPaid
     * @return string
     */
    public function setIsFullOrderManuallyPaid($isFullOrderManuallyPaid)
    {
        $this->_isFullOrderManuallyPaid = $isFullOrderManuallyPaid;

        return $this;
    }

    /**
     * @return string
     */
    public function getIsSecondPaymentManuallyPaid()
    {
        return $this->_isSecondPaymentManuallyPaid;
    }

    /**
     * @param string $isSecondPaymentManuallyPaid
     * @return string
     */
    public function setIsSecondPaymentManuallyPaid($isSecondPaymentManuallyPaid)
    {
        $this->_isSecondPaymentManuallyPaid = $isSecondPaymentManuallyPaid;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstPaymentGateway()
    {
        return $this->_firstPaymentGateway;
    }

    /**
     * @param string $firstPaymentGateway
     * @return string
     */
    public function setFirstPaymentGateway($firstPaymentGateway)
    {
        $this->_firstPaymentGateway = $firstPaymentGateway;

        return $this;
    }

    /**
     * @return string
     */
    public function getSecondPaymentGateway()
    {
        return $this->_secondPaymentGateway;
    }

    /**
     * @param string $secondPaymentGateway
     * @return string
     */
    public function setSecondPaymentGateway($secondPaymentGateway)
    {
        $this->_secondPaymentGateway = $secondPaymentGateway;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstPartialPaidAmount()
    {
        return $this->_firstPartialPaidAmount;
    }

    /**
     * @param string $firstPartialPaidAmount
     */
    public function setFirstPartialPaidAmount($firstPartialPaidAmount)
    {
        $this->_firstPartialPaidAmount = $firstPartialPaidAmount;
    }

    /**
     * @return string
     */
    public function getSecondPartialPaidAmount()
    {
        return $this->_secondPartialPaidAmount;
    }

    /**
     * @param string $secondPartialPaidAmount
     */
    public function setSecondPartialPaidAmount($secondPartialPaidAmount)
    {
        $this->_secondPartialPaidAmount = $secondPartialPaidAmount;
    }

    /**
     * @return null
     */
    public function getPickupNotificationSentOn()
    {
        return $this->_pickupNotificationSentOn;
    }

    /**
     * @param null $pickupNotificationSentOn
     */
    public function setPickupNotificationSentOn($pickupNotificationSentOn)
    {
        $this->_pickupNotificationSentOn = $pickupNotificationSentOn;
    }

    /**
     * @return string
     */
    public function getIsPickupNotificationSent()
    {
        return $this->_isPickupNotificationSent;
    }

    /**
     * @param string $isPickupNotificationSent
     */
    public function setIsPickupNotificationSent($isPickupNotificationSent)
    {
        $this->_isPickupNotificationSent = $isPickupNotificationSent;
    }

}
