<?php
/**
 * InventoryObserver.php
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
class Tools_InventoryObserver implements Interfaces_Observer {

	const UPDATE_SUBTRACT = -1;
	const UPDATE_ADD = 1;

    /**
     * plugin based inventory check stock method
     */
    const INVENTORY_IN_STOCK_METHOD = 'inStock';

    /**
     * plugin base inventory update method
     *
     */
    const INVENTORY_UPDATE_STOCK = 'updateStock';

	private $_oldStatus;

	/**
	 * @var Models_Model_CartSession
	 */
	private $_object;

	/**
	 * @param string     $oldStatus Previous status to track inventory changes
	 * @param null|array $options
	 */
	public function __construct($oldStatus, $options = null) {
		$this->_oldStatus = $oldStatus;
		if (!is_null($options)){
			$this->_options = $options;
		}
		$this->_dbTable = new Models_DbTable_Product();
    }

	/**
	 * @param $object Models_Model_CartSession
	 * @return bool
	 */
	public function notify($object) {
		$this->_object = $object;
		switch ($this->_oldStatus){
			case Models_Model_CartSession::CART_STATUS_NEW:
			case Models_Model_CartSession::CART_STATUS_PENDING:
			case Models_Model_CartSession::CART_STATUS_PROCESSING:
				if ($object->getStatus() === Models_Model_CartSession::CART_STATUS_COMPLETED) {
					return $this->_updateInventory(self::UPDATE_SUBTRACT);
				}
				break;
			default:
				break;
		}
		return true;
	}

	/**
	 * @param int $direction Increase/decrease stock quantity
	 * @return bool
	 */
	private function _updateInventory($direction = self::UPDATE_SUBTRACT){
		switch ($direction){
			case self::UPDATE_SUBTRACT:
				$sqlExpr = 'inventory - ?';
				break;
			case self::UPDATE_ADD:
				$sqlExpr = 'inventory + ?';
				break;
			default:
				return false;
                break;
		}
		$this->_dbTable->getAdapter()->beginTransaction();
		foreach ($this->_object->getCartContent() as $cartItem){
            if (!empty($cartItem['options'])) {
                $options = array();
                foreach ($cartItem['options'] as  $optionData) {
                    $options[$optionData['option_id']] = $optionData['id'];
                }
                Tools_Misc::applyInventory($cartItem['product_id'], $options, $cartItem['qty'], Tools_InventoryObserver::INVENTORY_UPDATE_STOCK);
            }

            $inventory = $this->_dbTable->getAdapter()->quoteInto($sqlExpr, intval($cartItem['qty']));
			$where = $this->_dbTable->getAdapter()->quoteInto('id = ? AND inventory IS NOT NULL', $cartItem['product_id']);
			$this->_dbTable->update(array('inventory' => new Zend_Db_Expr($inventory)), $where);
		}

		try {
			$this->_dbTable->getAdapter()->commit();
			return true;
		} catch (Exception $e){
			Tools_System_Tools::debugMode() && error_log($e->getMessage());
		}
	}
}
