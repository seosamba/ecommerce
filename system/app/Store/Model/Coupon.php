<?php
/**
 * Coupon
 *
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Store_Model_Coupon extends Application_Model_Models_Abstract {

	const COUPON_TYPE_DISCOUNT      = 'discount';

	const COUPON_TYPE_FREESHIPPING  = 'freeshipping';

	const DISCOUNT_UNITS     = 'unit';

	const DISCOUNT_PERCENTS   = 'percent';

	const DISCOUNT_SCOPE_CLIENT = 'client';

	const DISCOUNT_SCOPE_ORDER = 'order';

	protected $_code;

	protected $_products;

	protected $_startDate;

	protected $_endDate;

	protected $_allowCombination;

	protected $_type;

	protected $_scope;

	protected $_action;

	protected $_data = array();

	public function setAllowCombination($allowCombination) {
		$this->_allowCombination = $allowCombination;
		return $this;
	}

	public function getAllowCombination() {
		return $this->_allowCombination;
	}

	public function setCode($code) {
		$this->_code = $code;
		return $this;
	}

	public function getCode() {
		return $this->_code;
	}

	public function setEndDate($endDate) {
		$this->_endDate = $endDate;
		return $this;
	}

	public function getEndDate() {
		return $this->_endDate;
	}

	public function setStartDate($startDate) {
		$this->_startDate = $startDate;
		return $this;
	}

	public function getStartDate() {
		return $this->_startDate;
	}

	public function setType($type) {
		$this->_type = $type;
		return $this;
	}

	public function getType() {
		return $this->_type;
	}

	public function setScope($scope) {
		$this->_scope = $scope;
		return $this;
	}

	public function getScope() {
		return $this->_scope;
	}

	public function setProducts($products) {
		$this->_products = $products;
		return $this;
	}

	public function getProducts() {
		return $this->_products;
	}


	public function __call($name, $arguments) {
		$prefix     = strtolower(substr($name, 0, 3));
		$varname    = lcfirst(substr($name, 3));

		if ($prefix === 'set'){
			if (strtolower($varname) === 'data'){
				$this->_data = $arguments[0];
			} else {
				$this->_data[$varname] = $arguments[0];
			}
			return $this;
		}

		if ($prefix === 'get') {
			if (strtolower($varname) === 'data'){
				return $this->_data;
			}
			return isset($this->_data[$varname]) ? $this->_data[$varname] : null;
		}
	}

	public function __toString() {
		/**
		 * @var $currency Zend_Currency
		 */
		$currency = Zend_Registry::get('Zend_Currency');

		$string = '';
		switch ($this->_type){
			case self::COUPON_TYPE_DISCOUNT:
				if ($this->getDiscountUnit() === self::DISCOUNT_UNITS ){
					$string = sprintf('%s for orders over %s', $currency->toCurrency(floatval($this->getDiscountAmount())), $currency->toCurrency(floatval($this->getMinOrderAmount())));
				} else {
					$string = sprintf('%s for orders over %s', $this->getDiscountAmount().'%', $currency->toCurrency(floatval($this->getMinOrderAmount())));
				}
				break;
			case self::COUPON_TYPE_FREESHIPPING:
				$string = sprintf('Free shipping for orders over %s', $currency->toCurrency(floatval($this->getMinOrderAmount())) );
				break;
		}
		return $string;
	}

	public function getAction(){
		return $this->__toString();
	}

}