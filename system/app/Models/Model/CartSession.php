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
	 *
	 */
	const CART_STATUS_UNPROCESSED   = 'unprocessed';

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

	protected $_totalTax = 0;

	protected $_total = 0;

	protected $_referer = null;

	protected $_createdAt;

	protected $_updatedAt;
    
    protected $_notes = null;
    

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

}
