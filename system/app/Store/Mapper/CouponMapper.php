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
	 */
	public function save($model) {
		if (!$model instanceof $this->_model){
			$model = new $this->_model($model);
		}

		$data = $model->toArray();
		if (isset($data['action'])){
			unset($data['action']);
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

		return $model;
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
		$methodName = '_load'.ucfirst(strtolower($coupon->getType())).'CouponData';
		if (method_exists($this, $methodName)){
			return $this->$methodName($coupon);
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

	/**
	 * Check if coupon used by client
	 * @param $couponId Coupon ID
	 * @param $clientId Client ID
	 * @return bool Result
	 */
	public function checkCouponByClientId($couponId, $clientId) {
		$dbTable = new Zend_Db_Table('shopping_coupon_customer');

		return (bool)$dbTable->find($couponId, $clientId)->count();
	}
}
