<?php
/**
 * CouponMapper.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 *
 * @method Store_Mapper_CouponMapper   getInstance()   getInstance()   Returns an instance of itself
 * @method Zend_Db_Table    getDbTable()    getDbTable()    Returns an instance of DbTable
 */
class Store_Mapper_CouponMapper extends Application_Model_Mappers_Abstract {

	protected $_model   = 'Store_Model_Coupon';

	protected $_dbTable = 'Store_DbTable_Coupon';

    /**
     * Save coupon model to DB
     * @param $model Store_Model_Coupon
     * @return Store_Model_Coupon
     * @throws Exceptions_SeotoasterException
     */
	public function save($model) {
		if (!$model instanceof $this->_model){
			$model = new $this->_model($model);
		}

		$data = $model->toArray();
		if (isset($data['action'])){
			unset($data['action']);
		}
		if (isset($data['products'])){
			unset($data['products']);
		}

		if ($model->getId()){
			$this->getDbTable()->update($data, array('id = ?', $model->getId()));
		} else {
			$id = $this->getDbTable()->insert($data);
			if ($id){
				$model->setId($id);
			} else {
				throw new Exceptions_SeotoasterException('Can\'t save coupon');
			}
		}

		$extraMethod = '_save'.ucfirst(strtolower($model->getType())).'CouponData';
		if (method_exists($this, $extraMethod)){
			$this->$extraMethod($model);
		}

		$this->_saveCouponToProduct($model);

		return $model;
	}

	/**
	 * Method saves coupon to product relations if any
	 * @param Store_Model_Coupon $coupon
	 * @return mixed
	 */
	protected function _saveCouponToProduct(Store_Model_Coupon $coupon){
		$products = $coupon->getProducts();

		if ($products){
			$dbTable = new Store_DbTable_CouponProduct();

			if (!is_array($products)){
				$products = array($products);
			}
			$dbTable->delete(array('coupon_id = ?' => $coupon->getId()));
			$data = array();
			foreach ($products as $pid) {
				$dbTable->insert(array(
					'coupon_id' => intval($coupon->getId()),
					'product_id' => intval($pid)
				));
			}
		}

		return $this;
	}

	/**
	 * Save discount coupon data to proper table
	 * @param Store_Model_Coupon $coupon
	 * @return mixed
	 */
	protected function _saveDiscountCouponData(Store_Model_Coupon $coupon){
		$dbTable = new Store_DbTable_CouponDiscount();

		$row = $dbTable->fetchRow(array('coupon_id = ?' => $coupon->getId()));

		if ($row) {
			return $row->setFromArray($coupon->getData())->save();
		} else {
			$row = $dbTable->createRow($coupon->getData());
			$row->coupon_id = $coupon->getId();
			return $row->save();
		}
	}

	protected function _saveFreeshippingCouponData(Store_Model_Coupon $coupon){
		$dbTable = new Store_DbTable_CouponFreeshipping();

		$row = $dbTable->fetchRow(array('coupon_id = ?' => $coupon->getId()));

		if ($row) {
			return $row->setFromArray($coupon->getData())->save();
		} else {
			$row = $dbTable->createRow($coupon->getData());
			$row->coupon_id = $coupon->getId();
			return $row->save();
		}
	}

	public function find($id) {
		$coupon = parent::find($id);

		if ($coupon){

		}

		return $coupon;
	}

	public function fetchAll($where = null, $order = array()) {
		$coupons = parent::fetchAll($where, $order);

		if (!empty($coupons)){
			$coupons = array_map(array($this, '_loadCouponData'), $coupons);    //loading additional data
		}

		return $coupons;
	}

	private function _loadCouponData(Store_Model_Coupon $coupon){
		$this->_loadCouponProducts($coupon);

		$methodName = '_load'.ucfirst(strtolower($coupon->getType())).'CouponData';
		if (method_exists($this, $methodName)){
			return $this->$methodName($coupon);
		}
		return $coupon;
	}

	private function _loadCouponProducts(Store_Model_Coupon $coupon){
		$dbTable = new Store_DbTable_CouponProduct();

		$select = $dbTable->select()->from('shopping_coupon_product', array('product_id'))->where('coupon_id = ?', $coupon->getId());
		$productCoupons = $dbTable->getAdapter()->fetchCol($select);
		if (!empty($productCoupons)){
			$coupon->setProducts(array_values($productCoupons));
		}

		return $coupon;
	}

	/**
	 * Fetching additional coupon fields
	 * @param Store_Model_Coupon $coupon
	 * @return Store_Model_Coupon
	 */
	protected function _loadDiscountCouponData(Store_Model_Coupon $coupon){
		$dbTable = new Store_DbTable_CouponDiscount();

		$row = $dbTable->fetchRow(array('coupon_id = ?' => $coupon->getId()));
		if (!is_null($row)){
			$coupon->setMinOrderAmount($row->minOrderAmount)
				->setDiscountUnit($row->discountUnits)
				->setDiscountAmount($row->discountAmount)
			;
		}

		return $coupon;
	}

	/**
	 * Fetching additional coupon fields
	 * @param Store_Model_Coupon $coupon
	 * @return Store_Model_Coupon
	 */
	protected function _loadFreeshippingCouponData(Store_Model_Coupon $coupon){
		$dbTable = new Store_DbTable_CouponFreeshipping();

		$row = $dbTable->fetchRow(array('coupon_id = ?' => $coupon->getId()));
		if (!is_null($row)){
			$coupon->setMinOrderAmount($row->minOrderAmount);
		}

		return $coupon;
	}

	/**
	 * Fetch available coupon types
	 * @param bool $pairs Fetch pairs
	 * @return array List of coupon types
	 */
	public function getCouponTypes($pairs = false){
		$dbTable = new Store_DbTable_CouponType();

		return $pairs ? $dbTable->getAdapter()->fetchPairs($dbTable->select()) : $dbTable->fetchAll()->toArray() ;
	}

	/**
	 * Delete coupon model from DB
	 * @param $model Store_Model_Coupon Coupon model
	 * @return bool Result of operation
	 */
	public function delete($model){
		if ($model instanceof $this->_model){
			$id = $model->getId();
		} elseif (is_numeric($model)) {
			$id = intval($model);
		}

		$where = $this->getDbTable()->getAdapter()->quoteInto('id = ?', $id);
		return (bool) $this->getDbTable()->delete($where);
	}

	/**
	 * Find coupon by code
	 * @param $code string|array Code or list of codes
	 * @return array|null List of results
	 */
	public function findByCode($code){
		if (!is_array($code)){
			$code = array($code);
		}

		$where = $this->getDbTable()->getAdapter()->quoteInto('code IN (?)', $code);

		return $this->fetchAll($where);
	}

	public function findByProductId($productId){
		if (!is_array($productId)){
			$productId = array($productId);
		}

		$select = $this->getDbTable()->getAdapter()->select()->from(array('c' => 'shopping_coupon'))
				->join(array('cp' => 'shopping_coupon_product'), 'c.id = cp.coupon_id', null)
				->where('cp.product_id IN (?)', $productId);

		$rawResults = $this->getDbTable()->getAdapter()->fetchAll($select);

		$coupons = array();
		if (!empty($rawResults)){
			foreach ($rawResults as $coupon) {
				array_push($coupons, new $this->_model($coupon));
			}
			$coupons = array_map(array($this, '_loadCouponData'), $coupons);    //loading additional data
		}
		return $coupons;
	}

	/**
	 * Check if coupon used by client
	 * @param $couponId Coupon ID
	 * @param $clientId Client ID
	 * @return bool Result
	 */
	public function checkCouponByClientId($couponId, $clientId) {
		$select = $this->getDbTable()->getAdapter()->select()
				->from(array('u' => 'shopping_coupon_usage'))
				->join(array('c' => 'shopping_cart_session'), 'c.id = u.cart_id')
				->where('c.user_id = ?', $clientId)
				->where('c.status != ?', Models_Model_CartSession::CART_STATUS_NEW );

		$results = $this->getDbTable()->getAdapter()->fetchAll($select);

		return (bool)sizeof($results);
	}

	public function saveCouponsToCart(Tools_ShoppingCart $cart) {
		$dbTable = new Zend_Db_Table('shopping_coupon_usage');
		$coupons = $cart->getCoupons();

		$dbTable->delete(array('cart_id' => $cart->getCartId()));
		foreach ($coupons as $coupon) {
			if ($coupon->getScope() === Store_Model_Coupon::DISCOUNT_SCOPE_CLIENT ){
				try {
					$dbTable->insert(array('coupon_id' => $coupon->getId(), 'cart_id' => $cart->getCartId() ));
				} catch (Exception $e) {
					Tools_System_Tools::debugMode() && error_log($e->getMessage());
				}
			}
		}

		return true;
	}

    /**
     * Store coupon sales history
     *
     * @param Tools_ShoppingCart $cart
     * @return bool
     */
    public function saveCouponSales(Tools_ShoppingCart $cart)
    {
        $dbTable = new Zend_Db_Table('shopping_coupon_sales');
        $coupons = $cart->getCoupons();

        $dbTable->delete(array('cart_id = ?' => $cart->getCartId()));
        foreach ($coupons as $coupon) {
            try {
                $dbTable->insert(array('coupon_code' => $coupon->getCode(), 'cart_id' => $cart->getCartId()));
            } catch (Exception $e) {
                Tools_System_Tools::debugMode() && error_log($e->getMessage());
            }

        }

        return true;
    }

    public function getCouponCodes()
    {
        $dbTable = new Zend_Db_Table('shopping_coupon_sales');
        $select = $dbTable->getAdapter()->select()->from('shopping_coupon_sales', array('coupon_code', 'coupon_code'))->group('coupon_code');
        return $dbTable->getAdapter()->fetchPairs($select);
    }
}
